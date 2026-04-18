<?php

namespace App\Http\Controllers;

use App\Models\PaymentInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BenefactorInvitationMail;
use Illuminate\Support\Facades\Log;

class TenantBenefactorController extends Controller
{
    /**
     * Generate a benefactor payment link
     */
    public function generateBenefactorLink(Request $request)
    {
        $validated = $request->validate([
            'proforma_id' => 'required|exists:profoma_receipt,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $tenant = Auth::user();
        $proforma = \App\Models\ProfomaReceipt::findOrFail($validated['proforma_id']);
        
        // Verify tenant owns this proforma
        if ($proforma->tenant_id !== $tenant->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to proforma.'
            ], 403);
        }

        // Create invitation without email (for link sharing)
        $invitation = PaymentInvitation::create([
            'tenant_id' => $tenant->user_id,
            'benefactor_email' => null, // No email for link sharing
            'proforma_id' => $validated['proforma_id'],
            'amount' => $validated['amount'],
            'token' => \Illuminate\Support\Str::random(64), // Generate token manually
            'expires_at' => now()->addDays(7), // Set expiry
            'invoice_details' => [
                'property_id' => $proforma->apartment->property_id ?? null,
                'apartment_id' => $proforma->apartment_id,
                'proforma_id' => $validated['proforma_id'],
                'tenant_name' => $tenant->first_name . ' ' . $tenant->last_name,
                'sharing_method' => 'link',
            ],
        ]);

        $paymentLink = route('benefactor.payment.show', $invitation->token);

        return response()->json([
            'success' => true,
            'payment_link' => $paymentLink,
            'invitation_token' => $invitation->token,
        ]);
    }

    /**
     * Invite a benefactor to pay rent
     */
    public function inviteBenefactor(Request $request)
    {
        $validated = $request->validate([
            'benefactor_email' => 'required|email',
            'amount' => 'required|numeric|min:0',
            'property_id' => 'nullable|exists:properties,id',
            'apartment_id' => 'nullable|exists:apartments,apartment_id',
            'proforma_id' => 'nullable|exists:profoma_receipt,id',
            'message' => 'nullable|string|max:500',
        ]);

        $tenant = Auth::user();

        // If proforma_id is provided, get amount and details from proforma
        if (!empty($validated['proforma_id'])) {
            $proforma = \App\Models\ProfomaReceipt::findOrFail($validated['proforma_id']);
            
            // Verify tenant owns this proforma
            if ($proforma->tenant_id !== $tenant->user_id) {
                return back()->with('error', 'Unauthorized access to proforma.');
            }
            
            $validated['amount'] = $proforma->total;
            $validated['property_id'] = $proforma->apartment->property_id ?? null;
            $validated['apartment_id'] = $proforma->apartment_id;
        }

        // Create invitation
        $invitation = PaymentInvitation::create([
            'tenant_id' => $tenant->user_id,
            'benefactor_email' => $validated['benefactor_email'],
            'proforma_id' => $validated['proforma_id'] ?? null,
            'amount' => $validated['amount'],
            'invoice_details' => [
                'property_id' => $validated['property_id'] ?? null,
                'apartment_id' => $validated['apartment_id'] ?? null,
                'proforma_id' => $validated['proforma_id'] ?? null,
                'message' => $validated['message'] ?? null,
                'tenant_name' => $tenant->first_name . ' ' . $tenant->last_name,
            ],
        ]);

        // Send email to benefactor
        try {
            Mail::to($validated['benefactor_email'])->send(new BenefactorInvitationMail($invitation));
        } catch (\Exception $e) {
            // Log error but don't fail the request
           Log::error('Failed to send benefactor invitation email: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment invitation sent to ' . $validated['benefactor_email']
            ]);
        }

