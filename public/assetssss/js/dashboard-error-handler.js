/**
 * Dashboard Error Handler - Helps detect and report view rendering errors
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check for dashboard error status in URL params
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    
    if (error) {
        console.error('Dashboard Error:', error);
        
        // Show an error notification to the user
        if (typeof showNotification === 'function') {
            showNotification('Error', 'There was a problem loading the dashboard view. Redirected to default view.', 'danger');
        } else {
            alert('Error: ' + error);
        }
    }
    
    // Add error tracking for the role switcher
    const roleSwitchers = document.querySelectorAll('.role-button, form[action*="switch-role"]');
    roleSwitchers.forEach(function(element) {
        if (element.tagName === 'A') {
            element.addEventListener('click', function(e) {
                localStorage.setItem('last_role_switch', new Date().toISOString());
                localStorage.setItem('last_role_target', element.textContent.trim());
            });
        } else if (element.tagName === 'FORM') {
            element.addEventListener('submit', function(e) {
                const roleSelect = element.querySelector('select[name="role"]');
                if (roleSelect) {
                    localStorage.setItem('last_role_switch', new Date().toISOString());
                    localStorage.setItem('last_role_target', roleSelect.options[roleSelect.selectedIndex].text);
                }
            });
        }
    });
    
    // Helper function to show notifications
    window.showNotification = function(title, message, type = 'info') {
        // Check if we have the notification container
        let notificationContainer = document.getElementById('notification-container');
        
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'notification-container';
            notificationContainer.style.position = 'fixed';
            notificationContainer.style.top = '10px';
            notificationContainer.style.right = '10px';
            notificationContainer.style.zIndex = '9999';
            notificationContainer.style.maxWidth = '350px';
            document.body.appendChild(notificationContainer);
        }
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            <strong>${title}</strong>
            <p>${message}</p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        // Add notification to container
        notificationContainer.appendChild(notification);
        
        // Set timeout to remove notification
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notificationContainer.removeChild(notification);
            }, 300);
        }, 5000);
        
        // Add close button event
        const closeButton = notification.querySelector('.close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                notification.classList.remove('show');
                setTimeout(() => {
                    notificationContainer.removeChild(notification);
                }, 300);
            });
        }
    };
});
