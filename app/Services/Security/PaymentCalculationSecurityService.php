<?php

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class PaymentCalculationSecurityService
{
    /**
     * Get security configuration value
     */
    protected function getConfig(string $key, $default = null)
    {
        return config("payment_calculation_security.{$key}", $default);
    }
    
    /**
     * Get input validation limits
     */
    protected function getValidationLimits(): array
    {
        return [
            'max_apartment_price' => $this->getConfig('input_validation.max_apartment_price', 999999999.99),
            'min_apartment_price' => $this->getConfig('input_validation.min_apartment_price', 0.00),
            'max_rental_duration' => $this->getConfig('input_validation.max_rental_duration', 120),
            'min_rental_duration' => $this->getConfig('input_validation.min_rental_duration', 1),
            'max_pricing_rules' => $this->getConfig('input_validation.max_pricing_rules', 10),
            'max_json_depth' => $this->getConfig('input_validation.max_json_depth', 5),
            'allowed_pricing_types' => $this->getConfig('input_validation.allowed_pricing_types', ['total', 'monthly']),
        ];
    }
    
    /**
     * Get rate limiting configuration
     */
    protected function getRateLimitConfig(): array
    {
        return [
            'requests_per_minute' => $this->getConfig('rate_limiting.requests_per_minute', 30),
            'requests_per_hour' => $this->getConfig('rate_limiting.requests_per_hour', 200),
            'suspicious_threshold' => $this->getConfig('rate_limiting.suspicious_threshold', 50),
            'user_requests_per_minute' => $this->getConfig('rate_limiting.user_requests_per_minute', 60),
            'user_requests_per_hour' => $this->getConfig('rate_limiting.user_requests_per_hour', 400),
        ];
    }
    
    /**
     * Sanitize and validate payment calculation inputs
     */
    public function sanitizeCalculationInputs(array $inputs): array
    {
        $result = [
            'is_valid' => true,
            'sanitized_inputs' => [],
            'validation_errors' => [],
            'security_issues' => []
        ];
        
        try {
            // Sanitize apartment price
            if (isset($inputs['apartment_price'])) {
                $priceResult = $this->sanitizeApartmentPrice($inputs['apartment_price']);
                if (!$priceResult['is_valid']) {
                    $result['is_valid'] = false;
                    $result['validation_errors'][] = $priceResult['error'];
                } else {
                    $result['sanitized_inputs']['apartment_price'] = $priceResult['sanitized_value'];
                }
            }
            
            // Sanitize rental duration
            if (isset($inputs['rental_duration'])) {
                $durationResult = $this->sanitizeRentalDuration($inputs['rental_duration']);
                if (!$durationResult['is_valid']) {
                    $result['is_valid'] = false;
                    $result['validation_errors'][] = $durationResult['error'];
                } else {
                    $result['sanitized_inputs']['rental_duration'] = $durationResult['sanitized_value'];
                }
            }
            
            // Sanitize pricing type
            if (isset($inputs['pricing_type'])) {
                $typeResult = $this->sanitizePricingType($inputs['pricing_type']);
                if (!$typeResult['is_valid']) {
                    $result['is_valid'] = false;
                    $result['validation_errors'][] = $typeResult['error'];
                } else {
                    $result['sanitized_inputs']['pricing_type'] = $typeResult['sanitized_value'];
                }
            }
            
            // Sanitize pricing configuration if present
            if (isset($inputs['pricing_configuration'])) {
                $configResult = $this->sanitizePricingConfiguration($inputs['pricing_configuration']);
                if (!$configResult['is_valid']) {
                    $result['is_valid'] = false;
                    $result['validation_errors'][] = $configResult['error'];
                    if ($configResult['is_security_issue']) {
                        $result['security_issues'][] = $configResult['security_issue'];
                    }
                } else {
                    $result['sanitized_inputs']['pricing_configuration'] = $configResult['sanitized_value'];
                }
            }
            
            // Check for injection attempts in all string inputs
            foreach ($inputs as $key => $value) {
                if (is_string($value)) {
                    $injectionCheck = $this->detectInjectionAttempts($key, $value);
                    if (!$injectionCheck['is_safe']) {
                        $result['security_issues'] = array_merge($result['security_issues'], $injectionCheck['threats']);
                        $this->logSecurityThreat($key, $value, $injectionCheck['threats']);
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Payment calculation input sanitization failed', [
                'inputs' => $inputs,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $result['is_valid'] = false;
            $result['validation_errors'][] = 'Input sanitization failed due to unexpected error';
        }
        
        return $result;
    }
    
    /**
     * Sanitize apartment price input
     */
    protected function sanitizeApartmentPrice($price): array
    {
        // Convert to float and validate
        if (!is_numeric($price)) {
            return [
                'is_valid' => false,
                'error' => 'Apartment price must be a valid number',
                'sanitized_value' => null
            ];
        }
        
        $sanitizedPrice = (float) $price;
        
        // Check for special float values
        if (!is_finite($sanitizedPrice)) {
            return [
                'is_valid' => false,
                'error' => 'Apartment price must be a finite number',
                'sanitized_value' => null
            ];
        }
        
        $limits = $this->getValidationLimits();
        
        // Validate range
        if ($sanitizedPrice < $limits['min_apartment_price']) {
            return [
                'is_valid' => false,
                'error' => 'Apartment price cannot be negative',
                'sanitized_value' => null
            ];
        }
        
        if ($sanitizedPrice > $limits['max_apartment_price']) {
            return [
                'is_valid' => false,
                'error' => 'Apartment price exceeds maximum allowed value of ' . number_format($limits['max_apartment_price'], 2),
                'sanitized_value' => null
            ];
        }
        
        // Round to 2 decimal places for currency precision
        $sanitizedPrice = round($sanitizedPrice, 2);
        
        return [
            'is_valid' => true,
            'sanitized_value' => $sanitizedPrice,
            'error' => null
        ];
    }
    
    /**
     * Sanitize rental duration input
     */
    protected function sanitizeRentalDuration($duration): array
    {
        // Convert to integer and validate
        if (!is_numeric($duration)) {
            return [
                'is_valid' => false,
                'error' => 'Rental duration must be a valid number',
                'sanitized_value' => null
            ];
        }
        
        $sanitizedDuration = (int) $duration;
        
        $limits = $this->getValidationLimits();
        
        // Validate range
        if ($sanitizedDuration < $limits['min_rental_duration']) {
            return [
                'is_valid' => false,
                'error' => 'Rental duration must be at least ' . $limits['min_rental_duration'] . ' month',
                'sanitized_value' => null
            ];
        }
        
        if ($sanitizedDuration > $limits['max_rental_duration']) {
            return [
                'is_valid' => false,
                'error' => 'Rental duration cannot exceed ' . $limits['max_rental_duration'] . ' months',
                'sanitized_value' => null
            ];
        }
        
        return [
            'is_valid' => true,
            'sanitized_value' => $sanitizedDuration,
            'error' => null
        ];
    }
    
    /**
     * Sanitize pricing type input
     */
    protected function sanitizePricingType($pricingType): array
    {
        if (!is_string($pricingType)) {
            return [
                'is_valid' => false,
                'error' => 'Pricing type must be a string',
                'sanitized_value' => null
            ];
        }
        
        $limits = $this->getValidationLimits();
        
        // Normalize and validate
        $sanitizedType = trim(strtolower($pricingType));
        
        if (!in_array($sanitizedType, $limits['allowed_pricing_types'])) {
            return [
                'is_valid' => false,
                'error' => 'Invalid pricing type. Must be one of: ' . implode(', ', $limits['allowed_pricing_types']),
                'sanitized_value' => null
            ];
        }
        
        return [
            'is_valid' => true,
            'sanitized_value' => $sanitizedType,
            'error' => null
        ];
    }
    
    /**
     * Sanitize and validate pricing configuration JSON
     */
    protected function sanitizePricingConfiguration($config): array
    {
        $result = [
            'is_valid' => true,
            'sanitized_value' => null,
            'error' => null,
            'is_security_issue' => false,
            'security_issue' => null
        ];
        
        try {
            // If it's a string, try to decode as JSON
            if (is_string($config)) {
                // Check for potential JSON injection attempts
                if ($this->detectJsonInjection($config)) {
                    $result['is_valid'] = false;
                    $result['is_security_issue'] = true;
                    $result['security_issue'] = 'Potential JSON injection detected';
                    $result['error'] = 'Invalid pricing configuration format';
                    return $result;
                }
                
                $limits = $this->getValidationLimits();
                $decoded = json_decode($config, true, $limits['max_json_depth']);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $result['is_valid'] = false;
                    $result['error'] = 'Invalid JSON format in pricing configuration: ' . json_last_error_msg();
                    return $result;
                }
                
                $config = $decoded;
            }
            
            // Validate configuration structure
            if (!is_array($config)) {
                $result['is_valid'] = false;
                $result['error'] = 'Pricing configuration must be an array or valid JSON object';
                return $result;
            }
            
            // Validate required fields
            if (!isset($config['pricing_type'])) {
                $result['is_valid'] = false;
                $result['error'] = 'Pricing configuration must include pricing_type field';
                return $result;
            }
            
            $limits = $this->getValidationLimits();
            
            // Validate pricing type
            if (!in_array($config['pricing_type'], $limits['allowed_pricing_types'])) {
                $result['is_valid'] = false;
                $result['error'] = 'Invalid pricing_type in configuration. Must be one of: ' . implode(', ', $limits['allowed_pricing_types']);
                return $result;
            }
            
            // Validate optional fields
            $sanitizedConfig = [
                'pricing_type' => $config['pricing_type']
            ];
            
            // Validate base_price if present
            if (isset($config['base_price'])) {
                if (!is_numeric($config['base_price']) || $config['base_price'] < 0) {
                    $result['is_valid'] = false;
                    $result['error'] = 'base_price must be a non-negative number';
                    return $result;
                }
                $sanitizedConfig['base_price'] = (float) $config['base_price'];
            }
            
            // Validate duration_multiplier if present
            if (isset($config['duration_multiplier'])) {
                if (!is_numeric($config['duration_multiplier']) || $config['duration_multiplier'] <= 0) {
                    $result['is_valid'] = false;
                    $result['error'] = 'duration_multiplier must be a positive number';
                    return $result;
                }
                $sanitizedConfig['duration_multiplier'] = (float) $config['duration_multiplier'];
            }
            
            // Validate pricing_rules if present
            if (isset($config['pricing_rules'])) {
                $rulesResult = $this->validatePricingRules($config['pricing_rules']);
                if (!$rulesResult['is_valid']) {
                    $result['is_valid'] = false;
                    $result['error'] = $rulesResult['error'];
                    return $result;
                }
                $sanitizedConfig['pricing_rules'] = $rulesResult['sanitized_rules'];
            }
            
            $result['sanitized_value'] = $sanitizedConfig;
            
        } catch (\Exception $e) {
            Log::error('Pricing configuration sanitization failed', [
                'config' => $config,
                'error' => $e->getMessage()
            ]);
            
            $result['is_valid'] = false;
            $result['error'] = 'Failed to process pricing configuration';
        }
        
        return $result;
    }
    
    /**
     * Validate pricing rules array
     */
    protected function validatePricingRules(array $rules): array
    {
        $limits = $this->getValidationLimits();
        
        if (count($rules) > $limits['max_pricing_rules']) {
            return [
                'is_valid' => false,
                'error' => 'Too many pricing rules. Maximum allowed: ' . $limits['max_pricing_rules'],
                'sanitized_rules' => null
            ];
        }
        
        $sanitizedRules = [];
        
        foreach ($rules as $index => $rule) {
            if (!is_array($rule)) {
                return [
                    'is_valid' => false,
                    'error' => "Pricing rule at index {$index} must be an array",
                    'sanitized_rules' => null
                ];
            }
            
            // Validate rule structure
            $requiredFields = ['condition', 'value'];
            foreach ($requiredFields as $field) {
                if (!isset($rule[$field])) {
                    return [
                        'is_valid' => false,
                        'error' => "Pricing rule at index {$index} missing required field: {$field}",
                        'sanitized_rules' => null
                    ];
                }
            }
            
            // Sanitize rule values
            $sanitizedRule = [
                'condition' => $this->sanitizeString($rule['condition']),
                'value' => is_numeric($rule['value']) ? (float) $rule['value'] : $rule['value']
            ];
            
            if (isset($rule['description'])) {
                $sanitizedRule['description'] = $this->sanitizeString($rule['description']);
            }
            
            $sanitizedRules[] = $sanitizedRule;
        }
        
        return [
            'is_valid' => true,
            'sanitized_rules' => $sanitizedRules,
            'error' => null
        ];
    }
    
    /**
     * Detect potential JSON injection attempts
     */
    protected function detectJsonInjection(string $json): bool
    {
        // Check for suspicious patterns that might indicate injection attempts
        $suspiciousPatterns = [
            '/\$\{.*\}/',           // Template injection
            '/<%.*%>/',             // Server-side template injection
            '/\{\{.*\}\}/',         // Template injection
            '/javascript:/i',        // JavaScript protocol
            '/<script/i',           // Script tags
            '/eval\s*\(/i',         // Eval function
            '/function\s*\(/i',     // Function declarations
            '/constructor/i',       // Constructor access
            '/__proto__/i',         // Prototype pollution
            '/prototype/i',         // Prototype manipulation
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $json)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect injection attempts in string inputs
     */
    protected function detectInjectionAttempts(string $fieldName, string $value): array
    {
        $threats = [];
        
        // SQL injection patterns
        $sqlPatterns = [
            '/(\bunion\b|\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b)/i',
            '/[\'";]\s*(or|and)\s+[\'"]?\w+[\'"]?\s*=/i',
            '/\b(exec|execute|sp_|xp_)\w*/i'
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = 'sql_injection_attempt';
                break;
            }
        }
        
        // XSS patterns
        $xssPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = 'xss_attempt';
                break;
            }
        }
        
        // Command injection patterns
        $cmdPatterns = [
            '/[;&|`$(){}[\]]/i',
            '/\b(exec|system|shell_exec|passthru|eval)\b/i'
        ];
        
        foreach ($cmdPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = 'command_injection_attempt';
                break;
            }
        }
        
        return [
            'is_safe' => empty($threats),
            'threats' => $threats
        ];
    }
    
    /**
     * Sanitize string input
     */
    protected function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        // Trim whitespace
        $value = trim($value);
        
        // Encode HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $value;
    }
    
    /**
     * Check rate limits for calculation requests
     */
    public function checkCalculationRateLimit(Request $request): array
    {
        $ipAddress = $request->ip();
        $userId = $request->user() ? $request->user()->id : null;
        
        // Create cache keys
        $minuteKey = "calc_rate_limit:minute:{$ipAddress}";
        $hourKey = "calc_rate_limit:hour:{$ipAddress}";
        $suspiciousKey = "calc_rate_limit:suspicious:{$ipAddress}";
        
        if ($userId) {
            $userMinuteKey = "calc_rate_limit:user:minute:{$userId}";
            $userHourKey = "calc_rate_limit:user:hour:{$userId}";
        }
        
        // Get current counts
        $requestsPerMinute = Cache::get($minuteKey, 0);
        $requestsPerHour = Cache::get($hourKey, 0);
        $suspiciousRequests = Cache::get($suspiciousKey, 0);
        
        $userRequestsPerMinute = $userId ? Cache::get($userMinuteKey, 0) : 0;
        $userRequestsPerHour = $userId ? Cache::get($userHourKey, 0) : 0;
        
        $rateLimits = $this->getRateLimitConfig();
        
        // Check for suspicious activity
        if ($suspiciousRequests >= $rateLimits['suspicious_threshold']) {
            return [
                'allowed' => false,
                'reason' => 'Suspicious calculation activity detected',
                'retry_after' => 300, // 5 minutes
                'is_suspicious' => true
            ];
        }
        
        // Check minute limits
        if ($requestsPerMinute >= $rateLimits['requests_per_minute']) {
            return [
                'allowed' => false,
                'reason' => 'Too many calculation requests per minute',
                'retry_after' => 60,
                'is_suspicious' => false
            ];
        }
        
        // Check hour limits
        if ($requestsPerHour >= $rateLimits['requests_per_hour']) {
            return [
                'allowed' => false,
                'reason' => 'Too many calculation requests per hour',
                'retry_after' => 3600,
                'is_suspicious' => false
            ];
        }
        
        // For authenticated users, apply stricter limits
        if ($userId) {
            if ($userRequestsPerMinute >= $rateLimits['user_requests_per_minute']) {
                return [
                    'allowed' => false,
                    'reason' => 'User calculation rate limit exceeded',
                    'retry_after' => 60,
                    'is_suspicious' => true
                ];
            }
        }
        
        return [
            'allowed' => true,
            'reason' => null,
            'retry_after' => 0,
            'is_suspicious' => false
        ];
    }
    
    /**
     * Record calculation request for rate limiting
     */
    public function recordCalculationRequest(Request $request): void
    {
        $ipAddress = $request->ip();
        $userId = $request->user() ? $request->user()->id : null;
        
        // Create cache keys
        $minuteKey = "calc_rate_limit:minute:{$ipAddress}";
        $hourKey = "calc_rate_limit:hour:{$ipAddress}";
        $suspiciousKey = "calc_rate_limit:suspicious:{$ipAddress}";
        
        // Increment counters
        Cache::increment($minuteKey, 1);
        Cache::expire($minuteKey, 60);
        
        Cache::increment($hourKey, 1);
        Cache::expire($hourKey, 3600);
        
        Cache::increment($suspiciousKey, 1);
        Cache::expire($suspiciousKey, 300);
        
        // Track user-specific limits if authenticated
        if ($userId) {
            $userMinuteKey = "calc_rate_limit:user:minute:{$userId}";
            $userHourKey = "calc_rate_limit:user:hour:{$userId}";
            
            Cache::increment($userMinuteKey, 1);
            Cache::expire($userMinuteKey, 60);
            
            Cache::increment($userHourKey, 1);
            Cache::expire($userHourKey, 3600);
        }
    }
    
    /**
     * Log security threat
     */
    protected function logSecurityThreat(string $fieldName, string $value, array $threats): void
    {
        Log::warning('Security threat detected in payment calculation input', [
            'field_name' => $fieldName,
            'value_length' => strlen($value),
            'value_preview' => substr($value, 0, 100),
            'threats' => $threats,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Validate access control for pricing configuration changes
     */
    public function validatePricingConfigurationAccess(Request $request): array
    {
        $user = $request->user();
        
        if (!$user) {
            return [
                'allowed' => false,
                'reason' => 'Authentication required for pricing configuration changes'
            ];
        }
        
        // Check if user has admin role
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
                return [
                    'allowed' => true,
                    'reason' => null
                ];
            }
        }
        
        // Check if user is property owner for the specific apartment
        if ($request->has('apartment_id')) {
            $apartmentId = $request->input('apartment_id');
            
            // This would need to be implemented based on your apartment ownership logic
            if ($this->userOwnsApartment($user, $apartmentId)) {
                return [
                    'allowed' => true,
                    'reason' => null
                ];
            }
        }
        
        return [
            'allowed' => false,
            'reason' => 'Insufficient permissions for pricing configuration changes'
        ];
    }
    
    /**
     * Check if user owns the apartment (placeholder - implement based on your logic)
     */
    protected function userOwnsApartment($user, $apartmentId): bool
    {
        // This is a placeholder - implement based on your apartment ownership logic
        // You might check through Property -> Apartment relationships
        return false;
    }
}