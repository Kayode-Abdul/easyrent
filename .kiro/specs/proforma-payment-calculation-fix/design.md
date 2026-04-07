# Proforma Payment Calculation Fix - Design Document

## Overview

This design addresses the critical payment calculation bug in the EasyRent system where apartment prices are incorrectly multiplied by rental duration, leading to inflated payment totals in both proforma generation and EasyRent invitation previews. The solution involves creating a centralized payment calculation service that properly handles different pricing models and ensures consistency across all payment-related features.

## Architecture

The fix implements a service-oriented architecture with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                       │
├─────────────────────────────────────────────────────────────┤
│  ProformaController  │  ApartmentInvitationController      │
│  PaymentController   │  EasyRentLinkController             │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Service Layer                            │
├─────────────────────────────────────────────────────────────┤
│           PaymentCalculationService (New)                   │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  • calculatePaymentTotal()                              ││
│  │  • validatePricingConfiguration()                       ││
│  │  │  • determineCalculationMethod()                      ││
│  │  • logCalculationSteps()                               ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Data Layer                               │
├─────────────────────────────────────────────────────────────┤
│  Apartment Model     │  ProformaReceipt Model              │
│  ApartmentInvitation │  Payment Model                      │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### PaymentCalculationService

**Primary Interface:**
```php
interface PaymentCalculationServiceInterface
{
    public function calculatePaymentTotal(
        float $apartmentPrice, 
        int $rentalDuration, 
        string $pricingType = 'total'
    ): PaymentCalculationResult;
    
    public function validatePricingConfiguration(array $config): bool;
    public function logCalculationSteps(PaymentCalculationResult $result): void;
}
```

**PaymentCalculationResult:**
```php
class PaymentCalculationResult
{
    public float $totalAmount;
    public string $calculationMethod;
    public array $calculationSteps;
    public bool $isValid;
    public ?string $errorMessage;
}
```

### Updated Controllers

**ProformaController Integration:**
- Replace direct price calculations with PaymentCalculationService calls
- Ensure consistent calculation logic across all proforma generation methods
- Add calculation logging for audit purposes

**ApartmentInvitationController Integration:**
- Update payment preview calculations to use centralized service
- Ensure EasyRent invitation totals match proforma calculations exactly
- Add validation for pricing configuration before displaying amounts

## Data Models

### Apartment Model Enhancement

```php
// Add pricing configuration fields
class Apartment extends Model
{
    protected $fillable = [
        // existing fields...
        'pricing_type', // 'total' or 'monthly'
        'price_configuration', // JSON field for complex pricing rules
    ];
    
    protected $casts = [
        'price_configuration' => 'array',
    ];
    
    public function getPricingType(): string
    {
        return $this->pricing_type ?? 'total';
    }
    
    public function getCalculatedPaymentTotal(int $duration): float
    {
        return app(PaymentCalculationServiceInterface::class)
            ->calculatePaymentTotal($this->amount, $duration, $this->getPricingType())
            ->totalAmount;
    }
}
```

### ProformaReceipt Model Updates

