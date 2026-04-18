<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #ffc107; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f8f9fa; padding: 30px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .button { display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .info-box { background-color: white; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Recurring Payment Paused</h2>
        </div>
        <div class="content">
            <p>Hello {{ $payment->tenant->first_name }},</p>
            
            <p>We wanted to inform you that <strong>{{ $payment->benefactor->full_name }}</strong> has paused their recurring payment for your rent.</p>
            
            <div class="info-box">
                <p><strong>Payment Details:</strong></p>
                <p>Amount: {{ format_money($payment->amount, $payment->currency) }}</p>
                <p>Frequency: {{ ucfirst($payment->frequency) }}</p>
                <p>Paused on: {{ $payment->paused_at->format('M d, Y') }}</p>
            </div>
            
            @if($payment->pause_reason)
            <p><strong>Reason provided:</strong></p>
            <p style="background-color: white; padding: 15px; border-left: 4px solid #ffc107;">
                {{ $payment->pause_reason }}
            </p>
            @endif
            
            <p>The benefactor can resume payments at any time. You may want to reach out to them to discuss the situation.</p>
            
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
