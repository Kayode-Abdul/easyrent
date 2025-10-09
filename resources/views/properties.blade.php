@extends('layout')
@section('content')
      <div class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="card shadow">
              <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0"> <i class="fas fa-building mr-2"></i> All Properties</h4>
                  <a href="/listing" class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> Add New Property
                  </a>
                </div>
              </div>
              <div class="card-body">
                <!-- Search and Filter Section -->
                <div class="row mb-4">
                  <div class="col-md-6">
                    <div class="input-group">
                      <input type="text" id="propertySearch" class="form-control" placeholder="Search properties...">
                      <div class="input-group-append">
                        <button class="btn btn-primary" type="button">
                          <i class="fas fa-search"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 text-right">
                    <div class="btn-group">
                      <button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-filter"></i> Filter
                      </button>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">All Properties</a>
                        <a class="dropdown-item" href="#">Apartments</a>
                        <a class="dropdown-item" href="#">Houses</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Recently Added</a>
                      </div>
                    </div>
                  </div>
                </div>
                
                @if(empty($all_properties))
                  <div class="alert alert-info text-center p-5">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h4>No properties found</h4>
                    <p>There are currently no properties in the system.</p>
                    <a href="/listing" class="btn btn-primary mt-3">
                      <i class="fas fa-plus"></i> Add Your First Property
                    </a>
                  </div>
                @else
                <div class="table-responsive">
                  <table class="table table-hover table-striped">
                    <thead class="thead-dark">
                      <th><i class="fas fa-user mr-1"></i> Owner</th>
                      <th><i class="fas fa-hashtag mr-1"></i> Property ID</th>
                      <th><i class="fas fa-map-marker-alt mr-1"></i> Address</th>
                      <th><i class="fas fa-map mr-1"></i> Location</th>
                      <th><i class="fas fa-tools mr-1"></i> Actions</th>
                    </thead>
                    <tbody>
                      @foreach($all_properties as $property)
                        <tr>
                          <td>{{ $property->user_id }}</td>
                          <td>{{ $property->prop_id }}</td>
                          <td>{{ $property->address }}</td>
                          <td>{{ $property->lga }}, {{ $property->state }}</td>
                          <td>
                            <div class="btn-group">
                              <a href="/dashboard/property/{{ $property->prop_id }}" class="btn btn-info btn-sm" data-toggle="tooltip" title="View Details">
                                <i class="fas fa-eye"></i>
                              </a>
                              <a href="/dashboard/property/{{ $property->prop_id }}/edit" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Edit Property">
                                <i class="fas fa-edit"></i>
                              </a>
                              <button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Delete Property" onclick="confirmDelete('{{ $property->prop_id }}')">
                                <i class="fas fa-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                  <div>
                    <p class="text-muted">Showing {{ $all_properties->firstItem() ?? 0 }} to {{ $all_properties->lastItem() ?? 0 }} of {{ $all_properties->total() ?? 0 }} properties</p>
                  </div>
                  <div>
                    {{ $all_properties->links() }}
                  </div>
                </div>
                @endif
              </div>
            </div>
          </div> 
        </div>
      </div>
    @endsection
    @section('scripts')

<script>
$(document).ready(function() {
  // Initialize tooltips
  $('[data-toggle="tooltip"]').tooltip();
  
  // Property search functionality
  $("#propertySearch").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("table tbody tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

// Delete confirmation function
function confirmDelete(propId) {
  if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
    // Create form element
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/dashboard/property/' + propId;
    form.style.display = 'none';
    
    // Add CSRF token
    var csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfToken);
    
    // Add method field for DELETE
    var methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    // Append form to body, submit it, and remove it
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  }
}
</script> 

<script>
function confirmDelete(propId) {
    if (confirm('Are you sure you want to delete this property? This will also delete all associated apartments.')) {
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        
        fetch(`/dashboard/property/${propId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.messages);
                window.location.reload();
            } else {
                alert('Error: ' + data.messages);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete property. Please try again.');
        });
    }
}
</script>
@endsection