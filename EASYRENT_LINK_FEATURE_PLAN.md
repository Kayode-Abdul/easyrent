# EasyRent Link Feature - Analysis & Implementation Plan

## 🎯 Feature Overview

**EasyRent Link** is a shareable invitation system that allows landlords to generate unique links for vacant apartments, enabling prospective tenants to view property details, see proforma information, and make payments directly through the link.

## 📋 Current State Analysis

### Existing Apartment System:
- ✅ Apartments can be created without `tenant_id` (vacant state)
- ✅ Apartment model has `occupied` field (0 = vacant, 1 = occupied)
- ✅ Proforma system exists for payment processing
- ✅ Email notification system in place
- ✅ Payment processing infrastructure available

### Current Gaps:
- ❌ No visual indicator for vacant apartments in UI
- ❌ No shareable link generation for apartments
- ❌ No public apartment viewing page for prospects
- ❌ No tenant invitation workflow via links

## 🎨 UI/UX Requirements

### Visual Indicators:
1. **Copy Icon/Link Button** - Appears next to vacant apartments
2. **"EasyRent Link" Label** - Clear branding for the feature
3. **Vacant Status Badge** - Visual indicator of availability
4. **Link Sharing Options** - Copy, WhatsApp, Email, SMS

### User Experience Flow:
```
Landlord Dashboard → Vacant Apartment → Click "EasyRent Link" → 
Copy/Share Link → Prospect Clicks Link → Property Details Page → 
Payment Process → Email Notifications → Apartment Assignment
```

## 🏗️ Technical Architecture

### 1. Database Schema Changes

#### New Table: `apartment_invitations`
```sql
CREATE TABLE apartment_invitations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    apartment_id BIGINT UNSIGNED NOT NULL,
    landlord_id BIGINT UNSIGNED NOT NULL,
    invitation_token VARCHAR(64) UNIQUE NOT NULL,
    status ENUM('active', 'used', 'expired', 'cancelled') DEFAULT 'active',
    expires_at TIMESTAMP NULL,
    prospect_email VARCHAR(255) NULL,
    prospect_phone VARCHAR(20) NULL,
    prospect_name VARCHAR(255) NULL,
    viewed_at TIMESTAMP NULL,
    payment_initiated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    FOREIGN KEY (landlord_id) REFERENCES users(user_id) ON DELETE CASCADE,
    
    INDEX idx_token (invitation_token),
    INDEX idx_apartment (apartment_id),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at)
);
```

#### Apartment Model Updates:
```php
// Add to existing Apartment model
public function invitations()
{
    return $this->hasMany(ApartmentInvitation::class);
}

public function activeInvitation()
{
    return $this->hasOne(ApartmentInvitation::class)
        ->where('status', 'active')
        ->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
}

public function isVacant(): bool
{
    return $this->tenant_id === null || $this->occupied == 0;
}

public function getEasyRentLink(): ?string
{
    $invitation = $this->activeInvitation;
    return $invitation ? route('apartment.invite.show', $invitation->invitation_token) : null;
}

public function generateEasyRentLink(int $landlordId, array $options = []): string
{
    // Deactivate existing invitations
    $this->invitations()->where('status', 'active')->update(['status' => 'cancelled']);
    
    // Create new invitation
    $invitation = ApartmentInvitation::create([
        'apartment_id' => $this->id,
        'landlord_id' => $landlordId,
        'invitation_token' => Str::random(32),
        'expires_at' => $options['expires_at'] ?? now()->addDays(30),
        'status' => 'active'
    ]);
    
    return route('apartment.invite.show', $invitation->invitation_token);
}
```

