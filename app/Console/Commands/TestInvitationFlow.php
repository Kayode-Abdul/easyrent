<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Property;
use App\Models\ApartmentInvitation;
use App\Models\Payment;
use Illuminate\Support\Str;
use App\Services\Payment\PaymentIntegrationService;
use Illuminate\Support\Facades\DB;

class TestInvitationFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:invitation-flow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate and test the apartment invitation to registration and assignment flow';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Starting simulated apartment invitation test...");
        
        DB::beginTransaction();
        try {
            // 1. Pull an existing apartment that isn't occupied
            $apartment = Apartment::where('occupied', 0)->first();
            if (!$apartment) {
                // fallback
                $apartment = Apartment::first();
            }
            
            $landlordId = $apartment->property->user_id ?? 1;

            $this->info("Used Existing Landlord ID: " . $landlordId);
            $this->info("Used Existing Apartment ID: " . $apartment->apartment_id);

            // 3. Generate an Invitation
            $token = Str::random(32);
            $invitation = ApartmentInvitation::create([
                'landlord_id' => $landlordId,
                'apartment_id' => $apartment->apartment_id,
                'prospect_email' => 'test_tenant@easyrent.africa',
                'prospect_name' => 'Test Guest Tenant',
                'invitation_token' => $token,
                'status' => ApartmentInvitation::STATUS_ACTIVE,
                'expires_at' => now()->addDays(7)
            ]);
            $this->info("Generated Invitation! Token: " . substr($token, 0, 8));

            // 4. Simulate the Guest Payment
            $integration = app(PaymentIntegrationService::class);
            $payment = $integration->createGuestInvitationPayment($invitation, ['duration' => 12]);
            $this->info("Created Guest Payment ID: " . $payment->id);

            // 5. Complete the payment (Webhook simulation)
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'paid_at' => now(),
                'payment_meta' => [
                    'invitation_token' => $invitation->invitation_token,
                    'processed_via' => 'invitation_flow',
                ]
            ]);
            $this->info("Simulated Paystack Webhook completing payment.");

            // 6. User fully registers on RegisterController
            $tenant = User::create([
                'user_id' => random_int(100000, 999999),
                'role' => 3, // Tenant
                'first_name' => 'Test',
                'last_name' => 'Tenant',
                'username' => 'tenant_' . Str::random(5),
                'email' => 'test_tenant_' . Str::random(5) . '@easyrent.africa',
                'password' => bcrypt('password'),
                'phone' => '0987654321'
            ]);
            $this->info("Tenant Registered! ID: " . $tenant->user_id);

            // 7. Fire Finalize
            $this->info("Firing finalizeAfterRegistration()...");
            $result = $integration->finalizeAfterRegistration($invitation, $payment, $tenant);

            if ($result['success']) {
                $this->info("SUCCESS! Returned status: True");
                
                // Assertions
                $apartment->refresh();
                if ($apartment->tenant_id === $tenant->user_id && $apartment->occupied == 1) {
                    $this->info("ASSERTION PASSED: Apartment successfully bound to new tenant user_id!");
                } else {
                    $this->error("ASSERTION FAILED: Apartment is NOT bound to tenant!");
                }

                $payment->refresh();
                if ($payment->tenant_id === $tenant->user_id) {
                    $this->info("ASSERTION PASSED: Payment successfully linked to new tenant user_id!");
                } else {
                    $this->error("ASSERTION FAILED: Payment has no tenant!");
                }
            } else {
                $this->error("FAILED: " . ($result['error'] ?? 'Unknown Error'));
            }

        } catch (\Exception $e) {
            $this->error("Crash: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        // Always rollback so we don't leave garbage in db
        $this->info("Rolling back database to clean up tests.");
        DB::rollBack();
        
        return 0;
    }
}
