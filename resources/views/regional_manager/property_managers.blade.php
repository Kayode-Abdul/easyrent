@extends('layout')
@section('content')
<div class="content">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-1">
                        <i class="fas fa-user-tie text-info me-2"></i>
                        Property Managers
                    </h4>
                    <p class="text-muted">Manage property managers in your region.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($managers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Properties Managed</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($managers as $manager)
                                        <tr>
                                            <td>{{ $manager->user_id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-light rounded-circle me-2">
                                                        <span class="avatar-title text-primary">
                                                            {{ substr($manager->first_name, 0, 1) }}{{ substr($manager->last_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $manager->first_name }} {{ $manager->last_name }}</h6>
                                                        <small class="text-muted">Property Manager</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ $manager->email }}</div>
                                                <small>{{ $manager->phone ?? 'No phone' }}</small>
                                            </td>
                                            <td>
                                                {{ $manager->properties_count }}
                                            </td>
                                            <td>{{ $manager->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('user.profile', $manager->user_id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-user"></i> View Profile
                                                    </a>
                                                    <a href="{{ route('manager.properties', $manager->user_id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-building"></i> View Properties
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $managers->links() }}
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No property managers found in your region.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
@endsection
