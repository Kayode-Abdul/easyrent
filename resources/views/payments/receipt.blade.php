@extends('layout')

@section('content')
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .receipt-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .receipt-details {
            margin-bottom: 30px;
        }
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-details th, .receipt-details td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .receipt-details th {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .print-section {
            text-align: right;
            margin-bottom: 15px;
        }
        .print-button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .print-button:hover {
            background-color: #45a049;
        }
        
        /* Print-specific styles */
        .download-section {
            margin-top: 20px;
            text-align: center;
        }
        .download-button, .back-button {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 5px;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .download-button {
            background-color: #007bff;
            color: white;
        }
        .download-button:hover {
            background-color: #0069d9;
            color: white;
        }
        .back-button {
            background-color: #6c757d;
            color: white;
        }
        .back-button:hover {
            background-color: #5a6268;
            color: white;
        }
        
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .receipt {
                border: none;
                padding: 0;
            }
            .print-section, .download-section {
                display: none;
            }
            .header {
                margin-bottom: 20px;
            }
            .receipt-details th {
                background-color: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .footer {
                margin-top: 30px;
            }
        }
    </style> 
    <div class="receipt content">
        <div class="header">
            <div class="print-section">
                <button onclick="window.print();" class="print-button">Print Receipt</button>
            </div>
            <img src="{{ asset('assets/img/logo.png') }}" alt="EasyRent Logo" class="logo">
            <h1 class="receipt-title">Payment Receipt</h1>
            <p>Transaction ID: {{ $payment->transaction_id }}</p>
        </div>

        <div class="receipt-details">
            <table>
                <tr>
                    <th>Date</th>
                    <td>{{ $payment->created_at->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <th>Property</th>
                    <td>{{ $payment->apartment->property->address }}</td>
                </tr>
                <tr>
                    <th>Apartment</th>
                    <td>{{ $payment->apartment->apartment_type }}</td>
                </tr>
                <tr>
                    <th>Tenant</th>
                    <td>{{ $payment->tenant->first_name }} {{ $payment->tenant->last_name }}</td>
                </tr>
                <tr>
                    <th>Landlord</th>
                    <td>{{ $payment->landlord->first_name }} {{ $payment->landlord->last_name }}</td>
                </tr>
                <tr>
                    <th>Duration</th>
                    <td>{{ $payment->duration }} {{ Str::plural('Month', $payment->duration) }}</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td>{{ $payment->getFormattedAmount() }}</td>
                </tr>
                <tr>
                    <th>Payment Method</th>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $payment->getFormattedStatus() }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for using EasyRent!</p>
            <p>For any questions, please contact support at support@easyrent.com</p>
            
            <div class="download-section">
                <a href="{{ route('payments.download-receipt', ['id' => $payment->id]) }}" class="download-button">Download as PDF</a>
                <a href="{{ url()->previous() }}" class="back-button">Back</a>
            </div>
        </div>
    </div>
@endsection
