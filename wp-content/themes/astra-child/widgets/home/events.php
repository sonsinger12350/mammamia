<?php
class Custom_Elementor_Widget_Events extends \Elementor\Widget_Base {

    public function get_name() {
        return 'custom_widget_events';
    }

    public function get_title() {
        return __('Custom Widget Events', 'astra-child');
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
        $settings = $this->get_settings_for_display();

		$args = array(
			'category_name' => 'su-kien',
			'posts_per_page' => 6,
		);

		$query = new WP_Query($args);
		
		if (empty($query->posts)) return null;

		$output = '<div class="events-widget">';
		$output .= '<div class="events-list owl-carousel owl-theme">';

		foreach ($query->posts as $post) {
            $categories = get_the_category($post->ID);
            $category = '';
 
            if (!empty($categories)) {
                $category = $categories[0];
                $category = '<a href="' . get_category_link($category->term_id) . '"><i class="fa fa-tags" aria-hidden="true"></i> ' . $category->name . '</a>';
            }

			$output .= '<div class="event-item">';
			$output .= '<div class="item-image">';
			$output .= '<a href="' . get_the_permalink($post) . '">';
			$output .= get_the_post_thumbnail($post, 'full');
			$output .= '</a>';
			$output .= '</div>';
			$output .= '<div class="item-content">';
			$output .= $category;
			$output .= '<h3 class="event-title">' . get_the_title($post) . '</h3>';
			$output .= '<div class="event-content">' . get_the_excerpt($post) . '</div>';
			$output .= '</div>';
			$output .= '<hr>';
			$output .= '<span class="event-date">' . get_the_date('d/m/Y', $post) . '</span>';
			$output .= '</div>';
		}

		$output .= '</div>';
		$output .= '</div>';

		echo $output;
    }
}
