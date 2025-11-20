/**
 * Chrome Automatic Dark Mode - Exact JavaScript Implementation
 * Replicates Chrome's auto-dark-mode behavior and controls
 */

(function() {
    'use strict';

    // Chrome's dark mode configuration
    const CHROME_DARK_CONFIG = {
        STORAGE_KEY: 'chrome-dark-mode-preference',
        TIMER_STORAGE_KEY: 'chrome-dark-mode-timer-enabled',
        ATTRIBUTE: 'data-chrome-dark',
        STATES: {
            OFF: 'false',
            ON: 'true', 
            AUTO: 'auto',
            TIMER: 'timer'
        },
        TIMER: {
            DARK_HOUR: 19,  // 7 PM
            LIGHT_HOUR: 7   // 7 AM
        }
    };

    // IMMEDIATE DARK MODE APPLICATION - Prevents FOUC (Flash of Unstyled Content)
    // This runs immediately when the script loads, before DOM is ready
    (function immediatelyApplyDarkMode() {
        const stored = localStorage.getItem(CHROME_DARK_CONFIG.STORAGE_KEY);
        let shouldBeDark = false;

        if (stored === CHROME_DARK_CONFIG.STATES.ON) {
            shouldBeDark = true;
        } else if (stored === CHROME_DARK_CONFIG.STATES.TIMER) {
            const hour = new Date().getHours();
            shouldBeDark = hour >= CHROME_DARK_CONFIG.TIMER.DARK_HOUR || hour < CHROME_DARK_CONFIG.TIMER.LIGHT_HOUR;
        } else if (stored === CHROME_DARK_CONFIG.STATES.AUTO || !stored) {
            // Check system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                shouldBeDark = true;
            }
        }

        // Apply immediately to prevent flash
        if (shouldBeDark) {
            document.documentElement.setAttribute(CHROME_DARK_CONFIG.ATTRIBUTE, CHROME_DARK_CONFIG.STATES.ON);
        } else {
            document.documentElement.setAttribute(CHROME_DARK_CONFIG.ATTRIBUTE, CHROME_DARK_CONFIG.STATES.OFF);
        }
    })();

    /**
     * Chrome's system preference detection
     */
    function getSystemPreference() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    /**
     * Check if current time should be dark mode (7PM - 7AM)
     */
    function shouldBeDarkByTime() {
        const now = new Date();
        const hour = now.getHours();
        
        // Dark mode from 7 PM (19:00) to 7 AM (07:00)
        return hour >= CHROME_DARK_CONFIG.TIMER.DARK_HOUR || hour < CHROME_DARK_CONFIG.TIMER.LIGHT_HOUR;
    }

    /**
     * Hybrid automatic + manual preference resolution
     * - Automatic switching at 7PM/7AM (default behavior)
     * - Manual override available (light/dark only)
     * - Automatic switching resumes at next time change
     */
    function resolvePreference() {
        const stored = localStorage.getItem(CHROME_DARK_CONFIG.STORAGE_KEY);
        const manualOverrideTime = localStorage.getItem('chrome-dark-manual-override-time');
        const currentHour = new Date().getHours();
        
        // Check if we have a manual override
        if (stored === CHROME_DARK_CONFIG.STATES.ON || stored === CHROME_DARK_CONFIG.STATES.OFF) {
            // Check if it's time to resume automatic switching
            if (manualOverrideTime) {
                const overrideHour = parseInt(manualOverrideTime);
                
                // Resume automatic at 7AM or 7PM (whichever comes first)
                if ((overrideHour < 7 && currentHour >= 7) || 
                    (overrideHour < 19 && currentHour >= 19) ||
                    (overrideHour >= 19 && currentHour >= 7 && currentHour < overrideHour)) {
                    
                    // Clear manual override and resume automatic
                    localStorage.removeItem(CHROME_DARK_CONFIG.STORAGE_KEY);
                    localStorage.removeItem('chrome-dark-manual-override-time');
                    
                    console.log('Resuming automatic dark mode switching');
                    return shouldBeDarkByTime() ? 'dark' : 'light';
                }
            }
            
            // Return manual preference
            return stored === CHROME_DARK_CONFIG.STATES.ON ? 'dark' : 'light';
        }
        
        // Default: Automatic time-based switching (7PM-7AM)
        return shouldBeDarkByTime() ? 'dark' : 'light';
    }

    /**
     * Apply Chrome's dark mode to the page
     */
    function applyChromeDarkMode(mode) {
        const html = document.documentElement;
        
        if (mode === 'dark') {
            html.setAttribute(CHROME_DARK_CONFIG.ATTRIBUTE, CHROME_DARK_CONFIG.STATES.ON);
            console.log('Chrome Dark Mode: ON');
        } else {
            html.setAttribute(CHROME_DARK_CONFIG.ATTRIBUTE, CHROME_DARK_CONFIG.STATES.OFF);
            console.log('Chrome Dark Mode: OFF');
        }
        
        updateToggleButton(mode);
        
        // Dispatch Chrome-style event
        window.dispatchEvent(new CustomEvent('chromeDarkModeChanged', {
            detail: { mode }
        }));
    }

    /**
     * Show mode notification with auto-resume info
     */
    function showModeNotification(state, mode, autoResumeText = '') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            z-index: 10000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            text-align: center;
            max-width: 300px;
        `;
        
        let message = '';
        if (mode === 'dark') {
            message = '🌙 Dark Mode';
        } else {
            message = '☀️ Light Mode';
        }
        
        if (autoResumeText) {
            message += `\n${autoResumeText}`;
        }
        
        notification.textContent = message;
        notification.style.whiteSpace = 'pre-line';
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(-50%) translateY(0)';
        }, 100);
        
        // Remove after 4 seconds (longer for auto-resume message)
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(-50%) translateY(-20px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }

    /**
     * Simple toggle button update: Light ↔ Dark
     */
    function updateToggleButton(mode) {
        const button = document.getElementById('chrome-dark-toggle');
        if (!button) return;

        if (mode === 'dark') {
            button.innerHTML = '☀️';
            button.title = 'Switch to light mode';
        } else {
            button.innerHTML = '🌙';
            button.title = 'Switch to dark mode';
        }
    }

    /**
     * Simple toggle functionality: Light ↔ Dark
     * Automatic switching resumes at next 7AM/7PM
     */
    function toggleChromeDarkMode() {
        const current = resolvePreference();
        const currentHour = new Date().getHours();
        
        // Simple toggle: dark ↔ light
        const newMode = current === 'dark' ? 'light' : 'dark';
        const newState = newMode === 'dark' ? CHROME_DARK_CONFIG.STATES.ON : CHROME_DARK_CONFIG.STATES.OFF;
        
        // Store manual preference and override time
        localStorage.setItem(CHROME_DARK_CONFIG.STORAGE_KEY, newState);
        localStorage.setItem('chrome-dark-manual-override-time', currentHour.toString());
        
        applyChromeDarkMode(newMode);
        
        // Show notification with auto-resume info
        const nextAutoSwitch = currentHour < 7 ? '7:00 AM' : 
                              currentHour < 19 ? '7:00 PM' : '7:00 AM tomorrow';
        
        showModeNotification(newState, newMode, `Auto-resume at ${nextAutoSwitch}`);
        
        console.log(`Manual override: ${newMode} mode (auto-resume at ${nextAutoSwitch})`);
    }

    /**
     * Create simple toggle button (Light ↔ Dark)
     */
    function createToggleButton() {
        if (document.getElementById('chrome-dark-toggle')) {
            return;
        }

        const button = document.createElement('button');
        button.id = 'chrome-dark-toggle';
        button.className = 'chrome-dark-toggle';
        button.setAttribute('aria-label', 'Toggle dark mode');
        
        // Set initial icon based on current mode
        const currentMode = resolvePreference();
        
        if (currentMode === 'dark') {
            button.innerHTML = '☀️';
            button.title = 'Switch to light mode';
        } else {
            button.innerHTML = '🌙';
            button.title = 'Switch to dark mode';
        }
        
        button.addEventListener('click', toggleChromeDarkMode);
        
        document.body.appendChild(button);
        console.log('Dark Mode toggle created - automatic switching with manual override');
    }

    /**
     * Chrome's image analysis (simplified version)
     */
    function analyzeImages() {
        const images = document.querySelectorAll('img');
        
        images.forEach(img => {
            // Chrome's heuristics for image preservation
            const src = img.src.toLowerCase();
            const alt = (img.alt || '').toLowerCase();
            const className = (img.className || '').toLowerCase();
            
            // Check if it's likely a photo vs graphic
            const isLikelyPhoto = src.includes('photo') || 
                                 src.includes('image') || 
                                 alt.includes('photo') ||
                                 className.includes('photo');
            
            const isLikelyGraphic = src.includes('icon') || 
                                   src.includes('logo') || 
                                   src.includes('svg') ||
                                   className.includes('icon') ||
                                   className.includes('logo');
            
            // Chrome preserves photos but may invert graphics
            if (isLikelyPhoto && !isLikelyGraphic) {
                img.style.filter = 'invert(1) hue-rotate(180deg)';
            }
        });
    }

    /**
     * Chrome's system preference listener
     */
    function setupSystemListener() {
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            mediaQuery.addEventListener('change', function(e) {
                const stored = localStorage.getItem(CHROME_DARK_CONFIG.STORAGE_KEY);
                
                // Only auto-switch if no explicit preference is set
                if (!stored || stored === CHROME_DARK_CONFIG.STATES.AUTO) {
                    const newMode = e.matches ? 'dark' : 'light';
                    applyChromeDarkMode(newMode);
                }
            });
        }
    }

    /**
     * Setup automatic switching and manual override resume
     */
    function setupAutomaticSwitching() {
        // Check every minute for automatic switching and manual override resume
        setInterval(() => {
            const currentMode = document.documentElement.getAttribute(CHROME_DARK_CONFIG.ATTRIBUTE) === 'true' ? 'dark' : 'light';
            const shouldBeMode = resolvePreference(); // This handles both automatic and manual override logic
            
            if (currentMode !== shouldBeMode) {
                applyChromeDarkMode(shouldBeMode);
                
                // Check if this was an automatic resume
                const stored = localStorage.getItem(CHROME_DARK_CONFIG.STORAGE_KEY);
                if (!stored) {
                    console.log(`Auto-switched to ${shouldBeMode} mode`);
                    
                    // Show subtle notification for automatic switching
                    const notification = document.createElement('div');
                    notification.style.cssText = `
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        background: rgba(0, 0, 0, 0.8);
                        color: white;
                        padding: 8px 16px;
                        border-radius: 6px;
                        font-size: 12px;
                        z-index: 9999;
                        opacity: 0;
                        transition: opacity 0.3s ease;
                    `;
                    notification.textContent = `Auto-switched to ${shouldBeMode} mode`;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => notification.style.opacity = '1', 100);
                    setTimeout(() => {
                        notification.style.opacity = '0';
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 300);
                    }, 2000);
                }
            }
        }, 60000); // Check every minute
        
        console.log('Automatic switching initialized - 7PM/7AM with manual override support');
    }

    /**
     * Chrome's initialization sequence with timer support
     */
    function initializeChromeDarkMode() {
        // Apply initial mode
        const initialMode = resolvePreference();
        applyChromeDarkMode(initialMode);
        
        // Create toggle button
        createToggleButton();
        
        // Setup system preference listener
        setupSystemListener();
        
        // Setup automatic switching
        setupAutomaticSwitching();
        
        // Analyze images (Chrome does this continuously)
        analyzeImages();
        
        // Re-analyze images when new ones load
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    analyzeImages();
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('Chrome Dark Mode with Timer initialized');
    }

    /**
     * Public API matching Chrome's interface with timer support
     */
    window.ChromeDarkMode = {
        toggle: toggleChromeDarkMode,
        enable: () => {
            localStorage.setItem(CHROME_DARK_CONFIG.STORAGE_KEY, CHROME_DARK_CONFIG.STATES.ON);
            applyChromeDarkMode('dark');
        },
        disable: () => {
            localStorage.setItem(CHROME_DARK_CONFIG.STORAGE_KEY, CHROME_DARK_CONFIG.STATES.OFF);
            applyChromeDarkMode('light');
        },
        auto: () => {
            localStorage.setItem(CHROME_DARK_CONFIG.STORAGE_KEY, CHROME_DARK_CONFIG.STATES.AUTO);
            applyChromeDarkMode(getSystemPreference());
        },
        timer: () => {
            localStorage.setItem(CHROME_DARK_CONFIG.STORAGE_KEY, CHROME_DARK_CONFIG.STATES.TIMER);
            const mode = shouldBeDarkByTime() ? 'dark' : 'light';
            applyChromeDarkMode(mode);
            showModeNotification(CHROME_DARK_CONFIG.STATES.TIMER, mode);
        },
        getMode: resolvePreference,
        isEnabled: () => resolvePreference() === 'dark',
        isTimerMode: () => localStorage.getItem(CHROME_DARK_CONFIG.STORAGE_KEY) === CHROME_DARK_CONFIG.STATES.TIMER,
        getNextSwitchTime: () => {
            if (!window.ChromeDarkMode.isTimerMode()) return null;
            
            const now = new Date();
            const hour = now.getHours();
            const nextSwitch = new Date(now);
            
            if (hour >= CHROME_DARK_CONFIG.TIMER.DARK_HOUR || hour < CHROME_DARK_CONFIG.TIMER.LIGHT_HOUR) {
                // Currently dark, next switch is to light at 7 AM
                nextSwitch.setHours(CHROME_DARK_CONFIG.TIMER.LIGHT_HOUR, 0, 0, 0);
                if (hour >= CHROME_DARK_CONFIG.TIMER.DARK_HOUR) {
                    nextSwitch.setDate(nextSwitch.getDate() + 1);
                }
            } else {
                // Currently light, next switch is to dark at 7 PM
                nextSwitch.setHours(CHROME_DARK_CONFIG.TIMER.DARK_HOUR, 0, 0, 0);
            }
            
            return nextSwitch;
        }
    };

    // Enable transitions after initial load to prevent FOUC
    function enableTransitions() {
        // Wait a bit to ensure page is fully rendered
        setTimeout(() => {
            document.documentElement.classList.add('transitions-enabled');
            // Also update the CSS to enable smooth transitions
            const style = document.createElement('style');
            style.textContent = `
                html.transitions-enabled {
                    transition: filter 0.3s ease !important;
                }
                html.transitions-enabled * {
                    transition: filter 0.3s ease !important;
                }
            `;
            document.head.appendChild(style);
        }, 200);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initializeChromeDarkMode();
            enableTransitions();
        });
    } else {
        initializeChromeDarkMode();
        enableTransitions();
    }

    // Chrome's debug interface with timer info
    window.debugChromeDarkMode = function() {
        console.log('=== CHROME DARK MODE DEBUG ===');
        console.log('Current mode:', resolvePreference());
        console.log('System preference:', getSystemPreference());
        console.log('Stored preference:', localStorage.getItem(CHROME_DARK_CONFIG.STORAGE_KEY));
        console.log('HTML attribute:', document.documentElement.getAttribute(CHROME_DARK_CONFIG.ATTRIBUTE));
        console.log('Toggle button exists:', !!document.getElementById('chrome-dark-toggle'));
        console.log('Timer mode active:', window.ChromeDarkMode.isTimerMode());
        console.log('Should be dark by time:', shouldBeDarkByTime());
        console.log('Current hour:', new Date().getHours());
        if (window.ChromeDarkMode.isTimerMode()) {
            console.log('Next switch time:', window.ChromeDarkMode.getNextSwitchTime());
        }
        console.log('==============================');
    };

})();