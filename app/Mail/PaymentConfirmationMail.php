<?php

namespace App\Mail;

use App\Models\ApartmentInvitation;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;
    public $payment;
    public $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(ApartmentInvitation $invitation, Payment $payment, string $recipient)
    {
        $this->invitation = $invitation;
        $this->payment = $payment;
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $apartment = $this->invitation->apartment;
        $property = $apartment->property;

        if ($this->recipient === 'landlord') {
            return $this->subject('Payment Received - ' . $property->prop_name)
                        ->view('emails.payment-confirmation-landlord')
                        ->with([
                            'landlord' => $this->invitation->landlord,
                            'tenant' => $this->invitation->tenant,
                            'apartment' => $apartment,
                            'property' => $property,
                            'invitation' => $this->invitation,
                            'payment' => $this->payment
                        ]);
        } else {
            return $this->subject('Payment Confirmation - ' . $property->prop_name)
                        ->view('emails.payment-confirmation-tenant')
                        ->with([
                            'tenant_name' => $this->invitation->prospect_name,
                            'apartment' => $apartment,
                            'property' => $property,
                            'invitation' => $this->invitation,
                            'payment' => $this->payment
                        ]);
        }
    }
}