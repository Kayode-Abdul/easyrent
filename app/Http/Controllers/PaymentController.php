<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Apartment;
use App\Models\ProfomaReceipt;
use App\Models\ApartmentInvitation;
use App\Models\User;
use App\Services\Payment\PaymentIntegrationService;
use App\Services\Payment\PaymentCalculationServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Unicodeveloper\Paystack\Facades\Paystack;
use App\Mail\PaymentReceiptMail;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsEasyRentEvents;

class PaymentController extends Controller
{
    use LogsEasyRentEvents;
    protected $paymentIntegrationService;
    protected $paymentCalculationService;

    public function __construct(
        PaymentIntegrationService $paymentIntegrationService,
        PaymentCalculationServiceInterface $paymentCalculationService
    ) {
        $this->paymentIntegrationService = $paymentIntegrationService;
        $this->paymentCalculationService = $paymentCalculationService;
    }
    public function index(Request $request)
    {
        $query = Payment::query();

        // Apply date filters
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Handle export
        if ($request->has('export')) {
            return $this->exportPayments($query, $request->export_format);
        }

        $payments = $query->latest()->paginate(15);
        return view('payments.index', compact('payments'));
    }

    /**
     * Show payment form for proforma
     */
    public function showProformaPaymentForm($id)
    {
        $proforma = ProfomaReceipt::findOrFail($id);
        
        // Ensure only the tenant can access their payment form
        if (auth()->user()->user_id !== $proforma->tenant_id) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        return view('proforma.payment', compact('proforma'));
    }

    /**
     * Redirect the User to Paystack Payment Page
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGateway(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'email' => 'required|email',
                'amount' => 'required|numeric|min:0.01',
                'metadata' => 'required'
            ]);
            
            // Parse metadata
            $metadata = json_decode($request->metadata, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid metadata JSON in payment request', [
                    'metadata' => $request->metadata,
                    'json_error' => json_last_error_msg()
                ]);
                return back()->withError('Invalid payment metadata format');
            }
            
            // Validate payment amount using calculation service
            $validationResult = $this->validatePaymentAmount($request->amount, $metadata);
            if (!$validationResult['valid']) {
                Log::warning('Payment amount validation failed', [
                    'requested_amount' => $request->amount,
                    'metadata' => $metadata,
                    'error' => $validationResult['error']
                ]);
                return back()->withError('Payment amount validation failed: ' . $validationResult['error']);
            }
            
            // Log calculation details for audit
            $this->logPaymentInitiation($request->amount, $metadata, $validationResult);
            
            // Check if this is an apartment invitation payment or proforma payment
            if (isset($metadata['invitation_token'])) {
                // Handle apartment invitation payment
                return $this->handleApartmentInvitationPayment($request, $metadata);
            } else {
                // Handle proforma payment (existing logic)
                return $this->handleProformaPayment($request, $metadata);
            }
        } catch(\Exception $e) {
            Log::error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->only(['email', 'amount'])
            ]);
            return back()->withError('The payment could not be initialized. Please try again.');
        }
    }

    /**
     * Handle apartment invitation payment
     */
    private function handleApartmentInvitationPayment(Request $request, array $metadata)
    {
        $invitationToken = $metadata['invitation_token'];
        
        // Find the apartment invitation
        $invitation = \App\Models\ApartmentInvitation::where('invitation_token', $invitationToken)->firstOrFail();
        
        // Validate payment amount against apartment pricing
        $apartment = $invitation->apartment;
        $duration = $invitation->lease_duration ?? 12;
        
        $calculationResult = $this->paymentCalculationService->calculatePaymentTotal(
            $apartment->amount,
            $duration,
            $apartment->getPricingType()
        );
        
        if (!$calculationResult->isValid) {
            throw new \Exception('Payment calculation failed: ' . $calculationResult->errorMessage);
        }
        
        // Validate that the requested amount matches the calculated amount
        $expectedAmount = $calculationResult->totalAmount;
        $requestedAmount = $request->amount;
        
        if (abs($expectedAmount - $requestedAmount) > 0.01) { // Allow for minor rounding differences
            Log::error('Payment amount mismatch for invitation', [
                'invitation_token' => $invitationToken,
                'expected_amount' => $expectedAmount,
                'requested_amount' => $requestedAmount,
                'calculation_method' => $calculationResult->calculationMethod
            ]);
            throw new \Exception('Payment amount does not match expected calculation');
        }
        
        // Log calculation for audit purposes
        $this->paymentCalculationService->logCalculationSteps($calculationResult);
        
        // Find or create the payment record
        $payment = \App\Models\Payment::where('payment_id', $request->payment_id)->first();
        if (!$payment) {
            throw new \Exception('Payment record not found');
        }
        
        // Store calculation details in payment metadata
        $payment->payment_meta = json_encode([
            'invitation_token' => $invitationToken,
            'calculation_method' => $calculationResult->calculationMethod,
            'calculation_steps' => $calculationResult->calculationSteps,
            'validated_amount' => $expectedAmount
        ]);
        
        // Use the reference from the form or generate a new one
        $reference = $request->reference ?? \Unicodeveloper\Paystack\Paystack::genTranxRef();
        
        // Update payment with reference
        $payment->payment_reference = $reference;
        $payment->save();
        
        // Prepare payment data for Paystack
        $data = [
            "amount" => $request->amount * 100, // Convert to kobo
            "reference" => $reference,
            "email" => $request->email,
            "currency" => $request->currency ?? "NGN",
            "callback_url" => $request->callback_url,
            "metadata" => $metadata
        ];

        return \Unicodeveloper\Paystack\Paystack::getAuthorizationUrl($data)->redirectNow();
    }

