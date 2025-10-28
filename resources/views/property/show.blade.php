@extends('layout')

@section('content')
<!-- Add jQuery, Bootstrap JS, and SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/apartment-functions.js') }}"></script>

<style>
.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
.alert-info {
    background-color: #e7f3ff;
    border-color: #b8daff;
}
.security-notice {
    animation: fadeIn 0.5s ease-in;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@php
    $types = [
        1 => 'Mansion',
        2 => 'Duplex',
        3 => 'Flat',
        4 => 'Terrace'
    ];
@endphp


<div class="content">
    @if(auth()->user()->user_id != $property->user_id && !auth()->user()->admin)
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info border-left-info security-notice">
                <div class="d-flex align-items-center">
                    <i class="fa fa-info-circle fa-2x text-info me-3"></i>
                    <div>
                        <h6 class="mb-1"><i class="fa fa-shield-alt me-1"></i>Limited Access Notice</h6>
                        <p class="mb-0">You are viewing this property with restricted permissions. Some actions may not be available to you.</p>
                        @if(auth()->user()->role == 5)
                            <small class="text-muted"><i class="fa fa-user-tie me-1"></i>Regional Manager View</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="card-title mb-2">Property Details</h4>
                            @if($property->agent_id)
                                <button type="button" class="btn btn-info btn-sm" onclick="viewAgent('{{ $property->agent_id }}')">
                                    <i class="fa fa-user-tie"></i> View Property Manager
                                </button>
                            @else
                                @if(auth()->user()->user_id == $property->user_id)
                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#agentModal">
                                        <i class="fa fa-user-tie"></i> Assign Property Manager
                                    </button>
                                @elseif(auth()->user()->admin)
                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#agentModal">
                                        <i class="fa fa-user-tie"></i> Admin Assign Manager
                                    </button>
                                @else
                                    <span class="badge bg-secondary text-white px-2 py-1">
                                        <i class="fa fa-user-tie me-1"></i>No Manager Assigned
                                    </span>
                                @endif
                            @endif
                        </div>
                        <div class="btn-group">
                            <a href="{{ url('/dashboard/myproperty') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to My Properties
                            </a>
                            @if(auth()->user()->user_id == $property->user_id)
                                <button type="button" class="btn btn-warning btn-sm" onclick="editProperty('{{ $property->prop_id }}')">
                                    <i class="fa fa-edit"></i> Edit Property
                                </button>
                                <form action="{{ url('/dashboard/property/' . $property->prop_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this property?')">
                                        <i class="fa fa-trash"></i> Delete Property
                                    </button>
                                </form>
                            @elseif(auth()->user()->admin)
                                <button type="button" class="btn btn-warning btn-sm" onclick="editProperty('{{ $property->prop_id }}')">
                                    <i class="fa fa-edit"></i> Admin Edit
                                </button>
                                <form action="{{ url('/dashboard/property/' . $property->prop_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this property? This action cannot be undone.')">
                                        <i class="fa fa-trash"></i> Admin Delete
                                    </button>
                                </form>
                            @else
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-info text-white px-3 py-2 me-2">
                                        <i class="fa fa-eye me-1"></i>View Only Access
                                    </span>
                                    @if(auth()->user()->role == 5) {{-- Regional Manager --}}
                                        <small class="text-muted">Regional Manager View</small>
                                    @else
                                        <small class="text-muted">Limited Access</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Property ID</label>
                                <p class="form-control-static">{{ $property->prop_id }}</p>
                            </div>
                            <div class="form-group">
                                <label>Property Type</label>
                                <p class="form-control-static">{{ $property->getPropertyTypeName() }}</p>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <p class="form-control-static">{{ $property->getFullAddress() }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Owner</label>
                                <div class="d-flex align-items-center">
                                    <span class="mr-2">{{ $property->owner->name ?? 'N/A' }}</span>
                                   
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Number of Apartments</label>
                                <p class="form-control-static">{{ $property->no_of_apartment }}</p>
                            </div>
                            <div class="form-group">
                                <label>Date Created</label>
                                <p class="form-control-static">{{ date('M d, Y', strtotime($property->date_created)) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Apartments Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Apartments</h4>
                        <div class="d-flex gap-3">
                            <div class="small text-muted">
                                Total Units: {{ $apartments->count() }}
                            </div>
                            <div class="small text-muted">
                                Occupied: {{ $apartments->where('tenant_id', '!=', null)->count() }}
                            </div>
                            <div class="small text-muted">
                                Vacant: {{ $apartments->where('tenant_id', null)->count() }}
                            </div>
                            @if(auth()->user()->user_id == $property->user_id)
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#apartmentModal">
                                    <i class="fa fa-plus"></i> Add Apartment
                                </button>
                            @elseif(auth()->user()->admin)
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#apartmentModal">
                                    <i class="fa fa-plus"></i> Admin Add Apartment
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="text-primary">
                                <tr> 
                                    <th>Type</th>
                                    <th>Tenant</th>
                                    <th>Duration</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($apartments as $apartment)
                                    @php 
                                            $now = now();
                                            $daysUntilExpiry = $apartment->range_end ? now()->diffInDays($apartment->range_end, false) : null;
                                            
                                            $status = match(true) {
                                                !$apartment->tenant_id => 'vacant',
                                                $now > $apartment->range_end => 'expired',
                                                $now < $apartment->range_start => 'upcoming',
                                                $daysUntilExpiry <= 30 => 'expiring-soon',
                                                default => 'active'
                                            };
                                            
                                            $statusClass = match($status) {
                                                'vacant' => 'danger',
                                                'expired' => 'dark',
                                                'upcoming' => 'info',
                                                'expiring-soon' => 'warning',
                                                'active' => 'success'
                                            }; 
                                    @endphp
                                <tr> 
                                    <td>{{ $apartment->apartment_type }}</td>  <!-- Apartment type -->
                                    <td>{{ $apartment->tenant ? $apartment->tenant->first_name . ' ' . $apartment->tenant->last_name : 'Vacant' }}</td>  <!-- Tenant name -->
                                    <td>
                                        @php
                                            $profoma = \App\Models\ProfomaReceipt::where('apartment_id', $apartment->apartment_id)->first();
                                        @endphp
                                        {{ $profoma ? ($profoma->duration ? $profoma->duration.' months' : 'N/A') : ($apartment->duration ?? 'N/A') }}
                                    </td>  <!-- Duration (profoma if exists) -->
                                    <td>{{ $apartment->range_start ? date('M d, Y', strtotime($apartment->range_start)) : 'N/A' }}</td>
                                    <td>{{ $apartment->range_end ? date('M d, Y', strtotime($apartment->range_end)) : 'N/A' }}</td>
                                    <td>{{ $apartment->amount ? '₦'.number_format($apartment->amount, 2) : 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $statusClass }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info btn-sm" onclick="viewApartment('{{ $apartment->apartment_id }}')">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            @if(auth()->user()->user_id == $property->user_id)
                                                <button type="button" class="btn btn-warning btn-sm" onclick="editApartment('{{ $apartment->apartment_id }}')">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteApartment('{{ $apartment->id }}')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @elseif(auth()->user()->admin)
                                                <button type="button" class="btn btn-warning btn-sm" onclick="editApartment('{{ $apartment->apartment_id }}')">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteApartment('{{ $apartment->id }}')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
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
</div>

<!-- Add/Edit Apartment Modal -->
<div class="modal fade" id="apartmentModal" tabindex="-1" role="dialog" aria-labelledby="apartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="apartmentModalLabel">Add Apartment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="apartmentMessage"></div>
                <form id="apartmentForm" class="p-3" action="/apartment" method="post">
                    @csrf
                    <input type="hidden" name="propertyId" value="{{ $property->prop_id }}">
                    <div id="apartmentFormFields" class="form-vertical">
                        <div class="form-group">
                            <label>Apartment Type</label>
                            <select class="form-control" name="apartmentType" required>
                                <option value="" disabled selected>Select Type</option>
                                <option value="Studio">Studio</option>
                                <option value="1-Bedroom">1-Bedroom</option>
                                <option value="2-Bedroom">2-Bedroom</option>
                                <option value="3-Bedroom">3-Bedroom</option>
                                <option value="Penthouse">Penthouse</option>
                                <option value="Duplex">Duplex</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tenant ID (Optional)</label>
                            <input type="text" class="form-control" name="tenantId" 
                                placeholder="Enter tenant ID if occupied">
                        </div>

                        <div class="form-group">
                            <label>Duration</label>
                            <select class="form-control" name="duration" required>
                                <option value="">Select Duration</option>
                                <option value="1">Monthly</option>
                                <option value="3">Quarterly</option>
                                <option value="6">Semi-Annual</option>
                                <option value="12">Annual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" class="form-control" name="fromDate" 
                                placeholder="Select start date" required>
                        </div>


                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" class="form-control" name="toDate" 
                                placeholder="Select end date" required>
                        </div>

                        <div class="form-group">
                            <label>Price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₦</span>
                                </div>
                                <input type="text" class="form-control" name="price" id="apartmentPriceInput" placeholder="Enter rental price" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveApartment">Save Apartment</button>
            </div>
        </div>
    </div>
</div>

<!-- Agent Selection Modal -->
<div class="modal fade" id="agentModal" tabindex="-1" role="dialog" aria-labelledby="agentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agentModalLabel">Select Property Manager</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="agentForm" action="/dashboard/property/{{ $property->prop_id }}/assign-agent" method="POST">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $property->prop_id }}">
                    
                    <!-- Previously Used Property Managers -->
                    @if($previousAgents->isNotEmpty())
                    <div class="form-group">
                        <label for="previous_agent_id" class="text-info">Prefer a previously used Property Manager?</label>
                        <select class="form-control" name="previous_agent_id" id="previous_agent_id">
                            <option value="">Select from previous manager...</option>
                            @foreach($previousAgents as $agent)
                                <option value="{{ $agent->user_id }}">
                                    {{ $agent->first_name }} {{ $agent->last_name }} ({{ $agent->username }}) - {{ $agent->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-center my-2" style="font-weight:600; color:#888;">— Or —</div>
                    @endif

                    <!-- Manual Property Manager ID Entry -->
                    <div class="form-group">
                        <small class="form-text text-muted">If you've not used a property manager previously but you have their ID.</small>
                        <label for="manual_agent_id"  class="text-info"> Enter Property Manager ID</label>
                        <input type="text" class="form-control" name="manual_agent_id" id="manual_agent_id" placeholder="Enter property manager ID manually">
                    </div>

                    <div class="text-center my-2" style="font-weight:600; color:#888;">— Or —</div>
                    <!-- Find a Verified Property Manager Button -->
                    <div class="form-group text-center">
                        <small class="form-text text-muted">Search and assign a verified property manager if you don't know their ID.</small>
                        <button type="button" class="btn btn-outline-info btn-block" id="openVerifiedAgentPanelFromModal">
                            <i class="fa fa-search"></i> Find a verified property manager if you don't know their ID.
                        </button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Property Manager</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Property Manager Details Modal -->
<div class="modal fade" id="agentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="agentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agentDetailsModalLabel">Property Manager Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="agentDetailsBody">
                <div class="text-center">
                    <span class="spinner-border spinner-border-sm"></span> Loading property manager details...
                </div>
                <div id="agentRatingSection" class="mt-4" style="display:none;">
                    <div class="agent-rating-summary mb-2 text-center">
                        <span id="agentAverageRating" style="font-size:1.2em;"></span>
                        <span id="agentAverageRatingText" class="ml-2 text-muted"></span>
                    </div>
                    <div id="agentReviewsList" class="mb-3"></div>
                    <div id="agentRatingFormContainer" class="mb-2" style="display:none;">
                        <hr>
                        <form id="agentRatingForm">
                            <div class="form-group text-center">
                                <label>Rate this Property Manager:</label><br>
                                <span id="agentRatingStars">
                                    <!-- Stars will be rendered here -->
                                </span>
                                <input type="hidden" name="rating" id="agentRatingInput" value="0">
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" name="comment" id="agentRatingComment" rows="2" maxlength="1000" placeholder="Leave a comment (optional)"></textarea>
                            </div>
                            <input type="hidden" name="agent_id" id="agentRatingAgentId">
                            <input type="hidden" name="property_id" id="agentRatingPropertyId">
                            <button type="submit" class="btn btn-primary btn-block">Submit Rating</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="removeAgentBtn" style="display:none;">Remove Property Manager</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Slide-in Verified Agent Finder Panel (discovery only, no manual ID) -->
<div id="verifiedAgentPanel" class="verified-agent-panel" style="display:none;position:fixed;top:0;right:0;width:400px;max-width:100vw;height:100vh;z-index:1050;background:#fff;box-shadow:-2px 0 10px rgba(0,0,0,0.2);overflow-y:auto;transition:right 0.4s;">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
    <h5 class="mb-0">Find a Verified Property Manager</h5>
        <button type="button" class="close" id="closeVerifiedAgentPanel" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="p-3">
    <div class="mb-3 text-muted small">Search for a verified property manager by name or city. All fields are optional.</div>
        <form id="agentSearchForm">
            <div class="form-group">
                <label for="states">State</label>
                <select name="state" id="states" class="form-control" onchange="getCities()">
                    <option value="" disabled="disabled" selected>Select State</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location['name'] }}">{{ $location['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="agentCity">City</label>
                <select name="city" id="agentCity" class="form-control">
                    <option value="" disabled="disabled" selected>Select City</option>
                </select>
            </div>
            <div class="form-group">
                <label for="agentName">Name</label>
                <input type="text" class="form-control" id="agentName" name="name" placeholder="Property manager name">
            </div>
            <!-- <div class="form-group">
                <label for="agentCity">City</label>
                <input type="text" class="form-control" id="agentCity" name="city" placeholder="City">
            </div> 
            <div class="form-group">
                <label for="agentSpecialty">Specialty</label>
                <input type="text" class="form-control" id="agentSpecialty" name="specialty" placeholder="e.g. Residential, Commercial">
            </div>-->
            <button type="submit" class="btn btn-primary btn-block">Search</button>
        </form>
        <div id="agentSearchResults" class="mt-4"></div>
    </div>
</div>
<style>
.verified-agent-panel { right: -400px; }
.verified-agent-panel.open { right: 0; }
@media (max-width: 500px) { .verified-agent-panel { width: 100vw; } }
</style>
<script>
    $(function() {
        // Open panel
        $('#findVerifiedAgentLink').on('click', function(e) {
            e.preventDefault();
            $('#verifiedAgentPanel').show().addClass('open');
            $('body').css('overflow', 'hidden');
        });
        // Close panel
        $('#closeVerifiedAgentPanel').on('click', function() {
            $('#verifiedAgentPanel').removeClass('open');
            setTimeout(function(){ $('#verifiedAgentPanel').hide(); $('body').css('overflow', ''); }, 400);
        });
        // AJAX search
        $('#agentSearchForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var resultsDiv = $('#agentSearchResults');
            resultsDiv.html('<div class="text-center"><span class="spinner-border spinner-border-sm"></span> Searching...</div>');
            $.ajax({
                url: '/dashboard/agents/search',
                method: 'GET',
                data: form.serialize(),
                success: function(data) {
                    if (data.length === 0) {
                        resultsDiv.html('<div class="alert alert-warning">No property managers found.</div>');
                        return;
                    }
                    var html = '<div class="row">';
                    data.forEach(function(agent) {
                        html += '<div class="col-12 mb-3">';
                        html += '<div class="card">';
                        html += '<div class="card-body d-flex align-items-center justify-content-between">';
                        html += '<div><strong>' + agent.first_name + ' ' + agent.last_name + '</strong><br>';
                        // Show average rating as stars
                        html += '<span>' + renderStars(agent.average_rating || 0) + '</span>';
                        html += '<small class="d-block mt-1">' + (agent.email || '') + ' | ' + (agent.phone || '') + '<br>';
                        html += (agent.lga ? agent.lga + ', ' : '') + (agent.state || '') + '<br>';
                        html += (agent.occupation ? '<span class="badge badge-info">' + agent.occupation + '</span>' : '') + '</small></div>';
                        html += '<button class="btn btn-success btn-sm select-agent-btn" data-agent-id="' + agent.user_id + '"><i class="fa fa-user-plus"></i> Select</button>';
                        html += '</div></div></div>';
                    });
                    html += '</div>';
                    resultsDiv.html(html);
                },
                error: function() {
                    resultsDiv.html('<div class="alert alert-danger">Failed to search agents.</div>');
                }
            });
        });
        // Select agent (assign directly)
        $(document).on('click', '.select-agent-btn', function() {
            var agentId = $(this).data('agent-id');
            var propId = "{{ $property->prop_id }}";
            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Assigning...');
            $.ajax({
                url: '/dashboard/property/' + propId + '/assign-agent',
                method: 'POST',
                data: {
                    agent_id: agentId,
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    property_id: propId
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Property Manager Assigned',
                            text: response.message || 'Property manager assigned successfully',
                            showConfirmButton: false,
                            timer: 1200
                        });
                        setTimeout(function() { location.reload(); }, 1300);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to assign property manager'
                        });
                        btn.prop('disabled', false).html('<i class="fa fa-user-plus"></i> Select');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to assign property manager. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                    btn.prop('disabled', false).html('<i class="fa fa-user-plus"></i> Select');
                }
            });
        });
        // Open panel from modal button
        $('#openVerifiedAgentPanelFromModal').on('click', function(e) {
            e.preventDefault();
            $('#agentModal').modal('hide');
            setTimeout(function() {
                $('#verifiedAgentPanel').show().addClass('open');
                $('body').css('overflow', 'hidden');
            }, 400);
        });
        // Optional: close panel on outside click
        $(document).on('mousedown', function(e) {
            var panel = $('#verifiedAgentPanel');
            if (panel.is(':visible') && !$(e.target).closest('#verifiedAgentPanel, #findVerifiedAgentLink').length) {
                panel.removeClass('open');
                setTimeout(function(){ panel.hide(); $('body').css('overflow', ''); }, 400);
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Handle select changes and manual input to ensure only one agent_id is submitted
        $('#previous_agent_id').on('change', function() {
            if ($(this).val()) {
                $('#manual_agent_id').val('');
            }
        });
        $('#manual_agent_id').on('input', function() {
            if ($(this).val()) {
                $('#previous_agent_id').val('');
            }
        });

        // AJAX submission for agent assignment
        $('#agentForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var agentId = $('#previous_agent_id').val() || $('#manual_agent_id').val();
            if (!agentId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a property manager or enter their ID'
                });
                return false;
            }
            var formData = form.serializeArray();
            formData.push({name: 'agent_id', value: agentId});
            // Disable button and show spinner
            var btn = form.find('button[type="submit"]');
            btn.prop('disabled', true);
            var originalText = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm"></span> Assigning...');
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: $.param(formData),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Property manager assigned successfully',
                            showConfirmButton: false,
                            timer: 1200
                        });
                        // Change button to view agent after short delay
                        setTimeout(function() {
                            $('#agentModal').modal('hide');
                            // Change the button to "View Property Manager"
                            var viewBtn = '<button type="button" class="btn btn-info btn-sm" onclick="viewAgent(\'' + agentId + '\')">' +
                                '<i class="fa fa-user-tie"></i> View Property Manager</button>';
                            $("[data-target='#agentModal']").replaceWith(viewBtn);
                        }, 1300);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to assign property manager'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Failed to assign property manager. Please try again.';
                    if (xhr.responseJSON) {
                        errorMessage = xhr.responseJSON.message || errorMessage;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });


        function calculateEndDate(startDate, duration) {
            const date = new Date(startDate);
            date.setMonth(date.getMonth() + parseInt(duration));
            return date.toISOString().split('T')[0];
        }

        // Use correct selectors for single apartment add form
        $('input[name="fromDate"]').on('change', function() {
            const parentDiv = $(this).closest('.form-group').parent();
            const duration = parentDiv.find('select[name="duration"]').val();
            const endDateInput = parentDiv.find('input[name="toDate"]');
            
            if (duration && this.value) {
                endDateInput.val(calculateEndDate(this.value, duration));
            }
        });

        $('select[name="duration"]').on('change', function() {
            const parentDiv = $(this).closest('.form-group').parent();
            const startDate = parentDiv.find('input[name="fromDate"]').val();
            const endDateInput = parentDiv.find('input[name="toDate"]');
            
            if (startDate) {
                endDateInput.val(calculateEndDate(startDate, this.value));
            }
        });

        // Format price input with comma separator as user types
        $('#apartmentPriceInput').on('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            let parts = value.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            this.value = parts.join('.');
        });

        // Remove formatting on form submit
        $('#apartmentForm').on('submit', function(e) {
            let priceInput = $('#apartmentPriceInput');
            let raw = priceInput.val().replace(/,/g, '');
            priceInput.val(raw);
            // Check if form is being submitted via AJAX elsewhere
            // If so, prevent default here to avoid double submission
            if ($(this).data('ajax-submit')) {
                e.preventDefault();
                return false;
            }
        });
        // Ensure Save Apartment button does not block form submission
        $('#saveApartment').off('click').on('click', function() {
            $('#apartmentForm').removeData('ajax-submit').submit();
        });

        // Apartment Modal: Enhanced UX for form submission
        $('#saveApartment').off('click').on('click', function() {
            // Disable button and show spinner
            var btn = $(this);
            btn.prop('disabled', true);
            var originalText = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            // Remove any previous alerts
            $('#apartmentMessage').html('');
            // Submit the form via AJAX
            var form = $('#apartmentForm');
            // Remove commas from price before sending
            var priceInput = $('#apartmentPriceInput');
            var rawPrice = priceInput.val().replace(/,/g, '');
            priceInput.val(rawPrice);
            var formData = form.serialize();
            // Restore formatted price for user
            priceInput.val(Number(rawPrice).toLocaleString());
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#apartmentMessage').html('<div class="alert alert-success">' + (response.message || 'Apartment added successfully!') + '</div>');
                        setTimeout(function() { location.reload(); }, 1200);
                    } else {
                        $('#apartmentMessage').html('<div class="alert alert-danger">' + (response.message || 'Failed to add apartment.') + '</div>');
                    }
                },
                error: function(xhr) {
                    let msg = 'Failed to add apartment.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        msg = '';
                        if (errors.tenantId) {
                            msg += '<div>' + errors.tenantId.join('<br>') + '</div>';
                        }
                        if (errors.price) {
                            msg += '<div>' + errors.price.join('<br>') + '</div>';
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#apartmentMessage').html('<div class="alert alert-danger">' + msg + '</div>');
                },
                complete: function() {
                    btn.prop('disabled', false);
                    btn.html(originalText);
                }
            });
        });
         // Add editProperty function for navigation to edit page
    window.editProperty = function(propId) {
        window.location.href = '/dashboard/property/' + propId + '/edit';
    }

    // Add viewApartment function for modal or navigation
    window.viewApartment = function(apartmentId) {
        // Example: navigate to apartment details page
        window.location.href = '/dashboard/apartment/' + apartmentId;
    }

    // Add editApartment function for navigation
    window.editApartment = function(apartmentId) {
        window.location.href = '/dashboard/apartment/' + apartmentId + '/edit';
    }

    // Add confirmDeleteApartment function for confirmation and AJAX delete
    window.confirmDeleteApartment = function(apartmentId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will permanently delete the apartment.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/dashboard/apartment/' + apartmentId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', 'Apartment has been deleted.', 'success');
                            setTimeout(function() { location.reload(); }, 1200);
                        } else {
                            Swal.fire('Error', response.messages || 'Failed to delete apartment.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to delete apartment.', 'error');
                    }
                });
            }
        });
    }
    });
