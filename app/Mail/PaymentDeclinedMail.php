<?php

namespace App\Mail;

use App\Models\PaymentInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentDeclinedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;

    public function __construct(PaymentInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function build()
    {
        return $this->subject('Payment Request Declined')
                    ->view('emails.payment-declined');
    }
}