    /**
     * Handle proforma payment (existing logic)
     */
    private function handleProformaPayment(Request $request, array $metadata)
    {
        $proformaId = $metadata['proforma_id'] ?? null;
        
        // Find the proforma receipt
        $proforma = ProfomaReceipt::findOrFail($proformaId);
        $apartment = Apartment::with('apartmentType')->where('apartment_id', $proforma->apartment_id)->firstOrFail();
        
        // Validate payment amount against proforma calculation
        $calculationResult = $this->paymentCalculationService->calculatePaymentTotal(
            $apartment->amount,
            $proforma->duration ?? 12,
            $apartment->getPricingType()
        );
        
        if (!$calculationResult->isValid) {
            throw new \Exception('Payment calculation failed: ' . $calculationResult->errorMessage);
        }
        
        // Validate that the requested amount matches the calculated amount
        $expectedAmount = $calculationResult->totalAmount;
        $requestedAmount = $request->amount / 100; // Convert from kobo to naira for comparison
        
        if (abs($expectedAmount - $requestedAmount) > 0.01) { // Allow for minor rounding differences
            Log::error('Payment amount mismatch for proforma', [
                'proforma_id' => $proformaId,
                'expected_amount' => $expectedAmount,
                'requested_amount' => $requestedAmount,
                'calculation_method' => $calculationResult->calculationMethod
            ]);
            throw new \Exception('Payment amount does not match proforma calculation');
        }
        
        // Log calculation for audit purposes
        $this->paymentCalculationService->logCalculationSteps($calculationResult);
        
        // Store calculation details in proforma
        $proforma->calculation_method = $calculationResult->calculationMethod;
        $proforma->calculation_log = $calculationResult->calculationSteps;
        
        // Use the reference from the form or generate a new one
        $reference = $request->reference ?? \Unicodeveloper\Paystack\Paystack::genTranxRef();

        // Persist reference on the related proforma so callback can locate it reliably
        $proforma->transaction_id = $reference;
        $proforma->save();
        
        // Prepare payment data
        $data = [
            "amount" => $request->amount, // Amount is already in kobo from the form
            "reference" => $reference,
            "email" => $request->email,
            "currency" => $request->currency ?? "NGN",
            "metadata" => $metadata
        ];

        return \Unicodeveloper\Paystack\Paystack::getAuthorizationUrl($data)->redirectNow();
    }

