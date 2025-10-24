<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\RegionalScope;
use App\Models\RoleAssignmentAudit;

class RoleManagementController extends Controller
{
    // GET /admin/dashboard/roles
    public function index()
    {
        // Load all roles modern table
        $allRoles = Role::orderBy('id')->get();
        $userCount = User::count();

        // Build role stats combining legacy numeric role field if needed
        $roles = [];
        foreach ($allRoles as $role) {
            // Count users from pivot
            $pivotCount = DB::table('role_user')->where('role_id', $role->id)->count();
            // Also count legacy users if the role maps to legacy numeric value
            $legacyCount = 0;
            $legacyMap = [
                1 => 'super_admin',
                2 => 'admin',
                3 => 'property_manager',
                4 => 'landlord',
                5 => 'tenant',
                6 => 'regional_manager',
                7 => 'marketer',
            ];
            $legacyNumeric = array_search($role->name, $legacyMap, true);
            if ($legacyNumeric !== false) {
                $legacyCount = User::where('role', $legacyNumeric)->count();
            }
            $roles[] = [
                'id' => $role->id,
                'name' => $role->display_name ?? ucfirst(str_replace('_',' ', $role->name)),
                'description' => $role->description,
                'user_count' => $pivotCount + $legacyCount,
            ];
        }

        // Include some users for quick-assign selector
        $users = User::orderBy('first_name')->limit(50)->get(['user_id','first_name','last_name','email']);

        return view('admin.roles.index', compact('roles','userCount','users','allRoles'));
    }

    // GET /admin/dashboard/roles/assign
    public function assign(Request $request)
    {
        $users = User::orderBy('first_name')->get(['user_id','first_name','last_name','email']);
        $allRoles = Role::orderBy('id')->get();
        $restricted = $this->restrictedRoles();
        $assignableRoles = $allRoles->reject(function($r) use ($restricted){
            return in_array(strtolower($r->name), $restricted, true);
        });
        $recentAudits = RoleAssignmentAudit::with(['actor','user','role'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();
        return view('admin.roles.assign', compact('users','allRoles','assignableRoles','recentAudits'));
    }

    // POST /admin/dashboard/roles/assign
    public function assignPost(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,user_id',
            'role_id' => 'nullable|integer|exists:roles,id',
            'legacy_role' => 'nullable|integer|min:1',
            'role_type' => 'nullable|string|in:modern,legacy',
            'modern_state.*' => 'sometimes|string',
            'modern_lga.*' => 'sometimes|nullable|string',
            'legacy_state.*' => 'sometimes|string',
            'legacy_lga.*' => 'sometimes|nullable|string',
        ]);

        $user = User::where('user_id', $request->user_id)->firstOrFail();

        if ($request->role_type === 'legacy' && $request->filled('legacy_role')) {
            $legacy = (int) $request->legacy_role;
            $blockedLegacy = [1,2,3,4,7]; // landlord, tenant, artisan, property manager, marketer
            if (in_array($legacy, $blockedLegacy, true)) {
                RoleAssignmentAudit::create([
                    'actor_id' => auth()->user()->user_id ?? null,
                    'user_id' => $user->user_id,
                    'legacy_role' => (string)$legacy,
                    'action' => 'blocked',
                    'reason' => 'Restricted legacy role assignment attempt'
                ]);
                return back()->withErrors(['legacy_role' => 'This legacy role cannot be manually assigned.']);
            }
            if ($legacy !== 6) { // Only regional manager allowed
                RoleAssignmentAudit::create([
                    'actor_id' => auth()->user()->user_id ?? null,
                    'user_id' => $user->user_id,
                    'legacy_role' => (string)$legacy,
                    'action' => 'blocked',
                    'reason' => 'Non-regional legacy assignment blocked'
                ]);
                return back()->withErrors(['legacy_role' => 'Only Regional Manager can be assigned via legacy pathway.']);
            }
            $user->role = $legacy; // retain legacy numeric for backward compatibility
            $user->save();
            if($legacy === 6 && $request->has('legacy_state')) {
                $this->storeScopes($user, $request->legacy_state, $request->legacy_lga);
            }
            RoleAssignmentAudit::create([
                'actor_id' => auth()->user()->user_id ?? null,
                'user_id' => $user->user_id,
                'legacy_role' => (string)$legacy,
                'action' => 'assigned',
                'reason' => 'Legacy regional manager assignment'
            ]);
            return back()->with('success', 'Legacy Regional Manager assigned successfully.');
        }

        if ($request->filled('role_id')) {
            $role = Role::findOrFail($request->role_id);
            if (in_array(strtolower($role->name), $this->restrictedRoles(), true)) {
                RoleAssignmentAudit::create([
                    'actor_id' => auth()->user()->user_id ?? null,
                    'user_id' => $user->user_id,
                    'role_id' => $role->id,
                    'action' => 'blocked',
                    'reason' => 'Restricted modern role assignment attempt'
                ]);
                return back()->withErrors(['role_id' => 'This role is lifecycle-managed and cannot be manually assigned.']);
            }
            DB::table('role_user')->updateOrInsert(
                ['user_id' => $user->user_id, 'role_id' => $role->id],
                []
            );
            if(strtolower($role->name) === 'regional_manager' && $request->has('modern_state')) {
                $this->storeScopes($user, $request->modern_state, $request->modern_lga);
            }
            RoleAssignmentAudit::create([
                'actor_id' => auth()->user()->user_id ?? null,
                'user_id' => $user->user_id,
                'role_id' => $role->id,
                'action' => 'assigned',
                'reason' => 'Modern role assignment'
            ]);
            return back()->with('success', 'Role assigned successfully.');
        }

        return back()->with('error', 'No role provided.');
    }

