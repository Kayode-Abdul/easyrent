/**
 * Enhanced Mobile Interactions for EasyRent
 * Touch gestures, pull-to-refresh, and mobile-specific features
 */

(function() {
    'use strict';

    // Check if mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    if (!isMobile && !isTouch) return;

    // ========================================
    // PULL TO REFRESH
    // ========================================
    let pullStartY = 0;
    let pullMoveY = 0;
    let isPulling = false;
    const pullThreshold = 80;

    function initPullToRefresh() {
        const container = document.querySelector('.dashboard-container') || document.body;
        
        container.addEventListener('touchstart', function(e) {
            if (window.scrollY === 0) {
                pullStartY = e.touches[0].clientY;
                isPulling = true;
            }
        }, { passive: true });

        container.addEventListener('touchmove', function(e) {
            if (!isPulling) return;
            
            pullMoveY = e.touches[0].clientY - pullStartY;
            
            if (pullMoveY > 0 && window.scrollY === 0) {
                // Show pull indicator
                const indicator = document.querySelector('.pull-to-refresh-indicator');
                if (indicator) {
                    indicator.style.top = Math.min(pullMoveY - 60, 0) + 'px';
                    indicator.style.opacity = Math.min(pullMoveY / pullThreshold, 1);
                }
            }
        }, { passive: true });

        container.addEventListener('touchend', function() {
            if (isPulling && pullMoveY > pullThreshold) {
                // Trigger refresh
                window.location.reload();
            }
            
            isPulling = false;
            pullStartY = 0;
            pullMoveY = 0;
            
            const indicator = document.querySelector('.pull-to-refresh-indicator');
            if (indicator) {
                indicator.style.top = '-60px';
                indicator.style.opacity = '0';
            }
        }, { passive: true });
    }

    // ========================================
    // SWIPEABLE CARDS
    // ========================================
    function initSwipeableCards() {
        const cards = document.querySelectorAll('.swipeable-card');
        
        cards.forEach(card => {
            let startX = 0;
            let currentX = 0;
            let isSwiping = false;

            card.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
                isSwiping = true;
                card.classList.add('swiping');
            }, { passive: true });

            card.addEventListener('touchmove', function(e) {
                if (!isSwiping) return;
                
                currentX = e.touches[0].clientX - startX;
                
                // Only allow left swipe
                if (currentX < 0) {
                    card.style.transform = `translateX(${currentX}px)`;
                }
            }, { passive: true });

            card.addEventListener('touchend', function() {
                isSwiping = false;
                card.classList.remove('swiping');
                
                // If swiped more than 100px, show actions
                if (currentX < -100) {
                    card.style.transform = 'translateX(-120px)';
                    card.classList.add('swiped');
                } else {
                    card.style.transform = 'translateX(0)';
                    card.classList.remove('swiped');
                }
                
                currentX = 0;
            }, { passive: true });
        });
    }

    // ========================================
    // BOTTOM NAVIGATION
    // ========================================
    function initBottomNav() {
        const currentPath = window.location.pathname;
        const navItems = document.querySelectorAll('.bottom-nav-item');
        
        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (currentPath.includes(href) && href !== '/') {
                item.classList.add('active');
            } else if (currentPath === '/' && href === '/') {
                item.classList.add('active');
            }
        });

        // Add body class for padding
        if (document.querySelector('.bottom-nav')) {
            document.body.classList.add('has-bottom-nav');
        }
    }

    // ========================================
    // TOUCH FEEDBACK
    // ========================================
    function initTouchFeedback() {
        // Add ripple effect to buttons
        const buttons = document.querySelectorAll('.btn, .card, .property-card');
        
        buttons.forEach(button => {
            button.addEventListener('touchstart', function(e) {
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                
                const rect = button.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.touches[0].clientX - rect.left - size / 2;
                const y = e.touches[0].clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                
                button.style.position = 'relative';
                button.style.overflow = 'hidden';
                button.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            }, { passive: true });
        });
    }

    // ========================================
    // IMPROVED FORM INPUTS
    // ========================================
    function initFormEnhancements() {
        // Auto-resize textareas
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });

        // Floating labels
        const inputs = document.querySelectorAll('.form-floating .form-control');
        inputs.forEach(input => {
            // Check if input has value on load
            if (input.value) {
                input.classList.add('has-value');
            }

            input.addEventListener('blur', function() {
                if (this.value) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });

        // Prevent zoom on input focus (iOS)
        const formControls = document.querySelectorAll('input, select, textarea');
        formControls.forEach(control => {
            control.addEventListener('focus', function() {
                const viewport = document.querySelector('meta[name=viewport]');
                if (viewport) {
                    viewport.setAttribute('content', 'width=device-width, initial-scale=1, maximum-scale=1');
                }
            });

            control.addEventListener('blur', function() {
                const viewport = document.querySelector('meta[name=viewport]');
                if (viewport) {
                    viewport.setAttribute('content', 'width=device-width, initial-scale=1');
                }
            });
        });
    }

    // ========================================
    // MODAL IMPROVEMENTS
    // ========================================
    function initModalEnhancements() {
        // Close modal on backdrop click
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    $(modal).modal('hide');
                }
            });
        });

        // Prevent body scroll when modal is open
        $(document).on('show.bs.modal', '.modal', function() {
            document.body.style.overflow = 'hidden';
        });

        $(document).on('hidden.bs.modal', '.modal', function() {
            document.body.style.overflow = '';
        });
    }

    // ========================================
    // LAZY LOAD IMAGES
    // ========================================
    function initLazyLoading() {
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

            const images = document.querySelectorAll('img.lazy');
            images.forEach(img => imageObserver.observe(img));
        }
    }

    // ========================================
    // SKELETON SCREENS
    // ========================================
    function showSkeletonScreen(container) {
        const skeleton = `
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
        `;
        container.innerHTML = skeleton;
    }

    function hideSkeletonScreen(container) {
        const skeletons = container.querySelectorAll('.skeleton');
        skeletons.forEach(skeleton => skeleton.remove());
    }

    // ========================================
    // HAPTIC FEEDBACK (iOS)
    // ========================================
    function triggerHaptic(type = 'light') {
        if (window.navigator && window.navigator.vibrate) {
            const patterns = {
                light: [10],
                medium: [20],
                heavy: [30],
                success: [10, 50, 10],
                error: [20, 100, 20]
            };
            window.navigator.vibrate(patterns[type] || patterns.light);
        }
    }

    // ========================================
    // SMOOTH SCROLL
    // ========================================
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // ========================================
    // NETWORK STATUS
    // ========================================
    function initNetworkStatus() {
        window.addEventListener('online', function() {
            showToast('Back online', 'success');
        });

        window.addEventListener('offline', function() {
            showToast('No internet connection', 'error');
        });
    }

    // ========================================
    // TOAST NOTIFICATIONS
    // ========================================
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `mobile-toast mobile-toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 90px;
            left: 50%;
            transform: translateX(-50%);
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
            color: white;
            padding: 12px 24px;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease;
        `;

        document.body.appendChild(toast);
        triggerHaptic(type);

        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ========================================
    // INITIALIZE ALL
    // ========================================
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        console.log('🚀 Initializing mobile enhancements...');

        initPullToRefresh();
        initSwipeableCards();
        initBottomNav();
        initTouchFeedback();
        initFormEnhancements();
        initModalEnhancements();
        initLazyLoading();
        initSmoothScroll();
        initNetworkStatus();

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideUp {
                from {
                    transform: translateX(-50%) translateY(20px);
                    opacity: 0;
                }
                to {
                    transform: translateX(-50%) translateY(0);
                    opacity: 1;
                }
            }

            @keyframes slideDown {
                from {
                    transform: translateX(-50%) translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(-50%) translateY(20px);
                    opacity: 0;
                }
            }

            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            }

            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        console.log('✅ Mobile enhancements initialized');
    }

    // Export functions for external use
    window.MobileEnhanced = {
        showToast,
        triggerHaptic,
        showSkeletonScreen,
        hideSkeletonScreen
    };

    // Auto-initialize
    init();

})();
