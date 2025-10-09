@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">User Profile</h4>
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
                <div class="card-body">
                    @if(isset($user))
                        <div class="media">
                            <img class="mr-3 rounded" style="width:80px;height:80px;object-fit:cover;" src="{{ $user->photo ? asset($user->photo) : asset('assets/images/default-avatar.png') }}" alt="Avatar">
                            <div class="media-body">
                                <h5 class="mt-0">{{ $user->first_name }} {{ $user->last_name }}</h5>
                                <p class="text-muted mb-1">Email: {{ $user->email }}</p>
                                <p class="text-muted mb-1">Phone: {{ $user->phone ?? 'N/A' }}</p>
                                <p class="text-muted mb-1">Address: {{ $user->address ?? 'N/A' }}</p>
                                <p class="text-muted mb-1">State / LGA: {{ $user->state ?? 'N/A' }} / {{ $user->lga ?? 'N/A' }}</p>
                                <p class="text-muted mb-1">Legacy Role: {{ $user->role ?? 'N/A' }}</p>
                                <p class="mb-0">Modern Roles:
                                    @php $rnames = $user->roles()->pluck('display_name','name'); @endphp
                                    @if($rnames->isEmpty())
                                        <span class="badge badge-secondary">None</span>
                                    @else
                                        @foreach($rnames as $n=>$dn)
                                            <span class="badge badge-info">{{ $dn ?? ucfirst(str_replace('_',' ', $n)) }}</span>
                                        @endforeach
                                    @endif
                                </p>
                            </div>
                        </div>
                        <hr>
                        <h5>Regional Scopes</h5>
                        @php $scopes = $user->regionalScopes; @endphp
                        @if($scopes->isEmpty())
                            <p class="text-muted">No regional scopes assigned (global access).</p>
                        @else
                            <ul class="list-group">
                                @foreach($scopes as $scope)
                                    <li class="list-group-item">{{ $scope->scope_type }}: {{ $scope->scope_value }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @else
                        <p class="text-danger">User record not found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
