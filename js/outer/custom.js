(function($) {
	"use strict";

	window.closeMobileMenu = window.closeMobileMenu || function() {};
	
	/* ..............................................
	Loader 
    ................................................. */
	
	$(window).on('load', function() { 
		$('.preloader').fadeOut(); 
		$('#preloader').delay(550).fadeOut('slow'); 
		$('body').delay(450).css({'overflow':'visible'});
	});
    	
	/* ..............................................
    Navbar Bar
    ................................................. */
	
	$('.navbar-nav .nav-link').on('click', function() {
		var toggle = $('.navbar-toggler').is(':visible');
		if (toggle && typeof window.closeMobileMenu === 'function') {
			window.closeMobileMenu();
		}
	});
	
	/* Mobile Menu Functionality */
	$(document).ready(function() {
		const hasModernMobileNav = $('#mobile-nav-overlay').length > 0 && $('[data-mobile-nav-toggle]').length > 0;
		if (hasModernMobileNav) {
			$('.mobile-menu-overlay').remove();
			window.closeMobileMenu = function() {
				var $overlay = $('#mobile-nav-overlay');
				if ($overlay.length) {
					$overlay.removeClass('is-open');
					$('body').removeClass('mobile-nav-open').css('overflow', '');
				}
			};
			return;
		}

		// Check if mobile device
		function isMobile() {
			return $(window).width() <= 991;
		}
		
		// Create mobile menu overlay
		if (!$('.mobile-menu-overlay').length) {
			$('body').append('<div class="mobile-menu-overlay"></div>');
		}
		
		// Create mobile menu close button
		if (!$('.mobile-menu-close').length) {
			$('#navbar-wd').append('<div class="mobile-menu-close"></div>');
		}
		
		// Mobile menu toggle
		$('.navbar-toggler').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			if (isMobile()) {
				var $navbar = $('#navbar-wd');
				var $overlay = $('.mobile-menu-overlay');
				
				if ($navbar.hasClass('show')) {
					closeMobileMenu();
				} else {
					openMobileMenu();
				}
			}
		});
		
		// Open mobile menu
		function openMobileMenu() {
			var $navbar = $('#navbar-wd');
			var $overlay = $('.mobile-menu-overlay');
			
			$navbar.addClass('show');
			$overlay.addClass('active');
			$('body').css('overflow', 'hidden');
			
			// Animate menu items
			$navbar.find('.nav-link').each(function(index) {
				$(this).css({
					'opacity': '0',
					'transform': 'translateX(50px)'
				});
				
				setTimeout(() => {
					$(this).css({
						'opacity': '1',
						'transform': 'translateX(0)',
						'transition': 'all 0.3s ease'
					});
				}, index * 50);
			});
		}
		
		// Close mobile menu
		window.closeMobileMenu = function() {
			var $navbar = $('#navbar-wd');
			var $overlay = $('.mobile-menu-overlay');
			
			$navbar.removeClass('show');
			$overlay.removeClass('active');
			$('body').css('overflow', '');
			
			// Close all dropdowns
			$('.navbar .dropdown').removeClass('active');
		}
		
		// Close menu on overlay click
		$('.mobile-menu-overlay').on('click', function() {
			closeMobileMenu();
		});
		
		// Close menu on close button click
		$(document).on('click', '.mobile-menu-close', function() {
			closeMobileMenu();
		});
		
		// Mobile dropdown toggle functionality
		if (isMobile()) {
			$('.navbar .dropdown > .nav-link').on('click', function(e) {
				// Don't prevent default for mailto links and external links
				var href = $(this).attr('href');
				if (href && (href.startsWith('mailto:') || href.startsWith('http') || href.startsWith('tel:'))) {
					return true; // Allow normal behavior for mailto, http, and tel links
				}
				
				e.preventDefault();
				e.stopPropagation();
				
				var $dropdown = $(this).parent('.dropdown');
				var $dropdownContent = $dropdown.find('.dropdown-content');
				
				// Close other dropdowns
				$('.navbar .dropdown').not($dropdown).removeClass('active');
				
				// Toggle current dropdown
				$dropdown.toggleClass('active');
				
				// Animate dropdown content
				if ($dropdown.hasClass('active')) {
					$dropdownContent.find('a').each(function(index) {
						$(this).css({
							'opacity': '0',
							'transform': 'translateY(20px)'
						});
						
						setTimeout(() => {
							$(this).css({
								'opacity': '1',
								'transform': 'translateY(0)',
								'transition': 'all 0.3s ease'
							});
						}, index * 30);
					});
				}
			});
			
			// Close dropdown when clicking on dropdown items
			$('.dropdown-section a').on('click', function() {
				setTimeout(() => {
					closeMobileMenu();
				}, 300);
			});
		}
		
		// Handle window resize
		$(window).on('resize', function() {
			if (!isMobile()) {
				closeMobileMenu();
			}
		});
		
		// Handle escape key
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' && isMobile()) {
				closeMobileMenu();
			}
		});
	});
	
	/* ..............................................
    Fixed Menu
    ................................................. */
    
	$(window).on('scroll', function () {
		if ($(window).scrollTop() > 50) {
			$('.top-header').addClass('fixed-menu');
		} else {
			$('.top-header').removeClass('fixed-menu');
		}
	});

	/* ..............................................
    Properties Filter
    ................................................. */
	var Container = $('.container');
	Container.imagesLoaded(function () {
		var portfolio = $('.properties-menu');
		portfolio.on('click', 'button', function () {
			$(this).addClass('active').siblings().removeClass('active');
			var filterValue = $(this).attr('data-filter');
			$grid.isotope({
				filter: filterValue
			});
		});
		var $grid = $('.properties-list').isotope({
			itemSelector: '.properties-grid'
		});

	});

	/* ..............................................
    Gallery
    ................................................. */
	
	$(document).ready(function() {
		$('.popup-gallery').magnificPopup({
			delegate: 'a',
			type: 'image',
			tLoading: 'Loading image #%curr%...',
			mainClass: 'mfp-img-mobile',
			gallery: {
				enabled: true,
				navigateByImgClick: true,
				preload: [0,1] // Will preload 0 - before current, and 1 after the current image
			},
			image: {
				tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
				titleSrc: function(item) {
					return item.el.attr('title') + '<small>by Marsel Van Oosten</small>';
				}
			}
		});
	});
	
	/* ..............................................
    Scroll To Top
    ................................................. */
	
	$(document).ready(function () {

		$(window).scroll(function () {
			if ($(this).scrollTop() > 100) {
				$('#scroll-to-top').fadeIn();
			} else {
				$('#scroll-to-top').fadeOut();
			}
		});

		$('#scroll-to-top').click(function () {
			$("html, body").animate({
				scrollTop: 0
			}, 600);
			return false;
		});

	});
	
	

	
}(jQuery));