### 2. New Model: ApartmentInvitation

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApartmentInvitation extends Model
{
    protected $fillable = [
        'apartment_id',
        'landlord_id', 
        'invitation_token',
        'status',
        'expires_at',
        'prospect_email',
        'prospect_phone',
        'prospect_name',
        'viewed_at',
        'payment_initiated_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'viewed_at' => 'datetime',
        'payment_initiated_at' => 'datetime'
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_USED = 'used';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id', 'user_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->update(['viewed_at' => now()]);
        }
    }

    public function markPaymentInitiated(): void
    {
        $this->update(['payment_initiated_at' => now()]);
    }

    public function markAsUsed(): void
    {
        $this->update(['status' => self::STATUS_USED]);
    }

    public function getShareableUrl(): string
    {
        return route('apartment.invite.show', $this->invitation_token);
    }

    public function getWhatsAppShareUrl(): string
    {
        $message = urlencode("Check out this apartment: " . $this->getShareableUrl());
        return "https://wa.me/?text=" . $message;
    }
}
```

### 3. Controller: ApartmentInvitationController

```php
<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\ApartmentInvitation;
use App\Mail\ApartmentInvitationMail;
use App\Mail\TenantApplicationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ApartmentInvitationController extends Controller
{
    /**
     * Generate EasyRent Link for apartment
     */
    public function generateLink(Request $request, Apartment $apartment)
    {
        // Verify landlord owns the apartment
        if ($apartment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Verify apartment is vacant
        if (!$apartment->isVacant()) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment is not vacant'
            ]);
        }

        $link = $apartment->generateEasyRentLink(auth()->id(), [
            'expires_at' => $request->expires_at ? 
                Carbon::parse($request->expires_at) : 
                now()->addDays(30)
        ]);

        return response()->json([
            'success' => true,
            'link' => $link,
            'whatsapp_url' => $apartment->activeInvitation->getWhatsAppShareUrl(),
            'message' => 'EasyRent link generated successfully!'
        ]);
    }

    /**
     * Show apartment details via invitation link
     */
    public function show(string $token)
    {
        $invitation = ApartmentInvitation::where('invitation_token', $token)
            ->with(['apartment.property', 'landlord'])
            ->firstOrFail();

        // Check if invitation is still valid
        if (!$invitation->isActive()) {
            return view('apartment.invite.expired', compact('invitation'));
        }

        // Mark as viewed
        $invitation->markAsViewed();

        // Get apartment and property details
        $apartment = $invitation->apartment;
        $property = $apartment->property;
        $landlord = $invitation->landlord;

        // Generate proforma details
        $proformaData = [
            'apartment_id' => $apartment->id,
            'property_name' => $property->prop_name,
            'apartment_type' => $apartment->apartment_type,
            'monthly_rent' => $apartment->amount,
            'duration' => 12, // Default duration
            'total_amount' => $apartment->amount * 12,
            'landlord_name' => $landlord->first_name . ' ' . $landlord->last_name,
            'landlord_email' => $landlord->email,
            'landlord_phone' => $landlord->phone,
        ];

        return view('apartment.invite.show', compact(
            'invitation', 
            'apartment', 
            'property', 
            'landlord', 
            'proformaData'
        ));
    }

    /**
     * Process tenant application via invitation
     */
    public function apply(Request $request, string $token)
    {
        $invitation = ApartmentInvitation::where('invitation_token', $token)
            ->with(['apartment', 'landlord'])
            ->firstOrFail();

        if (!$invitation->isActive()) {
            return redirect()->route('apartment.invite.expired', $token);
        }

        $request->validate([
            'tenant_name' => 'required|string|max:255',
            'tenant_email' => 'required|email|max:255',
            'tenant_phone' => 'required|string|max:20',
            'duration' => 'required|integer|min:1|max:24',
            'move_in_date' => 'required|date|after:today'
        ]);

        // Update invitation with prospect details
        $invitation->update([
            'prospect_name' => $request->tenant_name,
            'prospect_email' => $request->tenant_email,
            'prospect_phone' => $request->tenant_phone
        ]);

        // Create payment invitation
        $paymentInvitation = PaymentInvitation::create([
            'apartment_id' => $invitation->apartment->id,
            'landlord_id' => $invitation->landlord_id,
            'tenant_email' => $request->tenant_email,
            'tenant_phone' => $request->tenant_phone,
            'tenant_name' => $request->tenant_name,
            'amount' => $invitation->apartment->amount * $request->duration,
            'duration' => $request->duration,
            'move_in_date' => $request->move_in_date,
            'status' => 'pending',
            'invitation_token' => $invitation->invitation_token
        ]);

        // Mark payment initiated
        $invitation->markPaymentInitiated();

        // Send emails
        $this->sendApplicationEmails($invitation, $paymentInvitation, $request);

        return redirect()->route('apartment.invite.payment', [
            'token' => $token,
            'payment_id' => $paymentInvitation->id
        ]);
    }

    /**
     * Show payment page for apartment
     */
    public function payment(string $token, PaymentInvitation $paymentInvitation)
    {
        $invitation = ApartmentInvitation::where('invitation_token', $token)->firstOrFail();
        
        return view('apartment.invite.payment', compact(
            'invitation', 
            'paymentInvitation'
        ));
    }

    /**
     * Send application emails to landlord and tenant
     */
    private function sendApplicationEmails($invitation, $paymentInvitation, $request)
    {
        // Email to landlord
        Mail::to($invitation->landlord->email)->send(
            new TenantApplicationMail($invitation, $paymentInvitation, 'landlord')
        );

        // Email to tenant
        Mail::to($request->tenant_email)->send(
            new TenantApplicationMail($invitation, $paymentInvitation, 'tenant')
        );
    }
}
```

## 🎨 Frontend Implementation

### 1. UI Components for Landlord Dashboard

#### Vacant Apartment Indicator:
```html
<!-- In apartment listing -->
@if($apartment->isVacant())
    <div class="apartment-card vacant">
        <div class="apartment-header">
            <span class="badge badge-success">Vacant</span>
            <div class="apartment-actions">
                <button class="btn btn-sm btn-primary" onclick="generateEasyRentLink({{ $apartment->id }})">
                    <i class="fas fa-link"></i> EasyRent Link
                </button>
            </div>
        </div>
        <!-- Apartment details -->
    </div>
