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
		$is_current = isset($menu_item->object_id) && intval($menu_item->object_id) === intval($queried_object_id);
		$menu_path    = user_trailingslashit( wp_make_link_relative( $menu_item->url ) );
		$current_path = user_trailingslashit( wp_make_link_relative( $current_url ) );

		if (!$is_current) $is_current = $menu_path === $current_path;
		if (!$is_current) $is_current = ($menu_path !== '/') && str_starts_with($current_path, $menu_path);
	
		$menu_class = 'menu-item';
		if ($is_current) $menu_class .= ' active';
		if (!empty($menu_item->children)) $menu_class .= ' has-submenu';
		if ($level > 0) $menu_class .= ' submenu-item';
	
		$output = '<div class="' . esc_attr($menu_class) . '">';
		$output .= '<a href="' . esc_url($menu_item->url) . '" class="' . ($is_current ? 'active' : '') . '">';
		$output .= esc_html($menu_item->title);
		if (!empty($menu_item->children)) $output .= '<span class="submenu-toggle"><i class="fa fa-chevron-down"></i></span>';

		$output .= '</a>';
	
		// Chỉ render submenu nếu có
		if (!empty($menu_item->children)) {
			$output .= '<div class="submenu">';

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
		$menu_path    = user_trailingslashit( wp_make_link_relative( $menu_item->url ) );
		$current_path = user_trailingslashit( wp_make_link_relative( $current_url ) );

		$is_active = $menu_path === $current_path ? 'active' : '';
		if (!$is_active) $is_active = ($menu_path !== '/') && str_starts_with($current_path, $menu_path) ? 'active' : '';

		$menu_class = 'mobile-menu-item';
		if ($is_active) $menu_class .= ' active';
		if (!empty($menu_item->children)) $menu_class .= ' has-submenu';
		if ($level > 0) $menu_class .= ' submenu-item';

		$output = '<div class="' . $menu_class . '">';
		$output .= '<a href="' . esc_url($menu_item->url) . '" class="' . $is_active . '">';
		$output .= esc_html($menu_item->title);
		if (!empty($menu_item->children)) $output .= '<span class="mobile-submenu-toggle"><i class="fa fa-chevron-right"></i></span>';
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
			$count_wishlist = yith_wcwl_count_all_products();
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
				<img class="search-icon menu-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icon/search.svg" alt="search-icon" width="25" height="25">
				<a href="/san-pham-yeu-thich" class="wishlist-icon menu-icon" aria-label="Danh sách yêu thích"><i class="fa fa-heart-o" aria-hidden="true"></i><?php echo $count_wishlist; ?></a>
				<?php if (is_user_logged_in()): ?>
					<div class="dropdown">
						<span class="menu-icon btn-link-custom" id="dropdownUserAction" data-bs-toggle="dropdown" aria-expanded="false" rel="nofollow">
							<i class="fa fa-user-circle-o" aria-hidden="true"></i>
						</span>

						<ul class="dropdown-menu" aria-labelledby="dropdownUserAction">
							<li><a class="dropdown-item" href="<?php echo wp_logout_url(home_url()); ?>">Đăng xuất</a></li>
						</ul>
					</div>
				<?php else: ?>
					<span onclick="showCustomModal('#modal-login')" class="menu-icon btn-link-custom" rel="nofollow" aria-label="Đăng nhập"><i class="fa fa-sign-in" aria-hidden="true"></i></span>
				<?php endif; ?>
			</form>
			<div class="menu-list">
				<?php foreach ($menu_tree as $menu_item): ?>
					<?php echo $this->render_menu_item($menu_item, $current_url, $queried_object_id); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="header-menu-mobile">
			<div class="mobile-menu-toggle" aria-expanded="false" aria-controls="menu-list-mobile" aria-label="Mở menu" data-index="0">
				<span class="screen-reader-text">Main Menu</span>
				<span class="mobile-menu-toggle-icon">
					<span aria-hidden="true" class="toggle-icon active" data-action="open">
						<img class="search-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icon/menu-bar.svg" alt="menu-bar" width="24" height="24">
					</span>
					<span aria-hidden="true" class="toggle-icon" data-action="close">
						<img class="search-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icon/menu-bar-close.svg" alt="menu-bar-close" width="24" height="24">
					</span>
				</span>
			</div>
			<div id="menu-list-mobile" class="menu-list">
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
					<button type="submit" class="btn-search"><img class="search-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icon/search.svg" alt="search-icon" width="25" height="24"></button>
				</form>
			</div>
		</div>
		<?php
		echo $args['after_widget'];
	}
}
