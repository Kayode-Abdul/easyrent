<?php

namespace App\Services\Email;

use App\Models\ApartmentInvitation;
use App\Models\Payment;
use App\Models\User;

interface EmailNotificationInterface
{
    /**
     * Send application notification to both landlord and tenant
     */
    public function sendApplicationNotification(ApartmentInvitation $invitation, Payment $payment): bool;

    /**
     * Send payment confirmation to both parties
     */
    public function sendPaymentConfirmation(ApartmentInvitation $invitation, Payment $payment): bool;

    /**
     * Send welcome email to new users registered via invitation
     */
    public function sendWelcomeEmail(User $user, ApartmentInvitation $invitation): bool;

    /**
     * Send apartment assignment confirmation to both parties
     */
    public function sendAssignmentConfirmation(ApartmentInvitation $invitation, Payment $payment): bool;

    /**
     * Send email with retry logic for delivery failures
     */
    public function sendWithRetry(string $mailClass, array $data, string $to, int $maxRetries = 3): bool;

    /**
     * Get email delivery statistics
     */
    public function getDeliveryStats(): array;
}