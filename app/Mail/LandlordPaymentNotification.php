<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LandlordPaymentNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $payment;
    public $commissionAmount;
    public $netAmount;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        
        // Calculate commission (assuming 2.5% platform fee)
        $this->commissionAmount = $payment->amount * 0.025;
        $this->netAmount = $payment->amount - $this->commissionAmount;
    }

    public function build()
    {
        $filename = 'receipt_' . $this->payment->transaction_id . '.pdf';
        
        return $this->subject('Payment Received - EasyRent')
            ->view('emails.landlord-payment-notification')
            ->attachFromStorage('receipts/' . $filename);
    }
}