@else
    <div class="apartment-card occupied">
        <span class="badge badge-secondary">Occupied</span>
        <!-- Apartment details -->
    </div>
@endif
```

#### Link Generation Modal:
```html
<!-- EasyRent Link Modal -->
<div class="modal fade" id="easyRentLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-link text-primary"></i> EasyRent Link Generated
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Shareable Link:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="easyRentUrl" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="share-options mt-3">
                    <h6>Share via:</h6>
                    <div class="btn-group">
                        <button class="btn btn-success" onclick="shareWhatsApp()">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </button>
                        <button class="btn btn-primary" onclick="shareEmail()">
                            <i class="fas fa-envelope"></i> Email
                        </button>
                        <button class="btn btn-info" onclick="shareSMS()">
                            <i class="fas fa-sms"></i> SMS
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 2. Public Apartment View Page

#### Apartment Details Page:
```html
<!-- resources/views/apartment/invite/show.blade.php -->
@extends('layout')

@section('title', 'Apartment Details - ' . $property->prop_name)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Property Images -->
            <div class="property-gallery mb-4">
                <!-- Image carousel -->
            </div>
            
            <!-- Property Details -->
            <div class="card">
                <div class="card-header">
                    <h3>{{ $property->prop_name }}</h3>
                    <p class="text-muted">{{ $property->prop_address }}</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Apartment Details</h5>
                            <ul class="list-unstyled">
                                <li><strong>Type:</strong> {{ $apartment->apartment_type }}</li>
                                <li><strong>Monthly Rent:</strong> ₦{{ number_format($apartment->amount) }}</li>
                                <li><strong>Property Type:</strong> {{ $property->prop_type }}</li>
                                <li><strong>Location:</strong> {{ $property->prop_address }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Landlord Information</h5>
                            <ul class="list-unstyled">
                                <li><strong>Name:</strong> {{ $landlord->first_name }} {{ $landlord->last_name }}</li>
                                <li><strong>Email:</strong> {{ $landlord->email }}</li>
                                <li><strong>Phone:</strong> {{ $landlord->phone }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Application Form -->
            <div class="card sticky-top">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-home"></i> Apply for this Apartment
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('apartment.invite.apply', $invitation->invitation_token) }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="tenant_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="tenant_email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="tenant_phone" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Lease Duration (months) *</label>
                            <select name="duration" class="form-control" required>
                                <option value="6">6 months</option>
                                <option value="12" selected>12 months</option>
                                <option value="24">24 months</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Preferred Move-in Date *</label>
                            <input type="date" name="move_in_date" class="form-control" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        </div>
                        
                        <div class="total-calculation mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Monthly Rent:</span>
                                <span>₦{{ number_format($apartment->amount) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Duration:</span>
                                <span id="duration-display">12 months</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>Total Amount:</span>
                                <span id="total-amount">₦{{ number_format($apartment->amount * 12) }}</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-credit-card"></i> Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate total amount dynamically
$('select[name="duration"]').on('change', function() {
    const duration = parseInt($(this).val());
    const monthlyRent = {{ $apartment->amount }};
    const total = monthlyRent * duration;
    
    $('#duration-display').text(duration + ' months');
    $('#total-amount').text('₦' + total.toLocaleString());
});
</script>
@endsection
```

