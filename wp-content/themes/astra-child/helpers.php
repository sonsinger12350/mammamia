<?php

function get_projects_list_html($paged = 1) {
	$args = [
		'post_type' => 'du-an',
		'posts_per_page' => 3,
		'post_status' => 'publish',
		'paged' => $paged,
	];

	$query = new WP_Query($args);

	if (!$query->have_posts()) return '<div>Không có dự án nào.</div>';

	ob_start();
	$output = '';
	$output .= '<div class="list">';

	foreach ($query->posts as $post) {
		$output .= '<div class="item">'
					. '<div class="item-image">' . get_the_post_thumbnail($post, 'full') . '</div>'
					. '<div class="item-body">'
						. '<div class="content">'
							. '<h3 class="item-title">' . get_the_title($post) . '</h3>'
							. '<div class="item-content">' . get_the_excerpt($post) . '</div>'
						. '</div>'
						. '<div class="item-footer">'
							. '<a href="' . get_the_permalink($post) . '" class="button-primary">Xem thêm</a>'
						. '</div>'
					. '</div>'
				. '</div>';
	}

	$output .= '</div>';
	$output .= '<hr>';

	// Pagination
	$total_pages = $query->max_num_pages;

	if ($total_pages > 1) {
		$output .= '<div class="pagination">';

		for ($i = 1; $i <= $total_pages; $i++) {
			if ($i == $paged) $output .= '<span class="page-number current">' . $i . '</span>'; // current page
			else $output .= '<button data-page="' . $i . '" class="page-number">' . $i . '</button>';
		}

		// Next page button
		if ($paged < $total_pages) {
			$next_page = $paged + 1;
			$output .= '<button data-page="' . $next_page . '" class="next-page"><i class="fa fa-angle-double-right" aria-hidden="true"></i></button>';
		}

		$output .= '</div>';
	}

	echo $output;
	return ob_get_clean();
}

function getProductListByConditions($conditions) {
	if (empty($conditions['category'])) return null;

	$args = [
		'post_type'      => 'product',
		'posts_per_page' => 12,
		'post_status'    => 'publish',
		'tax_query'      => [
			'relation' => 'AND',
			[
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $conditions['category']->term_id,
			],
		],
		'meta_query'     => [
			'relation' => 'AND',
		],
	];

	foreach ($conditions as $k => $v) {
		if ($k == 'category') continue;

		if (!empty($v)) {
			if ($k == 'brand') {
				$args['tax_query'][] = [
					'taxonomy' => 'product_brand',
					'field'    => 'term_id',
					'terms'    => $v,
				];
			}
			else {
				$args['meta_query'][] = [
					'key'     => $k,
					'value'   => $v,
					'compare' => '=',
				];
			}
		}
	}

	$query = new WP_Query($args);
	if (!$query->have_posts()) return '<p align="center">Sản phẩm đang cập nhật.</p>';

	ob_start();
	$output = '';
	$output .= '<div class="list-product ' . (count($query->posts) > 4 ? 'collapse' : '') . '">';

	foreach ($query->posts as $post) {
		$output .= '<div class="product">'
					. '<button class="add-favorite" value="'. $post->ID .'"><i class="fa fa-heart-o" aria-hidden="true"></i></button>'
					. '<a class="product-content" href="' . get_permalink($post) . '">'
						. '<div class="product-image">' . get_the_post_thumbnail($post, 'post-thumbnail') . '</div>'
						. '<p class="product-title">' . get_the_title($post) . '</p>'
					. '</a>'
				. '</div>';
	}

	$output .= '</div>';

	$output .= '<div class="button-footer">';

	if (count($query->posts) > 4) {
		$output .= '
			<button type="button" class="button-primary btn-collapse">
				<span class="open">Xem thêm</span><span class="close">Thu gọn</span> <i class="fa fa-angle-down" aria-hidden="true"></i>
			</button>
		';
	}
	
	$output .= '
		<a href="' . get_term_link($conditions['category'], 'product_cat') . '" class="btn-view-all">
			Truy cập danh mục ' . $conditions['category']->name . ' <i class="fa fa-angle-double-right" aria-hidden="true"></i>
		</a>
		</div>
	';

	echo $output;
	return ob_get_clean();
}

function getAllBrandByCat($cat) {
	if (empty($cat->taxonomy) || $cat->taxonomy != 'product_cat') return null;
	global $wpdb;

	$listCat = get_term_children($cat->term_id, $cat->taxonomy);
	$listCat[] = $cat->term_id;
	$listCat = implode(',', $listCat);

	$wpdb->query("SET SESSION group_concat_max_len = 500000");
	$sql = " SELECT GROUP_CONCAT(DISTINCT tr1.term_taxonomy_id)
		FROM {$wpdb->term_relationships} tr1
		JOIN (
			SELECT DISTINCT object_id
			FROM {$wpdb->term_relationships}
			WHERE term_taxonomy_id IN ($listCat)
		) AS filtered_objects ON tr1.object_id = filtered_objects.object_id
		WHERE tr1.term_taxonomy_id NOT IN ($listCat)
	";

	$taxonomy = $wpdb->get_var($sql);
	if (empty($taxonomy)) return null;

	$sql = "SELECT t.term_id, tt.taxonomy, t.name
		FROM {$wpdb->term_taxonomy} AS tt
		JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
		WHERE tt.taxonomy = 'product_brand'
		AND t.term_id IN ($taxonomy)
	";
	$result = $wpdb->get_results($sql);

	foreach ($result as $item) {
		$data[$item->term_id] = $item->name;
	}

	return $data;
}

function getAllCustomFieldValueByCat($cat, $customField) {
	if (empty($cat->taxonomy) || $cat->taxonomy != 'product_cat' || empty($customField)) return null;

	global $wpdb;

	$listCat = get_term_children($cat->term_id, $cat->taxonomy);
	$listCat[] = $cat->term_id;
	$listCat = implode(',', array_map('intval', $listCat));

	$wpdb->query("SET SESSION group_concat_max_len = 500000");
	$sql = "
		SELECT GROUP_CONCAT(DISTINCT p.ID)
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
		INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
		WHERE tt.taxonomy = 'product_cat'
		AND tt.term_id IN ($listCat)
		AND p.post_type = 'product'
		AND p.post_status = 'publish'
	";

	$product_ids = $wpdb->get_var($sql);
	if (empty($product_ids)) return null;

	$sql = "
		SELECT DISTINCT meta_value
		FROM {$wpdb->postmeta}
		WHERE post_id IN ($product_ids)
		AND meta_key = '$customField'
		AND meta_value IS NOT NULL
		AND meta_value != ''
	";

	return $wpdb->get_col($sql);
}
