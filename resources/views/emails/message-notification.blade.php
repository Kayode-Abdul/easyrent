<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Message</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 10px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="{{ asset('assets/images/logo-small.png') }}" alt="EasyRent" style="max-width: 150px;">
        </div>
        
        <h2 style="color: #2c3e50; text-align: center;">New Message from {{ $senderName }}</h2>
        
        <p>Hello {{ $receiverName }},</p>
        
        <p>You have received a new message on EasyRent.</p>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;">
            <h3 style="color: #007bff; margin-top: 0;">{{ $message->subject }}</h3>
            <div style="color: #666; margin-top: 15px;">
                {!! nl2br(e($message->body)) !!}
            </div>
        </div>
        
        <div style="background-color: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0; color: #004085;"><strong>From:</strong> {{ $senderName }}</p>
            <p style="margin: 5px 0 0 0; color: #004085;"><strong>Date:</strong> {{ $message->created_at->format('M d, Y h:i A') }}</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('messages.inbox') }}" style="display: inline-block; background-color: #007bff; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                View Message in Dashboard
            </a>
        </div>
        
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0; color: #856404; font-size: 14px;">
                <strong>💡 Tip:</strong> You can reply to this message by logging into your EasyRent dashboard.
            </p>
        </div>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="color: #999; font-size: 12px; text-align: center;">
            This is an automated email from EasyRent. Please do not reply to this email.<br>
            To manage your notification preferences, visit your <a href="{{ route('dashboard.user') }}" style="color: #007bff;">account settings</a>.<br>
            If you have any questions, please contact us at support@easyrent.africa
        </p>
    </div>
</body>
</html>
