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

function getProductListByConditions($conditions, $limit = 12) {
	if (empty($conditions['category'])) return null;

	$args = [
		'post_type'      => 'product',
		'posts_per_page' => $limit,
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

	// Các field là taxonomy
	$taxonomy_map = [
		'brand'      => 'product_brand',
		'bo-suu-tap' => 'bo-suu-tap',
		'set_up'     => 'set_up',
	];

	// Các field là custom field (meta_key)
	$meta_fields = [
		'price_type',
	];

	foreach ($conditions as $key => $value) {
		if ($key === 'category' || empty($value)) continue;

		// Nếu là taxonomy
		if (isset($taxonomy_map[$key])) {
			$args['tax_query'][] = [
				'taxonomy' => $taxonomy_map[$key],
				'field'    => $key === 'brand' ? 'term_id' : 'slug',
				'terms'    => (array) $value,
			];
		}
		// Nếu là meta field
		elseif (in_array($key, $meta_fields)) {
			$args['meta_query'][] = [
				'key'     => $key,
				'value'   => $value,
				'compare' => '=',
			];
		}
	}

	$args['meta_query'][] = [
		'relation' => 'OR',
		[
			'key'     => 'featured',
			'compare' => 'EXISTS',
		],
		[
			'key'     => 'featured',
			'compare' => 'NOT EXISTS',
		]
	];
	
	$args['orderby'] = [
		'meta_value_num' => 'DESC', // featured = 1 sẽ lên đầu
		'date'           => 'DESC'  // sau đó sắp xếp theo ngày
	];

	$query = new WP_Query($args);
	if (!$query->have_posts()) return '<p align="center">Sản phẩm đang cập nhật.</p>';

	ob_start();
	$output = '';
	$output .= '<div class="list-product ' . (count($query->posts) > 4 ? 'collapse' : '') . '">';

	foreach ($query->posts as $post) {
		$thumbnail = get_the_post_thumbnail($post, 'post-thumbnail');
		$thumbnail = !empty($thumbnail) ? $thumbnail : '<img src="' . get_stylesheet_directory_uri() . '/assets/images/no-image.jpg" alt="' . get_the_title($post) . '">';

		$output .= '
			<div class="product">
				' . do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . $post->ID . '"]') . '
				<a class="product-content" href="' . get_permalink($post) . '">
					<div class="product-image">' . $thumbnail . '</div>
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
	
	if ($limit != -1) {
		$output .= '
			<a href="' . get_term_link($conditions['category'], 'product_cat') . '" class="btn-view-all">
				Truy cập danh mục ' . $conditions['category']->name . ' <i class="fa fa-angle-double-right" aria-hidden="true"></i>
			</a>
		';
	}
	
	$output .= '</div>';

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

function getTaxonomyTermsByCat($catObj, $taxonomy) {
	if (!$catObj || !isset($catObj->term_id)) return [];

	if ($taxonomy == 'price_type') {
		return [
			'Luxury',
			'Premium',
		];
	}

	$cat_id = $catObj->term_id;

	// Lấy tất cả sản phẩm thuộc category hiện tại
	$args = [
		'post_type' => 'product',
		'posts_per_page' => -1,
		'tax_query' => [
			[
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $cat_id,
			]
		],
		'fields' => 'ids',
	];

	$product_ids = get_posts($args);
	if (empty($product_ids)) return [];

	$terms = [];

	// Lặp qua sản phẩm để lấy taxonomy terms tương ứng
	foreach ($product_ids as $product_id) {
		$product_terms = get_the_terms($product_id, $taxonomy);
		if ($product_terms && !is_wp_error($product_terms)) {
			foreach ($product_terms as $term) {
				$terms[$term->slug] = $term->name;
			}
		}
	}

	// Sắp xếp theo tên
	asort($terms);

	return $terms;
}

// Modifi this function to get all attribute of product
function get_all_attributes_with_images($product) {
    $data = [];

    if (!$product || !$product->is_type('variable')) return $data;

    $children_ids = $product->get_children();
    $default_image_url = get_stylesheet_directory_uri() . '/assets/images/no-image.jpg';

    foreach ($children_ids as $child_id) {
        $variation = wc_get_product($child_id);
        if (!$variation || !$variation->exists()) continue;

        $attributes = $variation->get_attributes();
        $color_slug = $attributes['pa_mau-sac'] ?? null;
        $size_slug  = $attributes['pa_kich-thuoc'] ?? null;

        if (!$color_slug) continue;

        // --- Lấy thông tin màu sắc ---
        $color_term = get_term_by('slug', $color_slug, 'pa_mau-sac');
        if (!$color_term) continue;

        $color_code = get_term_meta($color_term->term_id, 'product_attribute_color', true);

        // Khởi tạo nếu chưa có màu
        if (!isset($data[$color_slug])) {
            $data[$color_slug] = [
                'slug'  => $color_slug,
                'name'  => $color_term->name,
                'color' => $color_code,
            ];
        }

        // --- Lấy ảnh từ biến thể ---
        $image_id = $variation->get_image_id();
        $image_url = $image_id ? wp_get_attachment_url($image_id) : null;

        $gallery = get_post_meta($child_id, 'woo_variation_gallery_images', true);
        if (is_string($gallery)) $gallery = maybe_unserialize($gallery);
        if (!is_array($gallery)) $gallery = [];

        $gallery_urls = array_map('wp_get_attachment_url', $gallery);
        $gallery_urls = array_filter($gallery_urls);

        // Đẩy ảnh đại diện lên đầu nếu có
        if ($image_url) array_unshift($gallery_urls, $image_url);

        // Nếu không có ảnh nào thì thêm ảnh mặc định
        if (empty($gallery_urls)) $gallery_urls[] = $default_image_url;

        $sku = $variation->get_sku();

        // Có kích thước → ảnh + sku gắn vào từng size
        if ($size_slug) {
            $size_term = get_term_by('slug', $size_slug, 'pa_kich-thuoc');
            if (!$size_term) continue;

            if (!isset($data[$color_slug]['size'])) $data[$color_slug]['size'] = [];

            $data[$color_slug]['size'][$size_slug] = [
                'slug'   => $size_slug,
                'name'   => $size_term->name,
                'images' => $gallery_urls,
                'sku'    => $sku,
            ];
        }
		else {
            // Không có kích thước → ảnh + sku gắn vào màu
            if (!isset($data[$color_slug]['images'])) $data[$color_slug]['images'] = [];

            $data[$color_slug]['images'] = array_merge($data[$color_slug]['images'], $gallery_urls);
            $data[$color_slug]['images'] = array_unique($data[$color_slug]['images']);

            // Chỉ gán SKU nếu chưa có (tránh ghi đè nếu có nhiều biến thể cùng màu)
            if (!isset($data[$color_slug]['sku']) && $sku) $data[$color_slug]['sku'] = $sku;
        }
    }

    return $data;
}

function get_all_acf_fields( $product_id ) {
    $acf_fields = [];
    $raw_fields = get_fields( $product_id );

    if ( is_array( $raw_fields ) ) {
        foreach ( $raw_fields as $key => $value ) {
			$acf_fields[$key] = [
				'label' => get_field_object( $key, $product_id )['label'] ?? $key,
				'value' => $value,
			];
        }
    }

    return $acf_fields;
}

function get_design_file_fields_label($product_id) {
	$field_object = get_field_object('design_file', $product_id);
	$data = [];

	foreach ($field_object['sub_fields'] as $field) {
		$data[$field['name']] = $field['label'];
	}

	return $data;
}

function get_all_custom_field_values( $product_id ) {
	if (empty($product_id)) return null;

	$data = [];

	require_once WEBSITE_CONFIG_PLUGIN_PATH . 'website-config.php';
	require_once WEBSITE_CONFIG_PLUGIN_PATH . 'column-mappings.php';

	$website_config = new WebsiteConfig();

	foreach ($website_config->custom_taxonomies_mappings as $k => $v) {
		$list_terms = [];
		$terms = get_the_terms($product_id, $k);
		$data['custom_taxonomies'][$k] = [
			'label' => mamma_mia_get_column_key($v, false),
			'value' => [],
		];

		if (!empty($terms)) {
			foreach ($terms as $term) {
				$list_terms[] = [
					'label' => $term->name,
					'link' => get_term_link($term),
				];
			}

			$data['custom_taxonomies'][$k]['value'] = $list_terms;
		}
	}

	$custom_field = get_all_acf_fields($product_id);

	foreach ($custom_field as $k => $v) {
		if (in_array($k, $website_config->acf_fields) && !empty($v['value'])) {
			$data['custom_field'][$k] = $v;
		}
	}

	return $data;
}

/**
 * Get set_up field options for products in a specific category
 * 
 * @param object $catObj Category object
 * @return array Array of set_up options with key => value pairs
 */
function getOptionsCustomFieldByCat($catObj, $field_name) {
	if (!$catObj || !isset($catObj->term_id) || empty($field_name)) return [];

	$cat_id = $catObj->term_id;

	// Get all products in the current category
	$args = [
		'post_type' => 'product',
		'posts_per_page' => -1,
		'tax_query' => [
			[
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $cat_id,
			]
		],
		'fields' => 'ids',
	];

	$product_ids = get_posts($args);
	if (empty($product_ids)) return [];

	$set_up_values = [];

	// Loop through products to get set_up field values
	foreach ($product_ids as $product_id) {
		$set_up_value = get_field($field_name, $product_id);
		if (!empty($set_up_value)) $set_up_values[$set_up_value] = $set_up_value;
	}

	if (isset($set_up_values['Không có'])) unset($set_up_values['Không có']);

	// Sort by value
	asort($set_up_values);
	
	return $set_up_values;
}

/**
 * Get collection options based on brand only - Optimized version
 * 
 * @param int $brand_id Brand term ID
 * @return array Array of collection options with key => value pairs
 */
function getCollectionOptionsByBrand($brand_id) {
	if (empty($brand_id)) return [];

	// Check cache first (both transient and object cache)
	$cache_key = 'collection_options_brand_' . $brand_id;
	$cached_result = get_transient($cache_key);
	
	if ($cached_result !== false) return $cached_result;
	
	// Also check object cache if available
	$object_cache_key = 'collection_options_brand_' . $brand_id;
	$object_cached_result = wp_cache_get($object_cache_key, 'filter_product');
	
	if ($object_cached_result !== false) return $object_cached_result;

	// Use direct database query for better performance
	global $wpdb;
	
	$sql = $wpdb->prepare("
		SELECT DISTINCT t.term_id, t.name
		FROM {$wpdb->terms} t
		INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
		INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
		INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
		INNER JOIN {$wpdb->term_relationships} tr2 ON p.ID = tr2.object_id
		INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
		WHERE tt.taxonomy = 'bo-suu-tap'
		AND tt2.taxonomy = 'product_brand'
		AND tt2.term_id = %d
		AND p.post_type = 'product'
		AND p.post_status = 'publish'
		ORDER BY t.name ASC
	", $brand_id);

	$results = $wpdb->get_results($sql);
	
	$collection_values = [];

	foreach ($results as $result) {
		$collection_values[$result->term_id] = $result->name;
	}

	// Cache the result for 1 hour (3600 seconds) in both transient and object cache
	set_transient($cache_key, $collection_values, 3600);
	wp_cache_set($object_cache_key, $collection_values, 'filter_product', 3600);

	return $collection_values;
}

/**
 * Preload collection options cache for all brands to improve performance
 */
function preload_collection_options_cache() {
	global $wpdb;
	
	// Get all brand IDs
	$brand_ids = $wpdb->get_col("
		SELECT DISTINCT t.term_id 
		FROM {$wpdb->terms} t 
		INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
		WHERE tt.taxonomy = 'product_brand'
	");
	
	// Preload cache for each brand
	foreach ($brand_ids as $brand_id) {
		getCollectionOptionsByBrand($brand_id);
	}
}

/**
 * Initialize cache preloading on appropriate hooks
 */
function init_collection_options_cache() {
	// Preload cache on category pages
	if (is_tax('product_cat')) preload_collection_options_cache();
}

// Hook to preload cache
add_action('wp', 'init_collection_options_cache');

/**
 * Get all collection options for all brands in a single query - Most efficient approach
 */
function getAllCollectionOptionsForAllBrands($category_id = 0) {
	global $wpdb;
	
	// Check cache first
	$category_id = absint($category_id);
	$cache_key = 'all_collection_options_all_brands_' . $category_id;
	$cached_result = get_transient($cache_key);
	
	if ($cached_result !== false) return $cached_result;

	$category_ids = [];
	if (!empty($category_id)) {
		$category_ids = get_term_children($category_id, 'product_cat');
		$category_ids[] = $category_id;
		$category_ids = array_map('absint', $category_ids);
		$category_ids = array_filter($category_ids);
	}

	$category_condition = '';
	if (!empty($category_ids)) {
		$category_condition = ' AND tt3.term_id IN (' . implode(',', $category_ids) . ')';
	}
	
	// Single query to get all collection options grouped by brand
	$sql = "
		SELECT 
			tt2.term_id as brand_id,
			t.term_id as collection_id,
			t.name as collection_name,
			t.slug as collection_slug,
			COUNT(DISTINCT p.ID) as product_count
		FROM {$wpdb->terms} t
		INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
		INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
		INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
		INNER JOIN {$wpdb->term_relationships} tr2 ON p.ID = tr2.object_id
		INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
		INNER JOIN {$wpdb->term_relationships} tr3 ON p.ID = tr3.object_id
		INNER JOIN {$wpdb->term_taxonomy} tt3 ON tr3.term_taxonomy_id = tt3.term_taxonomy_id
		WHERE tt.taxonomy = 'bo-suu-tap'
		AND tt2.taxonomy = 'product_brand'
		AND tt3.taxonomy = 'product_cat'
		AND p.post_type = 'product'
		AND p.post_status = 'publish'
		{$category_condition}
		GROUP BY tt2.term_id, t.term_id, t.name, t.slug
		HAVING product_count > 0
		ORDER BY tt2.term_id, t.name ASC
	";

	$results = $wpdb->get_results($sql);
	
	// Group results by brand
	$brand_collections = [];

	foreach ($results as $result) {
		if (!isset($brand_collections[$result->brand_id])) $brand_collections[$result->brand_id] = [];

		$brand_collections[$result->brand_id][$result->collection_id] = [
			'name' => $result->collection_name,
			'slug' => $result->collection_slug,
			'count' => (int) $result->product_count,
		];
	}
	
	// Cache the result for 2 hours (7200 seconds)
	set_transient($cache_key, $brand_collections, 7200);
	
	return $brand_collections;
}

/**
 * Optimized version of getCollectionOptionsByBrand using preloaded data
 */
function getCollectionOptionsByBrandOptimized($brand_id, $category_id = 0) {
	if (empty($brand_id)) return [];
	
	// Get all collections for all brands in current category scope
	$all_collections = getAllCollectionOptionsForAllBrands($category_id);
	
	// Return collections for specific brand
	return isset($all_collections[$brand_id]) ? $all_collections[$brand_id] : [];
}