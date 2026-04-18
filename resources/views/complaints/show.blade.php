@extends('layout')

@section('content')

    <div class="content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">
                                    <i class="nc-icon nc-support-17"></i>
                                    Complaint #{{ $complaint->complaint_number }}
                                </h4>
                                <div>
                                    <span class="badge badge-{{ $complaint->status_color }} badge-lg mr-2">
                                        {{ $complaint->status_formatted }}
                                    </span>
                                    <span class="badge badge-{{ $complaint->priority_color }} badge-lg">
                                        {{ $complaint->priority_formatted }} Priority
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-md-8">
                    <!-- Complaint Details -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="{{ $complaint->category->icon }} text-{{ $complaint->priority_color }}"></i>
                                {{ $complaint->title }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6>Description:</h6>
                                <p class="text-muted">{{ $complaint->description }}</p>
                            </div>

                            @if($complaint->attachments->count() > 0)
                                <div class="mb-4">
                                    <h6>Attachments:</h6>
                                    <div class="row">
                                        @foreach($complaint->attachments as $attachment)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="attachment-item border rounded p-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="{{ $attachment->file_icon }} text-primary mr-2"
                                                            style="font-size: 1.5rem;"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="font-weight-bold">{{ $attachment->original_name }}</div>
                                                            <small class="text-muted">{{ $attachment->file_size_formatted }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <a href="{{ $attachment->file_url }}" target="_blank"
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="nc-icon nc-zoom-split"></i> View
                                                        </a>
                                                        <a href="{{ $attachment->file_url }}" download
                                                            class="btn btn-sm btn-outline-secondary">
                                                            <i class="nc-icon nc-cloud-download-93"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($complaint->resolution_notes && $complaint->isResolved())
                                <div class="alert alert-success">
                                    <h6><i class="nc-icon nc-check-2"></i> Resolution Notes:</h6>
                                    <p class="mb-0">{{ $complaint->resolution_notes }}</p>
                                    @if($complaint->resolved_by)
                                                        <small class="text-muted">
                                                            Resolved by {{ $complaint->resolvedBy->first_name }} {{
                                        $complaint->resolvedBy->last_name }}
                                                            on {{ $complaint->resolved_at->format('M j, Y \a\t g:i A') }}
                                                        </small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Updates/Comments -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="nc-icon nc-chat-33"></i> Updates & Comments
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($updates->count() > 0)
                                <div class="timeline">
                                    @foreach($updates as $update)
                                        <div class="timeline-item {{ $update->is_internal ? 'internal' : '' }}">
                                            <div class="timeline-marker">
                                                <i class="{{ $update->update_icon }} text-{{ $update->update_color }}"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong>{{ $update->user->first_name }} {{ $update->user->last_name
                                                            }}</strong>
                                                        <span class="badge badge-{{ $update->update_color }} badge-sm ml-2">
                                                            {{ $update->update_type_formatted }}
                                                        </span>
                                                        @if($update->is_internal)
                                                            <span class="badge badge-secondary badge-sm ml-1">Internal</span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ $update->created_at->format('M j, Y g:i A')
                                                        }}</small>
                                                </div>
                                                <p class="mt-2 mb-0">{{ $update->message }}</p>
                                                @if($update->old_value && $update->new_value)
                                                    <small class="text-muted">
                                                        Changed from "{{ $update->old_value }}" to "{{ $update->new_value }}"
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No updates yet.</p>
                            @endif

                            <!-- Add Comment Form -->
                            @if(!$complaint->isResolved() || auth()->user()->isAgent() || auth()->user()->admin)
                                <div class="mt-4 pt-4 border-top">
                                    <h6>Add Comment:</h6>
                                    <form action="{{ route('complaints.comment', $complaint) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <textarea name="message" class="form-control" rows="3"
                                                placeholder="Add your comment or update..." required></textarea>
                                        </div>
                                        @if(auth()->user()->isAgent() || auth()->user()->admin)
                                            <div class="form-check mb-3">
                                                <input type="checkbox" name="is_internal" value="1" class="form-check-input"
                                                    id="internal">
                                                <label class="form-check-label" for="internal">
                                                    Internal note (not visible to tenant/landlord)
                                                </label>
                                            </div>
                                        @endif
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="nc-icon nc-send"></i> Add Comment
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">

                    <!-- Actions -->
                    @if(auth()->user()->isLandlord() || auth()->user()->isAgent() || auth()->user()->admin)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="nc-icon nc-settings-gear-65"></i> Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Status Update -->
                                @if(!$complaint->isResolved())
                                                <form action="{{ route('complaints.status', $complaint) }}" method="POST" class="mb-3">
                                                    @csrf
                                                    <div class="form-group">
                                                        <label for="status">Update Status:</label>
                                                        <select name="status" id="status" class="form-control form-control-sm">
                                                            <option value="open" {{ $complaint->status === 'open' ? 'selected' : '' }}>Open
                                                            </option>
                                                            <option value="in_progress" {{ $complaint->status === 'in_progress' ? 'selected' :
                                    '' }}>In Progress</option>
                                                            <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : ''
                                                }}>Resolved</option>
                                                            <option value="closed" {{ $complaint->status === 'closed' ? 'selected' : ''
                                                }}>Closed</option>
                                                            <option value="escalated" {{ $complaint->status === 'escalated' ? 'selected' : ''
                                                }}>Escalated</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <textarea name="notes" class="form-control form-control-sm" rows="2"
                                                            placeholder="Optional notes about status change..."></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-info btn-sm btn-block">
                                                        <i class="nc-icon nc-refresh-69"></i> Update Status
                                                    </button>
                                                </form>
                                @endif

                                <!-- Assignment -->
                                @if(auth()->user()->isLandlord() || auth()->user()->admin)
                                    <form action="{{ route('complaints.assign', $complaint) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label for="assigned_to">Assign To:</label>
                                            <select name="assigned_to" id="assigned_to" class="form-control form-control-sm">
                                                <option value="">Select assignee...</option>
                                                <!-- You would populate this with agents/property managers -->
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm btn-block">
                                            <i class="nc-icon nc-single-02"></i> Assign
                                        </button>
                                    </form>
                                @endif

                                <!-- Artisan Task Creation -->
                                @if((auth()->user()->isLandlord() || auth()->user()->admin) && !$complaint->artisanTask)
                                    <div class="mt-3 card-footer">
                                        <button type="button" class="btn btn-warning btn-block" data-toggle="modal"
                                            data-target="#getArtisanModal">
                                            <i class="nc-icon nc-settings"></i> Get Artisan
                                        </button>
                                    </div>
                                @elseif($complaint->artisanTask)
                                    <div class="mt-3">
                                        <div class="alert alert-info py-2 px-3">
                                            <small><i class="nc-icon nc-delivery-fast"></i> Artisan request active</small>
                                            <br>
                                            <a href="{{ route('artisan.tasks.show', $complaint->artisanTask) }}"
                                                class="btn btn-link btn-sm p-0 text-white">View Task Details</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <!-- Complaint Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="nc-icon nc-bullet-list-67"></i> Complaint Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item mb-3">
                                <strong>Category:</strong>
                                <div class="mt-1">
                                    <i
                                        class="{{ $complaint->category->icon }} text-{{ $complaint->category->priority_color }}"></i>
                                    {{ $complaint->category->name }}
                                </div>
                            </div>

                            <div class="info-item mb-3">
                                <strong>Property:</strong>
                                <div class="mt-1">{{ $complaint->apartment->property->address }}</div>
                            </div>

                            <div class="info-item mb-3">
                                <strong>Apartment:</strong>
                                <div class="mt-1">{{ $complaint->apartment->apartment_type ?? 'N/A' }}</div>
                            </div>

                            <div class="info-item mb-3">
                                <strong>Tenant:</strong>
                                <div class="mt-1">{{ $complaint->tenant->first_name }} {{ $complaint->tenant->last_name }}
                                </div>
                            </div>

                            <div class="info-item mb-3">
                                <strong>Landlord:</strong>
                                <div class="mt-1">{{ $complaint->landlord->first_name }} {{ $complaint->landlord->last_name
                                    }}</div>
                            </div>

                            @if($complaint->assigned_to)
                                                <div class="info-item mb-3">
                                                    <strong>Assigned To:</strong>
                                                    <div class="mt-1">{{ $complaint->assignedTo->first_name }} {{
                                $complaint->assignedTo->last_name }}</div>
                                                </div>
                            @endif

                            <div class="info-item mb-3">
                                <strong>Created:</strong>
                                <div class="mt-1">{{ $complaint->created_at->format('M j, Y \a\t g:i A') }}</div>
                            </div>

                            <div class="info-item mb-3">
                                <strong>Age:</strong>
                                <div class="mt-1">{{ $complaint->age_in_hours }} hours</div>
                            </div>

                            @if($complaint->isOverdue())
                                <div class="alert alert-warning">
                                    <i class="nc-icon nc-time-alarm"></i>
                                    <strong>Overdue!</strong><br>
                                    This complaint is past its expected resolution time.
                                </div>
                            @endif
                        </div>
                    </div>


                    <!-- Back Button -->
                    <div class="card">
                        <div class="card-body text-center">
                            <a href="{{ route('complaints.index') }}" class="btn btn-secondary">
                                <i class="nc-icon nc-minimal-left"></i> Back to Complaints
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e3e3e3;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-item.internal {
            opacity: 0.8;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border-left: 3px solid #6c757d;
        }

        .timeline-marker {
            position: absolute;
            left: -22px;
            top: 5px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e3e3e3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .timeline-content {
            background: white;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e3e3e3;
        }

        .info-item {
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .attachment-item {
            transition: all 0.2s ease;
        }

        .attachment-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>

    @if((auth()->user()->isLandlord() || auth()->user()->admin || auth()->user()->isTenant()) && !$complaint->artisanTask)
        <!-- Get Artisan Modal -->
        <div class="modal fade" id="getArtisanModal" tabindex="-1" role="dialog" aria-labelledby="getArtisanModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('artisan.tasks.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="complaint_id" value="{{ $complaint->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title" id="getArtisanModalLabel">Find an Artisan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-left">
                            <p class="text-muted small">Post this complaint to the artisan marketplace to get bids from
                                qualified professionals.</p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group text-left">
                                        <label>Min Budget
                                            ({{ $complaint->apartment->property->currency->symbol ?? format_money(0)->getSymbol() }})</label>
                                        <input type="number" name="budget_min" class="form-control" placeholder="e.g. 5000"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group text-left">
                                        <label>Max Budget
                                            ({{ $complaint->apartment->property->currency->symbol ?? format_money(0)->getSymbol() }})</label>
                                        <input type="number" name="budget_max" class="form-control" placeholder="e.g. 15000"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-left">
                                <label>Expected Duration</label>
                                <select name="duration" class="form-control" required>
                                    <option value="1-3 hours">1-3 hours</option>
                                    <option value="Same day">Same day</option>
                                    <option value="1-2 days">1-2 days</option>
                                    <option value="Within a week">Within a week</option>
                                </select>
                            </div>

                            @if(auth()->user()->isTenant())
                                <div class="form-group text-left border p-2 rounded bg-light">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="request_setoff"
                                            name="request_setoff" value="1">
                                        <label class="custom-control-label font-weight-bold" for="request_setoff">Request Rent
                                            Set-off</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">If checked, the cost of this repair will be deducted from
                                        your next rent payment (subject to landlord approval).</small>
                                </div>
                            @endif

                            <div class="form-group text-left">
                                <label>Detailed Requirements (Optional)</label>
                                <textarea name="description" class="form-control" rows="3"
                                    placeholder="Provide additional details for the artisan...">{{ $complaint->description }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Post to Marketplace</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection