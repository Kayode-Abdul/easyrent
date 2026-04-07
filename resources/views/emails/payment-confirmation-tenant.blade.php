<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745, #007bff); color: white; padding: 30px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .success-box { background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .payment-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .apartment-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff; }
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
            <h1>✅ Payment Confirmed!</h1>
            <p>Your payment has been processed successfully</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $tenant_name }},</p>
            
            <div class="success-box">
                <h3>🎉 Payment Successful!</h3>
                <p>Your payment has been processed successfully. Your apartment is now officially reserved and you're all set for move-in!</p>
            </div>
            
            <div class="payment-details">
                <h3>💳 Payment Summary</h3>
                <ul>
                    <li><span class="label">Transaction ID:</span> {{ $payment->transaction_id }}</li>
                    <li><span class="label">Amount Paid:</span> <span class="amount">₦{{ number_format($payment->amount) }}</span></li>
                    <li><span class="label">Payment Date:</span> {{ $payment->created_at->format('M d, Y \a\t g:i A') }}</li>
                    <li><span class="label">Payment Method:</span> {{ ucfirst($payment->payment_method ?? 'Online Payment') }}</li>
                    <li><span class="label">Status:</span> <span style="color: #28a745; font-weight: bold;">Completed</span></li>
                </ul>
            </div>
            
            <div class="apartment-details">
                <h3>🏢 Your Apartment Details</h3>
                <ul>
                    <li><span class="label">Property:</span> {{ $property->prop_name }}</li>
                    <li><span class="label">Address:</span> {{ $property->prop_address }}</li>
                    <li><span class="label">Apartment Type:</span> {{ $apartment->apartment_type }}</li>
                    <li><span class="label">Monthly Rent:</span> ₦{{ number_format($apartment->amount) }}</li>
                    <li><span class="label">Move-in Date:</span> {{ $invitation->move_in_date->format('M d, Y') }}</li>
                    <li><span class="label">Lease Duration:</span> {{ $invitation->lease_duration }} months</li>
                    <li><span class="label">Lease End Date:</span> {{ $invitation->move_in_date->addMonths($invitation->lease_duration)->format('M d, Y') }}</li>
                </ul>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <h4>📋 What's Next?</h4>
                <ul>
                    <li>Your landlord will contact you to arrange key collection</li>
                    <li>Schedule a property inspection before move-in</li>
                    <li>Prepare for your move-in on {{ $invitation->move_in_date->format('M d, Y') }}</li>
                    <li>Keep this email as proof of payment</li>
                </ul>
            </div>
            
            <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8; margin: 20px 0;">
                <h4>📄 Important Documents</h4>
                <p>Your payment receipt and lease agreement are available in your EasyRent dashboard. Please download and keep these documents for your records.</p>
            </div>
            
            <div style="background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545; margin: 20px 0;">
                <h4>⚠️ Important Reminder</h4>
                <p>Please save this transaction ID: <strong>{{ $payment->transaction_id }}</strong> for your records. You may need it for future reference or support inquiries.</p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/dashboard') }}" style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Access Your Dashboard
                </a>
            </div>
            
            <p>Thank you for choosing EasyRent! We're excited to have you as part of our community. If you need any assistance, our support team is always ready to help.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,<br><strong>EasyRent Team</strong></p>
            <p><small>This is an automated confirmation from EasyRent. Please keep this email for your records.</small></p>
        </div>
    </div>
</body>
</html>