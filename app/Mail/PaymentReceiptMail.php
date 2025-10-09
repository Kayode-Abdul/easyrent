<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function build()
    {
        $filename = 'receipt_' . $this->payment->transaction_id . '.pdf';
        
        return $this->subject('Payment Receipt - EasyRent')
            ->view('emails.payment-receipt')
            ->attachFromStorage('receipts/' . $filename);
    }
}
