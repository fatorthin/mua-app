<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class InvoiceController extends Controller
{
    private function getLogoBase64(Invoice $invoice): ?string
    {
        $path = $this->resolveLogoPath($invoice);
        if (! $path) {
            return null;
        }

        try {
            $mime = mime_content_type($path);
            $data = file_get_contents($path);
            return 'data:' . $mime . ';base64,' . base64_encode($data);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function pdf(Invoice $invoice): Response
    {
        abort_unless(
            $invoice->booking->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        $invoice->loadMissing(['booking.client', 'booking.user', 'booking.items.service']);

        $logoPath = $this->getLogoBase64($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        $pdf = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->setPaper('A4');

        return $pdf->stream($invoice->invoice_number . '.pdf');
    }

    public function previewJpg(Invoice $invoice): Response|RedirectResponse
    {
        abort_unless(
            $invoice->booking->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        $signedUrl = URL::temporarySignedRoute('invoices.public-jpg', now()->addMinutes(10), [
            'invoice' => $invoice,
        ]);

        return redirect()->away($signedUrl);
    }

    public function publicPdf(Request $request, Invoice $invoice): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        $invoice->loadMissing(['booking.client', 'booking.user', 'booking.items.service']);

        $logoPath = $this->getLogoBase64($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        $pdf = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->setPaper('A4');

        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    public function publicJpg(Request $request, Invoice $invoice): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        return $this->jpgResponse($invoice);
    }

    private function jpgResponse(Invoice $invoice): Response
    {
        $invoice->loadMissing(['booking.client', 'booking.service', 'booking.user', 'booking.items.service']);

        $logoPath = $this->getLogoBase64($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        $pdfBinary = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->loadView('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->setPaper('A4')
            ->output();

        $convertedJpg = $this->convertPdfToJpgBinary($pdfBinary);
        if ($convertedJpg !== null) {
            return response($convertedJpg, 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.jpg"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        $image = imagecreatetruecolor(1240, 1754);
        if ($image === false) {
            abort(500, 'Gagal membuat image invoice.');
        }

        $white = imagecolorallocate($image, 251, 249, 247);
        $strip = imagecolorallocate($image, 233, 221, 214);
        $accent = imagecolorallocate($image, 196, 130, 107);
        $border = imagecolorallocate($image, 221, 190, 177);
        $headBg = imagecolorallocate($image, 239, 228, 222);
        $dark = imagecolorallocate($image, 47, 42, 39);
        $muted = imagecolorallocate($image, 108, 90, 80);

        imagefilledrectangle($image, 0, 0, 1239, 1753, $white);
        imagefilledrectangle($image, 0, 0, 1239, 38, $strip);
        imagefilledrectangle($image, 0, 1716, 1239, 1753, $strip);

        imagestring($image, 5, 90, 88, strtoupper((string) ($invoice->booking->user->studio_name ?? 'MUA STUDIO')), $dark);
        imagestring($image, 2, 90, 118, 'Invoice layanan profesional', $muted);
        imagestring($image, 5, 880, 86, 'INVOICE', $accent);

        $this->drawLogo($image, $this->resolveLogoPath($invoice), 80, 48, 180, 36);

        imageline($image, 80, 160, 1160, 160, $accent);

        imagestring($image, 3, 80, 200, 'Kepada:', $muted);
        imagestring($image, 5, 80, 232, strtoupper((string) ($invoice->booking->client->name ?? '-')), $dark);
        imagestring($image, 3, 850, 200, 'Nomor: ' . $invoice->invoice_number, $dark);
        imagestring($image, 3, 850, 228, 'Tanggal: ' . $invoice->created_at->format('d M Y'), $dark);
        imagestring($image, 3, 850, 256, 'Jatuh Tempo: ' . ($invoice->due_date?->format('d M Y') ?? '-'), $dark);

        $tableX = 80;
        $tableY = 320;
        $tableW = 1080;
        $rowH = 58;
        $col1 = 420;
        $col2 = 190;
        $col3 = 260;
        $col4 = 210;

        imagesetthickness($image, 2);
        imagefilledrectangle($image, $tableX, $tableY, $tableX + $tableW, $tableY + $rowH, $headBg);
        imagerectangle($image, $tableX, $tableY, $tableX + $tableW, $tableY + $rowH, $border);
        imageline($image, $tableX + $col1, $tableY, $tableX + $col1, $tableY + $rowH, $border);
        imageline($image, $tableX + $col1 + $col2, $tableY, $tableX + $col1 + $col2, $tableY + $rowH, $border);
        imageline($image, $tableX + $col1 + $col2 + $col3, $tableY, $tableX + $col1 + $col2 + $col3, $tableY + $rowH, $border);
        imagesetthickness($image, 1);

        imagestring($image, 3, $tableX + 12, $tableY + 20, 'NAMA LAYANAN', $dark);
        imagestring($image, 3, $tableX + $col1 + 12, $tableY + 20, 'JUMLAH', $dark);
        imagestring($image, 3, $tableX + $col1 + $col2 + 12, $tableY + 20, 'HARGA SATUAN', $dark);
        imagestring($image, 3, $tableX + $col1 + $col2 + $col3 + 12, $tableY + 20, 'JUMLAH HARGA', $dark);

        $rows = [];
        if ($invoice->booking->items->count() > 0) {
            foreach ($invoice->booking->items as $item) {
                $qty = (float) ($item->qty ?? 1);
                $unitPrice = (float) ($item->price ?? 0);
                $rows[] = [
                    'name' => (string) ($item->service?->name ?? 'Layanan'),
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $qty * $unitPrice,
                ];
            }
        } else {
            $rows[] = [
                'name' => (string) ($invoice->booking->service->name ?? 'Layanan'),
                'qty' => 1,
                'unit_price' => (float) $invoice->subtotal,
                'line_total' => (float) $invoice->subtotal,
            ];
        }

        $cursorY = $tableY + $rowH;
        foreach (array_slice($rows, 0, 7) as $row) {
            $nextY = $cursorY + $rowH;
            imagesetthickness($image, 2);
            imagerectangle($image, $tableX, $cursorY, $tableX + $tableW, $nextY, $border);
            imageline($image, $tableX + $col1, $cursorY, $tableX + $col1, $nextY, $border);
            imageline($image, $tableX + $col1 + $col2, $cursorY, $tableX + $col1 + $col2, $nextY, $border);
            imageline($image, $tableX + $col1 + $col2 + $col3, $cursorY, $tableX + $col1 + $col2 + $col3, $nextY, $border);
            imagesetthickness($image, 1);

            imagestring($image, 3, $tableX + 12, $cursorY + 21, substr($row['name'], 0, 36), $dark);
            imagestring($image, 3, $tableX + $col1 + 12, $cursorY + 21, rtrim(rtrim(number_format((float) $row['qty'], 2, ',', '.'), '0'), ',') . ' paket', $dark);
            imagestring($image, 3, $tableX + $col1 + $col2 + 12, $cursorY + 21, 'Rp ' . number_format((float) $row['unit_price'], 0, ',', '.'), $dark);
            imagestring($image, 3, $tableX + $col1 + $col2 + $col3 + 12, $cursorY + 21, 'Rp ' . number_format((float) $row['line_total'], 0, ',', '.'), $dark);

            $cursorY = $nextY;
        }

        $summaryY = $cursorY + 36;
        imagestring($image, 5, 80, $summaryY, 'METODE PEMBAYARAN:', $dark);

        $footerNotes = trim((string) ($invoice->booking->user->invoice_footer_notes ?? ''));
        $paymentText = $footerNotes !== '' ? $footerNotes : 'Silakan hubungi admin untuk detail pembayaran.';
        $noteLines = explode("\n", wordwrap($paymentText, 48, "\n", true));
        $lineY = $summaryY + 38;
        foreach (array_slice($noteLines, 0, 7) as $line) {
            imagestring($image, 3, 80, $lineY, trim($line), $dark);
            $lineY += 22;
        }

        $totX = 760;
        $totY = $summaryY;
        imagestring($image, 4, $totX, $totY, 'Subtotal:', $dark);
        imagestring($image, 4, 1000, $totY, 'Rp ' . number_format((float) $invoice->subtotal, 0, ',', '.'), $dark);

        $totY += 30;
        if ($invoice->booking->is_dp_paid && (float) $invoice->booking->dp_amount > 0) {
            imagestring($image, 4, $totX, $totY, 'DP Dibayar:', $dark);
            imagestring($image, 4, 1000, $totY, '- Rp ' . number_format((float) $invoice->booking->dp_amount, 0, ',', '.'), $dark);
            $totY += 30;
        }

        imagestring($image, 4, $totX, $totY, 'Pajak:', $dark);
        imagestring($image, 4, 1000, $totY, 'Rp ' . number_format((float) $invoice->tax, 0, ',', '.'), $dark);

        $totY += 46;
        imagefilledrectangle($image, 700, $totY - 12, 1160, $totY + 52, $strip);
        imagestring($image, 5, 785, $totY + 13, 'TOTAL:', $dark);
        imagestring($image, 5, 975, $totY + 13, 'Rp ' . number_format((float) $invoice->total, 0, ',', '.'), $dark);

        $footerY = 1460;
        imagestring($image, 5, 80, $footerY, 'KONFIRMASI HUBUNGI:', $dark);
        imagestring($image, 3, 80, $footerY + 40, (string) ($invoice->booking->user->phone ?? '-'), $dark);
        imagestring($image, 3, 80, $footerY + 62, (string) ($invoice->booking->user->email ?? '-'), $dark);

        imagestring($image, 5, 930, $footerY, 'TERIMAKASIH', $dark);
        imagestring($image, 5, 860, $footerY + 72, substr((string) ($invoice->booking->user->name ?? 'Admin'), 0, 22), $accent);

        ob_start();
        imagejpeg($image, null, 90);
        $binary = ob_get_clean();
        imagedestroy($image);

        if ($binary === false) {
            abort(500, 'Gagal menghasilkan JPEG invoice.');
        }

        return response($binary, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.jpg"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    private function convertPdfToJpgBinary(string $pdfBinary): ?string
    {
        if (! extension_loaded('imagick') || ! class_exists('Imagick')) {
            return null;
        }

        $imagickClass = 'Imagick';
        $imagick = new $imagickClass();

        try {
            $imagick->setResolution(170, 170);
            $imagick->readImageBlob($pdfBinary);

            if ($imagick->getNumberImages() === 0) {
                return null;
            }

            $imagick->setIteratorIndex(0);
            $page = $imagick->getImage();

            if ($page->getImageAlphaChannel()) {
                $flattenMethod = defined('Imagick::LAYERMETHOD_FLATTEN')
                    ? constant('Imagick::LAYERMETHOD_FLATTEN')
                    : 11;
                $page = $page->mergeImageLayers($flattenMethod);
            }

            $page->setImageFormat('jpeg');
            $page->setImageCompressionQuality(90);

            return $page->getImageBlob();
        } catch (\Throwable $e) {
            Log::warning('Failed converting invoice PDF to JPG via Imagick, fallback to manual renderer.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        } finally {
            $imagick->clear();
            $imagick->destroy();
        }
    }

    private function resolveLogoPath(Invoice $invoice): ?string
    {
        $path = $invoice->booking->user->invoice_logo_path ?? null;
        if (! $path) {
            return null;
        }

        $absolute = storage_path('app/public/' . ltrim($path, '/'));
        return is_file($absolute) ? $absolute : null;
    }

    private function drawLogo($canvas, ?string $logoPath, int $x, int $y, int $maxWidth, int $maxHeight): void
    {
        if (! $logoPath || ! is_file($logoPath)) {
            return;
        }

        $content = @file_get_contents($logoPath);
        if ($content === false) {
            return;
        }

        $logo = @imagecreatefromstring($content);
        if ($logo === false) {
            return;
        }

        $w = imagesx($logo);
        $h = imagesy($logo);
        if ($w <= 0 || $h <= 0) {
            imagedestroy($logo);
            return;
        }

        $ratio = min($maxWidth / $w, $maxHeight / $h, 1);
        $targetW = (int) round($w * $ratio);
        $targetH = (int) round($h * $ratio);

        imagecopyresampled($canvas, $logo, $x + ($maxWidth - $targetW), $y + ($maxHeight - $targetH) / 2, 0, 0, $targetW, $targetH, $w, $h);
        imagedestroy($logo);
    }
}