## 📧 Email Notifications

### 1. Tenant Application Email (to Landlord):
```html
<!-- resources/views/emails/tenant-application-landlord.blade.php -->
<h2>New Tenant Application Received</h2>

<p>Dear {{ $landlord->first_name }},</p>

<p>You have received a new application for your apartment:</p>

<div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>Property Details:</h3>
    <ul>
        <li><strong>Property:</strong> {{ $property->prop_name }}</li>
        <li><strong>Apartment Type:</strong> {{ $apartment->apartment_type }}</li>
        <li><strong>Monthly Rent:</strong> ₦{{ number_format($apartment->amount) }}</li>
    </ul>
    
    <h3>Applicant Details:</h3>
    <ul>
        <li><strong>Name:</strong> {{ $paymentInvitation->tenant_name }}</li>
        <li><strong>Email:</strong> {{ $paymentInvitation->tenant_email }}</li>
        <li><strong>Phone:</strong> {{ $paymentInvitation->tenant_phone }}</li>
        <li><strong>Lease Duration:</strong> {{ $paymentInvitation->duration }} months</li>
        <li><strong>Move-in Date:</strong> {{ $paymentInvitation->move_in_date->format('M d, Y') }}</li>
        <li><strong>Total Amount:</strong> ₦{{ number_format($paymentInvitation->amount) }}</li>
    </ul>
</div>

<p>The tenant will proceed with payment. You will be notified once payment is completed.</p>

<p>Best regards,<br>EasyRent Team</p>
```

### 2. Application Confirmation Email (to Tenant):
```html
<!-- resources/views/emails/tenant-application-tenant.blade.php -->
<h2>Application Submitted Successfully</h2>

<p>Dear {{ $paymentInvitation->tenant_name }},</p>

<p>Your application for the apartment has been submitted successfully!</p>

<div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>Application Summary:</h3>
    <ul>
        <li><strong>Property:</strong> {{ $property->prop_name }}</li>
        <li><strong>Apartment Type:</strong> {{ $apartment->apartment_type }}</li>
        <li><strong>Monthly Rent:</strong> ₦{{ number_format($apartment->amount) }}</li>
        <li><strong>Lease Duration:</strong> {{ $paymentInvitation->duration }} months</li>
        <li><strong>Total Amount:</strong> ₦{{ number_format($paymentInvitation->amount) }}</li>
        <li><strong>Move-in Date:</strong> {{ $paymentInvitation->move_in_date->format('M d, Y') }}</li>
    </ul>
</div>

<p>Please proceed with payment to secure your apartment.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ route('apartment.invite.payment', ['token' => $invitation->invitation_token, 'payment_id' => $paymentInvitation->id]) }}" 
       style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px;">
        Complete Payment
    </a>
</div>

<p>Best regards,<br>EasyRent Team</p>
```

## 🔗 Routes Configuration