```php
class ProformaReceipt extends Model
{
    protected $fillable = [
        // existing fields...
        'calculation_method',
        'calculation_log', // JSON field storing calculation steps
    ];
    
    protected $casts = [
        'calculation_log' => 'array',
    ];
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Property 1: Total pricing calculation consistency
*For any* apartment with pricing_type 'total', the calculated payment total should equal the apartment price regardless of rental duration
**Validates: Requirements 1.1, 1.3**

Property 2: Monthly pricing calculation accuracy
*For any* apartment with pricing_type 'monthly', the calculated payment total should equal the apartment price multiplied by the rental duration
**Validates: Requirements 1.4**

Property 3: Calculation method consistency
*For any* apartment and rental duration, multiple calls to the calculation service with identical parameters should always produce the same result
**Validates: Requirements 1.2, 1.5**

Property 4: EasyRent invitation calculation consistency
*For any* apartment invitation, the payment preview total should match the result from the centralized PaymentCalculationService
**Validates: Requirements 2.1, 2.2, 2.3**

Property 5: Payment preview accuracy
*For any* payment scenario, the previewed amount should exactly match the final payment total when processed
**Validates: Requirements 2.5**

Property 6: Pricing configuration validation
*For any* apartment pricing configuration, the system should properly validate and store the pricing_type field
**Validates: Requirements 3.1**

Property 7: Calculation audit logging
*For any* payment calculation performed, the system should create detailed logs with calculation steps and method used
**Validates: Requirements 3.2, 3.5**

Property 8: Configuration change isolation
*For any* existing proforma or payment record, changes to pricing configuration should not affect the stored calculation results
**Validates: Requirements 3.3, 4.2**

Property 9: Error handling completeness
*For any* invalid calculation input, the system should return appropriate error messages indicating the specific issue
**Validates: Requirements 3.4**

Property 10: Service centralization
*For any* payment calculation need across the system, all components should use the centralized PaymentCalculationService
**Validates: Requirements 4.1**

Property 11: Input validation consistency
*For any* calculation method call, the system should validate input parameters and return consistent results for valid inputs
**Validates: Requirements 4.4**

<function_calls>
<invoke name="prework">
<parameter name="featureName">proforma-payment-calculation-fix

## Error Handling

### Validation Errors
- **Invalid Pricing Type**: Return clear error when pricing_type is not 'total' or 'monthly'
- **Negative Values**: Reject negative apartment prices or rental durations
- **Missing Configuration**: Handle cases where pricing configuration is incomplete

### Calculation Errors
- **Overflow Protection**: Prevent arithmetic overflow for large price calculations
- **Precision Handling**: Maintain proper decimal precision for currency calculations
- **Division by Zero**: Handle edge cases in complex pricing calculations

### Recovery Mechanisms
- **Fallback Logic**: Default to 'total' pricing when configuration is ambiguous
- **Graceful Degradation**: Continue operation with basic calculations if advanced features fail
- **Error Logging**: Comprehensive logging for debugging calculation issues

## Testing Strategy

### Unit Testing
- Test PaymentCalculationService with various pricing configurations
- Verify error handling for invalid inputs
- Test calculation accuracy with edge cases (zero amounts, maximum values)
- Validate logging functionality

### Property-Based Testing
The system will use **PHPUnit with Eris** for property-based testing, configured to run a minimum of 100 iterations per property test.

Each property-based test will be tagged with comments explicitly referencing the correctness property from this design document using the format: **Feature: proforma-payment-calculation-fix, Property {number}: {property_text}**

Property tests will cover:
- Calculation consistency across different input ranges
- Pricing type behavior verification
- Service integration consistency
- Error handling completeness

### Integration Testing
- Test complete proforma generation flow with corrected calculations
- Verify EasyRent invitation preview accuracy
- Test cross-system consistency between proforma and invitation calculations
- Validate audit logging across all calculation scenarios

### Performance Testing
- Benchmark calculation service performance with large datasets
- Test concurrent calculation requests
- Verify memory usage with complex pricing configurations

## Implementation Considerations

### Database Migration
- Add `pricing_type` column to apartments table
- Add `price_configuration` JSON column for complex pricing rules
- Add `calculation_method` and `calculation_log` to proforma_receipts table
- Create indexes for performance optimization

### Backward Compatibility
- Default existing apartments to 'total' pricing type
- Preserve existing proforma calculations in their current state
- Provide migration script to analyze and correct historical data if needed

### Configuration Management
- Environment-based pricing rule configuration
- Admin interface for managing pricing types
- Validation rules for pricing configuration changes

### Monitoring and Observability
- Metrics for calculation performance
- Alerts for calculation errors or inconsistencies
- Dashboard for monitoring pricing configuration usage

## Security Considerations

### Input Validation
- Sanitize all numeric inputs to prevent injection attacks
- Validate pricing configuration JSON structure
- Implement rate limiting for calculation API endpoints

### Audit Trail
- Log all calculation requests with user context
- Maintain immutable calculation history
- Secure storage of sensitive pricing information

### Access Control
- Restrict pricing configuration changes to authorized users
- Implement role-based access for calculation audit logs
- Secure API endpoints with proper authentication