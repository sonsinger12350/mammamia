<?php

class Astra_Child_Custom_Widget_Footer_Menu extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			'astra_child_custom_widget_footer_menu',
			__('Footer Menu', 'astra-child'),
			array('description' => __('Footer Menu', 'astra-child'))
		);
	}

	public function form($instance) {}

	public function update($new_instance, $old_instance)
	{
		return [];
	}

	public function widget($args, $instance)
	{
		$locations = get_nav_menu_locations();
		if (empty($locations['footer_menu'])) return null;
		$menu = wp_get_nav_menu_items($locations['footer_menu']);

		echo $args['before_widget'];

		echo '<div class="footer-menu">';

		foreach ( $menu as $menu_item ) {
			echo '<a href="' . esc_url( $menu_item->url ) . '">' . esc_html( $menu_item->title ) . '</a>';
		}

		echo '</div>';
		echo $args['after_widget'];
	}
}
