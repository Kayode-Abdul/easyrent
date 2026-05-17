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
                                <h5 class="mt-0">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                    @if($user->isArtisan())
                                        @if($user->is_artisan_verified)
                                            <span class="er-badge er-badge-verified" title="Verified Artisan"><i class="fa fa-check"></i></span>
                                        @else
                                            <span class="er-badge er-badge-unverified" title="Unverified Artisan"></span>
                                        @endif
                                    @elseif($user->isAgent())
                                        @if($user->hasRole('Verified_Property_Manager'))
                                            <span class="er-badge er-badge-verified" title="Verified Property Manager"><i class="fa fa-check"></i></span>
                                        @else
                                            <span class="er-badge er-badge-unverified" title="Property Manager"></span>
                                        @endif
                                    @endif
                                </h5>
                                <p class="text-muted mb-1">Email: {{ $user->email }}</p>
                                <p class="text-muted mb-1">Phone: {{ $user->phone ?? 'N/A' }}</p>
                                <p class="text-muted mb-1">Address: {{ $user->address ?? 'N/A' }}</p>
                                <p class="text-muted mb-1">State / LGA: {{ $user->state ?? 'N/A' }} / {{ $user->lga ?? 'N/A' }}</p>
                                @if($user->isArtisan())
                                    <p class="text-muted mb-1">
                                        <i class="fa fa-tools text-primary mr-1"></i> Category: 
                                        <strong class="text-primary">{{ optional($user->artisanCategory)->name ?? 'Not Specified' }}</strong>
                                    </p>
                                    <p class="text-muted mb-1">
                                        <i class="fa fa-briefcase text-primary mr-1"></i> Specialty: 
                                        <strong>{{ $user->occupation ?? 'General Artisan' }}</strong>
                                    </p>
                                @endif
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
                        
                        @if($user->isArtisan())
                        <hr>
                        <h5>Artisan Ratings & Reviews</h5>
                        @php 
                            $ratings = $user->artisanRatings()->with('rater')->get(); 
                            $avgRating = $ratings->avg('rating') ?? 0.5;
                        @endphp
                        @if($ratings->isEmpty())
                            <div class="mb-3">
                                <h3 class="text-warning mb-0">
                                    {{ number_format($avgRating, 1) }} <i class="fa fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>
                                </h3>
                                <small class="text-muted">No reviews yet.</small>
                            </div>
                        @else
                            <div class="mb-3">
                                <h3 class="text-warning mb-0">
                                    {{ number_format($avgRating, 1) }} <i class="fa fa-star"></i>
                                </h3>
                                <small class="text-muted">Based on {{ $ratings->count() }} review(s)</small>
                            </div>
                            <ul class="list-group">
                                @foreach($ratings as $r)
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <strong>{{ $r->rater->first_name }} {{ $r->rater->last_name }}</strong>
                                            <span class="text-warning">
                                                @for($i=1; $i<=5; $i++)
                                                    <i class="fa fa-star{{ $i <= $r->rating ? '' : '-o' }}"></i>
                                                @endfor
                                            </span>
                                        </div>
                                        @if($r->comment)
                                            <p class="mb-0 mt-2">{{ $r->comment }}</p>
                                        @endif
                                        <small class="text-muted">{{ $r->created_at->format('M j, Y') }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
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
