<?php

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InputValidationService
{
    /**
     * Dangerous patterns to detect
     */
    const XSS_PATTERNS = [
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<iframe/i',
        '/<object/i',
        '/<embed/i',
        '/<link/i',
        '/<meta/i'
    ];
    
    const SQL_INJECTION_PATTERNS = [
        '/(\bunion\b|\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b|\bcreate\b|\balter\b)/i',
        '/(\bor\b|\band\b)\s+\d+\s*=\s*\d+/i',
        '/[\'";]\s*(or|and)\s+[\'"]?\w+[\'"]?\s*=/i',
        '/\b(exec|execute|sp_|xp_)\w*/i'
    ];
    
    const PATH_TRAVERSAL_PATTERNS = [
        '/\.\.[\/\\\\]/',
        '/[\/\\\\]\.\.[\/\\\\]/',
        '/%2e%2e[\/\\\\]/',
        '/\.\.[%2f%5c]/',
        '/[%2f%5c]\.\.[%2f%5c]/'
    ];
    
    /**
     * Validate and sanitize request input
     */
    public function validateAndSanitizeRequest(Request $request): array
    {
        $results = [
            'is_safe' => true,
            'threats_detected' => [],
            'sanitized_input' => [],
            'blocked_fields' => []
        ];
        
        $input = $request->all();
        
        foreach ($input as $key => $value) {
            $fieldResult = $this->validateField($key, $value);
            
            if (!$fieldResult['is_safe']) {
                $results['is_safe'] = false;
                $results['threats_detected'] = array_merge(
                    $results['threats_detected'], 
                    $fieldResult['threats']
                );
                
                if ($fieldResult['should_block']) {
                    $results['blocked_fields'][] = $key;
                    continue; // Don't include in sanitized input
                }
            }
            
            // Always include sanitized value (even for unsafe but non-blocked fields)
            $results['sanitized_input'][$key] = $fieldResult['sanitized_value'];
        }
        
        // Log security issues if found
        if (!$results['is_safe']) {
            $this->logSecurityThreats($request, $results);
        }
        
        return $results;
    }
    
    /**
     * Validate individual field
     */
    protected function validateField(string $fieldName, $value): array
    {
        $result = [
            'is_safe' => true,
            'threats' => [],
            'sanitized_value' => $value,
            'should_block' => false
        ];
        
        // Skip validation for non-string values
        if (!is_string($value)) {
            return $result;
        }
        
        // Check for XSS attempts
        $xssThreats = $this->detectXSS($value);
        if (!empty($xssThreats)) {
            $result['is_safe'] = false;
            $result['threats'] = array_merge($result['threats'], $xssThreats);
            
            // For name fields, sanitize instead of blocking
            if (in_array($fieldName, ['name', 'first_name', 'last_name', 'prospect_name'])) {
                $result['should_block'] = false; // Allow sanitization
            } else {
                $result['should_block'] = true; // XSS is blocked for other fields
            }
        }
        
        // Check for SQL injection attempts
        $sqlThreats = $this->detectSQLInjection($value);
        if (!empty($sqlThreats)) {
            $result['is_safe'] = false;
            $result['threats'] = array_merge($result['threats'], $sqlThreats);
            
            // For name fields, sanitize instead of blocking
            if (in_array($fieldName, ['name', 'first_name', 'last_name', 'prospect_name'])) {
                $result['should_block'] = false; // Allow sanitization
            } else {
                $result['should_block'] = true; // SQL injection is blocked for other fields
            }
        }
        
        // Check for path traversal attempts
        $pathThreats = $this->detectPathTraversal($value);
        if (!empty($pathThreats)) {
            $result['is_safe'] = false;
            $result['threats'] = array_merge($result['threats'], $pathThreats);
            
            // For name fields, sanitize instead of blocking
            if (in_array($fieldName, ['name', 'first_name', 'last_name', 'prospect_name'])) {
                $result['should_block'] = false; // Allow sanitization
            } else {
                $result['should_block'] = true; // Path traversal is blocked for other fields
            }
        }
        
        // Check for command injection
        $cmdThreats = $this->detectCommandInjection($value);
        if (!empty($cmdThreats)) {
            $result['is_safe'] = false;
            $result['threats'] = array_merge($result['threats'], $cmdThreats);
            
            // For name fields, sanitize instead of blocking
            if (in_array($fieldName, ['name', 'first_name', 'last_name', 'prospect_name'])) {
                $result['should_block'] = false; // Allow sanitization
            } else {
                $result['should_block'] = true; // Command injection is blocked for other fields
            }
        }
        
        // Sanitize the value if it's safe to include
        if (!$result['should_block']) {
            $result['sanitized_value'] = $this->sanitizeValue($fieldName, $value);
        }
        
        return $result;
    }
    
    /**
     * Detect XSS attempts
     */
    protected function detectXSS(string $value): array
    {
        $threats = [];
        
        foreach (self::XSS_PATTERNS as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = 'xss_attempt';
                break;
            }
        }
        
        // Check for encoded XSS attempts
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $urlDecoded = urldecode($value);
        
        if ($decoded !== $value || $urlDecoded !== $value) {
            foreach (self::XSS_PATTERNS as $pattern) {
                if (preg_match($pattern, $decoded) || preg_match($pattern, $urlDecoded)) {
                    $threats[] = 'encoded_xss_attempt';
                    break;
                }
            }
        }
        
        return array_unique($threats);
    }
    
    /**
     * Detect SQL injection attempts
     */
    protected function detectSQLInjection(string $value): array
    {
        $threats = [];
        
        foreach (self::SQL_INJECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = 'sql_injection_attempt';
                break;
            }
        }
        
        // Check for common SQL injection indicators
        $indicators = ['\'', '"', ';', '--', '/*', '*/', 'xp_', 'sp_'];
        $suspiciousCount = 0;
        
        foreach ($indicators as $indicator) {
            if (stripos($value, $indicator) !== false) {
                $suspiciousCount++;
            }
        }
        
        if ($suspiciousCount >= 2) {
            $threats[] = 'potential_sql_injection';
        }
        
        return array_unique($threats);
    }
    
    /**
     * Detect path traversal attempts
     */
    protected function detectPathTraversal(string $value): array
    {
        $threats = [];
        
        foreach (self::PATH_TRAVERSAL_PATTERNS as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = 'path_traversal_attempt';
                break;
            }
        }
        
        return $threats;
    }
    
    /**
     * Detect command injection attempts
     */
    protected function detectCommandInjection(string $value): array
    {
        $threats = [];
        
        $cmdPatterns = [
            '/[;&|`$(){}[\]]/i',
            '/\b(exec|system|shell_exec|passthru|eval|base64_decode)\b/i',
            '/\b(wget|curl|nc|netcat|telnet|ssh)\b/i'
        ];
        
        foreach ($cmdPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = 'command_injection_attempt';
                break;
            }
        }
        
        return $threats;
    }
    
    /**
     * Sanitize value based on field type
     */
    protected function sanitizeValue(string $fieldName, string $value): string
    {
        // Field-specific sanitization
        switch ($fieldName) {
            case 'email':
            case 'prospect_email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
                
            case 'phone':
            case 'prospect_phone':
                return preg_replace('/[^0-9+\-\s()]/', '', $value);
                
            case 'name':
            case 'first_name':
            case 'last_name':
            case 'prospect_name':
                return $this->sanitizeName($value);
                
            case 'duration':
            case 'lease_duration':
                return (string) intval($value);
                
            case 'amount':
            case 'total_amount':
                return number_format(floatval($value), 2, '.', '');
                
            case 'move_in_date':
                return $this->sanitizeDate($value);
                
            default:
                return $this->sanitizeGeneral($value);
        }
    }
    
    /**
     * Sanitize name fields
     */
    protected function sanitizeName(string $value): string
    {
        // First, remove common script patterns
        $value = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $value);
        $value = preg_replace('/javascript:/i', '', $value);
        $value = preg_replace('/on\w+\s*=/i', '', $value);
        
        // Remove HTML tags
        $value = strip_tags($value);
        
        // Remove common XSS patterns that might remain
        $value = preg_replace('/alert\s*\([^)]*\)/i', '', $value);
        $value = preg_replace('/document\./i', '', $value);
        $value = preg_replace('/window\./i', '', $value);
        
        // Remove special characters except spaces, hyphens, and apostrophes
        $value = preg_replace('/[^a-zA-Z\s\-\']/', '', $value);
        
        // Trim and normalize spaces
        $value = preg_replace('/\s+/', ' ', trim($value));
        
        // Limit length
        return Str::limit($value, 100);
    }
    
    /**
     * Sanitize date fields
     */
    protected function sanitizeDate(string $value): string
    {
        // Remove any non-date characters
        $value = preg_replace('/[^0-9\-\/]/', '', $value);
        
        // Validate date format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        
        // Try to parse and reformat
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return '';
    }
    
    /**
     * General sanitization for text fields
     */
    protected function sanitizeGeneral(string $value): string
    {
        // Remove HTML tags
        $value = strip_tags($value);
        
        // Encode HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        // Normalize line endings
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        
        // Trim whitespace
        $value = trim($value);
        
        return $value;
    }
    
    /**
     * Log security threats
     */
    protected function logSecurityThreats(Request $request, array $results): void
    {
        Log::warning('Security threats detected in request input', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'threats' => $results['threats_detected'],
            'blocked_fields' => $results['blocked_fields'],
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Validate invitation token format
     */
    public function validateInvitationToken(string $token): array
    {
        $result = [
            'is_valid' => true,
            'issues' => []
        ];
        
        // Check length
        if (strlen($token) !== 64) {
            $result['is_valid'] = false;
            $result['issues'][] = 'invalid_length';
        }
        
        // Check format (should be hexadecimal)
        if (!ctype_xdigit($token)) {
            $result['is_valid'] = false;
            $result['issues'][] = 'invalid_format';
        }
        
        // Check for suspicious patterns
        if (preg_match('/(.)\1{10,}/', $token)) {
            $result['is_valid'] = false;
            $result['issues'][] = 'suspicious_pattern';
        }
        
        return $result;
    }
    
    /**
     * Sanitize file upload
     */
    public function sanitizeFileUpload($file): array
    {
        $result = [
            'is_safe' => true,
            'issues' => [],
            'sanitized_name' => null
        ];
        
        if (!$file) {
            return $result;
        }
        
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        
        // Check file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        if (!in_array(strtolower($extension), $allowedExtensions)) {
            $result['is_safe'] = false;
            $result['issues'][] = 'invalid_extension';
        }
        
        // Check MIME type
        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!in_array($mimeType, $allowedMimeTypes)) {
            $result['is_safe'] = false;
            $result['issues'][] = 'invalid_mime_type';
        }
        
        // Sanitize filename
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
        $sanitizedName = Str::limit($sanitizedName, 100);
        
        $result['sanitized_name'] = $sanitizedName;
        
        return $result;
    }
}