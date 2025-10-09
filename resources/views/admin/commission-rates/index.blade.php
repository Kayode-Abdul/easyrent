@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Commission Rate Management</h4>
                    <div>
                        <a href="{{ route('admin.commission-rates.bulk-update') }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i> Bulk Update
                        </a>
                        <a href="{{ route('admin.commission-rates.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add New Rate
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.commission-rates.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="region" class="form-control form-control-sm">
                                    <option value="">All Regions</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region }}" {{ request('region') == $region ? 'selected' : '' }}>
                                            {{ $region }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="role_id" class="form-control form-control-sm">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.commission-rates.index') }}" class="btn btn-light btn-sm">
                                    <i class="fa fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Commission Rates Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Region</th>
                                    <th>Role</th>
                                    <th>Commission %</th>
                                    <th>Effective From</th>
                                    <th>Effective Until</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rates as $rate)
                                    <tr class="{{ !$rate->is_active ? 'table-secondary' : '' }}">
                                        <td>
                                            <strong>{{ $rate->region }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $rate->role->name ?? 'Unknown' }}</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-success">{{ number_format($rate->commission_percentage, 2) }}%</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $rate->effective_from->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td>
                                            @if($rate->effective_until)
                                                <small class="text-muted">{{ $rate->effective_until->format('M d, Y H:i') }}</small>
                                            @else
                                                <span class="badge badge-success">Ongoing</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rate->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $rate->creator->name ?? 'System' }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.commission-rates.show', $rate) }}" 
                                                   class="btn btn-outline-info btn-sm" title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.commission-rates.edit', $rate) }}" 
                                                   class="btn btn-outline-primary btn-sm" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                @if($rate->is_active)
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="confirmDeactivate({{ $rate->id }})" title="Deactivate">
                                                        <i class="fa fa-ban"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fa fa-info-circle"></i> No commission rates found matching your criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Showing {{ $rates->firstItem() ?? 0 }} to {{ $rates->lastItem() ?? 0 }} of {{ $rates->total() }} results
                        </div>
                        {{ $rates->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Confirmation Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deactivation</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate this commission rate? This action will set the effective end date to now and prevent it from being used for future calculations.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deactivateForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Deactivate</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function confirmDeactivate(rateId) {
    const form = document.getElementById('deactivateForm');
    form.action = `/admin/commission-rates/${rateId}`;
    $('#deactivateModal').modal('show');
}
</script>
@endsection