    /**
     * Obtain Paystack payment information
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGatewayCallback(Request $request)
    {
        try {
            $this->logPaymentEvent('payment_callback_received', new Payment(), [
                'request_method' => $request->method(),
                'has_reference' => $request->has('reference'),
            ]);
            
            // Set the base URL for Paystack API
            config(['paystack.paymentUrl' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co')]);
            
            // Get the transaction reference from the request
            // Paystack sends the reference as 'reference' in the request body
            $transactionReference = $request->input('reference') ?? $request->query('reference') ?? $request->query('trxref');
            
            if (!$transactionReference) {
                $this->logEasyRentError('No transaction reference found in callback', new \Exception('Missing transaction reference'), []);
                return redirect('/dashboard')->with('error', 'Payment verification failed: No transaction reference');
            }
            
            // Temporarily set the reference in the request for the Paystack library
            // The library expects 'trxref' in the query parameters
            $request->query->set('trxref', $transactionReference);
            
            Log::info('Attempting to get payment data from Paystack', ['reference' => $transactionReference]);
            
            // Create a custom Paystack instance and verify the transaction
            $paystack = app(\Unicodeveloper\Paystack\Paystack::class);
            
            // Manually verify the transaction to avoid the getPaymentData() issue
            $client = new \GuzzleHttp\Client();
            $secretKey = config('paystack.secretKey');
            
            try {
                $response = $client->get(
                    config('paystack.paymentUrl') . '/transaction/verify/' . $transactionReference,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $secretKey,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json'
                        ]
                    ]
                );
                
                $paymentDetails = json_decode($response->getBody(), true);
                
                Log::info('Payment details retrieved', ['status' => $paymentDetails['status'] ?? 'unknown']);
                
                // Check if verification was successful
                if (!$paymentDetails['status'] || $paymentDetails['data']['status'] !== 'success') {
                    throw new \Exception('Transaction verification failed');
                }
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $errorResponse = json_decode($e->getResponse()->getBody(), true);
                Log::error('Paystack API error', [
                    'reference' => $transactionReference,
                    'error' => $errorResponse['message'] ?? 'Unknown error',
                    'status_code' => $e->getResponse()->getStatusCode()
                ]);
                
                // For test references, we'll simulate a successful response for testing purposes
                if (strpos($transactionReference, 'test_') === 0) {
                    Log::info('Test reference detected, simulating success');
                    $paymentDetails = [
                        'status' => true,
                        'data' => [
                            'status' => 'success',
                            'reference' => $transactionReference,
                            'amount' => 100000, // 1000 NGN in kobo
                            'metadata' => [
                                'proforma_id' => 1,
                                'custom_fields' => [
                                    ['display_name' => 'Apartment ID', 'variable_name' => 'apartment_id', 'value' => 'APT001']
                                ]
                            ]
                        ]
                    ];
                } else {
                    throw new \Exception('Transaction verification failed: ' . ($errorResponse['message'] ?? 'Unknown error'));
                }
            }
            
            // Check if payment was successful
            if ($paymentDetails['status'] && $paymentDetails['data']['status'] === 'success') {
                
                // Log the entire payment process for debugging
                $this->logPaymentEvent('payment_callback_processing_start', new Payment(), [
                    'reference' => $transactionReference,
                    'payment_status' => $paymentDetails['data']['status'] ?? 'unknown',
                ]);
                    $reference = $paymentDetails['data']['reference'];
                    $metadata = $paymentDetails['data']['metadata'];
                    
                    Log::info('Payment successful', [
                        'reference' => $reference,
                        'amount' => $paymentDetails['data']['amount'],
                        'metadata' => $metadata
                    ]);
                    
                    // Handle test references
                    if (str_starts_with($reference, 'test_')) {
                        // For test references, create a mock proforma if it doesn't exist
                        $proforma = ProfomaReceipt::where('transaction_id', $reference)->first();
                        
                        if (!$proforma) {
                            Log::info('Creating test proforma receipt', ['reference' => $reference]);
                            
                            // Create a test tenant if needed
                            $testTenant = \App\Models\User::where('email', 'test@example.com')->first();
                            if (!$testTenant) {
                                $testTenant = new \App\Models\User();
                                $testTenant->first_name = 'Test';
                                $testTenant->last_name = 'Tenant';
                                $testTenant->username = 'testtenant';
                                $testTenant->email = 'test@example.com';
                                $testTenant->user_id = 999001; // Numeric ID for test tenant
                                $testTenant->role = 1; // Tenant role ID
                                $testTenant->password = bcrypt('password');
                                $testTenant->save();
                            }
                            
                            // Create a test landlord if needed
                            $testLandlord = \App\Models\User::where('email', 'landlord@example.com')->first();
                            if (!$testLandlord) {
                                $testLandlord = new \App\Models\User();
                                $testLandlord->first_name = 'Test';
                                $testLandlord->last_name = 'Landlord';
                                $testLandlord->username = 'testlandlord';
                                $testLandlord->email = 'landlord@example.com';
                                $testLandlord->user_id = 999002; // Numeric ID for test landlord
                                $testLandlord->role = 2; // Landlord role ID
                                $testLandlord->password = bcrypt('password');
                                $testLandlord->save();
                            }
                            
                            // Create a test apartment if needed
                            $testApartment = Apartment::where('apartment_id', 999001)->first();
                            if (!$testApartment) {
                                $testApartment = new Apartment();
                                $testApartment->apartment_id = 999001; // Numeric ID
                                $testApartment->property_id = 999001; // Numeric property ID
                                $testApartment->apartment_type = 'Test Apartment';
                                $testApartment->occupied = false;
                                $testApartment->amount = 1000; // 1000 naira
                                $testApartment->user_id = $testLandlord->user_id; // Set landlord as owner
                                $testApartment->save();
                            }
                            
                            // Create the test proforma
                            $proforma = new ProfomaReceipt();
                            $proforma->transaction_id = $reference;
                            $proforma->tenant_id = $testTenant->user_id;
                            $proforma->user_id = $testLandlord->user_id;
                            $proforma->apartment_id = $testApartment->apartment_id;
                            $proforma->amount = $paymentDetails['data']['amount'] / 100;
                            $proforma->duration = 12; // 12 months
                            $proforma->status = ProfomaReceipt::STATUS_NEW;
                            $proforma->save();
                            
                            Log::info('Test proforma receipt created', ['proforma_id' => $proforma->id]);
                        }
                    } else {
                        // For real references, find the actual proforma receipt
                        $proforma = ProfomaReceipt::where('transaction_id', $reference)->first();
                        
                        // Fallback: if transaction_id isn't set, use proforma_id carried in Paystack metadata
                        if (!$proforma && !empty($metadata['proforma_id'])) {
                            $proforma = ProfomaReceipt::find($metadata['proforma_id']);
                        }
                        
                        if (!$proforma) {
                            Log::error('Proforma receipt not found for transaction', ['reference' => $reference, 'metadata' => $metadata]);
                            return redirect('/dashboard')->with('error', 'Payment verification failed: Receipt not found');
                        }
                    }
                
                Log::info('Proforma receipt found', ['proforma_id' => $proforma->id]);
                
                $apartment = Apartment::with('apartmentType')->where('apartment_id', $proforma->apartment_id)->first();
                
                if (!$apartment) {
                    Log::error('Apartment not found for proforma', [
                        'proforma_id' => $proforma->id, 
                        'apartment_id' => $proforma->apartment_id
                    ]);
                    return redirect('/dashboard')->with('error', 'Payment verification failed: Apartment not found');
                }
                
                Log::info('Apartment found', ['apartment_id' => $apartment->apartment_id]);
                
                // Create payment record
                try {
                    Log::info('Creating payment record', [
                        'transaction_id' => $reference,
                        'amount' => $paymentDetails['data']['amount'] / 100,
                        'tenant_id' => $proforma->tenant_id,
                        'landlord_id' => $proforma->user_id,
                        'apartment_id' => $proforma->apartment_id,
                        'duration' => $proforma->duration
                    ]);
                    
                    // Check if payment already exists to avoid duplicates
                    $existingPayment = Payment::where('transaction_id', $reference)->first();
                    if ($existingPayment) {
                        Log::info('Payment already exists', ['payment_id' => $existingPayment->id]);
                        
                        // Validate existing payment amount against current calculation
                        $validationResult = $this->validateExistingPaymentAmount($existingPayment);
                        if (!$validationResult['valid']) {
                            Log::warning('Existing payment amount validation failed', [
                                'payment_id' => $existingPayment->id,
                                'validation_error' => $validationResult['error']
                            ]);
                        }
                        
                        // Check if this is an invitation-based payment that needs processing
                        if ($this->isInvitationBasedPayment($existingPayment)) {
                            $result = $this->paymentIntegrationService->processInvitationPayment(
                                $existingPayment, 
                                $paymentDetails['data']
                            );
                            
                            if ($result['success']) {
                                return $result['apartment_assigned']
                                    ? redirect()->route('apartment.invite.success', $result['invitation']->invitation_token)
                                        ->with('success', 'Payment completed and apartment assigned successfully!')
                                    : redirect()->route('register', ['invitation_token' => $result['invitation']->invitation_token])
                                        ->with('success', 'Payment completed. Please register to finalize your apartment.');
                            }
                        }
                        
                        return redirect()->route('payment.receipt', ['id' => $existingPayment->id])->with('success', 'Payment was already processed!');
                    }
                    
                    // Validate required data before creating payment
                    if (!$proforma->tenant_id || !$proforma->user_id || !$proforma->apartment_id) {
                        throw new \Exception('Missing required proforma data: tenant_id=' . $proforma->tenant_id . ', landlord_id=' . $proforma->user_id . ', apartment_id=' . $proforma->apartment_id);
                    }
                    
                    // Validate payment amount against calculation service
                    $paymentAmount = $paymentDetails['data']['amount'] / 100; // Convert from kobo to naira
                    $calculationResult = $this->paymentCalculationService->calculatePaymentTotal(
                        $apartment->amount,
                        $proforma->duration ?? 12,
                        $apartment->getPricingType()
                    );
                    
                    if (!$calculationResult->isValid) {
                        throw new \Exception('Payment calculation validation failed: ' . $calculationResult->errorMessage);
                    }
                    
                    // Validate that the payment amount matches the calculated amount
                    $expectedAmount = $calculationResult->totalAmount;
                    $tolerance = 0.01; // Allow for minor rounding differences
                    
                    if (abs($expectedAmount - $paymentAmount) > $tolerance) {
                        Log::error('Payment amount discrepancy detected', [
                            'transaction_id' => $reference,
                            'expected_amount' => $expectedAmount,
                            'received_amount' => $paymentAmount,
                            'difference' => abs($expectedAmount - $paymentAmount),
                            'calculation_method' => $calculationResult->calculationMethod
                        ]);
                        
                        // For now, log the discrepancy but continue processing
                        // In production, you might want to halt processing or flag for review
                    }
                    
                    // Log calculation for audit purposes
                    $this->paymentCalculationService->logCalculationSteps($calculationResult);
                    
                    // Use DB transaction to ensure data consistency
                    DB::beginTransaction();
                    
                    $payment = new Payment();
                    $payment->transaction_id = $reference;
                    $payment->payment_reference = $reference;
                    $payment->amount = $paymentAmount;
                    $payment->tenant_id = $proforma->tenant_id;
                    $payment->landlord_id = $proforma->user_id;
                    $payment->apartment_id = $proforma->apartment_id;
                    $payment->status = 'completed';
                    $payment->payment_method = $paymentDetails['data']['channel'] ?? 'card';
                    $payment->duration = $proforma->duration ?? 12;
                    $payment->paid_at = now();
                    
                    // Store calculation details in payment metadata
                    $payment->payment_meta = json_encode([
                        'calculation_method' => $calculationResult->calculationMethod,
                        'calculation_steps' => $calculationResult->calculationSteps,
                        'expected_amount' => $expectedAmount,
                        'amount_validated' => abs($expectedAmount - $paymentAmount) <= $tolerance,
                        'paystack_data' => [
                            'channel' => $paymentDetails['data']['channel'] ?? null,
                            'gateway_response' => $paymentDetails['data']['gateway_response'] ?? null
                        ]
                    ]);
                    
                    Log::info('About to save payment', ['payment_data' => $payment->toArray()]);
                    
                    $saved = $payment->save();
                    
                    if (!$saved) {
                        throw new \Exception('Failed to save payment record - save() returned false');
                    }
                    
                    Log::info('Payment record created successfully', ['payment_id' => $payment->id, 'payment_data' => $payment->fresh()->toArray()]);
                    
                    // Check if this is an invitation-based payment
                    if ($this->isInvitationBasedPayment($payment)) {
                        // Process through invitation payment service
                        $result = $this->paymentIntegrationService->processInvitationPayment(
                            $payment, 
                            $paymentDetails['data']
                        );
                        
                        if ($result['success']) {
                            DB::commit();

                            Log::info('Invitation payment processed successfully', [
                                'payment_id' => $payment->id,
                                'invitation_id' => $result['invitation']->id,
                                'apartment_assigned' => $result['apartment_assigned'] ?? null,
                            ]);

                            // If apartment was assigned, proceed to success; otherwise send user to register
                            if (!empty($result['apartment_assigned'])) {
                                // Generate receipt
                                $receiptFile = $this->generateReceipt($payment);

                                return redirect()->route('apartment.invite.success', $result['invitation']->invitation_token)
                                    ->with('success', 'Payment completed and apartment assigned successfully!');
                            }

                            return redirect()->route('register', ['invitation_token' => $result['invitation']->invitation_token])
                                ->with('success', 'Payment completed. Please register to finalize your apartment.');
                        } else {
                            // Handle invitation payment failure
                            DB::rollBack();
                            return redirect('/dashboard')->with('error', 'Payment was received but apartment assignment failed: ' . $result['error']);
                        }
                    } else {
                        // Handle regular proforma payment
                        // Update apartment details
                        $apartment->update([
                            'tenant_id' => $proforma->tenant_id,
                            'occupied' => true,
                            'range_start' => now(),
                            'range_end' => now()->addMonths($proforma->duration)
                        ]);

                        // Update proforma status
                        $proforma->update(['status' => ProfomaReceipt::STATUS_CONFIRMED]);
                        
                        DB::commit();
                        
                        Log::info('Regular payment processing completed successfully');
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to create payment record', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'reference' => $reference,
                        'proforma_data' => $proforma ? $proforma->toArray() : null,
                        'apartment_data' => $apartment ? $apartment->toArray() : null
                    ]);
                    
                    // Try to save a minimal payment record for debugging
                    try {
                        Log::info('Attempting to save minimal payment record for debugging');
                        $debugPayment = new Payment();
                        $debugPayment->transaction_id = $reference . '_debug';
                        $debugPayment->amount = $paymentDetails['data']['amount'] / 100;
                        $debugPayment->tenant_id = $proforma->tenant_id ?? 0;
                        $debugPayment->landlord_id = $proforma->user_id ?? 0;
                        $debugPayment->apartment_id = $proforma->apartment_id ?? 0;
                        $debugPayment->status = 'failed';
                        $debugPayment->payment_method = 'debug';
                        $debugPayment->duration = 1;
                        $debugPayment->save();
                        Log::info('Debug payment record saved', ['debug_payment_id' => $debugPayment->id]);
                    } catch (\Exception $debugE) {
                        Log::error('Even debug payment failed', ['debug_error' => $debugE->getMessage()]);
                    }
                    
                    return redirect('/dashboard')->with('error', 'Payment was received but could not be recorded. Please contact support with reference: ' . $reference . '. Error: ' . $e->getMessage());
                }
                
                Log::info('=== PAYMENT CALLBACK PROCESSING END ===', [
                    'reference' => $reference,
                    'success' => true
                ]);
                
                // Generate receipt
                $receiptFile = $this->generateReceipt($payment);
                
                return redirect()->route('payment.receipt', ['id' => $payment->id])->with('success', 'Payment was successful! Your receipt has been generated.');
            }

            Log::warning('Payment verification failed', ['payment_details' => $paymentDetails ?? null]);
            return redirect('/dashboard')->with('error', 'Payment failed!');
        } catch(\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'reference' => $transactionReference ?? 'unknown'
            ]);
            
            // Try to create a minimal payment record for failed callbacks
            try {
                if (isset($transactionReference)) {
                    $this->createFallbackPayment($transactionReference, $e->getMessage());
                }
            } catch (\Exception $fallbackE) {
                Log::error('Fallback payment creation also failed', ['error' => $fallbackE->getMessage()]);
            }
            
            return redirect('/dashboard')->with('error', 'An error occurred while processing your payment. Reference: ' . ($transactionReference ?? 'unknown'));
        }
    }

    /**
     * Generate and save receipt PDF
     */
    private function generateReceipt($payment)
    {
        try {
            // Load payment with relationships
            $payment = Payment::with(['tenant', 'landlord', 'apartment.property'])
                ->where('id', $payment->id)
                ->firstOrFail();
            
            // Add calculation details to payment for receipt display
            $calculationDetails = $this->getPaymentCalculationDetails($payment);
            $payment->calculation_details = $calculationDetails;
                
            $pdf = Pdf::loadView('payments.receipt', compact('payment'));
            $filename = 'receipt_' . $payment->transaction_id . '.pdf';
            Storage::put('receipts/' . $filename, $pdf->output());
            
            // Send receipt via email to tenant
            if ($payment->tenant && $payment->tenant->email) {
                Mail::to($payment->tenant->email)->send(new PaymentReceiptMail($payment));
            }
            
            // Send landlord-specific notification email
            if ($payment->landlord && $payment->landlord->email) {
                Mail::to($payment->landlord->email)->send(new \App\Mail\LandlordPaymentNotification($payment));
            }
            
            // Create in-app message for landlord
            $this->createLandlordPaymentMessage($payment);
            
            return $filename;
        } catch(\Exception $e) {
            Log::error('Receipt generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create in-app message notification for landlord
     */
    private function createLandlordPaymentMessage($payment)
    {
        try {
            $commissionAmount = $payment->amount * 0.025;
            $netAmount = $payment->amount - $commissionAmount;
            
            $messageBody = sprintf(
                "You have received a rent payment from %s %s.\n\n" .
                "Property: %s\n" .
                "Apartment: %s\n" .
                "Gross Amount: ₦%s\n" .
                "Platform Fee (2.5%%): ₦%s\n" .
                "Net Amount: ₦%s\n\n" .
                "Transaction ID: %s\n" .
                "Payment Date: %s\n" .
                "Duration: %d %s",
                $payment->tenant->first_name,
                $payment->tenant->last_name,
                $payment->apartment->property->address ?? 'N/A',
                $payment->apartment->apartment_type ?? 'N/A',
                number_format($payment->amount, 2),
                number_format($commissionAmount, 2),
                number_format($netAmount, 2),
                $payment->transaction_id,
                $payment->paid_at ? $payment->paid_at->format('M d, Y h:i A') : $payment->created_at->format('M d, Y h:i A'),
                $payment->duration,
                \Illuminate\Support\Str::plural('Month', $payment->duration)
            );
            
            \App\Models\Message::create([
                'sender_id' => 0, // System message
                'receiver_id' => $payment->landlord_id,
                'subject' => 'Payment Received - ₦' . number_format($netAmount, 2),
                'body' => $messageBody,
                'is_read' => false,
            ]);
            
            Log::info('Landlord payment message created', [
                'landlord_id' => $payment->landlord_id,
                'payment_id' => $payment->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create landlord payment message: ' . $e->getMessage());
        }
    }

    /**
     * Display payment receipt
     */
    public function showReceipt($id)
    {
        $payment = Payment::with(['tenant', 'landlord', 'apartment.property'])
            ->findOrFail($id);
            
        // Ensure only the tenant or landlord can view the receipt
        $user = auth()->user();
        if ($user->user_id !== $payment->tenant_id && $user->user_id !== $payment->landlord_id) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access');
        }
        
        return view('payments.receipt_view', compact('payment'));
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt($transactionId)
    {
        try {
            $payment = Payment::where('transaction_id', $transactionId)
                ->with(['tenant', 'landlord', 'apartment'])
                ->firstOrFail();
            
            // Check if user is authorized to view this receipt
            if (auth()->user()->user_id !== $payment->tenant_id && 
                auth()->user()->user_id !== $payment->landlord_id) {
                abort(403);
            }

            $filename = 'receipt_' . $payment->transaction_id . '.pdf';
            if (!Storage::exists('receipts/' . $filename)) {
                // Generate new receipt if it doesn't exist
                $pdf = Pdf::loadView('payments.receipt', compact('payment'));
                Storage::put('receipts/' . $filename, $pdf->output());
            }

            return Storage::download('receipts/' . $filename);
        } catch(\Exception $e) {
            Log::error('Receipt download failed: ' . $e->getMessage());
            return back()->with('error', 'Unable to download receipt. Please try again later.');
        }
    }
    
    /**
     * Show payment receipt by transaction reference
     */
    public function showReceiptByReference($reference)
    {
        try {
            $payment = Payment::where('transaction_id', $reference)
                ->with(['tenant', 'landlord', 'apartment.property'])
                ->firstOrFail();
                
            // Ensure only the tenant or landlord can view the receipt
            $user = auth()->user();
            if ($user && $user->user_id !== $payment->tenant_id && $user->user_id !== $payment->landlord_id) {
                return redirect()->to('/dashboard')->with('error', 'Unauthorized access');
            }
            
            return view('payments.receipt_view', compact('payment'));
        } catch(\Exception $e) {
            Log::error('Receipt view failed: ' . $e->getMessage());
            return redirect()->to('/dashboard')->with('error', 'Unable to find payment receipt. Please contact support.');
        }
    }
    
    /**
     * Show payment receipt by ID
     */
    // public function showReceipt($id)
    // {
    //     try {
    //         $payment = Payment::findOrFail($id);
            
    //         // Check if user is authorized to view this receipt
    //         if (auth()->user()->user_id !== $payment->tenant_id && 
    //             auth()->user()->user_id !== $payment->landlord_id &&
    //             auth()->user()->role !== 1 && auth()->user()->role !== 2) { // Allow admins and super admins
    //             abort(403, 'You are not authorized to view this receipt.');
    //         }
            
    //         return view('payments.receipt', compact('payment'));
    //     } catch(\Exception $e) {
    //         Log::error('Receipt view failed: ' . $e->getMessage());
    //         return back()->with('error', 'Unable to view receipt. Please try again later.');
    //     }
    // }
    
    /**
     * Download payment receipt by ID
     */
    public function downloadReceiptById($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            
            // Check if user is authorized to view this receipt
            if (auth()->user()->user_id !== $payment->tenant_id && 
                auth()->user()->user_id !== $payment->landlord_id &&
                auth()->user()->role !== 1 && auth()->user()->role !== 2) { // Allow admins and super admins
                abort(403, 'You are not authorized to download this receipt.');
            }
            
            $filename = 'receipt_' . $payment->transaction_id . '.pdf';
            // Generate new receipt PDF
            $pdf = Pdf::loadView('payments.receipt', compact('payment'));
            
            return $pdf->download($filename);
        } catch(\Exception $e) {
            Log::error('Receipt download by ID failed: ' . $e->getMessage());
            return back()->with('error', 'Unable to download receipt. Please try again later.');
        }
    }

    public function analytics()
    {
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $totalTransactions = Payment::where('status', 'completed')->count();
        $monthlyAverage = Payment::where('status', 'completed')
            ->whereYear('created_at', Carbon::now()->year)
            ->avg('amount') ?? 0;
        $pendingPayments = Payment::where('status', 'pending')->count();

        // Get monthly revenue data for the past year
        $revenueData = Payment::where('status', 'completed')
            ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
            ->select(
                DB::raw('SUM(amount) as total'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year')
            )
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        // Format revenue data for the chart
        $labels = [];
        $values = [];
        $start = Carbon::now()->subYear();
        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->format('M Y');
            $monthData = $revenueData->where('month', $month->month)
                ->where('year', $month->year)
                ->first();
            $values[] = $monthData ? $monthData->total : 0;
        }

        // Get payment methods distribution
        $paymentMethods = Payment::where('status', 'completed')
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $methodLabels = $paymentMethods->pluck('payment_method')->toArray();
        $methodValues = $paymentMethods->pluck('count')->toArray();

        return view('payments.analytics', [
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'monthlyAverage' => $monthlyAverage,
            'pendingPayments' => $pendingPayments,
            'revenueData' => [
                'labels' => $labels,
                'values' => $values,
            ],
            'paymentMethods' => [
                'labels' => $methodLabels,
                'values' => $methodValues,
            ],
        ]);
    }

    protected function exportPayments($query, $format)
    {
        $payments = $query->get();
        
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($payments);
            case 'excel':
                return $this->exportToExcel($payments);
            case 'pdf':
                return $this->exportToPdf($payments);
            default:
                return redirect()->back()->with('error', 'Invalid export format');
        }
    }

    protected function exportToCsv($payments)
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=payments.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Transaction ID', 'Amount', 'Status', 'Payment Method', 'Date']);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->transaction_id,
                    $payment->amount,
                    $payment->status,
                    $payment->payment_method,
                    $payment->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportToExcel($payments)
    {
        // Build a simple Excel-compatible HTML table for export
        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="payments.xls"',
            'Expires'             => '0',
            'Cache-Control'       => 'must-revalidate',
            'Pragma'              => 'public',
        ];

        $callback = function () use ($payments) {
            // Output the Excel header
            echo "<table border='1'>";
            echo "<tr><th>Transaction ID</th><th>Amount</th><th>Status</th><th>Payment Method</th><th>Date</th></tr>";

            // Output each payment row
            foreach ($payments as $payment) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($payment->transaction_id) . "</td>";
                echo "<td>" . htmlspecialchars($payment->amount) . "</td>";
                echo "<td>" . htmlspecialchars($payment->status) . "</td>";
                echo "<td>" . htmlspecialchars($payment->payment_method) . "</td>";
                echo "<td>" . htmlspecialchars($payment->created_at) . "</td>";
                echo "</tr>";
            }

            echo "</table>";
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportToPdf($payments)
    {
        $pdf = Pdf::loadView('payments.export.pdf', compact('payments'));
        return $pdf->download('payments.pdf');
    }
    
    /**
     * Check if a payment is invitation-based
     */
    private function isInvitationBasedPayment(Payment $payment): bool
    {
        // Check by reference pattern (use Str::contains for PHP 7 compatibility)
        if (!empty($payment->payment_reference) && Str::contains($payment->payment_reference, 'easyrent_')) {
            return true;
        }

        // Safely inspect payment metadata for invitation token
        $meta = $payment->payment_meta;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $meta = $decoded;
            } else {
                $meta = null;
            }
        }
        if (is_array($meta) && isset($meta['invitation_token'])) {
            return true;
        }

        // Fallback: active invitation matching apartment and (optionally) tenant
        $query = ApartmentInvitation::where('apartment_id', $payment->apartment_id)
            ->where('status', '!=', ApartmentInvitation::STATUS_USED);
        if (!empty($payment->tenant_id)) {
            $query->where('tenant_user_id', $payment->tenant_id);
        }
        $invitation = $query->first();
        return $invitation !== null;
    }

    /**
     * Validate payment amount against expected calculation
     */
    private function validatePaymentAmount(float $requestedAmount, array $metadata): array
    {
        try {
            // Determine the source of the payment (invitation or proforma)
            if (isset($metadata['invitation_token'])) {
                return $this->validateInvitationPaymentAmount($requestedAmount, $metadata);
            } elseif (isset($metadata['proforma_id'])) {
                return $this->validateProformaPaymentAmount($requestedAmount, $metadata);
            } else {
                return [
                    'valid' => false,
                    'error' => 'Payment metadata missing required identifiers',
                    'calculation_result' => null
                ];
            }
        } catch (\Exception $e) {
            Log::error('Payment amount validation error', [
                'requested_amount' => $requestedAmount,
                'metadata' => $metadata,
                'error' => $e->getMessage()
            ]);
            
            return [
                'valid' => false,
                'error' => 'Payment validation failed due to system error',
                'calculation_result' => null
            ];
        }
    }

    /**
     * Validate invitation payment amount
     */
    private function validateInvitationPaymentAmount(float $requestedAmount, array $metadata): array
    {
        try {
            $invitationToken = $metadata['invitation_token'];
            $invitation = \App\Models\ApartmentInvitation::where('invitation_token', $invitationToken)->first();
            
            if (!$invitation) {
                return [
                    'valid' => false,
                    'error' => 'Apartment invitation not found',
                    'calculation_result' => null
                ];
            }
            
            $apartment = $invitation->apartment;
            if (!$apartment) {
                return [
                    'valid' => false,
                    'error' => 'Apartment not found for invitation',
                    'calculation_result' => null
                ];
            }
            
            $duration = $invitation->lease_duration ?? 12;
            $calculationResult = $this->paymentCalculationService->calculatePaymentTotal(
                $apartment->amount,
                $duration,
                $apartment->getPricingType()
            );
            
            if (!$calculationResult->isValid) {
                return [
                    'valid' => false,
                    'error' => 'Payment calculation failed: ' . $calculationResult->errorMessage,
                    'calculation_result' => $calculationResult
                ];
            }
            
            $expectedAmount = $calculationResult->totalAmount;
            $tolerance = 0.01; // Allow for minor rounding differences
            
            if (abs($expectedAmount - $requestedAmount) > $tolerance) {
                return [
                    'valid' => false,
                    'error' => sprintf(
                        'Payment amount mismatch. Expected: ₦%s, Requested: ₦%s',
                        number_format($expectedAmount, 2),
                        number_format($requestedAmount, 2)
                    ),
                    'calculation_result' => $calculationResult
                ];
            }
            
            return [
                'valid' => true,
                'error' => null,
                'calculation_result' => $calculationResult
            ];
            
        } catch (\Exception $e) {
            Log::error('Invitation payment validation error', [
                'metadata' => $metadata,
                'error' => $e->getMessage()
            ]);
            
            return [
                'valid' => false,
                'error' => 'Invitation payment validation failed',
                'calculation_result' => null
            ];
        }
    }

    /**
     * Validate proforma payment amount
     */
    private function validateProformaPaymentAmount(float $requestedAmount, array $metadata): array
    {
        try {
            $proformaId = $metadata['proforma_id'];
            $proforma = ProfomaReceipt::find($proformaId);
            
            if (!$proforma) {
                return [
                    'valid' => false,
                    'error' => 'Proforma receipt not found',
                    'calculation_result' => null
                ];
            }
            
            $apartment = Apartment::where('apartment_id', $proforma->apartment_id)->first();
            if (!$apartment) {
                return [
                    'valid' => false,
                    'error' => 'Apartment not found for proforma',
                    'calculation_result' => null
                ];
            }
            
            $duration = $proforma->duration ?? 12;
            $calculationResult = $this->paymentCalculationService->calculatePaymentTotal(
                $apartment->amount,
                $duration,
                $apartment->getPricingType()
            );
            
            if (!$calculationResult->isValid) {
                return [
                    'valid' => false,
                    'error' => 'Payment calculation failed: ' . $calculationResult->errorMessage,
                    'calculation_result' => $calculationResult
                ];
            }
            
            $expectedAmount = $calculationResult->totalAmount;
            $tolerance = 0.01; // Allow for minor rounding differences
            
            // Convert requested amount from kobo to naira if needed
            $requestedAmountNaira = $requestedAmount > 1000 ? $requestedAmount / 100 : $requestedAmount;
            
            if (abs($expectedAmount - $requestedAmountNaira) > $tolerance) {
                return [
                    'valid' => false,
                    'error' => sprintf(
                        'Payment amount mismatch. Expected: ₦%s, Requested: ₦%s',
                        number_format($expectedAmount, 2),
                        number_format($requestedAmountNaira, 2)
                    ),
                    'calculation_result' => $calculationResult
                ];
            }
            
            return [
                'valid' => true,
                'error' => null,
                'calculation_result' => $calculationResult
            ];
            
        } catch (\Exception $e) {
            Log::error('Proforma payment validation error', [
                'metadata' => $metadata,
                'error' => $e->getMessage()
            ]);
            
            return [
                'valid' => false,
                'error' => 'Proforma payment validation failed',
                'calculation_result' => null
            ];
        }
    }

    /**
     * Log payment initiation for audit purposes
     */
    private function logPaymentInitiation(float $amount, array $metadata, array $validationResult): void
    {
        try {
            $logData = [
                'event' => 'payment_initiation',
                'requested_amount' => $amount,
                'metadata' => $metadata,
                'validation_passed' => $validationResult['valid'],
                'validation_error' => $validationResult['error'] ?? null,
                'timestamp' => now()->toISOString(),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ];
            
            // Add calculation details if available
            if (isset($validationResult['calculation_result']) && $validationResult['calculation_result']) {
                $calculationResult = $validationResult['calculation_result'];
                $logData['calculation_details'] = [
                    'expected_amount' => $calculationResult->totalAmount,
                    'calculation_method' => $calculationResult->calculationMethod,
                    'steps_count' => count($calculationResult->calculationSteps)
                ];
                
                // Log the calculation steps for audit
                $this->paymentCalculationService->logCalculationSteps($calculationResult);
            }
            
            Log::info('Payment initiation logged', $logData);
            
        } catch (\Exception $e) {
            Log::error('Failed to log payment initiation', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'metadata_keys' => array_keys($metadata)
            ]);
        }
    }

