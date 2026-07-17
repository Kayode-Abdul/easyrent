<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Security Code</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { color: #ef8157; margin-top: 0; }
        p { font-size: 16px; line-height: 1.5; color: #555; }
        .otp-box { background-color: #f8f9fa; border: 1px dashed #ef8157; text-align: center; padding: 20px; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #ef8157; margin: 30px 0; border-radius: 4px; }
        .footer { margin-top: 40px; font-size: 12px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Security Verification</h2>
        <p>Hello,</p>
        <p>We received a request to update your <strong>{{ $updateType }}</strong>. To complete this action, please use the following One-Time Password (OTP):</p>
        
        <div class="otp-box">
            {{ $otp }}
        </div>
        
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not request this change, please ignore this email and ensure your account password is secure.</p>
        
        <p>Thank you,<br>The EasyRent Team</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} EasyRent. All rights reserved.
        </div>
    </div>
</body>
</html>
