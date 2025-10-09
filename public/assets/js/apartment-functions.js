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