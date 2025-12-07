<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Submitted</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .application-summary { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .amount { font-size: 24px; font-weight: bold; color: #28a745; }
        .footer { text-align: center; margin-top: 30px; color: #666; }
        ul { list-style: none; padding: 0; }
        li { margin: 8px 0; }
        .label { font-weight: bold; color: #555; }
        .payment-btn { background: #007bff; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-size: 18px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Application Submitted Successfully</h1>
            <p>Your apartment application has been received</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $tenant_name }},</p>
            
            <p>Thank you for your interest! Your application for the apartment has been submitted successfully.</p>
            
            <div class="application-summary">
                <h3>📋 Application Summary</h3>
                <ul>
                    <li><span class="label">Property:</span> {{ $property->prop_name }}</li>
                    <li><span class="label">Address:</span> {{ $property->prop_address }}</li>
                    <li><span class="label">Apartment Type:</span> {{ $apartment->apartment_type }}</li>
                    <li><span class="label">Monthly Rent:</span> ₦{{ number_format($apartment->amount) }}</li>
                    <li><span class="label">Lease Duration:</span> {{ $invitation->lease_duration }} months</li>
                    <li><span class="label">Move-in Date:</span> {{ $invitation->move_in_date->format('M d, Y') }}</li>
                    <li><span class="label">Total Amount:</span> <span class="amount">₦{{ number_format($invitation->total_amount) }}</span></li>
                </ul>
            </div>
            
            <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8; margin: 20px 0;">
                <h4>💳 Complete Your Payment</h4>
                <p>To secure your apartment, please complete the payment process. Your apartment will be reserved once payment is confirmed.</p>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ route('apartment.invite.payment', ['token' => $invitation->invitation_token, 'payment_id' => $payment->id]) }}" class="payment-btn">
                    Complete Payment - ₦{{ number_format($invitation->total_amount) }}
                </a>
            </div>
            
            <div style="background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545; margin: 20px 0;">
                <h4>⚠️ Important</h4>
                <ul>
                    <li>Your application is valid for 24 hours</li>
                    <li>Complete payment to secure the apartment</li>
                    <li>You will receive confirmation once payment is processed</li>
                </ul>
            </div>
            
            <p>If you have any questions, please contact the landlord directly or reach out to our support team.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,<br><strong>EasyRent Team</strong></p>
            <p><small>This is an automated notification from EasyRent. Please do not reply to this email.</small></p>
        </div>
    </div>
</body>
</html>