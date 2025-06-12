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

function get_product_list_by_category($cat) {
	$args = [
		'post_type'      => 'product',
		'posts_per_page' => 12,
		'post_status'    => 'publish',
		'tax_query'      => [
			[
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $cat,
			],
		],
	];

	$query = new WP_Query($args);

	if (!$query->have_posts()) return '<div>Không có dự án nào.</div>';

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
	if (count($query->posts) > 4) $output .= '<button type="button" class="button-primary btn-collapse"><span class="open">Xem thêm</span><span class="close">Thu gọn</span> <i class="fa fa-angle-down" aria-hidden="true"></i></button>';
	$output .= '<a href="' . get_term_link($cat, 'product_cat') . '" class="button-primary btn-view-all">Truy cập danh mục <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>';
	$output .= '</div>';

	echo $output;
	return ob_get_clean();
}