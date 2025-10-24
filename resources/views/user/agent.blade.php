<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Property Manager Profile</title>
    @include('header')
</head>
<body>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Property Manager Profile</h4>
                        <a href="javascript:history.back()" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img class="avatar border-gray user-img" src="{{ $agent->photo ? asset($agent->photo) : asset('assets/images/default-avatar.png') }}" alt="Property Manager Photo" style="object-fit:cover; border-radius:50%; max-width:120px; max-height:120px;">
                        @if(auth()->id() !== $agent->id)
                            <div class="mt-2">
                                <a href="{{ url('/messages/compose?to=' . $agent->user_id) }}" class="btn btn-success">
                                    <i class="fa fa-envelope"></i> Message
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name</label>
                                <p class="form-control-static">{{ $agent->name }}</p>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <p class="form-control-static">{{ $agent->email }}</p>
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <p class="form-control-static">
                                    <span class="badge badge-info">{{ ucfirst($agent->role) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Managed Properties Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Managed Properties</h4>
                        <div class="small text-muted">
                            Total Properties: {{ $properties->count() }}
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="text-primary">
                                <tr>
                                    <th>Property ID</th>
                                    <th>Type</th>
                                    <th>Address</th>
                                    <th>Total Units</th>
                                    <th>Occupied Units</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($properties as $property)
                                    <tr>
                                        <td>{{ $property->prop_id }}</td>
                                        <td>{{ $property->getPropertyTypeName() }}</td>
                                        <td>{{ $property->getFullAddress() }}</td>
                                        <td>{{ $property->apartments->count() }}</td>
                                        <td>{{ $property->apartments->where('tenant_id', '!=', null)->count() }}</td>
                                        <td>
                                            <a href="/dashboard/property/{{ $property->prop_id }}" class="btn btn-info btn-sm">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')
<!-- Footer area end -->
</body>
</html>