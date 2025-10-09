@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Edit Apartment</h4>
                        <a href="{{ url('/dashboard/apartment/'.$apartment->apartment_id) }}" class="btn btn-primary btn-round">
                            <i class="fa fa-arrow-left"></i> Back to Details
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="editApartmentForm">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apartmentType">Apartment Type</label>
                                    <input type="text" class="form-control" name="apartmentType" id="apartmentType" value="{{ $apartment->apartment_type }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="tenantId">Tenant ID</label>
                                    <input type="text" class="form-control" name="tenantId" id="tenantId" value="{{ $apartment->tenant_id }}">
                                </div>
                                <div class="form-group">
                                    <label for="duration">Duration</label>
                                    <select class="form-control" name="duration" id="duration" required>
                                        <option value="1" {{ $apartment->duration == 1 ? 'selected' : '' }}>Monthly</option>
                                        <option value="3" {{ $apartment->duration == 3 ? 'selected' : '' }}>Quarterly</option>
                                        <option value="6" {{ $apartment->duration == 6 ? 'selected' : '' }}>Semi-Annual</option>
                                        <option value="12" {{ $apartment->duration == 12 ? 'selected' : '' }}>Annual</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fromDate">Start Date</label>
                                    <input type="date" class="form-control" name="fromDate" id="fromDate" value="{{ $apartment->range_start ? date('Y-m-d', strtotime($apartment->range_start)) : '' }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="toDate">End Date</label>
                                    <input type="date" class="form-control" name="toDate" id="toDate" value="{{ $apartment->range_end ? date('Y-m-d', strtotime($apartment->range_end)) : '' }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="price">Price</label>
                                    <input type="text" class="form-control" name="price" id="price" value="{{ $apartment->amount }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div id="updateMessage"></div>
                                <button type="submit" class="btn btn-primary btn-round">
                                    <i class="fa fa-save"></i> Update Apartment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateEndDate(startDate, duration) {
    if (!startDate || !duration) return '';
    const date = new Date(startDate);
    date.setMonth(date.getMonth() + parseInt(duration));
    return date.toISOString().split('T')[0];
}

$(document).ready(function() {
    // Auto-update end date when start date or duration changes
    $('#fromDate').on('change', function() {
        const startDate = $(this).val();
        const duration = $('#duration').val();
        if (startDate && duration) {
            $('#toDate').val(calculateEndDate(startDate, duration));
        }
    });
    $('#duration').on('change', function() {
        const duration = $(this).val();
        const startDate = $('#fromDate').val();
        if (startDate && duration) {
            $('#toDate').val(calculateEndDate(startDate, duration));
        }
    });

    // Existing AJAX form submit logic
    $('#editApartmentForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serialize();
        $.ajax({
            url: '/dashboard/apartment/{{ $apartment->apartment_id }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(data) {
                if (data.success) {
                    $('#updateMessage').html('<div class="alert alert-success">' + data.messages + '</div>');
                    setTimeout(function() {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '/dashboard/apartment/{{ $apartment->apartment_id }}';
                        }
                    }, 1500);
                } else {
                    $('#updateMessage').html('<div class="alert alert-danger">' + data.messages + '</div>');
                }
            },
            error: function(xhr) {
                $('#updateMessage').html('<div class="alert alert-danger">An error occurred while updating the apartment. Please try again.</div>');
            }
        });
    });
});
</script>

@include('footer')
