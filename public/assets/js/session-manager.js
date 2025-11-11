/**
 * Session Manager - Handles session expiry and automatic redirects
 * Monitors user session and redirects to login when expired
 */

class SessionManager {
    constructor(options = {}) {
        this.options = {
            checkInterval: 60000, // Check every minute
            warningTime: 300000, // Warn 5 minutes before expiry
            loginUrl: '/login',
            sessionCheckUrl: '/api/session-check',
            showWarnings: true,
            ...options
        };
        
        this.sessionTimer = null;
        this.warningShown = false;
        this.isChecking = false;
        
        this.init();
    }
    
    init() {
        // Only run on authenticated pages (pages with sidebar)
        if (this.isAuthenticatedPage()) {
            this.startSessionMonitoring();
            this.setupActivityListeners();
            this.setupAjaxInterceptors();
        }
    }
    
    isAuthenticatedPage() {
        // Check if we're on a dashboard/authenticated page
        return document.querySelector('.sidebar') !== null || 
               window.location.pathname.startsWith('/dashboard') ||
               window.location.pathname.startsWith('/admin') ||
               window.location.pathname.startsWith('/marketer') ||
               window.location.pathname.startsWith('/property-manager') ||
               window.location.pathname.startsWith('/regional');
    }
    
    startSessionMonitoring() {
        // Check session status immediately
        this.checkSession();
        
        // Set up periodic checks
        this.sessionTimer = setInterval(() => {
            this.checkSession();
        }, this.options.checkInterval);
        
        console.log('Session monitoring started');
    }
    
    async checkSession() {
        if (this.isChecking) return;
        
        this.isChecking = true;
        
        try {
            const response = await fetch('/api/session-status', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });
            
            if (response.status === 401 || response.status === 419) {
                // Session expired or CSRF token mismatch
                this.handleSessionExpiry();
                return;
            }
            
            if (response.ok) {
                const data = await response.json();
                
                if (!data.authenticated) {
                    this.handleSessionExpiry();
                    return;
                }
                
                // Check if session is about to expire
                if (data.expires_in && data.expires_in < this.options.warningTime) {
                    this.showSessionWarning(data.expires_in);
                }
            }
            
        } catch (error) {
            console.warn('Session check failed:', error);
            // On network error, try one more time before redirecting
            setTimeout(() => {
                this.checkSessionFallback();
            }, 5000);
        } finally {
            this.isChecking = false;
        }
    }
    
    async checkSessionFallback() {
        try {
            // Simple check - try to access a protected endpoint
            const response = await fetch('/dashboard', {
                method: 'HEAD',
                credentials: 'same-origin'
            });
            
            if (response.status === 401 || response.status === 419) {
                this.handleSessionExpiry();
            }
        } catch (error) {
            console.warn('Fallback session check failed:', error);
        }
    }
    
    handleSessionExpiry() {
        console.log('Session expired - redirecting to login');
        
        // Clear the monitoring timer
        if (this.sessionTimer) {
            clearInterval(this.sessionTimer);
        }
        
        // Show toast notification if available
        if (typeof showToast === 'function') {
            showToast('Your session has expired. Please login again.', 'warning', 3000);
        }
        
        // Store current page for redirect after login
        const currentPath = window.location.pathname + window.location.search;
        if (currentPath !== '/login') {
            sessionStorage.setItem('redirect_after_login', currentPath);
        }
        
        // Redirect to login after a short delay
        setTimeout(() => {
            window.location.href = this.options.loginUrl + '?expired=1';
        }, 1000);
    }
    
    showSessionWarning(expiresIn) {
        if (this.warningShown) return;
        
        this.warningShown = true;
        const minutes = Math.ceil(expiresIn / 60000);
        
        if (typeof showToast === 'function') {
            showToast(
                `Your session will expire in ${minutes} minute(s). Please save your work.`,
                'warning',
                10000
            );
        } else {
            // Fallback alert
            alert(`Your session will expire in ${minutes} minute(s). Please save your work.`);
        }
        
        // Reset warning flag after some time
        setTimeout(() => {
            this.warningShown = false;
        }, 120000); // Reset after 2 minutes
    }
    
    setupActivityListeners() {
        // Reset warning flag on user activity
        const resetWarning = () => {
            this.warningShown = false;
        };
        
        // Listen for user activity
        ['click', 'keypress', 'scroll', 'mousemove'].forEach(event => {
            document.addEventListener(event, resetWarning, { passive: true });
        });
    }
    
    setupAjaxInterceptors() {
        // Intercept jQuery AJAX requests if jQuery is available
        if (typeof $ !== 'undefined' && $.ajaxSetup) {
            $(document).ajaxError((event, xhr, settings) => {
                if (xhr.status === 401 || xhr.status === 419) {
                    this.handleSessionExpiry();
                }
            });
        }
        
        // Intercept fetch requests
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                
                if ((response.status === 401 || response.status === 419) && 
                    !args[0].includes('/login') && 
                    !args[0].includes('/api/session-status')) {
                    this.handleSessionExpiry();
                }
                
                return response;
            } catch (error) {
                throw error;
            }
        };
    }
    
    // Public method to manually check session
    forceSessionCheck() {
        this.checkSession();
    }
    
    // Public method to stop monitoring
    stopMonitoring() {
        if (this.sessionTimer) {
            clearInterval(this.sessionTimer);
            console.log('Session monitoring stopped');
        }
    }
}

// Auto-initialize session manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on authenticated pages
    if (document.querySelector('.sidebar') || 
        window.location.pathname.startsWith('/dashboard') ||
        window.location.pathname.startsWith('/admin') ||
        window.location.pathname.startsWith('/marketer') ||
        window.location.pathname.startsWith('/property-manager') ||
        window.location.pathname.startsWith('/regional')) {
        
        window.sessionManager = new SessionManager({
            checkInterval: 60000, // Check every minute
            warningTime: 300000,  // Warn 5 minutes before expiry
            loginUrl: '/login'
        });
        
        console.log('Session manager initialized for authenticated page');
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SessionManager;
}