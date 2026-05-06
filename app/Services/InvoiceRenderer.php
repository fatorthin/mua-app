<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class InvoiceRenderer
{
    public function getJpgBinary(Invoice $invoice): ?string
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
            return $convertedJpg;
        }

        // Fallback: render dengan GD jika imagick tidak tersedia
        return $this->renderWithGd($invoice);
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

    private function renderWithGd(Invoice $invoice): ?string
    {
        $image = imagecreatetruecolor(1240, 1754);
        if ($image === false) {
            return null;
        }

        $white  = imagecolorallocate($image, 255, 255, 255);
        $strip  = imagecolorallocate($image, 233, 221, 214);
        $accent = imagecolorallocate($image, 196, 130, 107);
        $border = imagecolorallocate($image, 217, 156, 156);
        $headBg = imagecolorallocate($image, 245, 235, 230);
        $dark   = imagecolorallocate($image, 17, 17, 17);
        $muted  = imagecolorallocate($image, 100, 100, 100);
        $bold   = imagecolorallocate($image, 0, 0, 0);

        imagefilledrectangle($image, 0, 0, 1239, 1753, $white);

        // Header brand (kiri)
        $logoPath = $invoice->booking->user->invoice_logo_path ?? null;
        $absLogo  = $logoPath ? storage_path('app/public/' . ltrim($logoPath, '/')) : null;
        $this->drawLogo($image, $absLogo, 60, 60, 160, 60);

        imagestring($image, 5, 60, 130, (string) ($invoice->booking->user->studio_name ?? 'MUA STUDIO'), $bold);
        $phone = (string) ($invoice->booking->user->phone ?? '');
        if ($phone !== '') {
            imagestring($image, 3, 60, 158, 'WA : ' . $phone, $muted);
        }
        $instagram = ltrim((string) ($invoice->booking->user->instagram ?? ''), '@');
        if ($instagram !== '') {
            imagestring($image, 3, 60, 180, 'IG : @' . $instagram, $muted);
        }
        $tiktok = ltrim((string) ($invoice->booking->user->tiktok ?? ''), '@');
        if ($tiktok !== '') {
            imagestring($image, 3, 60, 202, 'TikTok : @' . $tiktok, $muted);
        }

        // Header invoice (kanan)
        imagestring($image, 5, 800, 60, 'INVOICE', $bold);

        // Badge total
        $totalStr = 'Rp ' . number_format((float) $invoice->total, 0, ',', '.');
        imagefilledrectangle($image, 800, 90, 1170, 130, $border);
        imagestring($image, 5, 820, 103, $totalStr, $white);

        imagestring($image, 3, 800, 145, $invoice->invoice_number, $muted);
        imagestring($image, 3, 800, 167, 'Terbit: ' . $invoice->created_at->format('d/m/Y'), $muted);

        // Separator
        imageline($image, 60, 210, 1180, 210, $border);

        // Untuk
        imagestring($image, 3, 60, 225, 'Untuk', $muted);
        imagestring($image, 5, 60, 248, strtoupper((string) ($invoice->booking->client->name ?? '-')), $bold);

        // Tabel header
        $tX = 60;
        $tY = 300;
        $tW = 1120;
        $c1 = 460;
        $c2 = 160;
        $c3 = 250;
        $c4 = 250;
        $rH = 60;

        imagefilledrectangle($image, $tX, $tY, $tX + $tW, $tY + $rH, $headBg);
        imagerectangle($image, $tX, $tY, $tX + $tW, $tY + $rH, $border);
        imageline($image, $tX + $c1, $tY, $tX + $c1, $tY + $rH, $border);
        imageline($image, $tX + $c1 + $c2, $tY, $tX + $c1 + $c2, $tY + $rH, $border);
        imageline($image, $tX + $c1 + $c2 + $c3, $tY, $tX + $c1 + $c2 + $c3, $tY + $rH, $border);

        imagestring($image, 4, $tX + 12, $tY + 20, 'Item', $bold);
        imagestring($image, 4, $tX + $c1 + 12, $tY + 20, 'Qty', $bold);
        imagestring($image, 4, $tX + $c1 + $c2 + 12, $tY + 20, 'Harga', $bold);
        imagestring($image, 4, $tX + $c1 + $c2 + $c3 + 12, $tY + 20, 'Subtotal', $bold);

        // Baris item
        $rows = [];
        if ($invoice->booking->items->count() > 0) {
            foreach ($invoice->booking->items as $item) {
                $qty   = (float) ($item->quantity ?? 1);
                $price = (float) ($item->price ?? 0);
                $rows[] = ['name' => (string) ($item->service?->name ?? 'Layanan'), 'qty' => $qty, 'price' => $price, 'subtotal' => $qty * $price];
            }
        } else {
            $rows[] = ['name' => (string) ($invoice->booking->service->name ?? 'Layanan'), 'qty' => 1, 'price' => (float) $invoice->subtotal, 'subtotal' => (float) $invoice->subtotal];
        }

        $curY = $tY + $rH;
        foreach (array_slice($rows, 0, 8) as $row) {
            $nextY = $curY + $rH;
            imagerectangle($image, $tX, $curY, $tX + $tW, $nextY, $border);
            imageline($image, $tX + $c1, $curY, $tX + $c1, $nextY, $border);
            imageline($image, $tX + $c1 + $c2, $curY, $tX + $c1 + $c2, $nextY, $border);
            imageline($image, $tX + $c1 + $c2 + $c3, $curY, $tX + $c1 + $c2 + $c3, $nextY, $border);

            imagestring($image, 3, $tX + 12, $curY + 21, substr($row['name'], 0, 42), $dark);
            imagestring($image, 3, $tX + $c1 + 12, $curY + 21, rtrim(rtrim(number_format($row['qty'], 2, ',', '.'), '0'), ','), $dark);
            imagestring($image, 3, $tX + $c1 + $c2 + 12, $curY + 21, 'Rp ' . number_format($row['price'], 0, ',', '.'), $dark);
            imagestring($image, 3, $tX + $c1 + $c2 + $c3 + 12, $curY + 21, 'Rp ' . number_format($row['subtotal'], 0, ',', '.'), $dark);
            $curY = $nextY;
        }

        // Summary
        $sY = $curY + 30;
        imageline($image, 60, $sY, 1180, $sY, $border);
        $sY += 20;

        $summaryX = 700;
        imagestring($image, 3, $summaryX, $sY, 'Subtotal', $muted);
        imagestring($image, 3, 1000, $sY, 'Rp ' . number_format((float) $invoice->subtotal, 0, ',', '.'), $dark);

        if ($invoice->tax > 0) {
            $sY += 34;
            imagestring($image, 3, $summaryX, $sY, 'Pajak / Fee', $muted);
            imagestring($image, 3, 1000, $sY, 'Rp ' . number_format((float) $invoice->tax, 0, ',', '.'), $dark);
        }

        if ($invoice->booking->is_dp_paid && (float) $invoice->booking->dp_amount > 0) {
            $sY += 34;
            imagestring($image, 3, $summaryX, $sY, 'Total DP', $muted);
            imagestring($image, 3, 1000, $sY, 'Rp ' . number_format((float) $invoice->booking->dp_amount, 0, ',', '.'), $dark);
        }

        $sY += 44;
        imageline($image, $summaryX, $sY, 1180, $sY, $border);
        $sY += 14;
        imagestring($image, 5, $summaryX, $sY, 'Total Booking', $bold);
        imagestring($image, 5, 1000, $sY, 'Rp ' . number_format((float) $invoice->total, 0, ',', '.'), $bold);

        $remaining = $invoice->booking->is_dp_paid && (float) $invoice->booking->dp_amount > 0
            ? $invoice->total - $invoice->booking->dp_amount
            : (float) $invoice->total;
        $sY += 34;
        imagestring($image, 5, $summaryX, $sY, 'Kekurangan', $bold);
        imagestring($image, 5, 1000, $sY, 'Rp ' . number_format($remaining, 0, ',', '.'), $bold);

        // Payment box
        $pY = $sY + 70;
        imagerectangle($image, 60, $pY, 1180, $pY + 160, $border);
        imagestring($image, 4, 80, $pY + 20, '• Informasi Pembayaran', $bold);

        $notes = trim((string) ($invoice->booking->user->invoice_footer_notes ?? 'Info pembayaran belum diatur. Hubungi pihak MUA.'));
        $lines = explode("\n", wordwrap($notes, 80, "\n", true));
        $lY = $pY + 50;
        foreach (array_slice($lines, 0, 4) as $line) {
            imagestring($image, 3, 80, $lY, trim($line), $dark);
            $lY += 26;
        }

        // Catatan
        $nY = $pY + 180;
        imagestring($image, 3, 60, $nY, 'Catatan', $muted);
        imagestring($image, 3, 60, $nY + 26, '- fee transport menyesuaikan jarak', $dark);
        imagestring($image, 3, 60, $nY + 52, '- tnc akan dikirimkan bersama invoice', $dark);

        ob_start();
        imagejpeg($image, null, 90);
        $binary = ob_get_clean();
        imagedestroy($image);

        return $binary ?: null;
    }

    private function drawLogo($canvas, ?string $logoPath, int $x, int $y, int $maxW, int $maxH): void
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
        $ratio   = min($maxW / $w, $maxH / $h, 1);
        $targetW = (int) round($w * $ratio);
        $targetH = (int) round($h * $ratio);
        imagecopyresampled($canvas, $logo, $x, $y, 0, 0, $targetW, $targetH, $w, $h);
        imagedestroy($logo);
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
            Log::warning('Failed converting invoice PDF to JPG via Imagick.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        } finally {
            $imagick->clear();
            $imagick->destroy();
        }
    }
}
