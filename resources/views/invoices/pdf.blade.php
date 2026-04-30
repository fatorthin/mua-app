<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #374151;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }

        .header {
            background: #ec4899;
            color: white;
            padding: 30px 40px;
        }

        .header h1 {
            margin: 0 0 4px;
            font-size: 28px;
        }

        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 13px;
        }

        .content {
            padding: 30px 40px;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            width: 45%;
        }

        .invoice-box h3 {
            margin: 0 0 8px;
            font-size: 12px;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.05em;
        }

        .invoice-box p {
            margin: 0 0 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead th {
            background: #fce7f3;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            color: #9d174d;
            letter-spacing: 0.05em;
        }

        tbody td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        .totals {
            margin-left: auto;
            width: 260px;
        }

        .totals table td {
            border: none;
            padding: 6px 12px;
        }

        .totals .grand-total td {
            background: #ec4899;
            color: white;
            font-weight: bold;
            border-radius: 6px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #9ca3af;
            text-align: center;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 12px;
            border-radius: 99px;
            font-weight: bold;
            font-size: 12px;
        }

        .status-unpaid {
            background: #fed7aa;
            color: #92400e;
            padding: 4px 12px;
            border-radius: 99px;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>💄 MUA Manager</h1>
        <p>Invoice Profesional</p>
    </div>
    <div class="content">
        <div class="invoice-info">
            <div class="invoice-box">
                <h3>Detail Invoice</h3>
                <p><strong>No. Invoice:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Tanggal:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
                <p><strong>Jatuh Tempo:</strong> {{ $invoice->due_date?->format('d M Y') ?? '-' }}</p>
                <p style="margin-top:8px;">
                    <span class="{{ $invoice->status === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                        {{ $invoice->status === 'paid' ? 'LUNAS' : 'BELUM DIBAYAR' }}
                    </span>
                </p>
            </div>
            <div class="invoice-box">
                <h3>Klien</h3>
                <p><strong>{{ $invoice->booking->client->name }}</strong></p>
                <p>{{ $invoice->booking->client->phone ?? '-' }}</p>
                <p>{{ $invoice->booking->client->email ?? '' }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Tanggal</th>
                    <th>Durasi</th>
                    <th style="text-align:right;">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice->booking->service->name }}</td>
                    <td>{{ $invoice->booking->booking_date->format('d M Y, H:i') }}</td>
                    <td>{{ $invoice->booking->duration }} menit</td>
                    <td style="text-align:right;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align:right;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pajak</td>
                    <td style="text-align:right;">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                </tr>
                <tr class="grand-total">
                    <td><strong>TOTAL</strong></td>
                    <td style="text-align:right;"><strong>Rp {{ number_format($invoice->total, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        @if ($invoice->notes)
            <div style="margin-top:20px; padding:12px; background:#f9fafb; border-radius:8px;">
                <strong>Catatan:</strong> {{ $invoice->notes }}
            </div>
        @endif

        <div class="footer">
            <p>Terima kasih telah menggunakan layanan kami. MUA Manager — Platform MUA Indonesia.</p>
        </div>
    </div>
</body>

</html>
