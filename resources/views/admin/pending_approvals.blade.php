@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-time-alarm"></i> Pending Approvals
                            </h4>
                            <p class="card-category">Review and approve properties, artisans, and property managers</p>
                        </div>
                        <div class="col-md-4">
                            <a href="/dashboard" class="btn btn-info btn-sm float-right">
                                <i class="nc-icon nc-minimal-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center">
                                <i class="nc-icon nc-bank text-warning" style="font-size: 2em;"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Pending Properties</p>
                                <p class="card-title">{{ $pendingProperties->total() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center">
                                <i class="nc-icon nc-settings text-info" style="font-size: 2em;"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Pending Artisans</p>
                                <p class="card-title">{{ $pendingArtisans->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center">
                                <i class="nc-icon nc-single-02 text-success" style="font-size: 2em;"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Pending Prop. Managers</p>
                                <p class="card-title">{{ $pendingPMs->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Properties -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-bank"></i> Pending Properties</h5>
                    <p class="card-category">Properties awaiting admin review</p>
                </div>
                <div class="card-body">
                    @if($pendingProperties->count())
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="text-primary">
                                <th>ID</th>
                                <th>Property</th>
                                <th>Owner</th>
                                <th>Location</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach($pendingProperties as $property)
                                <tr>
                                    <td><small class="text-muted">{{ $property->property_id }}</small></td>
                                    <td>
                                        <strong>{{ $property->prop_name ?? $property->address ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $property->getPropertyTypeName() ?? '' }}</small>
                                    </td>
                                    <td>
                                        {{ optional($property->user)->first_name }} {{ optional($property->user)->last_name }}
                                        <br><small class="text-muted">{{ optional($property->user)->email }}</small>
                                    </td>
                                    <td>
                                        {{ $property->lga }}, {{ $property->state }}
                                        <br><small class="text-muted">{{ $property->country_name ?? $property->country ?? '' }}</small>
                                    </td>
                                    <td><small>{{ optional($property->created_at)->format('M d, Y') }}</small></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <form action="{{ route('admin.approvals.property.approve', $property->property_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this property?')">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.approvals.property.reject', $property->property_id) }}" method="POST" class="d-inline ml-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this property?')">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $pendingProperties->links() }}
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="nc-icon nc-check-2" style="font-size: 40px;"></i>
                        <p class="mt-2">No pending properties. All caught up!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Artisans -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-settings"></i> Pending Artisans</h5>
                    <p class="card-category">Artisan accounts awaiting verification</p>
                </div>
                <div class="card-body">
                    @if($pendingArtisans->count())
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="text-primary">
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Category</th>
                                <th>Bio</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach($pendingArtisans as $artisan)
                                <tr>
                                    <td><small class="text-muted">{{ $artisan->user_id }}</small></td>
                                    <td><strong>{{ $artisan->first_name }} {{ $artisan->last_name }}</strong></td>
                                    <td>{{ $artisan->email }}</td>
                                    <td>{{ $artisan->phone ?? '—' }}</td>
                                    <td>
                                        @if($artisan->artisanCategory)
                                            <span class="badge badge-info">{{ $artisan->artisanCategory->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><small>{{ Str::limit($artisan->artisan_bio, 60) ?: '—' }}</small></td>
                                    <td><small>{{ optional($artisan->created_at)->format('M d, Y') }}</small></td>
                                    <td>
                                        <form action="{{ route('admin.approvals.artisan.toggle', $artisan) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this artisan?')">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="nc-icon nc-check-2" style="font-size: 40px;"></i>
                        <p class="mt-2">No pending artisans.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Property Managers -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-single-02"></i> Pending Property Managers</h5>
                    <p class="card-category">Property Managers awaiting upgrade to Verified status</p>
                </div>
                <div class="card-body">
                    @if($pendingPMs->count())
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="text-primary">
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Properties</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach($pendingPMs as $pm)
                                <tr>
                                    <td><small class="text-muted">{{ $pm->user_id }}</small></td>
                                    <td><strong>{{ $pm->first_name }} {{ $pm->last_name }}</strong></td>
                                    <td>{{ $pm->email }}</td>
                                    <td>{{ $pm->phone ?? '—' }}</td>
                                    <td>{{ $pm->lga ? $pm->lga . ', ' : '' }}{{ $pm->state ?? '—' }}</td>
                                    <td><span class="badge badge-info">{{ $pm->managedProperties()->count() }}</span></td>
                                    <td><small>{{ optional($pm->created_at)->format('M d, Y') }}</small></td>
                                    <td>
                                        <form action="{{ route('admin.approvals.pm.approve', $pm) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve and verify this Property Manager?')">
                                                <i class="fa fa-check"></i> Verify
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="nc-icon nc-check-2" style="font-size: 40px;"></i>
                        <p class="mt-2">No pending property managers.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')