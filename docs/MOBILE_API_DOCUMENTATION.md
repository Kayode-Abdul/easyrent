# EasyRent Mobile API Documentation

## Overview

The EasyRent Mobile API provides comprehensive endpoints for mobile applications to integrate with the EasyRent Link Authentication System. This API supports apartment invitations, user authentication, payment processing, and session management specifically designed for mobile apps.

## Base URL

```
https://your-domain.com/api/v1/mobile
```

## Authentication

The API supports two authentication methods:

### 1. Bearer Token Authentication (Recommended for mobile apps)
```
Authorization: Bearer {token}
```

### 2. API Key Authentication (For admin operations)
```
X-API-Key: {api_key}
```

## Response Format

All API responses follow this standard format:

```json
{
  "success": true|false,
  "message": "Response message",
  "data": {}, // Response data (when applicable)
  "errors": {}, // Validation errors (when applicable)
  "error_code": "ERROR_CODE" // Error code for programmatic handling
}
```

## Endpoints

### Authentication Endpoints

#### POST /auth/login
Authenticate user and get access token.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "invitation_token": "optional_invitation_token"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1234567,
      "first_name": "John",
      "last_name": "Doe",
      "email": "user@example.com",
      "phone": "+1234567890",
      "roles": ["tenant"]
    },
    "token": "1|abc123...",
    "invitation_context": {
      "invitation_token": "token123",
      "apartment_id": 456
    }
  }
}
```

#### POST /auth/register
Register new user account.

**Request:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "user@example.com",
  "phone": "+1234567890",
  "password": "password123",
  "password_confirmation": "password123",
  "invitation_token": "optional_invitation_token"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1234567,
      "first_name": "John",
      "last_name": "Doe",
      "email": "user@example.com",
      "phone": "+1234567890",
      "roles": ["tenant"]
    },
    "token": "1|abc123...",
    "invitation_context": null
  }
}
```

#### POST /auth/logout
**Requires:** Bearer Token

Logout and revoke current access token.

**Response:**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

#### GET /auth/profile
**Requires:** Bearer Token

Get current user profile information.

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1234567,
      "first_name": "John",
      "last_name": "Doe",
      "email": "user@example.com",
      "phone": "+1234567890",
      "roles": ["tenant"],
      "registration_source": "mobile_app",
      "created_at": "2024-01-01T00:00:00Z",
      "email_verified_at": "2024-01-01T00:00:00Z"
    }
  }
}
```

#### POST /auth/refresh-token
**Requires:** Bearer Token

Refresh the current access token.

**Response:**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "token": "2|def456..."
  }
}
```

### Invitation Endpoints

#### GET /invitations/{token}
Get apartment invitation details by token (public endpoint).

**Response:**
```json
{
  "success": true,
  "data": {
    "invitation": {
      "token": "abc123...",
      "expires_at": "2024-12-31T23:59:59Z",
      "access_count": 5,
      "created_at": "2024-01-01T00:00:00Z"
    },
    "apartment": {
      "id": 456,
      "rent": 50000,
      "duration": 12,
      "apartment_type": "2 Bedroom",
      "bedrooms": 2,
      "bathrooms": 2,
      "size": "120 sqm",
      "description": "Beautiful apartment...",
      "photos": ["photo1.jpg", "photo2.jpg"],
      "amenities": "WiFi, AC, Parking",
      "available": true
    },
    "property": {
      "id": 789,
      "address": "123 Main Street",
      "state": "Lagos",
      "lga": "Ikeja",
      "prop_type": 1
    },
    "landlord": {
      "name": "Jane Smith",
      "email": "landlord@example.com",
      "phone": "+1234567890"
    }
  }
}
```

#### POST /invitations/{token}/apply
**Requires:** Bearer Token

Apply for apartment via invitation.