    /**
     * Roles that cannot be manually assigned by an admin (lifecycle / user-driven)
     */
    protected function restrictedRoles(): array
    {
        return [
            'tenant',
            'landlord',
            'marketer',
            'super_marketer',
            'property_manager',
            'artisan'
        ];
    }

    // GET /admin/dashboard/roles/audits/export
    public function exportAudits(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $cutoff = now()->subDays($days);
        $audits = RoleAssignmentAudit::with(['actor','user','role'])
            ->where('created_at','>=',$cutoff)
            ->orderByDesc('id')
            ->limit(5000) // safety cap
            ->get();

        $filename = 'role_audits_last_'.$days.'_days_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ];

        $callback = function() use ($audits) {
            $out = fopen('php://output','w');
            fputcsv($out, ['Timestamp','Actor','Target User','Action','Role','Legacy Role','Reason']);
            foreach($audits as $a){
                fputcsv($out, [
                    $a->created_at,
                    $a->actor?->first_name.' '.$a->actor?->last_name,
                    $a->user?->first_name.' '.$a->user?->last_name,
                    $a->action,
                    $a->role?->name,
                    $a->legacy_role,
                    $a->reason
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    // POST /admin/dashboard/roles/audits/prune
    public function pruneAudits(Request $request)
    {
        $request->validate([
            'keep_days' => 'required|integer|min:1|max:365'
        ]);
        $keepDays = (int)$request->keep_days;
        $cutoff = now()->subDays($keepDays);
        $deleted = RoleAssignmentAudit::where('created_at','<',$cutoff)->delete();
        return back()->with('success', "Pruned $deleted audit record(s) older than $keepDays day(s).");
    }

    protected function storeScopes(User $user, $states, $lgas)
    {
        foreach((array)$states as $idx => $state){
            if(!$state) continue;
            
            // Create state scope
            RegionalScope::firstOrCreate([
                'user_id' => $user->user_id,
                'scope_type' => 'state',
                'scope_value' => $state,
            ]);
            
            // Create LGA scope if provided
            $lga = $lgas[$idx] ?? null;
            if ($lga) {
                RegionalScope::firstOrCreate([
                    'user_id' => $user->user_id,
                    'scope_type' => 'lga',
                    'scope_value' => $state . '::' . $lga, // Store as state::lga format
                ]);
            }
        }
    }

    // GET /admin/dashboard/roles/{id}
    public function show($id)
    {
        // Accept both numeric legacy role values and modern role id
        $roleRecord = Role::find($id);
        $usersQuery = User::query();

        if ($roleRecord) {
            // Users with this modern role via pivot OR legacy numeric if mapped
            $usersQuery->where(function($q) use ($roleRecord) {
                $q->whereIn('user_id', function($sub) use ($roleRecord) {
                    $sub->select('user_id')->from('role_user')->where('role_id', $roleRecord->id);
                });
                // legacy mapping
                $map = [
                    'super_admin' => 1,
                    'admin' => 2,
                    'property_manager' => 4, // legacy used 4 for agent/property manager in some places
                    'landlord' => 1, // may vary; keep minimal mapping
                    'tenant' => 2,
                    'regional_manager' => 6,
                    'marketer' => 7,
                ];
                if (isset($map[$roleRecord->name])) {
                    $legacyVal = $map[$roleRecord->name];
                    $q->orWhere('role', $legacyVal);
                }
            });
            $title = ($roleRecord->display_name ?? ucfirst(str_replace('_',' ', $roleRecord->name))) . ' Users';
        } else {
            // If not a modern role id, treat $id as legacy numeric role
            $usersQuery->where('role', (int) $id);
            $title = 'Role #' . (int) $id . ' Users';
        }

        $users = $usersQuery->orderBy('first_name')->paginate(20);

        return view('admin.roles.show', compact('users','roleRecord','title'));
    }
}
