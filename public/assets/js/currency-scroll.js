/**
 * Currency Scroll Component JS
 * Handles horizontal scrolling, dot navigation, and auto-cycling
 */

// Track active slide index per wrapper
const currencyScrollState = {};

function currencyScrollInit(wrapperId) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) return;

    const track = document.getElementById(wrapperId + '-track');
    if (!track) return;

    const slides = track.querySelectorAll('.currency-scroll-slide');
    if (slides.length <= 1) return;

    currencyScrollState[wrapperId] = {
        currentIndex: 0,
        totalSlides: slides.length,
        intervalId: null
    };

    // Start auto-cycling if enabled
    if (wrapper.classList.contains('auto-cycling')) {
        const interval = parseInt(wrapper.dataset.interval) || 4000;
        currencyScrollState[wrapperId].intervalId = setInterval(function () {
            currencyScrollNext(wrapperId);
        }, interval);

        // Pause on hover
        wrapper.addEventListener('mouseenter', function () {
            clearInterval(currencyScrollState[wrapperId].intervalId);
        });

        wrapper.addEventListener('mouseleave', function () {
            currencyScrollState[wrapperId].intervalId = setInterval(function () {
                currencyScrollNext(wrapperId);
            }, interval);
        });
    }

    // Handle scroll snap alignment for swipe
    track.addEventListener('scroll', function () {
        const slideWidth = track.offsetWidth;
        if (slideWidth === 0) return;
        const newIndex = Math.round(track.scrollLeft / slideWidth);
        if (newIndex !== currencyScrollState[wrapperId].currentIndex) {
            currencyScrollState[wrapperId].currentIndex = newIndex;
            currencyScrollUpdateDots(wrapperId, newIndex);
        }
    });
}

function currencyScrollNext(wrapperId) {
    const state = currencyScrollState[wrapperId];
    if (!state) return;

    const nextIndex = (state.currentIndex + 1) % state.totalSlides;
    currencyScrollTo(wrapperId, nextIndex);
}

function currencyScrollPrev(wrapperId) {
    const state = currencyScrollState[wrapperId];
    if (!state) return;

    const prevIndex = (state.currentIndex - 1 + state.totalSlides) % state.totalSlides;
    currencyScrollTo(wrapperId, prevIndex);
}

function currencyScrollTo(wrapperId, index) {
    const track = document.getElementById(wrapperId + '-track');
    if (!track) return;

    const state = currencyScrollState[wrapperId];
    if (!state) return;

    state.currentIndex = index;
    const slideWidth = track.offsetWidth;
    track.scrollTo({
        left: slideWidth * index,
        behavior: 'smooth'
    });

    currencyScrollUpdateDots(wrapperId, index);
}

function currencyScrollUpdateDots(wrapperId, activeIndex) {
    const dotsContainer = document.getElementById(wrapperId + '-dots');
    if (!dotsContainer) return;

    const dots = dotsContainer.querySelectorAll('.dot');
    dots.forEach(function (dot, i) {
        dot.classList.toggle('active', i === activeIndex);
    });
}

// Auto-initialize all currency scroll components on page load
document.addEventListener('DOMContentLoaded', function () {
    const wrappers = document.querySelectorAll('.currency-scroll-wrapper:not(.single)');
    wrappers.forEach(function (wrapper) {
        currencyScrollInit(wrapper.id);
    });
});
