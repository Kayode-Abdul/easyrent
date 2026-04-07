# Email Configuration Guide for EasyRent

## 📧 Email Setup for Verification & Password Reset

### 1. Configure .env File

Add these settings to your `.env` file:

```env
# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="EasyRent"

# App Configuration
APP_NAME="EasyRent"
APP_URL=http://your-domain.com
```

### 2. Gmail Setup (Recommended)

1. **Enable 2-Factor Authentication** on your Gmail account
2. **Generate App Password:**
   - Go to Google Account Settings
   - Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
   - Use this password in `MAIL_PASSWORD`

### 3. Alternative Email Providers

#### Mailgun (Production Recommended)
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-secret
```

#### SendGrid
```env
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=your-sendgrid-api-key
```

#### Mailtrap (Testing)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
```

### 4. Test Email Configuration

Run this command to test:
```bash
php artisan tinker
```

Then in tinker:
```php
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

### 5. Queue Configuration (Optional but Recommended)

For better performance, use queues for emails:

```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

## 🔧 How It Works

### Email Verification Flow:
1. User registers → `email_verified_at` is NULL
2. Verification email sent automatically
3. User clicks link → `email_verified_at` is set
4. User can access protected features

### Password Reset Flow:
1. User clicks "Forgot Password"
2. Enters email → Reset link sent
3. User clicks link → Reset form shown
4. New password set → User can login

### Middleware Protection:
```php
// Protect routes that require verified email
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

## 🎨 UI Features

### Modern Toast Notifications:
- ✅ Success messages with animations
- ❌ Error messages with proper styling
- ⚠️ Warning messages for unverified accounts
- ℹ️ Info messages for instructions

### Responsive Design:
- 📱 Mobile-friendly forms
- 🖥️ Desktop optimized layouts
- 🎯 Touch-friendly buttons
- ♿ Accessibility compliant

## 🚀 Testing

### Test Email Verification:
1. Register new account
2. Check email for verification link
3. Click link to verify
4. Login should work normally

### Test Password Reset:
1. Go to login page
2. Click "Forgot Password"
3. Enter email address
4. Check email for reset link
5. Click link and set new password
6. Login with new password

## 🔒 Security Features

### Built-in Protection:
- ✅ CSRF protection on all forms
- ✅ Rate limiting on password reset
- ✅ Secure token generation
- ✅ Email verification required
- ✅ Password confirmation for sensitive actions

### Additional Security:
- 🔐 Strong password requirements
- 🕒 Token expiration (60 minutes default)
- 🚫 Brute force protection
- 📧 Email notifications for security events