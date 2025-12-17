# Mobile API Documentation

This document provides comprehensive information about the EasyRent Mobile API endpoints, including authentication, apartment invitations, payments, and session management with enhanced pricing structure transparency and centralized calculation service integration.

## Base URL
```
https://your-domain.com/api/v1/mobile
```

## Authentication

The mobile API uses Laravel Sanctum for authentication. Most endpoints require a Bearer token in the Authorization header.

### Headers
```
Authorization: Bearer {your-token}
Content-Type: application/json
Accept: application/json
```

## Pricing Structure Overview

The EasyRent system supports two pricing models with full transparency:

### Pricing Types
1. **Total Pricing (`total`)**: The apartment price represents the complete rental amount for the entire period (no multiplication by duration)
2. **Monthly Pricing (`monthly`)**: The apartment price represents the monthly rent that will be multiplied by the rental duration

### Centralized Calculation Service
All payment calculations use the centralized `PaymentCalculationService` which ensures:
- Consistent calculation logic across all endpoints
- Comprehensive input validation and security
- Detailed audit logging for transparency
- Proper error handling with fallback mechanisms
- Support for additional charges and complex pricing configurations

## Endpoints Overview

### Authentication Endpoints
- `POST /auth/login` - User login
- `POST /auth/register` - User registration  
- `POST /auth/logout` - User logout
- `GET /auth/profile` - Get user profile
- `POST /auth/refresh-token` - Refresh authentication token

### Invitation Endpoints
- `GET /invitations/{token}` - Get invitation details
- `POST /invitations/{token}/apply` - Apply for apartment
- `POST /invitations/{token}/calculate` - **Enhanced** - Get payment calculation for invitation
- `GET /invitations/{token}/session` - Get session data
- `POST /invitations/session/store` - Store session data
- `DELETE /invitations/{token}/session` - Clear session data
- `POST /invitations/generate` - Generate invitation link (landlords)

### Payment Endpoints
- `GET /payments/{paymentId}` - Get payment details
- `POST /payments/initialize` - Initialize payment
- `POST /payments/callback` - Payment callback handler
- `GET /payments/user/history` - Get user payment history
- `POST /payments/{paymentId}/cancel` - Cancel payment
- `POST /payments/calculate/preview` - **Enhanced** - Calculate payment preview with pricing transparency
- `POST /payments/validate/calculation` - **New** - Validate payment calculation before processing

### Session Management
- `POST /sessions` - Create session
- `GET /sessions/{sessionKey}` - Get session
- `PUT /sessions/{sessionKey}` - Update session
- `DELETE /sessions/{sessionKey}` - Delete session

## Enhanced Endpoint Documentation

### Payment Calculation (Enhanced)

#### Calculate Payment Preview
```http
POST /api/v1/mobile/payments/calculate/preview
```

**Request Body:**
```json
{
  "apartment_id": 1,
  "rental_duration": 12,
  "additional_charges": [5000, 10000],
  "include_pricing_details": true,
  "include_calculation_audit": false
}
```

