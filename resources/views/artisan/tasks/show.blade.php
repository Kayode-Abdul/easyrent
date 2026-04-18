@extends('layout')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">{{ $task->complaint->title }}</h4>
                            <span class="badge badge-{{ $task->status == 'open' ? 'success' : 'secondary' }}">
                                {{ ucfirst($task->status) }}
                            </span>
                        </div>
                        <p class="card-category">Category: {{ $task->complaint->category->name }}</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6>Task Description:</h6>
                            <p>{{ $task->description }}</p>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Expected Budget:</h6>
                                <p class="text-primary font-weight-bold">{{ format_money($task->budget_min) }} - {{
                                    format_money($task->budget_max) }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Preferred Duration:</h6>
                                <p>{{ $task->duration }}</p>
                            </div>
                        </div>

                        @if(auth()->user()->isArtisan() && $task->status == 'open' &&
                        !$task->bids()->where('artisan_id', auth()->id())->exists())
                        <hr>
                        <h5>Place Your Bid</h5>
                        <form action="{{ route('artisan.tasks.bid', $task) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>My Quote (${window.currencySymbol})</label>
                                        <input type="number" name="amount" class="form-control"
                                            placeholder="Enter your price" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>My Duration</label>
                                        <input type="text" name="duration" class="form-control"
                                            placeholder="e.g. 2 hours" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Proposal / Why hire me?</label>
                                <textarea name="proposal" class="form-control" rows="4"
                                    placeholder="Describe how you will handle this task..." required></textarea>
                                <small class="text-muted">Minimum 20 characters.</small>
                            </div>
                            <button type="submit" class="btn btn-warning btn-fill pull-right">Submit Bid</button>
                            <div class="clearfix"></div>
                        </form>
                        @elseif(auth()->user()->isArtisan() && $task->bids()->where('artisan_id',
                        auth()->id())->exists())
                        <div class="alert alert-info">
                            <span>You have already submitted a bid for this task.</span>
                        </div>
                        @endif

                        @if($task->status == 'assigned' && $task->verificationCode)
                        @php
                        $isAuthParty = (auth()->id() == $task->landlord_id) ||
                        (auth()->id() == $task->tenant_id) ||
                        (auth()->user()->admin) ||
                        ($task->bids()->where('artisan_id', auth()->id())->where('status', 'accepted')->exists());
                        @endphp

                        @if($isAuthParty)
                        <hr>
                        <div class="alert alert-warning text-center">
                            <h5><i class="fa fa-shield-alt"></i> Identity Verification Code</h5>
                            <p class="mb-1">This code verifies the artisan's identity for this task.</p>
                            <h3 class="mb-0 font-weight-bold" style="letter-spacing: 5px;">{{
                                $task->verificationCode->code }}</h3>
                            <small>Valid until {{ $task->verificationCode->expires_at->format('M j, Y g:i A') }}</small>
                        </div>
                        @endif
                        @endif

                        @if($task->status == 'assigned')
                        @if(auth()->id() == $task->landlord_id || auth()->id() == $task->tenant_id ||
                        auth()->user()->admin)
                        <hr>
                        <form action="{{ route('artisan.tasks.complete', $task) }}" method="POST"
                            class="mt-4 text-center">
                            @csrf
                            <button type="submit" class="btn btn-success p-2 w-100"
                                onclick="return confirm('Are you sure you want to mark this task as completed? This will execute any selected rent set-offs.')">
                                <i class="fa fa-check-double pe-2"></i> Mark as Completed
                            </button>
                        </form>
                        @endif
                        @endif
                    </div>
                </div>

                <!-- Bids Section (For Landlord/Admin to view) -->
                @if(auth()->id() == $task->landlord_id || auth()->user()->admin)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Bids Received</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-primary">
                                    <th>Artisan</th>
                                    <th>Amount</th>
                                    <th>Duration</th>
                                    <th>Proposal</th>
                                    <th>Action</th>
                                </thead>
                                <tbody>
                                    @forelse($task->bids as $bid)
                                    <tr>
                                        <td>
                                            <strong>{{ $bid->artisan->first_name }} {{ $bid->artisan->last_name
                                                }}</strong><br>
                                            <small class="text-muted">{{ $bid->artisan->artisanCategory->name }}</small>
                                        </td>
                                        <td>{{ format_money($bid->amount) }}</td>
                                        <td>{{ $bid->duration }}</td>
                                        <td>{{ Str::limit($bid->proposal, 40) }}</td>
                                        <td>
                                            @if($bid->status == 'pending')
                                            <form action="{{ route('artisan.bids.accept', $bid) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm btn-link"
                                                    title="Accept Bid">
                                                    <i class="fa fa-check"></i> Accept
                                                </button>
                                            </form>
                                            @else
                                            <span
                                                class="badge badge-{{ $bid->status == 'accepted' ? 'success' : 'danger' }}">{{
                                                ucfirst($bid->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No bids received yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-4">
                <div class="card card-user">
                    <div class="card-header">
                        <h5 class="card-title">Landlord Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="author">
                            <h5 class="title text-primary">{{ $task->landlord->first_name }} {{
                                $task->landlord->last_name }}</h5>
                            <p class="description">
                                Property: {{ $task->complaint->apartment->property->address }}
                            </p>
                        </div>
                        <p class="description text-center">
                            {{ $task->complaint->city }}
                        </p>
                    </div>
                    <hr>
                    <div class="card-footer text-center">
                        <small class="text-muted">Task posted on {{ $task->created_at->format('M j, Y') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection