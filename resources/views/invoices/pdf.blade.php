<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 40px;
            size: A4 portrait;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #111;
            font-size: 13px;
            line-height: 1.5;
        }

        .container {
            background-color: #fff;
            width: 100%;
        }

        .header {
            width: 100%;
            margin-bottom: 5px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-section {
            width: 50%;
            vertical-align: top;
        }

        .invoice-section {
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .brand-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .brand-details {
            font-size: 13px;
            color: #333;
            line-height: 1.6;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .badge {
            display: inline-block;
            background-color: #d99c9c;
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .invoice-info {
            font-size: 12px;
            color: #555;
        }

        .separator {
            border-top: 1px solid #d99c9c;
            margin: 15px 0;
        }

        .untuk {
            color: #888;
            font-size: 12px;
            margin-bottom: 2px;
        }

        .client-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .table-items {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d99c9c;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .table-items th {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #d99c9c;
            font-weight: bold;
            font-size: 13px;
        }

        .table-items td {
            padding: 12px;
            font-size: 13px;
        }

        .summary-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }

        .summary-table td {
            padding: 6px 0;
        }

        .summary-table .value {
            text-align: right;
            font-weight: bold;
        }

        .summary-label {
            color: #666;
        }

        .summary-total {
            font-weight: bold;
            font-size: 14px;
        }

        .summary-total td {
            padding-top: 10px;
        }

        .summary-table .divider td {
            border-bottom: 1px solid #d99c9c;
            padding-bottom: 10px;
        }

        .payment-box {
            border: 1px solid #d99c9c;
            border-radius: 6px;
            padding: 15px;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 20px;
        }

        .payment-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
            font-size: 13px;
        }

        .payment-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background-color: #d99c9c;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }

        .payment-info {
            font-size: 13px;
            line-height: 1.6;
        }

        .notes-title {
            color: #888;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .notes-content {
            font-size: 12px;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <table>
                <tr>
                    <td class="brand-section">
                        @if (!empty($logoPath))
                            <img src="{{ $logoPath }}" style="max-height: 45px; margin-bottom: 12px;">
                        @endif
                        <div class="brand-name">{{ $invoice->booking->user->studio_name ?? 'MUA STUDIO' }}</div>
                        <div class="brand-details">
                            @if (!empty($invoice->booking->user->instagram))
                                <span>IG : {{ $invoice->booking->user->instagram }}</span> &nbsp;&bull;&nbsp;
                            @endif
                            @if (!empty($invoice->booking->user->tiktok))
                                <span>TikTok : {{ $invoice->booking->user->tiktok }}</span> &nbsp;&bull;&nbsp;
                            @endif
                            @if (!empty($invoice->booking->user->phone))
                                <span>WA : {{ $invoice->booking->user->phone }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="invoice-section">
                        <div class="invoice-title">INVOICE</div>
                        <div class="badge">Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}</div>
                        <div class="invoice-info">
                            <div>{{ $invoice->invoice_number }}</div>
                            <div>Terbit: {{ $invoice->created_at->format('d/m/Y') }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="separator"></div>

        <div class="untuk">Untuk</div>
        <div class="client-name">{{ $invoice->booking->client->name ?? '-' }}</div>

        <table class="table-items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="width: 15%; text-align: center;">Qty</th>
                    <th style="width: 25%; text-align: right;">Harga</th>
                    <th style="width: 25%; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @if ($invoice->booking->items->count() > 0)
                    @foreach ($invoice->booking->items as $item)
                        <tr>
                            <td>{{ $item->service->name ?? 'Layanan' }}</td>
                            <td style="text-align: center;">
                                {{ rtrim(rtrim(number_format((float) ($item->quantity ?? 1), 2, ',', '.'), '0'), ',') }}
                            </td>
                            <td style="text-align: right;">Rp
                                {{ number_format((float) ($item->price ?? 0), 0, ',', '.') }}</td>
                            <td style="text-align: right;">Rp
                                {{ number_format((float) ($item->quantity * $item->price), 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $invoice->booking->service->name ?? 'Layanan' }}</td>
                        <td style="text-align: center;">1</td>
                        <td style="text-align: right;">Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right;">Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td class="summary-label">Subtotal</td>
                <td class="value" style="font-weight: normal;">Rp
                    {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if ($invoice->tax > 0)
                <tr>
                    <td class="summary-label">Pajak / Fee</td>
                    <td class="value" style="font-weight: normal;">Rp
                        {{ number_format((float) $invoice->tax, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if ($invoice->booking->is_dp_paid && floatval($invoice->booking->dp_amount) > 0)
                <tr class="divider">
                    <td class="summary-label">Total DP</td>
                    <td class="value" style="font-weight: normal;">Rp
                        {{ number_format((float) $invoice->booking->dp_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="summary-total">
                    <td>Total Booking</td>
                    <td class="value">Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}</td>
                </tr>
                <tr class="summary-total" style="padding-top: 4px;">
                    <td>Kekurangan</td>
                    <td class="value">Rp
                        {{ number_format((float) ($invoice->total - $invoice->booking->dp_amount), 0, ',', '.') }}</td>
                </tr>
            @else
                <tr class="divider">
                    <td colspan="2"></td>
                </tr>
                <tr class="summary-total">
                    <td>Total Booking</td>
                    <td class="value">Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}</td>
                </tr>
                <tr class="summary-total" style="padding-top: 4px;">
                    <td>Kekurangan</td>
                    <td class="value">Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}</td>
                </tr>
            @endif
        </table>

        <div style="clear: both;"></div>

        <div class="payment-box">
            <div class="payment-title"><span class="payment-dot"></span> Informasi Pembayaran</div>
            <div class="payment-info">
                {!! nl2br(e($invoiceFooterNotes ?? 'Info pembayaran belum diatur. Hubungi pihak MUA.')) !!}
            </div>
        </div>

        <div class="notes-title">Catatan</div>
        <div class="notes-content">
            - fee transport menyesuaikan jarak<br>
            - tnc akan dikirimkan bersama invoice
        </div>
    </div>
</body>

</html>
