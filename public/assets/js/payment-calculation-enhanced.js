/**
 * Enhanced Payment Calculation JavaScript
 * Handles calculation display, error handling, and user feedback
 */

class PaymentCalculationHandler {
    constructor(options = {}) {
        this.options = {
            maxSafeInteger: Number.MAX_SAFE_INTEGER,
            maxDuration: 120, // months
            minPrice: 0.01,
            maxPrice: 999999999.99,
            ...options
        };
        
        this.init();
    }
    
    init() {
        // Initialize calculation handlers
        this.bindEvents();
        this.setupErrorHandling();
    }
    
    bindEvents() {
        // Duration change handlers
        const durationSelects = document.querySelectorAll('[id*="duration"]');
        durationSelects.forEach(select => {
            if (select.tagName === 'SELECT') {
                select.addEventListener('change', (e) => {
                    this.handleDurationChange(e.target);
                });
            }
        });
        
        // Form submission handlers
        const forms = document.querySelectorAll('form[id*="application"], form[id*="payment"]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                this.handleFormSubmission(e);
            });
        });
    }
    
    setupErrorHandling() {
        // Global error handler for calculation errors
        window.addEventListener('error', (e) => {
            if (e.message.includes('calculation') || e.message.includes('payment')) {
                this.showError('A calculation error occurred. Please refresh the page and try again.');
                console.error('Payment calculation error:', e);
            }
        });
    }
    
    handleDurationChange(selectElement) {
        try {
            const duration = parseInt(selectElement.value);
            const isUnauthenticated = selectElement.id.includes('unauth');
            
            // Validate duration
            if (!this.validateDuration(duration)) {
                this.showError('Invalid duration selected', isUnauthenticated);
                return;
            }
            
            // Get pricing data from page
            const pricingData = this.getPricingData();
            if (!pricingData) {
                this.showError('Unable to load pricing information', isUnauthenticated);
                return;
            }
            
            // Calculate total
            const result = this.calculateTotal(pricingData.basePrice, duration, pricingData.pricingType);
            if (!result.success) {
                this.showError(result.error, isUnauthenticated);
                return;
            }
            
            // Update display
            this.updateDisplay(duration, result.total, isUnauthenticated);
            this.hideError(isUnauthenticated);
            
        } catch (error) {
            console.error('Duration change error:', error);
            this.showError('Calculation failed: ' + error.message, selectElement.id.includes('unauth'));
        }
    }
    
    handleFormSubmission(event) {
        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (submitBtn) {
            // Add loading state
            this.setLoadingState(submitBtn, true);
            
            // Validate form data
            const validation = this.validateFormData(form);
            if (!validation.valid) {
                event.preventDefault();
                this.showError(validation.error);
                this.setLoadingState(submitBtn, false);
                return;
            }
        }
    }
    
    validateDuration(duration) {
        return Number.isInteger(duration) && 
               duration > 0 && 
               duration <= this.options.maxDuration;
    }
    
    validatePrice(price) {
        return typeof price === 'number' && 
               isFinite(price) && 
               price >= 0 && 
               price <= this.options.maxPrice;
    }
    
    getPricingData() {
        try {
            // Try to get data from global variables set by Blade
            if (typeof window.apartmentAmount !== 'undefined' && typeof window.pricingType !== 'undefined') {
                return {
                    basePrice: window.apartmentAmount,
                    pricingType: window.pricingType
                };
            }
            
            // Fallback: try to extract from page elements
            const priceElement = document.querySelector('[data-apartment-price]');
            const typeElement = document.querySelector('[data-pricing-type]');
            
            if (priceElement && typeElement) {
                return {
                    basePrice: parseFloat(priceElement.dataset.apartmentPrice),
                    pricingType: typeElement.dataset.pricingType
                };
            }
            
            return null;
        } catch (error) {
            console.error('Error getting pricing data:', error);
            return null;
        }
    }
    
    calculateTotal(basePrice, duration, pricingType) {
        try {
            // Validate inputs
            if (!this.validatePrice(basePrice)) {
                return { success: false, error: 'Invalid apartment price' };
            }
            
            if (!this.validateDuration(duration)) {
                return { success: false, error: 'Invalid duration' };
            }
            
            let total;
            
            if (pricingType === 'total') {
                total = basePrice;
            } else if (pricingType === 'monthly') {
                // Check for overflow
                if (basePrice > 0 && duration > (this.options.maxSafeInteger / basePrice)) {
                    return { success: false, error: 'Calculation would exceed safe limits' };
                }
                total = basePrice * duration;
            } else {
                return { success: false, error: 'Unknown pricing type: ' + pricingType };
            }
            
            // Validate result
            if (!isFinite(total) || total < 0) {
                return { success: false, error: 'Invalid calculation result' };
            }
            
            return { success: true, total: total };
            
        } catch (error) {
            return { success: false, error: 'Calculation error: ' + error.message };
        }
    }
    
    updateDisplay(duration, total, isUnauthenticated = false) {
        const prefix = isUnauthenticated ? 'unauth-' : '';
        
        // Update duration display with proper name
        const durationDisplay = document.getElementById(prefix + 'duration-display');
        if (durationDisplay) {
            // Try to get duration name from global durationNames if available
            const durationName = (typeof window.durationNames !== 'undefined' && window.durationNames[duration]) 
                ? window.durationNames[duration] 
                : duration + ' months';
            durationDisplay.textContent = durationName;
        }
        
        // Update total amount
        const totalAmountEl = document.getElementById(prefix + 'total-amount');
        if (totalAmountEl) {
            totalAmountEl.textContent = '₦' + this.formatNumber(total);
        }
        
        // Update calculation breakdown
        const calcDurationEl = document.getElementById((isUnauthenticated ? 'unauth-' : '') + 'calc-duration');
        if (calcDurationEl) {
            calcDurationEl.textContent = duration;
        }
    }
    
    showError(message, isUnauthenticated = false) {
        const prefix = isUnauthenticated ? 'unauth-' : '';
        let errorDiv = document.getElementById(prefix + 'calculation-error');
        
        if (!errorDiv) {
            // Create error div if it doesn't exist
            errorDiv = this.createErrorDiv(prefix + 'calculation-error');
        }
        
        const errorMessage = document.getElementById(prefix + 'error-message');
        if (errorMessage) {
            errorMessage.textContent = message;
        }
        
        if (errorDiv) {
            errorDiv.style.display = 'block';
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
    
    hideError(isUnauthenticated = false) {
        const prefix = isUnauthenticated ? 'unauth-' : '';
        const errorDiv = document.getElementById(prefix + 'calculation-error');
        
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }
    
    createErrorDiv(id) {
        const errorDiv = document.createElement('div');
        errorDiv.id = id;
        errorDiv.className = 'alert alert-warning calculation-error mt-2';
        errorDiv.style.display = 'none';
        errorDiv.innerHTML = `
            <small>
                <i class="fas fa-exclamation-triangle me-1"></i>
                <span id="${id.replace('-calculation-error', '-error-message')}">Calculation error occurred</span>
            </small>
        `;
        
        // Try to insert after payment summary
        const summaryCard = document.querySelector('.total-calculation .card-body');
        if (summaryCard) {
            summaryCard.appendChild(errorDiv);
        }
        
        return errorDiv;
    }
    
    validateFormData(form) {
        try {
            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    return {
                        valid: false,
                        error: `Please fill in the ${field.name || 'required'} field`
                    };
                }
            }
            
            // Validate email fields
            const emailFields = form.querySelectorAll('input[type="email"]');
            for (let field of emailFields) {
                if (field.value && !this.validateEmail(field.value)) {
                    return {
                        valid: false,
                        error: 'Please enter a valid email address'
                    };
                }
            }
            
            // Validate date fields
            const dateFields = form.querySelectorAll('input[type="date"]');
            for (let field of dateFields) {
                if (field.value && new Date(field.value) < new Date()) {
                    return {
                        valid: false,
                        error: 'Please select a future date'
                    };
                }
            }
            
            return { valid: true };
            
        } catch (error) {
            return {
                valid: false,
                error: 'Form validation error: ' + error.message
            };
        }
    }
    
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    setLoadingState(button, loading) {
        if (loading) {
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            button.disabled = true;
            button.classList.add('loading');
        } else {
            button.innerHTML = button.dataset.originalText || button.innerHTML;
            button.disabled = false;
            button.classList.remove('loading');
        }
    }
    
    formatNumber(number) {
        return new Intl.NumberFormat('en-NG').format(number);
    }
    
    // Public methods for external use
    static init(options = {}) {
        return new PaymentCalculationHandler(options);
    }
    
    static updateCalculation(duration, isUnauthenticated = false) {
        const handler = new PaymentCalculationHandler();
        const selectElement = document.getElementById(
            (isUnauthenticated ? 'unauth' : '') + 'DurationSelect'
        );
        
        if (selectElement) {
            selectElement.value = duration;
            handler.handleDurationChange(selectElement);
        }
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the payment calculation handler
    window.paymentCalculationHandler = PaymentCalculationHandler.init();
    
    // Set up global pricing data if available
    const apartmentAmountEl = document.querySelector('[data-apartment-amount]');
    const pricingTypeEl = document.querySelector('[data-pricing-type]');
    
    if (apartmentAmountEl && pricingTypeEl) {
        window.apartmentAmount = parseFloat(apartmentAmountEl.dataset.apartmentAmount);
        window.pricingType = pricingTypeEl.dataset.pricingType;
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PaymentCalculationHandler;
}