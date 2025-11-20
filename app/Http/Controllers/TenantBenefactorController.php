<?php

namespace App\Http\Controllers;

use App\Models\PaymentInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BenefactorInvitationMail;

class TenantBenefactorController extends Controller
{
    /**
     * Invite a benefactor to pay rent
     */
    public function inviteBenefactor(Request $request)
    {
        $validated = $request->validate([
            'benefactor_email' => 'required|email',
            'amount' => 'required|numeric|min:0',
            'property_id' => 'nullable|exists:properties,id',
            'apartment_id' => 'nullable|exists:apartments,id',
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
            \Log::error('Failed to send benefactor invitation email: ' . $e->getMessage());
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
}
