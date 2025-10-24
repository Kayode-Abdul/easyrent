@extends('layout')
@section('content')
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<h4 class="card-title">Regional Properties</h4>
						<p class="card-category">Scoped to your assigned regions</p>
					</div>
					<a href="{{ route('regional.dashboard') }}" class="btn btn-sm btn-outline-primary">
						<i class="fa fa-arrow-left"></i> Back to Dashboard
					</a>
				</div>
				<div class="card-body">
					<form class="mb-3" method="GET" action="{{ route('regional.properties') }}">
						<div class="form-row">
							<div class="col-md-6 mb-2">
								<input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by ID, address, state or LGA">
							</div>
							<div class="col-md-3 mb-2">
								<select name="status" class="form-control">
									<option value="">All Status</option>
									<option value="approved" {{ request('status')==='approved' ? 'selected' : '' }}>Approved</option>
									<option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
									<option value="rejected" {{ request('status')==='rejected' ? 'selected' : '' }}>Rejected</option>
									<option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspended</option>
								</select>
							</div>
							<div class="col-md-3 mb-2 d-flex">
								<select name="sort" class="form-control mr-2">
									<option value="newest" {{ request('sort')==='newest' ? 'selected' : '' }}>Newest</option>
									<option value="oldest" {{ request('sort')==='oldest' ? 'selected' : '' }}>Oldest</option>
								</select>
								<button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
							</div>
						</div>
					</form>

					@if($properties->count())
					<div class="table-responsive">
						<table class="table table-hover">
							<thead class="text-primary">
								<tr>
									<th>ID</th>
									<th>Owner</th>
									<th>Address</th>
									<th>State</th>
									<th>LGA</th>
									<th>Status</th>
									<th>Created</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								@foreach($properties as $property)
								<tr>
									<td>{{ $property->prop_id }}</td>
									<td>
										@if($property->owner)
											{{ $property->owner->first_name }} {{ $property->owner->last_name }}
											<br><small class="text-muted">{{ $property->owner->email }}</small>
										@else
											<span class="text-muted">Unknown</span>
										@endif
									</td>
									<td>{{ $property->address }}</td>
									<td>{{ $property->state }}</td>
									<td>{{ $property->lga }}</td>
									<td>{{ ucfirst($property->status ?? 'n/a') }}</td>
									<td>{{ optional($property->created_at)->format('Y-m-d') }}</td>
									<td>
										<a class="btn btn-sm btn-outline-info" href="{{ route('property.show', $property->prop_id) }}" target="_blank">View</a>
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<div class="d-flex justify-content-center">
						{{ $properties->links() }}
					</div>
					@else
						<div class="text-center text-muted py-5">
							<i class="fa fa-building fa-3x mb-2"></i>
							<div>No properties in your assigned regions.</div>
						</div>
					@endif
				</div>
				<div class="card-footer">
					<div class="stats"><i class="fa fa-clock-o"></i> Last updated {{ now()->format('M d, Y H:i') }}</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
