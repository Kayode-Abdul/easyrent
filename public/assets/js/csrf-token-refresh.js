/**
 * CSRF Token Auto-Refresh Handler
 * Prevents 419 errors when tabs are left idle for long periods
 */

(function() {
    'use strict';

    const CsrfTokenManager = {
        // Configuration
        config: {
            checkInterval: 60000, // Check every minute
            warningTime: 300000, // Warn 5 minutes before expiry
            sessionLifetime: null, // Will be set from Laravel config
            lastActivity: Date.now(),
            tokenRefreshEndpoint: '/api/csrf-token',
        },

        // Initialize the manager
        init: function() {
            // Get session lifetime from meta tag or default to 120 minutes
            const lifetimeMeta = document.querySelector('meta[name="session-lifetime"]');
            this.config.sessionLifetime = lifetimeMeta 
                ? parseInt(lifetimeMeta.content) * 60000 
                : 7200000; // 120 minutes default

            // Track user activity
            this.trackActivity();

            // Start checking token validity
            this.startTokenCheck();

            // Intercept form submissions
            this.interceptForms();

            // Intercept AJAX requests
            this.interceptAjax();

            console.log('CSRF Token Manager initialized');
        },

        // Track user activity to reset idle timer
        trackActivity: function() {
            const events = ['mousedown', 'keydown', 'scroll', 'touchstart'];
            const self = this;

            events.forEach(event => {
                document.addEventListener(event, function() {
                    self.config.lastActivity = Date.now();
                }, { passive: true });
            });
        },

        // Start periodic token validity checks
        startTokenCheck: function() {
            const self = this;
            
            setInterval(function() {
                const idleTime = Date.now() - self.config.lastActivity;
                const timeUntilExpiry = self.config.sessionLifetime - idleTime;

                // If session is about to expire, show warning
                if (timeUntilExpiry <= self.config.warningTime && timeUntilExpiry > 0) {
                    self.showExpiryWarning(Math.floor(timeUntilExpiry / 60000));
                }

                // If session has expired, redirect to login
                if (timeUntilExpiry <= 0) {
                    self.handleExpiredSession();
                }
            }, this.config.checkInterval);
        },

        // Show warning that session is about to expire
        showExpiryWarning: function(minutesLeft) {
            // Only show warning once
            if (this.warningShown) return;
            this.warningShown = true;

            const message = `Your session will expire in ${minutesLeft} minute${minutesLeft !== 1 ? 's' : ''}. Any unsaved changes may be lost.`;
            
            // Use existing toast system if available
            if (typeof showToast === 'function') {
                showToast(message, 'warning');
            } else {
                alert(message);
            }

            // Reset warning flag after 2 minutes
            setTimeout(() => {
                this.warningShown = false;
            }, 120000);
        },

        // Handle expired session
        handleExpiredSession: function() {
            // Store current URL for redirect after login
            sessionStorage.setItem('redirect_after_login', window.location.pathname);
            
            // Redirect to login with expired parameter
            window.location.href = '/login?expired=1';
        },

        // Refresh CSRF token from server
        refreshToken: function() {
            return fetch('/api/csrf-token', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to refresh token');
                }
                return response.json();
            })
            .then(data => {
                if (data.token) {
                    // Update meta tag
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', data.token);
                    }

                    // Update all forms
                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = data.token;
                    });

                    // Update jQuery AJAX setup if jQuery is available
                    if (typeof $ !== 'undefined' && $.ajaxSetup) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': data.token
                            }
                        });
                    }

                    console.log('CSRF token refreshed successfully');
                    return data.token;
                }
                throw new Error('No token in response');
            })
            .catch(error => {
                console.error('Failed to refresh CSRF token:', error);
                return null;
            });
        },

        // Intercept form submissions to check token validity
        interceptForms: function() {
            const self = this;

            document.addEventListener('submit', function(e) {
                const form = e.target;
                
                // Only intercept forms with CSRF tokens
                const tokenInput = form.querySelector('input[name="_token"]');
                if (!tokenInput) return;

                // Check if session might be expired
                const idleTime = Date.now() - self.config.lastActivity;
                if (idleTime > self.config.sessionLifetime) {
                    e.preventDefault();
                    self.handleExpiredSession();
                    return false;
                }
            }, true);
        },

        // Intercept AJAX requests to handle 419 errors
        interceptAjax: function() {
            const self = this;

            // Intercept fetch requests
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                return originalFetch.apply(this, args)
                    .then(response => {
                        if (response.status === 419) {
                            self.handleExpiredSession();
                            throw new Error('Session expired');
                        }
                        return response;
                    });
            };

            // Intercept jQuery AJAX if available
            if (typeof $ !== 'undefined') {
                $(document).ajaxError(function(event, jqXHR, settings, thrownError) {
                    if (jqXHR.status === 419) {
                        self.handleExpiredSession();
                    }
                });
            }
        },

        // Get current CSRF token
        getToken: function() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            return metaTag ? metaTag.getAttribute('content') : null;
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            CsrfTokenManager.init();
        });
    } else {
        CsrfTokenManager.init();
    }

    // Expose to window for manual token refresh if needed
    window.CsrfTokenManager = CsrfTokenManager;

})();
