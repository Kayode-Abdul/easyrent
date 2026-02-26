@extends('layout')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-settings-tool-66 text-warning"></i>
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