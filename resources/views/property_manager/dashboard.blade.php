@extends('layout')
@section('content')
<div class="content">
    <!-- Dashboard Mode Tabs -->
    <div class="container-fluid mb-3">
        <div class="row">
            <div class="col-md-6">
                <ul class="nav nav-tabs pm-tabs" id="pmTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="personal-tab" data-toggle="tab" href="#personal" role="tab" aria-controls="personal" aria-selected="false">
                            <i class="nc-icon nc-single-02"></i> Personal
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="property-manager-tab" data-toggle="tab" href="#property-manager" role="tab" aria-controls="property-manager" aria-selected="true">
                            <i class="nc-icon nc-settings-gear-65"></i> Property Manager
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Property Manager Dashboard</h4>
                            <p class="card-category">Overview of your managed properties</p>
                        </div>
                        <div>
                            <a href="{{ route('property-manager.managed-properties') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-building"></i> View All Properties
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-bank text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Managed Properties</p>
                                <p class="card-title">{{ $stats['total_properties'] }}</p>
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

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-home-gear text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Apartments</p>
                                <p class="card-title">{{ $stats['total_apartments'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-home"></i> All Units
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
                                <i class="nc-icon nc-chart-pie-36 text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Occupancy Rate</p>
                                <p class="card-title">{{ $stats['occupancy_rate'] }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-users"></i> {{ $stats['occupied_apartments'] }}/{{ $stats['total_apartments'] }} Occupied
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
                                <i class="nc-icon nc-money-coins text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Monthly Revenue</p>
                                <p class="card-title">₦{{ number_format($stats['monthly_revenue'], 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-calendar"></i> This Month
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Managed Properties Card -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Managed Properties</h4>
                            <p class="card-category">Properties assigned to you for management</p>
                        </div>
                        <a href="{{ route('property-manager.managed-properties') }}" class="btn btn-info btn-sm">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($managedProperties->isEmpty())
                        <div class="alert alert-info text-center">
                            <h5>No Properties Assigned</h5>
                            <p>You don't have any properties assigned to manage yet.</p>
                            <p class="text-muted">Contact your administrator to get properties assigned to you.</p>
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
                                            $occupancyRate = $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ $property->prop_id }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $property->address }}</strong><br>
                                                    <small class="text-muted">{{ $property->lga }}, {{ $property->state }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($property->owner)
                                                    <div>
                                                        <strong>{{ $property->owner->first_name }} {{ $property->owner->last_name }}</strong><br>
                                                        <small class="text-muted">{{ $property->owner->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $totalApartments }} Units</span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-{{ $occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger') }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $occupancyRate }}%"
                                                         aria-valuenow="{{ $occupancyRate }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ $occupancyRate }}%
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $occupiedApartments }}/{{ $totalApartments }} occupied</small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('property-manager.property-details', $property->prop_id) }}" 
                                                       class="btn btn-info btn-sm" title="View Details">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('property-manager.property-apartments', $property->prop_id) }}" 
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
                        
                        @if($managedProperties->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $managedProperties->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Recent Activities</h4>
                    <p class="card-category">Latest updates from your managed properties</p>
                </div>
                <div class="card-body">
                    @if($recentActivities->isEmpty())
                        <div class="text-center text-muted">
                            <i class="nc-icon nc-time-alarm" style="font-size: 48px; opacity: 0.3;"></i>
                            <p>No recent activities</p>
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($recentActivities as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        <i class="{{ $activity['icon'] }} {{ $activity['color'] }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">{{ $activity['title'] }}</h6>
                                        <p class="timeline-description">{{ $activity['description'] }}</p>
                                        <small class="text-muted">{{ $activity['time'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
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
                    <h4 class="card-title">Quick Actions</h4>
                    <p class="card-category">Frequently used management tools</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('property-manager.payments') }}" class="btn btn-outline-primary btn-block">
                                <i class="nc-icon nc-money-coins"></i><br>
                                View Payments
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('property-manager.analytics') }}" class="btn btn-outline-info btn-block">
                                <i class="nc-icon nc-chart-bar-32"></i><br>
                                Analytics
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('property-manager.managed-properties') }}" class="btn btn-outline-success btn-block">
                                <i class="nc-icon nc-bank"></i><br>
                                All Properties
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning btn-block" onclick="alert('Feature coming soon!')">
                                <i class="nc-icon nc-bell-55"></i><br>
                                Notifications
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-marker i {
    font-size: 10px;
}

.timeline-content {
    padding-left: 10px;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-description {
    margin-bottom: 5px;
    font-size: 14px;
}

.progress {
    margin-bottom: 5px;
}

/* Toggle Switch Styles - Match myProperty page */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
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
  -webkit-transition: .4s;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #007bff;
}

input:checked + .slider:before {
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


</style>

<script>
$(function() {
    // Personal tab click handler
    $('#personal-tab').on('click', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '/dashboard/switch-property-manager-mode',
            method: 'POST',
            data: { mode: 'personal' },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if(res.success) {
                    window.location.href = '/dashboard/myproperty';
                } else {
                    alert('Failed to switch to Personal mode: ' + (res.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Error switching to Personal mode. Please try again.');
            }
        });
    });
    
    // Property Manager tab is already active, no action needed for property-manager-tab click
});
</script>
@endsection