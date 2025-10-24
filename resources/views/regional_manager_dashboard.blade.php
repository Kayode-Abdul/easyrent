@extends('layout')
@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fa fa-map-marked-alt text-primary me-2"></i>
                                Regional Manager Dashboard
                            </h4>
                            <p class="text-muted mb-0">{{ $greeting ?? 'Welcome! Manage your regional operations and performance metrics.' }}</p>
                        </div>
                        <div>
                            <span class="badge bg-primary fs-6">
                                <i class="fa fa-globe"></i> Region: {{ $stats['region'] ?? 'All Regions' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 bg-primary-light">
                            <i class="fa fa-building text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">{{ number_format($stats['total_properties'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">Total Properties</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 bg-success-light">
                            <i class="fa fa-home text-success"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">{{ number_format($stats['total_apartments'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">Total Apartments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 bg-info-light">
                            <i class="fa fa-user-friends text-info"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">{{ number_format($stats['landlords_count'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">Landlords</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 bg-warning-light">
                            <i class="fa fa-dollar-sign text-warning"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h4>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- More dashboard content goes here -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Regional Manager Dashboard Options</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card card-body border">
                                <h5><i class="fa fa-chart-bar text-primary"></i> Analytics</h5>
                                <p>View detailed analytics for your region</p>
                                <a href="{{ route('regional.analytics') ?? '#' }}" class="btn btn-outline-primary">Go to Analytics</a>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card card-body border">
                                <h5><i class="fa fa-building text-success"></i> Properties</h5>
                                <p>Manage properties in your region</p>
                                <a href="{{ route('regional.properties') ?? '#' }}" class="btn btn-outline-success">Go to Properties</a>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card card-body border">
                                <h5><i class="fa fa-bullhorn text-warning"></i> Marketers</h5>
                                <p>Manage marketers in your region</p>
                                <a href="{{ route('regional.marketers') ?? '#' }}" class="btn btn-outline-warning">Go to Marketers</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    @if(isset($recentActivities) && count($recentActivities) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fa fa-bell text-info me-2"></i>Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($recentActivities as $activity)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                    <small>{{ $activity['time'] }}</small>
                                </div>
                                <p class="mb-1">{{ $activity['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection