<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmailDeliveryTracker
{
    private const CACHE_PREFIX = 'email_delivery_';
    private const STATS_TTL = 3600; // 1 hour

    /**
     * Track successful email delivery
     */
    public function trackSuccess(string $type, string $recipient): void
    {
        $this->incrementCounter('success', $type);
        $this->logDelivery('success', $type, $recipient);
    }

    /**
     * Track failed email delivery
     */
    public function trackFailure(string $type, string $recipient, string $error): void
    {
        $this->incrementCounter('failure', $type);
        $this->logDelivery('failure', $type, $recipient, $error);
    }

    /**
     * Track email retry attempt
     */
    public function trackRetry(string $type, string $recipient, int $attempt): void
    {
        $this->incrementCounter('retry', $type);
        $this->logDelivery('retry', $type, $recipient, null, $attempt);
    }

    /**
     * Get delivery statistics for a specific type
     */
    public function getStats(string $type = null): array
    {
        if ($type) {
            return [
                'success' => $this->getCounter('success', $type),
                'failure' => $this->getCounter('failure', $type),
                'retry' => $this->getCounter('retry', $type),
            ];
        }

        // Get stats for all types
        $types = ['application', 'payment_confirmation', 'welcome', 'assignment'];
        $stats = [];

        foreach ($types as $emailType) {
            $stats[$emailType] = $this->getStats($emailType);
        }

        return $stats;
    }

    /**
     * Get overall delivery health score (0-100)
     */
    public function getHealthScore(): int
    {
        $allStats = $this->getStats();
        $totalSent = 0;
        $totalFailed = 0;

        foreach ($allStats as $typeStats) {
            $totalSent += $typeStats['success'];
            $totalFailed += $typeStats['failure'];
        }

        if ($totalSent + $totalFailed === 0) {
            return 100; // No emails sent, assume healthy
        }

        $successRate = $totalSent / ($totalSent + $totalFailed);
        return (int) round($successRate * 100);
    }

    /**
     * Reset statistics for a specific type or all types
     */
    public function resetStats(string $type = null): void
    {
        if ($type) {
            Cache::forget(self::CACHE_PREFIX . 'success_' . $type);
            Cache::forget(self::CACHE_PREFIX . 'failure_' . $type);
            Cache::forget(self::CACHE_PREFIX . 'retry_' . $type);
        } else {
            $types = ['application', 'payment_confirmation', 'welcome', 'assignment'];
            foreach ($types as $emailType) {
                $this->resetStats($emailType);
            }
        }
    }

    /**
     * Increment a counter for a specific metric and type
     */
    private function incrementCounter(string $metric, string $type): void
    {
        $key = self::CACHE_PREFIX . $metric . '_' . $type;
        Cache::increment($key, 1);
        Cache::expire($key, self::STATS_TTL);
    }

    /**
     * Get counter value for a specific metric and type
     */
    private function getCounter(string $metric, string $type): int
    {
        $key = self::CACHE_PREFIX . $metric . '_' . $type;
        return Cache::get($key, 0);
    }

    /**
     * Log delivery event
     */
    private function logDelivery(string $status, string $type, string $recipient, ?string $error = null, ?int $attempt = null): void
    {
        $logData = [
            'status' => $status,
            'type' => $type,
            'recipient' => $this->maskEmail($recipient),
            'timestamp' => now()->toISOString()
        ];

        if ($error) {
            $logData['error'] = $error;
        }

        if ($attempt) {
            $logData['attempt'] = $attempt;
        }

        Log::info('Email delivery event', $logData);
    }

    /**
     * Mask email address for privacy in logs
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }

        $username = $parts[0];
        $domain = $parts[1];

        $maskedUsername = strlen($username) > 2 
            ? substr($username, 0, 2) . str_repeat('*', strlen($username) - 2)
            : str_repeat('*', strlen($username));

        return $maskedUsername . '@' . $domain;
    }
}