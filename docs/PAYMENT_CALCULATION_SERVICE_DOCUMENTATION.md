# Payment Calculation Service - Technical Documentation

## Overview

The Payment Calculation Service is a centralized component that handles all apartment payment calculations across the EasyRent system. It was implemented to fix critical calculation bugs where apartment prices were incorrectly multiplied by rental duration, leading to inflated payment totals in both proforma generation and EasyRent invitation previews.

## Architecture

### Service Components

```
┌─────────────────────────────────────────────────────────────┐
│                Payment Calculation Service                   │
├─────────────────────────────────────────────────────────────┤
│  PaymentCalculationService (Core)                           │
│  ├── PaymentCalculationServiceInterface                     │
│  ├── PaymentCalculationResult                               │
│  └── Supporting Services:                                   │
│      ├── PaymentCalculationSecurityService                 │
│      ├── PaymentCalculationMonitoringService               │
│      ├── PaymentCalculationAuditLogger                     │
│      └── PaymentCalculationCacheService                    │
└─────────────────────────────────────────────────────────────┘
```

### Integration Points

The service integrates with:
- **ProformaController**: For proforma payment calculations
- **ApartmentInvitationController**: For EasyRent invitation previews
- **PaymentController**: For payment processing validation
- **Apartment Model**: For pricing configuration retrieval
- **ProformaReceipt Model**: For calculation audit logging

## Core Interface

### PaymentCalculationServiceInterface

```php
interface PaymentCalculationServiceInterface
{
    public function calculatePaymentTotalSecure(array $inputs): PaymentCalculationResult;
    public function calculatePaymentTotal(float $apartmentPrice, int $rentalDuration, string $pricingType = 'total'): PaymentCalculationResult;
    public function validatePricingConfiguration(array $config): bool;
    public function logCalculationSteps(PaymentCalculationResult $result): void;
}
```

### PaymentCalculationResult

```php
class PaymentCalculationResult
{
    public float $totalAmount;
    public string $calculationMethod;
    public array $calculationSteps;
    public bool $isValid;
    public ?string $errorMessage;
    
    public static function success(float $amount, string $method, array $steps): self;
    public static function failure(string $error): self;
}
```

## Pricing Types

### Total Pricing (`pricing_type = 'total'`)
- Apartment price represents the complete rental amount
- No multiplication by rental duration
- Used for: Fixed-term rentals, all-inclusive packages

**Example:**
```php
$service->calculatePaymentTotal(500000, 12, 'total');
// Result: ₦500,000 (regardless of duration)
```

### Monthly Pricing (`pricing_type = 'monthly'`)
- Apartment price represents monthly rental amount
- Multiplied by rental duration
- Used for: Traditional monthly rentals

**Example:**
```php
$service->calculatePaymentTotal(50000, 12, 'monthly');
// Result: ₦600,000 (50,000 × 12 months)
```

## Configuration

### Environment Variables

```env
# Payment Calculation Service Configuration
PAYMENT_CALC_DEFAULT_PRICING_TYPE=total
PAYMENT_CALC_MAX_RENTAL_DURATION=120
PAYMENT_CALC_MAX_APARTMENT_PRICE=999999999.99
PAYMENT_CALC_MIN_APARTMENT_PRICE=0.01
PAYMENT_CALC_PRECISION=2

# Monitoring and Alerts
PAYMENT_CALC_HIGH_VALUE_THRESHOLD=1000000.00
PAYMENT_CALC_SUSPICIOUS_DURATION=60
PAYMENT_CALC_ENABLE_LOGGING=true

# Performance Optimization
PAYMENT_CALC_ENABLE_CACHING=true
PAYMENT_CALC_CACHE_TTL=60
PAYMENT_CALC_ENABLE_BULK_CACHING=true

# Security
PAYMENT_CALC_ENABLE_FALLBACK=true
PAYMENT_CALC_ENABLE_OVERFLOW_PROTECTION=true
```

### Configuration File

The service is configured via `config/payment_calculation.php`:

```php
return [
    'default_pricing_type' => env('PAYMENT_CALC_DEFAULT_PRICING_TYPE', 'total'),
    'validation' => [
        'max_rental_duration' => env('PAYMENT_CALC_MAX_RENTAL_DURATION', 120),
        'max_apartment_price' => env('PAYMENT_CALC_MAX_APARTMENT_PRICE', 999999999.99),
        // ... additional validation rules
    ],
    'monitoring' => [
        'high_value_threshold' => env('PAYMENT_CALC_HIGH_VALUE_THRESHOLD', 1000000.00),
        // ... monitoring configuration
    ],
    // ... additional configuration sections
];
```

## Security Features

### Input Validation
- **Type Validation**: Ensures correct data types for all inputs
- **Range Validation**: Validates values within acceptable limits
- **Overflow Protection**: Prevents arithmetic overflow errors
- **Sanitization**: Cleans and normalizes input data

