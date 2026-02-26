<?php

namespace App\Http\Controllers;

use App\Models\Benefactor;
use App\Models\BenefactorPayment;
use App\Models\PaymentInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class BenefactorPaymentController extends Controller
{
    /**
     * Show payment invitation page (approval step)
     */
    public function show($token)
    {
        $invitation = PaymentInvitation::where('token', $token)->firstOrFail();

        // Check if expired
        if ($invitation->isExpired()) {
            return view('benefactor.expired', compact('invitation'));
        }

        // Check if declined
        if ($invitation->isDeclined()) {
            return view('benefactor.declined', compact('invitation'));
        }

        // Check if already accepted
        if ($invitation->isAccepted()) {
            return view('benefactor.already-paid', compact('invitation'));
        }

        // Check if user is logged in
        $isLoggedIn = Auth::check();

        // If pending approval, show approval page
        if ($invitation->isPendingApproval()) {
            return view('benefactor.approval', compact('invitation', 'isLoggedIn'));
        }

        // If approved, show payment page
        return view('benefactor.payment', compact('invitation', 'isLoggedIn'));
    }

    /**
     * Approve payment request
     */
    public function approve(Request $request, $token)
    {
        $invitation = PaymentInvitation::where('token', $token)->firstOrFail();

        if (!$invitation->isPendingApproval()) {
            return back()->with('error', 'This invitation has already been processed.');
        }

        $invitation->approve();

        return redirect()->route('benefactor.payment.show', ['token' => $token])
            ->with('success', 'Payment request approved. Please proceed with payment.');
    }

    /**
     * Decline payment request
     */
    public function decline(Request $request, $token)
    {
        $invitation = PaymentInvitation::where('token', $token)->firstOrFail();

        if (!$invitation->isPendingApproval()) {
            return back()->with('error', 'This invitation has already been processed.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $invitation->decline($validated['reason'] ?? null);

        // Notify tenant
        Mail::to($invitation->tenant->email)->send(new \App\Mail\PaymentDeclinedMail($invitation));

        return view('benefactor.declined', compact('invitation'))
            ->with('success', 'Payment request declined. The tenant has been notified.');
    }

    /**
     * Process payment (guest or registered)
     */
    public function processPayment(Request $request, $token)
    {
        $invitation = PaymentInvitation::where('token', $token)->firstOrFail();

        // Validate invitation
        if ($invitation->isExpired() || $invitation->isAccepted()) {
            return back()->withInput()->with('error', 'This payment link is no longer valid.');
        }

        // Check if user is logged in
        $isLoggedIn = Auth::check();

        // Build validation rules based on user authentication status
        $validationRules = [
            'payment_type' => 'required|in:one_time,recurring',
            'frequency' => 'required_if:payment_type,recurring|in:monthly,quarterly,annually',
            'payment_day_of_month' => 'nullable|integer|min:1|max:31',
            'relationship_type' => 'required|in:employer,parent,guardian,sponsor,organization,other',
            'phone' => 'nullable|string|max:20',
        ];

        // Add guest-specific validation rules
        if (!$isLoggedIn) {
            $validationRules['full_name'] = 'required|string|max:255';
            $validationRules['email'] = 'required|email|max:255';
            $validationRules['create_account'] = 'nullable|boolean';
            
            // Only require password if user explicitly wants to create an account
            if ($request->has('create_account') && $request->input('create_account') == '1') {
                $validationRules['password'] = 'required|min:8|confirmed';
            }
        } else {
            $validationRules['full_name'] = 'nullable|string|max:255';
        }

        try {
            $validated = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Determine if guest or registered user
            $benefactor = null;
            
            if (Auth::check()) {
                // Registered user - verify user exists
                $user = Auth::user();
                
                // Use the correct primary key (user_id, not id)
                $userId = $user->user_id;
                
                // Double check user exists in database
                $userExists = User::where('user_id', $userId)->first();
                if (!$userExists) {
                    throw new \Exception('User account not found. Please log out and log in again.');
                }
                
                // Check if benefactor already exists by email first
                $benefactor = Benefactor::where('email', $user->email)->first();
                
                if ($benefactor) {
                    // Update existing benefactor
                    $benefactor->update([
                        'user_id' => $userId,
                        'full_name' => $user->first_name . ' ' . $user->last_name,
                        'phone' => $user->phone ?? $request->phone ?? $benefactor->phone,
                        'type' => 'registered',
                        'relationship_type' => $validated['relationship_type'],
                        'is_registered' => true,
                    ]);
                } else {
                    // Create new benefactor
                    $benefactor = Benefactor::create([
                        'user_id' => $userId,
                        'email' => $user->email,
                        'full_name' => $user->first_name . ' ' . $user->last_name,
                        'phone' => $user->phone ?? $request->phone,
                        'type' => 'registered',
                        'relationship_type' => $validated['relationship_type'],
                        'is_registered' => true,
                    ]);
                }
            } else {
                // Guest user or creating account
                $guestEmail = $request->email ?? $invitation->benefactor_email;
                
                if (!$guestEmail) {
                    throw new \Exception('Email address is required for payment processing.');
                }
                
                if ($request->create_account) {
                    // Create user account
                    $user = User::create([
                        'email' => $guestEmail,
                        'first_name' => explode(' ', $request->full_name)[0],
                        'last_name' => explode(' ', $request->full_name)[1] ?? '',
                        'phone' => $request->phone,
                        'password' => bcrypt($request->password),
                    ]);

                    $benefactor = Benefactor::create([
                        'user_id' => $user->user_id,
                        'email' => $user->email,
                        'full_name' => $request->full_name,
                        'phone' => $request->phone,
                        'type' => 'registered',
                        'relationship_type' => $validated['relationship_type'],
                        'is_registered' => true,
                    ]);

                    // Log the user in
                    Auth::login($user);
                } else {
                    // Guest checkout - check if benefactor exists from previous payments
                    $benefactor = Benefactor::where('email', $guestEmail)->first();
                    
                    if ($benefactor) {
                        // Update existing guest benefactor
                        $benefactor->update([
                            'full_name' => $request->full_name ?? $benefactor->full_name,
                            'phone' => $request->phone ?? $benefactor->phone,
                            'relationship_type' => $validated['relationship_type'],
                        ]);
                    } else {
                        // Create new guest benefactor
                        $benefactor = Benefactor::create([
                            'email' => $guestEmail,
                            'full_name' => $request->full_name,
                            'phone' => $request->phone,
                            'type' => 'guest',
                            'relationship_type' => $validated['relationship_type'],
                            'is_registered' => false,
                        ]);
                    }
                }
            }

            // Create payment record
            $payment = BenefactorPayment::create([
                'benefactor_id' => $benefactor->id,
                'tenant_id' => $invitation->tenant_id, // This is user_id from the invitation
                'proforma_id' => $invitation->proforma_id,
                'amount' => $invitation->amount,
                'payment_type' => $validated['payment_type'],
                'frequency' => $validated['frequency'] ?? null,
                'payment_day_of_month' => $validated['payment_day_of_month'] ?? null,
                'status' => 'pending',
                'payment_metadata' => $invitation->invoice_details,
            ]);

            // Mark invitation as accepted
            $invitation->markAsAccepted($benefactor->id);

            DB::commit();

            // Redirect to payment gateway
            return redirect()->route('benefactor.payment.gateway', ['payment' => $payment->id]);

        } catch (\Exception $e) {
            DB::rollBack();
           Log::error('Benefactor payment processing error: ' . $e->getMessage(), [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Show payment gateway page
     */
    public function paymentGateway($paymentId)
    {
        $payment = BenefactorPayment::with(['benefactor', 'tenant'])->findOrFail($paymentId);

        return view('benefactor.gateway', compact('payment'));
    }

    /**
     * Handle payment callback from Paystack
     */
    public function paymentCallback(Request $request)
    {
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect()->route('dashboard')->with('error', 'Invalid payment reference.');
        }

        try {
            // Verify payment with Paystack
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer " . config('services.paystack.secret_key'),
                    "Cache-Control: no-cache",
                ],
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if ($err) {
               Log::error('Paystack verification error: ' . $err);
                return redirect()->route('dashboard')->with('error', 'Payment verification failed.');
            }
            
            $result = json_decode($response);
            
            if (!$result->status || $result->data->status !== 'success') {
                return redirect()->route('dashboard')->with('error', 'Payment was not successful.');
            }
            
            // Extract payment ID from reference (format: BEN-{payment_id}-{random})
            $parts = explode('-', $reference);
            if (count($parts) < 2) {
                return redirect()->route('dashboard')->with('error', 'Invalid payment reference format.');
            }
            
            $paymentId = $parts[1];
            $payment = BenefactorPayment::findOrFail($paymentId);
            
            // Check if already processed
            if ($payment->status === 'completed') {
                return redirect()->route('benefactor.payment.success', ['payment' => $payment->id]);
            }
            
            // Mark as completed
            $payment->markAsCompleted($result->data->reference);
            
            // Send notification to tenant
            try {
                Mail::to($payment->tenant->email)->send(new \App\Mail\BenefactorPaymentSuccessMail($payment));
            } catch (\Exception $e) {
               Log::error('Failed to send payment success email: ' . $e->getMessage());
            }
            
            return redirect()->route('benefactor.payment.success', ['payment' => $payment->id]);
            
        } catch (\Exception $e) {
           Log::error('Payment callback error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'An error occurred processing your payment.');
        }
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess($paymentId)
    {
        $payment = BenefactorPayment::with(['benefactor', 'tenant'])->findOrFail($paymentId);

        return view('benefactor.success', compact('payment'));
    }

    /**
     * Benefactor dashboard (for registered users)
     */
    public function dashboard()
    {
        $user = Auth::user();
        $benefactor = Benefactor::where('user_id', $user->id)->first();

        if (!$benefactor) {
            // Check if there's a guest benefactor with this email that needs migration
            $guestBenefactor = Benefactor::where('email', $user->email)
                ->whereNull('user_id')
                ->first();
            
            if ($guestBenefactor) {
                // Migrate guest benefactor to registered
                $guestBenefactor->update([
                    'user_id' => $user->id,
                    'type' => 'registered',
                    'is_registered' => true,
                ]);
                
                $benefactor = $guestBenefactor;
                
                // Show welcome message with history
                session()->flash('benefactor_migrated', true);
                session()->flash('payment_count', $benefactor->payments()->count());
            } else {
                return redirect()->route('dashboard')->with('info', 'You have no benefactor payments yet.');
            }
        }

        $payments = $benefactor->payments()->with(['tenant', 'property'])->latest()->paginate(20);
        $recurringPayments = $benefactor->recurringPayments()->with(['tenant'])->get();
        $pausedPayments = $benefactor->payments()
            ->where('is_paused', true)
            ->with(['tenant'])
            ->get();
        $tenants = $benefactor->tenants()->get();

        return view('benefactor.dashboard', compact('benefactor', 'payments', 'recurringPayments', 'pausedPayments', 'tenants'));
    }

    /**
     * Pause recurring payment
     */
    public function pauseRecurring(Request $request, $paymentId)
    {
        $payment = BenefactorPayment::findOrFail($paymentId);
        
        // Verify ownership
        if (Auth::check() && $payment->benefactor->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$payment->isRecurring()) {
            return back()->with('error', 'Only recurring payments can be paused.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $payment->pause($validated['reason'] ?? null);

        // Notify tenant
        Mail::to($payment->tenant->email)->send(new \App\Mail\PaymentPausedMail($payment));

        return back()->with('success', 'Recurring payment paused successfully.');
    }

    /**
     * Resume paused recurring payment
     */
    public function resumeRecurring($paymentId)
    {
        $payment = BenefactorPayment::findOrFail($paymentId);
        
        // Verify ownership
        if (Auth::check() && $payment->benefactor->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$payment->isPaused()) {
            return back()->with('error', 'This payment is not paused.');
        }

        $payment->resume();

        // Notify tenant
        Mail::to($payment->tenant->email)->send(new \App\Mail\PaymentResumedMail($payment));

        return back()->with('success', 'Recurring payment resumed successfully.');
    }

    /**
     * Cancel recurring payment
     */
    public function cancelRecurring($paymentId)
    {
        $payment = BenefactorPayment::findOrFail($paymentId);
        
        // Verify ownership
        if (Auth::check() && $payment->benefactor->user_id !== Auth::id()) {
            abort(403);
        }

        $payment->cancel();

        // Notify tenant
        Mail::to($payment->tenant->email)->send(new \App\Mail\PaymentCancelledMail($payment));

        return back()->with('success', 'Recurring payment cancelled successfully.');
    }
}
