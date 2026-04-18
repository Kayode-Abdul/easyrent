<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->transaction_id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0;
            border: 1px solid #eee;
            padding: 30px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #f8f9fa;
            margin-bottom: 10px;
            padding-bottom: 20px;
        }

        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
            text-transform: uppercase;
        }

        .transaction-id {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .details-table th {
            text-align: left;
            background-color: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            width: 35%;
            font-weight: 600;
        }

        .details-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: capitalize;
            font-size: 12px;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
        }

        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #95a5a6;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .amount-row td {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="header">
            @if(file_exists(public_path('assets/img/logo.png')))
                <img src="{{ public_path('assets/img/logo.png') }}" alt="EasyRent Logo" class="logo">
            @endif
            <h1 class="title">Payment Receipt</h1>
            <div class="transaction-id">Ref: {{ $payment->transaction_id }}</div>
        </div>

        <table class="details-table">
            <tr>
                <th>Payment Date</th>
                <td>{{ $payment->paid_at ? $payment->paid_at->format('F d, Y H:i') : ($payment->created_at ? $payment->created_at->format('F d, Y H:i') : 'N/A') }}
                </td>
            </tr>
            <tr>
                <th>Payer (Tenant)</th>
                <td>{{ $payment->tenant->first_name ?? '' }} {{ $payment->tenant->last_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Payee (Landlord)</th>
                <td>{{ $payment->landlord->first_name ?? '' }} {{ $payment->landlord->last_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Property</th>
                <td>
                    {{ $payment->apartment->property->prop_name ?? 'N/A' }}<br>
                    <small>{{ $payment->apartment->property->address ?? '' }},
                        {{ $payment->apartment->property->lga ?? '' }},
                        {{ $payment->apartment->property->state ?? '' }}</small>
                </td>
            </tr>
            <tr>
                <th>Apartment</th>
                <td>{{ $payment->apartment->apartment_type ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Duration</th>
                <td>{{ $payment->duration }} month(s)</td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
            </tr>
            <tr>
                <th>Payment Reference</th>
                <td>{{ $payment->payment_reference ?? 'N/A' }}</td>
            </tr>
            <tr class="amount-row">
                <th>Amount Paid</th>
                <td>{{ format_money($payment->amount, $payment->currency->code ?? null) }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @php
                        $statusClass = $payment->status === 'successful' ? 'status-success' : ($payment->status === 'pending' ? 'status-warning' : 'status-danger');
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $payment->status }}</span>
                </td>
            </tr>
        </table>

        <div class="footer">
            <p>This is a computer-generated receipt. No signature is required.</p>
            <p>Thank you for choosing EasyRent! | www.easyrent.africa | support@easyrent.africa</p>
        </div>
    </div>
</body>

</html>