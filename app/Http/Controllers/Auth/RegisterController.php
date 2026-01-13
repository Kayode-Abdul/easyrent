<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Session\SessionManagerInterface;
use App\Services\Logging\EasyRentLogger;
use App\Traits\LogsEasyRentEvents;
use App\Models\ApartmentInvitation;
use App\Mail\WelcomeToEasyRentMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers, LogsEasyRentEvents;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

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
        $this->middleware('guest');
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $messages = [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'phone.required' => 'Phone number is required',
            'email.required' => 'Email address is required',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Show the registration form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm(Request $request)
    {
        // Handle invitation context preservation
        $this->handleInvitationContext($request);
        
        // Store referrer in session if present
        if ($request->has('ref')) {
            session(['referrer_id' => $request->query('ref')]);
            
            // Track campaign if present
            if ($request->has('campaign')) {
                session(['campaign_code' => $request->query('campaign')]);
                
                // Increment campaign clicks
                $campaign = \App\Models\ReferralCampaign::where('campaign_code', $request->query('campaign'))
                    ->where('status', 'active')
                    ->first();
                    
                if ($campaign && $campaign->isWithinDateRange()) {
                    $campaign->incrementClicks();
                }
            }
        }
        
        // Pre-fill form data from invitation context if available
        $invitationData = $this->getInvitationFormData($request);
        
        return view('auth.register', compact('invitationData'));
    }

    /**
     * Override the default register method to handle file upload.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        $data = $request->all();

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photo = $request->file('photo');
            $photoName = 'user_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('assets/photos'), $photoName);
            $photoPath = 'assets/photos/' . $photoName;
        }
        $data['photo'] = $photoPath;

        $user = $this->create($data);

        // Log user registration
        $this->logger->logRegistration($request, $user, session()->has('invitation_token'));

        // Enhanced referral logic with campaign tracking
        $referrerId = session('referrer_id');
        $campaignCode = session('campaign_code');
        
        if ($referrerId && $user) {
            try {
                // Only create referral if referrer exists and is not the same as referred
                if ($referrerId != $user->user_id && \App\Models\User::where('user_id', $referrerId)->exists()) {
                    $referralData = [
                        'referrer_id' => $referrerId,
                        'referred_id' => $user->user_id,
                        'referral_status' => 'active', // Fixed: use referral_status instead of status
                        'referral_source' => 'link', // Default source
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ];
                    
                    // Add campaign tracking if available
                    if ($campaignCode) {
                        $campaign = \App\Models\ReferralCampaign::where('campaign_code', $campaignCode)
                            ->where('marketer_id', $referrerId)
                            ->where('status', 'active')
                            ->first();
                            
                        if ($campaign && $campaign->isWithinDateRange()) {
                            $referralData['campaign_id'] = $campaignCode;
                            $referralData['referral_source'] = 'qr_code';
                            
                            // Increment conversions
                            $campaign->incrementConversions();
                        }
                    }
                    
                    $referral = \App\Models\Referral::create($referralData);
                    
                    $this->logAuthEvent('referral_created', [
                        'referral_id' => $referral->id,
                        'referrer_id' => $referrerId,
                        'referred_id' => $user->user_id,
                        'campaign_code' => $campaignCode,
                    ]);
                    
                    // Create commission reward if user registers as landlord
                    $landlordRoleId = DB::table('roles')->where('name', 'landlord')->value('id');
                    if ($user->role == $landlordRoleId) { // Landlord role
                        $this->createCommissionReward($referrerId, $referral->id);
                    }
                } else {
                    $this->logAuthEvent('referral_creation_failed', [
                        'referrer_id' => $referrerId,
                        'referred_id' => $user->user_id,
                        'same_user' => $referrerId == $user->user_id,
                        'referrer_exists' => \App\Models\User::where('user_id', $referrerId)->exists(),
                    ]);
                }
            } catch (\Exception $e) {
                $this->logEasyRentError('Failed to create referral', $e, [
                    'referrer_id' => $referrerId,
                    'referred_id' => $user->user_id,
                    'campaign_code' => $campaignCode,
                ]);
                // Don't throw - let registration continue even if referral fails
            }
            
            session()->forget(['referrer_id', 'campaign_code']);
        }

        $this->guard()->login($user);
        return redirect($this->redirectPath());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Generate unique user_id
        do {
            $user_id = mt_rand(100000, 999999);
        } while (User::where('user_id', $user_id)->exists());

        // Check if this is an invitation-based registration
        $registrationSource = session('invitation_token') ? 'easyrent_invitation' : 'direct';

        // Generate username from email if not provided
        $username = $data['username'] ?? explode('@', $data['email'])[0] . '_' . substr($user_id, -4);

        // Default role to tenant (1) if not provided
        $role = $data['role'] ?? 1;

        return User::create([
            'user_id' => $user_id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $username,
            'email' => $data['email'],
            'role' => $role,
            'occupation' => $data['occupation'] ?? null,
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'state' => $data['state'] ?? null,
            'lga' => $data['lga'] ?? null,
            'admin' => $data['admin'] ?? 0,
            'created_at' => now(),
            'password' => Hash::make($data['password']),
            'photo' => $data['photo'] ?? null,
            'registration_source' => $registrationSource,
        ]);
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function registered(Request $request, $user)
    {
        // Handle invitation-based registration
        $invitationRedirect = $this->handleInvitationBasedRegistration($request, $user);
        if ($invitationRedirect) {
            return $invitationRedirect;
        }
        
        // Check if user came from EasyRent invitation (both session and URL parameter)
        $hasEasyRentInvitation = session()->has('easyrent_invitation_token') || session()->has('invitation_token') || $request->has('invitation_token');
        
        if ($hasEasyRentInvitation) {
            // For EasyRent users, skip email verification and login directly
            $this->guard()->login($user);
            
            // Send welcome email instead of verification
            try {
                Mail::to($user->email)->send(new WelcomeToEasyRentMail($user));
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email', ['user_id' => $user->user_id, 'error' => $e->getMessage()]);
            }
            
            // Redirect to EasyRent invitation or registration completion
            $redirectUrl = session()->pull('easyrent_redirect_url') ?? session()->pull('invitation_redirect_url');
            
            if (!$redirectUrl && $request->has('invitation_token')) {
                $redirectUrl = route('apartment.invite.show', $request->get('invitation_token'));
            }
            
            if ($redirectUrl) {
                return redirect($redirectUrl)->with('success', 'Welcome to EasyRent! You can now complete your apartment application.');
            }
        }
        
        // Send email verification notification
        $user->sendEmailVerificationNotification();
        
        // Log the user out since they need to verify email first
        $this->guard()->logout();
        
        // Redirect to email verification notice
        return redirect()->route('verification.notice')
            ->with('status', 'Registration successful! Please check your email to verify your account.');
    }
    
    /**
     * Create commission reward for successful referral
     */
    private function createCommissionReward($marketerId, $referralId)
    {
        $marketer = \App\Models\User::where('user_id', $marketerId)->first();
        
        if (!$marketer || !$marketer->isMarketer() || !$marketer->isActiveMarketer()) {
            return;
        }
        
        // Calculate commission amount (default 5% of average property rent)
        $commissionRate = $marketer->commission_rate ?? 5.0;
        $averageRent = \App\Models\Property::avg('price') ?? 100000; // Default to ₦100k if no properties
        $commissionAmount = ($averageRent * $commissionRate) / 100;
        
        \App\Models\ReferralReward::create([
            'marketer_id' => $marketerId,
            'referral_id' => $referralId,
            'reward_type' => 'commission',
            'amount' => $commissionAmount,
            'description' => 'Commission for landlord referral',
            'status' => 'pending'
        ]);
        
        // Update marketer profile stats
        $marketer->marketerProfile?->increment('total_referrals');
    }

    /**
     * Handle invitation context preservation during registration
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
                // Store invitation token in session for post-registration retrieval
                session(['invitation_token' => $invitationToken]);
                
                // Get invitation details and store redirect URL
                $invitation = ApartmentInvitation::where('invitation_token', $invitationToken)
                    ->active()
                    ->first();
                
                if ($invitation) {
                    // Store the invitation URL for post-registration redirect
                    $invitationUrl = route('apartment.invite.show', $invitationToken);
                    session(['invitation_redirect_url' => $invitationUrl]);
                    session(['easyrent_redirect_url' => $invitationUrl]); // Also store in legacy key for compatibility
                    
                    // Mark that authentication is required for this invitation
                    $invitation->markAuthenticationRequired();
                    $invitation->setRegistrationSource('easyrent_invitation');
                    
                    // Store comprehensive registration data in session manager
                    $registrationData = [
                        'invitation_token' => $invitationToken,
                        'apartment_id' => $invitation->apartment_id,
                        'landlord_id' => $invitation->landlord_id,
                        'original_url' => $request->fullUrl(),
                        'access_timestamp' => now()->toISOString(),
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip(),
                        'redirect_from' => 'registration',
                        'prospect_data' => [
                            'name' => $invitation->prospect_name,
                            'email' => $invitation->prospect_email,
                            'phone' => $invitation->prospect_phone
                        ]
                    ];
                    
                    $this->sessionManager->storeRegistrationData($invitationToken, $registrationData);
                    
                    // Also store in invitation context for consistency
                    $this->sessionManager->storeInvitationContext($invitationToken, $registrationData);
                    
                    Log::info('Invitation context preserved for registration', [
                        'invitation_token' => substr($invitationToken, 0, 8) . '...',
                        'apartment_id' => $invitation->apartment_id
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to handle invitation context during registration', [
                'error' => $e->getMessage(),
                'request_data' => $request->only(['invitation_token'])
            ]);
        }
    }

    /**
     * Get invitation form data for pre-filling registration form
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getInvitationFormData(Request $request): array
    {
        $invitationData = [];
        
        try {
            $invitationToken = session('invitation_token');
            
            if ($invitationToken) {
                // Get stored registration data from session manager
                $registrationData = $this->sessionManager->retrieveRegistrationData($invitationToken);
                
                if ($registrationData) {
                    $invitationData = [
                        'from_invitation' => true,
                        'apartment_info' => $registrationData['apartment_id'] ?? null,
                        'suggested_role' => 'tenant' // Default role for invitation-based registration
                    ];
                }
                
                // Get invitation details for additional context
                $invitation = ApartmentInvitation::where('invitation_token', $invitationToken)
                    ->active()
                    ->first();
                
                if ($invitation) {
                    $invitationData['property_name'] = $invitation->apartment->property->prop_name ?? 'Property';
                    $invitationData['apartment_type'] = $invitation->apartment->apartment_type ?? 'Apartment';
                    $invitationData['landlord_name'] = $invitation->landlord->first_name . ' ' . $invitation->landlord->last_name;
                    
                    // Pre-fill with prospect data if available
                    if ($invitation->prospect_name) {
                        $nameParts = explode(' ', $invitation->prospect_name, 2);
                        $invitationData['suggested_first_name'] = $nameParts[0] ?? '';
                        $invitationData['suggested_last_name'] = $nameParts[1] ?? '';
                    }
                    
                    if ($invitation->prospect_email) {
                        $invitationData['suggested_email'] = $invitation->prospect_email;
                    }
                    
                    if ($invitation->prospect_phone) {
                        $invitationData['suggested_phone'] = $invitation->prospect_phone;
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to get invitation form data', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $invitationData;
    }

    /**
     * Handle invitation-based registration completion
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function handleInvitationBasedRegistration(Request $request, $user): ?\Illuminate\Http\RedirectResponse
    {
        try {
            // Check for invitation token in session or URL parameter
            $invitationToken = session('invitation_token') ?? $request->get('invitation_token');
            
            if (!$invitationToken) {
                return null;
            }
            
            // Get invitation
            $invitation = ApartmentInvitation::where('invitation_token', $invitationToken)
                ->active()
                ->first();
            
            if (!$invitation) {
                // Clear invalid invitation token
                session()->forget(['invitation_token', 'invitation_redirect_url']);
                return null;
            }
            
            // For invitation-based registration, skip email verification and login directly
            $this->guard()->login($user);
            
            // Transfer session data to authenticated user session
            $this->sessionManager->transferToAuthenticatedSession($invitationToken, $user->user_id);
            
            // Update invitation with tenant information
            $invitation->update([
                'tenant_user_id' => $user->user_id,
                'prospect_name' => $user->first_name . ' ' . $user->last_name,
                'prospect_email' => $user->email,
                'prospect_phone' => $user->phone
            ]);

            // If a completed guest payment exists, finalize assignment now
            $guestPayment = \App\Models\Payment::where('status', \App\Models\Payment::STATUS_COMPLETED)
                ->whereNull('tenant_id')
                ->where('payment_meta->invitation_token', $invitationToken)
                ->orderBy('paid_at', 'desc')
                ->first();

            if ($guestPayment) {
                /** @var \App\Services\Payment\PaymentIntegrationService $paymentIntegration */
                $paymentIntegration = app(\App\Services\Payment\PaymentIntegrationService::class);
                $finalize = $paymentIntegration->finalizeAfterRegistration($invitation, $guestPayment, $user);

                if ($finalize['success']) {
                    Log::info('Post-registration finalization completed', [
                        'user_id' => $user->user_id,
                        'invitation_token' => substr($invitationToken, 0, 8) . '...'
                    ]);
                } else {
                    Log::warning('Post-registration finalization failed', [
                        'user_id' => $user->user_id,
                        'error' => $finalize['error'] ?? 'unknown'
                    ]);
                }
            }

            // Get redirect URL - check both possible session keys and URL parameter
            $redirectUrl = session()->pull('invitation_redirect_url') ?? session()->pull('easyrent_redirect_url');
            
            if (!$redirectUrl && $invitation) {
                // If no stored redirect URL, create one to the apartment invitation page
                $redirectUrl = route('apartment.invite.show', $invitationToken);
            }
            
            if ($redirectUrl) {
                // Clear invitation token from session but keep application data
                session()->forget('invitation_token');
                
                // Store the invitation token in a different session key for application flow
                session(['active_invitation_token' => $invitationToken]);
                
                Log::info('User registered via invitation and redirected', [
                    'user_id' => $user->user_id,
                    'invitation_token' => substr($invitationToken, 0, 8) . '...',
                    'redirect_url' => $redirectUrl
                ]);
                
                return redirect($redirectUrl)->with('success', 'Welcome to EasyRent! Your payment is confirmed and your apartment is being finalized.');
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle invitation-based registration', [
                'user_id' => $user->user_id ?? null,
                'error' => $e->getMessage()
            ]);
            
            // Clear potentially corrupted session data
            session()->forget(['invitation_token', 'invitation_redirect_url']);
        }
        
        return null;
    }

    /**
     * Evaluate marketer qualification for referrer
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ApartmentInvitation  $invitation
     * @return void
     */
    protected function evaluateMarketerQualification(User $user, ApartmentInvitation $invitation): void
    {
        try {
            // Check if there's a referrer for this registration
            $referrerId = session('referrer_id');
            
            if ($referrerId) {
                $referrer = User::where('user_id', $referrerId)->first();
                
                if ($referrer) {
                    // Check if referrer qualifies for marketer status
                    $referrer->evaluateMarketerPromotion();
                    
                    Log::info('Marketer qualification evaluated for referrer', [
                        'referrer_id' => $referrerId,
                        'new_user_id' => $user->user_id,
                        'invitation_token' => substr($invitation->invitation_token, 0, 8) . '...'
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to evaluate marketer qualification', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
