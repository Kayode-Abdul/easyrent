<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalCommissionError extends Notification implements ShouldQueue
{
    use Queueable;

    protected $errorType;
    protected $errorData;
    protected $timestamp;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $errorType, array $errorData)
    {
        $this->errorType = $errorType;
        $this->errorData = $errorData;
        $this->timestamp = now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Critical Commission Calculation Error - Immediate Action Required')
            ->error()
            ->line('A critical error has occurred in the commission calculation system.')
            ->line('**Error Type:** ' . $this->errorType)
            ->line('**Timestamp:** ' . $this->timestamp->format('Y-m-d H:i:s T'))
            ->line('**Error Details:**')
            ->line($this->formatErrorDetails())
            ->action('View System Logs', url('/admin/system-logs'))
            ->line('Please investigate and resolve this issue immediately to prevent commission calculation disruptions.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'critical_commission_error',
            'error_type' => $this->errorType,
            'error_data' => $this->errorData,
            'timestamp' => $this->timestamp->toISOString(),
            'requires_action' => true
        ];
    }

    /**
     * Format error details for email display
     */
    private function formatErrorDetails(): string
    {
        $details = [];
        
        foreach ($this->errorData as $key => $value) {
            if (is_array($value)) {
                $details[] = "- {$key}: " . json_encode($value);
            } else {
                $details[] = "- {$key}: {$value}";
            }
        }
        
        return implode("\n", $details);
    }
}