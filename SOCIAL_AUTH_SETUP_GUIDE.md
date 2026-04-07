# Social Authentication Setup Guide

## Quick Start

### 1. Install Laravel Socialite
```bash
composer require laravel/socialite
```

### 2. Add Environment Variables
Copy these to your `.env` file and replace with your actual credentials:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URL=https://yourdomain.com/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URL=https://yourdomain.com/auth/facebook/callback

# GitHub OAuth
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
GITHUB_REDIRECT_URL=https://yourdomain.com/auth/github/callback
```

## Provider Setup Instructions

### Google OAuth Setup

1. **Go to Google Cloud Console**
   - Visit: https://console.cloud.google.com/

2. **Create/Select Project**
   - Click "Select a project" → "New Project"
   - Name it "EasyRent" or your app name
   - Click "Create"

3. **Enable Google+ API**
   - Go to "APIs & Services" → "Library"
   - Search for "Google+ API"
   - Click "Enable"

4. **Create OAuth Credentials**
   - Go to "APIs & Services" → "Credentials"
   - Click "Create Credentials" → "OAuth 2.0 Client ID"
   - Configure consent screen if prompted:
     - User Type: External
     - App name: EasyRent
     - User support email: your-email@domain.com
     - Developer contact: your-email@domain.com
   - Application type: Web application
   - Name: EasyRent Web Client
   - Authorized redirect URIs:
     - `http://localhost:8000/auth/google/callback` (for local testing)
     - `https://yourdomain.com/auth/google/callback` (for production)

5. **Copy Credentials**
   - Copy "Client ID" → paste as `GOOGLE_CLIENT_ID` in `.env`
   - Copy "Client Secret" → paste as `GOOGLE_CLIENT_SECRET` in `.env`

### Facebook OAuth Setup

1. **Go to Facebook Developers**
   - Visit: https://developers.facebook.com/

2. **Create App**
   - Click "My Apps" → "Create App"
   - Select "Consumer" as app type
   - App name: EasyRent
   - App contact email: your-email@domain.com
   - Click "Create App"

3. **Add Facebook Login**
   - In dashboard, click "Add Product"
   - Find "Facebook Login" → Click "Set Up"
   - Select "Web"
   - Site URL: `https://yourdomain.com`

4. **Configure OAuth Settings**
   - Go to "Facebook Login" → "Settings"
   - Valid OAuth Redirect URIs:
     - `http://localhost:8000/auth/facebook/callback` (for local)
     - `https://yourdomain.com/auth/facebook/callback` (for production)
   - Click "Save Changes"

5. **Get App Credentials**
   - Go to "Settings" → "Basic"
   - Copy "App ID" → paste as `FACEBOOK_CLIENT_ID` in `.env`
   - Copy "App Secret" (click "Show") → paste as `FACEBOOK_CLIENT_SECRET` in `.env`

6. **Make App Live** (for production)
   - Toggle "App Mode" from "Development" to "Live"
   - Complete app review if required

### GitHub OAuth Setup

1. **Go to GitHub Settings**
   - Visit: https://github.com/settings/developers

2. **Create OAuth App**
   - Click "OAuth Apps" → "New OAuth App"
   - Application name: EasyRent
   - Homepage URL: `https://yourdomain.com`
   - Application description: Property rental platform
   - Authorization callback URL:
     - For local: `http://localhost:8000/auth/github/callback`
     - For production: `https://yourdomain.com/auth/github/callback`
   - Click "Register application"

3. **Get Credentials**
   - Copy "Client ID" → paste as `GITHUB_CLIENT_ID` in `.env`
   - Click "Generate a new client secret"
   - Copy "Client Secret" → paste as `GITHUB_CLIENT_SECRET` in `.env`

## Testing

### Local Testing

1. **Update Callback URLs**
   ```env
   GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback
   FACEBOOK_REDIRECT_URL=http://localhost:8000/auth/facebook/callback
   GITHUB_REDIRECT_URL=http://localhost:8000/auth/github/callback
   ```

2. **Test Registration**
   - Go to `http://localhost:8000/register`
   - Click on any social button
   - Authorize the app
   - Should redirect back and create account

### Production Testing

1. **Update Callback URLs**
   ```env
   GOOGLE_REDIRECT_URL=https://yourdomain.com/auth/google/callback
   FACEBOOK_REDIRECT_URL=https://yourdomain.com/auth/facebook/callback
   GITHUB_REDIRECT_URL=https://yourdomain.com/auth/github/callback
   ```

2. **Ensure HTTPS**
   - OAuth requires HTTPS in production
   - Install SSL certificate (Let's Encrypt recommended)

3. **Test Each Provider**
   - Test Google login
   - Test Facebook login
   - Test GitHub login

## Troubleshooting

### "redirect_uri_mismatch" Error
- **Cause**: Callback URL doesn't match exactly
- **Fix**: Ensure URLs in provider settings match `.env` exactly (including http/https)

### "invalid_client" Error
- **Cause**: Wrong Client ID or Secret
- **Fix**: Double-check credentials in `.env`

### "App Not Setup" Error (Facebook)
- **Cause**: App is in development mode
- **Fix**: Make app live or add test users

### "Unauthorized" Error
- **Cause**: API not enabled or app not approved
- **Fix**: Enable required APIs in provider console

### Users Can't See Social Buttons
- **Cause**: JavaScript error or missing Font Awesome
- **Fix**: Check browser console, ensure Font Awesome is loaded

## Security Best Practices

1. **Never Commit Credentials**
   - Keep `.env` in `.gitignore`
   - Use environment variables in production

2. **Use HTTPS in Production**
   - OAuth requires secure connections
   - Get free SSL from Let's Encrypt

3. **Restrict Redirect URIs**
   - Only add necessary callback URLs
   - Remove test URLs in production

4. **Monitor OAuth Usage**
   - Check provider dashboards regularly
   - Set up usage alerts

5. **Handle Errors Gracefully**
   - Show user-friendly error messages
   - Log errors for debugging

## Features

### What Users Get
- ✅ One-click registration
- ✅ No password to remember (for social auth)
- ✅ Auto-verified email
- ✅ Profile photo from social account
- ✅ Faster onboarding

### What You Get
- ✅ Higher conversion rates
- ✅ Verified user emails
- ✅ Reduced support tickets
- ✅ Better user data
- ✅ Lower abandonment rates

## Next Steps

1. **Install Socialite**: `composer require laravel/socialite`
2. **Set up providers**: Follow guides above
3. **Test locally**: Use localhost callbacks
4. **Deploy**: Update to production URLs
5. **Monitor**: Check registration analytics

## Support

If you need help:
- Check provider documentation
- Review error logs: `storage/logs/laravel.log`
- Test with different browsers
- Verify SSL certificate is valid

## Additional Resources

- [Laravel Socialite Docs](https://laravel.com/docs/socialite)
- [Google OAuth Guide](https://developers.google.com/identity/protocols/oauth2)
- [Facebook Login Guide](https://developers.facebook.com/docs/facebook-login)
- [GitHub OAuth Guide](https://docs.github.com/en/developers/apps/building-oauth-apps)
