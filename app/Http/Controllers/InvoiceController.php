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

    private function resolveLogoPath(Invoice $invoice): ?string
    {
        $path = $invoice->booking->user->invoice_logo_path ?? null;
        if (! $path) {
            return null;
        }

        $absolute = storage_path('app/public/' . ltrim($path, '/'));
        return is_file($absolute) ? $absolute : null;
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
        $renderer = new InvoiceRenderer();
        $convertedJpg = $renderer->getJpgBinary($invoice);

        if ($convertedJpg !== null) {
            return response($convertedJpg, 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.jpg"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        abort(500, 'Gagal menghasilkan JPEG invoice. Pastikan extension imagick aktif.');
    }
}
