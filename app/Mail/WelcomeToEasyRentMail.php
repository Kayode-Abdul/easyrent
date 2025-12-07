<?php

namespace App\Mail;

use App\Models\User;
use App\Models\ApartmentInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeToEasyRentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ApartmentInvitation $invitation = null)
    {
        $this->user = $user;
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->invitation 
            ? 'Welcome to EasyRent - Complete Your Application'
            : 'Welcome to EasyRent!';

        return $this->subject($subject)
                    ->view('emails.welcome-to-easyrent')
                    ->with([
                        'user' => $this->user,
                        'invitation' => $this->invitation,
                        'isInvitationBased' => !is_null($this->invitation)
                    ]);
    }
}