        return back()->with('success', 'Payment invitation sent to ' . $validated['benefactor_email']);
    }

    /**
     * View all benefactor invitations
     */
    public function invitations()
    {
        $tenant = Auth::user();
        $invitations = PaymentInvitation::where('tenant_id', $tenant->id)
            ->with('benefactor')
            ->latest()
            ->paginate(20);

        return view('tenant.benefactor-invitations', compact('invitations'));
    }

    /**
     * Cancel an invitation
     */
    public function cancelInvitation(PaymentInvitation $invitation)
    {
        // Verify ownership
        if ($invitation->tenant_id !== Auth::id()) {
            abort(403);
        }

        $invitation->cancel();

        return back()->with('success', 'Invitation cancelled successfully.');
    }



    /**
     * Send benefactor invitation via multiple channels
     */
    public function sendBenefactorInvitation(Request $request)
    {
        $validated = $request->validate([
            'invitation_token' => 'required|exists:payment_invitations,invitation_token',
            'channel' => 'required|in:email,whatsapp,sms',
            'recipient' => 'required|string',
            'message' => 'nullable|string|max:500',
        ]);

        $tenant = Auth::user();
        $invitation = PaymentInvitation::where('invitation_token', $validated['invitation_token'])->firstOrFail();
        
        // Verify tenant owns this invitation
        if ($invitation->tenant_id !== $tenant->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invitation.'
            ], 403);
        }

        $paymentLink = route('benefactor.payment.show', $invitation->invitation_token);
        $amount = format_money($invitation->amount, $invitation->proforma->currency ?? null);
        $tenantName = $tenant->first_name . ' ' . $tenant->last_name;
        
        // Get property details
        $propertyAddress = 'N/A';
        if ($invitation->proforma) {
            $propertyAddress = $invitation->proforma->apartment->property->address ?? 'N/A';
        }

        $customMessage = $validated['message'] ?? '';

        switch ($validated['channel']) {
            case 'email':
                return $this->sendEmailInvitation($invitation, $validated['recipient'], $customMessage);
                
            case 'whatsapp':
                return $this->sendWhatsAppInvitation($paymentLink, $validated['recipient'], $amount, $tenantName, $propertyAddress, $customMessage);
                
            case 'sms':
                return $this->sendSMSInvitation($paymentLink, $validated['recipient'], $amount, $tenantName, $customMessage);
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid channel specified.'
                ], 400);
        }
    }

    /**
     * Send invitation via email
     */
    private function sendEmailInvitation($invitation, $email, $customMessage)
    {
        // Update invitation with benefactor email
        $invitation->update(['benefactor_email' => $email]);

        try {
            Mail::to($email)->send(new BenefactorInvitationMail($invitation, $customMessage));
            
            return response()->json([
                'success' => true,
                'message' => 'Email invitation sent successfully to ' . $email
            ]);
        } catch (\Exception $e) {
           Log::error('Failed to send benefactor invitation email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again.'
            ], 500);
        }
    }

    /**
     * Send invitation via WhatsApp
     */
    private function sendWhatsAppInvitation($paymentLink, $phone, $amount, $tenantName, $propertyAddress, $customMessage)
    {
        // Format phone number (remove spaces and special characters)
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Build WhatsApp message
        $message = "🏠 *Payment Request from {$tenantName}*\n\n";
        
        if ($customMessage) {
            $message .= "{$customMessage}\n\n";
        }
        
        $message .= "📍 Property: {$propertyAddress}\n";
        $message .= "💰 Amount: {$amount}\n\n";
        $message .= "Click the link below to make payment:\n{$paymentLink}\n\n";
        $message .= "Thank you! 🙏";
        
        // Generate WhatsApp link
        $whatsappLink = "https://wa.me/{$phone}?text=" . urlencode($message);
        
        return response()->json([
            'success' => true,
            'whatsapp_link' => $whatsappLink,
            'message' => 'WhatsApp link generated. Opening WhatsApp...'
        ]);
    }

    /**
     * Send invitation via SMS
     */
    private function sendSMSInvitation($paymentLink, $phone, $amount, $tenantName, $customMessage)
    {
        // Format phone number
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Build SMS message (keep it short for SMS)
        $message = "Payment Request from {$tenantName}\n";
        
        if ($customMessage) {
            $message .= substr($customMessage, 0, 50) . "\n";
        }
        
        $message .= "Amount: {$amount}\n";
        $message .= "Pay here: {$paymentLink}";
        
        // Generate SMS link (will open default SMS app)
        $smsLink = "sms:{$phone}?body=" . urlencode($message);
        
        return response()->json([
            'success' => true,
            'sms_link' => $smsLink,
            'message' => 'SMS link generated. Opening messaging app...'
        ]);
    }
}
