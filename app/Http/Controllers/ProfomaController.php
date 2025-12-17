<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\ProfomaReceipt;
use App\Models\User;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use App\Mail\MessageNotification;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\PaymentCalculationServiceInterface;

class ProfomaController extends Controller
{
    protected PaymentCalculationServiceInterface $paymentCalculationService;

    public function __construct(PaymentCalculationServiceInterface $paymentCalculationService)
    {
        $this->paymentCalculationService = $paymentCalculationService;
    }
    /**
     * Send a proforma receipt and create a notification message.
     */
    public function send(Request $request, $apartmentId): JsonResponse
    {
        try {
            // Resolve apartment by either numeric PK id or public apartment_id
            $apartment = Apartment::find($apartmentId);
            if (!$apartment) {
                $apartment = Apartment::where('apartment_id', $apartmentId)->firstOrFail();
            }

            $user = Auth::user();

            // Gather all proforma fields from request or defaults
            $duration = $request->input('duration', $apartment->duration ?? 12);
            $amount = $request->input('amount', $apartment->amount ?? 0);
            $security_deposit = $request->input('security_deposit');
            $water = $request->input('water');
            $internet = $request->input('internet');
            $generator = $request->input('generator');
            $other_charges_desc = $request->input('other_charges_desc');
            $other_charges_amount = $request->input('other_charges_amount');
            
            // Use centralized payment calculation service
            $additionalCharges = [
                'security_deposit' => $security_deposit ?: 0,
                'water' => $water ?: 0,
                'internet' => $internet ?: 0,
                'generator' => $generator ?: 0,
                'other_charges' => $other_charges_amount ?: 0
            ];
            
            $calculationResult = $this->paymentCalculationService->calculatePaymentTotalWithCharges(
                $amount,
                $duration,
                $apartment->getPricingType(),
                $additionalCharges
            );
            
            if (!$calculationResult->isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment calculation failed: ' . $calculationResult->errorMessage
                ], 400);
            }
            
            $total = $calculationResult->totalAmount;
            
            // Log calculation for audit purposes
            $this->paymentCalculationService->logCalculationSteps($calculationResult);

