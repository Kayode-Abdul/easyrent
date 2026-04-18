@extends('layout')

@section('content')

<div class="content">
    <!-- Page Header -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="nc-icon nc-support-17"></i>
                            @php
                            $user = auth()->user();
                            $isTenant = $user->isTenant();
                            $isLandlord = $user->isLandlord();
                            $isAgent = $user->isAgent();
                            $hasTenancy = $user->tenantLeases()->exists();
                            @endphp

                            @if($isTenant && !$isLandlord && !$isAgent)
                            My Complaints
                            @elseif($isLandlord && !$hasTenancy && !$isAgent)
                            Tenant Complaints
                            @elseif($isAgent && !$isLandlord && !$hasTenancy)
                            Assigned Complaints
                            @else
                            My Complaints & Tasks
                            @endif
                        </h4>
                        @if($isTenant)
                        <a href="{{ route('complaints.create') }}" class="btn btn-primary btn-sm">
                            <i class="nc-icon nc-simple-add"></i> Submit New Complaint
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('complaints.index') }}" class="row">
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="open" {{ request('status')==='open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ request('status')==='in_progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="resolved" {{ request('status')==='resolved' ? 'selected' : '' }}>Resolved
                                </option>
                                <option value="closed" {{ request('status')==='closed' ? 'selected' : '' }}>Closed
                                </option>
                                <option value="escalated" {{ request('status')==='escalated' ? 'selected' : '' }}>
                                    Escalated</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="priority" class="form-control">
                                <option value="">All Priorities</option>
                                <option value="low" {{ request('priority')==='low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority')==='medium' ? 'selected' : '' }}>Medium
                                </option>
                                <option value="high" {{ request('priority')==='high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ request('priority')==='urgent' ? 'selected' : '' }}>Urgent
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category')==$category->id ? 'selected' :
                                    '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="nc-icon nc-zoom-split"></i> Filter
                            </button>
                            <a href="{{ route('complaints.index') }}" class="btn btn-secondary btn-sm">
                                <i class="nc-icon nc-simple-remove"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaints List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if($complaints->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Complaint #</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    @if($isLandlord || $isAgent)
                                    <th>Tenant</th>
                                    @endif
                                    @if($isTenant || $isAgent)
                                    <th>Property</th>
                                    @endif
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($complaints as $complaint)
                                <tr class="{{ $complaint->isOverdue() ? 'table-warning' : '' }}">
                                    <td>
                                        <strong>{{ $complaint->complaint_number }}</strong>
                                        @if($complaint->isOverdue())
                                        <span class="badge badge-warning badge-sm ml-1">Overdue</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i
                                                class="{{ $complaint->category->icon }} text-{{ $complaint->priority_color }} mr-2"></i>
                                            {{ Str::limit($complaint->title, 40) }}
                                        </div>
                                    </td>
                                    <td>{{ $complaint->category->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $complaint->priority_color }}">
                                            {{ $complaint->priority_formatted }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $complaint->status_color }}">
                                            {{ $complaint->status_formatted }}
                                        </span>
                                    </td>
                                    @if($isLandlord || $isAgent)
                                    <td>{{ $complaint->tenant->first_name }} {{ $complaint->tenant->last_name }}</td>
                                    @endif
                                    @if($isTenant || $isAgent)
                                    <td>{{ Str::limit($complaint->apartment->property->address, 30) }}</td>
                                    @endif
                                    <td>{{ $complaint->created_at->format('M j, Y') }}</td>
                                    <td>
                                        <a href="{{ route('complaints.show', $complaint) }}"
                                            class="btn btn-info btn-sm">
                                            <i class="nc-icon nc-zoom-split"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $complaints->appends(request()->query())->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="nc-icon nc-support-17" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3">No complaints found</h4>
                        <p class="text-muted">
                            @if($isTenant && !$isLandlord && !$isAgent)
                            You haven't submitted any complaints yet.
                            @else
                            No complaints match your current filters.
                            @endif
                        </p>
                        @if($isTenant)
                        <a href="{{ route('complaints.create') }}" class="btn btn-primary">
                            <i class="nc-icon nc-simple-add"></i> Submit Your First Complaint
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection