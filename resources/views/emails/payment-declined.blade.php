<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f8f9fa; padding: 30px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .button { display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Payment Request Declined</h2>
        </div>
        <div class="content">
            <p>Hello {{ $invitation->tenant->first_name }},</p>
            
            <p>We wanted to let you know that your payment request to <strong>{{ $invitation->benefactor_email }}</strong> has been declined.</p>
            
            @if($invitation->decline_reason)
            <p><strong>Reason provided:</strong></p>
            <p style="background-color: white; padding: 15px; border-left: 4px solid #dc3545;">
                {{ $invitation->decline_reason }}
            </p>
            @endif
            
            <p>You may want to reach out to them directly to discuss alternative arrangements.</p>
            
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
