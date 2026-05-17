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
                                <p class="form-control-static d-flex align-items-center">
                                    {{ $agent->name ?? $agent->first_name . ' ' . $agent->last_name }}
                                    @if(method_exists($agent, 'hasRole') && $agent->hasRole('Verified_Property_Manager'))
                                        <span class="er-badge er-badge-verified" title="Verified Property Manager"><i class="fa fa-check"></i></span>
                                    @else
                                        <span class="er-badge er-badge-unverified" title="Property Manager"></span>
                                    @endif
                                </p>
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
                                        <td>{{ $property->property_id }}</td>
                                        <td>{{ $property->getPropertyTypeName() }}</td>
                                        <td>{{ $property->getFullAddress() }}</td>
                                        <td>{{ $property->apartments->count() }}</td>
                                        <td>{{ $property->apartments->where('tenant_id', '!=', null)->count() }}</td>
                                        <td>
                                            <a href="/dashboard/property/{{ $property->property_id }}" class="btn btn-info btn-sm">
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

    @php
        $currentUser = auth()->user();
        $isEligibleToRate = false;
        $eligiblePropertyId = null;

        if ($currentUser && $currentUser->user_id !== $agent->user_id) {
            $managedPropertyIds = $properties->pluck('property_id')->toArray();
            
            // Check Landlord ownership
            $ownedProperty = \App\Models\Property::whereIn('property_id', $managedPropertyIds)
                ->where('user_id', $currentUser->user_id)
                ->first();

            if ($ownedProperty) {
                $isEligibleToRate = true;
                $eligiblePropertyId = $ownedProperty->property_id;
            } else {
                // Check Tenant
                $rentedApartment = \App\Models\Apartment::whereIn('property_id', $managedPropertyIds)
                    ->where('tenant_id', $currentUser->user_id)
                    ->first();
                if ($rentedApartment) {
                    $isEligibleToRate = true;
                    $eligiblePropertyId = $rentedApartment->property_id;
                }
            }

            // Check if already rated
            if ($isEligibleToRate) {
                $hasAlreadyRated = \App\Models\AgentRating::where('agent_id', $agent->user_id)
                    ->where('user_id', $currentUser->user_id)
                    ->where('property_id', $eligiblePropertyId)
                    ->exists();
                if ($hasAlreadyRated) {
                    $isEligibleToRate = false;
                }
            }
        }
    @endphp

    @if($isEligibleToRate)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><i class="fa fa-star text-warning"></i> Rate this Property Manager</h4>
                </div>
                <div class="card-body">
                    <form id="agentRatingForm">
                        @csrf
                        <input type="hidden" name="agent_id" value="{{ $agent->user_id }}">
                        <input type="hidden" name="property_id" value="{{ $eligiblePropertyId }}">
                        
                        <div class="form-group">
                            <label>Select Rating</label>
                            <select name="rating" class="form-control" style="max-width: 200px;" required>
                                <option value="">-- Stars --</option>
                                <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                                <option value="4">⭐⭐⭐⭐ (4/5)</option>
                                <option value="3">⭐⭐⭐ (3/5)</option>
                                <option value="2">⭐⭐ (2/5)</option>
                                <option value="1">⭐ (1/5)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Comment</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Write your review here..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">Submit Rating</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Reviews Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Reviews & Ratings</h4>
                </div>
                <div class="card-body">
                    @php 
                        $ratings = $agent->agentRatings; 
                        $avgRating = $ratings->avg('rating') ?? 0.5;
                    @endphp
                    @if($ratings->isEmpty())
                        <div class="mb-4">
                            <h3 class="text-warning mb-0">
                                {{ number_format($avgRating, 1) }} <i class="fa fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>
                            </h3>
                            <small class="text-muted">No reviews yet.</small>
                        </div>
                    @else
                        <div class="mb-4">
                            <h3 class="text-warning mb-0">
                                {{ number_format($avgRating, 1) }} <i class="fa fa-star"></i>
                            </h3>
                            <small class="text-muted">Based on {{ $ratings->count() }} review(s)</small>
                        </div>
                        <div class="list-group">
                            @foreach($ratings as $r)
                                <div class="list-group-item list-group-item-action flex-column align-items-start mb-2 border rounded">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ optional($r->user)->first_name }} {{ optional($r->user)->last_name }}</h6>
                                        <small class="text-muted">{{ $r->created_at->format('M j, Y') }}</small>
                                    </div>
                                    <div class="text-warning mb-2">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="fa fa-star{{ $i <= $r->rating ? '' : '-o' }}"></i>
                                        @endfor
                                    </div>
                                    @if($r->comment)
                                        <p class="mb-1">{{ $r->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')
<!-- Footer area end -->
<script>
$(document).ready(function() {
    $('#agentRatingForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('agent.rate') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    });
                }
            },
            error: function(xhr) {
                var message = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Toast.fire({
                    icon: 'error',
                    title: message
                });
            }
        });
    });
});
</script>
</body>
</html>