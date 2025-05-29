<?php
// Include các file widget
require_once get_stylesheet_directory() . '/widgets/footer-contact-info.php';
require_once get_stylesheet_directory() . '/widgets/footer-socials.php';
require_once get_stylesheet_directory() . '/widgets/footer-menu.php';

// Đăng ký các widget
function astra_child_register_custom_widgets() {
	register_widget('Astra_Child_Custom_Widget_Footer_Contact_Info');
	register_widget('Astra_Child_Custom_Widget_Footer_Socials');
	register_widget('Astra_Child_Custom_Widget_Footer_Menu');
}
add_action('widgets_init', 'astra_child_register_custom_widgets');

function astra_child_enqueue_custom_widget_styles() {
    wp_enqueue_style(
        'astra-child-custom-widget-css',
        get_stylesheet_directory_uri() . '/css/custom-widget.css',
        array(),
        '1.0',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_custom_widget_styles');

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
        filemtime( get_stylesheet_directory() . '/js/custom.js' )
    );

    wp_enqueue_style('font-awesome');

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
        '2.3.4'
    );
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_styles' );

// Đăng ký nhóm widget mới cho Elementor
add_action('elementor/elements/categories_registered', function($elements_manager) {
	$elements_manager->add_category(
		'widget-custom',
		[
			'title' => __('Custom', 'astra-child'),
			'icon' => 'fa fa-plug',
		]
	);
});

// Đăng ký custom Elementor widget
add_action('elementor/widgets/register', function($widgets_manager) {
    if (defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')) {
        require_once get_stylesheet_directory() . '/widgets/home/events.php';
        \Elementor\Plugin::instance()->widgets_manager->register( new \Custom_Elementor_Widget_Events() );
    }
});

?>
