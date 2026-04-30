<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function pdf(Invoice $invoice): Response
    {
        abort_unless(
            $invoice->booking->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('A4');

        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}
