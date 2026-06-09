@extends('layout')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h4 class="card-title d-flex align-items-center flex-wrap mb-1">
                                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm mr-3 mb-1">
                                    <i class="fa fa-arrow-left mr-1"></i> Back
                                </a>
                                <span class="mb-1">{{ optional($task->complaint)->title ?? 'Untitled Task' }}</span>
                            </h4>
                            <span class="badge badge-{{ $task->status == 'open' ? 'success' : 'secondary' }}">
                                {{ ucfirst($task->status) }}
                            </span>
                            @if($task->payment_status === 'escrowed')
                            <span class="badge badge-warning ml-2" title="Funds are held securely by EasyRent until work is completed">
                                <i class="fa fa-lock"></i> Funds in Escrow
                            </span>
                            @endif
                        </div>
                        <p class="card-category">Category: {{ optional(optional($task->complaint)->category)->name ?? 'Uncategorized' }}</p>
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
                                        <label>My Quote ({{ format_money(0)->getSymbol() }})</label>
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
                        <div class="d-flex flex-column mt-4 text-center">
                            <button type="button" class="btn btn-success btn-block p-2 mb-2" data-toggle="modal" data-target="#completeTaskModal">
                                <i class="fa fa-check-double mr-2"></i> Mark Work as Completed
                            </button>
                            <button type="button" class="btn btn-danger btn-block p-2 mt-2" data-toggle="modal" data-target="#cancelTaskModal">
                                <i class="fa fa-times mr-2"></i> Cancel Assignment / File Complaint
                            </button>
                        </div>
                        @endif
                        @endif

                        @if($task->status == 'completed')
                            @php
                                $acceptedBid = $task->bids()->where('status', 'accepted')->first();
                                $artisan = $acceptedBid ? $acceptedBid->artisan : null;
                                $isAuthParty = (auth()->id() == $task->landlord_id) || (auth()->id() == $task->tenant_id);
                                $hasRated = $artisan ? \App\Models\ArtisanRating::where('task_id', $task->id)->where('user_id', auth()->id())->exists() : false;
                            @endphp

                            @if($isAuthParty && $artisan && !$hasRated)
                            <hr>
                            <div class="text-center mt-4 p-3 border rounded bg-light">
                                <h5><i class="fa fa-star text-warning"></i> Rate Artisan: {{ $artisan->first_name }} {{ $artisan->last_name }}</h5>
                                <form action="{{ route('artisan.rate') }}" method="POST" id="artisanRatingForm">
                                    @csrf
                                    <input type="hidden" name="artisan_id" value="{{ $artisan->user_id }}">
                                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                                    
                                    <div class="form-group mt-3">
                                        <label>Select Rating</label>
                                        <select name="rating" class="form-control mx-auto" style="width:150px;" required>
                                            <option value="">-- Stars --</option>
                                            <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                                            <option value="4">⭐⭐⭐⭐ (4/5)</option>
                                            <option value="3">⭐⭐⭐ (3/5)</option>
                                            <option value="2">⭐⭐ (2/5)</option>
                                            <option value="1">⭐ (1/5)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Comment (Optional)</label>
                                        <textarea name="comment" class="form-control" rows="2" placeholder="How was the service?"></textarea>
                                    </div>
                                    <button type="button" class="btn btn-warning btn-fill" onclick="submitArtisanRating()">Submit Rating</button>
                                </form>
                                <div id="artisanRatingMessage" class="mt-2"></div>
                            </div>
                            <script>
                                function submitArtisanRating() {
                                    var form = $('#artisanRatingForm');
                                    $.ajax({
                                        url: form.attr('action'),
                                        method: 'POST',
                                        data: form.serialize(),
                                        success: function(res) {
                                            if(res.success) {
                                                $('#artisanRatingForm').hide();
                                                $('#artisanRatingMessage').html('<div class="alert alert-success">' + res.message + '</div>');
                                            } else {
                                                $('#artisanRatingMessage').html('<div class="alert alert-danger">' + res.message + '</div>');
                                            }
                                        },
                                        error: function(err) {
                                            $('#artisanRatingMessage').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                                        }
                                    });
                                }
                            </script>
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
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle mr-2"></i> <strong>Warning:</strong> For security reasons and to ensure quality service, please ensure that you only employ <strong>verified artisans</strong>.
                        </div>
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
                                            <strong>
                                                <a href="javascript:void(0)" class="view-artisan-details text-primary"
                                                   data-name="{{ $bid->artisan->first_name }} {{ $bid->artisan->last_name }}"
                                                   data-contact="{{ $bid->artisan->phone }} | {{ $bid->artisan->email }}"
                                                   data-location="{{ $bid->artisan->city }}, {{ $bid->artisan->state }}"
                                                   data-rating="{{ number_format($bid->artisan->artisanRatings()->avg('rating') ?? 0.5, 1) }}"
                                                   data-reviews="{{ $bid->artisan->artisanRatings()->count() }}"
                                                   data-verified="{{ $bid->artisan->is_artisan_verified ? 1 : 0 }}"
                                                >
                                                    {{ $bid->artisan->first_name }} {{ $bid->artisan->last_name }}
                                                </a>
                                                @if($bid->artisan->is_artisan_verified)
                                                    <span class="er-badge er-badge-verified" title="Verified Artisan"><i class="fa fa-check"></i></span>
                                                @endif
                                            </strong><br>
                                            <small class="text-muted">{{ optional($bid->artisan->artisanCategory)->name ?? 'Uncategorized' }}</small>
                                        </td>
                                        <td>{{ format_money($bid->amount) }}</td>
                                        <td>{{ $bid->duration }}</td>
                                        <td>{{ Str::limit($bid->proposal, 40) }}</td>
                                        <td>
                                            @if($bid->status == 'pending')
                                            <form action="{{ route('artisan.bids.accept', $bid) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('You will be redirected to Paystack to secure the funds for this task. A 3.5% processing and platform fee will be added to the bid amount. Do you want to proceed?');">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm btn-link"
                                                    title="Accept Bid">
                                                    <i class="fa fa-check"></i> Accept & Pay
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
                                Property: {{ optional(optional(optional($task->complaint)->apartment)->property)->address ?? 'Address Unavailable' }}
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

