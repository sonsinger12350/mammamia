<?php
class Custom_Elementor_Widget_Projects extends \Elementor\Widget_Base {

    public function get_name() {
        return 'custom_widget_projects';
    }

    public function get_title() {
        return __('Custom Widget Projects', 'astra-child');
    }

    public function get_icon() {
        return 'eicon-code'; // Icon cho widget
    }

    public function get_categories() {
        return ['widget-custom']; // Danh mục widget
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'astra-child'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
		$args = array(
			'post_type' => 'du-an',
			'posts_per_page' => 6,
            'post_status' => 'publish',
		);

		$query = new WP_Query($args);
        
		if (empty($query->posts)) return null;

		$output = '<div class="projects-widget">';
		$output .= '<div class="projects-list owl-carousel owl-theme">';

		foreach ($query->posts as $post) {
			$output .= '<div class="item">'
                        . '<div class="item-body">'
                            . '<span class="item-date">' . get_the_date('d M Y', $post) . '</span>'
                            . '<h3 class="item-title">' . get_the_title($post) . '</h3>'
                            . '<div class="item-content">' . get_the_excerpt($post) . '</div>'
                            . '<a href="' . get_the_permalink($post) . '" class="button-primary">Xem thêm</a>'
                        . '</div>'
                        . '<div class="item-image">' . get_the_post_thumbnail($post, 'full')
                        .     '<a href="' . get_the_post_thumbnail_url($post, 'full') . '" class="zoom-image" data-fancybox>'
                        .         '<img src="' . get_stylesheet_directory_uri() . '/assets/icon/zoom.svg" alt="zoom">'
                        .     '</a>'
                        . '</div>'
			        . '</div>';
		}

		$output .= '</div></div>';

		echo $output;
    }
}
