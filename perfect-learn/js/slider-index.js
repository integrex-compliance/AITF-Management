// Optimized slider initialization with performance improvements
function initSlider() {
    const slider = $('#js-main-slider');
    if (!slider.length || slider.hasClass('slider-initialized')) return;
    
    slider.addClass('slider-initialized');
    
    const initFn = () => {
        requestAnimationFrame(() => {
            slider.pogoSlider({
                autoplay: true,
                autoplayTimeout: 5000,
                displayProgess: true,
                preserveTargetSize: false,
                targetWidth: 1000,
                targetHeight: 300,
                responsive: true,
                pauseOnHover: false,
                lazyLoad: true,
                lazyLoadEager: 1,
                transition: 'fade',
                animationDuration: 800,
                beforeSlide: () => requestAnimationFrame(() => $(window).trigger('resize'))
            });
            
            // Add performance class to slider container
            slider.closest('.slider-container').addClass('optimized-slider');
        });
    };

    // Use requestIdleCallback if available, otherwise use setTimeout
    if ('requestIdleCallback' in window) {
        requestIdleCallback(initFn, { timeout: 2000 });
    } else {
        setTimeout(initFn, 0);
    }
}

// Initialize slider when visible
function initSliderWhenVisible() {
    const slider = $('#js-main-slider');
    if (!slider.length) return;

    if (!window.IntersectionObserver) {
        $(document).ready(initSlider);
        return;
    }

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                initSlider();
                observer.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: '200px',
        threshold: 0.01,
        root: null
    });

    observer.observe(slider[0]);
}

// Start initialization when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSliderWhenVisible);
} else {
    initSliderWhenVisible();
}

// Performance optimizations
function initPerformanceOptimizations() {
    // Add scroll optimization
    let isScrolling;
    window.addEventListener('scroll', function() {
        document.body.classList.add('scrolling');
        window.clearTimeout(isScrolling);
        isScrolling = setTimeout(() => {
            document.body.classList.remove('scrolling');
        }, 100);
    }, { passive: true });

    // Optimize images
    document.querySelectorAll('img[data-src]').forEach(img => {
        if ('loading' in HTMLImageElement.prototype) {
            img.src = img.dataset.src;
        } else {
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        observer.unobserve(img);
                    }
                });
            });
            observer.observe(img);
        }
    });
}

// Initialize performance optimizations
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPerformanceOptimizations);
} else {
    initPerformanceOptimizations();
}

// Clean up
window.addEventListener('beforeunload', () => {
    const slider = $('#js-main-slider').data('plugin_pogoSlider');
    if (slider) {
        slider.destroy();
    }
});