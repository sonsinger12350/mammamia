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
