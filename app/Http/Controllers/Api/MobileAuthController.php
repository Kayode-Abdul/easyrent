<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApartmentInvitation;
use App\Services\Session\SessionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class MobileAuthController extends Controller
{
    protected $sessionManager;

    public function __construct(SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Mobile login endpoint
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'invitation_token' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'error_code' => 'INVALID_CREDENTIALS'
            ], 401);
        }

        $user = Auth::user();
        
        // Create API token for mobile app
        $token = $user->createToken('mobile-app')->plainTextToken;

        // Handle invitation context if provided
        $invitationContext = null;
        if ($request->invitation_token) {
            $invitationContext = $this->sessionManager->retrieveInvitationContext($request->invitation_token);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->user_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'roles' => $user->roles->pluck('name')->toArray()
                ],
                'token' => $token,
                'invitation_context' => $invitationContext
            ]
        ]);
    }

    /**
     * Mobile registration endpoint
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'invitation_token' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate unique user ID
            do {
                $userId = mt_rand(1000000, 9999999);
            } while (User::where('user_id', $userId)->exists());

            $user = User::create([
                'user_id' => $userId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'registration_source' => $request->invitation_token ? 'easyrent_invitation' : 'mobile_app',
                'email_verified_at' => now() // Auto-verify for mobile registrations
            ]);

            // Assign default tenant role
            $tenantRole = \App\Models\Role::where('name', 'tenant')->first();
            if ($tenantRole) {
                $user->roles()->attach($tenantRole->id);
            }

            // Create API token
            $token = $user->createToken('mobile-app')->plainTextToken;

            // Handle invitation context if provided
            $invitationContext = null;
            if ($request->invitation_token) {
                $invitationContext = $this->sessionManager->retrieveInvitationContext($request->invitation_token);
                
                // Store invitation context in user session for later use
                if ($invitationContext) {
                    $this->sessionManager->storeInvitationContext(
                        $request->invitation_token . '_user_' . $user->user_id,
                        $invitationContext
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $user->user_id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'roles' => $user->roles->pluck('name')->toArray()
                    ],
                    'token' => $token,
                    'invitation_context' => $invitationContext
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
                'error_code' => 'REGISTRATION_FAILED'
            ], 500);
        }
    }

    /**
     * Logout endpoint
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke the current access token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->user_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'registration_source' => $user->registration_source,
                    'created_at' => $user->created_at,
                    'email_verified_at' => $user->email_verified_at
                ]
            ]
        ]);
    }

    /**
     * Refresh token endpoint
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $newToken = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed: ' . $e->getMessage()
            ], 500);
        }
    }
}