```php
// Add to routes/web.php

// EasyRent Link Routes (Public)
Route::prefix('apartment/invite')->name('apartment.invite.')->group(function () {
    Route::get('/{token}', [ApartmentInvitationController::class, 'show'])->name('show');
    Route::post('/{token}/apply', [ApartmentInvitationController::class, 'apply'])->name('apply');
    Route::get('/{token}/payment/{paymentInvitation}', [ApartmentInvitationController::class, 'payment'])->name('payment');
    Route::get('/{token}/expired', function($token) {
        return view('apartment.invite.expired', compact('token'));
    })->name('expired');
});

// Landlord EasyRent Link Management (Protected)
Route::middleware(['auth'])->group(function () {
    Route::post('/apartment/{apartment}/generate-link', [ApartmentInvitationController::class, 'generateLink'])
        ->name('apartment.generate-link');
    Route::get('/apartment/{apartment}/invitations', [ApartmentInvitationController::class, 'invitations'])
        ->name('apartment.invitations');
});
```

## 📱 JavaScript Functions

```javascript
// EasyRent Link Management
function generateEasyRentLink(apartmentId) {
    $.ajax({
        url: `/apartment/${apartmentId}/generate-link`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#easyRentUrl').val(response.link);
                window.currentWhatsAppUrl = response.whatsapp_url;
                $('#easyRentLinkModal').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to generate EasyRent link');
        }
    });
}

function copyToClipboard() {
    const urlField = document.getElementById('easyRentUrl');
    urlField.select();
    document.execCommand('copy');
    
    // Show success message
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
    }, 2000);
}

function shareWhatsApp() {
    if (window.currentWhatsAppUrl) {
        window.open(window.currentWhatsAppUrl, '_blank');
    }
}

function shareEmail() {
    const link = document.getElementById('easyRentUrl').value;
    const subject = encodeURIComponent('Check out this apartment');
    const body = encodeURIComponent(`I found this apartment that might interest you: ${link}`);
    window.open(`mailto:?subject=${subject}&body=${body}`);
}

function shareSMS() {
    const link = document.getElementById('easyRentUrl').value;
    const message = encodeURIComponent(`Check out this apartment: ${link}`);
    window.open(`sms:?body=${message}`);
}
```

## 🚀 Implementation Phases

### Phase 1: Core Infrastructure (Week 1)
1. ✅ Create database migration for `apartment_invitations`
2. ✅ Create `ApartmentInvitation` model
3. ✅ Update `Apartment` model with new methods
4. ✅ Create `ApartmentInvitationController`

### Phase 2: UI Integration (Week 2)
1. ✅ Add vacant apartment indicators to landlord dashboard
2. ✅ Implement EasyRent link generation modal
3. ✅ Create public apartment viewing page
4. ✅ Add sharing functionality (copy, WhatsApp, email, SMS)

### Phase 3: Payment Integration (Week 3)
1. ✅ Integrate with existing payment system
2. ✅ Create tenant application form
3. ✅ Implement payment processing workflow
4. ✅ Add apartment assignment after payment

### Phase 4: Email Notifications (Week 4)
1. ✅ Create email templates for landlord and tenant
2. ✅ Implement notification system
3. ✅ Add email tracking and delivery confirmation
4. ✅ Testing and bug fixes

## 🔒 Security Considerations

1. **Token Security**: Use cryptographically secure random tokens
2. **Expiration**: Links expire after 30 days by default
3. **Rate Limiting**: Prevent spam link generation
4. **Validation**: Verify apartment ownership before link generation
5. **Privacy**: Don't expose sensitive landlord information publicly

## 📊 Analytics & Tracking

1. **Link Generation**: Track when links are created
2. **Link Views**: Monitor how many times links are viewed
3. **Applications**: Count tenant applications per link
4. **Conversion Rate**: Track link-to-payment conversion
5. **Popular Properties**: Identify most-shared apartments

## 🎯 Success Metrics

1. **Reduced Vacancy Time**: Faster tenant acquisition
2. **Increased Applications**: More tenant inquiries per property
3. **Improved UX**: Streamlined tenant onboarding
4. **Higher Conversion**: Better link-to-lease conversion rates
5. **Landlord Satisfaction**: Easier property marketing

This comprehensive plan provides a complete roadmap for implementing the EasyRent Link feature, improving the user experience for both landlords and prospective tenants while maintaining security and scalability.