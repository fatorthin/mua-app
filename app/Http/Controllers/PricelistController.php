<?php

namespace App\Http\Controllers;

use App\Models\Pricelist;
use App\Services\PricelistRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PricelistController extends Controller
{
    public function pdf(Pricelist $pricelist, PricelistRenderer $renderer)
    {
        abort_unless($pricelist->user_id === auth()->id(), 403);

        $pdfBinary = $renderer->getPdfBinary($pricelist);
        $filename = 'Pricelist-' . str_replace(' ', '_', $pricelist->title) . '.pdf';

        return response($pdfBinary, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function jpg(Pricelist $pricelist, PricelistRenderer $renderer)
    {
        abort_unless($pricelist->user_id === auth()->id(), 403);

        $jpgBinary = $renderer->getJpgBinary($pricelist);
        if (!$jpgBinary) {
            abort(500, 'Gagal menghasilkan gambar pricelist.');
        }

        $filename = 'Pricelist-' . str_replace(' ', '_', $pricelist->title) . '.jpg';

        return response($jpgBinary, 200, [
            'Content-Type'        => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
