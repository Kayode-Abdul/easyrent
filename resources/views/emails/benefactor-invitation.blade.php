<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Payment Request</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 10px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="{{ asset('assets/images/logo-small.png') }}" alt="EasyRent" style="max-width: 150px;">
        </div>
        
        <h2 style="color: #2c3e50; text-align: center;">Rent Payment Request</h2>
        
        <p>Hello,</p>
        
        <p><strong>{{ $invitation->tenant->first_name }} {{ $invitation->tenant->last_name }}</strong> has requested that you pay their rent on EasyRent.</p>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #007bff; margin-top: 0;">Payment Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eee;"><strong>Amount:</strong></td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eee; text-align: right;">{{ format_money($invitation->amount, $invitation->proforma->apartment->currency ?? null) }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eee;"><strong>Tenant:</strong></td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eee; text-align: right;">{{ $invitation->tenant->first_name }} {{ $invitation->tenant->last_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0;"><strong>Valid Until:</strong></td>
                    <td style="padding: 10px 0; text-align: right;">{{ $invitation->expires_at->format('M d, Y') }}</td>
                </tr>
            </table>
            
            @if($invitation->invoice_details && isset($invitation->invoice_details['message']))
            <div style="margin-top: 20px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #007bff; border-radius: 4px;">
                <p style="margin: 0;"><strong>Message from tenant:</strong></p>
                <p style="margin: 10px 0 0 0;">{{ $invitation->invoice_details['message'] }}</p>
            </div>
            @endif
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $invitation->getPaymentLink() }}" style="display: inline-block; background-color: #007bff; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                View Payment Request
            </a>
        </div>
        
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0; color: #856404;"><strong>Payment Options:</strong></p>
            <ul style="margin: 10px 0; padding-left: 20px; color: #856404;">
                <li>One-time payment (pay as guest)</li>
                <li>Recurring payment (requires account for security)</li>
            </ul>
        </div>
        
        <p style="color: #666; font-size: 14px; margin-top: 30px;">
            This payment link will expire on <strong>{{ $invitation->expires_at->format('M d, Y') }}</strong>.
        </p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="color: #999; font-size: 12px; text-align: center;">
            This is an automated email from EasyRent. Please do not reply to this email.<br>
            If you have any questions, please contact us at support@easyrent.africa
        </p>
    </div>
</body>
</html>
