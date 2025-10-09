@extends('layout')


@section('content') 
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">
                            <i class="nc-icon nc-key-25"></i> Role Management
                        </h4>
                        <div>
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                                <i class="nc-icon nc-simple-add"></i> Create Role
                            </a>
                            <a href="{{ route('admin.roles.assign') }}" class="btn btn-success btn-sm ml-2">
                                <i class="nc-icon nc-single-02"></i> Assign Roles
                            </a>
                        </div>
                    </div>
                    <p class="card-category">Manage user roles and permissions in the system</p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Role Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>Role Distribution</h5>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="progress-container">
                                                @foreach($roles as $role)
                                                    @php
                                                        $percentage = $userCount > 0 ? round(($role['user_count'] / $userCount) * 100) : 0;
                                                        $colors = [
                                                            1 => 'danger', // Super Admin
                                                            2 => 'warning', // Admin
                                                            3 => 'info', // Property Manager
                                                            4 => 'primary', // Landlord
                                                            5 => 'success', // Tenant
                                                            6 => 'secondary', // Regional Manager
                                                            7 => 'dark', // Marketer
                                                        ];
                                                        $color = $colors[$role['id']] ?? 'info';
                                                    @endphp
                                                    <div class="mb-2">
                                                        <span>{{ $role['name'] }}: {{ $role['user_count'] }} users ({{ $percentage }}%)</span>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h3 class="mb-0">{{ $userCount }}</h3>
                                                <p>Total Users</p>
                                                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa fa-users"></i> Manage Users
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="text-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>User Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role['id'] }}</td>
                                    <td>{{ $role['name'] }}</td>
                                    <td>{{ $role['description'] }}</td>
                                    <td>{{ $role['user_count'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.roles.show', $role['id']) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> View Users
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4>Quick Assign Role to User</h4>
                            <form action="{{ route('admin.roles.assign.post') }}" method="POST" class="row">
                                @csrf
                                <input type="hidden" name="role_type" value="modern">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Select User</label>
                                        <select name="user_id" class="form-control" required>
                                            <option value="">-- Select User --</option>
                                            @isset($users)
                                                @foreach($users as $u)
                                                    <option value="{{ $u->user_id }}">{{ $u->first_name }} {{ $u->last_name }} ({{ $u->email }})</option>
                                                @endforeach
                                            @endisset
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Select Role</label>
                                        <select name="role_id" class="form-control" required>
                                            <option value="">-- Select Role --</option>
                                            @isset($allRoles)
                                                @foreach($allRoles as $r)
                                                    <option value="{{ $r->id }}">{{ $r->display_name ?? ucfirst(str_replace('_',' ', $r->name)) }}</option>
                                                @endforeach
                                            @endisset
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">Assign</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Add any JavaScript needed for the roles page
    });
</script>
@endpush