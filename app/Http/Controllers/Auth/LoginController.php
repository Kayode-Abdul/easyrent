<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Services\Session\SessionManagerInterface;
use App\Services\Logging\EasyRentLogger;
use App\Traits\LogsEasyRentEvents;
use App\Models\ApartmentInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, LogsEasyRentEvents;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return \Illuminate\Support\Facades\Auth::guard();
    }

    /**
     * Get the post-login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/dashboard';
    }

    /**
     * Session manager for invitation context
     */
    protected $sessionManager;

    /**
     * EasyRent Link Logger
     */
    protected $logger;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SessionManagerInterface $sessionManager, EasyRentLogger $logger)
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;
    }

    /**
     * Show the application's login form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm(Request $request)
    {
        // If user is already authenticated, redirect to dashboard
        if (auth()->check()) {
            return redirect('/dashboard');
        }
        
        // Handle invitation context preservation
        $this->handleInvitationContext($request);
        
        // Check if session expired
        if ($request->has('expired')) {
            session()->flash('status', 'Your session has expired. Please login again.');
        }
        
        // Check if coming from invitation redirect
        if ($request->has('invitation_redirect')) {
            session()->flash('info', 'Please login to continue with your apartment application.');
        }
        
        return view('auth.login');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        // Log successful authentication
        $this->logger->logAuthenticationEvent('login_success', $request, $user, [
            'has_invitation_context' => session()->has('easyrent_invitation_token'),
            'invitation_token' => session()->has('easyrent_invitation_token') ? 
                substr(session('easyrent_invitation_token'), 0, 8) . '...' : null,
        ]);

        // Update last activity timestamp
        session(['last_activity' => time()]);
        
        // Handle invitation context after authentication
        $invitationRedirect = $this->handlePostAuthenticationInvitation($request, $user);
        if ($invitationRedirect) {
            return $invitationRedirect;
        }
        
        // Check if user came from EasyRent invitation link (legacy support)
        if (session()->has('easyrent_redirect_url')) {
            $redirectUrl = session()->pull('easyrent_redirect_url');
            
            // If there's application data, process it after redirect
            if (session()->has('easyrent_application_data')) {
                session()->flash('process_application', true);
            }
            
            return redirect($redirectUrl);
        }
        
        return redirect()->intended($this->redirectPath());
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been logged out successfully.');
    }

    /**
     * Handle invitation context preservation during login redirect
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function handleInvitationContext(Request $request): void
    {
        try {
            // Check if there's an invitation token in the request (support both 'token' and 'invitation_token' parameters)
            $invitationToken = $request->get('token') ?? $request->get('invitation_token') ?? session('invitation_token');
            
            if ($invitationToken) {
                // Store invitation token in session for post-authentication retrieval
                session(['invitation_token' => $invitationToken]);
                
                // Get invitation details and store redirect URL
                $invitation = ApartmentInvitation::where('invitation_token', $invitationToken)
                    ->active()
                    ->first();
                
                if ($invitation) {
                    // Store the invitation URL for post-authentication redirect
                    $invitationUrl = route('apartment.invite.show', $invitationToken);
                    session(['invitation_redirect_url' => $invitationUrl]);
                    session(['easyrent_redirect_url' => $invitationUrl]); // Also store in legacy key for compatibility
                    
                    // Mark that authentication is required for this invitation
                    $invitation->markAuthenticationRequired();
                    
                    // Store invitation context in session manager
                    $contextData = [
                        'invitation_token' => $invitationToken,
                        'apartment_id' => $invitation->apartment_id,
                        'landlord_id' => $invitation->landlord_id,
                        'original_url' => $request->fullUrl(),
                        'access_timestamp' => now()->toISOString(),
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip(),
                        'redirect_from' => 'login'
                    ];
                    
                    $this->sessionManager->storeInvitationContext($invitationToken, $contextData);
                    
                    $this->logSessionEvent('invitation_context_preserved', [
                        'invitation_token' => substr($invitationToken, 0, 8) . '...',
                        'apartment_id' => $invitation->apartment_id,
                    ]);
                }
            }
            
            // Also check for legacy EasyRent invitation parameters
            if ($request->has('from_invitation')) {
                session(['from_invitation' => true]);
            }
            
        } catch (\Exception $e) {
            $this->logEasyRentError('Failed to handle invitation context during login', $e, [
                'request_data' => $request->only(['invitation_token', 'from_invitation']),
            ]);
        }
    }

    /**
     * Handle post-authentication invitation retrieval and redirect
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function handlePostAuthenticationInvitation(Request $request, $user): ?\Illuminate\Http\RedirectResponse
    {
        try {
            $invitationToken = session('invitation_token');
            
            if (!$invitationToken) {
                return null;
            }
            
            // Retrieve invitation context from session manager
            $contextData = $this->sessionManager->retrieveInvitationContext($invitationToken);
            
            if (!$contextData) {
                // Try to get invitation from database
                $invitation = ApartmentInvitation::where('invitation_token', $invitationToken)
                    ->active()
                    ->first();
                
                if (!$invitation) {
                    // Clear invalid invitation token
                    session()->forget(['invitation_token', 'invitation_redirect_url']);
                    return null;
                }
                
                // Create context data from invitation
                $contextData = [
                    'invitation_token' => $invitationToken,
                    'apartment_id' => $invitation->apartment_id,
                    'landlord_id' => $invitation->landlord_id,
                    'retrieved_from' => 'database'
                ];
            }
            
            // Transfer session data to authenticated user session
            $this->sessionManager->transferToAuthenticatedSession($invitationToken, $user->user_id);
            
            // Update invitation with authenticated user information
            $invitation = ApartmentInvitation::where('invitation_token', $invitationToken)->first();
            if ($invitation && !$invitation->tenant_user_id) {
                $invitation->update([
                    'tenant_user_id' => $user->user_id,
                    'prospect_name' => $user->first_name . ' ' . $user->last_name,
                    'prospect_email' => $user->email,
                    'prospect_phone' => $user->phone
                ]);
                
                // Mark invitation as viewed by authenticated user
                $invitation->markAsViewed();
            }
            
            // Extend session for continued use
            $this->extendInvitationSession($invitationToken);
            
            // Get redirect URL - check both possible session keys
            $redirectUrl = session()->pull('invitation_redirect_url') ?? session()->pull('easyrent_redirect_url');
            
            if (!$redirectUrl && $invitation) {
                // If no stored redirect URL, create one to the apartment invitation page
                $redirectUrl = route('apartment.invite.show', $invitationToken);
            }
            
            if ($redirectUrl) {
                // Clear invitation token from session but keep application data
                session()->forget('invitation_token');
                
                // Add success message
                session()->flash('success', 'Welcome back! You can now continue with your apartment application.');
                
                Log::info('User authenticated and redirected to invitation', [
                    'user_id' => $user->user_id,
                    'invitation_token' => substr($invitationToken, 0, 8) . '...',
                    'redirect_url' => $redirectUrl
                ]);
                
                return redirect($redirectUrl);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to handle post-authentication invitation', [
                'user_id' => $user->user_id ?? null,
                'error' => $e->getMessage()
            ]);
            
            // Clear potentially corrupted session data
            session()->forget(['invitation_token', 'invitation_redirect_url']);
        }
        
        return null;
    }

    /**
     * Handle failed login attempts with invitation context
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Preserve invitation context on failed login
        $invitationToken = session('invitation_token');
        
        if ($invitationToken) {
            // Suggest registration if user doesn't exist
            $email = $request->get($this->username());
            
            if ($email && !\App\Models\User::where('email', $email)->exists()) {
                session()->flash('suggestion', 'Don\'t have an account? <a href="' . route('register') . '?invitation_token=' . $invitationToken . '">Register here</a> to continue with your apartment application.');
            }
            
            // Log failed login attempt with invitation context
            Log::info('Failed login attempt with invitation context', [
                'email' => $email,
                'invitation_token' => substr($invitationToken, 0, 8) . '...',
                'ip_address' => $request->ip()
            ]);
        }
        
        // Return failed login response with validation errors
        throw \Illuminate\Validation\ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Extend session expiration for active invitation flows
     *
     * @param  string  $invitationToken
     * @return void
     */
    protected function extendInvitationSession(string $invitationToken): void
    {
        try {
            // Extend session expiration by 24 hours for active users
            $this->sessionManager->extendSessionExpiration($invitationToken, 24);
            
            // Also extend database session expiration
            $invitation = ApartmentInvitation::where('invitation_token', $invitationToken)->first();
            if ($invitation) {
                $invitation->extendSessionExpiration(24);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to extend invitation session', [
                'invitation_token' => substr($invitationToken, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);
        }
    }
}
