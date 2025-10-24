<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@include('header')

<div class="content">
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-bank text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Property</p>
                                <p class="card-title">{{ $apartment->property->address }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-building"></i> Property Address
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
                                <i class="nc-icon nc-single-02 text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Tenant</p>
                                <!-- In the tenant card -->
                                <p class="card-title">
                                    @if($apartment->tenant_id)
                                        <a href="javascript:void(0)" onclick="showTenantDetails('{{ $apartment->tenant_id }}')">
                                            Veiw Tenant
                                        </a>
                                    @else
                                        Vacant
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-user"></i> Current Tenant
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
                                <i class="nc-icon nc-money-coins text-danger"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Amount</p>
                                <p class="card-title">{{ $apartment->amount ? '₦'.number_format($apartment->amount, 2) : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-calendar"></i> Rental Amount
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
                                @php
                                    $now = now();
                                    if ($apartment->tenant_id && $apartment->duration) {
                                        $daysUntilExpiry = $apartment->range_end ? now()->diffInDays($apartment->range_end, false) : null;
                                        $status = match(true) {
                                            $now > $apartment->range_end => 'expired',
                                            $now < $apartment->range_start => 'upcoming',
                                            $daysUntilExpiry <= 30 => 'expiring-soon',
                                            default => 'active'
                                        };
                                        $statusClass = match($status) {
                                            'expired' => 'warning',
                                            'upcoming' => 'info',
                                            'expiring-soon' => 'warning',
                                            'active' => 'success'
                                        };
                                        $iconClass = match($status) {
                                            'expired' => 'exclamation-circle',
                                            'upcoming' => 'clock-o',
                                            'expiring-soon' => 'exclamation-triangle',
                                            'active' => 'check-square'
                                        };
                                    } else {
                                        $status = 'vacant';
                                        $statusClass = 'danger';
                                        $iconClass = 'times-circle';
                                    }
                                @endphp
                                <i class="nc-icon nc-tag-content text-{{ $statusClass }}"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Status</p>
                                <p class="card-title">{{ ucfirst($status) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-{{ $iconClass }}"></i> Lease Status
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Apartment Details Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Apartment Details </h4>
                            @if(auth()->user()->admin || auth()->user()->user_id == $apartment->property->user_id)
                                <button type="button" class="btn btn-primary btn-sm ml-2" id="sendProfomaBtn" data-tenant-id="{{ $apartment->tenant_id }}">
                                    <i class="fa fa-paper-plane"></i> Send Profoma
                                </button>
                            @endif
                            <p class="card-category">Complete information about this apartment</p>
                        </div>
                        <div class="btn-group">
                            <a href="{{ url('/dashboard/property/'.$apartment->property->prop_id) }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Property
                            </a>
                            <button type="button" class="btn btn-warning btn-sm" onclick="editApartment('{{ $apartment->apartment_id }}')">
                                <i class="fa fa-edit"></i> Edit Apartment
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteApartment('{{ $apartment->apartment_id }}')">
                                <i class="fa fa-trash"></i> Delete Apartment
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Property</label>
                                <p class="form-control-static">{{ $apartment->property->address }}</p>
                            </div>
                            <div class="form-group">
                                <label>Tenant</label>
                                <p class="form-control-static">{{ $apartment->tenant->name ?? 'Vacant' }}</p>
                            </div>
                            <div class="form-group">
                                <label>Amount</label>
                                <p class="form-control-static">{{ $apartment->amount ? '₦'.number_format($apartment->amount, 2) : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Apartment Type</label>
                                <p class="form-control-static">{{ $apartment->apartment_type ?? 'N/A' }}</p>
                            </div>
                            <div class="form-group">
                                <label>Start Date</label>
                                <p class="form-control-static">{{ $apartment->range_start ? date('M d, Y', strtotime($apartment->range_start)) : 'N/A' }}</p>
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <p class="form-control-static">{{ $apartment->range_end ? date('M d, Y', strtotime($apartment->range_end)) : 'N/A' }}</p>
                            </div>
                            <div class="form-group">
                                <label>Duration</label>
                                <p class="form-control-static">{{ $apartment->duration ? $apartment->duration.' months' : 'N/A' }}</p>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <p class="form-control-static">
                                    <span class="badge badge-{{ $statusClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $profoma = \App\Models\ProfomaReceipt::where('apartment_id', $apartment->id)->first();
    @endphp
    @if($profoma)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Profoma Receipt Details</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item"><strong>Transaction ID:</strong> {{ $profoma->transaction_id }}</li>
                    <li class="list-group-item"><strong>Status:</strong> {{ $profoma->status_label }}</li>
                    <li class="list-group-item"><strong>Duration:</strong> {{ $profoma->duration ? $profoma->duration.' months' : 'N/A' }}</li>
                    <li class="list-group-item"><strong>Created At:</strong> {{ $profoma->created_at ? $profoma->created_at->format('M d, Y') : 'N/A' }}</li>
                </ul>
            </div>
        </div>
    @endif

    <!-- Payment Section -->
    @if(auth()->check() && auth()->user()->role === 2 && !$apartment->occupied)
        <div class="row mt-4">
            <div class="col-md-8">
                <x-payment-form :apartment="$apartment" />
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><strong>Secure Payment:</strong> All transactions are secured by Paystack</li>
                            <li><strong>Instant Confirmation:</strong> Get immediate confirmation after payment</li>
                            <li><strong>Support:</strong> 24/7 customer support available</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Edit Apartment Modal -->
<div class="modal fade" id="editApartmentModal" tabindex="-1" role="dialog" aria-labelledby="editApartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editApartmentModalLabel">Edit Apartment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="apartmentMessage"></div>
                <form id="apartmentForm" class="p-3">
                    @csrf
                    <input type="hidden" name="propertyId" value="{{ $apartment->property->prop_id }}">
                    <div class="table-responsive">
                        <table id="apartmentTable" class="table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Apartment Type</th>
                                    <th>Tenant ID</th>
                                    <th>Duration (months)</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Price</th>
                                    <th class="text-center">Occupied Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveApartment">Update Apartment</button>
            </div>
        </div>
    </div>
</div>

<!-- Tenant Details Modal -->
<div class="modal fade" id="tenantDetailsModal" tabindex="-1" role="dialog" aria-labelledby="tenantDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tenantDetailsModalLabel">Tenant Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="tenantMessage"></div>
                <div class="tenant-details text-center">
                    <div class="form-group">
                        <div class="avatar-circle mb-3">
                            <img id="tenantPhoto" src="{{ asset('assets/images/default-avatar.png') }}" alt="Tenant Photo" style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tenant ID</label>
                        <p class="form-control-static" id="tenantId"></p>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <p class="form-control-static" id="tenantName"></p>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <p class="form-control-static" id="tenantEmail"></p>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <p class="form-control-static" id="tenantPhone"></p>
                    </div>
                    <div class="form-group">
                        <label>Registration Date</label>
                        <p class="form-control-static" id="tenantRegDate"></p>
                    </div>
                    <div class="form-group">
                        <button id="messageTenantBtn" class="btn btn-success btn-block">
                            <i class="fa fa-envelope"></i> Message Tenant
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Proforma Modal -->
<div class="modal fade" id="proformaModal" tabindex="-1" role="dialog" aria-labelledby="proformaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proformaModalLabel">Send Proforma</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="proformaForm">
                <div class="modal-body">
                    @csrf
                    @if($apartment->tenant_id)
                    <input type="hidden" name="tenant_id" value="{{ $apartment->tenant_id }}">
                    @else
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This apartment has no tenant assigned. You must assign a tenant before sending a proforma.
                    </div>
                    @endif
                    <div class="form-group">
                        <label>Duration (months)</label>
                        <input type="number" min="1" class="form-control" name="duration" id="proformaDuration" value="{{ $apartment->duration ?? 12 }}" required>
                    </div>
                    <div class="form-group">
                        <label>Monthly Rent</label>
                        <input type="number" min="0" step="50" class="form-control" name="amount" id="proformaAmount" value="{{ $apartment->amount ?? 0 }}" required>
                    </div>
                    <div class="form-group">
                        <label>Security Deposit</label>
                        <input type="number" min="0" step="500" class="form-control" name="security_deposit" id="proformaSecurityDeposit">
                    </div>
                    <div class="form-group">
                        <label>Water</label>
                        <input type="number" min="0" step="500" class="form-control" name="water" id="proformaWater">
                    </div>
                    <div class="form-group">
                        <label>Internet</label>
                        <input type="number" min="0" step="500" class="form-control" name="internet" id="proformaInternet">
                    </div>
                    <div class="form-group">
                        <label>Generator</label>
                        <input type="number" min="0" step="500" class="form-control" name="generator" id="proformaGenerator">
                    </div>
                    <div class="form-group">
                        <label>Other Charges Description</label>
                        <textarea class="form-control" name="other_charges_desc" id="proformaOtherChargesDesc"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Other Charges Amount</label>
                        <input type="number" min="0" step="500" class="form-control" name="other_charges_amount" id="proformaOtherChargesAmount">
                    </div>
                    
                    <!-- Commission Information (only shown for managed properties) -->
                    <div id="commissionInfo" class="form-group">
                        <div class="alert alert-default">
                            <p><strong>Service Charge:</strong> <span id="commissionAmount"></span> (<span id="commissionRate"></span>)</p>
                            <p class="mb-0"><small>This commission will be deducted from the total amount.</small></p>
                        <p class="font-weight-bold"> You'll receive <span id="proformaTotal"></span>. </p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Total</strong></label>
                        <input type="number" class="form-control" name="total" id="total" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" {{ !$apartment->tenant_id ? 'disabled' : '' }}>Send Proforma</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 100px;
    height: 100px;
    background-color: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.contact-info {
    max-width: 300px;
    margin: 0 auto;
    text-align: left;
}

.contact-info i {
    width: 20px;
    margin-right: 10px;
    color: #6c757d;
}
</style>

<!-- Define a JavaScript variable with the property agent status before any JavaScript code -->
<script>
// Set property management status from server-side
var hasPropertyAgent = {{ $apartment->property->agent_id ? 'true' : 'false' }};
</script>

<script>
function showTenantDetails(tenantId) {
    // Show loading state
    $('#tenantMessage').html('<div class="alert alert-info">Loading tenant details...</div>');
    $('#tenantDetailsModal').modal('show');
    
    // Fetch tenant details
    fetch(`/dashboard/tenant/${tenantId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tenant = data.data.tenant;
            
            // Update modal with tenant details
            document.getElementById('tenantId').textContent = tenant.user_id;
            document.getElementById('tenantName').textContent = tenant.first_name+' '+tenant.last_name;
            document.getElementById('tenantEmail').textContent = tenant.email;
            document.getElementById('tenantPhone').textContent = tenant.phone || 'N/A';
            document.getElementById('tenantRegDate').textContent = new Date(tenant.created_at).toLocaleDateString();
            
            // Set tenant photo or default
            let photoUrl = tenant.photo ? `/assets/photos/${tenant.photo}` : `{{ asset('assets/images/default-avatar.png') }}`;
            document.getElementById('tenantPhoto').src = photoUrl;
            
            // Clear loading message
            document.getElementById('tenantMessage').innerHTML = '';
        } else {
            document.getElementById('tenantMessage').innerHTML = `
                <div class="alert alert-danger">
                    ${data.messages}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('tenantMessage').innerHTML = `
            <div class="alert alert-danger">
                Failed to load tenant details. Please try again.
            </div>
        `;
    });
}

// Reset modal when closed
$('#tenantDetailsModal').on('hidden.bs.modal', function () {
    // Clear tenant details
    document.getElementById('tenantId').textContent = '';
    document.getElementById('tenantName').textContent = '';
    document.getElementById('tenantEmail').textContent = '';
    document.getElementById('tenantPhone').textContent = '';
    document.getElementById('tenantRegDate').textContent = '';
    
    // Clear message
    document.getElementById('tenantMessage').innerHTML = '';
});

$(document).ready(function() {
    // Check if proforma button should show tooltip
    if ($('#proformaForm button[type=submit]').is(':disabled')) {
        $('#proformaForm button[type=submit]').attr('title', 'You must assign a tenant before sending a proforma');
        $('#proformaForm button[type=submit]').tooltip();
    }
    
    // $('#sendProfomaBtn').on('click', function() {
    //     var btn = $(this);
    //     // Use the tenant ID from the button's data attribute if available
    //     var tenantId = btn.data('tenant-id') || $('#tenantId').text();
    //     if (!tenantId) {
    //         Swal.fire({
    //             icon: 'error',
    //             title: 'Error',
    //             text: 'Please select a tenant first'
    //         });
    //         return;
    //     }
    //     btn.prop('disabled', true);
    //     btn.html('<i class="fa fa-spinner fa-spin"></i> Sending...');
    //     $.ajax({
    //         url: '/dashboard/apartment/{{ $apartment->id }}/send-profoma',
    //         type: 'POST',
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         data: {
    //             tenant_id: tenantId
    //         },
    //         success: function(response) {
    //             if (response.success) {
    //                 Swal.fire({
    //                     icon: 'success',
    //                     title: 'Success', 
    //                     text: 'Proforma sent successfully!',
    //                     showConfirmButton: false,
    //                     timer: 1500
    //                 });
    //                 btn.html('<i class="fa fa-check"></i> Sent!');
    //                 btn.removeClass('btn-primary').addClass('btn-success');
    //                 setTimeout(function() {
    //                     btn.html('<i class="fa fa-paper-plane"></i> Send Profoma');
    //                     btn.removeClass('btn-success').addClass('btn-primary');
    //                     btn.prop('disabled', false);
    //                 }, 2000);
    //             } else {
    //                 Swal.fire({
    //                     icon: 'error',
    //                     title: 'Error',
    //                     text: response.message || 'Failed to send proforma.'
    //                 });
    //                 btn.prop('disabled', false);
    //                 btn.html('<i class="fa fa-paper-plane"></i> Send Profoma');
    //             }
    //         },
    //         error: function(xhr) {
    //             Swal.fire({
    //                 icon: 'error',
    //                 title: 'Error',
    //                 text: 'Failed to send proforma. Please try again.'
    //             });
    //             btn.prop('disabled', false);
    //             btn.html('<i class="fa fa-paper-plane"></i> Send Profoma');
    //         }
    //     });
    // });
    
    // Add event listener for Message Tenant button
    $(document).on('click', '#messageTenantBtn', function() {
        var tenantId = document.getElementById('tenantId').textContent;
        if(tenantId) {
            window.location.href = '/dashboard/messages/compose?to=' + tenantId;
        }
    });

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

    // Open modal on Send Profoma button click
    $(document).on('click', '#sendProfomaBtn', function() {
        $('#proformaModal').modal('show');
        calculateProformaTotal();
    });
    // Auto-calculate total
    function calculateProformaTotal() {
        let duration = parseFloat($('#proformaDuration').val()) || 0;
        let amount = parseFloat($('#proformaAmount').val()) || 0;
        let security = parseFloat($('#proformaSecurityDeposit').val()) || 0;
        let water = parseFloat($('#proformaWater').val()) || 0;
        let internet = parseFloat($('#proformaInternet').val()) || 0;
        let generator = parseFloat($('#proformaGenerator').val()) || 0;
        let other = parseFloat($('#proformaOtherChargesAmount').val()) || 0;
        let total = amount  + security + water + internet + generator + other;
        
        // Check if property is managed by a property manager using the variable defined at the top
        if (hasPropertyAgent) {
            // Calculate commission (5% for new tenants, 2.5% for renewals)
            // For simplicity, we'll use 5% as default
            const commissionRate = 0.025;
            const commission = total * commissionRate;
            
            // Display commission information
            $('#commissionInfo').show();
            $('#commissionAmount').text('₦' + commission.toFixed(2));
            $('#commissionRate').text((commissionRate * 100) + '%');
            
            $('#total').val((total).toFixed(2));
            // Update total after deducting commission
            $('#proformaTotal').text('₦'+(total - commission).toFixed(2));
        } else {
            // Hide commission information if property is not managed
            
            const commissionRate = 0.05;
            const commission = total * commissionRate;
            
            // Display commission information
            $('#commissionInfo').show();
            $('#commissionAmount').text('₦' + commission.toFixed(2));
            $('#commissionRate').text((commissionRate * 100) + '%');
            
            $('#total').val(total.toFixed(2));
            // Update total after deducting commission
            $('#proformaTotal').text('₦'+(total - commission).toFixed(2));
        }
    }
    $('#proformaForm input, #proformaForm textarea').on('input', calculateProformaTotal);
    // AJAX submit
    $('#proformaForm').on('submit', function(e) {
        e.preventDefault();
        
        // Check if tenant_id exists
        if (!$('input[name="tenant_id"]').length) {
            Swal.fire('Error', 'Cannot send proforma: No tenant assigned to this apartment.', 'error');
            return false;
        }
        
        let form = $(this);
        let btn = form.find('button[type=submit]');
        btn.prop('disabled', true).text('Sending...');
        $.ajax({
            url: '/dashboard/apartment/{{ $apartment->apartment_id }}/send-profoma',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    $('#proformaModal').modal('hide');
                } else {
                    Swal.fire('Error', response.message || 'Failed to send proforma.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to send proforma. Please try again.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).text('Send Proforma');
            }
        });
    });
});
</script>

@include('footer')
<!-- Footer area end -->