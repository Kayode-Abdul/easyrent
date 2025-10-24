@if(Auth::check())
@php
    // Default to showing all available roles for this user
    $roleMap = [
        7 => 'admin',
        2 => 'landlord', 
        1 => 'tenant', 
        6 => 'property_manager',
        3 => 'marketer',
        5 => 'artisan',
        9 => 'regional_manager'
    ];
    
    // Get all roles the user has
    $allUserRoles = [];
    
    // Check admin status
    if (Auth::user()->admin == 1) {
        $allUserRoles[] = 'admin';
    }
    
    // Check role from role column
    if (isset(Auth::user()->role) && isset($roleMap[Auth::user()->role])) {
        $allUserRoles[] = $roleMap[Auth::user()->role];
    }
    
    // Add roles from passed $userRoles variable if available
    if (isset($userRoles) && is_array($userRoles)) {
        $allUserRoles = array_unique(array_merge($allUserRoles, $userRoles));
    }
    
    // Only show role switcher if the user has more than one role
    $showRoleSwitcher = count($allUserRoles) > 1;
    
    // Role icons mapping
    $roleIcons = [
        'admin' => 'nc-icon nc-settings-gear-65',
        'regional_manager' => 'nc-icon nc-chart-pie-36',
        'landlord' => 'nc-icon nc-bank',
        'marketer' => 'nc-icon nc-single-02',
        'property_manager' => 'nc-icon nc-istanbul',
        'tenant' => 'nc-icon nc-key-25'
    ];
    
    // Get current role from $primaryRole or default
    $currentRole = $primaryRole ?? '';
@endphp

@if($showRoleSwitcher)
<div class="role-switcher">
    <h5>Switch Your Role</h5>
    <div class="role-buttons">
        @foreach($allUserRoles as $role)
            <form action="{{ route('switch.role') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="role" value="{{ $role }}">
                <button type="submit" class="role-button {{ $currentRole == $role ? 'active' : '' }}">
                    <i class="{{ $roleIcons[$role] ?? 'nc-icon nc-single-02' }}"></i> {{ ucfirst(str_replace('_', ' ', $role)) }}
                </button>
            </form>
        @endforeach
    </div>
    <div class="role-hint mt-2">
        <small class="text-muted">Currently viewing as: <strong>{{ ucfirst(str_replace('_', ' ', $currentRole)) }}</strong></small>
    </div>
</div>
@endif

<style>
    .role-switcher {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .role-switcher h5 {
        margin-top: 0;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    
    .role-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .role-button {
        padding: 8px 15px;
        border-radius: 4px;
        background-color: #e9ecef;
        color: #495057;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.85rem;
        border: none;
        cursor: pointer;
    }
    
    .role-button:hover {
        background-color: #007bff;
        color: white;
        text-decoration: none;
    }
    
    .role-button.active {
        background-color: #007bff;
        color: white;
    }
    
    .role-button i {
        font-size: 1rem;
    }
</style>
@endif
