@extends('layout')

@section('content')
<div class="content pt-3">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <div class="pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Complaint Management Dashboard</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('complaints.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fafa-list me-1"></i>All Complaints
                            </a>
                        </div>
                    </div>
                </div>
                <div class="px-0 pt-0 pb-2">

                    <!-- Search and Filter Section -->
                    <div class="px-3 mb-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fafa-search me-2"></i>Search & Filter Complaints
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('complaints.landlord.dashboard') }}"
                                    class="row g-3">
                                    <!-- Search by Tenant Name -->
                                    <div class="col-md-3">
                                        <label class="form-label small font-weight-bold">Tenant Name</label>
                                        <input type="text" name="tenant_name" class="form-control form-control-sm"
                                            placeholder="Search tenant..." value="{{ request('tenant_name') }}">
                                    </div>

                                    <!-- Search by Complaint Title -->
                                    <div class="col-md-3">
                                        <label class="form-label small font-weight-bold">Complaint Title</label>
                                        <input type="text" name="title" class="form-control form-control-sm"
                                            placeholder="Search title..." value="{{ request('title') }}">
                                    </div>

                                    <!-- Filter by Status -->
                                    <div class="col-md-2">
                                        <label class="form-label small font-weight-bold">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="">All Status</option>
                                            <option value="open" {{ request('status')==='open' ? 'selected' : '' }}>Open
                                            </option>
                                            <option value="in_progress" {{ request('status')==='in_progress'
                                                ? 'selected' : '' }}>In Progress</option>
                                            <option value="resolved" {{ request('status')==='resolved' ? 'selected' : ''
                                                }}>Resolved</option>
                                            <option value="closed" {{ request('status')==='closed' ? 'selected' : '' }}>
                                                Closed</option>
                                        </select>
                                    </div>

                                    <!-- Filter by Priority -->
                                    <div class="col-md-2">
                                        <label class="form-label small font-weight-bold">Priority</label>
                                        <select name="priority" class="form-select form-select-sm">
                                            <option value="">All Priority</option>
                                            <option value="low" {{ request('priority')==='low' ? 'selected' : '' }}>Low
                                            </option>
                                            <option value="medium" {{ request('priority')==='medium' ? 'selected' : ''
                                                }}>Medium</option>
                                            <option value="high" {{ request('priority')==='high' ? 'selected' : '' }}>
                                                High</option>
                                            <option value="urgent" {{ request('priority')==='urgent' ? 'selected' : ''
                                                }}>Urgent</option>
                                        </select>
                                    </div>

                                    <!-- Filter by Category -->
                                    <div class="col-md-2">
                                        <label class="form-label small font-weight-bold">Category</label>
                                        <select name="category" class="form-select form-select-sm">
                                            <option value="">All Categories</option>
                                            @foreach($categories ?? [] as $cat)
                                            <option value="{{ $cat->id }}" {{ request('category')==$cat->id ? 'selected'
                                                : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Filter by Property -->
                                    <div class="col-md-2">
                                        <label class="form-label small font-weight-bold">Property</label>
                                        <input type="text" name="property" class="form-control form-control-sm"
                                            placeholder="Property name..." value="{{ request('property') }}">
                                    </div>

                                    <!-- Date Range From -->
                                    <div class="col-md-2">
                                        <label class="form-label small font-weight-bold">From Date</label>
                                        <input type="date" name="date_from" class="form-control form-control-sm"
                                            value="{{ request('date_from') }}">
                                    </div>

                                    <!-- Date Range To -->
                                    <div class="col-md-2">
                                        <label class="form-label small font-weight-bold">To Date</label>
                                        <input type="date" name="date_to" class="form-control form-control-sm"
                                            value="{{ request('date_to') }}">
                                    </div>

                                    <!-- Sort By -->
                                    <div class="col-md-2">
                                        <label class="form-label small font-weight-bold">Sort By</label>
                                        <select name="sort_by" class="form-select form-select-sm">
                                            <option value="latest" {{ request('sort_by', 'latest' )==='latest'
                                                ? 'selected' : '' }}>Latest</option>
                                            <option value="oldest" {{ request('sort_by')==='oldest' ? 'selected' : ''
                                                }}>Oldest</option>
                                            <option value="priority" {{ request('sort_by')==='priority' ? 'selected'
                                                : '' }}>Priority</option>
                                            <option value="status" {{ request('sort_by')==='status' ? 'selected' : ''
                                                }}>Status</option>
                                        </select>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-12 d-flex gap-2 pt-2">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fafa-search me-1"></i>Search
                                        </button>
                                        <a href="{{ route('complaints.landlord.dashboard') }}"
                                            class="btn btn-sm btn-secondary">
                                            <i class="fafa-redo me-1"></i>Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4 px-3">
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="number">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total
                                                    Complaints</p>
                                                <h5 class="font-weight-bolder mb-0">
                                                    {{ $stats['total'] ?? 0 }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div
                                                class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                                <i class="fafa-exclamation-triangle text-lg opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="number">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Open Complaints
                                                </p>
                                                <h5 class="font-weight-bolder mb-0 text-warning">
                                                    {{ $stats['open'] ?? 0 }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div
                                                class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                                <i class="fafa-clock text-lg opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="number">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Resolved</p>
                                                <br />
                                                <h5 class="font-weight-bolder mb-0 text-success">
                                                    {{ $stats['resolved'] ?? 0 }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div
                                                class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                                <i class="fafa-check-circle text-lg opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="number">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Overdue</p>
                                                <br />
                                                <h5 class="font-weight-bolder mb-0 text-danger">
                                                    {{ $stats['overdue'] ?? 0 }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div
                                                class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                                <i class="fafa-exclamation-circle text-lg opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Complaints Table -->
                    <div class="px-3">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Complaint</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Property</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Priority</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Created</th>
                                        <th class="text-secondary opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($complaints ?? [] as $complaint)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $complaint->title }}</h6>
                                                    <p class="text-xs text-secondary mb-0">
                                                        {{ $complaint->category->name ?? 'General' }}
                                                    </p>
                                                    <p class="text-xs text-secondary mb-0">
                                                        by {{ $complaint->tenant->name ?? 'Unknown' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{
                                                $complaint->apartment->property->prop_name ?? 'N/A' }}</p>
                                            <p class="text-xs text-secondary mb-0">Apt {{
                                                $complaint->apartment->apartment_id ?? 'N/A' }}</p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            @php
                                            $statusColors = [
                                            'open' => 'warning',
                                            'in_progress' => 'info',
                                            'resolved' => 'success',
                                            'closed' => 'secondary'
                                            ];
                                            $color = $statusColors[$complaint->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-sm bg-gradient-{{ $color }}">{{
                                                ucfirst(str_replace('_', ' ', $complaint->status)) }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            @php
                                            $priorityColors = [
                                            'low' => 'success',
                                            'medium' => 'warning',
                                            'high' => 'danger',
                                            'urgent' => 'danger'
                                            ];
                                            $priorityColor = $priorityColors[$complaint->priority] ?? 'secondary';
                                            @endphp
                                            <span class="text-secondary text-xs font-weight-bold">
                                                <i class="fafa-circle text-{{ $priorityColor }} me-1"></i>
                                                {{ ucfirst($complaint->priority) }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">{{
                                                $complaint->created_at->format('M d, Y') }}</span>
                                            <br>
                                            <span class="text-secondary text-xs">{{
                                                $complaint->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('complaints.show', $complaint) }}"
                                                    class="btn btn-link text-primary text-gradient px-2 mb-0">
                                                    <i class="fafa-eye text-primary me-1"></i>View
                                                </a>

                                                @if($complaint->status !== 'resolved' && $complaint->status !==
                                                'closed')
                                                <div class="dropdown">
                                                    <button class="btn btn-link text-secondary px-2 mb-0" type="button"
                                                        data-bs-toggle="dropdown">
                                                        <i class="fafa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form method="POST"
                                                                action="{{ route('complaints.status', $complaint) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="in_progress">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fafa-play me-2"></i>Mark In Progress
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST"
                                                                action="{{ route('complaints.status', $complaint) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="resolved">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fafa-check me-2"></i>Mark Resolved
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fafa-inbox fa-3x text-secondary mb-3"></i>
                                                <h6 class="text-secondary">No complaints found</h6>
                                                <p class="text-xs text-secondary mb-0">All your properties are
                                                    complaint-free!</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(isset($complaints) && method_exists($complaints, 'hasPages') && $complaints->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $complaints->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fafa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif

@if(session('error'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fafa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif
@endsection