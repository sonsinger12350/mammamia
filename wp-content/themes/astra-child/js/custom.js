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

function showCustomModal(element) {
	jQuery(element).modal('show');
}

function scrollToElement(element, distance = 200) {
	if (element.length <= 0) return;

	jQuery('html, body').animate({
		scrollTop: element.offset().top - distance
	}, 500);
}

function validateEmail(email) {
	if (!/^[\w-\.]+@([\w-]+\.)+[\w-]{1,15}$/g.test(email)) return 0;
	if (email.trim() === "") return 0;
	return 1;
}

jQuery(function($) {
	function updateCurrentCount(event) {
		let currentItem = (event.item.index) - event.relatedTarget._clones.length / 2;

		jQuery(`.custom-product-content .product-slide-image .slide-dots .item[data-slide="${currentItem}"]`).click();
	}

	function initProductGallery() {
		let slideProductImage = jQuery('.custom-product-content .product-slide-image .owl-carousel');

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

		jQuery('body').on('click', '.custom-product-content .product-slide-image .slide-dots .item', function() {
			let slideIndex = jQuery(this).attr('data-slide');

			jQuery('.custom-product-content .product-slide-image .slide-dots .item').removeClass('active');
			jQuery(this).addClass('active');

			slideProductImage.trigger('to.owl.carousel', [slideIndex, 300]);
		});
	}

	$(document).ready(function() {
		initProductGallery();
	});

	let dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
	let dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
		return new bootstrap.Dropdown(dropdownToggleEl)
	});

	let loadingIcon = '<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>';

	jQuery(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope, $) {
			var $carousel = $scope.find('.projects-slide .list');
	
			if ($carousel.length && !$carousel.hasClass('owl-loaded')) {
				$carousel.owlCarousel({
					loop: true,
					margin: 1,
					nav: true,
					dots: false,
					autoplay: false,
					autoplayTimeout: 5000,
					autoplayHoverPause: true,
					navText:[
						'<span aria-label="Previous slide" aria-controls="projects-slide-list"><svg aria-hidden="true" class="e-font-icon-svg e-eicon-chevron-left" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M646 125C629 125 613 133 604 142L308 442C296 454 292 471 292 487 292 504 296 521 308 533L604 854C617 867 629 875 646 875 663 875 679 871 692 858 704 846 713 829 713 812 713 796 708 779 692 767L438 487 692 225C700 217 708 204 708 187 708 171 704 154 692 142 675 129 663 125 646 125Z"></path></svg></span>',
						'<span aria-label="Next slide" aria-controls="projects-slide-list"><svg aria-hidden="true" class="e-font-icon-svg e-eicon-chevron-right" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M696 533C708 521 713 504 713 487 713 471 708 454 696 446L400 146C388 133 375 125 354 125 338 125 325 129 313 142 300 154 292 171 292 187 292 204 296 221 308 233L563 492 304 771C292 783 288 800 288 817 288 833 296 850 308 863 321 871 338 875 354 875 371 875 388 867 400 854L696 533Z"></path></svg></span>'
					],
					responsive: {
						0: {
							items: 1
						}
					}
				});
			}
		});
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

	$('body').on('click', '.custom-table-content .elementor-toc__list-item-text', function(e) {
		e.preventDefault();
		let target = $(this).attr('href');
		let text = $(this).text();
		let matched = $(target).find(`*:contains('${text}')`).filter(function() {
			// lấy text trực tiếp của node (không tính text trong con)
			let directText = $(this).contents().filter(function() {
				return this.nodeType === 3; // node text
			}).text().trim();

			return directText.includes(text);
		});

		scrollToElement(matched, 150);

		return false;
	});

	$('body').on('click', '.custom-product-content .product-content .list-color span', function () {
		let color = $(this).attr('data-color');

		$('.custom-product-content .product-content .list-color span').removeClass('active');
		$(this).addClass('active');

		let gallery = [];

		if (Object.keys(product_attributes).length > 0 && product_attributes[color]) {
			let list_size = product_attributes[color]['size'];

			// Nếu có size
			if (list_size && Object.keys(list_size).length > 0) {
				let list_size_html = '';

				Object.keys(list_size).forEach(function (key) {
					list_size_html += `
						<div class="item">
							<input type="radio" name="size" id="size-${key}" value="${key}">
							<label for="size-${key}">${list_size[key]['name']}</label>
						</div>
					`;
				});

				$('.custom-product-content .product-content .list-size').html(list_size_html).show();

				// Click size đầu tiên và cập nhật SKU trong sự kiện 'change'
				$('.custom-product-content .product-content .list-size .item input[type="radio"]').first().trigger('click');
			}
			else {
				// Không có size → lấy ảnh & sku từ color
				gallery = product_attributes[color]['images'] ?? [];
				let sku = product_attributes[color]['sku'] ?? '';

				$('.custom-product-content .product-content .list-size').hide();
				renderGallery(gallery);
				$('.product-sku span').text(sku);
			}
		}

		$('.attribute-color').html($(this).attr('data-name'));
	});

	$('body').on('change', '.custom-product-content .product-content .list-size .item input[type="radio"]', function () {
		let size = $('input[name="size"]:checked').val();
		let color = $('.custom-product-content .product-content .list-color span.active').attr('data-color');

		if (!color || !size) {
			console.error("Missing color or size");
			return false;
		}

		if (Object.keys(product_attributes).length > 0) {
			let gallery = product_attributes[color]['size'][size]['images'] ?? [];
			let sku = product_attributes[color]['size'][size]['sku'] ?? '';

			renderGallery(gallery);
			$('.product-sku span').text(sku);
		}
	});

	// Hàm hiển thị ảnh gallery
	function renderGallery(gallery) {
		let gallery_html = '<div class="owl-carousel owl-theme">';
		let slide_dots_html = '<div class="slide-dots"><div class="list">';
	
		if (gallery.length > 0) {
			gallery.forEach(function (item, index) {
				gallery_html += `
					<div class="item">
						<img src="${item}" alt="gallery-image-${index}">
					</div>
				`;
				slide_dots_html += `
					<div class="item" data-slide="${index}">
						<img src="${item}" alt="gallery-thumb-${index}">
					</div>
				`;
			});
			gallery_html += '</div>';
			slide_dots_html += '</div></div>';
	
			$('.custom-product-content .product-slide-image').html(gallery_html + slide_dots_html);
			initProductGallery();
		}
	}

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

	// Mobile submenu toggle functionality
	$('.mobile-menu-item.has-submenu > a').on('click', function(e) {
		e.preventDefault();

		let $menuItem = $(this).parent();
		let $submenu = $menuItem.find('.mobile-submenu').first();

		// Close other open submenus at the same level
		$menuItem.siblings('.mobile-menu-item.has-submenu.active').removeClass('active');

		// Toggle current submenu
		$menuItem.toggleClass('active');

		// Close all submenus in deeper levels
		$submenu.find('.mobile-menu-item.has-submenu').removeClass('active');
	});

	// Desktop submenu hover functionality (optional enhancement)
	$('.header-menu-desktop .menu-item.has-submenu').hover(
		function() {
			// Mouse enter
			$(this).addClass('hover');
		},
		function() {
			// Mouse leave
			$(this).removeClass('hover');
		}
	);
	
	// Close desktop submenus when clicking outside
	$(document).on('click', function(e) {
		if (!$(e.target).closest('.header-menu-desktop .menu-item').length) {
			$('.header-menu-desktop .menu-item').removeClass('hover');
		}
	});
	
	// Handle keyboard navigation for accessibility
	$('.menu-item a, .mobile-menu-item a').on('keydown', function(e) {
		let $menuItem = $(this).parent();
		let $submenu = $menuItem.find('.submenu, .mobile-submenu').first();

		if (e.key === 'Enter' || e.key === ' ') {
			if ($menuItem.hasClass('has-submenu')) {
				e.preventDefault();
				$menuItem.toggleClass('active hover');
			}
		}

		if (e.key === 'Escape') {
			$menuItem.removeClass('active hover');
		}
	});

	$('body').on('submit', '.form-login-register form', function(e) {
		let form = $(this);
		let btn = form.find('button[type="submit"]');
		let btnHtml = btn.html();
		let formData = form.serialize();
		let error = false;
		let action = form.attr('name');
		let nonce = form.attr('data-nonce');
		let email = form.find('input[name="email"]').val();

		e.preventDefault();
		
		form.find('input[required]').each(function() {
			if ($(this).val() == '') {
				$(this).addClass('is-invalid');
				error = true;
			}
		});

		if (error) {
			showNotification('Vui lòng điền đầy đủ thông tin', 'error');
			return false;
		}

		if (validateEmail(email) == 0) {
			form.find('input[name="email"]').addClass('is-invalid');
			showNotification('Email không hợp lệ', 'error');
			return false;
		}

		if (action == 'register_user') {
			let password = form.find('input[name="password"]').val();
			let confirmPassword = form.find('input[name="confirm_password"]').val();

			if (password != confirmPassword) {
				form.find('input[name="confirm_password"]').addClass('is-invalid');
				showNotification('Mật khẩu không khớp', 'error');
				return false;
			}
		}

		$.ajax({
			url: woocommerce_params.ajax_url,
			type: 'POST',
			data: {
				action,
				nonce: nonce,
				form: formData,
			},
			beforeSend: function() {
				btn.html(loadingIcon);
				btn.prop('disabled', true);
			},
			success: function(response) {
				btn.html(btnHtml);
				btn.prop('disabled', false);

				if (response.success) {
					showNotification(response.data.message, 'success');

					setTimeout(function() {
						location.reload();
					}, 1000);
				}
				else {
					showNotification(response.data.message, 'error');
				}
			}
		});

		return false;
	});

	$('body').on('input', '.form-login-register input', function() {
		if ($(this).val() != '') $(this).removeClass('is-invalid');
		else $(this).addClass('is-invalid');
	});

	$('body').on('click', '.footer-button .btn-scroll-top', function() {
		scrollToElement($('html, body'));
	});

	// Show button scroll top when scroll down 30% height of page
	$(document).scroll(function() {
		if ($(document).scrollTop() > $(document).height() * 0.3) {
			$('.footer-button .btn-scroll-top').show();
		}
		else {
			$('.footer-button .btn-scroll-top').hide();
		}
	});

	$('body').on('click', '.footer-contact-form .show-footer-contact-form', function() {
		let element = $('.footer-contact-form');
		element.toggleClass('active');

		if (element.hasClass('active')) {
			element.css('max-width', '100%');

			setTimeout(function() {
				element.css('max-height', '100%');
			}, 400);
		}
		else {
			element.css('max-height', window.screen.width <= 1199 ? '32px' : '38px');

			setTimeout(function() {
				element.css('max-width', window.screen.width <= 1199 ? '120px' : '10%');
			}, 400);
		}
	});

	$('.product [data-product-id]').off('click').on('click', function() {
		updateWishlistCount();
	});

	$('.add-to-wishlist .yith-add-to-wishlist-button-block').off('click').on('click', function() {
		updateWishlistCount();
	});

	function updateWishlistCount() {
		setTimeout(function () {
			$.ajax({
				url: yith_wcwl_l10n.ajax_url,
				type: 'POST',
				data: {
					action: 'yith_wcwl_update_wishlist_count'
				},
				success: function(response) {
					if ($('.wishlist-icon .wishlist-count').length > 0) {
						$('.wishlist-icon .wishlist-count').text(response.count);
					}
					else {
						$('.wishlist-icon').append(`
							<span class="wishlist-count">${response.count}</span>
						`);
					}
				}
			});
		}, 1000);
	}

	// Table of Contents functionality
	$('body').on('click', '.custom-product-content-tab .btn-table-content', function(e) {
		e.preventDefault();
		let $button = $(this);
		let $tableContent = $button.siblings('.table-content-list');
		let $icon = $button.find('img');
		
		// Toggle table of contents
		$tableContent.toggleClass('active');
		
		// Rotate icon
		if ($tableContent.hasClass('active')) {
			$icon.css('transform', 'rotate(180deg)');
		} else {
			$icon.css('transform', 'rotate(0deg)');
		}
	});

	// Smooth scrolling for table of contents links
	$('body').on('click', '.custom-product-content-tab .toc-link', function(e) {
		e.preventDefault();
		let targetId = $(this).attr('href');
		let $target = $(targetId);
		
		if ($target.length) {
			// Close table of contents on mobile
			if (window.innerWidth <= 767) {
				$('.custom-product-content-tab .table-content-list').removeClass('active');
				$('.custom-product-content-tab .btn-table-content img').css('transform', 'rotate(0deg)');
			}
			
			// Smooth scroll to target
			$('html, body').animate({
				scrollTop: $target.offset().top - 200
			}, 800);
			
			// Add highlight effect to target heading
			$target.addClass('toc-highlight');
			setTimeout(function() {
				$target.removeClass('toc-highlight');
			}, 2000);
		}
	});

	// Filter Product Widget AJAX functionality
	$('#brand').on('change', function() {
		var brandId = $(this).val();
		var collectionSelect = $('#bo-suu-tap');
		var defaultCollectionOptions = collectionSelect.data('default-options');

		if (!defaultCollectionOptions) {
			defaultCollectionOptions = collectionSelect.html();
			collectionSelect.data('default-options', defaultCollectionOptions);
		}

		// Reset collection field
		collectionSelect.html('<option value="">Tất cả</option>');

		if (brandId) {
			// Show loading state for collection field
			collectionSelect.prop('disabled', true);
			collectionSelect.html('<option value="" selected>Đang tải...</option>');

			// Add loading spinner
			collectionSelect.addClass('loading');

			// Make AJAX request for collection options
			$.ajax({
				url: filter_product_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'get_collection_options',
					brand_id: brandId,
					nonce: filter_product_ajax.nonce
				},
				success: function(response) {
					if (response.success) {
						collectionSelect.html(response.data.html);
					} else {
						console.error('Error:', response.data);
						collectionSelect.html('<option value="">Tất cả</option>');
					}
				},
				complete: function() {
					collectionSelect.prop('disabled', false);
					collectionSelect.removeClass('loading');
				}
			});
		} else {
			// Restore default collection list when no brand is selected
			collectionSelect.html(defaultCollectionOptions);
			collectionSelect.prop('disabled', false);
			collectionSelect.removeClass('loading');
		}
	});
});