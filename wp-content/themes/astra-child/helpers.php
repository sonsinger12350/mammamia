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
		$output .= '
			<div class="product">
				' . do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . $post->ID . '"]') . '
				<a class="product-content" href="' . get_permalink($post) . '">
					<div class="product-image">' . get_the_post_thumbnail($post, 'post-thumbnail') . '</div>
					<p class="product-title">' . get_the_title($post) . '</p>
				</a>
			</div>
		';
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
	$data = [];

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

function get_product_color( $product ) {
    $attribute_name = 'pa_mau-sac';
    $colors = [];

    if ( ! $product || ! $product->is_type( 'variable' ) ) return $colors;

    $children_ids = $product->get_children();

    foreach ( $children_ids as $child_id ) {
        $variation = wc_get_product( $child_id );

        if ( ! $variation || ! $variation->exists() ) continue;

        $attributes = $variation->get_attributes();

        if ( isset( $attributes[ $attribute_name ] ) ) {
            $color_slug = $attributes[ $attribute_name ];
            $term = get_term_by( 'slug', $color_slug, $attribute_name );

            if ( ! $term ) continue;

            $image_id  = $variation->get_image_id();
            $image_url = $image_id ? wp_get_attachment_url( $image_id ) : null;
			$color_code  = get_term_meta( $term->term_id, 'product_attribute_color', true );

            // Tránh trùng màu (chỉ lấy 1 ảnh đầu tiên mỗi màu)
            if ( ! isset( $colors[ $color_slug ] ) ) {
                $colors[ $color_slug ] = [
                    'slug'  => $color_slug,
                    'name'  => $term->name,
                    'color' => $color_code,
                    'image' => $image_url,
                ];
            }
        }
    }

    return array_values( $colors ); // trả về mảng tuần tự
}

function get_all_acf_fields( $product_id ) {
    $acf_fields = [];
    $raw_fields = get_fields( $product_id );

    if ( is_array( $raw_fields ) ) {
        foreach ( $raw_fields as $key => $value ) {
			if ($key == 'warranty_policy') {
				$newValue = [];

				if (!empty($value)) {
					foreach ($value as $item) {
						$newValue[] = $item['label'];
					}
				}

				$acf_fields[$key] = [
					'label' => get_field_object( $key, $product_id )['label'] ?? $key,
					'value' => $newValue,
				];
			}
			else {
				$acf_fields[$key] = [
					'label' => get_field_object( $key, $product_id )['label'] ?? $key,
					'value' => $value,
				];
			}
            
        }
    }

    return $acf_fields;
}

function get_user_wishlist_count() {
	global $wpdb;

	$user_id = get_current_user_id();
	$table = $wpdb->prefix . 'yith_wcwl';

	if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) return 0;

	$wishlist_count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE user_id = %d",
			$user_id
		)
	);
	return $wishlist_count;
}