### Security Service Integration
```php
public function calculatePaymentTotalSecure(array $inputs): PaymentCalculationResult
{
    $validationResult = $this->securityService->sanitizeCalculationInputs($inputs);
    
    if (!$validationResult['is_valid']) {
        return PaymentCalculationResult::failure('Input validation failed');
    }
    
    // Continue with sanitized inputs...
}
```

### Rate Limiting
- API endpoint rate limiting via middleware
- Bulk calculation limits
- Suspicious activity detection

## Performance Optimization

### Caching Strategy
- **Result Caching**: Cache calculation results for identical inputs
- **Bulk Caching**: Cache results for batch calculations
- **Apartment Config Caching**: Cache apartment pricing configurations
- **Cache Invalidation**: Automatic cache invalidation on configuration changes

### Performance Monitoring
```php
// Performance metrics collection
$this->monitoringService->recordCalculationPerformance($calculationId, $executionTime, $result, $inputs);
$this->monitoringService->recordCalculationAccuracy($calculationId, $result, $inputs);
```

### Query Optimization
- Database indexes for apartment pricing queries
- Optimized bulk calculation processing
- Connection pooling for high-volume scenarios

## Error Handling

### Validation Errors
```php
// Input validation with detailed error messages
if ($apartmentPrice < 0) {
    return ['valid' => false, 'error' => 'Apartment price cannot be negative. Received: ' . $apartmentPrice];
}

if ($rentalDuration > self::MAX_RENTAL_DURATION) {
    return ['valid' => false, 'error' => 'Rental duration exceeds maximum allowed value'];
}
```

### Calculation Errors
```php
try {
    $totalAmount = $this->performCalculationWithProtection($apartmentPrice, $rentalDuration, $pricingType);
} catch (ArithmeticError $e) {
    return PaymentCalculationResult::failure('Arithmetic calculation error: Invalid mathematical operation');
} catch (OverflowException $e) {
    return PaymentCalculationResult::failure('Calculation result exceeds system limits');
}
```

### Fallback Logic
```php
protected function applyFallbackLogic(string $pricingType, float $apartmentPrice, int $rentalDuration): string
{
    // Apply intelligent fallback for suspicious configurations
    if ($normalizedType === 'monthly' && $apartmentPrice > 100000) {
        $projectedTotal = $apartmentPrice * $rentalDuration;
        if ($projectedTotal > self::HIGH_VALUE_THRESHOLD * 10) {
            return 'total'; // Likely a total price being treated as monthly
        }
    }
    
    return $normalizedType;
}
```

## Monitoring and Observability

### Audit Logging
```php
public function logCalculationSteps(PaymentCalculationResult $result): void
{
    Log::info('Payment calculation completed', [
        'calculation_id' => uniqid('calc_'),
        'total_amount' => $result->totalAmount,
        'calculation_method' => $result->calculationMethod,
        'is_valid' => $result->isValid,
        'steps_count' => count($result->calculationSteps),
        'calculation_steps' => $result->calculationSteps,
        'timestamp' => now()->toISOString()
    ]);
}
```

### Performance Metrics
- Calculation execution time
- Cache hit/miss ratios
- Error rates by type
- High-value calculation alerts
- Suspicious pattern detection

### Health Monitoring
```php
// Monitor for suspicious calculations
if ($totalAmount > self::HIGH_VALUE_THRESHOLD) {
    Log::info('High-value payment calculation', [
        'calculation_id' => $calculationId,
        'total_amount' => $totalAmount,
        'threshold' => self::HIGH_VALUE_THRESHOLD
    ]);
}
```

## Database Schema

### Apartment Model Extensions
```sql
ALTER TABLE apartments ADD COLUMN (
    pricing_type ENUM('total', 'monthly') DEFAULT 'total',
    price_configuration JSON NULL,
    INDEX idx_pricing_type (pricing_type)
);
```

### ProformaReceipt Model Extensions
```sql
ALTER TABLE profoma_receipts ADD COLUMN (
    calculation_method VARCHAR(100) NULL,
    calculation_log JSON NULL,
    INDEX idx_calculation_method (calculation_method)
);
```

## API Usage Examples

### Basic Calculation
```php
$service = app(PaymentCalculationServiceInterface::class);

$result = $service->calculatePaymentTotal(
    apartmentPrice: 500000.00,
    rentalDuration: 12,
    pricingType: 'total'
);

if ($result->isValid) {
    echo "Total Amount: ₦" . number_format($result->totalAmount, 2);
    echo "Method: " . $result->calculationMethod;
} else {
    echo "Error: " . $result->errorMessage;
}
```

### Secure Calculation with Input Validation
```php
$inputs = [
    'apartment_price' => $_POST['apartment_price'],
    'rental_duration' => $_POST['rental_duration'],
    'pricing_type' => $_POST['pricing_type'] ?? 'total'
];

$result = $service->calculatePaymentTotalSecure($inputs);
```

### Bulk Calculations
```php
$calculations = [
    ['apartment_price' => 500000, 'rental_duration' => 12, 'pricing_type' => 'total'],
    ['apartment_price' => 50000, 'rental_duration' => 12, 'pricing_type' => 'monthly'],
    // ... more calculations
];

$results = $service->calculateBulkPaymentTotals($calculations);
```

