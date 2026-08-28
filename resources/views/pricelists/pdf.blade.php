<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $pricelist->title }} - {{ $pricelist->user->studio_name ?: $pricelist->user->name }}</title>
    <style>
        @page {
            margin: 0px;
            size: A4 portrait;
        }

        @php
            $primary = $pricelist->primary_color ?: '#ec4899';
            $theme = $pricelist->theme_template ?: 'rose_blush';

            // Theme-specific styles matching the builder preview
            $bodyBg = match($theme) {
                'luxury_gold'    => '#121212',
                'clean_nude'      => '#fdfaf7',
                'sage_botanical' => '#f4f9f5',
                default          => '#ffffff',
            };

            $bodyTextColor = match($theme) {
                'luxury_gold'    => '#f3f4f6',
                'clean_nude'      => '#292524',
                'sage_botanical' => '#064e3b',
                default          => '#111827',
            };

            $cardBg = match($theme) {
                'luxury_gold'    => '#1c1c1c',
                'clean_nude'      => '#ffffff',
                'sage_botanical' => '#ffffff',
                default          => '#ffffff',
            };

            $cardBorder = match($theme) {
                'luxury_gold'    => '#333333',
                'clean_nude'      => '#f3e8d6',
                'sage_botanical' => '#d1fae5',
                default          => '#fce7f3',
            };

            $subtextCol = match($theme) {
                'luxury_gold'    => '#9ca3af',
                'clean_nude'      => '#78716c',
                'sage_botanical' => '#047857',
                default          => '#6b7280',
            };

            $dividerCol = match($theme) {
                'luxury_gold'    => '#2d2d2d',
                'clean_nude'      => '#f3e8d6',
                'sage_botanical' => '#d1fae5',
                default          => '#f3f4f6',
            };
        @endphp

        html, body {
            margin: 0;
            padding: 0;
            background-color: {{ $bodyBg }};
            color: {{ $bodyTextColor }};
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            line-height: 1.4;
            width: 100%;
        }

        .page-wrapper {
            padding: 24px 30px;
            background-color: {{ $bodyBg }};
            box-sizing: border-box;
        }

        /* Top Header Branding */
        .header-container {
            text-align: center;
            padding-bottom: 16px;
            border-bottom: 1.5px solid {{ $dividerCol }};
            margin-bottom: 20px;
        }

        .studio-logo {
            max-height: 55px;
            max-width: 140px;
            margin-bottom: 6px;
        }

        .studio-name {
            font-family: 'Georgia', 'DejaVu Serif', serif;
            font-size: 20px;
            font-weight: bold;
            color: {{ $primary }};
            letter-spacing: 0.5px;
            margin: 0;
        }

        .doc-title {
            font-size: 12px;
            font-weight: bold;
            color: {{ $bodyTextColor }};
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 4px 0 0 0;
        }

        .doc-desc {
            font-size: 9.5px;
            color: {{ $subtextCol }};
            font-style: italic;
            margin: 5px auto 0 auto;
            max-width: 85%;
            line-height: 1.35;
        }

        .social-bar {
            margin-top: 8px;
            font-size: 9px;
            color: {{ $subtextCol }};
        }

        .social-item {
            display: inline-block;
            margin: 0 10px;
        }

        /* Section Container & Section Titles */
        .section-container {
            margin-bottom: 22px;
        }

        .section-header-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .section-line {
            height: 1px;
            background-color: {{ $primary }};
        }

        .section-title-text {
            font-family: 'Georgia', 'DejaVu Serif', serif;
            font-size: 13px;
            font-weight: bold;
            color: {{ $primary }};
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 0 12px;
            white-space: nowrap;
            text-align: center;
        }

        .section-desc {
            font-size: 9px;
            color: {{ $subtextCol }};
            text-align: center;
            margin: 2px 0 10px 0;
        }

        /* Package Card */
        .package-card {
            background-color: {{ $cardBg }};
            border: 1px solid {{ $cardBorder }};
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 10px;
            page-break-inside: avoid;
            position: relative;
        }

        .package-card-highlighted {
            border: 1.5px solid {{ $primary }};
        }

        .highlight-badge {
            background-color: {{ $primary }};
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            padding: 2px 7px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            margin-left: 6px;
            vertical-align: middle;
        }

        .package-title-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .package-name {
            font-size: 12px;
            font-weight: bold;
            color: {{ $bodyTextColor }};
        }

        .package-duration {
            font-size: 8.5px;
            color: {{ $subtextCol }};
            margin-top: 2px;
        }

        .package-price {
            font-size: 13px;
            font-weight: bold;
            color: {{ $primary }};
            text-align: right;
            white-space: nowrap;
            vertical-align: top;
        }

        .package-desc {
            font-size: 9px;
            color: {{ $subtextCol }};
            margin: 2px 0 6px 0;
            line-height: 1.35;
        }

        .benefit-divider {
            border-top: 1px solid {{ $dividerCol }};
            margin-top: 6px;
            padding-top: 6px;
        }

        .benefit-item {
            font-size: 9px;
            color: {{ $bodyTextColor }};
            margin-bottom: 2.5px;
            line-height: 1.3;
        }

        .check-icon {
            color: {{ $primary }};
            font-weight: bold;
            font-size: 10px;
            margin-right: 4px;
        }

        /* Terms & Conditions Section */
        .terms-container {
            margin-top: 20px;
            padding-top: 14px;
            border-top: 1.5px solid {{ $dividerCol }};
            page-break-inside: avoid;
        }

        .terms-title {
            font-family: 'Georgia', 'DejaVu Serif', serif;
            font-size: 11px;
            font-weight: bold;
            color: {{ $primary }};
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            margin-bottom: 8px;
        }

        .terms-list {
            margin: 0;
            padding-left: 18px;
            font-size: 8.5px;
            color: {{ $subtextCol }};
            line-height: 1.45;
        }

        .terms-list li {
            margin-bottom: 3px;
        }

        /* Footer & Contact CTA */
        .footer-container {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1.5px solid {{ $dividerCol }};
            text-align: center;
            page-break-inside: avoid;
        }

        .footer-note {
            font-size: 9px;
            color: {{ $subtextCol }};
            line-height: 1.4;
            margin-bottom: 8px;
        }

        .footer-cta-box {
            display: inline-block;
            background-color: {{ $primary }};
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            padding: 6px 18px;
            border-radius: 6px;
            text-align: center;
            margin-top: 4px;
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    {{-- 1. Header Studio Branding --}}
    <div class="header-container">
        @if($pricelist->show_logo && !empty($logoBase64))
            <div>
                <img src="{{ $logoBase64 }}" alt="Logo Studio" class="studio-logo">
            </div>
        @endif

        <h1 class="studio-name">{{ $pricelist->user->studio_name ?: $pricelist->user->name }}</h1>
        <div class="doc-title">{{ $pricelist->title ?: 'Pricelist Layanan' }}</div>

        @if($pricelist->description)
            <div class="doc-desc">"{{ $pricelist->description }}"</div>
        @endif

        @if($pricelist->show_social_media && ($pricelist->user->instagram || $pricelist->user->tiktok))
            <div class="social-bar">
                @if($pricelist->user->instagram)
                    <span class="social-item">Instagram: @ {{ ltrim($pricelist->user->instagram, '@') }}</span>
                @endif
                @if($pricelist->user->tiktok)
                    <span class="social-item">TikTok: @ {{ ltrim($pricelist->user->tiktok, '@') }}</span>
                @endif
            </div>
        @endif
    </div>

    {{-- 2. Sections & Packages --}}
    @foreach($pricelist->sections as $section)
        <div class="section-container">
            {{-- Section Title with Decorative Lines --}}
            <table class="section-header-table">
                <tr>
                    <td style="width: 25%;"><div class="section-line"></div></td>
                    <td class="section-title-text">{{ $section->name }}</td>
                    <td style="width: 25%;"><div class="section-line"></div></td>
                </tr>
            </table>

            @if($section->description)
                <div class="section-desc">{{ $section->description }}</div>
            @endif

            {{-- Packages List --}}
            @foreach($section->items as $item)
                <div class="package-card {{ $item->is_highlighted ? 'package-card-highlighted' : '' }}">
                    <table class="package-title-table">
                        <tr>
                            <td style="vertical-align: top;">
                                <span class="package-name">{{ $item->name }}</span>
                                @if($item->is_highlighted)
                                    <span class="highlight-badge">&#9733; Best Seller</span>
                                @endif
                                @if($item->duration_text)
                                    <div class="package-duration">Durasi: {{ $item->duration_text }}</div>
                                @endif
                            </td>
                            <td class="package-price">
                                {{ $item->formatted_price }}
                            </td>
                        </tr>
                    </table>

                    @if($item->description)
                        <div class="package-desc">{{ $item->description }}</div>
                    @endif

                    @if(!empty($item->features) && count($item->features) > 0)
                        <div class="benefit-divider">
                            @foreach($item->features as $feature)
                                <div class="benefit-item">
                                    <span class="check-icon">&#10003;</span> {{ $feature }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach

    {{-- 3. Terms & Conditions --}}
    @if(!empty($pricelist->terms_conditions) && count($pricelist->terms_conditions) > 0)
        <div class="terms-container">
            <div class="terms-title">Syarat & Ketentuan (Terms & Conditions)</div>
            <ol class="terms-list">
                @foreach($pricelist->terms_conditions as $term)
                    <li>{{ $term }}</li>
                @endforeach
            </ol>
        </div>
    @endif

    {{-- 4. Footer & Contact Info --}}
    <div class="footer-container">
        @if($pricelist->custom_footer_notes)
            <div class="footer-note">{{ $pricelist->custom_footer_notes }}</div>
        @endif

        @if($pricelist->show_contact_button && $pricelist->user->phone)
            <div class="footer-cta-box">
                Booking via WhatsApp ({{ $pricelist->user->phone }})
            </div>
        @endif
    </div>

</div>
</body>
</html>
