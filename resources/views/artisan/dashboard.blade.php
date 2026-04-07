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
        <div class="row">
            <div class="col-md-12">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-settings-gear-65 text-warning"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">Artisan Dashboard</p>
                                    <h4 class="card-title">{{ auth()->user()->first_name }} {{ auth()->user()->last_name
                                        }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-briefcase"></i> {{ auth()->user()->artisanCategory->name ?? 'Artisan' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Active Bids -->
            <div class="col-md-8">
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
                                    <th>Budget</th>
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
                                        </td>
                                        <td>₦{{ number_format($bid->task->budget_min) }} - ₦{{
                                            number_format($bid->task->budget_max) }}</td>
                                        <td>₦{{ number_format($bid->amount) }}</td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $bid->status == 'accepted' ? 'success' : ($bid->status == 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($bid->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $bid->created_at->format('M j, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">You haven't placed any bids
                                            yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Tasks in Market -->
            <div class="col-md-4">
                <div class="card card-tasks">
                    <div class="card-header">
                        <h4 class="card-title">Open Opportunities</h4>
                        <p class="card-category">Newest tasks in {{ auth()->user()->artisanCategory->name ?? 'your area'
                            }}</p>
                    </div>
                    <div class="card-body">
                        <div class="table-full-width table-responsive">
                            <table class="table">
                                <tbody>
                                    @forelse($relevantTasks as $task)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong>{{ $task->complaint->title }}</strong>
                                                <small class="text-muted">{{ Str::limit($task->description, 50)
                                                    }}</small>
                                                <div class="mt-2">
                                                    <span class="badge badge-info badge-pill">₦{{
                                                        number_format($task->budget_min) }} - ₦{{
                                                        number_format($task->budget_max) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="td-actions text-right">
                                            <a href="{{ route('artisan.tasks.show', $task) }}"
                                                class="btn btn-info btn-round btn-link btn-icon btn-sm"
                                                title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="text-center py-4 text-muted">No new tasks found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <a href="{{ route('artisan.market') }}" class="btn btn-warning btn-block">Explore Task
                                Marketplace</a>
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