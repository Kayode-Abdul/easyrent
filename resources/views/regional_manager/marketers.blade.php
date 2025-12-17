@extends('layouts.admin')

@section('title', 'Marketers in Your Region')

@push('styles')
<style>
    .marketer-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    
    .marketer-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    .marketer-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .info-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        background: #f8f9fa;
        color: #495057;
        margin-right: 8px;
        margin-bottom: 8px;
    }
    
    .info-badge i {
        margin-right: 6px;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<div class="content">
    <!-- Page Header -->
    <div class="page-header-custom mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2">
                    <i class="fa fa-users me-3"></i>Marketers in Your Region
                </h2>
                <p class="mb-0 opacity-90">View and manage marketers operating in your assigned territories</p>
            </div>
            <a href="{{ route('regional.dashboard') }}" class="btn btn-light btn-lg">
                <i class="fa fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card-stats">
                <div class="d-flex card-body">
                    <div class="icon me-3">
                        <i class="fa fa-users"></i>
                    </div>
                    <div>
                        <div class="value">{{ $marketers->total() }}</div>
                        <div class="label">Total Marketers</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-mini-card" style="border-left-color: #51cbce;">
                <div class="d-flex align-items-center">
                    <div class="icon me-3" style="background: linear-gradient(135deg, #51cbce 0%, #3e8189 100%);">
                        <i class="fa fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <div class="value">{{ $scopes->count() }}</div>
                        <div class="label">Your Regions</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-mini-card" style="border-left-color: #f5576c;">
                <div class="d-flex align-items-center">
                    <div class="icon me-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa fa-building"></i>
                    </div>
                    <div>
                        <div class="value">--</div>
                        <div class="label">Properties Referred</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Marketers List -->
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="fa fa-list me-2"></i>Marketers List
            </h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($marketers->count() > 0)
                <div class="row">
                    @foreach($marketers as $marketer)
                        <div class="col-md-6 col-lg-4">
                            <div class="marketer-card">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="marketer-avatar me-3">
                                        {{ strtoupper(substr($marketer->first_name, 0, 1)) }}{{ strtoupper(substr($marketer->last_name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">{{ $marketer->first_name }} {{ $marketer->last_name }}</h5>
                                        <small class="text-muted">ID: {{ $marketer->user_id }}</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="info-badge">
                                        <i class="fa fa-envelope"></i>
                                        {{ $marketer->email }}
                                    </div>
                                    @if($marketer->phone)
                                        <div class="info-badge">
                                            <i class="fa fa-phone"></i>
                                            {{ $marketer->phone }}
                                        </div>
                                    @endif
                                    @if($marketer->state)
                                        <div class="info-badge">
                                            <i class="fa fa-map-marker-alt"></i>
                                            {{ $marketer->state }}{{ $marketer->lga ? ', ' . $marketer->lga : '' }}
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="{{ route('regional.marketer.properties', ['id' => $marketer->user_id]) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fa fa-building me-2"></i>View Referred Properties
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $marketers->links() }}
                </div>
            @else
                <div class="empty-state-modern">
                    <div class="empty-state-icon-modern">
                        <i class="fa fa-users-slash"></i>
                    </div>
                    <h4 class="text-muted mb-3">No Marketers Found</h4>
                    <p class="text-muted mb-4">
                        @if($scopes->count() > 0)
                            There are no marketers operating in your assigned regions yet.
                        @else
                            You don't have any regions assigned. Contact your administrator.
                        @endif
                    </p>
                    <a href="{{ route('regional.dashboard') }}" class="btn btn-primary">
                        <i class="fa fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection