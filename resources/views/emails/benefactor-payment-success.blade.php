<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .payment-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .amount {
            font-size: 24px;
            color: #28a745;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✓ Payment Received!</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $payment->tenant->first_name }},</p>
        
        <p>Great news! Your rent payment has been successfully received from your benefactor.</p>
        
        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value amount">₦{{ number_format($payment->amount, 2) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Paid By:</span>
                <span class="detail-value">{{ $payment->benefactor->full_name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Type:</span>
                <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</span>
            </div>
            
            @if($payment->isRecurring())
            <div class="detail-row">
                <span class="detail-label">Frequency:</span>
                <span class="detail-value">{{ ucfirst($payment->frequency) }}</span>
            </div>
            
            @if($payment->next_payment_date)
            <div class="detail-row">
                <span class="detail-label">Next Payment:</span>
                <span class="detail-value">{{ $payment->next_payment_date->format('M d, Y') }}</span>
            </div>
            @endif
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Payment Reference:</span>
                <span class="detail-value">{{ $payment->payment_reference }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ $payment->paid_at->format('M d, Y H:i A') }}</span>
            </div>
        </div>
        
        @if($payment->isRecurring())
        <p><strong>Note:</strong> This is a recurring payment. Your benefactor will be automatically charged {{ strtolower($payment->frequency) }} until they cancel or pause the payment.</p>
        @endif
        
        <p>Your landlord has been notified of this payment.</p>
        
        <div style="text-align: center;">
            <a href="{{ url('/dashboard') }}" class="button">View Dashboard</a>
        </div>
        
        <p>Thank you for using EasyRent!</p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} EasyRent. All rights reserved.</p>
    </div>
</body>
</html>
