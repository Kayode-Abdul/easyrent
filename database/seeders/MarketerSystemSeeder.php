<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MarketerProfile;
use App\Models\ReferralCampaign;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\CommissionPayment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketerSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample marketers
        $marketers = [
            [
                'first_name' => 'John',
                'last_name' => 'Marketing',
                'username' => 'john_marketing',
                'email' => 'john@marketing.com',
                'phone' => '0701234567',
                'commission_rate' => 5.0,
                'marketer_status' => 'active',
                'profile_data' => [
                    'business_name' => 'John Marketing Solutions',
                    'business_registration' => 'BN123456',
                    'address' => '123 Marketing Street, Nairobi',
                    'kyc_status' => 'verified'
                ]
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Property Pro',
                'username' => 'sarah_propertypro',
                'email' => 'sarah@propertypro.com',
                'phone' => '0712345678',
                'commission_rate' => 7.0,
                'marketer_status' => 'active',
                'profile_data' => [
                    'business_name' => 'Property Pro Marketing',
                    'business_registration' => 'BN789012',
                    'address' => '456 Business Avenue, Mombasa',
                    'kyc_status' => 'verified'
                ]
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Real Estate',
                'username' => 'mike_realestate',
                'email' => 'mike@realestate.com',
                'phone' => '0723456789',
                'commission_rate' => 3.0,
                'marketer_status' => 'pending',
                'profile_data' => [
                    'business_name' => 'Mike Real Estate Marketing',
                    'business_registration' => 'BN345678',
                    'address' => '789 Commerce Road, Kisumu',
                    'kyc_status' => 'pending'
                ]
            ]
        ];

        foreach ($marketers as $index => $marketerData) {
            // Create marketer user
            $marketer = User::create([
                'user_id' => 1000 + $index + 1, // Start from 1001
                'first_name' => $marketerData['first_name'],
                'last_name' => $marketerData['last_name'],
                'username' => $marketerData['username'],
                'email' => $marketerData['email'],
                'phone' => $marketerData['phone'],
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 5, // Marketer role
                'marketer_status' => $marketerData['marketer_status'],
                'commission_rate' => $marketerData['commission_rate'],
                'referral_code' => strtoupper(Str::random(8)),
                'bank_name' => 'Equity Bank',
                'bank_account_number' => '1234567890',
                'bank_account_name' => $marketerData['first_name'] . ' ' . $marketerData['last_name']
            ]);

            // Create marketer profile
            MarketerProfile::create([
                'user_id' => $marketer->id,
                'business_name' => $marketerData['profile_data']['business_name'],
                'business_registration' => $marketerData['profile_data']['business_registration'],
                'business_type' => 'marketing_agency',
                'address' => $marketerData['profile_data']['address'],
                'website' => 'https://' . strtolower(str_replace(' ', '', $marketerData['profile_data']['business_name'])) . '.com',
                'experience_years' => rand(2, 10),
                'target_market' => 'landlords',
                'marketing_channels' => json_encode(['social_media', 'referrals', 'online_advertising']),
                'kyc_status' => $marketerData['profile_data']['kyc_status'],
                'kyc_documents' => json_encode([
                    'id_document' => 'documents/sample_id.pdf',
                    'business_permit' => 'documents/sample_permit.pdf'
                ]),
                'approved_at' => $marketerData['profile_data']['kyc_status'] === 'verified' ? now() : null
            ]);

            // Create campaigns for active marketers
            if ($marketerData['marketer_status'] === 'active') {
                for ($i = 1; $i <= 3; $i++) {
                    ReferralCampaign::create([
                        'marketer_id' => $marketer->id,
                        'name' => "Campaign $i - " . $marketerData['profile_data']['business_name'],
                        'description' => "Marketing campaign $i for landlord acquisition",
                        'campaign_code' => strtoupper(Str::random(6) . $i),
                        'campaign_type' => $i === 1 ? 'qr_code' : 'link',
                        'target_audience' => 'landlords',
                        'budget' => rand(50000, 200000),
                        'start_date' => now()->subDays(rand(1, 30)),
                        'end_date' => now()->addDays(rand(30, 90)),
                        'status' => 'active',
                        'clicks' => rand(50, 500),
                        'conversions' => rand(5, 50)
                    ]);
                }
            }
        }

        // Create sample landlords who were referred
        $referredLandlords = [
            [
                'first_name' => 'Peter',
                'last_name' => 'Landlord',
                'username' => 'peter_landlord',
                'email' => 'peter@landlord.com',
                'phone' => '0734567890'
            ],
            [
                'first_name' => 'Mary',
                'last_name' => 'Property Owner',
                'username' => 'mary_property',
                'email' => 'mary@property.com',
                'phone' => '0745678901'
            ],
            [
                'first_name' => 'James',
                'last_name' => 'Housing',
                'username' => 'james_housing',
                'email' => 'james@housing.com',
                'phone' => '0756789012'
            ]
        ];

        $approvedMarketers = User::where('role', 5)->where('marketer_status', 'active')->get();
        $campaigns = ReferralCampaign::all();

        foreach ($referredLandlords as $index => $landlordData) {
            // Create landlord user
            $landlord = User::create([
                'user_id' => 2000 + $index + 1, // Start from 2001
                'first_name' => $landlordData['first_name'],
                'last_name' => $landlordData['last_name'],
                'username' => $landlordData['username'],
                'email' => $landlordData['email'],
                'phone' => $landlordData['phone'],
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 2 // Landlord role
            ]);

            // Create referral record
            $marketer = $approvedMarketers->random();
            $campaign = $campaigns->where('marketer_id', $marketer->id)->random();
            
            $referral = Referral::create([
                'referrer_id' => $marketer->id,
                'referred_id' => $landlord->id,
                'referral_code' => $marketer->referral_code,
                'status' => 'completed',
                'commission_amount' => rand(5000, 15000),
                'commission_status' => $index === 0 ? 'paid' : ($index === 1 ? 'approved' : 'pending'),
                'campaign_id' => $campaign->id,
                'referral_source' => $campaign->campaign_type === 'qr_code' ? 'qr_code' : 'referral_link',
                'conversion_date' => now()->subDays(rand(1, 30))
            ]);

            // Create referral reward
            ReferralReward::create([
                'referral_id' => $referral->id,
                'marketer_id' => $marketer->id,
                'landlord_id' => $landlord->id,
                'commission_amount' => $referral->commission_amount,
                'commission_percentage' => $marketer->commission_rate,
                'reward_type' => 'commission',
                'status' => $referral->commission_status,
                'calculation_date' => $referral->conversion_date,
                'approved_at' => in_array($referral->commission_status, ['approved', 'paid']) ? now() : null,
                'paid_at' => $referral->commission_status === 'paid' ? now() : null
            ]);

            // Create commission payment for paid rewards
            if ($referral->commission_status === 'paid') {
                CommissionPayment::create([
                    'marketer_id' => $marketer->id,
                    'amount' => $referral->commission_amount,
                    'payment_method' => 'bank_transfer',
                    'payment_reference' => 'PAY' . strtoupper(Str::random(8)),
                    'bank_name' => $marketer->bank_name,
                    'account_number' => $marketer->bank_account_number,
                    'account_name' => $marketer->bank_account_name,
                    'status' => 'completed',
                    'processed_at' => now(),
                    'processed_by' => 1, // Admin user ID
                    'notes' => 'Automated seeded payment'
                ]);
            }
        }

        echo "Marketer system seeder completed!\n";
        echo "Created:\n";
        echo "- 3 marketers (2 approved, 1 pending)\n";
        echo "- 6 marketing campaigns\n";
        echo "- 3 referred landlords\n";
        echo "- 3 referral records with rewards\n";
        echo "- 1 commission payment\n";
    }
}
