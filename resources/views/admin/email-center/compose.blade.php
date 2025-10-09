@extends('layout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-send"></i> Compose Email
                            </h4>
                            <p class="card-category">Send bulk emails to user groups</p>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.email-center') }}" class="btn btn-info btn-sm float-right">
                                <i class="nc-icon nc-minimal-left"></i> Back to Email Center
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="nc-icon nc-check-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="nc-icon nc-simple-remove"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-email-85"></i> Email Composition
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.email-center.send') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label>Recipients <span class="text-danger">*</span></label>
                            <select name="recipients" class="form-control" id="recipients" required onchange="updateRecipientCount()">
                                <option value="">Select recipient group...</option>
                                @foreach($userGroups as $key => $label)
                                    <option value="{{ $key }}" {{ request('group') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Choose which user group will receive this email</small>
                        </div>

                        <div class="form-group">
                            <label>Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" placeholder="Enter email subject..." 
                                   value="{{ old('subject') }}" required maxlength="255">
                        </div>

                        <div class="form-group">
                            <label>Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="12" placeholder="Enter your message here..." required>{{ old('message') }}</textarea>
                            <small class="text-muted">
                                You can use placeholders: <code>{user_name}</code>, <code>{user_email}</code>, <code>{date}</code>
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Send Method <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="radio" name="send_method" value="immediate" class="form-check-input" 
                                               id="immediate" checked onchange="toggleSchedule()">
                                        <label class="form-check-label" for="immediate">
                                            <strong>Send Immediately</strong>
                                            <br><small class="text-muted">Email will be sent right away</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="radio" name="send_method" value="scheduled" class="form-check-input" 
                                               id="scheduled" onchange="toggleSchedule()">
                                        <label class="form-check-label" for="scheduled">
                                            <strong>Schedule for Later</strong>
                                            <br><small class="text-muted">Choose when to send the email</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="schedule-datetime" style="display: none;">
                            <label>Schedule Date & Time</label>
                            <input type="datetime-local" name="schedule_date" class="form-control" 
                                   min="{{ now()->addMinutes(30)->format('Y-m-d\TH:i') }}">
                            <small class="text-muted">Select when the email should be sent</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="nc-icon nc-bell-55"></i>
                            <strong>Preview:</strong> You are about to send an email to <span id="recipient-count">0</span> users.
                            Please review your message carefully before sending.
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#previewModal">
                                <i class="nc-icon nc-zoom-split"></i> Preview Email
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="nc-icon nc-send"></i> Send Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Email Tips -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-bulb-63"></i> Email Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>Keep it concise:</strong> Users prefer shorter emails
                        </li>
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>Clear subject:</strong> Make the subject line descriptive
                        </li>
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>Personalize:</strong> Use {user_name} for personal touch
                        </li>
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>Call to action:</strong> Include clear next steps
                        </li>
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>Professional tone:</strong> Maintain brand consistency
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Available Placeholders -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-tag-content"></i> Available Placeholders
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Placeholder</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>{user_name}</code></td>
                                    <td>User's full name</td>
                                </tr>
                                <tr>
                                    <td><code>{user_email}</code></td>
                                    <td>User's email address</td>
                                </tr>
                                <tr>
                                    <td><code>{date}</code></td>
                                    <td>Current date</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">
                        These placeholders will be automatically replaced with actual user data when the email is sent.
                    </small>
                </div>
            </div>

            <!-- Quick Templates -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-paper"></i> Quick Templates
                    </h5>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical btn-block">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadTemplate('welcome')">
                            <i class="nc-icon nc-single-02"></i> Welcome Message
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="loadTemplate('newsletter')">
                            <i class="nc-icon nc-email-85"></i> Newsletter
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="loadTemplate('maintenance')">
                            <i class="nc-icon nc-settings"></i> Maintenance Notice
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="loadTemplate('announcement')">
                            <i class="nc-icon nc-bell-55"></i> General Announcement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="nc-icon nc-zoom-split"></i> Email Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header bg-light">
                        <strong>Subject:</strong> <span id="preview-subject">Your email subject</span>
                    </div>
                    <div class="card-body" style="border: 1px solid #dee2e6;">
                        <div id="preview-message">Your email message will appear here...</div>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <strong>Recipients:</strong> <span id="preview-recipients">Select recipient group</span>
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
// Template data
const templates = {
    welcome: {
        subject: 'Welcome to EasyRent Platform',
        message: 'Dear {user_name},\n\nWelcome to EasyRent! We\'re excited to have you join our platform.\n\nAs a new member, you now have access to:\n• Browse property listings\n• Connect with landlords and agents\n• Manage your rental applications\n• Access our customer support\n\nIf you have any questions, please don\'t hesitate to contact our support team.\n\nBest regards,\nThe EasyRent Team'
    },
    newsletter: {
        subject: 'EasyRent Monthly Newsletter',
        message: 'Dear {user_name},\n\nWelcome to this month\'s EasyRent newsletter!\n\n🏠 New Property Highlights:\n• Premium listings in downtown area\n• Affordable housing options\n• Student accommodation deals\n\n📈 Market Update:\n• Current rental trends\n• Price analysis by area\n• Tips for tenants and landlords\n\nStay tuned for more updates!\n\nBest regards,\nEasyRent Team'
    },
    maintenance: {
        subject: 'Scheduled System Maintenance Notification',
        message: 'Dear {user_name},\n\nWe will be performing scheduled system maintenance on {date}.\n\nMaintenance Window: 2:00 AM - 4:00 AM EST\n\nDuring this time, the EasyRent platform may be temporarily unavailable. We apologize for any inconvenience.\n\nWhat to expect:\n• Brief service interruption\n• Improved system performance\n• Enhanced security features\n\nThank you for your patience.\n\nEasyRent Support Team'
    },
    announcement: {
        subject: 'Important Announcement from EasyRent',
        message: 'Dear {user_name},\n\nWe have an important announcement to share with you.\n\n[Your announcement content here]\n\nWhat this means for you:\n• [Benefit/Impact 1]\n• [Benefit/Impact 2]\n• [Benefit/Impact 3]\n\nFor more information, please visit our website or contact our support team.\n\nThank you for being part of the EasyRent community.\n\nBest regards,\nThe EasyRent Team'
    }
};

function updateRecipientCount() {
    const select = document.getElementById('recipients');
    const countSpan = document.getElementById('recipient-count');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const text = selectedOption.text;
        const match = text.match(/\((\d+)\)/);
        const count = match ? match[1] : '0';
        countSpan.textContent = count;
    } else {
        countSpan.textContent = '0';
    }
}

function toggleSchedule() {
    const immediate = document.getElementById('immediate');
    const scheduleDiv = document.getElementById('schedule-datetime');
    
    if (immediate.checked) {
        scheduleDiv.style.display = 'none';
    } else {
        scheduleDiv.style.display = 'block';
    }
}

function loadTemplate(templateName) {
    if (templates[templateName]) {
        document.querySelector('input[name="subject"]').value = templates[templateName].subject;
        document.querySelector('textarea[name="message"]').value = templates[templateName].message;
    }
}

// Preview functionality
$('#previewModal').on('show.bs.modal', function () {
    const subject = document.querySelector('input[name="subject"]').value || 'No subject';
    const message = document.querySelector('textarea[name="message"]').value || 'No message';
    const recipients = document.getElementById('recipients');
    const recipientText = recipients.options[recipients.selectedIndex].text || 'No recipients selected';
    
    document.getElementById('preview-subject').textContent = subject;
    document.getElementById('preview-message').innerHTML = message.replace(/\n/g, '<br>');
    document.getElementById('preview-recipients').textContent = recipientText;
});

// Initialize recipient count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRecipientCount();
});
</script>

@endsection