<!-- Artisan Details Modal -->
<div class="modal fade" id="artisanDetailsModal" tabindex="-1" role="dialog" aria-labelledby="artisanDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="artisanDetailsModalLabel">Artisan Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h5 id="modalArtisanName" class="font-weight-bold text-primary mb-1"></h5>
                    <div id="modalArtisanRating" class="text-warning mb-2" style="font-size: 1.2rem;"></div>
                    <small id="modalArtisanReviews" class="text-muted"></small>
                </div>
                <hr>
                <div class="mb-3">
                    <strong><i class="fa fa-address-book text-muted mr-2"></i> Contact:</strong>
                    <span id="modalArtisanContact" class="d-block mt-1 pl-4"></span>
                </div>
                <div class="mb-3">
                    <strong><i class="fa fa-map-marker-alt text-muted mr-2"></i> Location:</strong>
                    <span id="modalArtisanLocation" class="d-block mt-1 pl-4"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.view-artisan-details').on('click', function() {
        var btn = $(this);
        var name = btn.data('name');
        if (btn.data('verified') == 1) {
            name += ' <span class="er-badge er-badge-verified" title="Verified Artisan"><i class="fa fa-check"></i></span>';
            $('#modalArtisanName').html(name);
        } else {
            $('#modalArtisanName').text(name);
        }
        $('#modalArtisanContact').text(btn.data('contact'));
        $('#modalArtisanLocation').text(btn.data('location'));
        
        var rating = parseFloat(btn.data('rating'));
        var reviews = parseInt(btn.data('reviews'));
        
        var stars = '';
        for (var i = 1; i <= 5; i++) {
            if (rating >= i) {
                stars += '<i class="fa fa-star"></i>';
            } else if (rating >= (i - 0.5)) {
                stars += '<i class="fa fa-star-half-alt"></i>';
            } else {
                stars += '<i class="far fa-star"></i>';
            }
        }
        
        $('#modalArtisanRating').html(stars + ' <span class="text-dark font-weight-bold ml-1">' + rating.toFixed(1) + '</span>');
        $('#modalArtisanReviews').text('(' + reviews + ' reviews)');
        
        $('#artisanDetailsModal').modal('show');
    });
});
</script>
@endpush

<!-- Complete Task Modal -->
<div class="modal fade" id="completeTaskModal" tabindex="-1" role="dialog" aria-labelledby="completeTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="completeTaskModalLabel">Complete Artisan Task & Rate</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('artisan.tasks.complete', $task) }}" method="POST">
                @csrf
                <div class="modal-body text-left">
                    <p>Are you sure you want to mark this task as completed? You can also rate the artisan's service below:</p>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Select Rating (Optional)</label>
                        <select name="rating" class="form-control">
                            <option value="">-- Rate Artisan --</option>
                            <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                            <option value="4">⭐⭐⭐⭐ (4/5)</option>
                            <option value="3">⭐⭐⭐ (3/5)</option>
                            <option value="2">⭐⭐ (2/5)</option>
                            <option value="1">⭐ (1/5)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Review Comment (Optional)</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="Leave a review comment for the artisan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete & Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Task Modal -->
<div class="modal fade" id="cancelTaskModal" tabindex="-1" role="dialog" aria-labelledby="cancelTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-danger" id="cancelTaskModalLabel">Cancel Task Assignment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('artisan.tasks.cancel', $task) }}" method="POST">
                @csrf
                <div class="modal-body text-left">
                    <p class="text-danger font-weight-bold"><i class="fa fa-exclamation-triangle"></i> Cancel task and submit a complaint about the artisan's service.</p>
                    
                    <div class="form-group">
                        <label class="font-weight-bold text-danger">Reason for Cancellation / Complaint <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason or complaint details (required)..." required minlength="5"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Rate Artisan (Optional)</label>
                        <select name="rating" class="form-control">
                            <option value="">-- Rate Artisan --</option>
                            <option value="1">⭐ Poor Service (1/5)</option>
                            <option value="2">⭐⭐ Fair (2/5)</option>
                            <option value="3">⭐⭐⭐ Good (3/5)</option>
                            <option value="4">⭐⭐⭐⭐ Very Good (4/5)</option>
                            <option value="5">⭐⭐⭐⭐⭐ Excellent (5/5)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Additional Comments (Optional)</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="Any additional comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection