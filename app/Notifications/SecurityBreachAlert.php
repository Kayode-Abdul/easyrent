<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityBreachAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $breachData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $breachData)
    {
        $this->breachData = $breachData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $severity = $this->breachData['severity'] ?? 'unknown';
        $isEmergency = $this->breachData['emergency_lockdown'] ?? false;
        
        $subject = $isEmergency ? 
            '🚨 EMERGENCY: Critical Security Breach Detected' : 
            "🔒 Security Breach Alert - {$severity} severity";
            
        $message = (new MailMessage)
            ->subject($subject)
            ->greeting($isEmergency ? 'EMERGENCY ALERT' : 'Security Alert')
            ->line($this->getBreachDescription())
            ->line('**Breach Details:**')
            ->line("- Severity: {$severity}")
            ->line("- Detected at: " . ($this->breachData['detected_at'] ?? now()))
            ->line("- IP Address: " . ($this->breachData['ip_address'] ?? 'Unknown'))
            ->line("- Risk Score: " . ($this->breachData['risk_score'] ?? 'N/A'));
            
        if (!empty($this->breachData['patterns'])) {
            $message->line("- Suspicious Patterns: " . implode(', ', $this->breachData['patterns']));
        }
        
        if ($isEmergency) {
            $message->line('')
                   ->line('⚠️ **EMERGENCY LOCKDOWN INITIATED**')
                   ->line('All invitation tokens have been invalidated as a precautionary measure.');
        }
        
        $message->line('')
               ->line('Please review the security logs and take appropriate action.')
               ->action('View Security Dashboard', url('/admin/security'))
               ->line('This is an automated security alert from the EasyRent system.');
               
        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'security_breach',
            'severity' => $this->breachData['severity'] ?? 'unknown',
            'ip_address' => $this->breachData['ip_address'] ?? 'unknown',
            'patterns' => $this->breachData['patterns'] ?? [],
            'risk_score' => $this->breachData['risk_score'] ?? 0,
            'detected_at' => $this->breachData['detected_at'] ?? now(),
            'emergency_lockdown' => $this->breachData['emergency_lockdown'] ?? false,
            'message' => $this->getBreachDescription()
        ];
    }
    
    /**
     * Get breach description based on patterns
     */
    protected function getBreachDescription(): string
    {
        $patterns = $this->breachData['patterns'] ?? [];
        $severity = $this->breachData['severity'] ?? 'unknown';
        
        if (empty($patterns)) {
            return "A {$severity} severity security breach has been detected in the EasyRent invitation system.";
        }
        
        $descriptions = [
            'rapid_access' => 'Rapid successive access attempts detected',
            'multiple_tokens' => 'Multiple invitation tokens accessed from same IP',
            'failed_tokens' => 'Excessive failed token validation attempts',
            'bot_user_agent' => 'Bot-like user agent detected',
            'suspicious_headers' => 'Suspicious or missing HTTP headers',
            'geographic_anomaly' => 'Geographic access anomaly detected'
        ];
        
        $patternDescriptions = [];
        foreach ($patterns as $pattern) {
            if (isset($descriptions[$pattern])) {
                $patternDescriptions[] = $descriptions[$pattern];
            }
        }
        
        $description = "Security breach detected with the following suspicious activities: " . 
                      implode(', ', $patternDescriptions);
                      
        if ($this->breachData['emergency_lockdown'] ?? false) {
            $description .= " Due to the critical nature of this breach, an emergency lockdown has been initiated.";
        }
        
        return $description;
    }
}