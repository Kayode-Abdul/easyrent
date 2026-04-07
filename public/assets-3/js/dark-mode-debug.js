/**
 * Dark Mode Debug and Force Activation
 * Use this to test and troubleshoot dark mode
 */

// Force activate dark mode for testing
function forceDarkMode() {
    document.documentElement.setAttribute('data-chrome-dark', 'true');
    console.log('✅ Dark mode FORCED ON');
    console.log('HTML attribute set:', document.documentElement.getAttribute('data-chrome-dark'));
}

// Force deactivate dark mode
function forceLightMode() {
    document.documentElement.setAttribute('data-chrome-dark', 'false');
    console.log('✅ Dark mode FORCED OFF');
    console.log('HTML attribute set:', document.documentElement.getAttribute('data-chrome-dark'));
}

// Debug function to check everything
function debugDarkMode() {
    console.log('=== DARK MODE DEBUG REPORT ===');
    console.log('1. HTML data-chrome-dark attribute:', document.documentElement.getAttribute('data-chrome-dark'));
    console.log('2. Chrome dark CSS loaded:', !!document.querySelector('link[href*="chrome-dark-mode.css"]'));
    console.log('3. Hero dark fix CSS loaded:', !!document.querySelector('link[href*="hero-dark-fix.css"]'));
    console.log('4. Hero element found:', !!document.querySelector('.hero-wrap'));
    console.log('5. Toggle button exists:', !!document.getElementById('chrome-dark-toggle'));
    console.log('6. ChromeDarkMode object available:', typeof window.ChromeDarkMode !== 'undefined');
    
    // Check computed styles
    const heroElement = document.querySelector('.hero-wrap');
    if (heroElement) {
        const computedStyle = window.getComputedStyle(heroElement);
        console.log('7. Hero element filter:', computedStyle.filter);
        console.log('8. Hero element background-image:', computedStyle.backgroundImage);
        console.log('9. Hero element background-size:', computedStyle.backgroundSize);
        console.log('10. Hero element background-position:', computedStyle.backgroundPosition);
        console.log('11. Hero inline style:', heroElement.getAttribute('style'));
        console.log('12. Hero classes:', heroElement.className);
    }
    
    // Test image loading
    const testImg = new Image();
    testImg.onload = function() {
        console.log('✅ Background image loads successfully');
    };
    testImg.onerror = function() {
        console.log('❌ Background image failed to load');
    };
    testImg.src = 'assets/images/bg_1.jpg';
    
    console.log('==============================');
}

// Function to test different background image paths
function testBackgroundPaths() {
    const heroElement = document.querySelector('.hero-wrap');
    if (!heroElement) {
        console.log('❌ Hero element not found');
        return;
    }
    
    const paths = [
        'assets/images/bg_1.jpg',
        '../images/bg_1.jpg',
        './assets/images/bg_1.jpg',
        '/assets/images/bg_1.jpg'
    ];
    
    console.log('=== TESTING BACKGROUND PATHS ===');
    
    paths.forEach((path, index) => {
        const testImg = new Image();
        testImg.onload = function() {
            console.log(`✅ Path ${index + 1} works: ${path}`);
            // Apply the working path
            heroElement.style.backgroundImage = `url('${path}')`;
        };
        testImg.onerror = function() {
            console.log(`❌ Path ${index + 1} failed: ${path}`);
        };
        testImg.src = path;
    });
}

// Auto-run debug on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(debugDarkMode, 2000);
});

// Make functions globally available
window.forceDarkMode = forceDarkMode;
window.forceLightMode = forceLightMode;
window.debugDarkMode = debugDarkMode;
window.testBackgroundPaths = testBackgroundPaths;

// Add a visual indicator for dark mode status
function addDarkModeIndicator() {
    const indicator = document.createElement('div');
    indicator.id = 'dark-mode-indicator';
    indicator.style.cssText = `
        position: fixed;
        top: 10px;
        left: 10px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
        z-index: 9999;
        pointer-events: none;
    `;
    
    function updateIndicator() {
        const isDark = document.documentElement.getAttribute('data-chrome-dark') === 'true';
        const stored = localStorage.getItem('chrome-dark-mode-preference');
        const isTimer = stored === 'timer';
        const currentHour = new Date().getHours();
        
        let status = `Mode: ${isDark ? 'DARK' : 'LIGHT'}`;
        let bgColor = isDark ? 'rgba(0, 100, 0, 0.8)' : 'rgba(100, 0, 0, 0.8)';
        
        if (isTimer) {
            status += `\nTimer: ON (${currentHour}:00)`;
            status += '\nSwitch: 7AM/7PM';
            bgColor = 'rgba(100, 0, 100, 0.8)';
        } else if (stored === 'auto') {
            status += '\nAuto: SYSTEM';
            bgColor = 'rgba(0, 0, 100, 0.8)';
        }
        
        indicator.textContent = status;
        indicator.style.background = bgColor;
        indicator.style.whiteSpace = 'pre-line';
        indicator.style.minWidth = '120px';
    }
    
    updateIndicator();
    document.body.appendChild(indicator);
    
    // Update indicator when dark mode changes
    const observer = new MutationObserver(updateIndicator);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-chrome-dark']
    });
}

// Auto-add indicator
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(addDarkModeIndicator, 1000);
});
//Timer testing functions
function testTimerMode() {
    console.log('=== TIMER MODE TEST ===');
    
    // Enable timer mode
    if (typeof ChromeDarkMode !== 'undefined') {
        ChromeDarkMode.timer();
        console.log('✅ Timer mode enabled');
        console.log('Current hour:', new Date().getHours());
        console.log('Should be dark:', new Date().getHours() >= 19 || new Date().getHours() < 7);
        console.log('Next switch:', ChromeDarkMode.getNextSwitchTime());
    } else {
        console.log('❌ ChromeDarkMode not available');
    }
}

function simulateTimeSwitch() {
    console.log('=== SIMULATING TIME SWITCH ===');
    
    if (typeof ChromeDarkMode !== 'undefined' && ChromeDarkMode.isTimerMode()) {
        const currentMode = ChromeDarkMode.getMode();
        const newMode = currentMode === 'dark' ? 'light' : 'dark';
        
        // Manually trigger the switch
        if (newMode === 'dark') {
            document.documentElement.setAttribute('data-chrome-dark', 'true');
        } else {
            document.documentElement.setAttribute('data-chrome-dark', 'false');
        }
        
        console.log(`Simulated switch from ${currentMode} to ${newMode}`);
    } else {
        console.log('❌ Timer mode not active');
    }
}

// Enhanced debug function with timer info
function debugTimerMode() {
    console.log('=== TIMER MODE DEBUG ===');
    
    if (typeof ChromeDarkMode !== 'undefined') {
        console.log('Timer mode active:', ChromeDarkMode.isTimerMode());
        console.log('Current mode:', ChromeDarkMode.getMode());
        console.log('Current hour:', new Date().getHours());
        console.log('Should be dark by time:', new Date().getHours() >= 19 || new Date().getHours() < 7);
        
        if (ChromeDarkMode.isTimerMode()) {
            console.log('Next switch time:', ChromeDarkMode.getNextSwitchTime());
        }
        
        console.log('Stored preference:', localStorage.getItem('chrome-dark-mode-preference'));
    } else {
        console.log('❌ ChromeDarkMode not available');
    }
    
    console.log('========================');
}

// Make timer functions globally available
window.testTimerMode = testTimerMode;
window.simulateTimeSwitch = simulateTimeSwitch;
window.debugTimerMode = debugTimerMode;