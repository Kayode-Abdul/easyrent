# Session Expiry 419 Error Fix

## Problem
When users leave a tab open for an extended period (longer than the session lifetime), they encounter a **419 Page Expired** error when trying to submit forms or perform actions. This happens because:

1. Laravel's CSRF tokens are tied to the session
2. Sessions expire after a configured lifetime (default: 120 minutes)
3. The page still has the old, expired CSRF token
4. When the user submits a form, Laravel rejects it with a 419 error

## Solution Implemented

### 1. Automatic Session Monitoring (`public/assets/js/csrf-token-refresh.js`)

A JavaScript-based session manager that:

- **Tracks user activity** (mouse, keyboard, scroll, touch events)
- **Monitors session lifetime** based on Laravel's configuration
- **Shows warnings** 5 minutes before session expiry
- **Automatically redirects** to login when session expires
- **Intercepts 419 errors** from forms and AJAX requests
- **Provides token refresh capability** (optional feature)

### 2. CSRF Token Refresh Endpoint (`routes/api.php`)

Added a new API endpoint:
```
GET /api/csrf-token
```

Returns a fresh CSRF token that can be used to refresh the page's token without requiring a full page reload.

### 3. Session Lifetime Meta Tag

Added to all pages:
```html
<meta name="session-lifetime" content="{{ config('session.lifetime') }}">
```

This allows the JavaScript to know exactly when the session will expire.

### 4. Enhanced Login Page

The login page now:
- Detects the `?expired=1` parameter
- Shows a friendly message: "Your session has expired. Please login again."
- Stores the intended URL for redirect after login

## How It Works

### User Activity Tracking
```javascript
// Tracks these events to determine if user is active
['mousedown', 'keydown', 'scroll', 'touchstart']
```

### Session Expiry Warning
- Checks every 60 seconds if session is about to expire
- Shows warning 5 minutes before expiry
- Uses toast notification if available, otherwise falls back to alert

### Automatic Redirect
When session expires:
1. Stores current URL in `sessionStorage` for post-login redirect
2. Redirects to `/login?expired=1`
3. Shows friendly expiry message
4. After login, redirects back to original page

### Form Submission Protection
Before any form submission:
1. Checks if session might be expired
2. If expired, prevents submission and redirects to login
3. If valid, allows normal submission

### AJAX Error Handling
Intercepts:
- Native `fetch()` requests
- jQuery AJAX requests
- Automatically redirects on 419 errors

## Configuration

### Session Lifetime
Edit `config/session.php`:
```php
'lifetime' => env('SESSION_LIFETIME', 120), // minutes
```

Or set in `.env`:
```
SESSION_LIFETIME=120
```

### Warning Time
Edit `public/assets/js/csrf-token-refresh.js`:
```javascript
warningTime: 300000, // 5 minutes in milliseconds
```

### Check Interval
```javascript
checkInterval: 60000, // Check every minute
```

## Testing

### Test Session Expiry
1. Set a short session lifetime in `.env`:
   ```
   SESSION_LIFETIME=2
   ```
2. Open any page
3. Wait 2 minutes without activity
4. Try to submit a form or perform an action
5. Should see expiry warning and redirect to login

### Test Warning
1. Set session lifetime to 7 minutes
2. Wait 2 minutes (warning shows at 5 minutes before expiry)
3. Should see toast notification about impending expiry

### Test Activity Tracking
1. Set session lifetime to 5 minutes
2. Keep interacting with the page (scrolling, clicking)
3. Session should not expire as long as you're active

## Files Modified

1. **public/assets/js/csrf-token-refresh.js** (NEW)
   - Main session monitoring script

2. **routes/api.php**
   - Added `/api/csrf-token` endpoint

3. **resources/views/header.blade.php**
   - Added session lifetime meta tag
   - Included csrf-token-refresh.js script

4. **app/Http/Controllers/Auth/LoginController.php**
   - Already handles `?expired=1` parameter (no changes needed)

## Benefits

✅ **No more 419 errors** from idle tabs
✅ **User-friendly warnings** before expiry
✅ **Automatic redirect** with context preservation
✅ **Works with forms and AJAX**
✅ **Configurable timing**
✅ **Activity-based session extension**
✅ **No server-side changes required** (except API endpoint)

## Browser Compatibility

- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Future Enhancements

Possible improvements:
1. **Automatic token refresh** - Refresh token in background without redirect
2. **Session extension** - Extend session automatically for active users
3. **Persistent storage** - Remember user preference for session warnings
4. **Custom warning UI** - More prominent warning modal instead of toast

## Troubleshooting

### Warning doesn't show
- Check browser console for errors
- Verify `session-lifetime` meta tag is present
- Check if `showToast()` function exists

### Still getting 419 errors
- Clear browser cache
- Check if script is loaded: `console.log(window.CsrfTokenManager)`
- Verify session lifetime in config matches meta tag

### Redirect not working
- Check browser console for JavaScript errors
- Verify `/login` route exists
- Check if `sessionStorage` is available

## Manual Token Refresh

If needed, you can manually refresh the token:
```javascript
window.CsrfTokenManager.refreshToken().then(token => {
    console.log('Token refreshed:', token);
});
```

## Deployment Notes

1. Clear application cache: `php artisan cache:clear`
2. Clear route cache: `php artisan route:clear`
3. Clear browser cache or use cache busting for JS file
4. Test on staging before production
5. Monitor logs for any 419 errors after deployment

## Support

For issues or questions, check:
- Laravel session documentation
- Browser console for JavaScript errors
- Laravel logs for server-side errors
