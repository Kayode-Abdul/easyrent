<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 10px; border: 1px solid #e9ecef;">
        <!-- EasyRent Centered Brand Logo -->
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ef8157; padding-bottom: 15px;">
            <img src="{{ asset('assets/images/logo-small.png') }}" alt="EasyRent" style="max-width: 150px;">
        </div>
        
        <h2 style="color: #2c3e50; text-align: center; margin-top: 0;">Important Property Announcement</h2>
        
        <p>Hello {{ $tenant->first_name }} {{ $tenant->last_name }},</p>
        
        <p>Your property manager has broadcasted a new notice regarding your residency at <strong>{{ $property->address }}</strong>.</p>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ef8157; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <h3 style="color: #ef8157; margin-top: 0; font-size: 1.15rem;">{{ $subjectLine }}</h3>
            <div style="color: #495057; font-size: 0.95rem; white-space: pre-wrap; line-height: 1.7;">{!! nl2br(e($messageBody)) !!}</div>
        </div>
        
        <div style="background-color: #fff9f6; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px dashed #ef8157;">
            <p style="margin: 0; color: #721c24; font-size: 0.9rem;">
                <strong>Sender:</strong> {{ $senderName }} (Property Manager)<br>
                <strong>Date Sent:</strong> {{ now()->format('M j, Y h:i A') }}
            </p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('dashboard') }}" style="display: inline-block; background-color: #ef8157; color: white; padding: 12px 35px; text-decoration: none; border-radius: 25px; font-weight: bold; box-shadow: 0 4px 6px rgba(239, 129, 87, 0.2); transition: all 0.2s ease;">
                Go to Dashboard
            </a>
        </div>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="color: #999; font-size: 11px; text-align: center; line-height: 1.5;">
            This is an authenticated broadcast message sent via EasyRent to active tenants of {{ $property->address }}.<br>
            Please do not reply directly to this automated email. If you need to contact your manager, please use the dashboard message center.<br>
            EasyRent Support &copy; {{ date('Y') }}
        </p>
    </div>
</body>
</html>
