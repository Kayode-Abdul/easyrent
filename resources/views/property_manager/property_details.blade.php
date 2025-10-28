@extends('layout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Property Details - {{ $property->prop_id }}</h4>
                            <p class="card-category">Detailed information about this managed property</p>
                        </div>
                        <div>
                            <a href="{{ route('property-manager.managed-properties') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Properties
                            </a>
                            <a href="{{ route('property-manager.dashboard') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fa fa-tachometer-alt"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Information -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Property Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Property ID:</strong></td>
                                    <td>{{ $property->prop_id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td>{{ $property->address }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td>{{ $property->lga }}, {{ $property->state }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Property Type:</strong></td>
                                    <td>
                                        @php
                                            $types = [1 => 'Mansion', 2 => 'Duplex', 3 => 'Flat', 4 => 'Terrace'];
                                        @endphp
                                        <span class="badge badge-primary">{{ $types[$property->prop_type] ?? 'Other' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($property->status)
                                            <span class="badge badge-{{ $property->status === 'approved' ? 'success' : ($property->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($property->status) }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">No Status</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $property->created_at ? $property->created_at->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Approved:</strong></td>
                                    <td>{{ $property->approved_at ? $property->approved_at->format('M d, Y') : 'Not approved' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Apartments:</strong></td>
                                    <td><span class="badge badge-info">{{ $propertyStats['total_apartments'] }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Occupied:</strong></td>
                                    <td><span class="badge badge-success">{{ $propertyStats['occupied_apartments'] }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Vacant:</strong></td>
                                    <td><span class="badge badge-warning">{{ $propertyStats['vacant_apartments'] }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Property Owner Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Property Owner</h5>
                </div>
                <div class="card-body">
                    @if($property->owner)
                        <div class="text-center mb-3">
                            <i class="nc-icon nc-single-02" style="font-size: 48px; color: #007bff;"></i>
                        </div>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $property->owner->first_name }} {{ $property->owner->last_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>
                                    <a href="mailto:{{ $property->owner->email }}">{{ $property->owner->email }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>
                                    @if($property->owner->phone)
                                        <a href="tel:{{ $property->owner->phone }}">{{ $property->owner->phone }}</a>
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>User ID:</strong></td>
                                <td>{{ $property->owner->user_id }}</td>
                            </tr>
                        </table>
                        <div class="text-center mt-3">
                            <a href="mailto:{{ $property->owner->email }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-envelope"></i> Send Email
                            </a>
                            @if($property->owner->phone)
                                <a href="tel:{{ $property->owner->phone }}" class="btn btn-success btn-sm">
                                    <i class="fa fa-phone"></i> Call
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning text-center">
                            <i class="nc-icon nc-alert-circle-i"></i>
                            <p>Owner information not available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Property Statistics -->
    <div class="row">
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-home-gear text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Apartments</p>
                                <p class="card-title">{{ $propertyStats['total_apartments'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-chart-pie-36 text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Occupancy Rate</p>
                                <p class="card-title">{{ $propertyStats['occupancy_rate'] }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-money-coins text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Monthly Revenue</p>
                                <p class="card-title">₦{{ number_format($propertyStats['monthly_revenue'], 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-chart-bar-32 text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Revenue</p>
                                <p class="card-title">₦{{ number_format($propertyStats['total_revenue'], 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Apartments and Recent Payments -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Apartments Overview</h5>
                        <a href="{{ route('property-manager.property-apartments', $property->prop_id) }}" class="btn btn-info btn-sm">
                            <i class="fa fa-home"></i> View All Apartments
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($property->apartments->isEmpty())
                        <div class="alert alert-info text-center">
                            <h5>No Apartments</h5>
                            <p>This property doesn't have any apartments registered yet.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-primary">
                                    <tr>
                                        <th>Apartment ID</th>
                                        <th>Type</th>
                                        <th>Tenant</th>
                                        <th>Rent Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($property->apartments->take(5) as $apartment)
                                        <tr>
                                            <td>{{ $apartment->apartment_id }}</td>
                                            <td>{{ $apartment->apartment_type ?? 'N/A' }}</td>
                                            <td>
                                                @if($apartment->tenant)
                                                    <div>
                                                        <strong>{{ $apartment->tenant->first_name }} {{ $apartment->tenant->last_name }}</strong><br>
                                                        <small class="text-muted">{{ $apartment->tenant->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Vacant</span>
                                                @endif
                                            </td>
                                            <td>₦{{ number_format($apartment->amount ?? 0, 2) }}</td>
                                            <td>
                                                @if($apartment->occupied)
                                                    <span class="badge badge-success">Occupied</span>
                                                @else
                                                    <span class="badge badge-warning">Vacant</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($property->apartments->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('property-manager.property-apartments', $property->prop_id) }}" class="btn btn-outline-info">
                                    View All {{ $property->apartments->count() }} Apartments
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Recent Payments</h5>
                        <a href="{{ route('property-manager.payments') }}?property_id={{ $property->prop_id }}" class="btn btn-info btn-sm">
                            <i class="fa fa-money-bill"></i> All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($recentPayments->isEmpty())
                        <div class="text-center text-muted">
                            <i class="nc-icon nc-money-coins" style="font-size: 48px; opacity: 0.3;"></i>
                            <p>No recent payments</p>
                        </div>
                    @else
                        @foreach($recentPayments as $payment)
                            <div class="payment-item mb-3 p-2 border-left border-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>₦{{ number_format($payment->amount, 2) }}</strong><br>
                                        <small class="text-muted">
                                            {{ $payment->apartment->apartment_id ?? 'N/A' }}
                                            @if($payment->tenant)
                                                - {{ $payment->tenant->first_name }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span><br>
                                        <small class="text-muted">{{ $payment->created_at->format('M d') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('property-manager.property-apartments', $property->prop_id) }}" class="btn btn-outline-primary btn-block">
                                <i class="nc-icon nc-home-gear"></i><br>
                                Manage Apartments
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('property-manager.payments') }}?property_id={{ $property->prop_id }}" class="btn btn-outline-success btn-block">
                                <i class="nc-icon nc-money-coins"></i><br>
                                View Payments
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="mailto:{{ $property->owner->email ?? '' }}" class="btn btn-outline-info btn-block">
                                <i class="nc-icon nc-email-85"></i><br>
                                Contact Owner
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning btn-block" onclick="alert('Feature coming soon!')">
                                <i class="nc-icon nc-chart-bar-32"></i><br>
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-item {
    background-color: #f8f9fa;
    border-radius: 5px;
}

.border-left {
    border-left: 4px solid !important;
}

.border-success {
    border-left-color: #28a745 !important;
}

.border-warning {
    border-left-color: #ffc107 !important;
}

.border-danger {
    border-left-color: #dc3545 !important;
}
</style>
@endsection