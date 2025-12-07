<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ApartmentInvitation;
use App\Models\Apartment;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::create([
            'user_id' => 1234567,
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'phone' => '+1234567890',
            'role' => 1,
            'email_verified_at' => now()
        ]);
        
        $this->property = Property::create([
            'user_id' => $this->user->user_id,
            'property_id' => 1234567,
            'prop_type' => 1,
            'address' => '123 Test Street',
            'state' => 'Lagos',
            'lga' => 'Ikeja',
            'no_of_apartment' => 1
        ]);
        
        $this->apartment = Apartment::create([
            'apartment_id' => 7654321,
            'property_id' => $this->property->property_id,
            'user_id' => $this->user->user_id,
            'rent' => 50000,
            'apartment_type' => '2 Bedroom',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'available' => true
        ]);
    }

    public function test_api_status_endpoint()
    {
        $response = $this->getJson('/api/status');
        
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'active',
                    'version' => '1.0.0'
                ])
                ->assertJsonStructure([
                    'status',
                    'version',
                    'timestamp',
                    'endpoints',
                    'mobile_endpoints'
                ]);
    }

    public function test_mobile_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/mobile/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
        
        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'error_code' => 'INVALID_CREDENTIALS'
                ]);
    }

    public function test_mobile_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/v1/mobile/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Login successful'
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
                            'phone',
                            'roles'
                        ],
                        'token'
                    ]
                ]);
    }

    public function test_mobile_registration()
    {
        $response = $this->postJson('/api/v1/mobile/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        
        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Registration successful'
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
                            'phone',
                            'roles'
                        ],
                        'token'
                    ]
                ]);
    }

    public function test_invitation_not_found()
    {
        $response = $this->getJson('/api/v1/mobile/invitations/nonexistent-token');
        
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invitation not found or expired',
                    'error_code' => 'INVITATION_NOT_FOUND'
                ]);
    }

    public function test_generate_invitation_link_requires_authentication()
    {
        $response = $this->postJson('/api/v1/mobile/invitations/generate', [
            'apartment_id' => $this->apartment->apartment_id
        ]);
        
        $response->assertStatus(401);
    }

    public function test_generate_invitation_link_with_authentication()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/v1/mobile/invitations/generate', [
            'apartment_id' => $this->apartment->apartment_id,
            'expires_in_hours' => 72
        ]);
        
        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Invitation link generated successfully'
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'invitation' => [
                            'id',
                            'token',
                            'url',
                            'expires_at',
                            'created_at'
                        ],
                        'apartment' => [
                            'id',
                            'rent',
                            'apartment_type'
                        ]
                    ]
                ]);
    }

    public function test_user_profile_requires_authentication()
    {
        $response = $this->getJson('/api/v1/mobile/auth/profile');
        
        $response->assertStatus(401);
    }

    public function test_user_profile_with_authentication()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->getJson('/api/v1/mobile/auth/profile');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
                            'phone',
                            'roles',
                            'registration_source',
                            'created_at',
                            'email_verified_at'
                        ]
                    ]
                ]);
    }

    public function test_session_management_requires_authentication()
    {
        $response = $this->postJson('/api/v1/mobile/sessions', [
            'session_key' => 'test_session',
            'session_data' => ['test' => 'data']
        ]);
        
        $response->assertStatus(401);
    }

    public function test_session_management_with_authentication()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/v1/mobile/sessions', [
            'session_key' => 'test_session',
            'session_data' => ['test' => 'data'],
            'expires_in_minutes' => 60
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Session data stored successfully'
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'session_key',
                        'expires_at'
                    ]
                ]);
    }
}