<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ComplaintNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $complaint;
    public $notificationType;

    /**
     * Create a new message instance.
     */
    public function __construct(Complaint $complaint, string $notificationType)
    {
        $this->complaint = $complaint;
        $this->notificationType = $notificationType;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = match($this->notificationType) {
            'new' => 'New Complaint Submitted - ' . $this->complaint->complaint_number,
            'status_update' => 'Complaint Status Updated - ' . $this->complaint->complaint_number,
            'assignment' => 'Complaint Assigned to You - ' . $this->complaint->complaint_number,
            'escalation' => 'Complaint Escalated - ' . $this->complaint->complaint_number,
            default => 'Complaint Notification - ' . $this->complaint->complaint_number
        };

        return $this->subject($subject)
                    ->view('emails.complaint-notification')
                    ->with([
                        'complaint' => $this->complaint,
                        'notificationType' => $this->notificationType
                    ]);
    }
}