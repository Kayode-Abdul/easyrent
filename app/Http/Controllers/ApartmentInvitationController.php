<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\ApartmentInvitation;
use App\Models\User;
use App\Models\Payment;
use App\Mail\TenantApplicationMail;
use App\Mail\ApartmentAssignedMail;
use App\Services\Session\SessionManagerInterface;
use App\Services\Payment\PaymentIntegrationService;
use App\Services\Logging\EasyRentLogger;
use App\Services\Security\SuspiciousActivityDetector;
use App\Services\Security\SecurityBreachResponseService;
use App\Services\Security\InputValidationService;
use App\Services\ErrorHandling\EasyRentErrorHandler;
use App\Services\ErrorHandling\ErrorRecoveryService;
use App\Services\Monitoring\ErrorMonitoringService;
use App\Services\Cache\EasyRentCacheInterface;
use App\Traits\LogsEasyRentEvents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApartmentInvitationController extends Controller
{
    use LogsEasyRentEvents;
    /**
     * Session manager for invitation context
     */
    protected $sessionManager;

    /**
     * Payment integration service
     */
    protected $paymentIntegrationService;

    /**
     * EasyRent Logger
     */
    protected $logger;

    /**
     * Security services
     */
    protected $suspiciousActivityDetector;
    protected $securityBreachResponseService;
    protected $inputValidationService;

    /**
     * Cache service for performance optimization
     */
    protected $cacheService;

    /**
     * Error handling services
     */
    protected $errorHandler;
    protected $recoveryService;
    protected $monitoringService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        PaymentIntegrationService $paymentIntegrationService,
        EasyRentLogger $logger,
        EasyRentCacheInterface $cacheService,
        SuspiciousActivityDetector $suspiciousActivityDetector = null,
        SecurityBreachResponseService $securityBreachResponseService = null,
        InputValidationService $inputValidationService = null,
        EasyRentErrorHandler $errorHandler = null,
        ErrorRecoveryService $recoveryService = null,
        ErrorMonitoringService $monitoringService = null
    ) {
        $this->sessionManager = $sessionManager;
        $this->paymentIntegrationService = $paymentIntegrationService;
        $this->logger = $logger;
        $this->cacheService = $cacheService;
        $this->suspiciousActivityDetector = $suspiciousActivityDetector;
        $this->securityBreachResponseService = $securityBreachResponseService;
        $this->inputValidationService = $inputValidationService;
        $this->errorHandler = $errorHandler;
        $this->recoveryService = $recoveryService;
        $this->monitoringService = $monitoringService;
        
        // Apply security middleware to invitation routes only if services are available
        if ($suspiciousActivityDetector && $securityBreachResponseService) {
            $this->middleware('invitation.rate.limit')->only(['show', 'apply', 'storeSession']);
            $this->middleware('enhanced.csrf')->only(['apply', 'storeSession']);
        }
    }
    /**
     * Generate EasyRent Link for apartment
     */
    public function generateLink(Request $request, Apartment $apartment)
    {
        // Verify landlord owns the apartment
        if ($apartment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this apartment'
            ], 403);
        }

        // Verify apartment is vacant
        if (!$apartment->isVacant()) {
            return response()->json([
                'success' => false,
                'message' => 'This apartment is not vacant and cannot be shared'
            ]);
        }

        try {
            $link = $apartment->generateEasyRentLink(auth()->id(), [
                'expires_at' => $request->expires_at ? 
                    Carbon::parse($request->expires_at) : 
                    now()->addDays(30)
            ]);

            $invitation = $apartment->activeInvitation;

            $this->logger->logInvitationCreation($invitation, auth()->user());

            return response()->json([
                'success' => true,
                'link' => $link,
                'whatsapp_url' => $invitation->getWhatsAppShareUrl(),
                'email_url' => $invitation->getEmailShareUrl(),
                'sms_url' => $invitation->getSMSShareUrl(),
                'expires_at' => $invitation->expires_at->format('M d, Y'),
                'message' => 'EasyRent link generated successfully!'
            ]);

        } catch (\Exception $e) {
            // Use comprehensive error handling
            if ($this->errorHandler && $this->monitoringService) {
                $context = ['apartment_id' => $apartment->id, 'user_id' => auth()->id()];
                $this->monitoringService->trackError($e, $request, 'system', $context);
                
                $errorData = $this->errorHandler->handleSystemError($e, $request, $context);
                return response()->json([
                    'success' => false,
                    'message' => $errorData['user_message'],
                    'error_type' => 'system'
                ], 500);
            }

            // Fallback error handling
            $this->logEasyRentError('Failed to generate EasyRent link', $e, [
                'apartment_id' => $apartment->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate link. Please try again.'
            ], 500);
        }
    }

    /**
     * Show apartment details via invitation link with enhanced security
     */
    public function show(string $token)
    {
        // Validate token format first (only if service is available)
        if ($this->inputValidationService) {
            $tokenValidation = $this->inputValidationService->validateInvitationToken($token);
            if (!$tokenValidation['is_valid']) {
                if ($this->suspiciousActivityDetector) {
                    $this->suspiciousActivityDetector->recordFailedTokenAttempt(request()->ip());
                }
                return view('apartment.invite.invalid-token');
            }
        }

        // Check for emergency lockdown (only if service is available)
        if ($this->securityBreachResponseService && $this->securityBreachResponseService->isEmergencyLockdown()) {
            return view('apartment.invite.security-blocked', [
                'message' => 'System is temporarily unavailable due to security maintenance.',
                'emergency' => true
            ]);
        }

        // Check if IP is blocked for security breach (only if service is available)
        if ($this->securityBreachResponseService && $this->securityBreachResponseService->isIpBlockedForBreach(request()->ip())) {
            $blockInfo = $this->securityBreachResponseService->getIpBlockInfo(request()->ip());
            return view('apartment.invite.security-blocked', [
                'message' => 'Access blocked due to security concerns.',
                'block_info' => $blockInfo
            ]);
        }

        // Try to get invitation data from cache first for better performance
        $cachedInvitationData = $this->cacheService->getCachedInvitationData($token);
        
        if ($cachedInvitationData && isset($cachedInvitationData['invitation'])) {
            // Use cached data to minimize database queries
            $invitation = ApartmentInvitation::find($cachedInvitationData['invitation']['id']);
            
            // Set cached relationships to avoid additional queries
            if ($invitation && isset($cachedInvitationData['apartment_data'])) {
                $apartment = $invitation->apartment;
                $apartment->setRawAttributes($cachedInvitationData['apartment_data']['apartment']);
                
                if (isset($cachedInvitationData['apartment_data']['property'])) {
                    $property = $apartment->property;
                    $property->setRawAttributes($cachedInvitationData['apartment_data']['property']);
                }
            }
        } else {
            // Fallback to optimized database query
            $invitation = ApartmentInvitation::where('invitation_token', $token)
                ->with([
                    'apartment' => function($query) {
                        $query->select([
                            'apartment_id', 'property_id', 'apartment_type', 'apartment_type_id',
                            'amount', 'range_start', 'range_end', 'tenant_id', 'user_id', 'occupied'
                        ]);
                    },
                    'apartment.property' => function($query) {
                        $query->select([
                            'property_id', 'prop_name', 'prop_description', 'prop_address',
                            'prop_state', 'prop_lga', 'prop_type', 'user_id'
                        ]);
                    },
                    'landlord:user_id,first_name,last_name,email,phone'
                ])
                ->select([
                    'id', 'apartment_id', 'landlord_id', 'invitation_token', 'status',
                    'expires_at', 'tenant_user_id', 'access_count', 'last_accessed_at',
                    'total_amount', 'lease_duration', 'move_in_date'
                ])
                ->first();
                
            // Cache the invitation data for future requests
            if ($invitation) {
                $this->cacheService->cacheInvitationData($token);
            }
        }

        if (!$invitation) {
            $this->suspiciousActivityDetector->recordFailedTokenAttempt(request()->ip());
            $this->logSecurityEvent('invitation_not_found', [
                'token' => substr($token, 0, 8) . '...',
            ]);
            return view('apartment.invite.not-found');
        }

        // Analyze request for suspicious activity (only if service is available)
        if ($this->suspiciousActivityDetector && $this->securityBreachResponseService) {
            $suspiciousAnalysis = $this->suspiciousActivityDetector->analyzeRequest(request(), $token);
            
            if ($suspiciousAnalysis['is_suspicious']) {
                $this->suspiciousActivityDetector->handleSuspiciousActivity(
                    request(), 
                    $suspiciousAnalysis, 
                    $token
                );
                
                // Handle security breach if action required
                if ($suspiciousAnalysis['action_required']) {
                    $breachData = [
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'patterns' => $suspiciousAnalysis['patterns'],
                        'risk_score' => $suspiciousAnalysis['risk_score'],
                        'tokens' => [$token],
                        'detected_at' => now()->toISOString(),
                        'request_data' => request()->all()
                    ];
                    
                    $this->securityBreachResponseService->handleSecurityBreach($breachData);
                    
                    return view('apartment.invite.security-blocked', [
                        'message' => 'Suspicious activity detected. Access has been temporarily blocked.'
                    ]);
                }
            }
        }

        // Perform comprehensive security validation
        $securityIssues = $invitation->performSecurityValidation(
            request()->ip(), 
            request()->userAgent()
        );

        // Handle security issues
        if (!empty($securityIssues)) {
            if (in_array('invitation_expired', $securityIssues)) {
                return view('apartment.invite.expired', compact('invitation'));
            }
            
            if (in_array('rate_limit_exceeded', $securityIssues)) {
                $this->logger->logRateLimitExceeded(request(), 'invitation_access');
                return view('apartment.invite.rate-limited', compact('invitation'));
            }
            
            if (in_array('suspicious_activity_detected', $securityIssues)) {
                $this->logger->logSuspiciousActivity(request(), 'Suspicious access pattern detected', [
                    'invitation_id' => $invitation->id,
                    'token' => substr($token, 0, 8) . '...',
                ]);
                $invitation->invalidateForSecurity('Suspicious access pattern detected');
                return view('apartment.invite.security-blocked', compact('invitation'));
            }
            
            if (in_array('token_integrity_failed', $securityIssues)) {
                $this->logSecurityEvent('token_integrity_failed', [
                    'invitation_id' => $invitation->id,
                    'token' => substr($token, 0, 8) . '...',
                ]);
                return view('apartment.invite.invalid-token', compact('invitation'));
            }
        }

        // Check if invitation is already used
        if ($invitation->status === ApartmentInvitation::STATUS_USED) {
            return view('apartment.invite.already-used', compact('invitation'));
        }

        // Mark as viewed (only if security validation passed)
        $invitation->markAsViewed();

        // Log invitation access
        $this->logInvitationAccess($invitation);

        // Get apartment and property details with optimized loading
        $apartment = $invitation->apartment;
        $property = $apartment->property;
        $landlord = $invitation->landlord;
        
        // Use cached apartment data if available, otherwise load with optimized query
        $apartmentData = $this->cacheService->getCachedApartmentData($apartment->apartment_id);
        
        if ($apartmentData) {
            // Use cached amenities and attributes
            if (isset($apartmentData['amenities'])) {
                $property->setRelation('amenities', collect($apartmentData['amenities'])->map(function($name) {
                    return (object)['name' => $name];
                }));
            }
            
            if (isset($apartmentData['attributes'])) {
                $property->setRelation('attributes', collect($apartmentData['attributes'])->map(function($value, $name) {
                    return (object)['attribute_name' => $name, 'attribute_value' => $value];
                }));
            }
        } else {
            // Load with optimized eager loading and cache the result
            $property->load([
                'amenities:amenity_id,name',
                'attributes:id,property_id,attribute_name,attribute_value'
            ]);
            
            // Cache the apartment data for future requests
            $this->cacheService->cacheApartmentData($apartment->apartment_id);
            $property->load('amenities');
            
            // Cache the invitation data for future requests
            $this->cacheService->cacheInvitationData($token);
        }

        // Generate proforma details
        $proformaData = [
            'apartment_id' => $apartment->id,
            'property_name' => $property->prop_name,
            'apartment_type' => $apartment->apartment_type,
            'monthly_rent' => $apartment->amount,
            'duration' => 12, // Default duration
            'total_amount' => $apartment->amount * 12,
            'landlord_name' => $landlord->first_name . ' ' . $landlord->last_name,
            'landlord_email' => $landlord->email,
            'landlord_phone' => $landlord->phone,
        ];

        // Store comprehensive invitation context in session for unauthenticated users
        if (!Auth::check()) {
            $invitationContext = [
                'apartment_id' => $apartment->id,
                'property_id' => $property->property_id,
                'property_name' => $property->prop_name,
                'property_address' => $property->prop_address,
                'apartment_type' => $apartment->apartment_type,
                'monthly_rent' => $apartment->amount,
                'landlord_id' => $landlord->user_id,
                'landlord_name' => $landlord->first_name . ' ' . $landlord->last_name,
                'landlord_email' => $landlord->email,
                'landlord_phone' => $landlord->phone,
                'access_time' => now()->toISOString(),
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
                'viewing_session_id' => session()->getId()
            ];
            
            session([
                'easyrent_invitation_token' => $token,
                'easyrent_redirect_url' => route('apartment.invite.show', $token),
                'easyrent_invitation_context' => $invitationContext
            ]);
            
            // Store in session manager for better tracking
            if (isset($this->sessionManager)) {
                try {
                    $this->sessionManager->storeInvitationContext($token, $invitationContext);
                } catch (\Exception $e) {
                    $this->logEasyRentError('Failed to store invitation context in session manager', $e, [
                        'invitation_token' => substr($token, 0, 8) . '...',
                    ]);
                }
            }
            
            $this->logSessionEvent('invitation_context_stored', [
                'invitation_token' => substr($token, 0, 8) . '...',
                'apartment_id' => $apartment->id,
                'property_name' => $property->prop_name,
            ]);
        }

        return view('apartment.invite.show', compact(
            'invitation', 
            'apartment', 
            'property', 
            'landlord', 
            'proformaData'
        ));
    }

    /**
     * Process tenant application via invitation
     */
    public function apply(Request $request, string $token)
    {
        // Validate and sanitize input (only if service is available)
        if ($this->inputValidationService) {
            $inputValidation = $this->inputValidationService->validateAndSanitizeRequest($request);
            
            if (!$inputValidation['is_safe']) {
                Log::warning('Unsafe input detected in apartment application', [
                    'ip_address' => $request->ip(),
                    'threats' => $inputValidation['threats_detected'],
                    'blocked_fields' => $inputValidation['blocked_fields'],
                    'token' => substr($token, 0, 8) . '...'
                ]);
                
                // If critical threats detected, block the request
                $criticalThreats = ['xss_attempt', 'sql_injection_attempt', 'command_injection_attempt'];
                $hasCriticalThreats = !empty(array_intersect($inputValidation['threats_detected'], $criticalThreats));
                
                if ($hasCriticalThreats) {
                    if ($this->suspiciousActivityDetector) {
                        $this->suspiciousActivityDetector->recordFailedTokenAttempt($request->ip());
                    }
                    return response()->view('apartment.invite.security-blocked', [
                        'message' => 'Invalid input detected. Please check your data and try again.'
                    ], 400);
                }
            }
            
            // Use sanitized input for processing
            $request->merge($inputValidation['sanitized_input']);
        }

        $invitation = ApartmentInvitation::where('invitation_token', $token)
            ->with(['apartment.property', 'landlord'])
            ->first();

        if (!$invitation || !$invitation->isActive()) {
            return redirect()->route('apartment.invite.expired', $token);
        }

        // Check if user is authenticated
        if (!Auth::check()) {
            // Store comprehensive application data in session for unauthenticated users
            $applicationData = [
                'duration' => $request->input('duration'),
                'move_in_date' => $request->input('move_in_date'),
                'application_timestamp' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'apartment_id' => $invitation->apartment_id,
                'landlord_id' => $invitation->landlord_id,
                'total_amount' => $invitation->apartment->amount * $request->input('duration', 12)
            ];
            
            // Store in multiple session keys for compatibility
            session([
                'easyrent_application_data' => $applicationData,
                'easyrent_invitation_token' => $token,
                'easyrent_redirect_url' => route('apartment.invite.apply', $token),
                'application_attempt_data' => $applicationData // New structured format
            ]);
            
            // Store in session manager for better tracking
            if (isset($this->sessionManager)) {
                try {
                    $this->sessionManager->storeApplicationData($token, $applicationData);
                } catch (\Exception $e) {
                    Log::error('Failed to store application data in session manager', [
                        'invitation_token' => substr($token, 0, 8) . '...',
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::info('Application attempt by unauthenticated user stored in session', [
                'invitation_token' => substr($token, 0, 8) . '...',
                'apartment_id' => $invitation->apartment_id,
                'duration' => $request->input('duration'),
                'ip_address' => $request->ip()
            ]);
            
            return redirect()->route('login', ['invitation_redirect' => true])
                ->with('info', 'Please login or register to complete your apartment application. Your application details have been saved.');
        }

        $user = Auth::user();
        $apartment = $invitation->apartment;
        
        // Check if user has stored application data from previous attempt
        $storedApplicationData = session('easyrent_application_data') ?? session('application_attempt_data');
        
        // Use stored data if available and no new data provided
        if ($storedApplicationData && !$request->has('duration')) {
            $duration = $storedApplicationData['duration'] ?? 12;
            $moveInDate = $storedApplicationData['move_in_date'] ?? date('Y-m-d', strtotime('+7 days'));
            
            // Clear stored data after use
            session()->forget(['easyrent_application_data', 'application_attempt_data']);
            
            Log::info('Using stored application data for authenticated user', [
                'user_id' => $user->user_id,
                'invitation_token' => substr($token, 0, 8) . '...',
                'duration' => $duration
            ]);
        } else {
            // Validate new form data
            $request->validate([
                'duration' => 'required|integer|min:1|max:24',
                'move_in_date' => 'required|date|after:today'
            ]);
            
            $duration = $request->duration;
            $moveInDate = $request->move_in_date;
        }
        
        $totalAmount = $apartment->amount * $duration;

        try {
            // Update invitation with application details
            $invitation->update([
                'tenant_user_id' => $user->user_id,
                'prospect_name' => $user->first_name . ' ' . $user->last_name,
                'prospect_email' => $user->email,
                'prospect_phone' => $user->phone,
                'lease_duration' => $duration,
                'move_in_date' => $moveInDate,
                'total_amount' => $totalAmount
            ]);

            // Mark payment initiated
            $invitation->markPaymentInitiated();

            // Create payment record using the integration service
            $applicationData = [
                'duration' => $duration,
                'move_in_date' => $moveInDate,
                'total_amount' => $totalAmount
            ];
            
            $payment = $this->paymentIntegrationService->createInvitationPayment(
                $invitation, 
                $user, 
                $applicationData
            );

            // Send application emails
            $this->sendApplicationEmails($invitation, $payment);

            Log::info('Tenant application submitted', [
                'invitation_id' => $invitation->id,
                'tenant_id' => $user->user_id,
                'payment_id' => $payment->id
            ]);

            return redirect()->route('apartment.invite.payment', [
                'token' => $token,
                'payment_id' => $payment->id
            ])->with('success', 'Application submitted successfully! Please complete payment to secure your apartment.');

        } catch (\Exception $e) {
            // Use comprehensive error handling
            if ($this->errorHandler && $this->recoveryService && $this->monitoringService) {
                $context = [
                    'invitation_id' => $invitation->id,
                    'user_id' => $user->user_id,
                    'apartment_id' => $invitation->apartment_id,
                    'application_data' => compact('duration', 'moveInDate', 'totalAmount')
                ];
                
                $this->monitoringService->trackError($e, $request, 'system', $context);
                $errorData = $this->errorHandler->handleSystemError($e, $request, $context);
                $recoveryResult = $this->recoveryService->recoverFromSystemError($errorData, $request);
                
                return back()
                    ->with('error', $errorData['user_message'])
                    ->with('recovery_options', $errorData['alternative_actions'] ?? [])
                    ->with('support_reference', $this->generateSupportReference($context));
            }

            // Fallback error handling
            Log::error('Failed to process apartment application', [
                'invitation_id' => $invitation->id,
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to submit application. Please try again.');
        }
    }

    /**
     * Show payment page for apartment
     */
    public function payment(string $token, Payment $payment)
    {
        $invitation = ApartmentInvitation::where('invitation_token', $token)->first();
        
        if (!$invitation || !Auth::check()) {
            return redirect()->route('apartment.invite.show', $token);
        }

        // Verify payment belongs to current user
        if ($payment->tenant_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this payment');
        }

        return view('apartment.invite.payment', compact('invitation', 'payment'));
    }

    /**
     * Handle payment completion callback
     */
    public function paymentCallback(Request $request, string $token)
    {
        $invitation = ApartmentInvitation::where('invitation_token', $token)->first();
        
        if (!$invitation) {
            return response()->json(['success' => false, 'message' => 'Invalid invitation']);
        }

        $payment = Payment::where('payment_reference', 'easyrent_' . $token)->first();
        
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found']);
        }

        try {
            // Process payment through integration service
            $result = $this->paymentIntegrationService->processInvitationPayment(
                $payment, 
                $request->all()
            );

            if ($result['success']) {
                Log::info('Apartment payment completed and assigned via callback', [
                    'invitation_id' => $invitation->id,
                    'payment_id' => $payment->id,
                    'apartment_id' => $invitation->apartment_id,
                    'tenant_id' => $payment->tenant_id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed successfully!',
                    'redirect_url' => route('apartment.invite.success', $token)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment processing failed: ' . $result['error']
                ]);
            }

        } catch (\Exception $e) {
            // Use comprehensive error handling for payment errors
            if ($this->errorHandler && $this->recoveryService && $this->monitoringService) {
                $context = [
                    'invitation_id' => $invitation->id,
                    'payment_id' => $payment->id,
                    'payment_reference' => $payment->payment_reference ?? null,
                    'amount' => $payment->amount ?? null
                ];
                
                $this->monitoringService->trackError($e, $request, 'payment', $context);
                $errorData = $this->errorHandler->handlePaymentError($e, $request, $context);
                $recoveryResult = $this->recoveryService->recoverFromPaymentError($errorData, $request, $payment);
                
                return response()->json([
                    'success' => false,
                    'error_type' => 'payment',
                    'message' => $errorData['user_message'],
                    'recovery_options' => $errorData['recovery_options'],
                    'support_reference' => $errorData['support_reference'] ?? null
                ]);
            }

            // Fallback error handling
            Log::error('Failed to process payment completion callback', [
                'invitation_id' => $invitation->id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment completion'
            ]);
        }
    }

    /**
     * Show success page after payment
     */
    public function success(string $token)
    {
        $invitation = ApartmentInvitation::where('invitation_token', $token)
            ->with(['apartment.property', 'landlord', 'tenant'])
            ->first();

        if (!$invitation || $invitation->status !== ApartmentInvitation::STATUS_USED) {
            return redirect()->route('apartment.invite.show', $token);
        }

        return view('apartment.invite.success', compact('invitation'));
    }

    /**
     * Send application emails to landlord and tenant
     */
    private function sendApplicationEmails($invitation, $payment)
    {
        try {
            // Email to landlord
            Mail::to($invitation->landlord->email)->send(
                new TenantApplicationMail($invitation, $payment, 'landlord')
            );

            // Email to tenant
            Mail::to($invitation->prospect_email)->send(
                new TenantApplicationMail($invitation, $payment, 'tenant')
            );

        } catch (\Exception $e) {
            Log::error('Failed to send application emails', [
                'invitation_id' => $invitation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send completion emails after successful payment
     */
    private function sendCompletionEmails($invitation, $payment)
    {
        try {
            // Email to landlord
            Mail::to($invitation->landlord->email)->send(
                new ApartmentAssignedMail($invitation, $payment, 'landlord')
            );

            // Email to tenant
            Mail::to($invitation->prospect_email)->send(
                new ApartmentAssignedMail($invitation, $payment, 'tenant')
            );

        } catch (\Exception $e) {
            Log::error('Failed to send completion emails', [
                'invitation_id' => $invitation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store session data for unauthenticated users
     */
    public function storeSession(Request $request)
    {
        try {
            // Validate and sanitize input (only if service is available)
            $sanitizedInput = $request->all();
            if ($this->inputValidationService) {
                $inputValidation = $this->inputValidationService->validateAndSanitizeRequest($request);
                
                if (!$inputValidation['is_safe']) {
                    Log::warning('Unsafe input detected in session storage', [
                        'ip_address' => $request->ip(),
                        'threats' => $inputValidation['threats_detected'],
                        'blocked_fields' => $inputValidation['blocked_fields']
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid input detected'
                    ], 400);
                }
                
                // Use sanitized input
                $sanitizedInput = $inputValidation['sanitized_input'];
            }
            $token = $sanitizedInput['token'] ?? null;
            $applicationData = $sanitizedInput['application_data'] ?? null;
            
            if (!$token || !$applicationData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required data'
                ], 400);
            }
            
            // Validate invitation exists
            $invitation = ApartmentInvitation::where('invitation_token', $token)->first();
            if (!$invitation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid invitation token'
                ], 404);
            }
            
            // Store in session
            session([
                'easyrent_application_data' => $applicationData,
                'easyrent_invitation_token' => $token,
                'easyrent_redirect_url' => route('apartment.invite.show', $token)
            ]);
            
            // Store in session manager
            if (isset($this->sessionManager)) {
                $this->sessionManager->storeApplicationData($token, $applicationData);
            }
            
            Log::info('Application data stored in session for unauthenticated user', [
                'invitation_token' => substr($token, 0, 8) . '...',
                'apartment_id' => $applicationData['apartment_id'] ?? null,
                'duration' => $applicationData['duration'] ?? null
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Application data stored successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to store session data', [
                'error' => $e->getMessage(),
                'token' => substr($request->input('token', ''), 0, 8) . '...'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to store session data'
            ], 500);
        }
    }

    /**
     * Get invitation statistics for landlord
     */
    public function invitationStats(Apartment $apartment)
    {
        if ($apartment->user_id !== auth()->id()) {
            abort(403);
        }

        $stats = [
            'total_invitations' => $apartment->invitations()->count(),
            'active_invitations' => $apartment->invitations()->active()->count(),
            'views' => $apartment->invitations()->whereNotNull('viewed_at')->count(),
            'applications' => $apartment->invitations()->whereNotNull('payment_initiated_at')->count(),
            'completed' => $apartment->invitations()->where('status', 'used')->count()
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * Generate support reference for error tracking
     */
    private function generateSupportReference(array $context): string
    {
        return 'ER-' . date('Ymd') . '-' . substr(md5(json_encode($context) . time()), 0, 8);
    }
}