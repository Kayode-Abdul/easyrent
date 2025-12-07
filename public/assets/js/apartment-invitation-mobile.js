/**
 * Mobile-Enhanced JavaScript for EasyRent Link Authentication System
 * Provides touch-friendly interactions and mobile-optimized functionality
 */

(function() {
    'use strict';

    // ========================================
    // MOBILE DETECTION AND SETUP
    // ========================================

    const isMobile = window.innerWidth <= 768;
    const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    // ========================================
    // ENHANCED FORM INTERACTIONS
    // ========================================

    function initializeFormEnhancements() {
        // Prevent zoom on iOS when focusing inputs
        if (isMobile && /iPhone|iPad|iPod/.test(navigator.userAgent)) {
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.style.fontSize !== '16px') {
                    input.style.fontSize = '16px';
                }
            });
        }

        // Enhanced form validation feedback
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const invalidInputs = form.querySelectorAll(':invalid');
                if (invalidInputs.length > 0) {
                    e.preventDefault();
                    
                    // Scroll to first invalid input
                    invalidInputs[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    
                    // Add shake animation
                    invalidInputs[0].classList.add('shake-animation');
                    setTimeout(() => {
                        invalidInputs[0].classList.remove('shake-animation');
                    }, 600);
                }
            });
        });

        // Auto-resize textareas
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });
    }

    // ========================================
    // TOUCH-FRIENDLY INTERACTIONS
    // ========================================

    function initializeTouchInteractions() {
        // Enhanced card interactions
        const cards = document.querySelectorAll('.card, .payment-method-card');
        cards.forEach(card => {
            if (isTouch) {
                card.addEventListener('touchstart', function() {
                    this.classList.add('touch-active');
                });

                card.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.classList.remove('touch-active');
                    }, 150);
                });
            }
        });

        // Enhanced button feedback
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            if (isTouch) {
                button.addEventListener('touchstart', function() {
                    if (!this.disabled) {
                        this.classList.add('btn-pressed');
                    }
                });

                button.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.classList.remove('btn-pressed');
                    }, 100);
                });
            }
        });
    }

    // ========================================
    // MOBILE NAVIGATION ENHANCEMENTS
    // ========================================

    function initializeMobileNavigation() {
        // Smooth scroll for anchor links
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href.length > 1) {
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Back to top functionality for long pages
        if (document.body.scrollHeight > window.innerHeight * 2) {
            createBackToTopButton();
        }
    }

    function createBackToTopButton() {
        const backToTop = document.createElement('button');
        backToTop.innerHTML = '<i class="fas fa-chevron-up"></i>';
        backToTop.className = 'btn btn-primary back-to-top';
        backToTop.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: none;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        `;

        document.body.appendChild(backToTop);

        // Show/hide based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });

        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ========================================
    // ENHANCED LOADING STATES
    // ========================================

    function initializeLoadingStates() {
        // Enhanced button loading states
        const submitButtons = document.querySelectorAll('button[type="submit"], .btn-submit');
        submitButtons.forEach(button => {
            const form = button.closest('form');
            if (form) {
                form.addEventListener('submit', function() {
                    setButtonLoading(button, true);
                });
            }
        });

        // Payment button loading state
        const paymentButton = document.getElementById('proceedPaymentBtn');
        if (paymentButton) {
            paymentButton.addEventListener('click', function() {
                setButtonLoading(this, true);
            });
        }
    }

    function setButtonLoading(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            button.classList.add('btn-loading');
            button.setAttribute('data-original-text', button.innerHTML);
            
            const loadingText = button.getAttribute('data-loading-text') || 'Processing...';
            button.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${loadingText}`;
        } else {
            button.disabled = false;
            button.classList.remove('btn-loading');
            button.innerHTML = button.getAttribute('data-original-text') || button.innerHTML;
        }
    }

    // ========================================
    // MOBILE-OPTIMIZED MODALS AND ALERTS
    // ========================================

    function initializeMobileAlerts() {
        // Convert alerts to mobile-friendly toasts on small screens
        if (isMobile) {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                // Convert to toast-style positioning
                alert.style.cssText += `
                    position: fixed;
                    top: 20px;
                    left: 20px;
                    right: 20px;
                    z-index: 1050;
                    margin: 0;
                `;

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-100%)';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 300);
                }, 5000);
            });
        }
    }

    // ========================================
    // PERFORMANCE OPTIMIZATIONS
    // ========================================

    function initializePerformanceOptimizations() {
        // Lazy load images
        const images = document.querySelectorAll('img[data-src]');
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            images.forEach(img => {
                img.src = img.dataset.src;
            });
        }

        // Debounce resize events
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Recalculate mobile state
                const newIsMobile = window.innerWidth <= 768;
                if (newIsMobile !== isMobile) {
                    location.reload(); // Simple approach for layout changes
                }
            }, 250);
        });
    }

    // ========================================
    // ACCESSIBILITY ENHANCEMENTS
    // ========================================

    function initializeAccessibilityEnhancements() {
        // Enhanced focus management
        const focusableElements = document.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        // Skip to main content link
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.textContent = 'Skip to main content';
        skipLink.className = 'sr-only sr-only-focusable';
        skipLink.style.cssText = `
            position: absolute;
            top: -40px;
            left: 6px;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        `;

        skipLink.addEventListener('focus', function() {
            this.style.cssText = `
                position: absolute;
                top: 6px;
                left: 6px;
                width: auto;
                height: auto;
                padding: 8px 16px;
                margin: 0;
                overflow: visible;
                clip: auto;
                white-space: normal;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                z-index: 9999;
            `;
        });

        skipLink.addEventListener('blur', function() {
            this.style.cssText = `
                position: absolute;
                top: -40px;
                left: 6px;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            `;
        });

        document.body.insertBefore(skipLink, document.body.firstChild);

        // Add main content landmark if not present
        if (!document.getElementById('main-content')) {
            const mainContent = document.querySelector('main, .container, .main-content');
            if (mainContent) {
                mainContent.id = 'main-content';
            }
        }
    }

    // ========================================
    // INITIALIZATION
    // ========================================

    function initialize() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initialize);
            return;
        }

        try {
            initializeFormEnhancements();
            initializeTouchInteractions();
            initializeMobileNavigation();
            initializeLoadingStates();
            initializeMobileAlerts();
            initializePerformanceOptimizations();
            initializeAccessibilityEnhancements();

            // Add CSS classes for JavaScript-enabled features
            document.documentElement.classList.add('js-enabled');
            if (isMobile) {
                document.documentElement.classList.add('mobile-device');
            }
            if (isTouch) {
                document.documentElement.classList.add('touch-device');
            }

            console.log('EasyRent Mobile Enhancements initialized successfully');
        } catch (error) {
            console.error('Error initializing mobile enhancements:', error);
        }
    }

    // ========================================
    // PUBLIC API
    // ========================================

    window.EasyRentMobile = {
        setButtonLoading: setButtonLoading,
        isMobile: isMobile,
        isTouch: isTouch
    };

    // Auto-initialize
    initialize();

})();

// ========================================
// CSS ANIMATIONS (Added via JavaScript)
// ========================================

const mobileStyles = document.createElement('style');
mobileStyles.textContent = `
    .touch-active {
        transform: scale(0.98);
        transition: transform 0.1s ease;
    }

    .btn-pressed {
        transform: scale(0.95);
        transition: transform 0.1s ease;
    }

    .shake-animation {
        animation: shake 0.6s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }

    .back-to-top {
        transition: all 0.3s ease;
    }

    .back-to-top:hover {
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .sr-only-focusable:focus {
            position: absolute !important;
            width: auto !important;
            height: auto !important;
            padding: 8px 16px !important;
            margin: 0 !important;
            overflow: visible !important;
            clip: auto !important;
            white-space: normal !important;
        }
    }
`;

document.head.appendChild(mobileStyles);