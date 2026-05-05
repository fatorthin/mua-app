<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendReminder(Booking $booking): bool
    {
        $url = rtrim((string) config('services.whatsapp_gateway.url'), '/');
        $auth = (string) config('services.whatsapp_gateway.auth');
        $deviceId = (string) config('services.whatsapp_gateway.device_id');

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

        $headers = [];
        if ($deviceId !== '') {
            $headers['X-Device-Id'] = $deviceId;
        }

        $response = Http::withBasicAuth($username, $password)
            ->withHeaders($headers)
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
        $url = rtrim((string) config('services.whatsapp_gateway.url'), '/');
        $auth = (string) config('services.whatsapp_gateway.auth');
        $deviceId = (string) config('services.whatsapp_gateway.device_id');

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
            Log::warning('Skip WhatsApp invoice notification because client phone is empty.', [
                'booking_id' => $booking->id,
            ]);
            return false;
        }

        $caption = $this->buildInvoiceCaption($booking, $invoice);

        $invoice->loadMissing(['booking.client', 'booking.service', 'booking.user', 'booking.items.service']);
        $logoPath = $this->getLogoBase64($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        $fileBinary = \Barryvdh\DomPDF\Facade\Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->setPaper('A4')
            ->output();

        $fileName = 'Invoice-' . $invoice->invoice_number . '.pdf';

        $headers = [];
        if ($deviceId !== '') {
            $headers['X-Device-Id'] = $deviceId;
        }

        $response = Http::withBasicAuth($username, $password)
            ->withHeaders($headers)
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

    private function getLogoBase64(Invoice $invoice): ?string
    {
        $path = $invoice->booking->user->invoice_logo_path ?? null;
        if (! $path) {
            return null;
        }

        $absolute = storage_path('app/public/' . ltrim($path, '/'));
        if (! is_file($absolute)) {
            return null;
        }

        try {
            $mime = mime_content_type($absolute);
            $data = file_get_contents($absolute);
            return 'data:' . $mime . ';base64,' . base64_encode($data);
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

        if ($booking->is_dp_paid && (float) $booking->dp_amount > 0) {
            $lines[] = 'DP Dibayar: Rp ' . number_format((float) $booking->dp_amount, 0, ',', '.');
        }

        $lines[] = 'Sisa Tagihan: Rp ' . $remaining;
        $lines[] = 'Jatuh Tempo: ' . $dueDateStr;
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
