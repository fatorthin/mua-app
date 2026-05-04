<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
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

        $pdfBinary = \Barryvdh\DomPDF\Facade\Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->setPaper('A4')
            ->output();

        $headers = [];
        if ($deviceId !== '') {
            $headers['X-Device-Id'] = $deviceId;
        }

        $response = Http::withBasicAuth($username, $password)
            ->withHeaders($headers)
            ->attach('file', $pdfBinary, 'Invoice-' . $invoice->invoice_number . '.pdf')
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

    private function buildInvoiceCaption(Booking $booking, Invoice $invoice): string
    {
        $clientName = $booking->client?->name ?? 'Pelanggan';
        $bookingDate = $booking->booking_date?->format('d M Y H:i') ?? '-';
        $subtotal = number_format((float) $invoice->subtotal, 0, ',', '.');
        $remaining = number_format((float) $invoice->total, 0, ',', '.');
        $dueDate = $invoice->due_date?->format('d M Y') ?? '-';

        $lines = [
            'Halo ' . $clientName . ',',
            '',
            'Booking Anda berhasil dibuat.',
            'No. Invoice: ' . $invoice->invoice_number,
            'Tanggal Booking: ' . $bookingDate,
            'Total Layanan: Rp ' . $subtotal,
        ];

        if ($booking->is_dp_paid && (float) $booking->dp_amount > 0) {
            $lines[] = 'DP Dibayar: Rp ' . number_format((float) $booking->dp_amount, 0, ',', '.');
        }

        $lines[] = 'Sisa Tagihan: Rp ' . $remaining;
        $lines[] = 'Jatuh Tempo: ' . $dueDate;
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
