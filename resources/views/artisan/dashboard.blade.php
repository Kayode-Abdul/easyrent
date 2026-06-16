@extends('layout')

@section('content')
<div class="content">
    <!-- Dashboard Mode Toggles -->
    <div class="container-fluid mb-3">
        <div class="d-flex justify-content-end align-items-center">
            @php
            $user = auth()->user();
            $isAdmin = ($user->admin == 1 || $user->role == 7);
            $isArtisan = $user->isArtisan();
            @endphp

            @if($isAdmin)
            <!-- Admin Toggle -->
            <div class="mr-4">
                <span class="switch-label-left">Personal</span>
                <label class="switch mb-0">
                    <input type="checkbox" id="adminDashboardSwitch" {{ session('admin_dashboard_mode')==='admin'
                        ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
                <span class="switch-label">Admin Dashboard</span>
            </div>
            @endif

            @if($isArtisan)
            <!-- Artisan Toggle -->
            <div>
                <span class="switch-label-left">Personal</span>
                <label class="switch mb-0">
                    <input type="checkbox" id="artisanDashboardSwitch" {{ session('dashboard_mode', 'personal'
                        )==='artisan' ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
                <span class="switch-label">Artisan Dashboard</span>
            </div>
            @endif
        </div>
    </div>
    <div class="container-fluid">
        <!-- Artisan Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-briefcase-24 text-primary"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Total Bids</p>
                                    <p class="card-title">{{ $stats['total_bids'] }}</p>
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
                                    <p class="card-category">Pending Bids</p>
                                    <p class="card-title">{{ $stats['pending_bids'] }}</p>
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
                                    <i class="nc-icon nc-check-2 text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Accepted Bids</p>
                                    <p class="card-title">{{ $stats['accepted_bids'] }}</p>
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
                                    <i class="nc-icon nc-trophy text-info"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Completed Tasks</p>
                                    <p class="card-title">{{ $stats['completed_tasks'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Earnings by Currency -->
            @if(isset($stats['earnings_by_currency']) && count($stats['earnings_by_currency']) > 0)
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Total Earnings (Accepted Bids)</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($stats['earnings_by_currency'] as $earning)
                            <div class="col-md-3">
                                <div class="alert alert-success text-center mb-0">
                                    <h4 class="mb-0">{{ $earning['currency']->symbol }} {{ number_format($earning['total']) }}</h4>
                                    <small>{{ $earning['currency']->code }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Active Bids -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">My Recent Bids</h4>
                            <a href="#" class="btn btn-link pr-0">See all</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-primary">
                                    <th>Task</th>
                                    <th>My Bid</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </thead>
                                <tbody>
                                    @forelse($myBids as $bid)
                                    <tr>
                                        <td>
                                            <a href="{{ route('artisan.tasks.show', $bid->task) }}">
                                                {{ Str::limit($bid->task->complaint->title, 30) }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $bid->task->complaint->category->name ?? 'General' }}</small>
                                        </td>
                                        <td>{{ format_money($bid->amount) }}</td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $bid->status == 'accepted' ? 'success' : ($bid->status == 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($bid->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $bid->created_at->format('M j') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">You haven't placed any bids yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Opportunities -->
            <div class="col-md-5">
                <div class="card card-tasks">
                    <div class="card-header">
                        <h4 class="card-title">Discover Tasks</h4>
                        <ul class="nav nav-pills nav-pills-primary mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="pill" href="#categoryTasks" role="tab">My Category</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="#allTasks" role="tab">All Open</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Category Tasks -->
                            <div class="tab-pane active" id="categoryTasks" role="tabpanel">
                                <div class="table-full-width table-responsive">
                                    <table class="table">
                                        <tbody>
                                            @forelse($categoryTasks as $task)
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <strong>{{ $task->complaint->title }}</strong>
                                                        <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                                        <div class="mt-2">
                                                            <span class="badge badge-info badge-pill">{{ format_money($task->budget_min) }} - {{ format_money($task->budget_max) }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="td-actions text-right">
                                                    <a href="{{ route('artisan.tasks.show', $task) }}" class="btn btn-info btn-round btn-link btn-icon btn-sm"><i class="fa fa-eye"></i></a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td class="text-center py-4 text-muted">No category-specific tasks.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- All Tasks -->
                            <div class="tab-pane" id="allTasks" role="tabpanel">
                                <div class="table-full-width table-responsive">
                                    <table class="table">
                                        <tbody>
                                            @forelse($relevantTasks as $task)
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <strong>{{ $task->complaint->title }}</strong>
                                                        <small class="text-muted">{{ $task->complaint->category->name ?? 'General' }}</small>
                                                        <div class="mt-2">
                                                            <span class="badge badge-info badge-pill">{{ format_money($task->budget_min) }} - {{ format_money($task->budget_max) }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="td-actions text-right">
                                                    <a href="{{ route('artisan.tasks.show', $task) }}" class="btn btn-info btn-round btn-link btn-icon btn-sm"><i class="fa fa-eye"></i></a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td class="text-center py-4 text-muted">No open tasks found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <a href="{{ route('artisan.market') }}" class="btn btn-warning btn-block">Explore Full Marketplace</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    /* Modern switch toggle */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #007bff;
    }

    input:checked+.slider:before {
        transform: translateX(26px);
    }

    .switch-label {
        margin-left: 12px;
        font-weight: bold;
        vertical-align: middle;
        font-size: 14px;
        color: #495057;
    }

    .switch-label-left {
        margin-right: 12px;
        font-weight: bold;
        vertical-align: middle;
        font-size: 14px;
        color: #495057;
    }
</style>
@endpush

@push('scripts')
<script>
    $(function () {
        // Admin Dashboard Toggle
        $('#adminDashboardSwitch').on('change', function () {
            var mode = this.checked ? 'admin' : 'personal';
            var $switch = $(this);
            $switch.prop('disabled', true);

            $.ajax({
                url: '/dashboard/switch-admin-mode',
                method: 'POST',
                data: { mode: mode },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    if (res.success) {
                        window.location.href = '/dashboard';
                    } else {
                        $switch.prop('disabled', false);
                        alert('Failed to switch admin mode: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function (xhr, status, error) {
                    $switch.prop('disabled', false);
                    alert('Error switching admin mode.');
                }
            });
        });

        // Artisan Dashboard Toggle
        $('#artisanDashboardSwitch').on('change', function () {
            var mode = this.checked ? 'artisan' : 'personal';
            var $switch = $(this);
            $switch.prop('disabled', true);

            $.ajax({
                url: '/dashboard/switch-artisan-mode',
                method: 'POST',
                data: { mode: mode },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    if (res.success) {
                        window.location.href = res.mode === 'artisan' ? '/artisan/dashboard' : '/dashboard';
                    } else {
                        $switch.prop('disabled', false);
                        alert('Failed to switch artisan mode: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function (xhr, status, error) {
                    $switch.prop('disabled', false);
                    alert('Error switching artisan mode.');
                }
            });
        });
    });
</script>
@endpush