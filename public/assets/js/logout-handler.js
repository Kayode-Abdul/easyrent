/**
 * Global Logout Handler
 * Handles secure logout functionality across all pages
 * Includes CSRF token expiration handling
 */

// Global logout handler function
function handleLogout(formId) {
    if (typeof event !== 'undefined') {
        event.preventDefault();
    }
    
    // Show confirmation (optional - can be disabled by setting showConfirmation to false)
    const showConfirmation = true;
    
    if (!showConfirmation || confirm('Are you sure you want to logout?')) {
        const form = document.getElementById(formId);
        if (form) {
            // Add loading state if possible
            const logoutLink = (typeof event !== 'undefined' && event.target) ? event.target.closest('a') : null;
            if (logoutLink) {
                const originalContent = logoutLink.innerHTML;
                logoutLink.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
                
                // Restore original content after a delay in case of errors
                setTimeout(() => {
                    if (logoutLink.innerHTML.includes('Logging out...')) {
                        logoutLink.innerHTML = originalContent;
                    }
                }, 5000);
            }
            
            // Use fetch to handle CSRF token expiration gracefully
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                // If 419 (CSRF token mismatch) or any error, just redirect to login
                if (response.status === 419 || !response.ok) {
                    window.location.href = '/login';
                    return;
                }
                // Success - redirect to home or login
                window.location.href = response.redirected ? response.url : '/login';
            })
            .catch(error => {
                console.error('Logout error:', error);
                // On any error, just redirect to login page
                window.location.href = '/login';
            });
            
        } else {
            console.error('Logout form not found:', formId);
            
            // Show error message if toast system is available
            if (typeof showToast === 'function') {
                showToast('Logout form not found. Redirecting to login page.', 'warning');
            }
            
            // Fallback: redirect to login after a short delay
            setTimeout(() => {
                window.location.href = '/login';
            }, 1000);
        }
    }
}

// Alternative logout function for pages that might use different naming
function logout(formId) {
    return handleLogout(formId || 'logout-form');
}

// Make functions globally available
if (typeof window !== 'undefined') {
    window.handleLogout = handleLogout;
    window.logout = logout;
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { handleLogout, logout };
}