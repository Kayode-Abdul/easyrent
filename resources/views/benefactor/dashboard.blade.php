@include('header')

<div class="wrapper">
    <div class="main-panel">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="mb-4">Benefactor Dashboard</h2>

                        @if(session('benefactor_migrated'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Welcome back!</strong> We found {{ session('payment_count') }} previous payment(s)
                            you made.
                            Your complete payment history is now available in your dashboard.
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        @endif

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        @endif

                        <!-- Summary Cards -->
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <div class="card card-stats">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center icon-warning">
                                                    <i class="nc-icon nc-money-coins text-warning"></i>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="numbers">
                                                    <p class="card-category">Total Paid</p>
                                                    <p class="card-title">{{ format_money(isset($payments) ? $payments->where('status', 'completed')->sum('amount') : 0) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="card card-stats">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center icon-success">
                                                    <i class="nc-icon nc-single-02 text-success"></i>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="numbers">
                                                    <p class="card-category">Tenants</p>
                                                    <p class="card-title">{{ isset($tenants) ? $tenants->count() : 0 }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="card card-stats">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center icon-info">
                                                    <i class="nc-icon nc-refresh-69 text-info"></i>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="numbers">
                                                    <p class="card-category">Recurring</p>
                                                    <p class="card-title">{{ isset($recurringPayments) ?
                                                        $recurringPayments->count() : 0 }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="card card-stats">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center icon-primary">
                                                    <i class="nc-icon nc-chart-bar-32 text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="numbers">
                                                    <p class="card-category">Payments</p>
                                                    <p class="card-title">{{ isset($payments) ? $payments->total() : 0
                                                        }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recurring Payments -->
                        @if($recurringPayments->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Active Recurring Payments</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Tenant</th>
                                                        <th>Amount</th>
                                                        <th>Frequency</th>
                                                        <th>Payment Day</th>
                                                        <th>Next Payment</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recurringPayments as $recurring)
                                                    <tr>
                                                        <td>{{ $recurring->tenant->first_name }} {{
                                                            $recurring->tenant->last_name }}</td>
                                                        <td>{{ format_money($recurring->amount) }}</td>
                                                        <td><span class="badge badge-info">{{
                                                                ucfirst($recurring->frequency) }}</span></td>
                                                        <td>
                                                            @if($recurring->payment_day_of_month)
                                                            {{ $recurring->payment_day_of_month }}{{
                                                            $recurring->payment_day_of_month == 1 ? 'st' :
                                                            ($recurring->payment_day_of_month == 2 ? 'nd' :
                                                            ($recurring->payment_day_of_month == 3 ? 'rd' : 'th')) }}
                                                            @else
                                                            <span class="text-muted">Auto</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $recurring->next_payment_date->format('M d, Y') }}</td>
                                                        <td>
                                                            @if($recurring->isPaused())
                                                            <span class="badge badge-warning">Paused</span>
                                                            @else
                                                            <span class="badge badge-success">Active</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($recurring->isPaused())
                                                            <form
                                                                action="{{ route('benefactor.payment.resume', $recurring->id) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success"
                                                                    title="Resume Payment">
                                                                    <i class="nc-icon nc-button-play"></i> Resume
                                                                </button>
                                                            </form>
                                                            @else
                                                            <button type="button" class="btn btn-sm btn-warning"
                                                                data-toggle="modal"
                                                                data-target="#pauseModal{{ $recurring->id }}"
                                                                title="Pause Payment">
                                                                <i class="nc-icon nc-button-pause"></i> Pause
                                                            </button>
                                                            @endif

                                                            <form
                                                                action="{{ route('benefactor.payment.cancel', $recurring->id) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                    onclick="return confirm('Are you sure you want to cancel this recurring payment?')"
                                                                    title="Cancel Payment">
                                                                    <i class="nc-icon nc-simple-remove"></i> Cancel
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>

                                                    <!-- Pause Modal -->
                                                    <div class="modal fade" id="pauseModal{{ $recurring->id }}"
                                                        tabindex="-1" role="dialog">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <form
                                                                    action="{{ route('benefactor.payment.pause', $recurring->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Pause Recurring Payment
                                                                        </h5>
                                                                        <button type="button" class="close"
                                                                            data-dismiss="modal">
                                                                            <span>&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p>Are you sure you want to pause this recurring
                                                                            payment for <strong>{{
                                                                                $recurring->tenant->first_name }} {{
                                                                                $recurring->tenant->last_name
                                                                                }}</strong>?</p>
                                                                        <div class="form-group">
                                                                            <label
                                                                                for="reason{{ $recurring->id }}">Reason
                                                                                (Optional)</label>
                                                                            <textarea name="reason"
                                                                                id="reason{{ $recurring->id }}"
                                                                                class="form-control" rows="3"
                                                                                placeholder="Let the tenant know why you're pausing..."></textarea>
                                                                        </div>
                                                                        <p class="text-muted"><small>You can resume this
                                                                                payment anytime from your
                                                                                dashboard.</small></p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-dismiss="modal">Cancel</button>
                                                                        <button type="submit"
                                                                            class="btn btn-warning">Pause
                                                                            Payment</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Paused Payments -->
                        @if($pausedPayments->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-warning">
                                        <h4 class="card-title text-white">Paused Recurring Payments</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Tenant</th>
                                                        <th>Amount</th>
                                                        <th>Frequency</th>
                                                        <th>Paused On</th>
                                                        <th>Reason</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($pausedPayments as $paused)
                                                    <tr>
                                                        <td>{{ $paused->tenant->first_name }} {{
                                                            $paused->tenant->last_name }}</td>
                                                        <td>{{ format_money($paused->amount) }}</td>
                                                        <td><span class="badge badge-info">{{
                                                                ucfirst($paused->frequency) }}</span></td>
                                                        <td>{{ $paused->paused_at->format('M d, Y') }}</td>
                                                        <td>{{ $paused->pause_reason ?? 'No reason provided' }}</td>
                                                        <td>
                                                            <form
                                                                action="{{ route('benefactor.payment.resume', $paused->id) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success">
                                                                    <i class="nc-icon nc-button-play"></i> Resume
                                                                </button>
                                                            </form>

                                                            <form
                                                                action="{{ route('benefactor.payment.cancel', $paused->id) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                    onclick="return confirm('Are you sure you want to cancel this payment?')">
                                                                    <i class="nc-icon nc-simple-remove"></i> Cancel
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Payment History -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Payment History</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Reference</th>
                                                        <th>Tenant</th>
                                                        <th>Amount</th>
                                                        <th>Type</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($payments ?? [] as $payment)
                                                    <tr>
                                                        <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                                        <td>{{ $payment->payment_reference }}</td>
                                                        <td>{{ $payment->tenant->first_name }} {{
                                                            $payment->tenant->last_name }}</td>
                                                        <td>{{ format_money($payment->amount) }}</td>
                                                        <td>
                                                            @if($payment->isRecurring())
                                                            <span class="badge badge-info">Recurring</span>
                                                            @else
                                                            <span class="badge badge-secondary">One-time</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($payment->status === 'completed')
                                                            <span class="badge badge-success">Completed</span>
                                                            @elseif($payment->status === 'pending')
                                                            <span class="badge badge-warning">Pending</span>
                                                            @elseif($payment->status === 'cancelled')
                                                            <span class="badge badge-danger">Cancelled</span>
                                                            @else
                                                            <span class="badge badge-secondary">{{
                                                                ucfirst($payment->status) }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">No payments yet</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-3">
                                            {{ isset($payments) ? $payments->links() : '' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')