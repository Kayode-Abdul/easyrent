<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Your New Home!</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #007bff, #28a745); color: white; padding: 30px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .welcome-box { background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .apartment-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff; }
        .landlord-contact { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #17a2b8; }
        .amount { font-size: 20px; font-weight: bold; color: #28a745; }
        .footer { text-align: center; margin-top: 30px; color: #666; }
        ul { list-style: none; padding: 0; }
        li { margin: 8px 0; }
        .label { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Welcome to Your New Home!</h1>
            <p>Congratulations! Your payment has been processed successfully</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $tenant->first_name }},</p>
            
            <div class="welcome-box">
                <h3>🎉 Congratulations!</h3>
                <p>Your payment has been processed successfully and your apartment is now officially reserved for you. Welcome to your new home!</p>
            </div>
            
            <div class="apartment-details">
                <h3>🏢 Your New Apartment</h3>
                <ul>
                    <li><span class="label">Property:</span> {{ $property->prop_name }}</li>
                    <li><span class="label">Address:</span> {{ $property->prop_address }}</li>
                    <li><span class="label">Apartment Type:</span> {{ $apartment->apartment_type }}</li>
                    <li><span class="label">Monthly Rent:</span> {{ $apartment->getFormattedAmount() }}</li>
                    <li><span class="label">Move-in Date:</span> {{ $invitation->move_in_date->format('M d, Y') }}</li>
                    <li><span class="label">Lease Duration:</span> {{ $invitation->lease_duration }} months</li>
                    <li><span class="label">Lease End Date:</span> {{ $invitation->move_in_date->addMonths($invitation->lease_duration)->format('M d, Y') }}</li>
                    <li><span class="label">Total Paid:</span> <span class="amount">{{ format_money($payment->amount, $payment->currency) }}</span></li>
                </ul>
            </div>
            
            <div class="landlord-contact">
                <h3>📞 Your Landlord Contact</h3>
                <ul>
                    <li><span class="label">Name:</span> {{ $landlord->first_name }} {{ $landlord->last_name }}</li>
                    <li><span class="label">Email:</span> {{ $landlord->email }}</li>
                    <li><span class="label">Phone:</span> {{ $landlord->phone }}</li>
                </ul>
                <p><small>Please contact your landlord to arrange key collection and move-in details.</small></p>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <h4>📋 Next Steps</h4>
                <ul>
                    <li>Contact your landlord to arrange key collection</li>
                    <li>Schedule a property inspection before move-in</li>
                    <li>Arrange utility connections if needed</li>
                    <li>Plan your move-in for {{ $invitation->move_in_date->format('M d, Y') }}</li>
                </ul>
            </div>
            
            <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8; margin: 20px 0;">
                <h4>📄 Important Documents</h4>
                <p>Your lease agreement and payment receipt are available in your EasyRent dashboard. Please keep these documents safe for your records.</p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/dashboard') }}" style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Access Your Dashboard
                </a>
            </div>
            
            <p>Thank you for choosing EasyRent! We're excited to have you as part of our community. If you need any assistance, our support team is here to help.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,<br><strong>EasyRent Team</strong></p>
            <p><small>This is an automated notification from EasyRent. Please do not reply to this email.</small></p>
        </div>
    </div>
</body>
</html>