<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceRenderer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class InvoiceController extends Controller
{
    private function getLogoBase64(Invoice $invoice): ?string
    {
        $path = $this->resolveLogoPath($invoice);
        if (! $path || ! is_file($path)) {
            return null;
        }

        try {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = match($ext) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                default => 'image/png',
            };
            $data = file_get_contents($path);
            if ($data === false) {
                return null;
            }
            return 'data:' . $mime . ';base64,' . base64_encode($data);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveLogoPath(Invoice $invoice): ?string
    {
        $path = $invoice->booking->user->invoice_logo_path ?? null;
        if (! $path) {
            return null;
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            $storagePath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
            if (is_file($storagePath)) {
                return $storagePath;
            }
        }

        $absolute = storage_path('app/public/' . ltrim($path, '/'));
        if (is_file($absolute)) {
            return $absolute;
        }

        $publicAbsolute = public_path('storage/' . ltrim($path, '/'));
        if (is_file($publicAbsolute)) {
            return $publicAbsolute;
        }

        return null;
    }

    private function getLogoForPdf(Invoice $invoice): ?string
    {
        $path = $this->resolveLogoPath($invoice);
        if (! $path || ! is_file($path)) {
            return null;
        }

        // Return base64 or absolute path. In DomPDF base64 data URI works seamlessly.
        return $this->getLogoBase64($invoice) ?: $path;
    }

    public function pdf(Invoice $invoice): Response
    {
        abort_unless(
            $invoice->booking->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        $invoice->loadMissing(['booking.client', 'booking.user', 'booking.items.service']);

        $logoPath = $this->getLogoForPdf($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => [base_path(), storage_path(), public_path()],
        ])
            ->loadView('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->setPaper('A4');

        $fileName = 'Invoice-' . $invoice->invoice_number . '.pdf';
        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => strlen($output),
            'Cache-Control' => 'private, max-age=3600, must-revalidate',
        ]);
    }

    public function download(Invoice $invoice, ?string $filename = null): Response
    {
        abort_unless(
            $invoice->booking->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        $invoice->loadMissing(['booking.client', 'booking.user', 'booking.items.service']);

        $logoPath = $this->getLogoForPdf($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => [base_path(), storage_path(), public_path()],
        ])
            ->loadView('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->setPaper('A4');

        $fileName = 'Invoice-' . $invoice->invoice_number . '.pdf';
        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => strlen($output),
            'Cache-Control' => 'private, max-age=3600, must-revalidate',
        ]);
    }

    public function previewHtml(Invoice $invoice): Response
    {
        abort_unless(
            $invoice->booking->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        $invoice->loadMissing(['booking.client', 'booking.user', 'booking.items.service']);

        $logoPath = $this->getLogoBase64($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        return response()
            ->view('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'private, max-age=60');
    }

    public function publicPreviewHtml(Request $request, Invoice $invoice): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        $invoice->loadMissing(['booking.client', 'booking.user', 'booking.items.service']);

        $logoPath = $this->getLogoBase64($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        return response()
            ->view('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'private, max-age=300');
    }

    public function previewJpg(Invoice $invoice): Response
    {
        abort_unless(
            $invoice->booking->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        return $this->jpgResponse($invoice);
    }

    public function publicPdf(Request $request, Invoice $invoice): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        $invoice->loadMissing(['booking.client', 'booking.user', 'booking.items.service']);

        $logoPath = $this->getLogoForPdf($invoice);
        $invoiceFooterNotes = $invoice->booking->user->invoice_footer_notes ?? null;

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => [base_path(), storage_path(), public_path()],
        ])
            ->loadView('invoices.pdf', compact('invoice', 'logoPath', 'invoiceFooterNotes'))
            ->setPaper('A4');

        $fileName = 'Invoice-' . $invoice->invoice_number . '.pdf';
        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => strlen($output),
            'Cache-Control' => 'private, max-age=3600, must-revalidate',
        ]);
    }

    public function publicJpg(Request $request, Invoice $invoice): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        return $this->jpgResponse($invoice);
    }

    private function jpgResponse(Invoice $invoice): Response
    {
        $renderer = new InvoiceRenderer();
        $convertedJpg = $renderer->getJpgBinary($invoice);

        if ($convertedJpg !== null) {
            return response($convertedJpg, 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => strlen($convertedJpg),
                'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.jpg"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }

        abort(500, 'Gagal menghasilkan JPEG invoice. Pastikan extension imagick aktif.');
    }
}
