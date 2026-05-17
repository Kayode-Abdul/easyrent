<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Property;
use App\Models\User;

class PropertyBulkNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $messageBody;
    public $property;
    public $tenant;
    public $senderName;

    /**
     * Create a new message instance.
     */
    public function __construct($subjectLine, $messageBody, Property $property, User $tenant)
    {
        $this->subjectLine = $subjectLine;
        $this->messageBody = $messageBody;
        $this->property = $property;
        $this->tenant = $tenant;
        
        // Sender is the property manager
        $manager = $property->agent;
        $this->senderName = $manager ? ($manager->first_name . ' ' . $manager->last_name) : 'Property Manager';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[EasyRent Broadcast] ' . $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.property-bulk-notification',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
