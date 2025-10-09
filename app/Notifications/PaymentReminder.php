<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $type;

    public function __construct(Payment $payment, $type)
    {
        $this->payment = $payment;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = $this->type === 'upcoming' 
            ? 'Your rent payment is due in 3 days'
            : 'Your rent payment is overdue';

        return (new MailMessage)
            ->subject($message)
            ->line($message)
            ->line('Amount: ₦' . number_format($this->payment->amount, 2))
            ->line('Due Date: ' . $this->payment->due_date->format('M d, Y'))
            ->action('Make Payment', route('payments.show', $this->payment->id))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'due_date' => $this->payment->due_date,
            'type' => $this->type,
        ];
    }
}
