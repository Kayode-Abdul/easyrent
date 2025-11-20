function useAgent(propertyId) {
    // Show a modal to confirm agent assignment
    Swal.fire({
        title: 'Assign Property to Agent',
        text: 'Do you want to assign this property to an agent for management?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, assign agent',
        cancelButtonText: 'No, cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you can either:
            // 1. Redirect to an agent selection page
            window.location.href = `/dashboard/property/${propertyId}/select-agent`;
            // 2. Or open a modal with agent selection
            // $('#selectAgentModal').modal('show');
        }
    });
}

function viewAgent(agentId) {
    window.location.href = `/dashboard/agent/${agentId}`;
}

$(document).ready(function() {
    $('#agentForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Agent assigned successfully',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to assign agent',
                    icon: 'error'
                });
            }
        });
    });
});