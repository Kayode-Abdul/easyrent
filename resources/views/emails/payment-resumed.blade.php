<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f8f9fa; padding: 30px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .button { display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .info-box { background-color: white; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Recurring Payment Resumed</h2>
        </div>
        <div class="content">
            <p>Hello {{ $payment->tenant->first_name }},</p>
            
            <p>Great news! <strong>{{ $payment->benefactor->full_name }}</strong> has resumed their recurring payment for your rent.</p>
            
            <div class="info-box">
                <p><strong>Payment Details:</strong></p>
                <p>Amount: {{ format_money($payment->amount, $payment->currency) }}</p>
                <p>Frequency: {{ ucfirst($payment->frequency) }}</p>
                <p>Next payment: {{ $payment->next_payment_date->format('M d, Y') }}</p>
            </div>
            
            <p>Automatic payments will continue as scheduled.</p>
            
            <p style="margin-top: 30px;">
                <a href="{{ url('/dashboard') }}" class="button">Go to Dashboard</a>
            </p>
        </div>
        <div class="footer">
            <p>This is an automated message from your property management system.</p>
        </div>
    </div>
</body>
</html>
