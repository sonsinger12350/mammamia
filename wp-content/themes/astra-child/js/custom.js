function showNotification(message, type = 'success') {
	Toastify({
		text: message,
		offset: {
			x: 50,
			y: 10
		},
		className: `toast-${type}`,
	}).showToast();
}

jQuery(function($) {
	function scrollToElement(element, distance = 200) {
		$('html, body').animate({
			scrollTop: element.offset().top - distance
		}, 500);
	}

	let loadingIcon = '<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>';

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
				container.find('.pagination').html(loadingIcon);
			},
			success: function(response) {
				container.html(response);
				scrollToElement(container);
			}
		});
	});

	$('body').on('click', '.product-list-by-category .btn-collapse', function(e) {
		$(this).toggleClass('active');
		$(this).closest('.item-body').find('.list-product').toggleClass('active');
		$(this).closest('.item-body').find('.btn-view-all').toggleClass('active');
	});

	$('.custom-filter-product').on('submit', function(e) {
		let btn = $(this).find('button[type="submit"]');
		let btnHtml = btn.html();
		let form = $(this).serialize();

		$.ajax({
			url: woocommerce_params.ajax_url,
			type: 'POST',
			data: {
				action: 'filter_products',
				form: form,
			},
			beforeSend: function() {
				btn.html(loadingIcon);
				btn.prop('disabled', true);
			},
			success: function(response) {
				btn.html(btnHtml);
				btn.prop('disabled', false);
				$('.product-list-by-category').html(response);
				scrollToElement($('.product-list-by-category'));
			}
		});

		return false;
	});

	$('body').on('click', '#collapse-content', function() {
		let show = 'Xem thêm';
		let hide = 'Thu gọn';

		$('.content-collapse').toggleClass('active');

		if ($('.content-collapse').hasClass('active')) {
			$(this).html(hide);
		}
		else {
			$(this).html(show);
		}
	});

	$('body').on('click', '.custom-table-content .elementor-toc__top-level', function(e) {
		e.preventDefault();
		let target = $(this).attr('href');

		scrollToElement($(target), 150);

		return false;
	});

	let slideProductImage = $('.custom-product-content .product-slide-image .owl-carousel');
	slideProductImage.owlCarousel({
		margin: 10,
		nav: true,
		dots: false,
		onInitialized: updateCurrentCount,
		onChanged: updateCurrentCount,
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

	function updateCurrentCount(event) {
		let currentItem = (event.item.index) - event.relatedTarget._clones.length / 2;

		$(`.custom-product-content .product-slide-image .slide-dots .item[data-slide="${currentItem}"]`).click();
	}

	$('body').on('click', '.custom-product-content .product-slide-image .slide-dots .item', function() {
		let slideIndex = $(this).attr('data-slide');
		let color = $(this).attr('data-color');
		let itemColor = $(`.custom-product-content .product-content .list-color span[data-color="${color}"]`);

		$('.custom-product-content .product-slide-image .slide-dots .item').removeClass('active');
		$('.custom-product-content .product-content .list-color span').removeClass('active');

		$(this).addClass('active');
		itemColor.addClass('active');
		$('.attribute-color').html(itemColor.attr('data-name'));
		$('.product-sku').html(itemColor.attr('data-sku'));

		slideProductImage.trigger('to.owl.carousel', [slideIndex, 300]);
	});

	$('body').on('click', '.custom-product-content .product-content .list-color span', function() {
		let color = $(this).attr('data-color');

		$(`.custom-product-content .product-slide-image .slide-dots .item[data-color="${color}"]`).click();

		$('.attribute-color').html($(this).attr('data-name'));
	});

	$('body').on('click', '.custom-product-content-tab .tabs-title .tab-title', function() {
		let tab = $(this).attr('href');

		$('.custom-product-content-tab .tabs-title .tab-title').removeClass('active');
		$(this).addClass('active');

		$(`.custom-product-content-tab .tabs-content .tab-content`).removeClass('active');
		$(`.custom-product-content-tab .tabs-content .tab-content${tab}`).addClass('active');

		return false;
	});

	$('.custom-product-image-slide .list').owlCarousel({
		loop: true,
		margin: 10,
		dots: false,
		nav: true,
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
});