<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Tenant Application</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .property-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff; }
        .applicant-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .amount { font-size: 24px; font-weight: bold; color: #007bff; }
        .footer { text-align: center; margin-top: 30px; color: #666; }
        ul { list-style: none; padding: 0; }
        li { margin: 8px 0; }
        .label { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 New Tenant Application</h1>
            <p>You have received a new application for your apartment</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $landlord->first_name }},</p>
            
            <p>Great news! You have received a new application for your apartment through EasyRent Link.</p>
            
            <div class="property-details">
                <h3>🏢 Property Details</h3>
                <ul>
                    <li><span class="label">Property:</span> {{ $property->prop_name }}</li>
                    <li><span class="label">Address:</span> {{ $property->prop_address }}</li>
                    <li><span class="label">Apartment Type:</span> {{ $apartment->apartment_type }}</li>
                    <li><span class="label">Monthly Rent:</span> {{ $apartment->getFormattedAmount() }}</li>
                </ul>
            </div>
            
            <div class="applicant-details">
                <h3>👤 Applicant Details</h3>
                <ul>
                    <li><span class="label">Name:</span> {{ $invitation->prospect_name }}</li>
                    <li><span class="label">Email:</span> {{ $invitation->prospect_email }}</li>
                    <li><span class="label">Phone:</span> {{ $invitation->prospect_phone }}</li>
                    <li><span class="label">Lease Duration:</span> {{ $invitation->lease_duration }} months</li>
                    <li><span class="label">Move-in Date:</span> {{ $invitation->move_in_date->format('M d, Y') }}</li>
                    <li><span class="label">Total Amount:</span> <span class="amount">{{ format_money($invitation->total_amount, $apartment->currency) }}</span></li>
                </ul>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <h4>⏳ Next Steps</h4>
                <p>The tenant is proceeding with payment. You will receive another notification once the payment is completed and the apartment is officially assigned.</p>
            </div>
            
            <p>You can track all your apartment applications and payments through your EasyRent dashboard.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/dashboard') }}" style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    View Dashboard
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>Best regards,<br><strong>EasyRent Team</strong></p>
            <p><small>This is an automated notification from EasyRent. Please do not reply to this email.</small></p>
        </div>
    </div>
</body>
</html>