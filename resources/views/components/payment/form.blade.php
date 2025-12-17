<div class="payment-form-component">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
                <i class="nc-icon nc-money-coins"></i>
                Payment Information
            </h5>
        </div>
        <div class="card-body">
            <form id="paymentForm" method="POST" action="{{ $action ?? '#' }}">
                @csrf
                
                <!-- Payment Amount -->
                <div class="form-group">
                    <label for="amount">Payment Amount *</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">₦</span>
                        </div>
                        <input type="number" 
                               class="form-control" 
                               id="amount" 
                               name="amount" 
                               value="{{ $amount ?? '' }}" 
                               step="0.01" 
                               min="0" 
                               required>
                    </div>
                    <small class="form-text text-muted">Enter the payment amount in Naira</small>
                </div>

                <!-- Payment Description -->
                <div class="form-group">
                    <label for="description">Payment Description</label>
                    <textarea class="form-control" 
                              id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Enter payment description or notes">{{ $description ?? '' }}</textarea>
                </div>

                <!-- Payment Method -->
                <div class="form-group">
                    <label for="payment_method">Payment Method *</label>
                    <select class="form-control" id="payment_method" name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="bank_transfer" {{ ($paymentMethod ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="card" {{ ($paymentMethod ?? '') == 'card' ? 'selected' : '' }}>Debit/Credit Card</option>
                        <option value="ussd" {{ ($paymentMethod ?? '') == 'ussd' ? 'selected' : '' }}>USSD</option>
                        <option value="mobile_money" {{ ($paymentMethod ?? '') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    </select>
                </div>

                <!-- Payment Type (if applicable) -->
                @if(isset($showPaymentType) && $showPaymentType)
                <div class="form-group">
                    <label for="payment_type">Payment Type</label>
                    <select class="form-control" id="payment_type" name="payment_type">
                        <option value="rent" {{ ($paymentType ?? '') == 'rent' ? 'selected' : '' }}>Rent Payment</option>
                        <option value="deposit" {{ ($paymentType ?? '') == 'deposit' ? 'selected' : '' }}>Security Deposit</option>
                        <option value="commission" {{ ($paymentType ?? '') == 'commission' ? 'selected' : '' }}>Commission</option>
                        <option value="maintenance" {{ ($paymentType ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance Fee</option>
                        <option value="other" {{ ($paymentType ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                @endif

                <!-- Due Date (if applicable) -->
                @if(isset($showDueDate) && $showDueDate)
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" 
                           class="form-control" 
                           id="due_date" 
                           name="due_date" 
                           value="{{ $dueDate ?? '' }}">
                </div>
                @endif

                <!-- Recurring Payment Options (if applicable) -->
                @if(isset($showRecurring) && $showRecurring)
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_recurring" 
                               name="is_recurring" 
                               value="1" 
                               {{ ($isRecurring ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_recurring">
                            Set up recurring payment
                        </label>
                    </div>
                </div>

                <div id="recurringOptions" style="display: {{ ($isRecurring ?? false) ? 'block' : 'none' }};">
                    <div class="form-group">
                        <label for="recurring_frequency">Frequency</label>
                        <select class="form-control" id="recurring_frequency" name="recurring_frequency">
                            <option value="monthly" {{ ($recurringFrequency ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ ($recurringFrequency ?? '') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ ($recurringFrequency ?? '') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                </div>
                @endif

                <!-- Hidden Fields -->
                @if(isset($hiddenFields))
                    @foreach($hiddenFields as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                @endif

                <!-- Form Actions -->
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            @if(isset($showCancel) && $showCancel)
                            <button type="button" class="btn btn-secondary btn-block" onclick="history.back()">
                                <i class="nc-icon nc-minimal-left"></i>
                                Cancel
                            </button>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="nc-icon nc-check-2"></i>
                                {{ $submitText ?? 'Process Payment' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle recurring options
    const recurringCheckbox = document.getElementById('is_recurring');
    const recurringOptions = document.getElementById('recurringOptions');
    
    if (recurringCheckbox && recurringOptions) {
        recurringCheckbox.addEventListener('change', function() {
            recurringOptions.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Format amount input
    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            // Remove any non-numeric characters except decimal point
            this.value = this.value.replace(/[^0-9.]/g, '');
            
            // Ensure only one decimal point
            const parts = this.value.split('.');
            if (parts.length > 2) {
                this.value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limit to 2 decimal places
            if (parts[1] && parts[1].length > 2) {
                this.value = parts[0] + '.' + parts[1].substring(0, 2);
            }
        });
    }

    // Form validation
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            const amount = parseFloat(document.getElementById('amount').value);
            
            if (amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid payment amount greater than 0');
                return false;
            }
            
            if (amount > 10000000) { // 10 million naira limit
                e.preventDefault();
                alert('Payment amount cannot exceed ₦10,000,000');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="nc-icon nc-refresh-69 fa-spin"></i> Processing...';
            }
        });
    }
});
</script>

<style>
.payment-form-component .card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.payment-form-component .card-header {
    background: linear-gradient(45deg, #51cbce, #4CAF50);
    color: white;
    border-radius: 10px 10px 0 0;
}

.payment-form-component .form-group label {
    font-weight: 600;
    color: #333;
}

.payment-form-component .input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
    font-weight: bold;
}

.payment-form-component .form-control:focus {
    border-color: #51cbce;
    box-shadow: 0 0 0 0.2rem rgba(81, 203, 206, 0.25);
}

.payment-form-component .btn-primary {
    background: linear-gradient(45deg, #51cbce, #4CAF50);
    border: none;
    font-weight: 600;
}

.payment-form-component .btn-primary:hover {
    background: linear-gradient(45deg, #4CAF50, #51cbce);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

#recurringOptions {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
}
</style>