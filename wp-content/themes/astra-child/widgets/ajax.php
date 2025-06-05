<?php

add_action('wp_ajax_load_projects_page', 'load_projects_page');
add_action('wp_ajax_nopriv_load_projects_page', 'load_projects_page');

function load_projects_page() {
	$paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
	echo get_projects_list_html($paged);
	wp_die();
}
