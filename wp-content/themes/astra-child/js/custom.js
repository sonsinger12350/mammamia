jQuery(function($) {
	$(window).on('scroll', function () {
		if ($(this).scrollTop() > 100) {
			$('#masthead').addClass('active');
		}
		else {
			$('#masthead').removeClass('active');
		}
	});

	$('.projects-slide .list').owlCarousel({
		loop: true,
		margin: 10,
		nav: true,
		autoplay: false,
		autoplayTimeout: 5000,
		autoplayHoverPause: true,
		navText:[
			'<svg aria-hidden="true" class="e-font-icon-svg e-eicon-chevron-left" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M646 125C629 125 613 133 604 142L308 442C296 454 292 471 292 487 292 504 296 521 308 533L604 854C617 867 629 875 646 875 663 875 679 871 692 858 704 846 713 829 713 812 713 796 708 779 692 767L438 487 692 225C700 217 708 204 708 187 708 171 704 154 692 142 675 129 663 125 646 125Z"></path></svg>',
			'<svg aria-hidden="true" class="e-font-icon-svg e-eicon-chevron-right" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M696 533C708 521 713 504 713 487 713 471 708 454 696 446L400 146C388 133 375 125 354 125 338 125 325 129 313 142 300 154 292 171 292 187 292 204 296 221 308 233L563 492 304 771C292 783 288 800 288 817 288 833 296 850 308 863 321 871 338 875 354 875 371 875 388 867 400 854L696 533Z"></path></svg>'
		],
		responsive: {
			0: {
				items: 1
			}
		}
	});

	const { OverlayScrollbars, ClickScrollPlugin } = OverlayScrollbarsGlobal;
	OverlayScrollbars.plugin(ClickScrollPlugin);

	OverlayScrollbars(document.body, {
		scrollbars: {
			clickScroll: true,
			autoHide: 'scroll',
		},
	});
	
	$('.header-menu-desktop .search-form .search-icon').on('click', function() {
		$('.header-menu-desktop .search-form input').focus();
		$('.header-menu-desktop .search-form input').toggleClass('active');
	});

	$('body').on('click', '.header-menu-mobile .mobile-menu-toggle .toggle-icon', function() {
		let action = $(this).data('action');

		$('.header-menu-mobile .mobile-menu-toggle .toggle-icon').removeClass('active');

		if (action == 'open') {
			$('.header-menu-mobile .menu-list').addClass('active');
			$('.header-menu-mobile .mobile-menu-toggle .toggle-icon[data-action="close"]').addClass('active');
		}
		else {
			$('.header-menu-mobile .menu-list').removeClass('active');
			$('.header-menu-mobile .mobile-menu-toggle .toggle-icon[data-action="open"]').addClass('active');
		}
	});

	$('body').on('click', '.projects-list-widget .pagination .page-number, .projects-list-widget .pagination .next-page', function(e) {
		e.preventDefault();
	
		let page = $(this).data('page');
		let container = $('.projects-list-widget');
	
		if (!page) return;

		$.ajax({
			url: woocommerce_params.ajax_url,
			type: 'POST',
			data: {
				action: 'load_projects_page',
				page: page,
			},
			beforeSend: function() {
				container.find('.pagination').html('<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>');
			},
			success: function(response) {
				container.html(response);

				$('html, body').animate({
					scrollTop: container.offset().top - 200
				}, 500);
			}
		});
	});

	$('body').on('click', '.product-list-by-category .btn-collapse', function(e) {
		let btn = $(this);
		let item = btn.closest('.item-body').find('.list-product');

		btn.toggleClass('active');
		item.toggleClass('active');
	});
});