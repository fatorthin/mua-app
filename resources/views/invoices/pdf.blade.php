<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap"
        rel="stylesheet">
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', Arial, sans-serif;
            color: #2f2a27;
            font-size: 13px;
            line-height: 1.4;
        }

        .sheet {
            background: #fbf9f7;
            width: 100%;
            height: 100%;
        }

        .top-strip,
        .bottom-strip {
            height: 20px;
            background: #e9ddd6;
        }

        .content {
            padding: 24px 40px 20px;
        }

        .hero {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .hero td {
            vertical-align: top;
        }

        .brand {
            width: 48%;
        }

        .brand-logo {
            display: inline-block;
            max-height: 62px;
            max-width: 185px;
            width: auto;
            margin-bottom: 8px;
        }

        .brand-name {
            margin: 0;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 30px;
            font-weight: 600;
            letter-spacing: 0.03em;
            color: #2d2118;
        }

        .brand-sub {
            margin: 4px 0 0;
            font-family: 'Montserrat', Arial, sans-serif;
            font-weight: 300;
            color: #7d6e64;
            font-size: 11px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .title-wrap {
            text-align: right;
            width: 52%;
            padding-top: 2px;
        }

        .title {
            margin: 0;
            color: #c4826b;
            font-family: 'Alex Brush', "Brush Script MT", "Segoe Script", cursive;
            font-size: 90px;
            font-weight: 400;
            letter-spacing: 0.01em;
            line-height: 0.88;
        }

        .line {
            border-bottom: 2px solid #dba894;
            margin: 10px 0 20px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .meta td {
            vertical-align: top;
        }

        .meta .left {
            width: 62%;
        }

        .label {
            color: #615249;
            margin: 0 0 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .client-name {
            margin: 0;
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 600;
            font-size: 30px;
            letter-spacing: 0.04em;
            color: #21170f;
            text-transform: uppercase;
        }

        .meta-right p {
            margin: 0 0 8px;
            text-align: right;
            font-size: 16px;
            color: #271b14;
            font-weight: 500;
            font-family: 'Playfair Display', Georgia, serif;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .items th,
        .items td {
            border: 1.4px solid #d9b8ab;
            padding: 8px 10px;
            font-size: 12px;
        }

        .items th {
            background: #efe4de;
            text-transform: uppercase;
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 600;
            color: #3a2e27;
            letter-spacing: 0.05em;
            font-size: 13px;
            text-align: center;
            border-bottom-width: 1.8px;
        }

        .items td:nth-child(1) {
            width: 43%;
        }

        .items td:nth-child(2),
        .items td:nth-child(3),
        .items td:nth-child(4) {
            text-align: right;
            width: 19%;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .summary td {
            vertical-align: top;
        }

        .payment {
            width: 47%;
            padding-right: 20px;
        }

        .payment-title {
            margin: 0 0 8px;
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 600;
            font-size: 20px;
            text-transform: uppercase;
            color: #2a2019;
            letter-spacing: 0.03em;
        }

        .payment-box {
            white-space: pre-line;
            color: #2f2722;
            font-size: 14px;
            line-height: 1.6;
        }

        .totals {
            width: 53%;
        }

        .totals-wrap {
            width: 82%;
            margin-left: auto;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 3px 0;
            font-size: 12px;
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: 700;
            width: 42%;
        }

        .total-box {
            margin-top: 12px;
            background: #e9ddd6;
            padding: 14px 16px;
            border-radius: 0;
            font-weight: 700;
        }

        .total-box td {
            font-size: 20px;
        }

        .total-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-box td:last-child {
            text-align: right;
        }

        .footer {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            color: #241a14;
            page-break-inside: avoid;
        }

        .footer td {
            width: 50%;
            vertical-align: top;
        }

        .footer-title {
            margin: 0 0 10px;
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 600;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .signature {
            text-align: right;
        }

        .signature-name {
            margin: 12px 0 0;
            font-family: 'Alex Brush', "Brush Script MT", cursive;
            font-size: 48px;
            font-weight: 400;
            color: #1f1610;
            line-height: 1;
        }

        .status-tag {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 10px;
            border: 1px solid #ccb6aa;
            color: #6a5448;
            font-size: 10px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    @php
        $booking = $invoice->booking;
        $studioName = $booking->user->studio_name ?? 'MUA Studio';
        $rows = [];
        if ($booking->relationLoaded('items') && $booking->items->count() > 0) {
            foreach ($booking->items as $item) {
                $qty = (float) ($item->qty ?? 1);
                $unitPrice = (float) ($item->price ?? 0);
                $rows[] = [
                    'name' => $item->service?->name ?? 'Layanan',
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $qty * $unitPrice,
                ];
            }
        } else {
            $rows[] = [
                'name' => $booking->service->name ?? 'Layanan',
                'qty' => 1,
                'unit_price' => (float) $invoice->subtotal,
                'line_total' => (float) $invoice->subtotal,
            ];
        }
    @endphp

    <div class="sheet">
        <div class="top-strip"></div>

        <div class="content">
            <table class="hero">
                <tr>
                    <td class="brand">
                        @if (!empty($logoPath))
                            <img src="{{ $logoPath }}" alt="Logo" class="brand-logo">
                        @endif
                        <h2 class="brand-name">{{ strtoupper($studioName) }}</h2>
                        <p class="brand-sub">Invoice layanan profesional</p>
                    </td>
                    <td class="title-wrap">
                        <h1 class="title">Invoice</h1>
                    </td>
                </tr>
            </table>

            <div class="line"></div>

            <table class="meta">
                <tr>
                    <td class="left">
                        <p class="label">Kepada:</p>
                        <h3 class="client-name">{{ $booking->client->name }}</h3>
                        <span class="status-tag">{{ $invoice->status === 'paid' ? 'Lunas' : 'Belum Dibayar' }}</span>
                    </td>
                    <td class="meta-right">
                        <p>Nomor: {{ $invoice->invoice_number }}</p>
                        <p>Tanggal: {{ $invoice->created_at->format('d M Y') }}</p>
                        <p>Jatuh Tempo: {{ $invoice->due_date?->format('d M Y') ?? '-' }}</p>
                    </td>
                </tr>
            </table>

            <table class="items">
                <thead>
                    <tr>
                        <th>Nama Layanan</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ rtrim(rtrim(number_format($row['qty'], 2, ',', '.'), '0'), ',') }} paket</td>
                            <td>Rp {{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row['line_total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="summary">
                <tr>
                    <td class="payment">
                        <h4 class="payment-title">Metode Pembayaran:</h4>
                        <div class="payment-box">
                            {{ trim($invoiceFooterNotes ?: 'Silakan hubungi admin untuk detail pembayaran.') }}</div>
                        @if (!empty($invoice->notes))
                            <div style="margin-top: 10px; color:#6c5a50;">Catatan: {{ $invoice->notes }}</div>
                        @endif
                    </td>
                    <td class="totals">
                        <div class="totals-wrap">
                            <table class="totals-table">
                                <tr>
                                    <td>Subtotal:</td>
                                    <td>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @if ($booking->is_dp_paid && (float) $booking->dp_amount > 0)
                                    <tr>
                                        <td>DP Dibayar:</td>
                                        <td>- Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>Pajak
                                        ({{ number_format((float) ($invoice->tax > 0 && $invoice->subtotal > 0 ? ($invoice->tax / $invoice->subtotal) * 100 : 0), 0) }}%):
                                    </td>
                                    <td>Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                                </tr>
                            </table>

                            <div class="total-box">
                                <table>
                                    <tr>
                                        <td>TOTAL:</td>
                                        <td>Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="footer">
                <tr>
                    <td>
                        <h5 class="footer-title">Konfirmasi Hubungi:</h5>
                        <div>{{ $booking->user->phone ?? '-' }}</div>
                        <div>{{ $booking->user->email ?? '-' }}</div>
                    </td>
                    <td class="signature">
                        <h5 class="footer-title">Terimakasih</h5>
                        <p class="signature-name">{{ $booking->user->name }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="bottom-strip"></div>
    </div>
</body>

</html>
