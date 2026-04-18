<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to EasyRent!</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #007bff, #28a745); color: white; padding: 30px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .welcome-box { background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .footer { text-align: center; margin-top: 30px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Welcome to EasyRent!</h1>
            <p>Your account has been created successfully</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $user->first_name }},</p>
            
            <div class="welcome-box">
                <h3>🎉 Welcome to the EasyRent Community!</h3>
                @if($isInvitationBased && $invitation)
                    <p>Your account has been created successfully through an apartment invitation. You can now complete your apartment application for <strong>{{ $invitation->apartment->property->prop_name }}</strong>.</p>
                @else
                    <p>Your account has been created successfully. You can now enjoy all the benefits of our platform.</p>
                @endif
            </div>
            
            @if($isInvitationBased && $invitation)
                <div style="background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff;">
                    <h3>🏢 Your Apartment Application</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin: 8px 0;"><span style="font-weight: bold; color: #555;">Property:</span> {{ $invitation->apartment->property->prop_name }}</li>
                        <li style="margin: 8px 0;"><span style="font-weight: bold; color: #555;">Address:</span> {{ $invitation->apartment->property->prop_address }}</li>
                        <li style="margin: 8px 0;"><span style="font-weight: bold; color: #555;">Apartment Type:</span> {{ $invitation->apartment->apartment_type }}</li>
                        <li style="margin: 8px 0;"><span style="font-weight: bold; color: #555;">Monthly Rent:</span> {{ $invitation->apartment->getFormattedAmount() }}</li>
                    </ul>
                </div>
                
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0;">
                    <h4>⏳ Next Steps</h4>
                    <p>Your apartment application is ready to continue. Please complete the payment process to secure your apartment.</p>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ url('/apartment/invite/' . $invitation->token) }}" style="background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Complete Application
                    </a>
                </div>
            @else
                <p>With EasyRent, you can:</p>
                <ul>
                    <li>✅ Apply for apartments with ease</li>
                    <li>✅ Make secure online payments</li>
                    <li>✅ Track your rental history</li>
                    <li>✅ Communicate with landlords</li>
                    <li>✅ Access your documents anytime</li>
                </ul>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ url('/dashboard') }}" style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Access Your Dashboard
                    </a>
                </div>
            @endif
            
            <p>If you have any questions or need assistance, our support team is always ready to help.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,<br><strong>EasyRent Team</strong></p>
            <p><small>This is an automated welcome email from EasyRent.</small></p>
        </div>
    </div>
</body>
</html>