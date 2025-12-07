# Regional Manager AJAX Fix

## Problem
The regional manager action buttons were failing with the error:
```
Error loading regions: SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
```

This error occurred because the AJAX request was receiving HTML (likely a 404 or login page) instead of the expected JSON response.

## Root Causes Identified

1. **Wrong Role Name**: The controller was looking for role name "Regional Manager" but the actual role name in the database is "regional_manager" (lowercase with underscore).

2. **Missing Route Key Name**: The User model uses `user_id` as primary key but didn't define `getRouteKeyName()`, causing Laravel's route model binding to fail.

3. **Improper AJAX Headers**: The fetch requests weren't setting the proper headers to indicate they expected JSON responses.

4. **Controller Not Handling JSON**: The `removeRegionalScope` method only returned redirects, not JSON for AJAX requests.

## Fixes Applied

### 1. Fixed Role Name in Controller
**File**: `app/Http/Controllers/Admin/RegionalManagerManagementController.php`

Updated all role checks to use the correct role name:

```php
// Before
$regionalManagerRole = Role::where('name', 'Regional Manager')->orWhere('id', 8)->first();
if (!$regionalManager->hasRole('Regional Manager')) {

// After  
$regionalManagerRole = Role::where('name', 'regional_manager')->orWhere('id', 9)->first();
if (!$regionalManager->hasRole('regional_manager')) {
```

### 2. Fixed User Model Route Binding
**File**: `app/Models/User.php`

Added the `getRouteKeyName()` method to properly handle route model binding:

```php
/**
 * Get the route key for the model.
 */
public function getRouteKeyName()
{
    return 'user_id';
}
```

### 3. Updated AJAX Request Headers
**File**: `resources/views/admin/regional-managers/index.blade.php`

Updated the `editManagerRegions` function to include proper headers:

```javascript
fetch(`/admin/regional-managers/${managerId}`, {
    method: 'GET',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
})
```

### 4. Enhanced Error Handling
Added better error handling and debugging information:

```javascript
// Check if CSRF token exists
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (!csrfToken) {
    console.error('CSRF token meta tag not found');
    alert('CSRF token not found. Please refresh the page.');
    return;
}
```

### 5. Fixed Controller JSON Response
**File**: `app/Http/Controllers/Admin/RegionalManagerManagementController.php`

Updated `removeRegionalScope` method to handle AJAX requests:

```php
// Return JSON for AJAX requests
if ($request->expectsJson()) {
    return response()->json([
        'success' => true,
        'message' => "Regional scope '{$scopeDescription}' removed successfully"
    ]);
}
```

## How It Works Now

1. **View Button Click**: User clicks "Edit" button on regional manager
2. **AJAX Request**: JavaScript makes properly formatted AJAX request with JSON headers
3. **Route Binding**: Laravel correctly binds the `user_id` parameter to User model
4. **Controller Response**: Controller detects AJAX request and returns JSON
5. **Frontend Update**: JavaScript receives JSON and updates the modal with region data

## Testing

To test the fix:

1. Navigate to `/admin/regional-managers`
2. Click the "Edit" button (pencil icon) on any regional manager
3. The modal should open and load the assigned regions
4. You should be able to remove regions by clicking the "×" button
5. Check browser console for any errors

## Files Modified

- `app/Models/User.php` - Added route key name method
- `app/Http/Controllers/Admin/RegionalManagerManagementController.php` - Added JSON response handling
- `resources/views/admin/regional-managers/index.blade.php` - Fixed AJAX headers and error handling

## Notes

- The CSRF token meta tag already existed in `resources/views/header.blade.php`
- All routes are properly defined in `routes/web.php`
- The controller's `show` method already had JSON response capability
- Bootstrap 5 modal system is properly initialized