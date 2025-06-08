<?php

class Astra_Child_Custom_Widget_Header_Menu extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			'astra_child_custom_widget_header_menu',
			__('Header Menu', 'astra-child'),
			array('description' => __('Header Menu', 'astra-child'))
		);
	}

	public function form($instance) {}

	public function update($new_instance, $old_instance)
	{
		return [];
	}

	public function widget($args, $instance)
	{
		global $wp;

		$queried_object_id = get_queried_object_id();
		$current_url = home_url(add_query_arg([], $wp->request));
		$locations = get_nav_menu_locations();
		if (empty($locations['primary'])) return null;

		$menu = wp_get_nav_menu_items($locations['primary']);
		$count_wishlist = '';
		// $count_wishlist = '<span class="wishlist-count">1</span>';

		echo $args['before_widget'];

		echo '<div class="header-menu-desktop">';
			echo '<form class="search-form" action="/" method="get">';
				echo '<input type="text" name="s" placeholder="' . esc_attr__( 'Tìm kiếm...', 'astra-child' ) . '" required
						oninvalid="this.setCustomValidity(\'Vui lòng nội dung tìm kiếm\')"
						oninput="this.setCustomValidity(\'\')"
				>';
				echo '<img class="search-icon" src="' . get_stylesheet_directory_uri() . '/assets/icon/search.svg" alt="zoom">';
				echo '<a href="/wishlist" class="wishlist-icon"><img src="' . get_stylesheet_directory_uri() . '/assets/icon/heart.svg" alt="heart">' . $count_wishlist . '</a>';
			echo '</form>';
			echo '<div class="menu-list">';

			foreach ( $menu as $menu_item ) {
				if (isset($menu_item->object_id) && intval($menu_item->object_id) === intval($queried_object_id)) {
					$is_active = 'active';
				} else {
					$is_active = (trailingslashit($menu_item->url) === trailingslashit($current_url)) ? 'active' : '';
				}
				echo '<a href="' . esc_url( $menu_item->url ) . '" text="'.$menu_item->object_id.'"  id="'.$queried_object_id.'" class="' . $is_active . '">' . esc_html( $menu_item->title ) . '</a>';
			}

			echo '</div>';
		echo '</div>';
		echo '
			<div class="header-menu-mobile">
				<div class="mobile-menu-toggle" aria-expanded="false" data-index="0">
					<span class="screen-reader-text">Main Menu</span>
					<span class="mobile-menu-toggle-icon">
						<span aria-hidden="true" class="toggle-icon active" data-action="open">
							<img class="search-icon" src="' . get_stylesheet_directory_uri() . '/assets/icon/menu-bar.svg" alt="zoom">
						</span>
						<span aria-hidden="true" class="toggle-icon" data-action="close">
							<img class="search-icon" src="' . get_stylesheet_directory_uri() . '/assets/icon/menu-bar-close.svg" alt="zoom">
						</span>
					</span>
				</div>
				<div class="menu-list">
			';

			foreach ( $menu as $menu_item ) {
				$is_active = (trailingslashit($menu_item->url) === trailingslashit($current_url)) ? 'active' : '';
				echo '<a href="' . esc_url( $menu_item->url ) . '" class="' . $is_active . '">' . esc_html( $menu_item->title ) . '</a>';
			}
			echo '<a href="/wishlist" class="wishlist-link">' . esc_attr__( 'Sản phẩm yêu thích', 'astra-child' ) . '</a>';
			echo '<form class="search-form" action="/" method="get">';
					echo '<input type="text" name="s" placeholder="' . esc_attr__( 'Tìm kiếm...', 'astra-child' ) . '" required
							oninvalid="this.setCustomValidity(\'Vui lòng nội dung tìm kiếm\')"
							oninput="this.setCustomValidity(\'\')"
					>';
				echo '<button type="submit" class="btn-search"><img class="search-icon" src="' . get_stylesheet_directory_uri() . '/assets/icon/search.svg" alt="zoom"></button>';
			echo '</form>';
			echo '</div>';
		echo '</div>';
		echo $args['after_widget'];
	}
}
