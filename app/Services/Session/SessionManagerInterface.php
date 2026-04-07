<?php

namespace App\Services\Session;

interface SessionManagerInterface
{
    /**
     * Store invitation context data in session
     *
     * @param string $token The invitation token
     * @param array $data The context data to store
     * @return void
     */
    public function storeInvitationContext(string $token, array $data): void;

    /**
     * Retrieve invitation context data from session
     *
     * @param string $token The invitation token
     * @return array|null The stored context data or null if not found
     */
    public function retrieveInvitationContext(string $token): ?array;

    /**
     * Clear invitation context data from session
     *
     * @param string $token The invitation token
     * @return void
     */
    public function clearInvitationContext(string $token): void;

    /**
     * Check if invitation context exists in session
     *
     * @param string $token The invitation token
     * @return bool True if context exists, false otherwise
     */
    public function hasInvitationContext(string $token): bool;

    /**
     * Clean up expired session data
     *
     * @return int Number of expired sessions cleaned up
     */
    public function cleanupExpiredSessions(): int;

    /**
     * Store application data for unauthenticated users
     *
     * @param string $token The invitation token
     * @param array $applicationData The application form data
     * @return void
     */
    public function storeApplicationData(string $token, array $applicationData): void;

    /**
     * Retrieve application data for a token
     *
     * @param string $token The invitation token
     * @return array|null The stored application data or null if not found
     */
    public function retrieveApplicationData(string $token): ?array;

    /**
     * Store registration data during the registration process
     *
     * @param string $token The invitation token
     * @param array $registrationData The registration form data
     * @return void
     */
    public function storeRegistrationData(string $token, array $registrationData): void;

    /**
     * Retrieve registration data for a token
     *
     * @param string $token The invitation token
     * @return array|null The stored registration data or null if not found
     */
    public function retrieveRegistrationData(string $token): ?array;

    /**
     * Transfer session data to authenticated user session
     *
     * @param string $token The invitation token
     * @param int $userId The authenticated user ID
     * @return void
     */
    public function transferToAuthenticatedSession(string $token, int $userId): void;

    /**
     * Get session expiration time for a token
     *
     * @param string $token The invitation token
     * @return \Carbon\Carbon|null The expiration time or null if not set
     */
    public function getSessionExpiration(string $token): ?\Carbon\Carbon;

    /**
     * Extend session expiration for a token
     *
     * @param string $token The invitation token
     * @param int $hours Hours to extend the session
     * @return void
     */
    public function extendSessionExpiration(string $token, int $hours = 24): void;
}