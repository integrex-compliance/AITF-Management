(function($){"use strict";var spinner=function(){setTimeout(function(){if($('#spinner').length>0){$('#spinner').removeClass('show');}},1);};spinner();new WOW().init();$(window).scroll(function(){if($(this).scrollTop()>45){$('.navbar').addClass('sticky-top shadow-sm');}else{$('.navbar').removeClass('sticky-top shadow-sm');}});const $dropdown=$(".dropdown");const $dropdownToggle=$(".dropdown-toggle");const $dropdownMenu=$(".dropdown-menu");const showClass="show";$(window).on("load resize",function(){if(this.matchMedia("(min-width: 992px)").matches){$dropdown.hover(function(){const $this=$(this);$this.addClass(showClass);$this.find($dropdownToggle).attr("aria-expanded","true");$this.find($dropdownMenu).addClass(showClass);},function(){const $this=$(this);$this.removeClass(showClass);$this.find($dropdownToggle).attr("aria-expanded","false");$this.find($dropdownMenu).removeClass(showClass);});}else{$dropdown.off("mouseenter mouseleave");}});$('[data-toggle="counter-up"]').counterUp({delay:10,time:2000});$(window).scroll(function(){if($(this).scrollTop()>100){$('.back-to-top').fadeIn('slow');}else{$('.back-to-top').fadeOut('slow');}});$('.back-to-top').click(function(){$('html, body').animate({scrollTop:0},1500,'easeInOutExpo');return false;});// Optimized testimonial carousel
$(".testimonial-carousel").owlCarousel({
    items: 3,
    loop: true,
    center: true,
    autoplay: true,
    autoplayTimeout: 4000, // Slightly faster rotation
    autoplayHoverPause: false, // Don't pause on hover for better performance
    smartSpeed: 800, // Slightly faster animation
    dots: true,
    lazyLoad: true, // Enable lazy loading
    responsive: {
        0: { items: 1 },
        576: { items: 1 },
        768: { items: 2 },
        992: { items: 3 }
    }
});

// Optimized vendor carousel
$('.vendor-carousel').owlCarousel({
    loop: true,
    margin: 30, // Reduced margin for better performance
    dots: false,
    autoplay: true,
    autoplayTimeout: 3000, // Faster rotation
    autoplayHoverPause: false, // Don't pause on hover
    smartSpeed: 600, // Faster animation
    lazyLoad: true, // Enable lazy loading
    responsive: {
        0: { items: 2 },
        576: { items: 4 },
        768: { items: 5 }, // Reduced items for better performance
        992: { items: 6 }  // Reduced items for better performance
    }
});})(jQuery);