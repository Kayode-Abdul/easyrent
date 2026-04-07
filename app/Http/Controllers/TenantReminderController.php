<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\User;
use App\Models\Message;
use App\Mail\OverdueTenancyReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TenantReminderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Trigger reminders for all overdue tenancies
     */
    public function sendOverdueReminders()
    {
        $overdueApartments = Apartment::where('range_end', '<', now())
            ->whereNotNull('tenant_id')
            ->with(['tenant', 'property'])
            ->get();

        $count = 0;
        foreach ($overdueApartments as $apartment) {
            $tenant = $apartment->tenant;
            if (!$tenant)
                continue;

            // 1. Send Email
            try {
                Mail::to($tenant->email)->send(new OverdueTenancyReminder($tenant, $apartment));
            }
            catch (\Exception $e) {
                Log::error("Failed to send overdue email to {$tenant->email}: " . $e->getMessage());
            }

            // 2. Send In-platform Message
            Message::create([
                'sender_id' => auth()->user()->user_id, // Admin
                'receiver_id' => $tenant->user_id,
                'subject' => 'URGENT: Tenancy Overdue Notice',
                'body' => "Your tenancy for {$apartment->apartment_type} at {$apartment->property->address} expired on {$apartment->range_end->format('M d, Y')}. Please settle your outstanding bills or contact your landlord immediately.",
            ]);

            // 3. Log Call Requirement (Placeholder)
            Log::info("Overdue Tenancy Reminder: Call required for tenant {$tenant->first_name} ({$tenant->phone}) regarding apartment #{$apartment->apartment_id}");

            $count++;
        }

        return redirect()->back()->with('success', "Reminders sent to {$count} overdue tenants.");
    }
}