<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Session\SessionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MobileSessionController extends Controller
{
    protected $sessionManager;

    public function __construct(SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Store session data
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_key' => 'required|string|max:255',
            'session_data' => 'required|array',
            'expires_in_minutes' => 'nullable|integer|min:1|max:1440' // Max 24 hours
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Add metadata to session data
            $sessionData = $request->session_data;
            $sessionData['_metadata'] = [
                'created_at' => now()->toISOString(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'user_id' => $request->user()?->user_id,
                'expires_at' => now()->addMinutes($request->expires_in_minutes ?? 1440)->toISOString()
            ];

            $this->sessionManager->storeInvitationContext(
                $request->session_key,
                $sessionData
            );

            return response()->json([
                'success' => true,
                'message' => 'Session data stored successfully',
                'data' => [
                    'session_key' => $request->session_key,
                    'expires_at' => $sessionData['_metadata']['expires_at']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store session data: ' . $e->getMessage(),
                'error_code' => 'SESSION_STORE_FAILED'
            ], 500);
        }
    }

    /**
     * Retrieve session data
     */
    public function show(Request $request, string $sessionKey): JsonResponse
    {
        try {
            $sessionData = $this->sessionManager->retrieveInvitationContext($sessionKey);

            if (!$sessionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session data not found or expired',
                    'error_code' => 'SESSION_NOT_FOUND'
                ], 404);
            }

            // Check if session has expired
            if (isset($sessionData['_metadata']['expires_at'])) {
                $expiresAt = \Carbon\Carbon::parse($sessionData['_metadata']['expires_at']);
                if ($expiresAt->isPast()) {
                    $this->sessionManager->clearInvitationContext($sessionKey);
                    return response()->json([
                        'success' => false,
                        'message' => 'Session has expired',
                        'error_code' => 'SESSION_EXPIRED'
                    ], 410);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'session_key' => $sessionKey,
                    'session_data' => $sessionData,
                    'metadata' => $sessionData['_metadata'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve session data: ' . $e->getMessage(),
                'error_code' => 'SESSION_RETRIEVAL_FAILED'
            ], 500);
        }
    }

    /**
     * Update session data
     */
    public function update(Request $request, string $sessionKey): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_data' => 'required|array',
            'merge' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $existingData = $this->sessionManager->retrieveInvitationContext($sessionKey);

            if (!$existingData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session data not found',
                    'error_code' => 'SESSION_NOT_FOUND'
                ], 404);
            }

            // Merge or replace session data
            if ($request->merge) {
                $newData = array_merge($existingData, $request->session_data);
            } else {
                $newData = $request->session_data;
                // Preserve metadata if it exists
                if (isset($existingData['_metadata'])) {
                    $newData['_metadata'] = $existingData['_metadata'];
                }
            }

            // Update metadata
            if (!isset($newData['_metadata'])) {
                $newData['_metadata'] = [];
            }
            $newData['_metadata']['updated_at'] = now()->toISOString();
            $newData['_metadata']['user_id'] = $request->user()?->user_id;

            $this->sessionManager->storeInvitationContext($sessionKey, $newData);

            return response()->json([
                'success' => true,
                'message' => 'Session data updated successfully',
                'data' => [
                    'session_key' => $sessionKey,
                    'updated_at' => $newData['_metadata']['updated_at']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update session data: ' . $e->getMessage(),
                'error_code' => 'SESSION_UPDATE_FAILED'
            ], 500);
        }
    }

    /**
     * Delete session data
     */
    public function destroy(Request $request, string $sessionKey): JsonResponse
    {
        try {
            $this->sessionManager->clearInvitationContext($sessionKey);

            return response()->json([
                'success' => true,
                'message' => 'Session data deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete session data: ' . $e->getMessage(),
                'error_code' => 'SESSION_DELETE_FAILED'
            ], 500);
        }
    }

    /**
     * Check if session exists
     */
    public function exists(Request $request, string $sessionKey): JsonResponse
    {
        try {
            $exists = $this->sessionManager->hasInvitationContext($sessionKey);

            return response()->json([
                'success' => true,
                'data' => [
                    'session_key' => $sessionKey,
                    'exists' => $exists
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check session existence: ' . $e->getMessage(),
                'error_code' => 'SESSION_CHECK_FAILED'
            ], 500);
        }
    }

    /**
     * Cleanup expired sessions
     */
    public function cleanup(Request $request): JsonResponse
    {
        try {
            $cleanedCount = $this->sessionManager->cleanupExpiredSessions();

            return response()->json([
                'success' => true,
                'message' => 'Session cleanup completed',
                'data' => [
                    'cleaned_sessions' => $cleanedCount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session cleanup failed: ' . $e->getMessage(),
                'error_code' => 'SESSION_CLEANUP_FAILED'
            ], 500);
        }
    }

    /**
     * Get session statistics
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            // This would require additional methods in SessionManager
            // For now, return basic stats
            return response()->json([
                'success' => true,
                'data' => [
                    'active_sessions' => 'Not implemented',
                    'expired_sessions' => 'Not implemented',
                    'total_sessions' => 'Not implemented',
                    'cleanup_last_run' => 'Not implemented'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve session stats: ' . $e->getMessage(),
                'error_code' => 'SESSION_STATS_FAILED'
            ], 500);
        }
    }
}