@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Commission Rate Details</h4>
                    <div>
                        <a href="{{ route('admin.commission-rates.edit', $commissionRate) }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-edit"></i> Edit Rate
                        </a>
                        <a href="{{ route('admin.commission-rates.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Rate Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">Rate Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Region:</strong></td>
                                            <td><span class="badge badge-primary badge-lg">{{ $commissionRate->region }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Role:</strong></td>
                                            <td><span class="badge badge-info badge-lg">{{ $commissionRate->role->name ?? 'Unknown' }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Commission Rate:</strong></td>
                                            <td><span class="badge badge-success badge-lg">{{ number_format($commissionRate->commission_percentage, 2) }}%</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @if($commissionRate->is_active)
                                                    <span class="badge badge-success badge-lg">Active</span>
                                                @else
                                                    <span class="badge badge-secondary badge-lg">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-left-info">
                                <div class="card-body">
                                    <h5 class="card-title text-info">Effective Period</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Effective From:</strong></td>
                                            <td>{{ $commissionRate->effective_from ? $commissionRate->effective_from->format('M d, Y H:i A') : 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Effective Until:</strong></td>
                                            <td>
                                                @if($commissionRate->effective_until)
                                                    {{ $commissionRate->effective_until->format('M d, Y H:i A') }}
                                                @else
                                                    <span class="text-success">Ongoing</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created By:</strong></td>
                                            <td>{{ $commissionRate->creator->name ?? 'System' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created At:</strong></td>
                                            <td>{{ $commissionRate->created_at->format('M d, Y H:i A') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rate History -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-history"></i> Rate History for {{ $commissionRate->region }} - {{ $commissionRate->role->name ?? 'Unknown Role' }}
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($history->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Rate</th>
                                                <th>Effective From</th>
                                                <th>Effective Until</th>
                                                <th>Status</th>
                                                <th>Created By</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($history as $rate)
                                                <tr class="{{ $rate->id == $commissionRate->id ? 'table-warning' : '' }}">
                                                    <td>
                                                        <span class="font-weight-bold text-success">{{ number_format($rate->commission_percentage, 2) }}%</span>
                                                        @if($rate->id == $commissionRate->id)
                                                            <span class="badge badge-warning ml-2">Current</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small>{{ $rate->effective_from ? $rate->effective_from->format('M d, Y H:i') : 'Not set' }}</small>
                                                    </td>
                                                    <td>
                                                        @if($rate->effective_until)
                                                            <small>{{ $rate->effective_until->format('M d, Y H:i') }}</small>
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
                                                        <small>{{ $rate->creator->name ?? 'System' }}</small>
                                                    </td>
                                                    <td>
                                                        <small>{{ $rate->created_at->format('M d, Y H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        @if($rate->id != $commissionRate->id)
                                                            <a href="{{ route('admin.commission-rates.show', $rate) }}" 
                                                               class="btn btn-outline-info btn-sm" title="View Details">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-muted">Current</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fa fa-info-circle"></i> No historical rates found for this region and role combination.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4">
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.commission-rates.edit', $commissionRate) }}" class="btn btn-primary">
                                <i class="fa fa-edit"></i> Edit Rate
                            </a>
                            @if($commissionRate->is_active)
                                <button type="button" class="btn btn-warning" onclick="confirmDeactivate()">
                                    <i class="fa fa-ban"></i> Deactivate Rate
                                </button>
                            @endif
                            <a href="{{ route('admin.commission-rates.create') }}" class="btn btn-success">
                                <i class="fa fa-plus"></i> Create New Rate
                            </a>
                        </div>
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
                <p>Are you sure you want to deactivate this commission rate?</p>
                <div class="alert alert-warning">
                    <strong>This action will:</strong>
                    <ul class="mb-0">
                        <li>Set the effective end date to now</li>
                        <li>Prevent this rate from being used for future calculations</li>
                        <li>Preserve the rate for historical reference</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.commission-rates.destroy', $commissionRate) }}" style="display: inline;">
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
function confirmDeactivate() {
    $('#deactivateModal').modal('show');
}
</script>
@endsection