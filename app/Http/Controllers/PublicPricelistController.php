<?php

namespace App\Http\Controllers;

use App\Models\Pricelist;
use App\Services\PricelistRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicPricelistController extends Controller
{
    public function show(string $slug)
    {
        $pricelist = Pricelist::with(['user', 'sections.items'])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        return view('pricelists.public', compact('pricelist'));
    }

    public function pdf(string $slug, PricelistRenderer $renderer)
    {
        $pricelist = Pricelist::with(['user', 'sections.items'])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        $pdfBinary = $renderer->getPdfBinary($pricelist);
        $filename = 'Pricelist-' . str_replace(' ', '_', $pricelist->title) . '.pdf';

        return response($pdfBinary, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function jpg(string $slug, PricelistRenderer $renderer)
    {
        $pricelist = Pricelist::with(['user', 'sections.items'])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        $jpgBinary = $renderer->getJpgBinary($pricelist);
        if (!$jpgBinary) {
            abort(500, 'Gagal menghasilkan gambar pricelist.');
        }

        return response($jpgBinary, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
