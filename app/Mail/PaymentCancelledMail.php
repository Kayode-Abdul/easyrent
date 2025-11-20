<?php

namespace App\Mail;

use App\Models\BenefactorPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;

    public function __construct(BenefactorPayment $payment)
    {
        $this->payment = $payment;
    }

    public function build()
    {
        return $this->subject('Recurring Payment Cancelled')
                    ->view('emails.payment-cancelled');
    }
}
