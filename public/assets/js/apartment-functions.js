/**
 * Apartment management functions for LandlordDirect
 */

/**
 * Add a new apartment row to the apartment table in the property modal
 */
function addApartmentRow() {
    const formContainer = document.querySelector('#apartmentFormFields');
    
    const newFormGroup = document.createElement('div');
    newFormGroup.className = 'form-group';
    newFormGroup.innerHTML = `
        <div class="card card-body mb-3">
            <div class="form-group">
                <label>Apartment Type</label>
                <select class="form-control" name="apartmentType[]" required>
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
                <label>Tenant ID (optional)</label>
                <input type="text" class="form-control"
                    placeholder="Enter tenant ID"
                    name="tenantId[]">
            </div>
            <div class="form-group">
                <label>Lease Duration</label>
                <div class="d-flex flex-column gap-2">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Start</span>
                        </div>
                        <input type="date" class="form-control"
                            name="fromDate[]" required>
                    </div>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">End</span>
                        </div>
                        <input type="date" class="form-control"
                            name="toDate[]" required>
                    </div>
                </div>
            </div>
        </div>`;

    formContainer.appendChild(newFormGroup);
}

/**
 * Remove an apartment row from the apartment table
 * @param {HTMLElement} button - The button that was clicked
 */
function removeApartmentRow(button) {
    const row = button.closest('tr');
    row.remove();
}

/**
 * Toggle the occupied status of an apartment
 * @param {HTMLElement} button - The button that was clicked
 */
function toggleOccupied(button) {
    const occupied = button.dataset.occupied === '1' ? '0' : '1';
    button.dataset.occupied = occupied;
    
    // Update the hidden input
    const hiddenInput = button.previousElementSibling;
    hiddenInput.value = occupied;
    
    // Update the button appearance
    if (occupied === '1') {
        button.classList.remove('btn-danger');
        button.classList.add('btn-success');
        button.innerHTML = '<i class="fa fa-check"></i>';
    } else {
        button.classList.remove('btn-success');
        button.classList.add('btn-danger');
        button.innerHTML = '<i class="fa fa-times"></i>';
    }
}

/**
 * View apartment details
 * @param {string} apartmentId - The ID of the apartment to view
 */
function viewApartment(apartmentId) {
    // Redirect to the apartment details page
    window.location.href = `/dashboard/apartment/${apartmentId}`;
}

/**
 * Edit apartment details
 * @param {string} apartmentId - The ID of the apartment to edit
 */
function editApartment(apartmentId) {
    // Fetch apartment data and populate the modal
    fetch(`/api/apartment/${apartmentId}`)
        .then(response => response.json())
        .then(data => {
            // Set the modal title
            document.getElementById('apartmentModalLabel').textContent = 'Edit Apartment';
            
            // Populate the form fields
            const form = document.getElementById('apartmentForm');
            form.action = `/apartment/${apartmentId}`;
            
            // Add method override for PUT
            let methodField = form.querySelector('input[name="_method"]');
            if (!methodField) {
                methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                form.appendChild(methodField);
            }
            methodField.value = 'PUT';
            
            // Populate form fields with apartment data
            form.querySelector('select[name="apartmentType"]').value = data.apartment_type;
            form.querySelector('input[name="tenantId"]').value = data.tenant_id || '';
            form.querySelector('select[name="duration"]').value = data.duration || '';
            form.querySelector('input[name="fromDate"]').value = data.range_start || '';
            form.querySelector('input[name="toDate"]').value = data.range_end || '';
            form.querySelector('input[name="price"]').value = data.amount || '';
            
            // Show the modal
            $('#apartmentModal').modal('show');
        })
        .catch(error => {
            console.error('Error fetching apartment data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load apartment data. Please try again.'
            });
        });
}

/**
 * Confirm deletion of an apartment
 * @param {string} apartmentId - The ID of the apartment to delete
 */
function confirmDeleteApartment(apartmentId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form for deletion
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/apartment/${apartmentId}`;
            form.style.display = 'none';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);
            
            // Add method override for DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            // Submit the form
            document.body.appendChild(form);
            form.submit();
        }
    });
}