</script>
<script>

function getCities() {
    const locations = <?=json_encode($locations)?>;
    const stateSelect = document.getElementById("states");
    const citySelect = document.getElementById("agentCity");
    const selectedState = stateSelect.value;

    // Clear existing options
    citySelect.innerHTML = '<option value="" disabled="disabled" selected>Select City</option>';

    // Find the selected state in locations
    const state = locations.find(loc => loc.name === selectedState);
    if (state && state.cities) {
        state.cities.forEach(city => {
            const option = document.createElement("option");
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
    }
}
    function viewAgent(agentId) {
        var propId = "{{ $property->prop_id }}";
        // Show modal
        $('#agentDetailsModal').modal('show');
        // Show loading spinner
    $('#agentDetailsBody').html('<div class="text-center"><span class="spinner-border spinner-border-sm"></span> Loading property manager details...</div>');
        // Fetch agent details via AJAX (use the correct JSON route)
        $.ajax({
            url: '/dashboard/agent/' + agentId + '/json',
            method: 'GET',
            data: { property_id: propId },
            success: function(data) {
                var html = '<div class="p-2 text-center">';
                html += '<img src="' + (data.photo || '/assets/images/default-avatar.png') + '" alt="Property Manager Photo" style="width:90px;height:90px;border-radius:50%;object-fit:cover;margin-bottom:10px;">';
                html += '<h5 class="mt-2">' + (data.first_name || '') + ' ' + (data.last_name || '') + '</h5>';
                html += '<div><strong>Email:</strong> ' + (data.email || 'N/A') + '</div>';
                html += '<div><strong>Phone:</strong> ' + (data.phone || 'N/A') + '</div>';
                html += '<div><strong>Location:</strong> ' + (data.lga ? data.lga + ', ' : '') + (data.state || '') + '</div>';
                html += '<div><strong>Specialty:</strong> ' + (data.occupation || 'N/A') + '</div>';
                html += '</div>';
                $('#agentDetailsBody').html(html);
                // Show agent rating section
                loadAgentRatings(agentId, propId);
                // Show rating form if eligible
                $('#agentRatingFormContainer').hide();
                $.get('/dashboard/agent/' + agentId + '/ratings', function(res) {
                    if (res.success) {
                        // Check if user can rate (simple check: property owner or tenant, and not already rated)
                        if (data.can_rate) {
                            $('#agentRatingFormContainer').show();
                            $('#agentRatingAgentId').val(agentId);
                            $('#agentRatingPropertyId').val(propId);
                            $('#agentRatingStars').html(renderRatingStarsInput(0));
                            $('#agentRatingInput').val(0);
                        }
                    }
                });
                if (data.can_remove) {
                    $('#removeAgentBtn').show().off('click').on('click', function() {
                        removeAgent(agentId, propId);
                    });
                } else {
                    $('#removeAgentBtn').hide();
                }
            },
            error: function() {
                $('#agentDetailsBody').html('<div class="alert alert-danger">Failed to load property manager details.</div>');
                $('#removeAgentBtn').hide();
            }
        });
    }

    function renderStars(rating, max = 5) {
        let html = '';
        rating = Math.round(rating);
        for (let i = 1; i <= max; i++) {
            html += `<i class=\"fa fa-star${i <= rating ? ' text-warning' : ' text-secondary'}\"></i>`;
        }
        return html;
    }

    function loadAgentRatings(agentId, propertyId) {
        $('#agentRatingSection').show();
        $.get('/dashboard/agent/' + agentId + '/ratings', function(res) {
            if (res.success) {
                // Average rating
                let avg = res.average || 0;
                $('#agentAverageRating').html(renderStars(Math.round(avg)));
                $('#agentAverageRatingText').text(avg > 0 ? avg + ' / 5' : 'No ratings yet');
                // Reviews
                let reviewsHtml = '';
                if (res.ratings.length) {
                    res.ratings.slice(0, 5).forEach(function(r) {
                        reviewsHtml += `<div class='border rounded p-2 mb-2'>` +
                            `<div class='d-flex align-items-center mb-1'>` +
                            `<img src='${r.user.photo || '/assets/images/default-avatar.png'}' style='width:32px;height:32px;border-radius:50%;object-fit:cover;margin-right:8px;'>` +
                            `<strong>${r.user.first_name} ${r.user.last_name}</strong>` +
                            `<span class='ml-2'>${renderStars(r.rating)}</span>` +
                            `<span class='ml-auto text-muted' style='font-size:0.9em;'>${new Date(r.created_at).toLocaleDateString()}</span>` +
                            `</div>` +
                            (r.comment ? `<div class='text-muted small'>${r.comment}</div>` : '') +
                            `</div>`;
                    });
                } else {
                    reviewsHtml = '<div class="text-muted text-center">No reviews yet.</div>';
                }
                $('#agentReviewsList').html(reviewsHtml);
            }
        });
    }

    // Star rating UI for form
    function renderRatingStarsInput(selected) {
        let html = '';
        for (let i = 1; i <= 5; i++) {
            html += `<i class="fa fa-star rating-star${i <= selected ? ' text-warning' : ' text-secondary'}" data-value="${i}" style="cursor:pointer;font-size:1.5em;"></i>`;
        }
        return html;
    }

    // Star click handler for rating form
    $(document).on('click', '.rating-star', function() {
        var val = $(this).data('value');
        $('#agentRatingStars').html(renderRatingStarsInput(val));
        $('#agentRatingInput').val(val);
    });

    // Submit agent rating form
    $(document).on('submit', '#agentRatingForm', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Submitting...');
        var formData = form.serialize();
        $.ajax({
            url: '/dashboard/agent/rate',
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Thank you!', text: res.message, timer: 1200, showConfirmButton: false });
                    $('#agentRatingFormContainer').hide();
                    loadAgentRatings($('#agentRatingAgentId').val(), $('#agentRatingPropertyId').val());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: function(xhr) {
                let msg = 'Failed to submit rating.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            },
            complete: function() { btn.prop('disabled', false).text('Submit Rating'); }
        });
    });

    function removeAgent(agentId, propId) {
        Swal.fire({
            title: 'Remove Property Manager?',
            text: 'Are you sure you want to remove this property manager from your property?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#removeAgentBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Removing...');
                $.ajax({
                    url: '/dashboard/property/' + propId + '/remove-agent',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        property_id: propId,
                        agent_id: agentId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Property Manager Removed',
                                text: response.message || 'Property manager removed successfully',
                                showConfirmButton: false,
                                timer: 1200
                            });
                            setTimeout(function() { location.reload(); }, 1300);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to remove property manager'
                            });
                            $('#removeAgentBtn').prop('disabled', false).html('Remove Property Manager');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to remove property manager. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                        $('#removeAgentBtn').prop('disabled', false).html('Remove Property Manager');
                    }
                });
            }
        });
    }
</script>
@endsection