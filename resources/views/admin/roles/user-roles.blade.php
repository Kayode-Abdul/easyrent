@extends('layout')

@section('title', 'User Roles')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">
                            <i class="nc-icon nc-single-02"></i> User Roles for {{ $user->first_name }} {{ $user->last_name }}
                        </h4>
                        <div>
                            <a href="{{ route('admin.roles.assign') }}" class="btn btn-primary btn-sm">
                                <i class="nc-icon nc-simple-add"></i> Assign Role
                            </a>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-info btn-sm ml-2">
                                <i class="nc-icon nc-bullet-list-67"></i> All Roles
                            </a>
                        </div>
                    </div>
                    <p class="card-category">Manage roles for {{ $user->email }}</p>
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
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="mb-0">User Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="profile-info">
                                        <div class="profile-item">
                                            <strong>User ID:</strong> {{ $user->user_id }}
                                        </div>
                                        <div class="profile-item">
                                            <strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                        <div class="profile-item">
                                            <strong>Email:</strong> {{ $user->email }}
                                        </div>
                                        <div class="profile-item">
                                            <strong>Username:</strong> {{ $user->username }}
                                        </div>
                                        <div class="profile-item">
                                            <strong>Phone:</strong> {{ $user->phone ?? 'Not set' }}
                                        </div>
                                        <div class="profile-item">
                                            <strong>Joined:</strong> {{ $user->created_at ? $user->created_at->format('M d, Y') : 'Unknown' }}
                                        </div>
                                        <div class="profile-item">
                                            <strong>Admin:</strong> {{ $user->admin == 1 ? 'Yes' : 'No' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="mb-0">Assigned Roles</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Legacy Role -->
                                    <div class="mb-4">
                                        <h6>Legacy Role</h6>
                                        @if($legacyRole)
                                            <div class="legacy-role-card">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="badge badge-primary">ID: {{ $legacyRole['id'] }}</span>
                                                        <strong class="ml-2">{{ $legacyRole['display_name'] }}</strong>
                                                    </div>
                                                    <div>
                                                        <form action="{{ route('admin.roles.remove') }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="user_id" value="{{ $user->user_id }}">
                                                            <input type="hidden" name="clear_scopes" value="1">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this role?')">
                                                                <i class="nc-icon nc-simple-remove"></i> Remove
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-light">
                                                No legacy role assigned to this user.
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Modern Roles -->
                                    <div>
                                        <h6>Modern Roles</h6>
                                        @if(count($assignedRoles) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Role</th>
                                                            <th>Description</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($assignedRoles as $role)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $role->display_name }}</strong>
                                                                </td>
                                                                <td>{{ $role->description ?? 'No description' }}</td>
                                                                <td>
                                                                    <form action="{{ route('admin.roles.remove') }}" method="POST">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <input type="hidden" name="user_id" value="{{ $user->user_id }}">
                                                                        <input type="hidden" name="role_id" value="{{ $role->id }}">
                                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this role?')">
                                                                            <i class="nc-icon nc-simple-remove"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-light">
                                                No modern roles assigned to this user.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Add New Role -->
                            <div class="card bg-light mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Add Additional Role</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.roles.assign.post') }}" method="POST" class="form-inline">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->user_id }}">
                                        <input type="hidden" name="role_type" value="modern">
                                        
                                        <div class="form-group mr-2">
                                            <select name="role_id" class="form-control">
                                                <option value="">-- Select Role --</option>
                                                @foreach($availableRoles as $role)
                                                    <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success">Add Role</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.profile-info {
    padding: 0;
}

.profile-item {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.profile-item:last-child {
    border-bottom: none;
}

.legacy-role-card {
    padding: 10px;
    border: 1px solid #eee;
    border-radius: 4px;
    background-color: #f9f9f9;
}
</style>
@endsection
