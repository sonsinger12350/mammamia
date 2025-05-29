<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn chặn truy cập trực tiếp
}

$args = array(
    'category_name' => 'su-kien',
    'posts_per_page' => 6,
);

$query = new WP_Query($args);

if (empty($query->posts)) return;

$output = '<div class="events-list">';

foreach ($query->posts as $post) {
    $output .= '<div class="event-item">';
    $output .= '<div class="item-image">';
    $output .= '<a href="' . get_the_permalink($post) . '">';
    $output .= get_the_post_thumbnail($post, 'full');
    $output .= '</a>';
    $output .= '</div>';
    $output .= '<div class="item-content">';
    $output .= '<h3 class="event-title">' . get_the_title($post) . '</h3>';
    $output .= '<div class="event-content">' . get_the_excerpt($post) . '</div>';
    $output .= '</div>';
    $output .= '</div>';
}

$output .= '</div>';

echo $output;
?>
