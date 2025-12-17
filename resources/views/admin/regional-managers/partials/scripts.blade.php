<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#regionalManagersTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": -1 } // Disable sorting on Actions column
        ]
    });

    // Show/hide password fields in edit modal
    $('#change_password').change(function() {
        if ($(this).is(':checked')) {
            $('#password_fields').show();
            $('#edit_password').attr('required', true);
            $('#edit_password_confirmation').attr('required', true);
        } else {
            $('#password_fields').hide();
            $('#edit_password').attr('required', false);
            $('#edit_password_confirmation').attr('required', false);
        }
    });

    // Add Regional Manager Form
    $('#addRegionalManagerForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '/admin/regional-managers',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#addRegionalManagerModal').modal('hide');
                    showNotification('Regional Manager added successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message || 'Error adding Regional Manager', 'error');
                }
            },
            error: function(xhr) {
                let message = 'Error adding Regional Manager';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join(', ');
                }
                showNotification(message, 'error');
            }
        });
    });

    // Edit Regional Manager Form
    $('#editRegionalManagerForm').submit(function(e) {
        e.preventDefault();
        
        const userId = $('#edit_user_id').val();
        const formData = $(this).serialize();
        
        $.ajax({
            url: `/admin/regional-managers/${userId}`,
            method: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editRegionalManagerModal').modal('hide');
                    showNotification('Regional Manager updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message || 'Error updating Regional Manager', 'error');
                }
            },
            error: function(xhr) {
                let message = 'Error updating Regional Manager';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join(', ');
                }
                showNotification(message, 'error');
            }
        });
    });

    // Assign Regions Form
    $('#assignRegionsForm').submit(function(e) {
        e.preventDefault();
        
        const userId = $('#assign_user_id').val();
        const formData = $(this).serialize();
        
        $.ajax({
            url: `/admin/regional-managers/${userId}/assign-regions`,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#assignRegionsModal').modal('hide');
                    showNotification('Regions assigned successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message || 'Error assigning regions', 'error');
                }
            },
            error: function(xhr) {
                let message = 'Error assigning regions';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            }
        });
    });

    // Delete confirmation
    $('#confirmDeleteBtn').click(function() {
        const userId = $(this).data('user-id');
        
        $.ajax({
            url: `/admin/regional-managers/${userId}`,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    $('#deleteConfirmModal').modal('hide');
                    showNotification('Regional Manager removed successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message || 'Error removing Regional Manager', 'error');
                }
            },
            error: function(xhr) {
                let message = 'Error removing Regional Manager';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            }
        });
    });
});

// Global functions
function editRegionalManager(userId, firstName, lastName, email, phone, regions) {
    $('#edit_user_id').val(userId);
    $('#edit_first_name').val(firstName);
    $('#edit_last_name').val(lastName);
    $('#edit_email_address').val(email);
    $('#edit_phone_number').val(phone);
    
    // Clear and set regions
    $('#edit_regions option').prop('selected', false);
    if (regions && regions.length > 0) {
        regions.forEach(region => {
            $(`#edit_regions option[value="${region}"]`).prop('selected', true);
        });
    }
    
    // Reset password fields
    $('#change_password').prop('checked', false);
    $('#password_fields').hide();
    $('#edit_password').attr('required', false);
    $('#edit_password_confirmation').attr('required', false);
    
    $('#editRegionalManagerModal').modal('show');
}

function assignRegions(userId, managerName, currentRegions) {
    $('#assign_user_id').val(userId);
    $('#assign_manager_name').text(managerName);
    
    // Clear and set current regions
    $('#assign_regions_select option').prop('selected', false);
    if (currentRegions && currentRegions.length > 0) {
        currentRegions.forEach(region => {
            $(`#assign_regions_select option[value="${region}"]`).prop('selected', true);
        });
    }
    
    $('#assignRegionsModal').modal('show');
}

function deleteRegionalManager(userId, managerName) {
    $('#delete_manager_name').text(managerName);
    $('#confirmDeleteBtn').data('user-id', userId);
    $('#deleteConfirmModal').modal('show');
}

function showNotification(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(() => {
        notification.alert('close');
    }, 5000);
}

// Export functions
function exportRegionalManagers() {
    showNotification('Export functionality will be available soon!', 'info');
}

function generateReport() {
    showNotification('Report generation will be available soon!', 'info');
}

function bulkAction(action) {
    showNotification(`Bulk ${action} functionality will be available soon!`, 'info');
}
</script>