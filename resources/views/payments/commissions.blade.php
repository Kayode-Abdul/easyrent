@extends('layout')

@section('content')
<div class="content mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Commission Breakdown for Payment #{{ $payment->transaction_id }}</h4>
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">Back to Payments</a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card card-stats bg-light mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-info">{{ $payment->getFormattedAmount() }}</h5>
                                    <p class="card-category text-muted mb-0">Total Rent Paid</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-stats bg-light mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-warning">{{ format_money($totalPlatformFee, $payment->currency ?? ($payment->apartment->currency ?? null)) }}</h5>
                                    <p class="card-category text-muted mb-0">Platform Fee (2.5%)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-stats bg-light mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-success">{{ format_money($companyRetained, $payment->currency ?? ($payment->apartment->currency ?? null)) }}</h5>
                                    <p class="card-category text-muted mb-0">Company Retained</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3">Distribution Details</h5>
                    @if($commissionPayments->isEmpty())
                        <div class="alert alert-info">
                            No commissions were distributed for this payment. The company retained the full platform fee.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="text-primary">
                                    <tr>
                                        <th>Role / Tier</th>
                                        <th>Recipient</th>
                                        <th>Applied Rate</th>
                                        <th>Amount Received</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($commissionPayments as $commission)
                                    <tr>
                                        <td>
                                            @if($commission->commission_tier == 'super_marketer')
                                                <span class="badge badge-primary">Super Marketer</span>
                                            @elseif($commission->commission_tier == 'marketer')
                                                <span class="badge badge-info">Marketer</span>
                                            @elseif($commission->commission_tier == 'regional_manager')
                                                <span class="badge badge-warning">Regional Manager</span>
                                            @elseif($commission->commission_tier == 'standard_referrer')
                                                <span class="badge badge-secondary">Standard Referrer</span>
                                            @else
                                                <span class="badge badge-light">{{ ucfirst(str_replace('_', ' ', $commission->commission_tier)) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($commission->marketer)
                                                {{ $commission->marketer->first_name }} {{ $commission->marketer->last_name }} ({{ $commission->marketer->email }})
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $commission->regional_rate_applied }}%</td>
                                        <td><strong class="text-success">{{ format_money($commission->total_amount, $payment->currency ?? ($payment->apartment->currency ?? null)) }}</strong></td>
                                        <td>
                                            <span class="badge {{ $commission->payment_status == 'completed' ? 'badge-success' : 'badge-warning' }}">
                                                {{ ucfirst($commission->payment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-light">
                                        <td colspan="3" class="text-right"><strong>Total Distributed to Partners:</strong></td>
                                        <td><strong class="text-success">{{ format_money($totalDistributed, $payment->currency ?? ($payment->apartment->currency ?? null)) }}</strong></td>
                                        <td></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="3" class="text-right"><strong>Company Net Revenue:</strong></td>
                                        <td><strong class="text-success">{{ format_money($companyRetained, $payment->currency ?? ($payment->apartment->currency ?? null)) }}</strong></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
