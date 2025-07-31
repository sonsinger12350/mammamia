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
						<p class="item-description">' . esc_html($category->description) . '</p>
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
			'collection'    => $params['collection'],
			'price_type'    => $params['price_type'],
			'set_up'    	=> $params['set_up'],
		];
		$content .= '
			<div class="item">
				<div class="item-header">
					<h3 class="item-title">' . esc_html($category->name) . '</h3>
					<p class="item-description">' . esc_html($category->description) . '</p>
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