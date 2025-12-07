/**
 * Premium Mobile UI Interactions for EasyRent
 */

(function() {
    'use strict';

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        if (window.innerWidth > 991) return;
        
        initMobileMenu();
        initSmoothScroll();
    }

    function initMobileMenu() {
        const toggler = document.querySelector('.navbar-toggler');
        const collapse = document.querySelector('.navbar-collapse');
        const navLinks = document.querySelectorAll('.navbar-collapse .nav-link');

        if (!toggler || !collapse) return;

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (collapse.classList.contains('show')) {
                if (!collapse.contains(e.target) && !toggler.contains(e.target)) {
                    toggler.click();
                }
            }
        });

        // Close menu when clicking on a link
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (collapse.classList.contains('show')) {
                    setTimeout(() => toggler.click(), 300);
                }
            });
        });

        // Prevent body scroll when menu is open
        toggler.addEventListener('click', function() {
            setTimeout(() => {
                document.body.style.overflow = collapse.classList.contains('show') ? 'hidden' : '';
            }, 100);
        });

        // Add active class to current page
        const currentPath = window.location.pathname;
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    }

    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#' || href === '#!') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    console.log('✨ Premium Mobile UI initialized');
})();
