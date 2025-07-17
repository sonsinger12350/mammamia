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

	/**
	 * Build hierarchical menu structure
	 */
	private function build_menu_tree($menu_items, $parent_id = 0) {
		$tree = array();
		
		foreach ($menu_items as $item) {
			if ($item->menu_item_parent == $parent_id) {
				$item->children = $this->build_menu_tree($menu_items, $item->ID);
				$tree[] = $item;
			}
		}
		
		return $tree;
	}

	/**
	 * Render menu item with submenu support
	 */
	private function render_menu_item($menu_item, $current_url, $queried_object_id, $level = 0) {
		// Check if current item is active
		if (isset($menu_item->object_id) && intval($menu_item->object_id) === intval($queried_object_id)) {
			$is_active = 'active';
		} else {
			$is_active = (trailingslashit($menu_item->url) === trailingslashit($current_url)) ? 'active' : '';
		}

		// Check if any child is active
		$has_active_child = false;
		if (!empty($menu_item->children)) {
			foreach ($menu_item->children as $child) {
				if (isset($child->object_id) && intval($child->object_id) === intval($queried_object_id)) {
					$has_active_child = true;
					break;
				}
				if (trailingslashit($child->url) === trailingslashit($current_url)) {
					$has_active_child = true;
					break;
				}
			}
		}

		$menu_class = 'menu-item';
		if ($is_active || $has_active_child) {
			$menu_class .= ' active';
		}
		if (!empty($menu_item->children)) {
			$menu_class .= ' has-submenu';
		}
		if ($level > 0) {
			$menu_class .= ' submenu-item';
		}

		$output = '<div class="' . $menu_class . '">';
		$output .= '<a href="' . esc_url($menu_item->url) . '" class="' . $is_active . '">';
		$output .= esc_html($menu_item->title);
		if (!empty($menu_item->children)) {
			$output .= '<span class="submenu-toggle"><i class="fa fa-chevron-down"></i></span>';
		}
		$output .= '</a>';

		// Render submenu if exists
		if (!empty($menu_item->children)) {
			$submenu_class = 'submenu';
			if ($is_active || $has_active_child) {
				$submenu_class .= ' active';
			}
			$output .= '<div class="' . $submenu_class . '">';
			foreach ($menu_item->children as $child) {
				$output .= $this->render_menu_item($child, $current_url, $queried_object_id, $level + 1);
			}
			$output .= '</div>';
		}

		$output .= '</div>';
		return $output;
	}

	/**
	 * Render mobile menu item with submenu support
	 */
	private function render_mobile_menu_item($menu_item, $current_url, $level = 0) {
		$is_active = (trailingslashit($menu_item->url) === trailingslashit($current_url)) ? 'active' : '';
		
		$menu_class = 'mobile-menu-item';
		if ($is_active) {
			$menu_class .= ' active';
		}
		if (!empty($menu_item->children)) {
			$menu_class .= ' has-submenu';
		}
		if ($level > 0) {
			$menu_class .= ' submenu-item';
		}

		$output = '<div class="' . $menu_class . '">';
		$output .= '<a href="' . esc_url($menu_item->url) . '" class="' . $is_active . '">';
		$output .= esc_html($menu_item->title);
		if (!empty($menu_item->children)) {
			$output .= '<span class="mobile-submenu-toggle"><i class="fa fa-chevron-right"></i></span>';
		}
		$output .= '</a>';

		// Render mobile submenu if exists
		if (!empty($menu_item->children)) {
			$submenu_class = 'mobile-submenu';
			$output .= '<div class="' . $submenu_class . '">';
			foreach ($menu_item->children as $child) {
				$output .= $this->render_mobile_menu_item($child, $current_url, $level + 1);
			}
			$output .= '</div>';
		}

		$output .= '</div>';
		return $output;
	}

	public function widget($args, $instance) {
		global $wp;

		$queried_object_id = get_queried_object_id();
		$current_url = home_url(add_query_arg([], $wp->request));
		$locations = get_nav_menu_locations();
		if (empty($locations['primary'])) return null;

		$menu_items = wp_get_nav_menu_items($locations['primary']);
		$menu_tree = $this->build_menu_tree($menu_items);
		$count_wishlist = 0;

		if (is_user_logged_in()) {
			$count_wishlist = get_user_wishlist_count();
		} 
		else {
			if (class_exists('YITH_WCWL_Wishlist_Factory')) {
				$wishlist = YITH_WCWL_Wishlist_Factory::get_current_wishlist();
			
				if ($wishlist && is_a($wishlist, 'YITH_WCWL_Wishlist')) {
					$items = $wishlist->get_items();
					$count_wishlist = is_array($items) ? count($items) : 0;
				}
			}
		}

		$count_wishlist = $count_wishlist > 0 ? '<span class="wishlist-count">' . $count_wishlist . '</span>' : '';

		echo $args['before_widget'];

		?>
	
		<div class="header-menu-desktop">
			<form class="search-form" action="/" method="get">
				<input type="text" name="s" placeholder="<?php echo esc_attr__('Tìm kiếm...', 'astra-child'); ?>" required
						oninvalid="this.setCustomValidity('Vui lòng nội dung tìm kiếm')"
						oninput="this.setCustomValidity('')"
				>
				<img class="search-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icon/search.svg" alt="zoom">
				<a href="/san-pham-yeu-thich" class="wishlist-icon"><i class="fa fa-heart-o" aria-hidden="true"></i><?php echo $count_wishlist; ?></a>
			</form>
			<div class="menu-list">
				<?php foreach ($menu_tree as $menu_item): ?>
					<?php echo $this->render_menu_item($menu_item, $current_url, $queried_object_id); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="header-menu-mobile">
			<div class="mobile-menu-toggle" aria-expanded="false" data-index="0">
				<span class="screen-reader-text">Main Menu</span>
				<span class="mobile-menu-toggle-icon">
					<span aria-hidden="true" class="toggle-icon active" data-action="open">
						<img class="search-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icon/menu-bar.svg" alt="zoom">
					</span>
					<span aria-hidden="true" class="toggle-icon" data-action="close">
						<img class="search-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icon/menu-bar-close.svg" alt="zoom">
					</span>
				</span>
			</div>
			<div class="menu-list">
				<?php foreach ($menu_tree as $menu_item): ?>
					<?php echo $this->render_mobile_menu_item($menu_item, $current_url); ?>
				<?php endforeach; ?>
				<div class="mobile-menu-item">
					<a href="/san-pham-yeu-thich" class="wishlist-link <?= trailingslashit($current_url) == trailingslashit(home_url("san-pham-yeu-thich")) ? 'active' : '' ?>"><?php echo esc_attr__('Sản phẩm yêu thích', 'astra-child'); ?></a>
				</div>
				<form class="search-form" action="/" method="get">
					<input type="text" name="s" placeholder="<?php echo esc_attr__('Tìm kiếm...', 'astra-child'); ?>" required
							oninvalid="this.setCustomValidity('Vui lòng nội dung tìm kiếm')"
							oninput="this.setCustomValidity('')"
					>
					<button type="submit" class="btn-search"><img class="search-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icon/search.svg" alt="zoom"></button>
				</form>
			</div>
		</div>

		<?php
		echo $args['after_widget'];
	}
}
