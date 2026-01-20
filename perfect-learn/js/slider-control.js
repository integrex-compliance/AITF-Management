function initSliderControl() {
    debug('Initializing slider control...');
    
    // Wait for Pogo Slider to be initialized
    var checkSlider = setInterval(function() {
        var slider = $('#js-main-slider').data('plugin_pogoSlider');
        
        if (slider) {
            clearInterval(checkSlider);
            debug('Pogo Slider found, initializing controls...');
            
            // Get all slider control cards
            const sliderCards = document.querySelectorAll('.slider-control-card');
            debug('Found ' + sliderCards.length + ' control cards');
            
            if (sliderCards.length === 0) {
                debug('No slider control cards found!');
                return;
            }
            
            // Make sure the slider control section is visible
            const controlSection = document.querySelector('.slider-control-section');
            if (controlSection) {
                controlSection.style.display = 'block';
                debug('Slider control section is now visible');
            }
            
            // Add click event to each card
            sliderCards.forEach((card, index) => {
                card.addEventListener('click', function() {
                    debug('Clicked card ' + index);
                    // Remove active class from all cards
                    sliderCards.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked card
                    this.classList.add('active');
                    
                    // Navigate to the corresponding slide
                    slider.goToSlide(index);
                });
            });

            // Update active card when slide changes
            $(document).on('pogo:slideChange', function(event) {
                const currentSlide = event.currentSlide;
                debug('Slide changed to: ' + currentSlide);
                
                // Remove active class from all cards
                sliderCards.forEach(card => card.classList.remove('active'));
                
                // Add active class to corresponding card
                if (sliderCards[currentSlide]) {
                    sliderCards[currentSlide].classList.add('active');
                }
            });

            // Make first card active by default
            if (sliderCards[0]) {
                sliderCards[0].classList.add('active');
                debug('Set first card as active');
            }
        } else {
            debug('Waiting for Pogo Slider to initialize...');
        }
    }, 500);
}

// Start initialization when document is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSliderControl);
} else {
    initSliderControl();
}
