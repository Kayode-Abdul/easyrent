@extends('layout')

@section('title', 'Regional Marketer Management')

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
                                <i class="fas fa-globe text-primary me-2"></i>
                                Regional Marketer Management
                            </h4>
                            <p class="text-muted mb-0">Assign regional marketers to specific states or cities</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-primary">
                                <i class="fas fa-users"></i> User Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> Please check the form for errors.
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Regional Marketers List -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-users-cog text-primary me-2"></i>
                        Regional Marketers
                    </h5>
                </div>
                <div class="card-body">
                    @if($regionalMarketers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Assigned Region</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($regionalMarketers as $marketer)
                                        <tr>
                                            <td>{{ $marketer->first_name }} {{ $marketer->last_name }}</td>
                                            <td>{{ $marketer->email }}</td>
                                            <td>
                                                @if($marketer->region)
                                                    <span class="badge bg-success">{{ $marketer->region }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Not Assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignRegionModal{{ $marketer->user_id }}">
                                                    <i class="fas fa-map-marker-alt"></i> Assign Region
                                                </button>
                                                
                                                @if($marketer->region)
                                                    <form action="{{ route('regional-marketers.remove', $marketer->user_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this regional assignment?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-unlink"></i> Remove
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        
                                        <!-- Assign Region Modal -->
                                        <div class="modal fade" id="assignRegionModal{{ $marketer->user_id }}" tabindex="-1" aria-labelledby="assignRegionModalLabel{{ $marketer->user_id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="assignRegionModalLabel{{ $marketer->user_id }}">Assign Region to {{ $marketer->first_name }} {{ $marketer->last_name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('regional-marketers.assign', $marketer->user_id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="region" class="form-label">Select Region</label>
                                                                <select class="form-select" name="region" id="region" required>
                                                                    <option value="">-- Select a State or City --</option>
                                                                    <optgroup label="States">
                                                                        @foreach($regions as $key => $value)
                                                                            @if(!str_contains($key, ' - '))
                                                                                <option value="{{ $key }}" {{ $marketer->region === $key ? 'selected' : '' }}>{{ $value }}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </optgroup>
                                                                    <optgroup label="Cities">
                                                                        @foreach($regions as $key => $value)
                                                                            @if(str_contains($key, ' - '))
                                                                                <option value="{{ $key }}" {{ $marketer->region === $key ? 'selected' : '' }}>{{ $value }}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </optgroup>
                                                                </select>
                                                                <small class="text-muted">Select a state to assign the regional marketer to all cities within that state, or select a specific city.</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Assign Region</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No regional marketers found. To create a regional marketer, assign the "Regional Marketer" role to a user in User Management.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Region Overview -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marked-alt text-success me-2"></i>
                        Region Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @php
                            $assignedRegions = $regionalMarketers->pluck('region')->filter()->toArray();
                            
                            // Group marketers by state/region
                            $marketersByState = [];
                            foreach ($marketers as $region => $marketersInRegion) {
                                if (!empty($region)) {
                                    if (str_contains($region, ' - ')) {
                                        // For cities (State - City format), get just the state
                                        $state = explode(' - ', $region)[0];
                                        if (!isset($marketersByState[$state])) {
                                            $marketersByState[$state] = [];
                                        }
                                    } else {
                                        // For states
                                        $state = $region;
                                        if (!isset($marketersByState[$state])) {
                                            $marketersByState[$state] = [];
                                        }
                                    }
                                    
                                    if (!isset($marketersByState[$state]['marketers'])) {
                                        $marketersByState[$state]['marketers'] = 0;
                                    }
                                    
                                    $marketersByState[$state]['marketers'] += $marketersInRegion->count();
                                }
                            }
                            
                            // Sort by state name
                            ksort($marketersByState);
                        @endphp
                        
                        @if(count($marketersByState) > 0)
                            @foreach($marketersByState as $state => $data)
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">{{ $state }}</h5>
                                        <span class="badge bg-primary">{{ $data['marketers'] }} Marketers</span>
                                    </div>
                                    <p class="mb-1">
                                        @php
                                            $stateHasManager = in_array($state, $assignedRegions);
                                        @endphp
                                        
                                        @if($stateHasManager)
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i> 
                                                Managed by: 
                                                @foreach($regionalMarketers as $manager)
                                                    @if($manager->region === $state)
                                                        {{ $manager->first_name }} {{ $manager->last_name }}
                                                        @break
                                                    @endif
                                                @endforeach
                                            </span>
                                        @else
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> No regional marketer assigned
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i> No regions with marketers found.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Cities with regional managers -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-city text-info me-2"></i>
                        Cities with Regional Marketers
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $citiesWithManagers = array_filter($assignedRegions, function($region) {
                            return str_contains($region, ' - ');
                        });
                    @endphp
                    
                    @if(count($citiesWithManagers) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>City</th>
                                        <th>State</th>
                                        <th>Regional Marketer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($citiesWithManagers as $region)
                                        @php
                                            list($state, $city) = explode(' - ', $region);
                                            $manager = $regionalMarketers->firstWhere('region', $region);
                                        @endphp
                                        <tr>
                                            <td>{{ $city }}</td>
                                            <td>{{ $state }}</td>
                                            <td>{{ $manager->first_name }} {{ $manager->last_name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No cities have been assigned regional marketers yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $(".alert").alert('close');
    }, 5000);
});
</script>
@endsection
