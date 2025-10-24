@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ $title ?? 'Role Users' }}</h4>
                    <div>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                        <a href="{{ route('admin.roles.assign') }}" class="btn btn-sm btn-primary"><i class="fa fa-user-plus"></i> Assign Users</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($roleRecord))
                        <p><strong>Role:</strong> {{ $roleRecord->display_name ?? ucfirst(str_replace('_',' ', $roleRecord->name)) }}</p>
                        @if($roleRecord->description)
                            <p class="text-muted">{{ $roleRecord->description }}</p>
                        @endif
                    @else
                        <p class="text-warning">Legacy role view (role id not found in modern roles table).</p>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="text-primary">
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Primary Legacy Role</th>
                                    <th>Modern Roles</th>
                                    <th>Joined</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->user_id }}</td>
                                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role ?? '-' }}</td>
                                    <td>
                                        @php $rnames = $user->roles()->pluck('display_name','name'); @endphp
                                        @if($rnames->isEmpty())
                                            <span class="badge badge-secondary">None</span>
                                        @else
                                            @foreach($rnames as $n=>$dn)
                                                <span class="badge badge-info">{{ $dn ?? ucfirst(str_replace('_',' ', $n)) }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at?->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('users.profile', ['id' => $user->user_id]) }}" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted">No users assigned.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $users->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