**Enhanced Response with Pricing Structure Transparency:**
```json
{
  "success": true,
  "data": {
    "payment_preview": {
      "total_amount": 1815000,
      "formatted_total": "1,815,000.00",
      "calculation_method": "monthly_price_with_duration_multiplication_with_additional_charges",
      "pricing_breakdown": {
        "base_rent": 150000,
        "rental_duration": 12,
        "pricing_type": "monthly",
        "additional_charges": [5000, 10000],
        "additional_charges_total": 15000,
        "calculation_steps": []
      },
      "calculation_summary": {
        "total_amount": 1815000,
        "calculation_method": "monthly_price_with_duration_multiplication_with_additional_charges",
        "steps_count": 3,
        "is_valid": true,
        "error_message": null
      }
    },
    "apartment": {
      "id": 1,
      "rent": 150000,
      "apartment_type": "2 Bedroom",
      "bedrooms": 2,
      "bathrooms": 2,
      "size": "85 sqm",
      "address": "123 Main Street, Lagos",
      "pricing_type": "monthly",
      "available": true
    },
    "mobile_features": {
      "formatted_display_amounts": {
        "total_amount": "₦1,815,000.00",
        "base_rent": "₦150,000.00",
        "monthly_equivalent": null,
        "additional_charges_total": "₦15,000.00"
      },
      "calculation_explanation": {
        "method_description": "Monthly rent multiplied by rental duration plus additional charges",
        "pricing_explanation": "The apartment price represents the monthly rent that will be multiplied by the rental duration",
        "steps_count": 3,
        "has_additional_charges": true,
        "calculation_transparency": {
          "base_calculation": "₦150000 × 12 months = ₦1,800,000.00",
          "additional_charges_breakdown": [
            {
              "index": 0,
              "amount": 5000,
              "formatted_amount": "₦5,000.00",
              "description": "Additional charge 1"
            },
            {
              "index": 1,
              "amount": 10000,
              "formatted_amount": "₦10,000.00",
              "description": "Additional charge 2"
            }
          ]
        }
      },
      "user_experience": {
        "payment_affordability": {
          "monthly_cost": 150000,
          "affordability_rating": {
            "rating": "moderate",
            "description": "Reasonably priced",
            "monthly_equivalent": 150000,
            "formatted_monthly": "₦150,000.00/month"
          },
          "cost_comparison": {
            "per_month": 151250,
            "per_week": 34913.79,
            "per_day": 5041.67
          }
        },
        "rental_recommendations": {
          "optimal_duration": {
            "recommendation": "Consider 6-12 months for the best balance",
            "options": {
              "short_term": {
                "range": "1-6 months",
                "description": "Good for temporary stays or trial periods"
              },
              "medium_term": {
                "range": "6-12 months",
                "description": "Balanced option for most renters"
              },
              "long_term": {
                "range": "12+ months",
                "description": "Best value for extended stays"
              }
            },
            "reasoning": "Longer rentals often provide better value and stability"
          },
          "cost_savings_tips": [
            {
              "tip": "Review additional charges",
              "description": "Some additional charges might be negotiable",
              "potential_savings": "₦1,500.00 or more"
            },
            {
              "tip": "Ask about annual payment discounts",
              "description": "Many landlords offer discounts for upfront annual payments",
              "potential_savings": "5-10% discount possible"
            },
            {
              "tip": "Compare similar properties",
              "description": "Check other apartments in the same area for better deals",
              "potential_savings": "Market rate comparison"
            }
          ]
        }
      }
    },
    "pricing_structure_details": {
      "supported_pricing_types": {
        "total": "Complete rental amount (no duration multiplication)",
        "monthly": "Monthly rent (multiplied by rental duration)"
      },
      "validation_limits": {
        "max_rental_duration": 120,
        "max_apartment_price": 999999999.99,
        "min_apartment_price": 0.01,
        "max_calculation_result": 9999999999.99
      },
      "current_apartment_configuration": {
        "pricing_type": "monthly",
        "base_price": 150000,
        "price_configuration": null
      },
      "calculation_methodology": {
        "total_pricing": "The apartment price represents the complete rental amount for the entire period (no multiplication by duration)",
        "monthly_pricing": "The apartment price represents the monthly rent that will be multiplied by the rental duration",
        "additional_charges": "Any additional charges are added to the base calculation regardless of pricing type"
      }
    }
  },
  "meta": {
    "calculation_timestamp": "2024-01-01T00:00:00Z",
    "service_version": "1.0.0",
    "mobile_optimized": true,
    "api_version": "v1",
    "calculation_service_used": "PaymentCalculationService",
    "pricing_structure_transparency": true,
    "mobile_error_handling_enabled": true
  }
}
```

#### Validate Payment Calculation (New)
```http
POST /api/v1/mobile/payments/validate/calculation
```

**Request Body:**
```json
{
  "payment_id": 123,
  "expected_amount": 1815000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "validation": {
      "payment_id": 123,
      "expected_amount": 1815000,
      "calculated_amount": 1815000,
      "stored_amount": 1815000,
      "amount_matches_expected": true,
      "stored_matches_expected": true,
      "calculation_matches_stored": true,
      "validation_passed": true,
      "calculation_method": "monthly_price_with_duration_multiplication_with_additional_charges"
    },
    "discrepancies": {
      "expected_vs_calculated": 0,
      "expected_vs_stored": 0,
      "calculated_vs_stored": 0
    }
  },
  "meta": {
    "validation_timestamp": "2024-01-01T00:00:00Z",
    "service_version": "1.0.0"
  }
}
```

### Invitation Payment Calculation (Enhanced)

#### Get Payment Calculation for Invitation
```http
POST /api/v1/mobile/invitations/{token}/calculate
```

