<?php

namespace App\Services;

use App\Models\Pricelist;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PricelistRenderer
{
    public function getPdfBinary(Pricelist $pricelist): string
    {
        $pricelist->loadMissing(['user', 'sections.items']);
        $logoBase64 = $this->getLogoBase64($pricelist);

        return Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ])
            ->loadView('pricelists.pdf', compact('pricelist', 'logoBase64'))
            ->setPaper('A4')
            ->output();
    }

    public function getJpgBinary(Pricelist $pricelist): ?string
    {
        $pdfBinary = $this->getPdfBinary($pricelist);

        $convertedJpg = $this->convertPdfToJpgBinary($pdfBinary);
        if ($convertedJpg !== null) {
            return $convertedJpg;
        }

        // Fallback: render canvas menggunakan PHP GD dengan TrueType Anti-Aliasing
        return $this->renderWithGd($pricelist);
    }

    private function getLogoBase64(Pricelist $pricelist): ?string
    {
        $path = $pricelist->user->invoice_logo_path ?? null;
        if (!$path) {
            return null;
        }

        $absolute = storage_path('app/public/' . ltrim($path, '/'));
        if (!is_file($absolute)) {
            return null;
        }

        try {
            $mime = mime_content_type($absolute) ?: 'image/png';
            $data = file_get_contents($absolute);
            return 'data:' . $mime . ';base64,' . base64_encode($data);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function convertPdfToJpgBinary(string $pdfBinary): ?string
    {
        if (!extension_loaded('imagick') || !class_exists(\Imagick::class)) {
            return null;
        }

        try {
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImageBlob($pdfBinary);
            $imagick->setIteratorIndex(0);
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(90);

            $jpgBinary = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();

            return $jpgBinary;
        } catch (\Throwable $e) {
            Log::warning('PricelistRenderer: Imagick conversion failed: ' . $e->getMessage());
            return null;
        }
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return [236, 72, 153]; // default pink
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function renderWithGd(Pricelist $pricelist): ?string
    {
        $pricelist->loadMissing(['user', 'sections.items']);

        $width = 1240;
        $height = 1754;
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            return null;
        }

        // Enable Anti-Aliasing in GD
        if (function_exists('imageantialias')) {
            imageantialias($image, true);
        }

        // Fonts path (Using high-quality TTF fonts from vendor)
        $fontRegular = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $fontBold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $fontSerif = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSerif-Bold.ttf');

        $theme = $pricelist->theme_template ?? 'rose_blush';
        $primaryRgb = $this->hexToRgb($pricelist->primary_color ?: '#ec4899');

        // Exact Theme Palettes
        [$bgRgb, $cardRgb, $cardBorderRgb, $textRgb, $subtextRgb, $dividerRgb] = match($theme) {
            'luxury_gold' => [
                [18, 18, 18],     // bg (#121212)
                [28, 28, 28],     // card bg (#1c1c1c)
                [51, 51, 51],     // card border (#333333)
                [243, 244, 246],  // text
                [156, 163, 175],  // subtext
                [45, 45, 45],     // divider
            ],
            'clean_nude' => [
                [253, 250, 247],  // bg (#fdfaf7)
                [255, 255, 255],  // card bg
                [243, 232, 214],  // card border (#f3e8d6)
                [41, 37, 36],     // text
                [120, 113, 108],  // subtext
                [243, 232, 214],  // divider
            ],
            'sage_botanical' => [
                [244, 249, 245],  // bg (#f4f9f5)
                [255, 255, 255],  // card bg
                [209, 250, 229],  // card border (#d1fae5)
                [6, 78, 59],      // text
                [4, 120, 87],     // subtext
                [209, 250, 229],  // divider
            ],
            default => [
                [255, 255, 255],  // bg
                [255, 255, 255],  // card bg
                [252, 231, 243],  // card border (#fce7f3)
                [17, 24, 39],     // text
                [107, 114, 128],  // subtext
                [243, 244, 246],  // divider
            ],
        };

        // Allocate GD Colors
        $cBg         = imagecolorallocate($image, $bgRgb[0], $bgRgb[1], $bgRgb[2]);
        $cCard       = imagecolorallocate($image, $cardRgb[0], $cardRgb[1], $cardRgb[2]);
        $cCardBorder = imagecolorallocate($image, $cardBorderRgb[0], $cardBorderRgb[1], $cardBorderRgb[2]);
        $cText       = imagecolorallocate($image, $textRgb[0], $textRgb[1], $textRgb[2]);
        $cSubtext    = imagecolorallocate($image, $subtextRgb[0], $subtextRgb[1], $subtextRgb[2]);
        $cDivider    = imagecolorallocate($image, $dividerRgb[0], $dividerRgb[1], $dividerRgb[2]);
        $cPrimary    = imagecolorallocate($image, $primaryRgb[0], $primaryRgb[1], $primaryRgb[2]);
        $cWhite      = imagecolorallocate($image, 255, 255, 255);

        // Fill full-bleed canvas
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $cBg);

        $currentY = 55;

        // 1. Studio Logo
        if ($pricelist->show_logo && $pricelist->user->invoice_logo_path) {
            $logoPath = storage_path('app/public/' . ltrim($pricelist->user->invoice_logo_path, '/'));
            if (is_file($logoPath)) {
                $this->drawLogoCentered($image, $logoPath, $width, $currentY, 200, 80);
                $currentY += 95;
            }
        }

        // 2. Studio Name (Serif Elegant Heading)
        $studioName = $pricelist->user->studio_name ?: $pricelist->user->name;
        $this->drawTtfCentered($image, 24, $width, $currentY, $cPrimary, $fontSerif, $studioName);
        $currentY += 36;

        // 3. Document Title
        $docTitle = strtoupper($pricelist->title ?: 'PRICELIST LAYANAN');
        $this->drawTtfCentered($image, 13, $width, $currentY, $cText, $fontBold, $docTitle);
        $currentY += 26;

        // 4. Tagline / Description
        if ($pricelist->description) {
            $desc = '"' . $pricelist->description . '"';
            $this->drawTtfCentered($image, 11, $width, $currentY, $cSubtext, $fontRegular, $desc);
            $currentY += 24;
        }

        // 5. Social Media Bar
        if ($pricelist->show_social_media && ($pricelist->user->instagram || $pricelist->user->tiktok)) {
            $socialParts = [];
            if ($pricelist->user->instagram) $socialParts[] = 'Instagram: @' . ltrim($pricelist->user->instagram, '@');
            if ($pricelist->user->tiktok) $socialParts[] = 'TikTok: @' . ltrim($pricelist->user->tiktok, '@');
            $socialStr = implode('   |   ', $socialParts);
            $this->drawTtfCentered($image, 10, $width, $currentY, $cSubtext, $fontRegular, $socialStr);
            $currentY += 24;
        }

        // Header Divider Line
        $currentY += 12;
        imageline($image, 80, $currentY, $width - 80, $currentY, $cDivider);
        $currentY += 40;

        // 6. Sections & Packages
        $margin = 75;
        $contentWidth = $width - ($margin * 2);

        foreach ($pricelist->sections as $section) {
            if ($currentY > $height - 280) break;

            // Section Header with Decorative Lines
            $secTitle = strtoupper($section->name);
            $bbox = imagettfbbox(14, 0, $fontSerif, $secTitle);
            $secTextWidth = abs($bbox[4] - $bbox[0]);
            $textStartX = (int) (($width - $secTextWidth) / 2);

            imageline($image, $margin, $currentY - 5, $textStartX - 25, $currentY - 5, $cPrimary);
            $this->drawTtfCentered($image, 14, $width, $currentY, $cPrimary, $fontSerif, $secTitle);
            imageline($image, $textStartX + $secTextWidth + 25, $currentY - 5, $width - $margin, $currentY - 5, $cPrimary);
            $currentY += 28;

            if ($section->description) {
                $this->drawTtfCentered($image, 10, $width, $currentY, $cSubtext, $fontRegular, $section->description);
                $currentY += 24;
            }

            $currentY += 12;

            // Packages Cards in this Section
            foreach ($section->items as $item) {
                if ($currentY > $height - 200) break;

                $cardHeight = 120;
                $featureCount = !empty($item->features) && is_array($item->features) ? min(3, count($item->features)) : 0;
                if ($featureCount > 0) {
                    $cardHeight += 20 + ($featureCount * 24);
                }

                // Draw Card Box
                imagefilledrectangle($image, $margin, $currentY, $width - $margin, $currentY + $cardHeight, $cCard);
                imagerectangle($image, $margin, $currentY, $width - $margin, $currentY + $cardHeight, $item->is_highlighted ? $cPrimary : $cCardBorder);
                if ($item->is_highlighted) {
                    imagerectangle($image, $margin + 1, $currentY + 1, $width - $margin - 1, $currentY + $cardHeight - 1, $cPrimary);
                }

                // Item Name (Left)
                $itemY = $currentY + 34;
                imagettftext($image, 13, 0, $margin + 24, $itemY, $cText, $fontBold, (string) $item->name);

                // Best Seller Badge
                if ($item->is_highlighted) {
                    $nameBbox = imagettfbbox(13, 0, $fontBold, (string) $item->name);
                    $nameW = abs($nameBbox[4] - $nameBbox[0]);
                    $badgeX = $margin + 36 + $nameW;
                    $badgeText = '★ BEST SELLER';
                    $badgeBbox = imagettfbbox(9, 0, $fontBold, $badgeText);
                    $badgeW = abs($badgeBbox[4] - $badgeBbox[0]) + 16;
                    
                    imagefilledrectangle($image, $badgeX, $itemY - 18, $badgeX + $badgeW, $itemY + 4, $cPrimary);
                    imagettftext($image, 9, 0, $badgeX + 8, $itemY - 4, $cWhite, $fontBold, $badgeText);
                }

                // Price (Right Aligned)
                $priceStr = $item->formatted_price;
                $this->drawTtfRight($image, 14, $width - $margin - 24, $itemY, $cPrimary, $fontBold, $priceStr);

                $itemY += 24;

                // Duration Text
                if ($item->duration_text) {
                    imagettftext($image, 9.5, 0, $margin + 24, $itemY, $cSubtext, $fontRegular, 'Durasi: ' . $item->duration_text);
                    $itemY += 22;
                }

                // Description
                if ($item->description) {
                    imagettftext($image, 10, 0, $margin + 24, $itemY, $cSubtext, $fontRegular, $item->description);
                    $itemY += 24;
                }

                // Benefit Checklist (with true UTF-8 checkmark)
                if ($featureCount > 0) {
                    $itemY += 2;
                    imageline($image, $margin + 24, $itemY - 8, $width - $margin - 24, $itemY - 8, $cDivider);
                    $itemY += 12;

                    foreach (array_slice($item->features, 0, 3) as $feat) {
                        imagettftext($image, 11, 0, $margin + 24, $itemY, $cPrimary, $fontBold, '✓');
                        imagettftext($image, 10, 0, $margin + 46, $itemY, $cText, $fontRegular, (string) $feat);
                        $itemY += 24;
                    }
                }

                $currentY += $cardHeight + 16;
            }

            $currentY += 20;
        }

        // 7. Terms & Conditions
        if (!empty($pricelist->terms_conditions) && count($pricelist->terms_conditions) > 0 && $currentY < $height - 190) {
            imageline($image, 80, $currentY, $width - 80, $currentY, $cDivider);
            $currentY += 24;

            $this->drawTtfCentered($image, 12, $width, $currentY, $cPrimary, $fontSerif, 'SYARAT & KETENTUAN (TERMS & CONDITIONS)');
            $currentY += 28;

            $termIdx = 1;
            foreach (array_slice($pricelist->terms_conditions, 0, 4) as $term) {
                if ($currentY > $height - 110) break;
                $termLine = $termIdx . '. ' . $term;
                imagettftext($image, 9.5, 0, $margin + 24, $currentY, $cSubtext, $fontRegular, $termLine);
                $currentY += 20;
                $termIdx++;
            }
            $currentY += 14;
        }

        // 8. Footer Notes & WhatsApp CTA
        if ($currentY < $height - 90) {
            imageline($image, 80, $currentY, $width - 80, $currentY, $cDivider);
            $currentY += 22;

            if ($pricelist->custom_footer_notes) {
                $this->drawTtfCentered($image, 10, $width, $currentY, $cSubtext, $fontRegular, $pricelist->custom_footer_notes);
                $currentY += 26;
            }

            if ($pricelist->show_contact_button && $pricelist->user->phone) {
                $ctaStr = 'Booking via WhatsApp: ' . $pricelist->user->phone;
                $ctaBbox = imagettfbbox(11, 0, $fontBold, $ctaStr);
                $ctaW = abs($ctaBbox[4] - $ctaBbox[0]) + 44;
                $ctaX = (int) (($width - $ctaW) / 2);

                imagefilledrectangle($image, $ctaX, $currentY - 6, $ctaX + $ctaW, $currentY + 28, $cPrimary);
                imagettftext($image, 11, 0, $ctaX + 22, $currentY + 16, $cWhite, $fontBold, $ctaStr);
            }
        }

        ob_start();
        imagejpeg($image, null, 95);
        $output = ob_get_clean();
        imagedestroy($image);

        return $output ?: null;
    }

    private function drawTtfCentered($image, float $size, int $canvasWidth, int $y, int $color, string $font, string $text): void
    {
        $bbox = imagettfbbox($size, 0, $font, $text);
        $textWidth = abs($bbox[4] - $bbox[0]);
        $x = (int) (($canvasWidth - $textWidth) / 2);
        imagettftext($image, $size, 0, max(10, $x), $y, $color, $font, $text);
    }

    private function drawTtfRight($image, float $size, int $rightX, int $y, int $color, string $font, string $text): void
    {
        $bbox = imagettfbbox($size, 0, $font, $text);
        $textWidth = abs($bbox[4] - $bbox[0]);
        imagettftext($image, $size, 0, $rightX - $textWidth, $y, $color, $font, $text);
    }

    private function drawLogoCentered($image, string $path, int $canvasWidth, int $y, int $maxW, int $maxH): void
    {
        $info = @getimagesize($path);
        if (!$info) return;

        [$origW, $origH, $type] = $info;
        if ($origW <= 0 || $origH <= 0) return;

        $src = match($type) {
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => null,
        };

        if (!$src) return;

        $scale = min($maxW / $origW, $maxH / $origH, 1.0);
        $dstW = (int) ($origW * $scale);
        $dstH = (int) ($origH * $scale);
        $dstX = (int) (($canvasWidth - $dstW) / 2);

        imagecopyresampled($image, $src, $dstX, $y, 0, 0, $dstW, $dstH, $origW, $origH);
        imagedestroy($src);
    }
}
