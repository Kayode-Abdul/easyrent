<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Task Assigned</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .code-box {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div style="text-align: center; padding: 20px 0; background-color: #ffffff; border-bottom: 2px solid #f0f0f0; margin-bottom: 20px;">
    <img src="{{ asset('assets/images/logo-small.png') }}" alt="EasyRent Logo" style="height: 45px; width: auto; max-width: 200px; object-fit: contain;">
</div>
    <div class="container">
        <div class="header">
            <h2>EasyRent Artisan Marketplace</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>Congratulations! Your bid on the task <strong>{{ optional($task->complaint)->title }}</strong> has been accepted by the landlord.</p>
            
            <p>Here are the task details:</p>
            <ul>
                <li><strong>Description:</strong> {{ $task->description }}</li>
                <li><strong>Duration:</strong> {{ $task->duration }}</li>
            </ul>

            <p>For your security and verification, here is your <strong>Verification Code</strong>. Please present this code to the tenant/landlord when you arrive at the property:</p>
            
            <div class="code-box">
                {{ $code }}
            </div>

            <p>Please log in to your dashboard to view more details and coordinate with the tenant or landlord.</p>
            
            <p><a href="{{ url('/artisan/dashboard') }}" class="btn" style="color: #ffffff;">Go to Dashboard</a></p>
        </div>
        <div class="footer">
            <p>Thank you for using EasyRent.</p>
        </div>
    </div>
</body>
</html>