**Request Body:**
```json
{
  "rental_duration": 12,
  "additional_charges": [5000, 10000],
  "include_pricing_details": true,
  "include_calculation_audit": false
}
```

**Enhanced Response:**
```json
{
  "success": true,
  "data": {
    "invitation_token": "abc123...",
    "payment_calculation": {
      "total_amount": 1815000,
      "formatted_total": "1,815,000.00",
      "calculation_method": "monthly_price_with_duration_multiplication_with_additional_charges",
      "pricing_breakdown": {
        "base_rent": 150000,
        "rental_duration": 12,
        "pricing_type": "monthly",
        "additional_charges": [5000, 10000],
        "additional_charges_total": 15000,
        "calculation_steps": []
      },
      "calculation_summary": {
        "total_amount": 1815000,
        "calculation_method": "monthly_price_with_duration_multiplication_with_additional_charges",
        "steps_count": 3,
        "is_valid": true,
        "error_message": null
      }
    },
    "apartment": {
      "id": 1,
      "rent": 150000,
      "apartment_type": "2 Bedroom",
      "address": "123 Main Street, Lagos",
      "pricing_type": "monthly",
      "available": true
    },
    "invitation_details": {
      "expires_at": "2024-01-03T00:00:00Z",
      "time_remaining": {
        "expired": false,
        "hours_remaining": 48,
        "minutes_remaining": 30,
        "formatted_time": "2 days remaining",
        "urgency_level": "low"
      },
      "access_count": 3,
      "landlord_info": {
        "name": "Jane Smith",
        "contact_available": true
      }
    },
    "mobile_features": {
      "formatted_display_amounts": {
        "total_amount": "₦1,815,000.00",
        "base_rent": "₦150,000.00",
        "monthly_equivalent": null,
        "additional_charges_total": "₦15,000.00"
      },
      "calculation_explanation": {
        "method_description": "Monthly rent multiplied by rental duration plus additional charges",
        "pricing_explanation": "The apartment price represents the monthly rent that will be multiplied by the rental duration",
        "steps_count": 3,
        "has_additional_charges": true,
        "calculation_transparency": {
          "base_calculation": "₦150000 × 12 months = ₦1,800,000.00",
          "additional_charges_breakdown": [
            {
              "index": 0,
              "amount": 5000,
              "formatted_amount": "₦5,000.00",
              "description": "Additional charge 1"
            },
            {
              "index": 1,
              "amount": 10000,
              "formatted_amount": "₦10,000.00",
              "description": "Additional charge 2"
            }
          ]
        }
      },
      "invitation_guidance": {
        "next_steps": [
          "Review the calculation details",
          "Proceed with the application if satisfied",
          "Contact landlord for any questions"
        ],
        "urgency_indicator": {
          "level": "low",
          "message": "You have plenty of time to review.",
          "color": "green",
          "action_required": "Review details and apply when ready"
        },
        "application_tips": [
          "Have your identification ready",
          "Prepare payment method information",
          "Review all terms before proceeding"
        ]
      }
    },
    "pricing_structure_details": {
      "supported_pricing_types": {
        "total": "Complete rental amount (no duration multiplication)",
        "monthly": "Monthly rent (multiplied by rental duration)"
      },
      "validation_limits": {
        "max_rental_duration": 120,
        "max_apartment_price": 999999999.99,
        "min_apartment_price": 0.01,
        "max_calculation_result": 9999999999.99
      },
      "current_apartment_configuration": {
        "pricing_type": "monthly",
        "base_price": 150000,
        "price_configuration": null
      },
      "calculation_methodology": {
        "total_pricing": "The apartment price represents the complete rental amount for the entire period (no multiplication by duration)",
        "monthly_pricing": "The apartment price represents the monthly rent that will be multiplied by the rental duration",
        "additional_charges": "Any additional charges are added to the base calculation regardless of pricing type"
      }
    }
  },
  "meta": {
    "calculation_timestamp": "2024-01-01T00:00:00Z",
    "service_version": "1.0.0",
    "mobile_optimized": true,
    "api_version": "v1",
    "calculation_service_used": "PaymentCalculationService",
    "invitation_based_calculation": true,
    "pricing_structure_transparency": true
  }
}
```

## Enhanced Error Handling for Mobile Clients

All endpoints now include mobile-optimized error handling with user-friendly messages and suggested actions:

