<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\RegionalScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegionalManagerManagementController extends Controller
{
    /**
     * Display regional managers and their assigned regions
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $region = $request->get('region');
        
        // Get Regional Manager role
        $regionalManagerRole = Role::where('name', 'regional_manager')->orWhere('id', 9)->first();
        
        if (!$regionalManagerRole) {
            return redirect()->back()->with('error', 'Regional Manager role not found');
        }
        
        // Build query for regional managers
        $query = User::whereHas('roles', function($q) use ($regionalManagerRole) {
            $q->where('role_id', $regionalManagerRole->id);
        })->with(['regionalScopes']);
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply region filter
        if ($region) {
            $query->whereHas('regionalScopes', function($q) use ($region) {
                $q->where('scope_value', 'LIKE', "%{$region}%");
            });
        }
        
        $regionalManagers = $query->orderBy('first_name')->paginate(20);
        
        // Get all unique regions for filter dropdown
        $allRegions = RegionalScope::where('scope_type', 'state')
            ->distinct()
            ->pluck('scope_value')
            ->sort();
        
        return view('admin.regional-managers.index', compact(
            'regionalManagers', 
            'allRegions', 
            'search', 
            'region'
        ));
    }
    
    /**
     * Show detailed view of a regional manager and their scopes
     */
    public function show(Request $request, User $regionalManager)
    {
        // Verify user is a regional manager
        if (!$regionalManager->hasRole('regional_manager')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'User is not a Regional Manager'], 404);
            }
            return redirect()->back()->with('error', 'User is not a Regional Manager');
        }
        
        $scopes = $regionalManager->getFormattedRegionalScopes();
        $rawScopes = $regionalManager->regionalScopes()->get();
        
        // Get statistics for this regional manager
        $stats = [
            'total_scopes' => $rawScopes->count(),
            'state_scopes' => $rawScopes->where('scope_type', 'state')->count(),
            'lga_scopes' => $rawScopes->where('scope_type', 'lga')->count(),
        ];
        
        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'manager' => [
                    'id' => $regionalManager->user_id,
                    'name' => $regionalManager->first_name . ' ' . $regionalManager->last_name,
                    'email' => $regionalManager->email,
                ],
                'regions' => $rawScopes->map(function($scope) {
                    return [
                        'id' => $scope->id,
                        'state' => $scope->scope_value,
                        'lga' => $scope->scope_type === 'lga' ? $scope->scope_value : null,
                        'type' => $scope->scope_type
                    ];
                }),
                'stats' => $stats
            ]);
        }
        
        return view('admin.regional-managers.show', compact(
            'regionalManager', 
            'scopes', 
            'rawScopes', 
            'stats'
        ));
    }
    
    /**
     * Show form to assign new regions to a regional manager
     */
    public function assignRegions(User $regionalManager)
    {
        // Verify user is a regional manager
        if (!$regionalManager->hasRole('regional_manager')) {
            return redirect()->back()->with('error', 'User is not a Regional Manager');
        }
        
        $currentScopes = $regionalManager->getFormattedRegionalScopes();
        
        // Get available states (you might want to make this configurable)
        $availableStates = [
            'Lagos', 'Abuja', 'Kano', 'Rivers', 'Oyo', 'Kaduna', 
            'Ogun', 'Ondo', 'Delta', 'Anambra', 'Imo', 'Enugu'
        ];
        
        // Get available LGAs for each state (simplified - you might want to store this in DB)
        $availableLgas = [
            'Lagos' => ['Ikeja', 'Victoria Island', 'Lekki', 'Surulere', 'Yaba', 'Apapa', 'Ikoyi'],
            'Abuja' => ['Garki', 'Wuse', 'Maitama', 'Asokoro', 'Gwarinpa', 'Kubwa'],
            'Kano' => ['Kano Municipal', 'Fagge', 'Dala', 'Gwale', 'Tarauni'],
            // Add more as needed
        ];
        
        return view('admin.regional-managers.assign-regions', compact(
            'regionalManager', 
            'currentScopes', 
            'availableStates', 
            'availableLgas'
        ));
    }
    
    /**
     * Store new regional assignments
     */
    public function storeRegionalAssignments(Request $request, User $regionalManager)
    {
        $request->validate([
            'states' => 'required|array|min:1',
            'states.*' => 'required|string',
            'lgas' => 'array',
            'lgas.*' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $states = $request->input('states');
            $lgas = $request->input('lgas', []);
            
            // Create new scopes
            RegionalScope::createScopes($regionalManager->user_id, $states, $lgas);
            
            DB::commit();
            
            Log::info('Regional scopes assigned', [
                'regional_manager_id' => $regionalManager->user_id,
                'states' => $states,
                'lgas' => $lgas,
                'assigned_by' => auth()->id()
            ]);
            
            return redirect()
                ->route('admin.regional-managers.show', $regionalManager)
                ->with('success', 'Regional assignments added successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign regional scopes', [
                'error' => $e->getMessage(),
                'regional_manager_id' => $regionalManager->user_id
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to assign regions: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove a specific regional scope from a regional manager
     */
    public function removeRegionalScope(Request $request, User $regionalManager)
    {
        $request->validate([
            'scope_id' => 'required|exists:regional_scopes,id'
        ]);
        
        try {
            $scope = RegionalScope::where('id', $request->scope_id)
                ->where('user_id', $regionalManager->user_id)
                ->firstOrFail();
            
            $scopeDescription = $scope->scope_type . ': ' . $scope->scope_value;
            
            $scope->delete();
            
            Log::info('Regional scope removed', [
                'regional_manager_id' => $regionalManager->user_id,
                'scope_removed' => $scopeDescription,
                'removed_by' => auth()->id()
            ]);
            
            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Regional scope '{$scopeDescription}' removed successfully"
                ]);
            }
            
            return redirect()
                ->back()
                ->with('success', "Regional scope '{$scopeDescription}' removed successfully");
                
        } catch (\Exception $e) {
            Log::error('Failed to remove regional scope', [
                'error' => $e->getMessage(),
                'regional_manager_id' => $regionalManager->user_id,
                'scope_id' => $request->scope_id
            ]);
            
            // Return JSON error for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove regional scope: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Failed to remove regional scope: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove all regional scopes for a regional manager
     */
    public function removeAllRegionalScopes(User $regionalManager)
    {
        try {
            $scopeCount = $regionalManager->regionalScopes()->count();
            
            $regionalManager->regionalScopes()->delete();
            
            Log::info('All regional scopes removed', [
                'regional_manager_id' => $regionalManager->user_id,
                'scopes_removed_count' => $scopeCount,
                'removed_by' => auth()->id()
            ]);
            
            return redirect()
                ->back()
                ->with('success', "All regional assignments ({$scopeCount} scopes) removed successfully");
                
        } catch (\Exception $e) {
            Log::error('Failed to remove all regional scopes', [
                'error' => $e->getMessage(),
                'regional_manager_id' => $regionalManager->user_id
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to remove all regional assignments: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk assign regions to multiple regional managers
     */
    public function bulkAssignRegions(Request $request)
    {
        // Normalize comma-separated IDs to array if needed
        $ids = $request->input('regional_manager_ids');
        if (is_string($ids)) {
            $ids = collect(explode(',', $ids))
                ->map(fn($v) => trim($v))
                ->filter()
                ->values()
                ->all();
            $request->merge(['regional_manager_ids' => $ids]);
        }

        $request->validate([
            'regional_manager_ids' => 'required|array|min:1',
            'regional_manager_ids.*' => 'exists:users,user_id',
            'states' => 'required|array|min:1',
            'states.*' => 'required|string',
            'lgas' => 'array',
            'lgas.*' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $regionalManagerIds = $request->input('regional_manager_ids');
            $states = $request->input('states');
            $lgas = $request->input('lgas', []);
            
            $assignedCount = 0;
            
            foreach ($regionalManagerIds as $managerId) {
                RegionalScope::createScopes($managerId, $states, $lgas);
                $assignedCount++;
            }
            
            DB::commit();
            
            Log::info('Bulk regional scopes assigned', [
                'regional_manager_ids' => $regionalManagerIds,
                'states' => $states,
                'lgas' => $lgas,
                'assigned_by' => auth()->id(),
                'managers_affected' => $assignedCount
            ]);
            
            return redirect()
                ->back()
                ->with('success', "Regional assignments added to {$assignedCount} regional managers successfully");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk assign regional scopes', [
                'error' => $e->getMessage(),
                'regional_manager_ids' => $request->input('regional_manager_ids')
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to bulk assign regions: ' . $e->getMessage());
        }
    }
    
    /**
     * Get regional managers data for AJAX requests
     */
    public function getRegionalManagersData(Request $request)
    {
        $regionalManagerRole = Role::where('name', 'regional_manager')->orWhere('id', 9)->first();
        
        if (!$regionalManagerRole) {
            return response()->json(['error' => 'Regional Manager role not found'], 404);
        }
        
        $regionalManagers = User::whereHas('roles', function($q) use ($regionalManagerRole) {
            $q->where('role_id', $regionalManagerRole->id);
        })->with(['regionalScopes'])->get();
        
        $data = $regionalManagers->map(function($manager) {
            $scopes = $manager->getFormattedRegionalScopes();
            return [
                'id' => $manager->user_id,
                'name' => $manager->first_name . ' ' . $manager->last_name,
                'email' => $manager->email,
                'scopes_count' => $scopes->count(),
                'regions' => $scopes->map(function($scope) {
                    return $scope->state . ($scope->lga ? ' / ' . $scope->lga : ' (All LGAs)');
                })->toArray()
            ];
        });
        
        return response()->json($data);
    }
    
    /**
     * Update regional manager details
     */
    public function updateRegionalManager(Request $request, $regionalManagerId)
    {
        $regionalManager = User::findOrFail($regionalManagerId);
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $regionalManager->user_id . ',user_id',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update user details
            $regionalManager->first_name = $request->first_name;
            $regionalManager->last_name = $request->last_name;
            $regionalManager->email = $request->email;
            $regionalManager->save();
            
            DB::commit();
            
            return redirect()
                ->route('admin.regional-managers.index')
                ->with('success', 'Regional manager updated successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update regional manager: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove regional manager role entirely from a user
     */
    public function removeRegionalManagerRole(User $regionalManager)
    {
        try {
            DB::beginTransaction();
            
            // Remove all regional scopes first
            $scopeCount = $regionalManager->regionalScopes()->count();
            $regionalManager->regionalScopes()->delete();
            
            // Remove Regional Manager role
            $regionalManagerRole = Role::where('name', 'regional_manager')->orWhere('id', 9)->first();
            if ($regionalManagerRole) {
                $regionalManager->roles()->detach($regionalManagerRole->id);
            }
            
            // If user has legacy role system, update role field
            if (property_exists($regionalManager, 'role') && $regionalManager->role == 8) {
                $regionalManager->role = 1; // Set to basic user role
                $regionalManager->save();
            }
            
            DB::commit();
            
            Log::info('Regional Manager role removed', [
                'user_id' => $regionalManager->user_id,
                'user_name' => $regionalManager->first_name . ' ' . $regionalManager->last_name,
                'scopes_removed' => $scopeCount,
                'removed_by' => auth()->id()
            ]);
            
            return redirect()
                ->route('admin.regional-managers.index')
                ->with('success', "Regional Manager role removed from {$regionalManager->first_name} {$regionalManager->last_name}. {$scopeCount} regional assignments were also removed.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove Regional Manager role', [
                'error' => $e->getMessage(),
                'user_id' => $regionalManager->user_id
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to remove Regional Manager role: ' . $e->getMessage());
        }
    }
}