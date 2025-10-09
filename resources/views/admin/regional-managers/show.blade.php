@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.regional-managers.index') }}">Regional Managers</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $regionalManager->first_name }} {{ $regionalManager->last_name }}</li>
                        </ol>
                    </nav>
                    <h2>Regional Manager Details</h2>
                </div>
                <div>
                    <a href="{{ route('admin.regional-managers.assign-regions', $regionalManager) }}" 
                       class="btn btn-success">
                        <i class="fas fa-plus"></i> Assign New Regions
                    </a>
                    @if($scopes->count() > 0)
                        <button type="button" class="btn btn-danger" 
                                onclick="confirmRemoveAllScopes({{ $regionalManager->user_id }}, '{{ $regionalManager->first_name }} {{ $regionalManager->last_name }}')">
                            <i class="fas fa-trash"></i> Remove All Assignments
                        </button>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Manager Info -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Manager Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="avatar-lg bg-primary rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <span class="text-white fw-bold fs-3">
                                        {{ strtoupper(substr($regionalManager->first_name, 0, 1)) }}{{ strtoupper(substr($regionalManager->last_name, 0, 1)) }}
                                    </span>
                                </div>
                                <h5 class="mt-2 mb-0">{{ e($regionalManager->first_name) }} {{ e($regionalManager->last_name) }}</h5>
                <p class="text-muted">Regional Manager</p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold">User ID:</td>
                                        <td>{{ $regionalManager->user_id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td>{{ $regionalManager->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Phone:</td>
                                        <td>{{ $regionalManager->phone ?? 'Not provided' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Joined:</td>
                                        <td>{{ $regionalManager->created_at->format('M d, Y') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Assignment Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-primary mb-0">{{ $stats['total_scopes'] }}</h4>
                                        <small class="text-muted">Total Scopes</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-success mb-0">{{ $stats['state_scopes'] }}</h4>
                                        <small class="text-muted">States</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-info mb-0">{{ $stats['lga_scopes'] }}</h4>
                                    <small class="text-muted">LGAs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Regional Assignments -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Regional Assignments</h5>
                            <span class="badge bg-primary">{{ $scopes->count() }} Active Assignments</span>
                        </div>
                        <div class="card-body">
                            @if($scopes->count() > 0)
                                <div class="row">
                                    @foreach($scopes as $scope)
                                        <div class="col-md-6 mb-3">
                                            <div class="card border">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                                                {{ $scope->state }}
                                                            </h6>
                                                            @if($scope->lga)
                                                                <p class="text-muted mb-0">
                                                                    <small><i class="fas fa-location-dot me-1"></i>{{ $scope->lga }}</small>
                                                                </p>
                                                            @else
                                                                <p class="text-success mb-0">
                                                                    <small><i class="fas fa-globe me-1"></i>All LGAs</small>
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                    type="button" data-bs-toggle="dropdown">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @php
                                                                    $matchingRawScope = $rawScopes->first(function($rawScope) use ($scope) {
                                                                        if ($scope->lga) {
                                                                            return $rawScope->scope_type === 'lga' && 
                                                                                   $rawScope->scope_value === $scope->state . '::' . $scope->lga;
                                                                        } else {
                                                                            return $rawScope->scope_type === 'state' && 
                                                                                   $rawScope->scope_value === $scope->state;
                                                                        }
                                                                    });
                                                                @endphp
                                                                @if($matchingRawScope)
                                                                    <li>
                                                                        <button type="button" class="dropdown-item text-danger" 
                                                                                onclick="confirmRemoveScope({{ $matchingRawScope->id }}, '{{ $scope->state }}{{ $scope->lga ? ' / ' . $scope->lga : ' (All LGAs)' }}')">
                                                                            <i class="fas fa-trash me-1"></i> Remove Assignment
                                                                        </button>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-map fa-3x text-muted mb-3"></i>
                                    <h5>No Regional Assignments</h5>
                                    <p class="text-muted">This regional manager has no assigned regions.</p>
                                    <a href="{{ route('admin.regional-managers.assign-regions', $regionalManager) }}" 
                                       class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Assign Regions
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Raw Scopes (for debugging/admin view) -->
                    @if($rawScopes->count() > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">Raw Scope Data <small class="text-muted">(Admin View)</small></h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Type</th>
                                                <th>Value</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rawScopes as $rawScope)
                                                <tr>
                                                    <td>{{ $rawScope->id }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $rawScope->scope_type === 'state' ? 'primary' : 'info' }}">
                                                            {{ ucfirst($rawScope->scope_type) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $rawScope->scope_value }}</td>
                                                    <td>{{ $rawScope->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmRemoveScope({{ $rawScope->id }}, '{{ $rawScope->scope_type }}: {{ $rawScope->scope_value }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Remove Scope Modal -->
<div class="modal fade" id="removeScopeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="removeScopeForm">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Remove Regional Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to remove the assignment for <strong id="scopeToRemove"></strong>?</p>
                    <input type="hidden" name="scope_id" id="scopeIdToRemove">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Remove Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove All Scopes Modal -->
<div class="modal fade" id="removeAllScopesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="removeAllScopesForm">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Remove All Regional Assignments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Danger!</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to remove ALL regional assignments for <strong id="managerNameToRemove"></strong>?</p>
                    <p class="text-muted">This will remove the regional manager's access to all {{ $scopes->count() }} assigned regions.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Remove All Assignments</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.avatar-lg {
    width: 80px;
    height: 80px;
}
</style>
@endpush

@push('scripts')
<script>
// Confirm remove single scope
function confirmRemoveScope(scopeId, scopeDescription) {
    document.getElementById('scopeToRemove').textContent = scopeDescription;
    document.getElementById('scopeIdToRemove').value = scopeId;
    document.getElementById('removeScopeForm').action = 
        `{{ route('admin.regional-managers.show', $regionalManager) }}/remove-scope`;
    
    const modal = new bootstrap.Modal(document.getElementById('removeScopeModal'));
    modal.show();
}

// Confirm remove all scopes
function confirmRemoveAllScopes(managerId, managerName) {
    document.getElementById('managerNameToRemove').textContent = managerName;
    document.getElementById('removeAllScopesForm').action = 
        `{{ route('admin.regional-managers.show', $regionalManager) }}/remove-all-scopes`;
    
    const modal = new bootstrap.Modal(document.getElementById('removeAllScopesModal'));
    modal.show();
}
</script>
@endpush