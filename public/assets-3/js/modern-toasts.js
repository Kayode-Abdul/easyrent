/**
 * Modern Toast Notifications - Global JavaScript
 * A beautiful, modern toast notification system
 */

// Global toast configuration
const ToastConfig = {
    success: {
        icon: 'fas fa-check-circle',
        title: 'Success!'
    },
    error: {
        icon: 'fas fa-exclamation-circle',
        title: 'Error!'
    },
    warning: {
        icon: 'fas fa-exclamation-triangle',
        title: 'Warning!'
    },
    info: {
        icon: 'fas fa-info-circle',
        title: 'Info'
    }
};

/**
 * Show a modern toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of toast (success, error, warning, info)
 * @param {number} duration - Duration in milliseconds (default: 4000)
 * @param {string} title - Custom title (optional)
 */
function showToast(message, type = 'success', duration = 4000, title = null) {
    // Ensure container exists
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'modern-toast-container';
        document.body.appendChild(container);
    }
    
    // Prevent duplicate toasts with same message and type
    const existingToasts = container.querySelectorAll('.modern-toast');
    for (let existingToast of existingToasts) {
        const existingMessage = existingToast.querySelector('.toast-message');
        if (existingMessage && existingMessage.textContent === message && existingToast.classList.contains(type)) {
            return existingToast.id; // Return existing toast ID instead of creating duplicate
        }
    }
    
    const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    const config = ToastConfig[type] || ToastConfig.success;
    const toastTitle = title || config.title;
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `modern-toast ${type}`;
    toast.id = toastId;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="toast-icon ${type}">
            <i class="${config.icon}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${toastTitle}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button type="button" class="toast-close" onclick="closeToast('${toastId}')" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
        <div class="toast-progress">
            <div class="toast-progress-bar ${type}" style="animation-duration: ${duration}ms;"></div>
        </div>
    `;
    
    container.appendChild(toast);
    
    // Trigger show animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Auto remove after specified duration
    const autoCloseTimer = setTimeout(() => {
        closeToast(toastId);
    }, duration);
    
    // Store timer reference for potential cleanup
    toast.autoCloseTimer = autoCloseTimer;
    
    return toastId;
}

/**
 * Close a specific toast notification
 * @param {string} toastId - The ID of the toast to close
 */
function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        // Clear auto-close timer if it exists
        if (toast.autoCloseTimer) {
            clearTimeout(toast.autoCloseTimer);
            toast.autoCloseTimer = null;
        }
        
        // Only proceed if toast is not already being closed
        if (!toast.classList.contains('hide')) {
            toast.classList.remove('show');
            toast.classList.add('hide');
            
            setTimeout(() => {
                if (toast && toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 400);
        }
    }
}

/**
 * Close all toast notifications
 */
function closeAllToasts() {
    const container = document.getElementById('toast-container');
    if (container) {
        const toasts = container.querySelectorAll('.modern-toast');
        toasts.forEach(toast => {
            closeToast(toast.id);
        });
    }
}

/**
 * Convenience methods for different toast types
 */
const Toast = {
    success: (message, duration, title) => showToast(message, 'success', duration, title),
    error: (message, duration, title) => showToast(message, 'error', duration, title),
    warning: (message, duration, title) => showToast(message, 'warning', duration, title),
    info: (message, duration, title) => showToast(message, 'info', duration, title),
    close: closeToast,
    closeAll: closeAllToasts
};

// Make Toast available globally
window.Toast = Toast;
window.showToast = showToast;
window.closeToast = closeToast;
window.closeAllToasts = closeAllToasts;

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Create container if it doesn't exist
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'modern-toast-container';
        document.body.appendChild(container);
    }
    
    // Handle keyboard accessibility
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllToasts();
        }
    });
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Toast, showToast, closeToast, closeAllToasts };
}