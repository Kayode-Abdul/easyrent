<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Completed - Tenant Assigned</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .success-box { background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .tenant-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff; }
        .payment-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
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
            <h1>🎉 Payment Completed!</h1>
            <p>Your apartment has been successfully assigned to a new tenant</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $landlord->first_name }},</p>
            
            <div class="success-box">
                <h3>✅ Great News!</h3>
                <p>The payment for your apartment <strong>{{ $property->prop_name }}</strong> has been completed successfully. The apartment is now officially assigned to your new tenant.</p>
            </div>
            
            <div class="tenant-details">
                <h3>👤 Your New Tenant</h3>
                <ul>
                    <li><span class="label">Name:</span> {{ $tenant->first_name }} {{ $tenant->last_name }}</li>
                    <li><span class="label">Email:</span> {{ $tenant->email }}</li>
                    <li><span class="label">Phone:</span> {{ $tenant->phone }}</li>
                    <li><span class="label">Move-in Date:</span> {{ $invitation->move_in_date->format('M d, Y') }}</li>
                    <li><span class="label">Lease Duration:</span> {{ $invitation->lease_duration }} months</li>
                    <li><span class="label">Lease End Date:</span> {{ $invitation->move_in_date->addMonths($invitation->lease_duration)->format('M d, Y') }}</li>
                </ul>
            </div>
            
            <div class="payment-details">
                <h3>💰 Payment Details</h3>
                <ul>
                    <li><span class="label">Transaction ID:</span> {{ $payment->transaction_id }}</li>
                    <li><span class="label">Amount Received:</span> <span class="amount">₦{{ number_format($payment->amount) }}</span></li>
                    <li><span class="label">Payment Date:</span> {{ $payment->paid_at->format('M d, Y H:i A') }}</li>
                    <li><span class="label">Payment Method:</span> {{ ucfirst($payment->payment_method) }}</li>
                </ul>
            </div>
            
            <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8; margin: 20px 0;">
                <h4>📝 Next Steps</h4>
                <ul>
                    <li>Contact your new tenant to arrange key handover</li>
                    <li>Prepare the apartment for move-in</li>
                    <li>Update your records with the new lease information</li>
                    <li>Set up any necessary utilities or services</li>
                </ul>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/dashboard') }}" style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    View Dashboard
                </a>
            </div>
            
            <p>Congratulations on successfully renting out your apartment! We're here to help if you need any assistance.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,<br><strong>EasyRent Team</strong></p>
            <p><small>This is an automated notification from EasyRent. Please do not reply to this email.</small></p>
        </div>
    </div>
</body>
</html>