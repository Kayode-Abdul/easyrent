<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Payment;
use App\Models\Apartment;
use App\Models\ProfomaReceipt;
use App\Models\User;

class FlutterwaveIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_flutterwave_successful_callback_processes_payment()
    {
        // Mock the Guzzle HTTP client for verification
        $mockBody = json_encode([
            'status' => 'success',
            'data' => [
                'status' => 'successful',
                'amount' => 50000,
                'currency' => 'NGN',
                'tx_ref' => 'FLW-TEST-123',
                'payment_type' => 'card',
                'customer' => ['email' => 'test@example.com'],
                'meta' => [
                    'proforma_id' => 1
                ]
            ]
        ]);

        $mockResponse = new \GuzzleHttp\Psr7\Response(200, [], $mockBody);

        $mockClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockClient->shouldReceive('get')->once()->andReturn($mockResponse);

        $this->app->instance(\GuzzleHttp\Client::class , $mockClient);

        // Setup test data
        $landlord = User::factory()->create(['role' => 2]);
        $tenant = User::factory()->create(['role' => 1]);

        $apartment = Apartment::factory()->create([
            'user_id' => $landlord->user_id,
            'amount' => 50000,
            'occupied' => false
        ]);

        $proforma = ProfomaReceipt::factory()->create([
            'transaction_id' => 'FLW-TEST-123',
            'tenant_id' => $tenant->user_id,
            'user_id' => $landlord->user_id,
            'apartment_id' => $apartment->apartment_id,
            'amount' => 50000,
            'duration' => 12,
            'status' => ProfomaReceipt::STATUS_NEW
        ]);

        // Simulate callback
        $response = $this->get('/payment/callback?status=successful&tx_ref=FLW-TEST-123&transaction_id=123456');

        // Assert redirect to success
        $response->assertRedirect();

        // Assert payment DB entry
        $this->assertDatabaseHas('payments', [
            'transaction_id' => 'FLW-TEST-123',
            'amount' => 50000,
            'payment_method' => 'card',
            'status' => 'completed'
        ]);

        // Assert apartment is occupied
        $this->assertDatabaseHas('apartments', [
            'apartment_id' => $apartment->apartment_id,
            'occupied' => 1,
            'tenant_id' => $tenant->user_id
        ]);

        // Assert proforma is confirmed
        $this->assertDatabaseHas('profoma_receipts', [
            'id' => $proforma->id,
            'status' => ProfomaReceipt::STATUS_CONFIRMED
        ]);
    }
}