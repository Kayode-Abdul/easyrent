@extends('layout')
@section('content')

<div class="content">
    <!-- Dashboard Mode Controls -->
    <div class="container-fluid mb-3">
        <div class="row">
            @if(in_array(auth()->user()->role, [6, 8]))
            <!-- Property Manager Tabs (only for PMs) -->
            <div class="col-md-6">
                <ul class="nav nav-tabs pm-tabs" id="pmTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab"
                            aria-controls="personal" aria-selected="true">
                            <i class="nc-icon nc-single-02"></i> Personal
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="property-manager-tab" data-toggle="tab" href="#property-manager"
                            role="tab" aria-controls="property-manager" aria-selected="false">
                            <i class="nc-icon nc-settings-gear-65"></i> Property Manager
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Landlord/Tenant Toggle (for everyone) -->
            <div class="col-md-6 d-flex justify-content-end align-items-end">
                @else
                <div class="col-md-12 d-flex justify-content-end align-items-end">
                    @endif

                    <div>
                        <span class="switch-label-left">Landlord</span>
                        <label class="switch mb-0">
                            <input type="checkbox" id="dashboardSwitch" {{ $mode=='tenant' ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                        <span class="switch-label">Tenant</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript for toggles - always load -->
        <script>
            $(function () {
                // Debug: Check if elements exist
                console.log('jQuery loaded:', typeof $ !== 'undefined');
                console.log('Dashboard switch element found:', $('#dashboardSwitch').length);
                console.log('PM switch element found:', $('#propertyManagerSwitch').length);
                console.log('Current mode from toggle:', $('#dashboardSwitch').is(':checked') ? 'tenant' : 'landlord');
                // Landlord/Tenant Toggle (works for everyone)
                $('#dashboardSwitch').on('change', function () {
                    var mode = this.checked ? 'tenant' : 'landlord';
                    var $switch = $(this);
                    $switch.prop('disabled', true); // Prevent double clicks

                    console.log('Toggle clicked, switching to mode:', mode);
                    console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));

                    $.ajax({
                        url: '/dashboard/switch-mode',
                        method: 'POST',
                        data: { mode: mode },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (res) {
                            console.log('Success response:', res);
                            if (res.success) {
                                location.reload();
                            } else {
                                $switch.prop('disabled', false);
                                alert('Failed to switch mode: ' + (res.message || 'Unknown error'));
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log('Error response:', xhr.responseText);
                            console.log('Status:', status, 'Error:', error);
                            $switch.prop('disabled', false);
                            alert('Error switching mode. Please check console for details.');
                        }
                    });
                });

                // Property Manager Tabs (only for PMs)
                $('#property-manager-tab').on('click', function (e) {
                    e.preventDefault();

                    $.ajax({
                        url: '/dashboard/switch-property-manager-mode',
                        method: 'POST',
                        data: { mode: 'property_manager' },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (res) {
                            if (res.success) {
                                window.location.href = '/property-manager/dashboard';
                            } else {
                                alert('Failed to switch to Property Manager mode: ' + (res.message || 'Unknown error'));
                            }
                        },
                        error: function () {
                            alert('Error switching to Property Manager mode. Please try again.');
                        }
                    });
                });

                // Personal tab is already active, no action needed for personal tab click
            });

            // Tenant action functions
            function makePayment(apartmentId) {
                alert('Payment functionality for apartment ' + apartmentId + ' - Feature coming soon!');
                // TODO: Redirect to payment page or open payment modal
            }

            function viewPaymentHistory(apartmentId) {
                alert('Payment history for apartment ' + apartmentId + ' - Feature coming soon!');
                // TODO: Open payment history modal or redirect to payments page
            }

            function contactLandlord(email) {
                if (email && email !== 'undefined') {
                    window.location.href = 'mailto:' + email;
                } else {
                    alert('Landlord email not available');
                }
            }
        </script>

        @if($mode === 'landlord')
        <div class="row">
            <!-- Statistics Cards -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-bank text-warning"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Properties</p>
                                    <p class="card-title">{{ $myProperties->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-building"></i> Total Properties
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-shop text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Apartments</p>
                                    <p class="card-title">{{ $myApartment->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-home"></i> Total Apartments
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-money-coins text-danger"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Active Leases</p>
                                    <p class="card-title">{{ $myApartment->where('range_end', '>', now())->count() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-calendar"></i> Currently Active
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-single-02 text-primary"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Tenants</p>
                                    <p class="card-title">{{ $myApartment->unique('tenant_id')->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-users"></i> Unique Tenants
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Managed Properties Card (for Property Managers in PM mode only) -->
        @if(in_array(auth()->user()->role, [6, 8]) && session('dashboard_mode', 'property_manager') ===
        'property_manager')
        @php
        $managedProperties = \App\Models\Property::where('agent_id', auth()->user()->user_id)
        ->with(['owner', 'apartments'])
        ->limit(5)
        ->get();
        $totalManagedProperties = \App\Models\Property::where('agent_id', auth()->user()->user_id)->count();
        @endphp

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title">
                                    <i class="nc-icon nc-settings-gear-65 text-primary"></i>
                                    Managed Properties
                                </h4>
                                <p class="card-category">Properties assigned to you for management</p>
                            </div>
                            <div>
                                <span class="badge badge-primary badge-lg">{{ $totalManagedProperties }} Total</span>
                                <a href="{{ route('property-manager.dashboard') }}" class="btn btn-primary btn-sm ml-2">
                                    <i class="fa fa-tachometer-alt"></i> Manager Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($managedProperties->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="nc-icon nc-settings-gear-65" style="font-size: 48px; opacity: 0.3;"></i>
                            <h5>No Properties Assigned</h5>
                            <p>You don't have any properties assigned to manage yet.</p>
                            <p class="text-muted">Contact your administrator to get properties assigned to you for
                                management.</p>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-primary">
                                    <tr>
                                        <th>Property ID</th>
                                        <th>Address</th>
                                        <th>Owner</th>
                                        <th>Apartments</th>
                                        <th>Occupancy</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($managedProperties as $property)
                                    @php
                                    $totalApartments = $property->apartments->count();
                                    $occupiedApartments = $property->apartments->where('occupied', true)->count();
                                    $occupancyRate = $totalApartments > 0 ? round(($occupiedApartments /
                                    $totalApartments) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold text-primary">{{ $property->property_id
                                                }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $property->address }}</strong><br>
                                                <small class="text-muted">{{ $property->lga }}, {{ $property->state
                                                    }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($property->owner)
                                            <div>
                                                <strong>{{ $property->owner->first_name }} {{
                                                    $property->owner->last_name }}</strong><br>
                                                <small class="text-muted">{{ $property->owner->email }}</small>
                                            </div>
                                            @else
                                            <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $totalApartments }} Units</span>
                                            @if($occupiedApartments > 0)
                                            <br><span class="badge badge-success">{{ $occupiedApartments }}
                                                Occupied</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px; min-width: 80px;">
                                                <div class="progress-bar bg-{{ $occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger') }}"
                                                    role="progressbar" style="width: {{ $occupancyRate }}%"
                                                    aria-valuenow="{{ $occupancyRate }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    {{ $occupancyRate }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('property-manager.property-details', $property->property_id) }}"
                                                    class="btn btn-info btn-sm" title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('property-manager.property-apartments', $property->property_id) }}"
                                                    class="btn btn-success btn-sm" title="View Apartments">
                                                    <i class="fa fa-home"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($totalManagedProperties > 5)
                        <div class="text-center mt-3">
                            <a href="{{ route('property-manager.managed-properties') }}"
                                class="btn btn-outline-primary">
                                <i class="fa fa-list"></i> View All {{ $totalManagedProperties }} Managed Properties
                            </a>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Commission Transparency Section -->
        @if(isset($commissionData) && $commissionData['transparency_enabled'])
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title">Commission Transparency</h4>
                                <p class="card-category">View commission deductions from your rental income</p>
                            </div>
                            <div>
                                <button class="btn btn-info btn-sm" data-toggle="modal"
                                    data-target="#commissionRatesModal">
                                    <i class="fa fa-info-circle"></i> Current Rates
                                </button>
                                <button class="btn btn-warning btn-sm" onclick="loadCommissionNotifications()">
                                    <i class="fa fa-bell"></i> Rate Changes
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(!empty($commissionData['recent_payments']))
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-primary">
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Property</th>
                                        <th>Gross Amount</th>
                                        <th>Commission</th>
                                        <th>Net Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($commissionData['recent_payments'] as $payment)
                                    @php
                                    $breakdown = $commissionData['commission_breakdown'][$payment->id] ?? null;
                                    $commission = $breakdown ? $breakdown['total_commission'] : 0;
                                    $netAmount = $payment->amount - $commission;
                                    @endphp
                                    <tr>
                                        <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $payment->property?->address ?? 'N/A' }}</td>
                                        <td>₦{{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            @if($commission > 0)
                                            <span class="text-warning">₦{{ number_format($commission, 2) }}</span>
                                            <small class="text-muted">({{ number_format(($commission / $payment->amount)
                                                * 100, 2) }}%)</small>
                                            @else
                                            <span class="text-muted">₦0.00</span>
                                            @endif
                                        </td>
                                        <td class="text-success">₦{{ number_format($netAmount, 2) }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-info"
                                                onclick="viewCommissionDetails(<?= $payment->id ?>)"
                                                title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('landlord.commission-transparency') }}" class="btn btn-primary">
                                <i class="fa fa-chart-pie"></i> View Detailed Commission Report
                            </a>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <h5>No commission data available</h5>
                            <p>Commission transparency will be shown here once you have rental payments with referral
                                commissions.</p>
                            <a href="{{ route('landlord.commission-transparency') }}" class="btn btn-info">
                                <i class="fa fa-chart-pie"></i> View Commission Dashboard
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- Properties Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title">My Properties</h4>
                                <p class="card-category">List of all your properties</p>
                            </div>
                            <a href="#" class="btn btn-primary btn-round" data-toggle="modal"
                                data-target="#addPropertyModal">
                                <i class="fa fa-plus"></i> Add New Property
                            </a>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="Search with ID, Type, Address, Location,  ">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($myProperties->isEmpty())
                        <div class="alert alert-info">
                            <h5>No properties found</h5>
                            <p>Start by adding your first property!</p>
                            <a href="#" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#addPropertyModal">Add Property</a>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-primary">
                                    <tr>
                                        <th>Photo</th>
                                        <th>Address</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Manager</th>
                                        <th>Apartments</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myProperties as $property)
                                    <tr>
                                        <td>
                                            <div class="rounded-lg overflow-hidden border border-slate-200 shadow-sm"
                                                style="width: 50px; height: 50px;">
                                                @if($property->mainImage)
                                                <img src="{{ asset('storage/' . $property->mainImage->file_path) }}"
                                                    class="w-full h-full object-cover" alt="Property">
                                                @else
                                                <div
                                                    class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300">
                                                    <i class="bi bi-image" style="font-size: 20px;"></i>
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $property->address }}</td>
                                        <td>
                                            <span class="badge badge-primary">
                                                {{ $property->getPropertyTypeName() }}
                                            </span>
                                        </td>
                                        <td>{{ $property->lga }}, {{ $property->state }}</td>
                                        <td>
                                            @if($property->agent_id)
                                            <span class="badge badge-success"> Property Manager </span>
                                            @else
                                            <span class="badge badge-secondary">Self</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $property->apartments->count() }} Units
                                            </span>
                                            @if($property->apartments->where('range_end', '>', now())->count() > 0)
                                            <span class="badge badge-success">
                                                {{ $property->apartments->where('range_end', '>', now())->count() }}
                                                Active
                                            </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ url('/dashboard/property/'.$property->property_id) }}"
                                                    class="btn btn-info btn-sm" title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                @if(auth()->user()->user_id == $property->user_id)
                                                <a href="{{ url('/dashboard/property/'.$property->property_id.'/edit') }}"
                                                    class="btn btn-warning btn-sm" title="Edit Property">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    title="Delete Property"
                                                    onclick="confirmDelete('{{ $property->property_id }}')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                                @elseif(auth()->user()->admin)
                                                <a href="{{ url('/dashboard/property/'.$property->property_id.'/edit') }}"
                                                    class="btn btn-warning btn-sm" title="Admin Edit Property">
                                                    <i class="fa fa-edit"></i> Admin
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    title="Admin Delete Property"
                                                    onclick="confirmDelete('{{ $property->property_id }}')">
                                                    <i class="fa fa-trash"></i> Admin
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Add pagination links -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $myProperties->links() }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @elseif($mode === 'tenant')
        <div class="row">
            <!-- Tenant Statistics Cards -->
            @php
            $totalRentals = $myApartment->count();
            $activeRentals = $myApartment->where('range_end', '>', now())->count();
            $expiredRentals = $myApartment->where('range_end', '<=', now())->count();

                // Get payment statistics for tenant
                $tenantPayments = \App\Models\Payment::where('tenant_id', auth()->user()->user_id)->get();
                $totalPaid = $tenantPayments->where('status', 'completed')->sum('amount');
                $pendingPayments = $tenantPayments->where('status', 'pending')->count();
                $overduePayments = $tenantPayments->where('status', 'pending')->where('due_date', '<', now())->count();
                    $upcomingPayments = $tenantPayments->where('status', 'pending')->where('due_date', '>=',
                    now())->where('due_date', '<=', now()->addDays(7))->count();
                        @endphp

                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5 col-md-4">
                                            <div class="icon-big text-center icon-warning">
                                                <i class="nc-icon nc-settings-gear-65 text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-md-8">
                                            <div class="numbers">
                                                <p class="card-category">Active Rentals</p>
                                                <p class="card-title">{{ $activeRentals }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <hr>
                                    <div class="stats">
                                        <i class="fa fa-home"></i> {{ $totalRentals }} Total Rentals
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5 col-md-4">
                                            <div class="icon-big text-center icon-warning">
                                                <i class="nc-icon nc-money-coins text-success"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-md-8">
                                            <div class="numbers">
                                                <p class="card-category">Total Paid</p>
                                                <p class="card-title">₦{{ number_format($totalPaid, 0) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <hr>
                                    <div class="stats">
                                        <i class="fa fa-check-circle"></i> Completed Payments
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5 col-md-4">
                                            <div class="icon-big text-center icon-warning">
                                                <i class="nc-icon nc-alert-circle-i text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-md-8">
                                            <div class="numbers">
                                                <p class="card-category">Overdue</p>
                                                <p class="card-title">{{ $overduePayments }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <hr>
                                    <div class="stats">
                                        <i class="fa fa-exclamation-triangle"></i> Payments Overdue
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5 col-md-4">
                                            <div class="icon-big text-center icon-warning">
                                                <i class="nc-icon nc-time-alarm text-warning"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-md-8">
                                            <div class="numbers">
                                                <p class="card-category">Due Soon</p>
                                                <p class="card-title">{{ $upcomingPayments }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <hr>
                                    <div class="stats">
                                        <i class="fa fa-calendar"></i> Next 7 Days
                                    </div>
                                </div>
                            </div>
                        </div>
        </div>

        <!-- Tenancy Table -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">My Tenancies</h4>
                            <p class="card-category">Apartments you are renting</p>
                        </div>
                    </div>
                    <div class="row mb-4 mt-3">
                        <div class="col-md-6">
                            <form action="{{ route('dashboard.myproperty') }}" method="GET">
                                <input type="hidden" name="mode" value="tenant">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        value="{{ request('search') }}"
                                        placeholder="Search with ID, Type, Address, Location...">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary m-0"
                                            style="border-radius: 0 4px 4px 0;">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($myApartment->isEmpty())
                    <div class="alert alert-info">
                        <h5>No tenancies found</h5>
                        <p>You are not currently renting any apartments.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="text-primary">
                                <tr>
                                    <th>Apartment</th>
                                    <th>Property Address</th>
                                    <th>Rent Amount</th>
                                    <th>Lease Period</th>
                                    <th>Payment Status</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myApartment as $apartment)
                                @php
                                $latestPayment = \App\Models\Payment::where('tenant_id', auth()->user()->user_id)
                                ->where('apartment_id', $apartment->apartment_id)
                                ->orderBy('created_at', 'desc')
                                ->first();

                                $overduePayment = \App\Models\Payment::where('tenant_id', auth()->user()->user_id)
                                ->where('apartment_id', $apartment->apartment_id)
                                ->where('status', 'pending')
                                ->where('due_date', '<', now()) ->exists();

                                    $upcomingPayment = \App\Models\Payment::where('tenant_id', auth()->user()->user_id)
                                    ->where('apartment_id', $apartment->apartment_id)
                                    ->where('status', 'pending')
                                    ->where('due_date', '>=', now())
                                    ->where('due_date', '<=', now()->addDays(7))
                                        ->first();
                                        @endphp
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $apartment->apartment_id }}</strong><br>
                                                    <small class="text-muted">{{ $apartment->apartment_type ?? 'N/A'
                                                        }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $apartment->property->address ?? '-' }}</td>
                                            <td>
                                                <strong>₦{{ number_format($apartment->amount ?? 0, 2) }}</strong><br>
                                                <small class="text-muted">Monthly</small>
                                            </td>
                                            <td>
                                                @if($apartment->range_start && $apartment->range_end)
                                                <div>
                                                    <strong>{{ $apartment->range_start->format('M d, Y') }}</strong><br>
                                                    <small class="text-muted">to {{ $apartment->range_end->format('M d,
                                                        Y') }}</small>
                                                </div>
                                                @else
                                                <span class="text-muted">No lease period</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($overduePayment)
                                                <span class="badge badge-danger">Overdue</span>
                                                @elseif($upcomingPayment)
                                                <span class="badge badge-warning">Due {{
                                                    $upcomingPayment->due_date->format('M d') }}</span>
                                                @elseif($latestPayment && $latestPayment->status === 'completed')
                                                <span class="badge badge-success">Paid</span>
                                                @elseif($latestPayment && $latestPayment->status === 'pending')
                                                <span class="badge badge-info">Pending</span>
                                                @else
                                                <span class="badge badge-secondary">No Payments</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($apartment->range_end && $apartment->range_end > now())
                                                <span class="badge badge-success">Active</span>
                                                @if($apartment->range_end <= now()->addDays(30))
                                                    <br><small class="text-warning">Expires {{
                                                        $apartment->range_end->diffForHumans() }}</small>
                                                    @endif
                                                    @else
                                                    <span class="badge badge-secondary">Expired</span>
                                                    @endif
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical">
                                                    @if($overduePayment || $upcomingPayment)
                                                    <button class="btn btn-primary btn-sm mb-1"
                                                        onclick="makePayment('{{ $apartment->apartment_id }}')">
                                                        <i class="fa fa-credit-card"></i> Pay
                                                    </button>
                                                    @endif
                                                    <button class="btn btn-info btn-sm mb-1"
                                                        onclick="viewPaymentHistory('{{ $apartment->apartment_id }}')">
                                                        <i class="fa fa-history"></i> History
                                                    </button>
                                                    @if($apartment->property && $apartment->property->owner)
                                                    <button class="btn btn-success btn-sm"
                                                        onclick="contactLandlord('{{ $apartment->property->owner->email }}')">
                                                        <i class="fa fa-envelope"></i> Contact
                                                    </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                            </tbody>
                        </table>

                        <!-- Add pagination links -->
                        @if(method_exists($myApartment, 'links'))
                        <div class="d-flex justify-content-center mt-4">
                            {{ $myApartment->links() }}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($myProperties->isEmpty() && $myApartment->isEmpty())
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="alert alert-info text-center p-5">
                <h4>Welcome to your Dashboard!</h4>
                <p>You are currently neither a landlord nor a tenant.</p>
                <p>To get started, add your first property or contact a landlord to be assigned as a tenant.</p>
                <a href="#" class="btn btn-primary btn-lg mt-3" data-toggle="modal" data-target="#addPropertyModal">Add
                    Property</a>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- JavaScript for toggles - moved outside conditionals to always load -->
<script>
    $(function () {
        // Debug: Check if elements exist
        console.log('jQuery loaded:', typeof $ !== 'undefined');
        console.log('Dashboard switch element found:', $('#dashboardSwitch').length);
        console.log('PM switch element found:', $('#propertyManagerSwitch').length);
        console.log('Current mode from toggle:', $('#dashboardSwitch').is(':checked') ? 'tenant' : 'landlord');

        // Landlord/Tenant Toggle (works for everyone)
        $('#dashboardSwitch').on('change', function () {
            var mode = this.checked ? 'tenant' : 'landlord';
            var $switch = $(this);
            $switch.prop('disabled', true); // Prevent double clicks

            console.log('Toggle clicked, switching to mode:', mode);
            console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: '/dashboard/switch-mode',
                method: 'POST',
                data: { mode: mode },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    console.log('Success response:', res);
                    if (res.success) {
                        location.reload();
                    } else {
                        $switch.prop('disabled', false);
                        alert('Failed to switch mode: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function (xhr, status, error) {
                    console.log('Error response:', xhr.responseText);
                    console.log('Status:', status, 'Error:', error);
                    $switch.prop('disabled', false);
                    alert('Error switching mode. Please check console for details.');
                }
            });
        });

        // Tenant action functions
        function makePayment(apartmentId) {
            alert('Payment functionality for apartment ' + apartmentId + ' - Feature coming soon!');
            // TODO: Redirect to payment page or open payment modal
        }

        function viewPaymentHistory(apartmentId) {
            alert('Payment history for apartment ' + apartmentId + ' - Feature coming soon!');
            // TODO: Open payment history modal or redirect to payments page
        }

        function contactLandlord(email) {
            if (email && email !== 'undefined') {
                window.location.href = 'mailto:' + email;
            } else {
                alert('Landlord email not available');
            }
        }

        // Make functions global so they can be called from HTML
        window.makePayment = makePayment;
        window.viewPaymentHistory = viewPaymentHistory;
        window.contactLandlord = contactLandlord;
    });
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this item? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Property Modal -->
<div class="modal fade" id="addPropertyModal" tabindex="-1" role="dialog" aria-labelledby="addPropertyModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPropertyModalLabel">Add New Property</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="propertyMessage"></div>
                <form method="post" id="propertyForm" class="p-3">
                    @csrf
                    <div class="form-group">
                        <label for="property-type">Property Type</label>
                        <select name="propertyType" id="property-type" class="form-control" required>
                            <option value="" disabled="disabled" selected>-- Select Property Type --</option>
                            <optgroup label="Residential">
                                <option value="1">Mansion</option>
                                <option value="2">Duplex</option>
                                <option value="3">Flat</option>
                                <option value="4">Terrace</option>
                            </optgroup>
                            <optgroup label="Commercial">
                                <option value="5">Warehouse</option>
                                <option value="8">Store</option>
                                <option value="9">Shop</option>
                            </optgroup>
                            <optgroup label="Land/Agricultural">
                                <option value="6">Land</option>
                                <option value="7">Farm</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select name="country" id="country" class="form-control" onchange="getStatesForModal()"
                            required>
                            <option value="" disabled>Select Country</option>
                            @foreach ($countries as $c)
                            <option value="{{ $c['name'] }}" {{ $c['name']==='Nigeria' ? 'selected' : '' }}>{{
                                $c['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="states">State</label>
                        <select name="state" id="states" class="form-control" onchange="getCities()">
                            <option value="" disabled="disabled" selected>Select State</option>
                            @foreach ($locations as $location)
                            <option value="{{ $location['name'] }}">{{ $location['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cities" id="cityLabel">L.G.A</label>
                        <select name="city" id="cities" class="form-control">
                            <option value="" disabled="disabled" selected>Select L.G.A</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="propertyAdd">Property Address</label>
                        <textarea class="form-control" name="address" id="propertyAdd" rows="3"
                            placeholder="Enter full property address"></textarea>
                    </div>

                    <!-- Number of Apartments (for residential properties only) -->
                    <div class="form-group" id="apartments-field-modal" style="display: none;">
                        <label for="noOfApartment_modal">Number of Units/Apartments *</label>
                        <input type="number" class="form-control" name="noOfApartment" id="noOfApartment_modal" min="1"
                            placeholder="Enter number of units/apartments">
                        <small class="form-text text-muted">Number of rentable units in this property</small>
                    </div>

                    <!-- Size Fields (for commercial and land properties only) -->
                    <div class="form-group" id="size-fields-modal" style="display: none;">
                        <label for="size_value_modal">Property Size *</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="number" name="size_value" id="size_value_modal" class="form-control"
                                    placeholder="Enter size" step="0.01" min="0">
                            </div>
                            <div class="col-md-6">
                                <select name="size_unit" id="size_unit_modal" class="form-control">
                                    <option value="sqm">Square Meters (sqm)</option>
                                    <option value="sqft">Square Feet (sqft)</option>
                                    <option value="acres">Acres</option>
                                    <option value="hectares">Hectares</option>
                                </select>
                            </div>
                        </div>
                        <small class="form-text text-muted">Required for commercial and land properties</small>
                    </div>

                    <!-- Warehouse-specific fields -->
                    <div id="warehouse-fields-modal" style="display: none;">
                        <h6 class="mt-3 mb-2">Warehouse Details</h6>
                        <div class="form-group">
                            <label for="height_clearance_modal">Height Clearance (meters)</label>
                            <input type="number" name="height_clearance" id="height_clearance_modal"
                                class="form-control" placeholder="e.g., 8" step="0.1">
                        </div>
                        <div class="form-group">
                            <label for="loading_docks_modal">Number of Loading Docks</label>
                            <input type="number" name="loading_docks" id="loading_docks_modal" class="form-control"
                                placeholder="e.g., 3" min="0">
                        </div>
                        <div class="form-group">
                            <label for="storage_type_modal">Storage Type</label>
                            <select name="storage_type" id="storage_type_modal" class="form-control">
                                <option value="">-- Select Storage Type --</option>
                                <option value="dry_storage">Dry Storage</option>
                                <option value="cold_storage">Cold Storage</option>
                                <option value="hazmat">Hazardous Materials</option>
                                <option value="general">General Storage</option>
                            </select>
                        </div>
                    </div>

                    <!-- Land/Farm-specific fields -->
                    <div id="land-fields-modal" style="display: none;">
                        <h6 class="mt-3 mb-2">Land/Farm Details</h6>
                        <div class="form-group">
                            <label for="land_type_modal">Land Type</label>
                            <select name="land_type" id="land_type_modal" class="form-control">
                                <option value="">-- Select Land Type --</option>
                                <option value="agricultural">Agricultural</option>
                                <option value="residential">Residential</option>
                                <option value="commercial">Commercial</option>
                                <option value="mixed">Mixed Use</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="soil_type_modal">Soil Type</label>
                            <input type="text" name="soil_type" id="soil_type_modal" class="form-control"
                                placeholder="e.g., loamy, sandy, clay">
                        </div>
                        <div class="form-group">
                            <label for="water_access_modal">Water Access</label>
                            <select name="water_access" id="water_access_modal" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="water_source_modal">Water Source (if applicable)</label>
                            <input type="text" name="water_source" id="water_source_modal" class="form-control"
                                placeholder="e.g., borehole, river, well">
                        </div>
                        <div class="form-group">
                            <label for="topography_modal">Topography</label>
                            <select name="topography" id="topography_modal" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="flat">Flat</option>
                                <option value="hilly">Hilly</option>
                                <option value="sloped">Sloped</option>
                            </select>
                        </div>
                    </div>

                    <!-- Store/Shop-specific fields -->
                    <div id="store-fields-modal" style="display: none;">
                        <h6 class="mt-3 mb-2">Store/Shop Details</h6>
                        <div class="form-group">
                            <label for="frontage_width_modal">Frontage Width (meters)</label>
                            <input type="number" name="frontage_width" id="frontage_width_modal" class="form-control"
                                placeholder="e.g., 6" step="0.1">
                        </div>
                        <div class="form-group">
                            <label for="store_type_modal">Store Type</label>
                            <select name="store_type" id="store_type_modal" class="form-control">
                                <option value="">-- Select Store Type --</option>
                                <option value="retail">Retail</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="office">Office</option>
                                <option value="salon">Salon/Spa</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="supermarket">Supermarket</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="foot_traffic_modal">Foot Traffic Level</label>
                            <select name="foot_traffic" id="foot_traffic_modal" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="parking_spaces_modal">Parking Spaces</label>
                            <input type="number" name="parking_spaces" id="parking_spaces_modal" class="form-control"
                                placeholder="Number of parking spaces" min="0">
                        </div>
                    </div>
                </form>

                <!-- Apartment Section -->
                <!-- <div id="apartmentSection" style="display: none;">
                    <hr>
                    <h5>Add Apartments</h5>
                    <div id="apartmentMessage"></div>
                    <form id="apartmentForm" class="p-3">
                        @csrf
                        <input type="hidden" id="property-id" name="propertyId">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Apartment/Unit Type</label>
                                <select class="form-control" name="apartmentType" required>
                                    <option value="" disabled selected>-- Select Type --</option>
                                    <optgroup label="Residential Units">
                                        <option value="Studio">Studio</option>
                                        <option value="1 Bedroom">1 Bedroom</option>
                                        <option value="2 Bedroom">2 Bedroom</option>
                                        <option value="3 Bedroom">3 Bedroom</option>
                                        <option value="4 Bedroom">4 Bedroom</option>
                                        <option value="Penthouse">Penthouse</option>
                                        <option value="Duplex Unit">Duplex Unit</option>
                                    </optgroup>
                                    <optgroup label="Commercial Units">
                                        <option value="Shop Unit">Shop Unit</option>
                                        <option value="Store Unit">Store Unit</option>
                                        <option value="Office Unit">Office Unit</option>
                                        <option value="Restaurant Unit">Restaurant Unit</option>
                                        <option value="Warehouse Unit">Warehouse Unit</option>
                                        <option value="Showroom">Showroom</option>
                                    </optgroup>
                                    <optgroup label="Other">
                                        <option value="Storage Unit">Storage Unit</option>
                                        <option value="Parking Space">Parking Space</option>
                                        <option value="Other">Other</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Unit Number</th>
                                        <th>Rent Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" onclick="addApartmentRow()">
                            <i class="fa fa-plus"></i> Add Apartment
                        </button>
                    </form>
                </div> -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveProperty">Save Property</button>
                <button type="button" class="btn btn-success" id="saveApartments" style="display: none;">Save
                    Apartments</button>
            </div>
        </div>
    </div>
</div>
<!-- Add this before the closing body tag -->
<script src="{{ asset('assets/js/apartment-functions.js') }}"></script>
<script>
    function confirmDelete(propId) {
        if (confirm('Are you sure you want to delete this property? This will also delete all associated apartments.')) {
            fetch(`/dashboard/property/${propId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(function (data) {
                    if (data.success) {
                        alert(data.messages);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.messages);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete property. Please try again.');
                });
        }
    }

    function confirmDeleteApartment(apartmentId) {
        if (confirm('Are you sure you want to delete this apartment?')) {
            fetch(`/dashboard/apartment/${apartmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(function (data) {
                    if (data.success) {
                        alert(data.messages);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.messages);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete apartment. Please try again.');
                });
        }
    }

    function filterApartments(status) {
        const rows = document.querySelectorAll('#apartmentsTable tbody tr');
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }


    let cachedModalLocationData = null;

    function getStatesForModal() {
        const country = document.getElementById('country').value;
        const stateSelect = document.getElementById('states');
        const citySelect = document.getElementById('cities');
        const cityLabel = document.getElementById('cityLabel');

        if (cityLabel) {
            cityLabel.textContent = (country === 'Nigeria') ? 'L.G.A' : 'City';
        }

        stateSelect.innerHTML = '<option value="" disabled selected>Select State</option>';
        citySelect.innerHTML = '<option value="" disabled selected>Select ' + (country === 'Nigeria' ? 'L.G.A' : 'City') + '</option>';

        if (!country) return;

        fetch('/api/location-data?country=' + encodeURIComponent(country))
            .then(r => r.json())
            .then(data => {
                cachedModalLocationData = data.states || [];
                cachedModalLocationData.forEach(function (state) {
                    const opt = document.createElement('option');
                    opt.value = state.name;
                    opt.textContent = state.name;
                    stateSelect.appendChild(opt);
                });
            });
    }

    function getCities() {
        const stateSelect = document.getElementById("states");
        const citySelect = document.getElementById("cities");
        const country = document.getElementById('country').value;
        const selectedState = stateSelect.value;

        citySelect.innerHTML = '<option value="" disabled selected>Select ' + (country === 'Nigeria' ? 'L.G.A' : 'City') + '</option>';

        if (!selectedState || !cachedModalLocationData) return;

        const found = cachedModalLocationData.find(s => s.name === selectedState);
        if (found && found.cities) {
            found.cities.forEach(function (city) {
                const option = document.createElement("option");
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
        }
    }

    // Use vanilla JavaScript for DOM manipulation
    document.addEventListener('DOMContentLoaded', function () {
        // Handle modal reset
        const addPropertyModal = document.getElementById('addPropertyModal');
        if (addPropertyModal) {
            addPropertyModal.addEventListener('hidden.bs.modal', function () {
                var pf = document.getElementById('propertyForm');
                if (pf) pf.reset();
                var af = document.getElementById('apartmentForm');
                if (af) af.reset();
                var as = document.getElementById('apartmentSection');
                if (as) as.style.display = 'none';
                var sp = document.getElementById('saveProperty');
                if (sp) sp.style.display = 'inline-block';
                var sa = document.getElementById('saveApartments');
                if (sa) sa.style.display = 'none';
                var pm = document.getElementById('propertyMessage');
                if (pm) pm.innerHTML = '';
                var am = document.getElementById('apartmentMessage');
                if (am) am.innerHTML = '';
            });
        }
    });

</script>
<!-- Commission Rates Modal -->
<div class="modal fade" id="commissionRatesModal" tabindex="-1" role="dialog"
    aria-labelledby="commissionRatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commissionRatesModalLabel">Current Commission Rates</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(isset($commissionData) && !empty($commissionData['current_rates']))
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Commission Rate</th>
                                <th>Effective From</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commissionData['current_rates'] as $roleId => $rate)
                            <tr>
                                <td>
                                    @php
                                    $roleNames = [
                                    5 => 'Marketer',
                                    6 => 'Regional Manager',
                                    9 => 'Super Marketer'
                                    ];
                                    @endphp
                                    {{ $roleNames[$roleId] ?? "Role {$roleId}" }}
                                </td>
                                <td>{{ $rate->commission_percentage }}%</td>
                                <td>{{ $rate->effective_from->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info">
                    <strong>Region:</strong> {{ $commissionData['landlord_region'] ?? 'Default' }}
                </div>
                @else
                <div class="alert alert-info">
                    No commission rates configured for your region.
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('components.commission-breakdown-modal')

<!-- Commission Notifications Modal -->
<div class="modal fade" id="commissionNotificationsModal" tabindex="-1" role="dialog"
    aria-labelledby="commissionNotificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commissionNotificationsModalLabel">Recent Commission Rate Changes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="commissionNotificationsContent">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Commission transparency functions
    function viewCommissionDetails(paymentId) {
        $('#commissionDetailsModal').modal('show');

        $.ajax({
            url: `/dashboard/payment/${paymentId}/commission-details`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Amount:</strong></td><td>₦${parseFloat(response.payment.amount).toLocaleString()}</td></tr>
                                <tr><td><strong>Property:</strong></td><td>${response.payment.property_address || 'N/A'}</td></tr>
                                <tr><td><strong>Apartment:</strong></td><td>${response.payment.apartment_type || 'N/A'}</td></tr>
                                <tr><td><strong>Tenant:</strong></td><td>${response.payment.tenant_name || 'N/A'}</td></tr>
                                <tr><td><strong>Date:</strong></td><td>${response.payment.payment_date}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Commission Summary</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Total Commission:</strong></td><td class="text-warning">₦${parseFloat(response.commission_breakdown.total_commission || 0).toLocaleString()}</td></tr>
                                <tr><td><strong>Commission %:</strong></td><td>${parseFloat(response.commission_breakdown.commission_percentage || 0).toFixed(2)}%</td></tr>
                                <tr><td><strong>Net Amount:</strong></td><td class="text-success">₦${parseFloat(response.commission_breakdown.net_amount || response.payment.amount).toLocaleString()}</td></tr>
                            </table>
                        </div>
                    </div>
                `;

                    if (response.commission_breakdown.breakdown && response.commission_breakdown.breakdown.length > 0) {
                        content += `
                        <hr>
                        <h6>Commission Distribution</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tier</th>
                                        <th>Recipient</th>
                                        <th>Amount</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                        response.commission_breakdown.breakdown.forEach(function (item) {
                            content += `
                            <tr>
                                <td>${item.tier.replace('_', ' ').toUpperCase()}</td>
                                <td>${item.recipient ? item.recipient.name : 'N/A'}</td>
                                <td>₦${parseFloat(item.amount).toLocaleString()}</td>
                                <td>${parseFloat(item.percentage).toFixed(2)}%</td>
                            </tr>
                        `;
                        });

                        content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    } else {
                        content += `
                        <hr>
                        <div class="alert alert-info">
                            No commission breakdown available for this payment.
                        </div>
                    `;
                    }

                    $('#commissionDetailsContent').html(content);
                } else {
                    $('#commissionDetailsContent').html('<div class="alert alert-danger">Failed to load commission details.</div>');
                }
            },
            error: function () {
                $('#commissionDetailsContent').html('<div class="alert alert-danger">Error loading commission details.</div>');
            }
        });
    }

    function loadCommissionNotifications() {
        $('#commissionNotificationsModal').modal('show');

        $.ajax({
            url: '/dashboard/commission-notifications',
            method: 'GET',
            success: function (response) {
                if (response.success && response.notifications.length > 0) {
                    let content = '<div class="list-group">';

                    response.notifications.forEach(function (notification) {
                        content += `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Commission Rate Update</h6>
                                <small>${notification.created_at}</small>
                            </div>
                            <p class="mb-1">${notification.message}</p>
                            <small>Effective from: ${notification.effective_from}</small>
                            <br><small>Updated by: ${notification.created_by}</small>
                        </div>
                    `;
                    });

                    content += '</div>';
                    $('#commissionNotificationsContent').html(content);
                } else {
                    $('#commissionNotificationsContent').html('<div class="alert alert-info">No recent commission rate changes in your region.</div>');
                }
            },
            error: function () {
                $('#commissionNotificationsContent').html('<div class="alert alert-danger">Error loading notifications.</div>');
            }
        });
    }

    function toggleOccupied(button) {
        const isOccupied = button.getAttribute('data-occupied') === '1';
        const hiddenInput = button.previousElementSibling;

        if (isOccupied) {
            button.setAttribute('data-occupied', '0');
            button.classList.remove('btn-success');
            button.classList.add('btn-danger');
            button.innerHTML = '<i class="fa fa-times"></i>';
            hiddenInput.value = '0';
        } else {
            button.setAttribute('data-occupied', '1');
            button.classList.remove('btn-danger');
            button.classList.add('btn-success');
            button.innerHTML = '<i class="fa fa-check"></i>';
            hiddenInput.value = '1';
        }
    }

    // Handle property type change to show/hide conditional fields in modal
    document.getElementById('property-type').addEventListener('change', function () {
        const propType = parseInt(this.value);

        // Hide all conditional fields
        document.getElementById('size-fields-modal').style.display = 'none';
        document.getElementById('apartments-field-modal').style.display = 'none';
        document.getElementById('warehouse-fields-modal').style.display = 'none';
        document.getElementById('land-fields-modal').style.display = 'none';
        document.getElementById('store-fields-modal').style.display = 'none';

        // Remove required attributes
        document.getElementById('size_value_modal').removeAttribute('required');
        document.getElementById('noOfApartment_modal').removeAttribute('required');

        // Show relevant fields based on property type
        if (propType >= 1 && propType <= 4) { // Residential (Mansion, Duplex, Flat, Terrace)
            document.getElementById('apartments-field-modal').style.display = 'block';
            document.getElementById('noOfApartment_modal').setAttribute('required', 'required');
        } else if (propType === 5) { // Warehouse
            document.getElementById('size-fields-modal').style.display = 'block';
            document.getElementById('warehouse-fields-modal').style.display = 'block';
            document.getElementById('size_value_modal').setAttribute('required', 'required');
            // Warehouse doesn't need apartments field
        } else if (propType === 6 || propType === 7) { // Land or Farm
            document.getElementById('size-fields-modal').style.display = 'block';
            document.getElementById('land-fields-modal').style.display = 'block';
            document.getElementById('size_value_modal').setAttribute('required', 'required');
            // No apartments field for land/farm
        } else if (propType === 8 || propType === 9) { // Store or Shop
            document.getElementById('size-fields-modal').style.display = 'block';
            document.getElementById('store-fields-modal').style.display = 'block';
            document.getElementById('size_value_modal').setAttribute('required', 'required');
            // Store/Shop doesn't need apartments field
        }
    });

    // Handle property form submission
    document.getElementById('saveProperty').addEventListener('click', function () {


        const form = document.getElementById('propertyForm');
        const formData = new FormData(form);

        fetch('/listing', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => response.json())
            .then(data => {
                console.log("Data: ", data.success ? data.messages : data);
                if (data.success) {
                    document.getElementById('propertyMessage').innerHTML = `
                <div class="alert alert-success">
                    ${data.messages.message}
                </div>
            `;
                    // Close modal and reload table after short delay
                    setTimeout(() => {
                        $('#addPropertyModal').modal('hide');
                        window.location.reload();
                    }, 1200);
                } else {
                    document.getElementById('propertyMessage').innerHTML = `
                <div class="alert alert-danger">
                    ${data.messages.message}
                </div>
            `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('propertyMessage').innerHTML = `
            <div class="alert alert-danger">
                An error occurred while saving the property. Please try again.
            </div>
        `;
            });
    });

    // Handle apartment form submission
    document.getElementById('saveApartments').addEventListener('click', function () {
        const form = document.getElementById('apartmentForm');
        const formData = new FormData(form);

        // Log the form data for debugging
        console.log('Form Data:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        fetch('/apartment', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data); // Log the response
                if (data.success) {
                    document.getElementById('apartmentMessage').innerHTML = `
                <div class="alert alert-success">
                    ${data.messages.message}
                </div>
            `;
                    // Reload the page after successful submission
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    let errorMessage = '';
                    if (typeof data.messages === 'object') {
                        // Handle validation errors
                        for (let field in data.messages) {
                            errorMessage += `${data.messages[field].join('<br>')}<br>`;
                        }
                    } else {
                        errorMessage = data.messages;
                    }
                    document.getElementById('apartmentMessage').innerHTML = `
                <div class="alert alert-danger">
                    ${errorMessage}
                </div>
            `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('apartmentMessage').innerHTML = `
            <div class="alert alert-danger">
                An error occurred while saving the apartments. Please try again.
            </div>
        `;
            });
    });
</script>

<script>
    // Replace the existing search script with this
    $(document).ready(function () {
        $("#searchInput").on("keyup", function () {
            var value = $(this).val().toLowerCase();
            $("table tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });

            // Update visible count
            var visibleRows = $("table tbody tr:visible").length;
            if (visibleRows === 0) {
                if ($("#noResults").length === 0) {
                    $("table").after('<div id="noResults" class="alert alert-info text-center">No matching properties found</div>');
                } else {
                    $("#noResults").show();
                }
            } else {
                $("#noResults").hide();
            }
        });
    });
</script>

<style>
    /* Modern switch toggle */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #007bff;
    }

    input:checked+.slider:before {
        transform: translateX(26px);
    }

    .switch-label {
        margin-left: 12px;
        font-weight: bold;
        vertical-align: middle;
    }

    .switch-label-left {
        margin-right: 12px;
        font-weight: bold;
        vertical-align: middle;
    }

    /* Spacing for multiple toggles */
    .mr-4 {
        margin-right: 1.5rem;
    }

    /* Toggle container styling */
    .d-flex .switch-label-left,
    .d-flex .switch-label {
        font-size: 14px;
        color: #495057;
    }
</style>
@endsection