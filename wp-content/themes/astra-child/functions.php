<?php
require_once get_stylesheet_directory() . '/helpers.php';
require_once get_stylesheet_directory() . '/ajax.php';

// Widgets: [path => class]
$custom_widgets = [
	'/widgets/footer-contact-info.php' => 'Astra_Child_Custom_Widget_Footer_Contact_Info',
	'/widgets/footer-socials.php'      => 'Astra_Child_Custom_Widget_Footer_Socials',
	'/widgets/footer-menu.php'         => 'Astra_Child_Custom_Widget_Footer_Menu',
	'/widgets/header-menu.php'         => 'Astra_Child_Custom_Widget_Header_Menu',
];

// Require widget file
foreach ($custom_widgets as $path => $class) {
	require_once get_stylesheet_directory() . $path;
}

// Register widget
add_action('widgets_init', function() use ($custom_widgets) {
	foreach ($custom_widgets as $class) {
		if (class_exists($class)) {
			register_widget($class);
		}
	}
});

function custom_enqueue_editor_styles() {
	// Css for Elementor editor in admin
	wp_enqueue_style(
		'astra-child-custom-widget-css-editor',
		get_stylesheet_directory_uri() . '/css/custom-widget.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/custom-widget.css' )
	);
}
add_action( 'elementor/editor/after_enqueue_styles', 'custom_enqueue_editor_styles' );

function custom_enqueue_styles() {
	wp_enqueue_script('jquery');

	wp_enqueue_style(
		'custom-style',
		get_stylesheet_directory_uri() . '/css/custom.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/custom.css' )
	);

	wp_enqueue_script(
		'custom-script',
		get_stylesheet_directory_uri() . '/js/custom.js',
		array(),
		filemtime( get_stylesheet_directory() . '/js/custom.js' ),
		true
	);

	wp_enqueue_style(
		'astra-child-custom-widget-css',
		get_stylesheet_directory_uri() . '/css/custom-widget.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/custom-widget.css' )
	);

	wp_enqueue_style('font-awesome');

	// Owl Carousel
	wp_enqueue_style(
		'owl-carousel',
		'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css',
		array(),
		'2.3.4'
	);

	wp_enqueue_script(
		'owl-carousel',
		'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js',
		array(),
		'2.3.4',
		true
	);

	// Fancybox
	wp_enqueue_style(
		'fancybox',
		'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css',
		array(),
		'3.5.7'
	);

	wp_enqueue_script(
		'fancybox',
		'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js',
		array(),
		'3.5.7',
		true
	);

	// OverlayScrollbars
	wp_enqueue_style(
		'overlayscrollbars',
		'https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/2.11.0/styles/overlayscrollbars.min.css',
		array(),
		'2.11.0'
	);

	wp_enqueue_script(
		'overlayscrollbars',
		'https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/2.11.0/browser/overlayscrollbars.browser.es6.min.js',
		array('jquery'),
		'2.11.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_styles' );

// Register new widget group for Elementor
add_action('elementor/elements/categories_registered', function($elements_manager) {
	$elements_manager->add_category(
		'widget-custom',
		[
			'title' => __('Custom', 'astra-child'),
			'icon' => 'fa fa-plug',
		]
	);
});

// Register custom Elementor widget
add_action('elementor/widgets/register', function($widgets_manager) {
	if (defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')) {
		$widgets = [
			'/widgets/home/projects.php'        				=> 'Custom_Elementor_Widget_Projects',
			'/widgets/home/partners.php'        				=> 'Custom_Elementor_Widget_Partners',
			'/widgets/projects/slide.php'       				=> 'Custom_Elementor_Widget_Projects_Slide',
			'/widgets/projects/list.php'        				=> 'Custom_Elementor_Widget_Projects_List',
			'/widgets/products/list-product-category.php'       => 'Custom_Elementor_Widget_Product_List_By_Category',
			'/widgets/products/filter-product.php'       		=> 'Custom_Elementor_Widget_Filter_Product',
			'/widgets/products/product-content.php'       		=> 'Custom_Elementor_Widget_Product_Content',
			'/widgets/products/product-content-tab.php'       	=> 'Custom_Elementor_Widget_Product_Content_Tab',
			'/widgets/products/product-image-slide.php'       	=> 'Custom_Elementor_Widget_Product_Image_Slide',
		];

		foreach ($widgets as $path => $class) {
			require_once get_stylesheet_directory() . $path;
			if (class_exists($class)) {
				\Elementor\Plugin::instance()->widgets_manager->register( new $class );
			}
		}
	}
});

add_filter( 'wpseo_breadcrumb_separator', function($sep) {
	return '/';
} );

add_filter('wpseo_breadcrumb_links', function($links) {
    $site_name = get_bloginfo('name');

    if (isset($links[0]['text']) && $links[0]['text'] === 'Trang chủ') {
        $links[0]['text'] = $site_name;
    }

    return $links;
});

add_filter('wpseo_breadcrumb_links', 'custom_woocommerce_breadcrumb_for_product');

function custom_woocommerce_breadcrumb_for_product($links) {
    if (is_singular('product')) {
        global $post;

        // get primary category
        $term = null;

        if (class_exists('WPSEO_Primary_Term')) {
            $primary_term = new WPSEO_Primary_Term('product_cat', $post->ID);
            $term_id = $primary_term->get_primary_term();
            $term = get_term($term_id, 'product_cat');
        }

        // get first term if primary term is not set
        if (!$term || is_wp_error($term)) {
            $terms = get_the_terms($post->ID, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                $term = reset($terms);
            }
        }

        if ($term) {
            $term_ancestors = get_ancestors($term->term_id, 'product_cat');

            $breadcrumbs = [];
            foreach (array_reverse($term_ancestors) as $ancestor_id) {
                $ancestor = get_term($ancestor_id, 'product_cat');
                $breadcrumbs[] = [
                    'text' => $ancestor->name,
                    'url' => get_term_link($ancestor),
                ];
            }

            // Add current term to breadcrumbs
            $breadcrumbs[] = [
                'text' => $term->name,
                'url' => get_term_link($term),
            ];

            // Remove "Cửa hàng" or "Shop" link
            foreach ($links as $i => $link) {
                if ($link['text'] === 'Cửa hàng' || $link['text'] === 'Shop') {
                    unset($links[$i]);
                }
            }

            // Insert breadcrumbs after the first link
            array_splice($links, 1, 0, $breadcrumbs);
        }
    }

    return $links;
}


?>
