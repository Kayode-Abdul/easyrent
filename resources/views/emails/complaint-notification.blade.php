<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complaint Notification - EasyRent</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .complaint-info {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-open { background: #dc3545; color: white; }
        .status-in_progress { background: #ffc107; color: #212529; }
        .status-resolved { background: #28a745; color: white; }
        .status-closed { background: #6c757d; color: white; }
        .status-escalated { background: #343a40; color: white; }
        .priority-low { color: #28a745; }
        .priority-medium { color: #ffc107; }
        .priority-high { color: #dc3545; }
        .priority-urgent { color: #343a40; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EasyRent Complaint System</h1>
        <p>
            @if($notificationType === 'new')
                New Complaint Submitted
            @elseif($notificationType === 'status_update')
                Complaint Status Updated
            @elseif($notificationType === 'assignment')
                Complaint Assigned to You
            @elseif($notificationType === 'escalation')
                Complaint Escalated
            @else
                Complaint Notification
            @endif
        </p>
    </div>

    <div class="content">
        @if($notificationType === 'new')
            <h2>A new complaint has been submitted</h2>
            <p>Hello {{ $complaint->landlord->first_name }},</p>
            <p>Your tenant <strong>{{ $complaint->tenant->first_name }} {{ $complaint->tenant->last_name }}</strong> has submitted a new complaint for your property.</p>
        @elseif($notificationType === 'status_update')
            <h2>Your complaint status has been updated</h2>
            <p>Hello {{ $complaint->tenant->first_name }},</p>
            <p>The status of your complaint has been updated.</p>
        @elseif($notificationType === 'assignment')
            <h2>A complaint has been assigned to you</h2>
            <p>Hello,</p>
            <p>A complaint has been assigned to you for resolution.</p>
        @elseif($notificationType === 'escalation')
            <h2>A complaint has been escalated</h2>
            <p>Hello,</p>
            <p>A complaint has been escalated and requires immediate attention.</p>
        @endif

        <div class="complaint-info">
            <h3>Complaint Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Complaint Number:</td>
                    <td style="padding: 8px 0;">{{ $complaint->complaint_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Title:</td>
                    <td style="padding: 8px 0;">{{ $complaint->title }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Category:</td>
                    <td style="padding: 8px 0;">{{ $complaint->category->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Priority:</td>
                    <td style="padding: 8px 0;">
                        <span class="priority-{{ $complaint->priority }}">
                            {{ ucfirst($complaint->priority) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Status:</td>
                    <td style="padding: 8px 0;">
                        <span class="status-badge status-{{ $complaint->status }}">
                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Property:</td>
                    <td style="padding: 8px 0;">{{ $complaint->apartment->property->address }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Submitted:</td>
                    <td style="padding: 8px 0;">{{ $complaint->created_at->format('M j, Y \a\t g:i A') }}</td>
                </tr>
            </table>

            @if($complaint->description)
                <h4 style="margin-top: 20px;">Description:</h4>
                <p style="background: #f8f9fa; padding: 15px; border-radius: 3px; margin: 10px 0;">
                    {{ $complaint->description }}
                </p>
            @endif
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('complaints.show', $complaint) }}" class="btn">
                View Complaint Details
            </a>
        </div>

        @if($notificationType === 'new')
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4 style="color: #856404; margin-top: 0;">What happens next?</h4>
                <ul style="color: #856404; margin-bottom: 0;">
                    <li>Review the complaint details and any attached photos</li>
                    <li>Contact your tenant if you need more information</li>
                    <li>Take appropriate action to resolve the issue</li>
                    <li>Update the complaint status as you make progress</li>
                </ul>
            </div>
        @endif

        @if($complaint->priority === 'urgent')
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4 style="color: #721c24; margin-top: 0;">⚠️ Urgent Priority</h4>
                <p style="color: #721c24; margin-bottom: 0;">
                    This complaint has been marked as urgent and requires immediate attention. 
                    Please address this issue as soon as possible.
                </p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This is an automated notification from EasyRent Complaint System.</p>
        <p>Please do not reply to this email. Use the complaint system to communicate about this issue.</p>
        <p>&copy; {{ date('Y') }} EasyRent. All rights reserved.</p>
    </div>
</body>
</html>