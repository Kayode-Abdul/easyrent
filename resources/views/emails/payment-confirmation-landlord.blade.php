<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Received</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745, #007bff); color: white; padding: 30px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .success-box { background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .payment-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .property-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff; }
        .tenant-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #17a2b8; }
        .amount { font-size: 24px; font-weight: bold; color: #28a745; }
        .footer { text-align: center; margin-top: 30px; color: #666; }
        ul { list-style: none; padding: 0; }
        li { margin: 8px 0; }
        .label { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Payment Received!</h1>
            <p>Your tenant has successfully completed their payment</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $landlord->first_name }},</p>
            
            <div class="success-box">
                <h3>🎉 Great News!</h3>
                <p>Payment has been successfully received for your apartment. The tenant is now officially assigned to the property.</p>
            </div>
            
            <div class="payment-details">
                <h3>💳 Payment Details</h3>
                <ul>
                    <li><span class="label">Transaction ID:</span> {{ $payment->transaction_id }}</li>
                    <li><span class="label">Amount Received:</span> <span class="amount">{{ format_money($payment->amount, $payment->currency) }}</span></li>
                    <li><span class="label">Payment Date:</span> {{ $payment->created_at->format('M d, Y \a\t g:i A') }}</li>
                    <li><span class="label">Payment Method:</span> {{ ucfirst($payment->payment_method ?? 'Online Payment') }}</li>
                    <li><span class="label">Status:</span> <span style="color: #28a745; font-weight: bold;">Completed</span></li>
                </ul>
            </div>
            
            <div class="property-details">
                <h3>🏢 Property Details</h3>
                <ul>
                    <li><span class="label">Property:</span> {{ $property->prop_name }}</li>
                    <li><span class="label">Address:</span> {{ $property->prop_address }}</li>
                    <li><span class="label">Apartment Type:</span> {{ $apartment->apartment_type }}</li>
                    <li><span class="label">Monthly Rent:</span> {{ $apartment->getFormattedAmount() }}</li>
                </ul>
            </div>
            
            <div class="tenant-details">
                <h3>👤 Tenant Information</h3>
                <ul>
                    @if($tenant)
                        <li><span class="label">Name:</span> {{ $tenant->first_name }} {{ $tenant->last_name }}</li>
                        <li><span class="label">Email:</span> {{ $tenant->email }}</li>
                        <li><span class="label">Phone:</span> {{ $tenant->phone }}</li>
                    @else
                        <li><span class="label">Name:</span> {{ $invitation->prospect_name }}</li>
                        <li><span class="label">Email:</span> {{ $invitation->prospect_email }}</li>
                        <li><span class="label">Phone:</span> {{ $invitation->prospect_phone }}</li>
                    @endif
                    <li><span class="label">Move-in Date:</span> {{ $invitation->move_in_date->format('M d, Y') }}</li>
                    <li><span class="label">Lease Duration:</span> {{ $invitation->lease_duration }} months</li>
                </ul>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <h4>📋 Next Steps</h4>
                <ul>
                    <li>Contact your tenant to arrange key handover</li>
                    <li>Schedule a property inspection with the tenant</li>
                    <li>Provide move-in instructions and property guidelines</li>
                    <li>Update your records with the new tenant information</li>
                </ul>
            </div>
            
            <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8; margin: 20px 0;">
                <h4>💼 Financial Summary</h4>
                <p>The payment has been processed and will be transferred to your account according to your payment schedule. You can view detailed financial reports in your dashboard.</p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/dashboard') }}" style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    View Dashboard
                </a>
            </div>
            
            <p>Congratulations on successfully renting out your apartment! If you need any assistance or have questions, our support team is here to help.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,<br><strong>EasyRent Team</strong></p>
            <p><small>This is an automated notification from EasyRent. Please do not reply to this email.</small></p>
        </div>
    </div>
</body>
</html>