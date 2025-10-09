@props(['apartment'])

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Rent Payment</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('payment.pay') }}" accept-charset="UTF-8" class="form-horizontal" role="form">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="amount" class="form-label">Amount per Month</label>
                    <div class="input-group">
                        <span class="input-group-text">₦</span>
                        <input type="text" class="form-control" id="amount" value="{{ number_format($apartment->amount, 2) }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="duration" class="form-label">Duration (Months)</label>
                    <select name="duration" id="duration" class="form-control" required>
                        @for($i = 1; $i <= 24; $i++)
                            <option value="{{ $i }}">{{ $i }} {{ Str::plural('Month', $i) }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="total_amount" class="form-label">Total Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">₦</span>
                        <input type="text" class="form-control" id="total_amount" readonly>
                    </div>
                </div>
            </div>

            <input type="hidden" name="apartment_id" value="{{ $apartment->apartment_id }}">
            <input type="hidden" name="amount" id="hidden_amount">
            
            <div class="form-group">
                <button class="btn btn-primary btn-lg btn-block" type="submit">
                    Pay Now <i class="fa fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const durationSelect = document.getElementById('duration');
    const totalAmountInput = document.getElementById('total_amount');
    const hiddenAmountInput = document.getElementById('hidden_amount');

    function updateTotalAmount() {
        const amount = parseFloat(amountInput.value.replace(/,/g, ''));
        const duration = parseInt(durationSelect.value);
        const total = amount * duration;
        
        totalAmountInput.value = new Intl.NumberFormat().format(total.toFixed(2));
        hiddenAmountInput.value = total.toFixed(2);
    }

    durationSelect.addEventListener('change', updateTotalAmount);
    updateTotalAmount(); // Initial calculation
});
</script>
@endpush