**Request:**
```json
{
  "duration": 12,
  "move_in_date": "2024-02-01",
  "additional_notes": "Looking forward to moving in"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Application submitted successfully",
  "data": {
    "payment": {
      "id": 123,
      "reference": "mobile_abc123",
      "amount": 600000,
      "duration": 12,
      "status": "pending"
    },
    "next_step": "payment",
    "payment_url": "https://domain.com/apartment/invite/token123/payment/123"
  }
}
```

#### POST /invitations/generate
**Requires:** Bearer Token

Generate new invitation link (for landlords).

**Request:**
```json
{
  "apartment_id": 456,
  "expires_in_hours": 72
}
```

**Response:**
```json
{
  "success": true,
  "message": "Invitation link generated successfully",
  "data": {
    "invitation": {
      "id": 789,
      "token": "def456...",
      "url": "https://domain.com/apartment/invite/def456",
      "expires_at": "2024-12-31T23:59:59Z",
      "created_at": "2024-01-01T00:00:00Z"
    },
    "apartment": {
      "id": 456,
      "rent": 50000,
      "apartment_type": "2 Bedroom"
    }
  }
}
```

### Payment Endpoints

#### GET /payments/{paymentId}
**Requires:** Bearer Token

Get payment details.

**Response:**
```json
{
  "success": true,
  "data": {
    "payment": {
      "id": 123,
      "reference": "mobile_abc123",
      "transaction_id": "inv_token123_1234567890",
      "amount": 600000,
      "status": "pending",
      "payment_method": "mobile_app",
      "duration": 12,
      "move_in_date": "2024-02-01",
      "additional_notes": "Looking forward to moving in",
      "paid_at": null,
      "created_at": "2024-01-01T00:00:00Z"
    },
    "apartment": {
      "id": 456,
      "rent": 50000,
      "apartment_type": "2 Bedroom",
      "address": "123 Main Street"
    },
    "tenant": {
      "name": "John Doe",
      "email": "user@example.com"
    },
    "landlord": {
      "name": "Jane Smith",
      "email": "landlord@example.com"
    }
  }
}
```

#### POST /payments/initialize
**Requires:** Bearer Token

Initialize payment with gateway.

**Request:**
```json
{
  "payment_id": 123,
  "payment_method": "paystack",
  "callback_url": "https://mobile-app.com/payment-callback"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment initialized successfully",
  "data": {
    "payment_id": 123,
    "gateway_url": "https://checkout.paystack.com/abc123",
    "reference": "mobile_abc123",
    "amount": 600000,
    "payment_method": "paystack"
  }
}
```

#### POST /payments/callback
Handle payment callback from gateway (public endpoint).

**Request:**
```json
{
  "reference": "mobile_abc123",
  "status": "success"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment processed successfully",
  "data": {
    "payment": {
      "id": 123,
      "reference": "mobile_abc123",
      "amount": 600000,
      "status": "completed",
      "paid_at": "2024-01-01T12:00:00Z"
    },
    "apartment_assigned": true,
    "emails_sent": true
  }
}
```

#### GET /payments/user/history
**Requires:** Bearer Token

Get user's payment history.

