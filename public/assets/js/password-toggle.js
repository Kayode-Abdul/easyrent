/**
 * Password Toggle Functionality - Global JavaScript
 * Provides show/hide password functionality for all forms
 */

/**
 * Toggle password visibility for a specific field
 * @param {string} fieldId - The ID of the password field
 */
function togglePasswordVisibility(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId + '-toggle-icon');
    
    if (!passwordField || !toggleIcon) {
        console.warn('Password field or toggle icon not found:', fieldId);
        return;
    }
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
        toggleIcon.setAttribute('title', 'Hide password');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
        toggleIcon.setAttribute('title', 'Show password');
    }
}

/**
 * Initialize password toggle functionality for all password fields on the page
 */
function initializePasswordToggles() {
    // Find all password fields
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        // Skip if already has a toggle button
        const existingToggle = field.parentElement.querySelector('.password-toggle-btn, .password-toggle-btn-user');
        if (existingToggle) return;
        
        // Skip if field doesn't have an ID
        if (!field.id) return;
        
        // Create toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'password-toggle-btn';
        toggleBtn.setAttribute('aria-label', 'Toggle password visibility');
        toggleBtn.setAttribute('title', 'Show password');
        toggleBtn.onclick = () => togglePasswordVisibility(field.id);
        
        // Create icon
        const icon = document.createElement('i');
        icon.className = 'fas fa-eye';
        icon.id = field.id + '-toggle-icon';
        toggleBtn.appendChild(icon);
        
        // Add position relative to parent if needed
        const parent = field.parentElement;
        if (!parent.classList.contains('position-relative')) {
            parent.classList.add('position-relative');
        }
        
        // Insert toggle button after the input field
        field.parentElement.appendChild(toggleBtn);
    });
}

/**
 * Auto-initialize password toggles when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    initializePasswordToggles();
});

/**
 * Keyboard accessibility - toggle with Enter or Space
 */
document.addEventListener('keydown', function(e) {
    if (e.target.classList.contains('password-toggle-btn') || e.target.classList.contains('password-toggle-btn-user')) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            e.target.click();
        }
    }
});

/**
 * Legacy function names for backward compatibility
 */
function togglePassword() {
    togglePasswordVisibility('password');
}

function togglePasswordField(fieldId) {
    togglePasswordVisibility(fieldId);
}

// Export functions for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        togglePasswordVisibility,
        initializePasswordToggles,
        togglePassword,
        togglePasswordField
    };
}