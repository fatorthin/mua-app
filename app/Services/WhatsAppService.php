<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function createDevice(User $user, ?string $preferredDeviceId = null): array
    {
        ['url' => $url, 'auth' => $auth] = $this->gatewayConfigFor($user);

        if ($url === '' || $auth === '') {
            return ['ok' => false, 'message' => 'WhatsApp gateway belum dikonfigurasi.'];
        }

        $preferredDeviceId = trim((string) $preferredDeviceId);
        if ($preferredDeviceId === '') {
            $preferredDeviceId = 'user-' . $user->id . '-' . substr(md5($user->id . '-' . ($user->email ?? 'mua') . '-' . time() . '-' . uniqid()), 0, 8);
        }

        $payload = [
            'device_id' => $preferredDeviceId,
        ];

        $response = $this->authorizedRequest($auth)
            ->asJson()
            ->acceptJson()
            ->send('POST', $url . '/devices', [
                'body' => json_encode((object) $payload, JSON_UNESCAPED_SLASHES),
            ]);

        if ($response->failed()) {
            return [
                'ok' => false,
                'message' => $response->json('message') ?: 'Gagal membuat device WhatsApp.',
            ];
        }

        $deviceId = (string) ($response->json('results.id')
            ?? $response->json('results.device_id')
            ?? $preferredDeviceId);

        $user->forceFill([
            'whatsapp_device_id' => $deviceId !== '' ? $deviceId : null,
        ])->save();

        return [
            'ok' => true,
            'message' => 'Device WhatsApp berhasil dibuat.',
            'device_id' => $deviceId,
        ];
    }

    public function refreshDeviceStatus(User $user): array
    {
        ['url' => $url, 'auth' => $auth, 'device_id' => $deviceId] = $this->gatewayConfigFor($user);

        if ($url === '' || $auth === '') {
            return ['ok' => false, 'message' => 'WhatsApp gateway belum dikonfigurasi.'];
        }

        if ($deviceId === '') {
            $user->forceFill([
                'whatsapp_device_status' => null,
                'whatsapp_device_jid' => null,
                'whatsapp_device_last_synced_at' => now(),
            ])->save();

            return ['ok' => false, 'message' => 'Device ID belum diisi.'];
        }

        $response = $this->authorizedRequest($auth)
            ->acceptJson()
            ->get($url . '/devices');

        if ($response->failed()) {
            return [
                'ok' => false,
                'message' => $response->json('message') ?: 'Gagal mengambil daftar device.',
            ];
        }

        $devices = collect((array) ($response->json('results') ?? []));
        $device = $devices->first(function ($item) use ($deviceId) {
            return is_array($item) && (($item['id'] ?? null) === $deviceId || ($item['device_id'] ?? null) === $deviceId);
        });

        $status = $device['state'] ?? 'not_found';
        $jid = $device['jid'] ?? null;

        $user->forceFill([
            'whatsapp_device_status' => $status,
            'whatsapp_device_jid' => $jid,
            'whatsapp_device_last_synced_at' => now(),
        ])->save();

        return [
            'ok' => $device !== null,
            'message' => $device !== null ? 'Status device berhasil diperbarui.' : 'Device tidak ditemukan di gateway.',
            'device' => $device,
        ];
    }

    public function requestLoginQr(User $user): array
    {
        ['url' => $url, 'auth' => $auth, 'device_id' => $deviceId] = $this->gatewayConfigFor($user);

        if ($url === '' || $auth === '') {
            return ['ok' => false, 'message' => 'WhatsApp gateway belum dikonfigurasi.'];
        }

        if ($deviceId === '') {
            $created = $this->createDevice($user);
            if (!$created['ok']) {
                return $created;
            }
            $deviceId = $created['device_id'];
        }

        $response = $this->authorizedRequest($auth)
            ->withHeaders($this->deviceHeaders($deviceId))
            ->acceptJson()
            ->get($url . '/app/login');

        if ($response->failed()) {
            return [
                'ok' => false,
                'message' => $response->json('message') ?: 'Gagal mengambil QR login.',
            ];
        }

        $qrLink = $response->json('results.qr_link');
        $qrDuration = $response->json('results.qr_duration');

        if (!empty($qrLink) && is_string($qrLink) && !str_starts_with($qrLink, 'data:image')) {
            try {
                $parsedPath = parse_url($qrLink, PHP_URL_PATH) ?: $qrLink;
                $parsedQuery = parse_url($qrLink, PHP_URL_QUERY);
                $relativeOrFullPath = $parsedPath . ($parsedQuery ? '?' . $parsedQuery : '');

                $fetchUrl = rtrim($url, '/') . '/' . ltrim($relativeOrFullPath, '/');

                $imgResponse = $this->authorizedRequest($auth)
                    ->withHeaders($this->deviceHeaders($deviceId))
                    ->get($fetchUrl);

                if (!$imgResponse->successful() && (str_starts_with($qrLink, 'http://') || str_starts_with($qrLink, 'https://'))) {
                    $imgResponse = $this->authorizedRequest($auth)
                        ->withHeaders($this->deviceHeaders($deviceId))
                        ->get($qrLink);
                }

                if ($imgResponse->successful() && !empty($imgResponse->body())) {
                    $mimeType = $imgResponse->header('Content-Type') ?: 'image/png';
                    if (str_contains($mimeType, ';')) {
                        $mimeType = trim(explode(';', $mimeType)[0]);
                    }
                    $qrLink = 'data:' . $mimeType . ';base64,' . base64_encode($imgResponse->body());
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal mengunduh gambar QR login dari WhatsApp gateway: ' . $e->getMessage());
            }
        }

        return [
            'ok' => true,
            'message' => 'QR login berhasil dibuat.',
            'qr_link' => $qrLink,
            'qr_duration' => $qrDuration,
        ];
    }

    public function requestPairingCode(User $user, string $phone): array
    {
        ['url' => $url, 'auth' => $auth, 'device_id' => $deviceId] = $this->gatewayConfigFor($user);

        if ($url === '' || $auth === '') {
            return ['ok' => false, 'message' => 'WhatsApp gateway belum dikonfigurasi.'];
        }

        if ($deviceId === '') {
            $created = $this->createDevice($user);
            if (!$created['ok']) {
                return $created;
            }
            $deviceId = $created['device_id'];
        }

        $response = $this->authorizedRequest($auth)
            ->withHeaders($this->deviceHeaders($deviceId))
            ->acceptJson()
            ->get($url . '/app/login-with-code', [
                'phone' => $phone,
            ]);

        if ($response->failed()) {
            return [
                'ok' => false,
                'message' => $response->json('message') ?: 'Gagal mengambil pair code.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Pair code berhasil dibuat.',
            'pair_code' => $response->json('results.pair_code'),
        ];
    }

    public function sendTestMessage(User $user, string $phone, ?string $message = null): array
    {
        ['url' => $url, 'auth' => $auth, 'device_id' => $deviceId] = $this->gatewayConfigFor($user);

        if ($url === '' || $auth === '') {
            return ['ok' => false, 'message' => 'WhatsApp gateway belum dikonfigurasi.'];
        }

        if ($deviceId === '') {
            return ['ok' => false, 'message' => 'Device ID belum tersedia.'];
        }

        $jid = $this->toWhatsappJid($phone);
        if ($jid === null) {
            return ['ok' => false, 'message' => 'Nomor tujuan test tidak valid.'];
        }

        $response = $this->authorizedRequest($auth)
            ->withHeaders($this->deviceHeaders($deviceId))
            ->acceptJson()
            ->post($url . '/send/message', [
                'phone' => $jid,
                'message' => $message ?: 'Test koneksi WhatsApp dari MUA Manager berhasil.',
            ]);

        if ($response->failed()) {
            return [
                'ok' => false,
                'message' => $response->json('message') ?: 'Gagal mengirim pesan test.',
            ];
        }

        return ['ok' => true, 'message' => 'Pesan test berhasil dikirim.'];
    }

    public function sendReminder(Booking $booking): bool
    {
        $booking->loadMissing(['user', 'client', 'service']);

        ['url' => $url, 'auth' => $auth, 'device_id' => $deviceId] = $this->gatewayConfigFor($booking->user);

        if ($url === '' || $auth === '') {
            return false;
        }

        [$username, $password] = $this->parseBasicAuth($auth);
        if ($username === '' || $password === '') {
            Log::warning('WhatsApp gateway auth format invalid. Expected user:password.');
            return false;
        }

        $phone = $this->toWhatsappJid($booking->client?->phone);
        if ($phone === null) {
            Log::warning('Skip WhatsApp reminder because client phone is empty.', [
                'booking_id' => $booking->id,
            ]);
            return false;
        }

        $message = $this->buildReminderMessage($booking);

        $response = Http::withBasicAuth($username, $password)
            ->withHeaders($this->deviceHeaders($deviceId))
            ->acceptJson()
            ->post($url . '/send/message', [
                'phone'   => $phone,
                'message' => $message,
            ]);

        if ($response->failed()) {
            Log::warning('Failed sending WhatsApp reminder.', [
                'booking_id' => $booking->id,
                'status'     => $response->status(),
                'response'   => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    public function sendPaymentReceipt(Booking $booking, Invoice $invoice, string $paymentMethod = 'Transfer Bank'): bool
    {
        $booking->loadMissing(['user', 'client', 'service']);

        ['url' => $url, 'auth' => $auth, 'device_id' => $deviceId] = $this->gatewayConfigFor($booking->user);

        if ($url === '' || $auth === '') {
            return false;
        }

        [$username, $password] = $this->parseBasicAuth($auth);
        if ($username === '' || $password === '') {
            Log::warning('WhatsApp gateway auth format invalid. Expected user:password.');
            return false;
        }

        $phone = $this->toWhatsappJid($booking->client?->phone);
        if ($phone === null) {
            Log::warning('Skip WhatsApp receipt notification because client phone is empty.', [
                'booking_id' => $booking->id,
            ]);
            return false;
        }

        $clientName = $booking->client?->name ?? 'Pelanggan';
        $owner = $booking->user;
        $studioName = $owner->studio_name ?? 'MUA STUDIO';

        $lines = [
            'Halo ' . $clientName . ', 👋',
            '',
            '🧾 *KUITANSI PEMBAYARAN LUNAS*',
            '',
            'Terima kasih! Pembayaran untuk booking Anda telah kami terima dan berstatus *LUNAS*:',
            '📄 No. Invoice : ' . $invoice->invoice_number,
            '💄 Layanan     : ' . ($booking->service?->name ?? '-'),
            '💰 Total Bayar : Rp ' . number_format((float) $invoice->total, 0, ',', '.'),
            '💳 Metode      : ' . $paymentMethod,
            '📅 Tgl Lunas   : ' . now()->format('d/m/Y H:i'),
            '',
            'Kami sangat senang dapat melayani Anda. Sampai jumpa di hari H ya! 🌸',
            '',
            'Salam hangat,',
            '*' . $studioName . '*',
        ];

        $response = Http::withBasicAuth($username, $password)
            ->withHeaders($this->deviceHeaders($deviceId))
            ->acceptJson()
            ->post($url . '/send/message', [
                'phone'   => $phone,
                'message' => implode("\n", $lines),
            ]);

        if ($response->failed()) {
            Log::warning('Failed sending WhatsApp payment receipt.', [
                'booking_id' => $booking->id,
                'invoice_id' => $invoice->id,
                'status'     => $response->status(),
                'response'   => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    private function buildReminderMessage(Booking $booking): string
    {
        $clientName = $booking->client?->name ?? 'Pelanggan';

        $bookingDateStr = '-';
        if ($booking->booking_date) {
            $bd = $booking->booking_date;
            $bookingDateStr = $bd->format('d') . ' ' . $this->getIndonesianMonth((int) $bd->format('n')) . ' ' . $bd->format('Y') . ' pukul ' . $bd->format('H:i');
        }

        $location = $booking->location ?: '-';
        $serviceName = $booking->service?->name ?? '-';

        $remaining = $booking->is_dp_paid
            ? number_format(max(0, (float) $booking->price - (float) $booking->dp_amount), 0, ',', '.')
            : number_format((float) $booking->price, 0, ',', '.');

        $lines = [
            'Halo ' . $clientName . ', 👋',
            '',
            '⏰ *Pengingat Booking MUA*',
            '',
            'Booking Anda dijadwalkan *besok*:',
            '📅 Tanggal  : ' . $bookingDateStr,
            '💄 Layanan  : ' . $serviceName,
            '📍 Lokasi   : ' . $location,
        ];

        if ($booking->is_dp_paid && (float) $booking->dp_amount > 0) {
            $lines[] = '💰 Sisa Bayar: Rp ' . $remaining;
        } else {
            $lines[] = '💰 Total     : Rp ' . $remaining;
        }

        $lines[] = '';
        $lines[] = 'Mohon pastikan Anda sudah siap ya. Jika ada pertanyaan, jangan ragu untuk menghubungi kami.';
        $lines[] = '';
        $lines[] = 'Terima kasih! 🌸';

        return implode("\n", $lines);
    }

    public function sendInvoiceCreated(Booking $booking, Invoice $invoice): bool
    {
        $booking->loadMissing(['user', 'client', 'service']);

        ['url' => $url, 'auth' => $auth, 'device_id' => $deviceId] = $this->gatewayConfigFor($booking->user);

        if ($url === '' || $auth === '') {
            Log::warning('WhatsApp gateway URL or Auth not configured.');
            return false;
        }

        if ($deviceId === '') {
            Log::warning('WhatsApp device ID is empty for user: ' . ($booking->user_id ?? 'null'));
            return false;
        }

        $phone = $this->toWhatsappJid($booking->client?->phone);
        if ($phone === null) {
            Log::warning('Skip WhatsApp invoice notification because client phone is empty.', [
                'booking_id' => $booking->id,
            ]);
            return false;
        }

        $caption = $this->buildInvoiceCaption($booking, $invoice);

        $invoice->loadMissing(['booking.client', 'booking.service', 'booking.user', 'booking.items.service']);
        $logoPath = $this->getLogoBase64($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        $fileBinary = \Barryvdh\DomPDF\Facade\Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => [base_path(), storage_path(), public_path()],
        ])
            ->loadView('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->setPaper('A4')
            ->output();

        $fileName = 'Invoice-' . $invoice->invoice_number . '.pdf';

        $response = $this->authorizedRequest($auth)
            ->withHeaders($this->deviceHeaders($deviceId))
            ->attach('file', $fileBinary, $fileName)
            ->acceptJson()
            ->post($url . '/send/file', [
                'phone' => $phone,
                'caption' => $caption,
            ]);

        if ($response->failed()) {
            Log::warning('Failed sending WhatsApp invoice notification.', [
                'booking_id' => $booking->id,
                'invoice_id' => $invoice->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    public function gatewayConfigFor(?User $user): array
    {
        return [
            'url' => rtrim((string) config('services.whatsapp_gateway.url'), '/'),
            'auth' => (string) config('services.whatsapp_gateway.auth'),
            'device_id' => trim((string) ($user?->whatsapp_device_id ?: config('services.whatsapp_gateway.device_id'))),
        ];
    }

    public function deviceHeaders(string $deviceId): array
    {
        return $deviceId !== '' ? ['X-Device-Id' => $deviceId] : [];
    }

    private function authorizedRequest(string $auth): PendingRequest
    {
        [$username, $password] = $this->parseBasicAuth($auth);

        return Http::withBasicAuth($username, $password);
    }

    private function getLogoBase64(Invoice $invoice): ?string
    {
        $path = $invoice->booking->user->invoice_logo_path ?? null;
        if (! $path) {
            return null;
        }

        $absolute = null;
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            $storagePath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
            if (is_file($storagePath)) {
                $absolute = $storagePath;
            }
        }

        if (! $absolute) {
            $storagePath = storage_path('app/public/' . ltrim($path, '/'));
            if (is_file($storagePath)) {
                $absolute = $storagePath;
            }
        }

        if (! $absolute) {
            $publicPath = public_path('storage/' . ltrim($path, '/'));
            if (is_file($publicPath)) {
                $absolute = $publicPath;
            }
        }

        if (! $absolute || ! is_file($absolute)) {
            return null;
        }

        try {
            $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
            $mime = match($ext) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                default => 'image/png',
            };
            $data = file_get_contents($absolute);
            return $data !== false ? 'data:' . $mime . ';base64,' . base64_encode($data) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getIndonesianMonth(int $month): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        return $months[$month] ?? '';
    }

    private function buildInvoiceCaption(Booking $booking, Invoice $invoice): string
    {
        $clientName = $booking->client?->name ?? 'Pelanggan';
        $owner = $booking->user;

        $bookingDateStr = '-';
        if ($booking->booking_date) {
            $bd = $booking->booking_date;
            $bookingDateStr = $bd->format('d') . ' ' . $this->getIndonesianMonth((int)$bd->format('n')) . ' ' . $bd->format('Y H:i');
        }

        $subtotal = number_format((float) $invoice->subtotal, 0, ',', '.');
        $remaining = number_format((float) $invoice->total, 0, ',', '.');

        $dueDateStr = '-';
        if ($booking->booking_date) {
            $dd = $booking->booking_date->copy()->subDay();
            $dueDateStr = $dd->format('d') . ' ' . $this->getIndonesianMonth((int)$dd->format('n')) . ' ' . $dd->format('Y');
        }

        $lines = [
            'Halo ' . $clientName . ',',
            '',
            'Booking Anda berhasil dibuat.',
            'No. Invoice: ' . $invoice->invoice_number,
            'Tanggal Booking: ' . $bookingDateStr,
            'Total Layanan: Rp ' . $subtotal,
        ];

        if ((float) $booking->transport_fee > 0) {
            $lines[] = 'Biaya Transport: Rp ' . number_format((float) $booking->transport_fee, 0, ',', '.');
        }

        if ($booking->is_dp_paid && (float) $booking->dp_amount > 0) {
            $lines[] = 'DP Dibayar: Rp ' . number_format((float) $booking->dp_amount, 0, ',', '.');
        }

        $lines[] = 'Sisa Tagihan: Rp ' . $remaining;
        $lines[] = 'Jatuh Tempo: ' . $dueDateStr;

        $contacts = [];
        if (! empty($owner?->phone)) {
            $contacts[] = 'WA: ' . $owner->phone;
        }
        if (! empty($owner?->instagram)) {
            $contacts[] = 'IG: @' . ltrim((string) $owner->instagram, '@');
        }
        if (! empty($owner?->tiktok)) {
            $contacts[] = 'TikTok: @' . ltrim((string) $owner->tiktok, '@');
        }
        if (! empty($contacts)) {
            $lines[] = 'Kontak MUA: ' . implode(' | ', $contacts);
        }

        $lines[] = '';
        $lines[] = 'Terima kasih.';

        return implode("\n", $lines);
    }

    private function toWhatsappJid(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        if (str_contains($phone, '@')) {
            return $phone;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            $normalized = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $normalized = '62' . ltrim($digits, '0');
        } else {
            $normalized = '62' . $digits;
        }

        return $normalized . '@s.whatsapp.net';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseBasicAuth(string $auth): array
    {
        $parts = explode(':', $auth, 2);
        if (count($parts) !== 2) {
            return ['', ''];
        }

        return [$parts[0], $parts[1]];
    }
}