### Calculation with Additional Charges
```php
$additionalCharges = [
    'service_charge' => 25000,
    'security_deposit' => 100000,
    'legal_fee' => 15000
];

$result = $service->calculatePaymentTotalWithCharges(
    apartmentPrice: 500000,
    rentalDuration: 12,
    pricingType: 'total',
    additionalCharges: $additionalCharges
);
```

## Testing

### Unit Tests
```php
class PaymentCalculationServiceTest extends TestCase
{
    public function test_total_pricing_calculation()
    {
        $service = app(PaymentCalculationServiceInterface::class);
        $result = $service->calculatePaymentTotal(500000, 12, 'total');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(500000, $result->totalAmount);
        $this->assertEquals('total_price_no_multiplication', $result->calculationMethod);
    }
    
    public function test_monthly_pricing_calculation()
    {
        $service = app(PaymentCalculationServiceInterface::class);
        $result = $service->calculatePaymentTotal(50000, 12, 'monthly');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(600000, $result->totalAmount);
        $this->assertEquals('monthly_price_with_duration_multiplication', $result->calculationMethod);
    }
}
```

### Property-Based Tests
```php
class PaymentCalculationCorrectnessPropertiesTest extends TestCase
{
    /**
     * @test
     * Feature: proforma-payment-calculation-fix, Property 1: Total pricing calculation consistency
     */
    public function total_pricing_calculation_consistency()
    {
        $this->forAll(
            Generator\choose(1, 1000000),  // apartment_price
            Generator\choose(1, 120)       // rental_duration
        )->then(function ($apartmentPrice, $rentalDuration) {
            $service = app(PaymentCalculationServiceInterface::class);
            $result = $service->calculatePaymentTotal($apartmentPrice, $rentalDuration, 'total');
            
            $this->assertTrue($result->isValid);
            $this->assertEquals($apartmentPrice, $result->totalAmount);
        });
    }
}
```

## Troubleshooting

### Common Issues

#### 1. Calculation Returns Zero
**Cause**: Invalid pricing type or zero apartment price
**Solution**: Check pricing type and ensure apartment price is valid

#### 2. Overflow Errors
**Cause**: Very large apartment prices or rental durations
**Solution**: Check validation limits and adjust if necessary

#### 3. Cache Issues
**Cause**: Stale cached results after configuration changes
**Solution**: Clear calculation cache or restart cache service

#### 4. Performance Issues
**Cause**: High volume calculations without caching
**Solution**: Enable caching and optimize database queries

### Debugging

#### Enable Debug Logging
```env
PAYMENT_CALC_ENABLE_LOGGING=true
LOG_LEVEL=debug
```

#### Check Calculation Steps
```php
$result = $service->calculatePaymentTotal(500000, 12, 'total');
foreach ($result->calculationSteps as $step) {
    Log::debug('Calculation Step', $step);
}
```

#### Monitor Performance
```php
$metrics = $service->getPerformanceMetrics(24); // Last 24 hours
Log::info('Performance Metrics', $metrics);
```

## Migration Guide

### From Legacy Calculation Logic

1. **Identify Current Usage**: Find all direct price calculations in controllers
2. **Replace with Service**: Use PaymentCalculationService instead
3. **Update Pricing Types**: Set appropriate pricing_type for apartments
4. **Test Calculations**: Verify results match expected values
5. **Monitor Logs**: Check for calculation errors or warnings

### Example Migration
```php
// Before (Legacy)
$totalAmount = $apartment->amount * $rentalDuration;

// After (Service)
$service = app(PaymentCalculationServiceInterface::class);
$result = $service->calculatePaymentTotal(
    $apartment->amount,
    $rentalDuration,
    $apartment->getPricingType()
);
$totalAmount = $result->isValid ? $result->totalAmount : 0;
```

## Best Practices

### 1. Always Use the Service
- Never perform direct price calculations
- Always use PaymentCalculationService for consistency
- Handle calculation errors gracefully

### 2. Validate Inputs
- Use `calculatePaymentTotalSecure()` for user inputs
- Validate pricing configurations before saving
- Check calculation results before proceeding

### 3. Monitor Performance
- Enable caching for high-volume scenarios
- Monitor calculation performance metrics
- Set up alerts for calculation errors

### 4. Audit Trail
- Log all calculation steps for audit purposes
- Store calculation methods in database records
- Monitor for suspicious calculation patterns

### 5. Error Handling
- Always check `$result->isValid` before using results
- Provide meaningful error messages to users
- Log calculation errors for debugging

## Support and Maintenance

### Regular Tasks
- Monitor calculation performance metrics
- Review error logs for patterns
- Update validation limits as needed
- Clear old audit logs periodically

### Emergency Procedures
- Disable caching if cache corruption suspected
- Rollback to previous service version if critical issues
- Enable debug logging for troubleshooting
- Contact development team with specific error details

---

**Last Updated**: December 2024  
**Version**: 1.0  
**Maintained by**: EasyRent Development Team  
**Contact**: dev-team@easyrent.com