**Query Parameters:**
- `per_page` (optional): Number of items per page (max 100)
- `status` (optional): Filter by payment status

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "reference": "mobile_abc123",
      "amount": 600000,
      "status": "completed",
      "created_at": "2024-01-01T00:00:00Z",
      "apartment": {
        "id": 456,
        "address": "123 Main Street"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 25,
    "last_page": 2,
    "has_more": true
  }
}
```

#### POST /payments/{paymentId}/cancel
**Requires:** Bearer Token

Cancel pending payment.

**Response:**
```json
{
  "success": true,
  "message": "Payment cancelled successfully",
  "data": {
    "payment_id": 123,
    "status": "cancelled"
  }
}
```

### Session Management Endpoints

#### POST /sessions
**Requires:** Bearer Token

Store session data.

**Request:**
```json
{
  "session_key": "invitation_token_123",
  "session_data": {
    "invitation_token": "abc123",
    "application_data": {
      "duration": 12,
      "move_in_date": "2024-02-01"
    }
  },
  "expires_in_minutes": 1440
}
```

**Response:**
```json
{
  "success": true,
  "message": "Session data stored successfully",
  "data": {
    "session_key": "invitation_token_123",
    "expires_at": "2024-01-02T00:00:00Z"
  }
}
```

#### GET /sessions/{sessionKey}
**Requires:** Bearer Token

Retrieve session data.

**Response:**
```json
{
  "success": true,
  "data": {
    "session_key": "invitation_token_123",
    "session_data": {
      "invitation_token": "abc123",
      "application_data": {
        "duration": 12,
        "move_in_date": "2024-02-01"
      },
      "_metadata": {
        "created_at": "2024-01-01T00:00:00Z",
        "user_id": 1234567,
        "expires_at": "2024-01-02T00:00:00Z"
      }
    },
    "metadata": {
      "created_at": "2024-01-01T00:00:00Z",
      "user_id": 1234567,
      "expires_at": "2024-01-02T00:00:00Z"
    }
  }
}
```

#### PUT /sessions/{sessionKey}
**Requires:** Bearer Token

Update session data.

**Request:**
```json
{
  "session_data": {
    "new_field": "new_value"
  },
  "merge": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Session data updated successfully",
  "data": {
    "session_key": "invitation_token_123",
    "updated_at": "2024-01-01T12:00:00Z"
  }
}
```

#### DELETE /sessions/{sessionKey}
**Requires:** Bearer Token

Delete session data.

**Response:**
```json
{
  "success": true,
  "message": "Session data deleted successfully"
}
```

## Error Codes

| Code | Description |
|------|-------------|
| `INVALID_API_KEY` | API key is missing or invalid |
| `INVALID_CREDENTIALS` | Login credentials are incorrect |
| `REGISTRATION_FAILED` | User registration failed |
| `INVITATION_NOT_FOUND` | Invitation token not found or expired |
| `APARTMENT_UNAVAILABLE` | Apartment is no longer available |
| `AUTHENTICATION_REQUIRED` | User must be authenticated |
| `UNAUTHORIZED` | User lacks permission for this action |
| `PAYMENT_NOT_FOUND` | Payment record not found |
| `PAYMENT_INIT_FAILED` | Payment initialization failed |
| `VERIFICATION_FAILED` | Payment verification failed |
| `PAYMENT_FAILED` | Payment was not successful |
| `SESSION_NOT_FOUND` | Session data not found |
| `SESSION_EXPIRED` | Session has expired |

## Rate Limiting

API endpoints are rate limited to prevent abuse:
- Authentication endpoints: 5 requests per minute
- Other endpoints: 60 requests per minute
- Admin endpoints: 100 requests per minute

## Mobile App Integration Examples

### Complete Invitation Flow

1. **User clicks invitation link in mobile app**
```javascript
const response = await fetch('/api/v1/mobile/invitations/abc123');
const invitation = await response.json();
```

2. **User applies for apartment (requires authentication)**
```javascript
// If not authenticated, redirect to login
const applyResponse = await fetch('/api/v1/mobile/invitations/abc123/apply', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    duration: 12,
    move_in_date: '2024-02-01',
    additional_notes: 'Looking forward to moving in'
  })
});
```

3. **Initialize payment**
```javascript
const paymentResponse = await fetch('/api/v1/mobile/payments/initialize', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    payment_id: 123,
    payment_method: 'paystack',
    callback_url: 'myapp://payment-callback'
  })
});
```

4. **Handle payment callback**
```javascript
// In your mobile app's deep link handler
const callbackResponse = await fetch('/api/v1/mobile/payments/callback', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    reference: 'mobile_abc123'
  })
});
```

## Security Considerations

1. **Always use HTTPS** in production
2. **Store API keys securely** in your mobile app
3. **Implement token refresh** to maintain user sessions
4. **Validate all user inputs** before sending to API
5. **Handle errors gracefully** and provide user-friendly messages
6. **Implement proper session management** for invitation flows

## Support

For API support and questions, contact the development team or refer to the main EasyRent documentation.