### Validation Error Example
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "rental_duration": ["The rental duration must be between 1 and 120."]
  },
  "error_code": "VALIDATION_FAILED",
  "mobile_error_handling": {
    "user_friendly_message": "Please check your input values and try again",
    "field_errors": {
      "rental_duration": {
        "messages": ["The rental duration must be between 1 and 120."],
        "user_friendly_message": "Rental duration must be between 1 and 120 months"
      }
    },
    "retry_suggestions": [
      "Ensure rental duration is between 1 and 120 months",
      "Verify additional charges are positive numbers",
      "Check that all required fields are provided"
    ]
  }
}
```

### Calculation Error Example
```json
{
  "success": false,
  "message": "Payment calculation failed",
  "error": "Invalid pricing type provided",
  "error_code": "CALCULATION_FAILED",
  "apartment_context": {
    "apartment_id": 1,
    "pricing_type": "invalid",
    "base_price": 150000
  },
  "mobile_error_handling": {
    "user_friendly_message": "Unable to calculate payment for this apartment",
    "technical_details": "Invalid pricing type provided",
    "suggested_actions": [
      "Try a different rental duration",
      "Remove additional charges and try again",
      "Contact support for assistance"
    ]
  }
}
```

### Service Error Example
```json
{
  "success": false,
  "message": "Payment calculation service error",
  "error": "Service temporarily unavailable",
  "error_code": "CALCULATION_SERVICE_ERROR",
  "mobile_error_handling": {
    "user_friendly_message": "Unable to calculate payment at this time",
    "suggested_actions": [
      "Try again in a few moments",
      "Check your internet connection",
      "Contact support if the problem persists"
    ]
  }
}
```

## Pricing Structure Transparency Features

### Calculation Method Descriptions
The API provides human-readable descriptions for all calculation methods:

- `total_price_no_multiplication`: "Total rental amount (no duration multiplication)"
- `monthly_price_with_duration_multiplication`: "Monthly rent multiplied by rental duration"
- `total_price_no_multiplication_with_additional_charges`: "Total rental amount plus additional charges"
- `monthly_price_with_duration_multiplication_with_additional_charges`: "Monthly rent multiplied by duration plus additional charges"

### Pricing Type Explanations
Clear explanations for each pricing model:

- **Total Pricing**: "The apartment price represents the complete rental amount for the entire period"
- **Monthly Pricing**: "The apartment price represents the monthly rent that will be multiplied by the rental duration"

### Calculation Transparency
Detailed breakdown of how calculations are performed:

```json
{
  "calculation_transparency": {
    "base_calculation": "₦150000 × 12 months = ₦1,800,000.00",
    "additional_charges_breakdown": [
      {
        "index": 0,
        "amount": 5000,
        "formatted_amount": "₦5,000.00",
        "description": "Additional charge 1"
      }
    ]
  }
}
```

## Mobile-Specific Features

### Enhanced User Experience
- **Affordability Ratings**: Automatic categorization of rental costs (budget_friendly, moderate, premium, luxury, ultra_luxury)
- **Cost Comparisons**: Per-month, per-week, and per-day cost breakdowns
- **Rental Recommendations**: Optimal duration suggestions based on pricing type
- **Cost Savings Tips**: Personalized suggestions for reducing rental costs

### Invitation Management
- **Time Remaining Indicators**: Real-time countdown with urgency levels
- **Urgency Indicators**: Color-coded alerts based on expiration time
- **Application Guidance**: Step-by-step instructions for completing applications

### Formatted Display Values
All monetary amounts include mobile-friendly formatted versions:
```json
{
  "formatted_display_amounts": {
    "total_amount": "₦1,815,000.00",
    "base_rent": "₦150,000.00",
    "monthly_equivalent": "₦151,250.00/month",
    "additional_charges_total": "₦15,000.00"
  }
}
```

## Rate Limiting

Enhanced rate limiting with mobile-specific considerations:
- Authentication endpoints: 5 requests per minute
- Payment calculation endpoints: 10 requests per minute (with security middleware)
- Invitation endpoints: 15 requests per minute
- General endpoints: 60 requests per minute

## Security Features

### Input Validation
- Comprehensive sanitization of all calculation inputs
- Range validation for rental durations and pricing amounts
- JSON structure validation for complex pricing configurations

### Rate Limiting
- Endpoint-specific rate limits to prevent abuse
- IP-based and user-based rate limiting

### Access Control
- Role-based access for pricing configuration changes
- Secure API endpoints with proper authentication
- Audit logging for all calculation requests

## SDK Integration Examples

### JavaScript/React Native
```javascript
const EasyRentAPI = {
  baseURL: 'https://your-domain.com/api/v1/mobile',
  
  async calculatePaymentPreview(apartmentId, duration, additionalCharges = [], options = {}) {
    const response = await fetch(`${this.baseURL}/payments/calculate/preview`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${this.token}`
      },
      body: JSON.stringify({
        apartment_id: apartmentId,
        rental_duration: duration,
        additional_charges: additionalCharges,
        include_pricing_details: options.includePricingDetails || true,
        include_calculation_audit: options.includeCalculationAudit || false
      })
    });
    
    const result = await response.json();
    
    if (!result.success) {
      throw new Error(result.mobile_error_handling?.user_friendly_message || result.message);
    }
    
    return result.data;
  },

  async calculateInvitationPayment(token, duration, additionalCharges = []) {
    const response = await fetch(`${this.baseURL}/invitations/${token}/calculate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        rental_duration: duration,
        additional_charges: additionalCharges,
        include_pricing_details: true
      })
    });
    
    const result = await response.json();
    
    if (!result.success) {
      throw new Error(result.mobile_error_handling?.user_friendly_message || result.message);
    }
    
    return result.data;
  },

  async validatePaymentCalculation(paymentId, expectedAmount) {
    const response = await fetch(`${this.baseURL}/payments/validate/calculation`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${this.token}`
      },
      body: JSON.stringify({
        payment_id: paymentId,
        expected_amount: expectedAmount
      })
    });
    
    return response.json();
  }
};
```

### Swift/iOS
```swift
class EasyRentAPI {
    private let baseURL = "https://your-domain.com/api/v1/mobile"
    private var token: String?
    
    func calculatePaymentPreview(
        apartmentId: Int,
        duration: Int,
        additionalCharges: [Double] = [],
        includePricingDetails: Bool = true
    ) async throws -> PaymentPreviewResponse {
        
        let url = URL(string: "\(baseURL)/payments/calculate/preview")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        if let token = token {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }
        
        let body = [
            "apartment_id": apartmentId,
            "rental_duration": duration,
            "additional_charges": additionalCharges,
            "include_pricing_details": includePricingDetails
        ] as [String: Any]
        
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        let (data, _) = try await URLSession.shared.data(for: request)
        let response = try JSONDecoder().decode(APIResponse<PaymentPreviewData>.self, from: data)
        
        if !response.success {
            throw APIError.calculationFailed(response.mobileErrorHandling?.userFriendlyMessage ?? response.message)
        }
        
        return response.data
    }
}
```

## Testing

### Sample cURL Commands

**Calculate Payment Preview:**
```bash
curl -X POST https://your-domain.com/api/v1/mobile/payments/calculate/preview \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "apartment_id": 1,
    "rental_duration": 12,
    "additional_charges": [5000, 10000],
    "include_pricing_details": true,
    "include_calculation_audit": false
  }'
```

**Calculate Invitation Payment:**
```bash
curl -X POST https://your-domain.com/api/v1/mobile/invitations/abc123/calculate \
  -H "Content-Type: application/json" \
  -d '{
    "rental_duration": 12,
    "additional_charges": [5000, 10000],
    "include_pricing_details": true
  }'
```

**Validate Payment Calculation:**
```bash
curl -X POST https://your-domain.com/api/v1/mobile/payments/validate/calculation \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "payment_id": 123,
    "expected_amount": 1815000
  }'
```

## Changelog

### Version 1.0.0 (Current)
- Enhanced payment calculation endpoints with pricing structure transparency
- Added comprehensive mobile error handling with user-friendly messages
- Implemented centralized PaymentCalculationService integration
- Added detailed calculation audit trails and transparency features
- Enhanced invitation payment calculations with time-based urgency indicators
- Added payment calculation validation endpoint
- Improved mobile user experience with affordability ratings and cost comparisons
- Added comprehensive pricing structure documentation and explanations

## Support

For API support and questions:
- Email: api-support@easyrent.com
- Documentation: https://docs.easyrent.com/mobile-api
- Status Page: https://status.easyrent.com
- Pricing Structure Guide: https://docs.easyrent.com/pricing-structure
- Calculation Service Documentation: https://docs.easyrent.com/calculation-service