            // Avoid creating duplicate proformas for same apartment
            $existing = ProfomaReceipt::where('apartment_id', $apartment->id)->first();
            if ($existing) {
                $existing->status = ProfomaReceipt::STATUS_NEW; // sent and not viewed
                // Ensure base amount is updated alongside breakdown fields
                $existing->amount = $amount;
                $existing->duration = $duration;
                $existing->security_deposit = $security_deposit;
                $existing->water = $water;
                $existing->internet = $internet;
                $existing->generator = $generator;
                $existing->other_charges_desc = $other_charges_desc;
                $existing->other_charges_amount = $other_charges_amount;
                $existing->total = $total;
                // Add calculation tracking for audit purposes
                $existing->calculation_method = $calculationResult->calculationMethod;
                $existing->calculation_log = $calculationResult->calculationSteps;
                $existing->save();
                Log::info('Proforma updated', [
                    'proforma_id' => $existing->id,
                    'apartment_pk' => $apartment->id,
                    'amount' => $amount,
                    'total' => $total,
                    'tenant_id' => $existing->tenant_id,
                ]);
                // Always send notification message to tenant on update
                if ($existing->tenant_id) {
                    $message = Message::create([
                        'sender_id' => $user->user_id,
                        'receiver_id' => $existing->tenant_id,
                        'subject' => 'Rent Proforma',
                        'body' => "A new proforma receipt has been sent to you by {$user->username}.\n\n"
                            . (optional($apartment->property)->name ? ("Property: " . $apartment->property->name . "\n") : '')
                            . (property_exists($apartment, 'name') && $apartment->name ? ("Apartment: " . $apartment->name . "\n") : '')
                            . (isset($duration) ? ("Duration: {$duration} months\n\n") : "\n")
                            . "You can  <a class=\"btn btn-primary\" href=\"" . route('proforma.view', ['id' => $existing->id]) . "\">view the proforma</a>"
                    ]);
                    
                    // Send email notification
                    try {
                        $tenant = User::where('user_id', $existing->tenant_id)->first();
                        if ($tenant && $tenant->email) {
                            Mail::to($tenant->email)->send(new MessageNotification($message));
                        }
                    } catch (\Exception $e) {
                       Log::error('Failed to send proforma email: ' . $e->getMessage());
                    }
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Proforma updated and sent successfully!'
                ]);
            }

            // Create proforma receipt (reference apartments.id PK)
            $transactionId = (int)mt_rand(1000000, 9999999);
            $proforma = ProfomaReceipt::create([
                'user_id' => $user->user_id,
                'tenant_id' => $request->tenant_id ?? $apartment->tenant_id,
                'status' => ProfomaReceipt::STATUS_NEW,
                'transaction_id' => $transactionId,
                'apartment_id' => $apartment->id, // Use internal PK, not public apartment_id
                'amount' => $amount,
                'duration' => $duration,
                'security_deposit' => $security_deposit,
                'water' => $water,
                'internet' => $internet,
                'generator' => $generator,
                'other_charges_desc' => $other_charges_desc,
                'other_charges_amount' => $other_charges_amount,
                'total' => $total,
                'calculation_method' => $calculationResult->calculationMethod,
                'calculation_log' => $calculationResult->calculationSteps,
            ]);
            Log::info('Proforma created', [
                'proforma_id' => $proforma->id,
                'apartment_pk' => $apartment->id,
                'amount' => $amount,
                'total' => $total,
                'tenant_id' => $proforma->tenant_id,
            ]);
            // Always send notification message to tenant on create
            if ($proforma->tenant_id) {
                $message = Message::create([
                    'sender_id' => $user->user_id,
                    'receiver_id' => $proforma->tenant_id,
                    'subject' => 'Rent Proforma',
                    'body' => "A new proforma receipt has been sent to you by {$user->username}.\n\n"
                        . (optional($apartment->property)->name ? ("Property: " . $apartment->property->name . "\n") : '')
                        . (property_exists($apartment, 'name') && $apartment->name ? ("Apartment: " . $apartment->name . "\n") : '')
                        . (isset($duration) ? ("Duration: {$duration} months\n\n") : "\n")
                        . "You can  <a class=\"btn btn-primary\" href=\"" . route('proforma.view', ['id' => $proforma->id]) . "\">view the proforma</a>"
                ]);
                
                // Send email notification
                try {
                    $tenant = User::where('user_id', $proforma->tenant_id)->first();
                    if ($tenant && $tenant->email) {
                        Mail::to($tenant->email)->send(new MessageNotification($message));
                    }
                } catch (\Exception $e) {
                   Log::error('Failed to send proforma email: ' . $e->getMessage());
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Proforma sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send proforma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the proforma receipt.
     */
    public function view($id)
    {
        $proforma = ProfomaReceipt::with(['owner', 'tenant', 'apartment.property'])->findOrFail($id);
        // Check if user has permission to view this proforma
        $user = Auth::user();
        if ($user->user_id !== $proforma->user_id && $user->user_id !== $proforma->tenant_id) {
            abort(403, 'Unauthorized');
        }
        // We no longer automatically mark as confirmed when tenant views
        // This allows tenant to explicitly accept or reject the proforma
        // Use the new template for rendering
        return view('proforma.template', compact('proforma'));
    }
    
    /**
     * Accept a proforma receipt.
     */
    public function accept($id)
    {
        $proforma = ProfomaReceipt::findOrFail($id);
        $user = Auth::user();
        
        // Only tenant can accept their own proforma
        if ($user->user_id !== $proforma->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only the tenant can accept this proforma'
            ], 403);
        }
        
        // Update status to confirmed
        $proforma->status = ProfomaReceipt::STATUS_CONFIRMED;
        $proforma->save();
        
        // Create notification message for landlord
        $propertyName = 'property';
        if ($proforma->apartment && $proforma->apartment->property) {
            $propertyName = $proforma->apartment->property->name ?: 'property';
        }
        
        Message::create([
            'sender_id' => $user->user_id,
            'receiver_id' => $proforma->user_id,
            'subject' => 'Proforma Accepted',
            'body' => "Your proforma receipt for " . $propertyName . " has been accepted by the tenant."
        ]);
        
        // Return JSON with payment URL for AJAX handling
        return response()->json([
            'success' => true,
            'message' => 'Proforma accepted successfully!',
            'redirect' => route('proforma.payment.form', ['id' => $proforma->id]),
            'proforma_id' => $proforma->id
        ]);
    }
    
    /**
     * Reject a proforma receipt.
     */
    public function reject($id)
    {
        $proforma = ProfomaReceipt::findOrFail($id);
        $user = Auth::user();
        
        // Only tenant can reject their own proforma
        if ($user->user_id !== $proforma->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only the tenant can reject this proforma'
            ], 403);
        }
        
        // Update status to rejected
        $proforma->status = ProfomaReceipt::STATUS_REJECTED;
        $proforma->save();
        
        // Create notification message for landlord
        $propertyName = 'property';
        if ($proforma->apartment && $proforma->apartment->property) {
            $propertyName = $proforma->apartment->property->name ?: 'property';
        }
        
        Message::create([
            'sender_id' => $user->user_id,
            'receiver_id' => $proforma->user_id,
            'subject' => 'Proforma Rejected',
            'body' => "Your proforma receipt for " . $propertyName . " has been rejected by the tenant."
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Proforma rejected successfully!'
        ]);
    }

    /**
     * Check payment status for a proforma
     */
    public function checkPaymentStatus($id)
    {
        $proforma = ProfomaReceipt::findOrFail($id);
        $user = Auth::user();
        
        // Check if user has permission to view this proforma
        if ($user->user_id !== $proforma->user_id && $user->user_id !== $proforma->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $hasPayment = $proforma->hasSuccessfulPayment();
        $payment = $proforma->getSuccessfulPayment();
        
        return response()->json([
            'success' => true,
            'has_payment' => $hasPayment,
            'payment' => $payment ? [
                'reference' => $payment->payment_reference ?? $payment->transaction_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at
            ] : null
        ]);
    }
}
