# Quick Start: Social Authentication

## 1. Install Package (1 minute)
```bash
composer require laravel/socialite
```

## 2. Add to .env (2 minutes)
```env
# Google
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URL=https://yourdomain.com/auth/google/callback

# Facebook
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URL=https://yourdomain.com/auth/facebook/callback

# GitHub
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
GITHUB_REDIRECT_URL=https://yourdomain.com/auth/github/callback
```

## 3. Get OAuth Credentials

### Google (5 minutes)
1. Go to: https://console.cloud.google.com/
2. Create project → Enable Google+ API
3. Credentials → OAuth 2.0 Client ID
4. Add redirect: `https://yourdomain.com/auth/google/callback`
5. Copy Client ID & Secret

### Facebook (5 minutes)
1. Go to: https://developers.facebook.com/
2. Create App → Add Facebook Login
3. Settings → Valid OAuth Redirect URIs
4. Add: `https://yourdomain.com/auth/facebook/callback`
5. Copy App ID & Secret

### GitHub (3 minutes)
1. Go to: https://github.com/settings/developers
2. New OAuth App
3. Callback: `https://yourdomain.com/auth/github/callback`
4. Copy Client ID & Secret

## 4. Test (1 minute)
1. Go to `/register`
2. Click social button
3. Authorize
4. Should create account & login

## Done! ✅

Total time: ~15-20 minutes

## What Changed?

### Registration Form
- **Before**: 11 required fields, 2 steps
- **After**: 4 required fields, 1 step
- **Added**: Google, Facebook, GitHub login

### Required Fields Now
1. First Name
2. Last Name
3. Phone Number
4. Email
5. Password

### Auto-Generated
- Username (from email)
- Role (defaults to Tenant)

## Troubleshooting

### "redirect_uri_mismatch"
→ Check callback URL matches exactly in provider settings

### "invalid_client"
→ Double-check Client ID and Secret in .env

### Buttons not showing
→ Check browser console for JavaScript errors

## Files Changed
- ✅ `RegisterController.php` - Simplified validation
- ✅ `register.blade.php` - Added social buttons
- ✅ `SocialAuthController.php` - New file
- ✅ `web.php` - Added routes
- ✅ `services.php` - Added config
- ✅ `.env.example` - Added variables

## Need Help?
See detailed guides:
- `SOCIAL_AUTH_SETUP_GUIDE.md` - Step-by-step setup
- `SIMPLIFIED_REGISTRATION_AND_SOCIAL_AUTH.md` - Full documentation
- `REGISTRATION_SIMPLIFICATION_SUMMARY.md` - Complete summary
