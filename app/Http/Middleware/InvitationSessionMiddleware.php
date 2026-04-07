<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Session\SessionManagerInterface;
use App\Models\ApartmentInvitation;

class InvitationSessionMiddleware
{
    /**
     * The session manager instance
     */
    protected SessionManagerInterface $sessionManager;

    /**
     * Create a new middleware instance.
     */
    public function __construct(SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if this is an invitation-related request
        $invitationToken = $this->extractInvitationToken($request);
        
        if ($invitationToken) {
            $this->handleInvitationSession($request, $invitationToken);
        }

        $response = $next($request);

        // Store any form data for unauthenticated users
        if ($invitationToken && !auth()->check() && $request->isMethod('post')) {
            $this->storeFormDataInSession($request, $invitationToken);
        }

        return $response;
    }

    /**
     * Extract invitation token from request
     */
    private function extractInvitationToken(Request $request): ?string
    {
        // Check route parameter
        $token = $request->route('token');
        
        // Check query parameter
        if (!$token) {
            $token = $request->query('invitation_token');
        }

        // Check session for stored token
        if (!$token) {
            $token = $request->session()->get('current_invitation_token');
        }

        return $token;
    }

    /**
     * Handle invitation session logic
     */
    private function handleInvitationSession(Request $request, string $token): void
    {
        try {
            // Validate invitation exists and is active
            $invitation = ApartmentInvitation::where('invitation_token', $token)
                ->where('status', ApartmentInvitation::STATUS_ACTIVE)
                ->first();

            if (!$invitation) {
                return;
            }

            // Store current invitation token in session
            $request->session()->put('current_invitation_token', $token);

            // If user is not authenticated, store invitation context
            if (!auth()->check()) {
                $contextData = [
                    'apartment_id' => $invitation->apartment_id,
                    'landlord_id' => $invitation->landlord_id,
                    'original_url' => $request->fullUrl(),
                    'access_timestamp' => now()->toISOString(),
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip()
                ];

                $this->sessionManager->storeInvitationContext($token, $contextData);
                
                // Mark invitation as requiring authentication
                $invitation->markAuthenticationRequired();

                Log::info('Invitation context stored for unauthenticated user', [
                    'token' => substr($token, 0, 8) . '...',
                    'apartment_id' => $invitation->apartment_id
                ]);
            } else {
                // User is authenticated, transfer any existing session data
                $this->sessionManager->transferToAuthenticatedSession($token, auth()->id());
                
                Log::info('Session data transferred for authenticated user', [
                    'token' => substr($token, 0, 8) . '...',
                    'user_id' => auth()->id()
                ]);
            }

            // Mark invitation as viewed
            $invitation->markAsViewed();

        } catch (\Exception $e) {
            Log::error('Error handling invitation session', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store form data in session for unauthenticated users
     */
    private function storeFormDataInSession(Request $request, string $token): void
    {
        try {
            $formData = $request->except(['_token', '_method']);
            
            if (!empty($formData)) {
                // Determine the type of form data
                if ($request->routeIs('apartment.invite.apply')) {
                    $this->sessionManager->storeApplicationData($token, $formData);
                } elseif ($request->routeIs('register')) {
                    $this->sessionManager->storeRegistrationData($token, $formData);
                }

                Log::info('Form data stored in session', [
                    'token' => substr($token, 0, 8) . '...',
                    'route' => $request->route()->getName(),
                    'data_keys' => array_keys($formData)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error storing form data in session', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);
        }
    }
}
