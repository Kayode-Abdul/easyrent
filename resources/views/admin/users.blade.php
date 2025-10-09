@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-single-02"></i> User Management
                            </h4>
                            <p class="card-category">Comprehensive user oversight and administration</p>
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

    <!-- User Statistics -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-single-02 text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Users</p>
                                <p class="card-title">{{ number_format($stats['total_users']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-check-2 text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Active Users</p>
                                <p class="card-title">{{ number_format($stats['active_users']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-time-alarm text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">New Today</p>
                                <p class="card-title">{{ $stats['new_users_today'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-time-alarm text-danger"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Inactive</p>
                                <p class="card-title">{{ $stats['inactive_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Type Distribution -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">User Type Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="text-primary">{{ $stats['users_by_type']['landlords'] }}</h3>
                                <p>Landlords</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="text-warning">{{ $stats['users_by_type']['tenants'] }}</h3>
                                <p>Tenants</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="text-danger">{{ $stats['users_by_type']['admins'] }}</h3>
                                <p>Administrators</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">All Users</h5>
                    <p class="card-category">Comprehensive user listing with management options</p>
                </div>
                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="userTypeFilter">
                                <option value="">All User Types</option>
                                <option value="landlord">Landlords</option>
                                <option value="tenant">Tenants</option>
                                <option value="property manager">Property Managers</option>
                                <option value="admin">Admins</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="verified">Verified</option>
                                <option value="unverified">Unverified</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="text-primary">
                                <th>User</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Properties</th>
                                <th>Payments</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>
                                                <br>
                                                <small class="text-muted">ID: {{ $user->user_id }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge badge-{{ ($user->admin == 1 || $user->role == 1) ? 'danger' : ($user->role == 2 ? 'primary' : ($user->role == 3 ? 'warning' : 'info')) }}">
                                                {{ $user->admin == 1 ? 'Admin' : ($user->role == 2 ? 'Landlord' : ($user->role == 3 ? 'Tenant' : ($user->role == 4 ? 'Property Manager' : 'User'))) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $user->email_verified_at ? 'success' : 'secondary' }}">
                                                {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $user->properties_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">{{ $user->payments_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $user->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($user->admin != 1)
                                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">
                                                        <i class="nc-icon nc-ruler-pencil"></i> Edit
                                                    </a>
                                                    
                                                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger ml-1" 
                                                                onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">>
                                                            Delete
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted">Admin Protected</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row">
                        <div class="col-md-12">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search and Filter functionality
document.getElementById('userSearch').addEventListener('keyup', function() {
    filterTable();
});

document.getElementById('userTypeFilter').addEventListener('change', function() {
    filterTable();
});

document.getElementById('statusFilter').addEventListener('change', function() {
    filterTable();
});

function filterTable() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    const userTypeFilter = document.getElementById('userTypeFilter').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        const email = row.cells[1].textContent.toLowerCase();
    let userType = row.cells[2].textContent.toLowerCase();
    if (userType.includes('property manager')) userType = 'property manager';
        const status = row.cells[3].textContent.toLowerCase();
        
        const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
        const matchesType = !userTypeFilter || userType.includes(userTypeFilter);
        const matchesStatus = !statusFilter || status.includes(statusFilter);
        
        if (matchesSearch && matchesType && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

@if(session('success'))
    <script>
        setTimeout(() => {
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            alert.innerHTML = `
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => alert.remove(), 3000);
        }, 100);
    </script>
@endif

@if(session('error'))
    <script>
        setTimeout(() => {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
            alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            alert.innerHTML = `
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => alert.remove(), 3000);
        }, 100);
    </script>
@endif

@include('footer')
