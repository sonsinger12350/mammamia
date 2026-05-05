<?php

add_action('wp_ajax_load_projects_page', 'load_projects_page');
add_action('wp_ajax_nopriv_load_projects_page', 'load_projects_page');

function load_projects_page() {
	$paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
	echo get_projects_list_html($paged);
	wp_die();
}

add_action('wp_ajax_filter_products', 'filter_products');
add_action('wp_ajax_nopriv_filter_products', 'filter_products');

function filter_products() {
	if (empty($_POST['form'])) return null;

	$params = [];
	parse_str($_POST['form'], $params);

	$child_categories = get_terms([
		'taxonomy'   => 'product_cat',
		'parent'     => $params['category'],
		'hide_empty' => true,
	]);

	$content = '';

	if (!empty($child_categories) && !is_wp_error($child_categories)) {
		foreach ($child_categories as $category) {
			$paramsProduct = [
				'category' 		=> $category,
				'brand'   	 	=> $params['brand'],
				'bo-suu-tap'    => $params['bo-suu-tap'],
				'price_type'    => $params['price_type'],
				'set_up'    	=> $params['set_up'],
			];

			$content .= '
				<div class="item">
					<div class="item-header">
						<h3 class="item-title">' . esc_html($category->name) . '</h3>
						<!--<p class="item-description">' . wp_kses_post($category->description) . '</p>-->
					</div>
					<hr>
					<div class="item-body">
						'. getProductListByConditions($paramsProduct) .'
					</div>
				</div>
			';
		}
	}
	else {
		$category = $category = get_term($params['category'], 'product_cat');
		$paramsProduct = [
			'category' 		=> $category,
			'brand'   	 	=> $params['brand'],
			'bo-suu-tap'    => $params['bo-suu-tap'],
			'price_type'    => $params['price_type'],
			'set_up'    	=> $params['set_up'],
		];
		$content .= '
			<div class="item">
				<div class="item-header">
					<h3 class="item-title">' . esc_html($category->name) . '</h3>
					<!--<p class="item-description">' . wp_kses_post($category->description) . '</p>-->
				</div>
				<hr>
				<div class="item-body">
					'. getProductListByConditions($paramsProduct) .'
				</div>
			</div>
		';
	}

	echo $content;
	wp_die();
}

add_action('wp_ajax_login_user', 'login_user');
add_action('wp_ajax_nopriv_login_user', 'login_user');

function login_user() {
	if (empty($_POST['form'])) return null;

	$params = [];
	parse_str($_POST['form'], $params);

	$user = wp_signon([
		'user_login'    => $params['email'],
		'user_password' => $params['password'],
	]);

	if (is_wp_error($user)) {
		wp_send_json_error([
			'message' => wp_strip_all_tags($user->get_error_message()),
		]);
	}

	wp_send_json_success([
		'message' => 'Đăng nhập thành công',
	]);
}

add_action('wp_ajax_register_user', 'register_user');
add_action('wp_ajax_nopriv_register_user', 'register_user');

function register_user() {
	if (empty($_POST['form'])) return null;

	$params = [];
	parse_str($_POST['form'], $params);

	$user = wp_create_user($params['email'], $params['password'], $params['email']);

	if (is_wp_error($user)) wp_send_json_error([
		'message' => wp_strip_all_tags($user->get_error_message()),
	]);

	$user = wp_signon([
		'user_login'    => $params['email'],
		'user_password' => $params['password'],
	]);

	wp_send_json_success([
		'message' => 'Đăng ký thành công',
	]);
}

if ( defined( 'YITH_WCWL' ) && ! function_exists( 'yith_wcwl_ajax_update_count' ) ) {
    function yith_wcwl_ajax_update_count() {
        wp_send_json( array(
            'count' => yith_wcwl_count_all_products()
        ) );
    }

    add_action( 'wp_ajax_yith_wcwl_update_wishlist_count', 'yith_wcwl_ajax_update_count' );
    add_action( 'wp_ajax_nopriv_yith_wcwl_update_wishlist_count', 'yith_wcwl_ajax_update_count' );
}

add_action('wp_ajax_get_collection_options', 'ajax_get_collection_options');
add_action('wp_ajax_nopriv_get_collection_options', 'ajax_get_collection_options');

function ajax_get_collection_options() {
	// This endpoint is public (nopriv). Cached pages can serve stale nonce values
	// to guests, so enforce nonce strictly only for logged-in users.
	$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
	if (is_user_logged_in() && !wp_verify_nonce($nonce, 'filter_product_nonce')) {
		wp_send_json_error('Security check failed', 403);
	}

	$brand_id = intval($_POST['brand_id']);
	if (empty($brand_id)) wp_send_json_error('Invalid parameters');
	$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

	// Get collection options using optimized function
	$collection_options = getCollectionOptionsByBrandOptimized($brand_id, $category_id);

	// Build HTML options
	$html = '<option value="">Tất cả</option>';

	if (!empty($collection_options)) {
		foreach ($collection_options as $option) {
			// Backward compatibility: support old cached format (string value only)
			$collection_name = is_array($option) ? ($option['name'] ?? '') : $option;
			$collection_slug = is_array($option) ? ($option['slug'] ?? '') : '';
			$collection_count = is_array($option) ? (int) ($option['count'] ?? 0) : 0;

			if (empty($collection_name)) continue;
			$option_value = !empty($collection_slug) ? $collection_slug : $collection_name;

			$label = $collection_count > 0 ? sprintf('%s (%d)', $collection_name, $collection_count) : $collection_name;
			$html .= '<option value="' . esc_attr($option_value) . '">' . esc_html($label) . '</option>';
		}
	}

	wp_send_json_success([
		'html' => $html,
		'options' => $collection_options
	]);
}

/**
 * Clear collection options cache when products are updated
 */
function clear_collection_options_cache() {
	global $wpdb;
	
	// Get all brand IDs
	$brand_ids = $wpdb->get_col("
		SELECT DISTINCT t.term_id 
		FROM {$wpdb->terms} t 
		INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
		WHERE tt.taxonomy = 'product_brand'
	");
	
	// Delete all collection options cache for all brands
	foreach ($brand_ids as $brand_id) {
		delete_transient('collection_options_brand_' . $brand_id);
	}
	
	// Clear all scoped optimized caches (all_collection_options_all_brands_{category_id})
	$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_all_collection_options_all_brands_%'");
	$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_all_collection_options_all_brands_%'");
}

// Hook to clear cache when products are updated
add_action('save_post_product', 'clear_collection_options_cache');
add_action('wp_update_nav_menu', 'clear_collection_options_cache');
add_action('wp_insert_term', 'clear_collection_options_cache');
add_action('wp_update_term', 'clear_collection_options_cache');
add_action('wp_delete_term', 'clear_collection_options_cache');