    /**
     * Validate existing payment amount against current calculation
     */
    private function validateExistingPaymentAmount(Payment $payment): array
    {
        try {
            // Get the apartment for this payment
            $apartment = Apartment::where('apartment_id', $payment->apartment_id)->first();
            if (!$apartment) {
                return [
                    'valid' => false,
                    'error' => 'Apartment not found for existing payment validation'
                ];
            }
            
            // Calculate expected amount using current pricing
            $calculationResult = $this->paymentCalculationService->calculatePaymentTotal(
                $apartment->amount,
                $payment->duration ?? 12,
                $apartment->getPricingType()
            );
            
            if (!$calculationResult->isValid) {
                return [
                    'valid' => false,
                    'error' => 'Current calculation failed: ' . $calculationResult->errorMessage
                ];
            }
            
            $expectedAmount = $calculationResult->totalAmount;
            $actualAmount = $payment->amount;
            $tolerance = 0.01; // Allow for minor rounding differences
            
            $isValid = abs($expectedAmount - $actualAmount) <= $tolerance;
            
            if (!$isValid) {
                Log::info('Payment amount validation discrepancy', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'expected_amount' => $expectedAmount,
                    'actual_amount' => $actualAmount,
                    'difference' => abs($expectedAmount - $actualAmount),
                    'calculation_method' => $calculationResult->calculationMethod,
                    'apartment_pricing_type' => $apartment->getPricingType(),
                    'apartment_amount' => $apartment->amount,
                    'payment_duration' => $payment->duration
                ]);
            }
            
            return [
                'valid' => $isValid,
                'error' => $isValid ? null : sprintf(
                    'Payment amount mismatch. Expected: ₦%s, Actual: ₦%s (Difference: ₦%s)',
                    number_format($expectedAmount, 2),
                    number_format($actualAmount, 2),
                    number_format(abs($expectedAmount - $actualAmount), 2)
                ),
                'calculation_result' => $calculationResult,
                'expected_amount' => $expectedAmount,
                'actual_amount' => $actualAmount,
                'difference' => abs($expectedAmount - $actualAmount)
            ];
            
        } catch (\Exception $e) {
            Log::error('Existing payment validation error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'valid' => false,
                'error' => 'Payment validation failed due to system error'
            ];
        }
    }

    /**
     * Get payment calculation details for display
     */
    private function getPaymentCalculationDetails(Payment $payment): array
    {
        try {
            // Try to get calculation details from payment metadata first
            if ($payment->payment_meta) {
                $meta = is_string($payment->payment_meta) 
                    ? json_decode($payment->payment_meta, true) 
                    : $payment->payment_meta;
                
                if (is_array($meta) && isset($meta['calculation_method'])) {
                    return [
                        'method' => $meta['calculation_method'],
                        'steps' => $meta['calculation_steps'] ?? [],
                        'expected_amount' => $meta['expected_amount'] ?? $payment->amount,
                        'amount_validated' => $meta['amount_validated'] ?? false,
                        'source' => 'payment_metadata'
                    ];
                }
            }
            
            // If no metadata available, recalculate for display
            $apartment = Apartment::where('apartment_id', $payment->apartment_id)->first();
            if (!$apartment) {
                return [
                    'method' => 'unknown',
                    'steps' => [],
                    'expected_amount' => $payment->amount,
                    'amount_validated' => false,
                    'source' => 'apartment_not_found'
                ];
            }
            
            $calculationResult = $this->paymentCalculationService->calculatePaymentTotal(
                $apartment->amount,
                $payment->duration ?? 12,
                $apartment->getPricingType()
            );
            
            if (!$calculationResult->isValid) {
                return [
                    'method' => 'calculation_failed',
                    'steps' => [],
                    'expected_amount' => $payment->amount,
                    'amount_validated' => false,
                    'error' => $calculationResult->errorMessage,
                    'source' => 'recalculation_failed'
                ];
            }
            
            return [
                'method' => $calculationResult->calculationMethod,
                'steps' => $calculationResult->calculationSteps,
                'expected_amount' => $calculationResult->totalAmount,
                'amount_validated' => abs($calculationResult->totalAmount - $payment->amount) <= 0.01,
                'source' => 'recalculated'
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to get payment calculation details', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'method' => 'error',
                'steps' => [],
                'expected_amount' => $payment->amount,
                'amount_validated' => false,
                'error' => $e->getMessage(),
                'source' => 'error'
            ];
        }
    }

    /**
     * Create a fallback payment record when the main process fails
     */
    private function createFallbackPayment($reference, $errorMessage)
    {
        try {
            Log::info('Creating fallback payment record', ['reference' => $reference]);
            
            // Try to find any user and apartment for the fallback
            $user = User::first();
            $apartment = Apartment::first();
            
            if (!$user || !$apartment) {
                Log::error('Cannot create fallback payment - no user or apartment found');
                return;
            }
            
            $fallbackPayment = new Payment();
            $fallbackPayment->transaction_id = $reference . '_fallback';
            $fallbackPayment->payment_reference = $reference;
            $fallbackPayment->amount = 0; // Unknown amount
            $fallbackPayment->tenant_id = $user->user_id;
            $fallbackPayment->landlord_id = $user->user_id;
            $fallbackPayment->apartment_id = $apartment->apartment_id;
            $fallbackPayment->status = 'failed';
            $fallbackPayment->payment_method = 'unknown';
            $fallbackPayment->duration = 1;
            $fallbackPayment->payment_meta = json_encode(['error' => $errorMessage, 'type' => 'fallback']);
            $fallbackPayment->save();
            
            Log::info('Fallback payment created', ['payment_id' => $fallbackPayment->id]);
            
        } catch (\Exception $e) {
            Log::error('Fallback payment creation failed', ['error' => $e->getMessage()]);
        }
    }
}
