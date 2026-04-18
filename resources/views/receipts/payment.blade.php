<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->transaction_id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #5d5fef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #5d5fef;
            margin: 0;
            font-size: 28px;
            text-transform: uppercase;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .receipt-title {
            text-align: center;
            margin-bottom: 40px;
        }
        .receipt-title h2 {
            margin: 0;
            font-size: 22px;
            color: #444;
            letter-spacing: 1px;
        }
        .info-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-col {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
        }
        .value {
            font-size: 15px;
            display: block;
            margin-bottom: 15px;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .payment-table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
            padding: 12px;
            color: #444;
            font-size: 13px;
        }
        .payment-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .amount-big {
            font-size: 24px;
            font-weight: bold;
            color: #5d5fef;
        }
        .qr-section {
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 20px;
            color: #999;
            font-size: 12px;
        }
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="float: left;">
                <h1>{{ $appName }}</h1>
                <p style="margin: 5px 0 0 0; color: #666;">Modern Real Estate Management</p>
            </div>
            <div style="float: right; text-align: right;">
                <p style="margin: 0; font-weight: bold;">Date Issued:</p>
                <p style="margin: 0;">{{ $date }}</p>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="receipt-title">
            <h2>OFFICIAL PAYMENT RECEIPT</h2>
            <p>Transaction ID: <strong>{{ $payment->transaction_id }}</strong></p>
        </div>

        <div class="info-section">
            <div class="info-col">
                <span class="label">Payer Information:</span>
                <span class="value">
                    <strong>{{ $payment->tenant->first_name }} {{ $payment->tenant->last_name }}</strong><br>
                    {{ $payment->tenant->email }}<br>
                    {{ $payment->tenant->phone }}
                </span>
            </div>
            <div class="info-col" style="float: right; text-align: right;">
                <span class="label">Recipient Information:</span>
                <span class="value">
                    <strong>{{ $payment->landlord->first_name }} {{ $payment->landlord->last_name }}</strong><br>
                    Property Owner/Manager<br>
                    Via {{ $appName }} Platform
                </span>
            </div>
            <div style="clear: both;"></div>
        </div>

        <table class="payment-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Reference</th>
                    <th>Payment Method</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Rental Payment</strong><br>
                        <span style="font-size: 12px; color: #777;">
                            {{ $payment->apartment->property->name ?? 'Property' }} - {{ $payment->apartment->apartment_number ?? 'Unit' }}<br>
                            Duration: {{ $payment->duration }} months
                        </span>
                    </td>
                    <td>{{ $payment->payment_reference }}</td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td style="text-align: right;">
                        <span class="amount-big">
                            @php
                                $currency = $payment->currency ?? ($payment->apartment && $payment->apartment->currency ? $payment->apartment->currency : null);
                                $symbol = $currency ? $currency->symbol : '₦';
                            @endphp
                            {{ $symbol }}{{ number_format($payment->amount, 2) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="info-section">
            <div class="info-col" style="width: 70%;">
                <span class="label">Status:</span>
                <span class="value status-success">{{ strtoupper($payment->status) }}</span>
                
                <span class="label">Payment Date:</span>
                <span class="value">{{ $payment->paid_at ? $payment->paid_at->format('F j, Y') : 'N/A' }}</span>
                
                @if($payment->due_date)
                    <span class="label">Next Due Date:</span>
                    <span class="value">{{ $payment->due_date->format('F j, Y') }}</span>
                @endif
            </div>
            <div class="info-col" style="width: 25%; float: right; text-align: right;">
                <span class="label">Verify Receipt:</span>
                <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" style="margin-top: 10px;">
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
            <p>Thank you for choosing {{ $appName }} for your rental needs.</p>
        </div>
    </div>
</body>
</html>
