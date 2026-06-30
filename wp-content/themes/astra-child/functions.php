<?php
require_once get_stylesheet_directory() . '/helpers.php';
require_once get_stylesheet_directory() . '/ajax.php';

// Widgets: [path => class]
$custom_widgets = [
	'/widgets/footer-contact-info.php' => 'Astra_Child_Custom_Widget_Footer_Contact_Info',
	'/widgets/footer-socials.php'      => 'Astra_Child_Custom_Widget_Footer_Socials',
	'/widgets/footer-menu.php'         => 'Astra_Child_Custom_Widget_Footer_Menu',
	'/widgets/header-menu.php'         => 'Astra_Child_Custom_Widget_Header_Menu',
];

// Require widget file
foreach ($custom_widgets as $path => $class) {
	require_once get_stylesheet_directory() . $path;
}

// Register widget
add_action('widgets_init', function() use ($custom_widgets) {
	foreach ($custom_widgets as $class) {
		if (class_exists($class)) {
			register_widget($class);
		}
	}
});

function custom_enqueue_editor_styles() {
	// Css for Elementor editor in admin
	wp_enqueue_style(
		'astra-child-custom-widget-css-editor',
		get_stylesheet_directory_uri() . '/css/custom-widget.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/custom-widget.css' )
	);
}
add_action( 'elementor/editor/after_enqueue_styles', 'custom_enqueue_editor_styles' );

function custom_enqueue_styles() {
	wp_enqueue_script('jquery');

    // Bootstrap
    wp_enqueue_style(
		'bootstrap',
		'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/css/bootstrap.min.css',
		array(),
		'5.3.7'
	);

	wp_enqueue_script(
		'bootstrap',
		'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/js/bootstrap.bundle.min.js',
		array(),
		'5.3.7',
		true
	);

	wp_enqueue_style('font-awesome');

	// Owl Carousel
	wp_enqueue_style(
		'owl-carousel',
		'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css',
		array(),
		'2.3.4'
	);

	wp_enqueue_script(
		'owl-carousel',
		'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js',
		array(),
		'2.3.4',
		true
	);

	// Fancybox
	wp_enqueue_style(
		'fancybox',
		'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css',
		array(),
		'3.5.7'
	);

	wp_enqueue_script(
		'fancybox',
		'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js',
		array(),
		'3.5.7',
		true
	);

	// OverlayScrollbars
	wp_enqueue_style(
		'overlayscrollbars',
		'https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/2.11.0/styles/overlayscrollbars.min.css',
		array(),
		'2.11.0'
	);

	wp_enqueue_script(
		'overlayscrollbars',
		'https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/2.11.0/browser/overlayscrollbars.browser.es6.min.js',
		array('jquery'),
		'2.11.0',
		true
	);

	// Toastify
	wp_enqueue_style(
		'toastify',
		'https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css',
		array(),
		'1.12.0'
	);

	wp_enqueue_script(
		'toastify',
		'https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js',
		array('jquery'),
		'1.12.0',
		true
	);

    wp_enqueue_style(
		'custom-style',
		get_stylesheet_directory_uri() . '/css/custom.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/custom.css' )
	);

	wp_enqueue_script(
		'custom-script',
		get_stylesheet_directory_uri() . '/js/custom.js',
		array(),
		filemtime( get_stylesheet_directory() . '/js/custom.js' ),
		true
	);

	// Localize AJAX URL for filter product widget
	wp_localize_script('custom-script', 'filter_product_ajax', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('filter_product_nonce')
	));

	// Elementor Pagination Scroll Custom - Scroll to top when pagination clicked
	wp_enqueue_script(
		'elementor-pagination-scroll',
		get_stylesheet_directory_uri() . '/js/elementor-pagination-scroll.js',
		array('jquery', 'elementor-frontend'),
		filemtime( get_stylesheet_directory() . '/js/elementor-pagination-scroll.js' ),
		true
	);

	wp_enqueue_style(
		'astra-child-custom-widget-css',
		get_stylesheet_directory_uri() . '/css/custom-widget.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/custom-widget.css' )
	);

    wp_enqueue_style(
		'animation',
		get_stylesheet_directory_uri() . '/css/animation.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/animation.css' )
	);
    
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_styles' );

// Register new widget group for Elementor
add_action('elementor/elements/categories_registered', function($elements_manager) {
	$elements_manager->add_category(
		'widget-custom',
		[
			'title' => __('Custom', 'astra-child'),
			'icon' => 'fa fa-plug',
		]
	);
});

// Register custom Elementor widget
add_action('elementor/widgets/register', function($widgets_manager) {
	if (defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')) {
		$widgets = [
			'/widgets/home/projects.php'        				=> 'Custom_Elementor_Widget_Projects',
			'/widgets/home/partners.php'        				=> 'Custom_Elementor_Widget_Partners',
			'/widgets/projects/slide.php'       				=> 'Custom_Elementor_Widget_Projects_Slide',
			'/widgets/projects/list.php'        				=> 'Custom_Elementor_Widget_Projects_List',
			'/widgets/products/list-product-category.php'       => 'Custom_Elementor_Widget_Product_List_By_Category',
			'/widgets/products/filter-product.php'       		=> 'Custom_Elementor_Widget_Filter_Product',
			'/widgets/products/product-content.php'       		=> 'Custom_Elementor_Widget_Product_Content',
			'/widgets/products/product-content-tab.php'       	=> 'Custom_Elementor_Widget_Product_Content_Tab',
			'/widgets/products/product-image-slide.php'       	=> 'Custom_Elementor_Widget_Product_Image_Slide',
		];

		foreach ($widgets as $path => $class) {
			require_once get_stylesheet_directory() . $path;
			if (class_exists($class)) {
				\Elementor\Plugin::instance()->widgets_manager->register( new $class );
			}
		}
	}
});

add_filter( 'wpseo_breadcrumb_separator', function($sep) {
	return '/';
} );

add_filter('wpseo_breadcrumb_links', function($links) {
    $site_name = get_bloginfo('name');

    if (isset($links[0]['text']) && $links[0]['text'] === 'Trang chủ') {
        $links[0]['text'] = $site_name;
    }

    return $links;
});

add_filter('wpseo_breadcrumb_links', 'custom_woocommerce_breadcrumb_for_product');

function custom_woocommerce_breadcrumb_for_product($links) {
    if (is_singular('product')) {
        global $post;

        // get primary category
        $term = null;

        if (class_exists('WPSEO_Primary_Term')) {
            $primary_term = new WPSEO_Primary_Term('product_cat', $post->ID);
            $term_id = $primary_term->get_primary_term();
            $term = get_term($term_id, 'product_cat');
        }

        // get first term if primary term is not set
        if (!$term || is_wp_error($term)) {
            $terms = get_the_terms($post->ID, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                $term = reset($terms);
            }
        }

        if ($term) {
            $term_ancestors = get_ancestors($term->term_id, 'product_cat');

            $breadcrumbs = [];
            foreach (array_reverse($term_ancestors) as $ancestor_id) {
                $ancestor = get_term($ancestor_id, 'product_cat');
                $breadcrumbs[] = [
                    'text' => $ancestor->name,
                    'url' => get_term_link($ancestor),
                ];
            }

            // Add current term to breadcrumbs
            $breadcrumbs[] = [
                'text' => $term->name,
                'url' => get_term_link($term),
            ];

            // Remove "Cửa hàng" or "Shop" link
            foreach ($links as $i => $link) {
                if ($link['text'] === 'Cửa hàng' || $link['text'] === 'Shop') {
                    unset($links[$i]);
                }
            }

            // Insert breadcrumbs after the first link
            array_splice($links, 1, 0, $breadcrumbs);
        }
    }

    return $links;
}

// Thêm metabox vào trang chỉnh sửa sản phẩm
add_action('add_meta_boxes', function () {
    add_meta_box(
        'custom_product_features',
        'Danh sách tính năng',
        'render_custom_product_features',
        'product',
        'normal',
        'default'
    );
});

// Hiển thị giao diện metabox
function render_custom_product_features($post) {
    $values = get_post_meta($post->ID, '_custom_product_features', true);
    wp_nonce_field('save_custom_product_features', 'custom_product_features_nonce');
    ?>
    <style>
        #custom-repeatable-fields {
            display: flex;
            flex-wrap: wrap;
        }

        #custom-repeatable-fields .repeatable-item {
            position: relative;
            width: 200px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        #custom-repeatable-fields .repeatable-item .custom-image-preview {
            width: 100%;
            height: 100px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            margin-bottom: 4px;
        }

        #custom-repeatable-fields .repeatable-item .remove-item {
            position: absolute;
            top: 5px;
            right: 5px;
            text-decoration: none;
            color: #b32d2e;
        }

        #custom-repeatable-fields .repeatable-item .select-image {
            margin-bottom: 8px;
        }

        #custom-repeatable-fields .repeatable-item input {
            width: 100%
        }
    </style>
    <div id="custom-repeatable-fields">
        <?php if (!empty($values) && is_array($values)) : ?>
            <?php foreach ($values as $index => $item): ?>
                <div class="repeatable-item">
                    <input class="custom-image-url" type="hidden" name="custom_product_features[<?= $index; ?>][image]" value="<?php echo esc_attr($item['image']); ?>" />
                    <img class="custom-image-preview" src="<?= !empty($item['image']) ? esc_url($item['image']) : '/wp-content/themes/astra-child/assets/images/no-image.jpg' ?>" style="" />
                    <button class="button select-image">Chọn ảnh</button>
                    <input type="text" name="custom_product_features[<?php echo $index; ?>][feature]" value="<?php echo esc_attr($item['feature']); ?>" placeholder="Tính năng" />
                    <a href="javascript:void(0)" class="remove-item" rel="nofollow"><span class="dashicons dashicons-remove"></span></a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button class="button" id="add-new-item">Thêm phần mới</button>

    <script>
    jQuery(function($){
        let product_features_index = $('#custom-repeatable-fields .repeatable-item').length || 0;

        $('#add-new-item').on('click', function(e){
            e.preventDefault();
            const html = `
                <div class="repeatable-item">
                    <input class="custom-image-url" type="hidden" name="custom_product_features[${product_features_index}][image]" value="" />
                    <img class="custom-image-preview" src="/wp-content/themes/astra-child/assets/images/no-image.jpg"/>
                    <button class="button select-image">Chọn ảnh</button>
                    <input type="text" name="custom_product_features[${product_features_index}][feature]" value="" placeholder="Tính năng" />
                    <a href="javascript:void(0)" class="remove-item" rel="nofollow"><span class="dashicons dashicons-remove"></span></a>
                </div>`;
            $('#custom-repeatable-fields').append(html);
            product_features_index++;
        });

        $(document).on('click', '.remove-item', function(e){
            e.preventDefault();
            $(this).closest('.repeatable-item').remove();
        });

        $(document).on('click', '.select-image', function(e){
            e.preventDefault();
            const button = $(this);
            const frame = wp.media({
                title: 'Chọn ảnh',
                multiple: false,
                library: { type: 'image' },
                button: { text: 'Chọn ảnh' }
            });

            frame.on('select', function(){
                const attachment = frame.state().get('selection').first().toJSON();
                button.siblings('.custom-image-preview').attr('src', attachment.url);
                button.siblings('.custom-image-url').val(attachment.url);
            });

            frame.open();
        });
    });
    </script>
    <?php
}

// Lưu dữ liệu khi cập nhật sản phẩm
add_action('save_post_product', function($post_id){
    if (!isset($_POST['custom_product_features_nonce']) || !wp_verify_nonce($_POST['custom_product_features_nonce'], 'save_custom_product_features'))
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    if (isset($_POST['custom_product_features']) && is_array($_POST['custom_product_features'])) {
        $data = array_values(array_filter($_POST['custom_product_features'], function($item){
            return !empty($item['image']) || !empty($item['feature']);
        }));
        update_post_meta($post_id, '_custom_product_features', $data);
    } else {
        delete_post_meta($post_id, '_custom_product_features');
    }
});

// Sắp xếp template Elementor theo level của category
add_filter( 'elementor/theme/get_location_templates/template_id', function( $template_id ) {
    if ( is_tax( 'product_cat' ) ) {
        $term = get_queried_object();
        $level = 0;
        $parent = $term->parent;

        while ( $parent != 0 ) {
            $parent_term = get_term( $parent, 'product_cat' );
            $parent = $parent_term->parent;
            $level++;
        }

        if ( $level === 0 ) {
            return 624; // ID template Elementor cho category cấp 1
        } elseif ( $level === 1 ) {
            return 774; // ID template Elementor cho category cấp 2
        } elseif ( $level === 2 ) {
            return 1721; // ID template Elementor cho category cấp 3
        }
    }
    return $template_id;
});

// Ngăn template Product Archive in lại ở location elementor_body_end (wp_footer)
add_action( 'elementor/theme/before_do_elementor_body_end', function( $locations_manager ) {
	if ( ! is_tax( 'product_cat' ) ) {
		return;
	}
	$product_archive_template_ids = [ 624, 774, 1721 ];
	foreach ( $product_archive_template_ids as $template_id ) {
		$locations_manager->skip_doc_in_location( 'elementor_body_end', $template_id );
	}
}, 5 );

// 1) Bắt mọi lần before_section_end rồi lọc theo widget = wc-categories
add_action('elementor/element/before_section_end', function($element, $section_id, $args){
    if ( ! method_exists($element, 'get_name') ) return;

    $name = $element->get_name();

    // 1) Product Categories: thêm 'menu_order' trong Order By
    if ($name === 'wc-categories') {
        if ( ! method_exists($element, 'get_controls') ) return;

        $controls = $element->get_controls();
        if ( empty($controls['orderby']) ) return;

        $opts = isset($controls['orderby']['options']) && is_array($controls['orderby']['options'])
            ? $controls['orderby']['options'] : [];

        if ( ! isset($opts['menu_order']) ) {
            $opts['menu_order'] = __('Thứ tự trong admin', 'astra-child');
            $element->update_control('orderby', ['options' => $opts]);
        }

        return;
    }

    // 2) + 3) Post Content & Text Editor: thêm Heading Typography vào section_style
    if ( ($name === 'theme-post-content' || $name === 'text-editor') && $section_id === 'section_style' ) {
        $element->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'my_heading_typography',
                'label'    => __('Heading Typography (H1–H6)', 'astra-child'),
                'selector' => '{{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3, {{WRAPPER}} h4, {{WRAPPER}} h5, {{WRAPPER}} h6',
            ]
        );
    }
}, 20, 3);

// 2) Đảm bảo khi người dùng chọn 'menu_order' thì query thực sự dùng menu_order
add_action('elementor/frontend/widget/before_render', function($widget){
    if (!($widget instanceof \ElementorPro\Modules\Woocommerce\Widgets\Categories)) return;

    $settings = $widget->get_settings_for_display();
    // Chỉ can thiệp nếu Order By = menu_order
    if (empty($settings['orderby']) || $settings['orderby'] !== 'menu_order') return;

    // Lấy chiều sắp xếp từ control 'order' có sẵn của widget (nếu có)
    $order = '';

    if (!empty($settings['order'])) {
        $maybe = strtoupper($settings['order']);
        if ($maybe === 'ASC' || $maybe === 'DESC') $order = $maybe;
    }

    $cb = function($args, $taxonomies) use ($order){
        if (!empty($taxonomies) && in_array('product_cat', (array)$taxonomies, true)) {
            $args['orderby'] = 'meta_value_num';
            if ($order) $args['order'] = $order;
        }

        return $args;
    };

    $widget->__my_terms_cb = $cb;
    add_filter('get_terms_args', $cb, 10, 2);
}, 8);

add_action('elementor/frontend/widget/after_render', function($widget){
    if (isset($widget->__my_terms_cb) && is_callable($widget->__my_terms_cb)) {
        remove_filter('get_terms_args', $widget->__my_terms_cb, 10);
        unset($widget->__my_terms_cb);
    }
}, 999);

// Cho phép upload thêm các loại file CAD/3D và RAR
add_filter('upload_mimes', function ($mimes) {
    // Nếu không phải admin thì return luôn
    if ( ! current_user_can('manage_options') ) return $mimes;

    // Nén
    $mimes['rar'] = 'application/x-rar-compressed'; // RAR
    $mimes['cbr'] = 'application/x-cbr'; // Comic RAR

    // CAD / 3D
    $mimes['stp'] = 'application/x-step'; // STEP
    $mimes['max'] = 'application/octet-stream'; // 3ds Max
    $mimes['dwg'] = 'application/acad'; // AutoCAD DWG
    $mimes['dxf'] = 'application/dxf'; // AutoCAD DXF

    return $mimes;
});

// Tin vào phần mở rộng nếu Finfo không match (cho 5 định dạng này)
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    if ( ! current_user_can('manage_options') ) return $data;

    $checked = wp_check_filetype($filename, $mimes);
    $allow_exts = ['rar', 'stp', 'max', 'dwg', 'dxf'];

    if (!empty($checked['ext']) && in_array($checked['ext'], $allow_exts, true)) {
        // Ghi đè kết quả để cho phép upload
        $data['ext']  = $checked['ext'];
        $data['type'] = $checked['type'] ?: 'application/octet-stream';
        $data['proper_filename'] = $filename;
    }

    return $data;
}, 10, 4);

/**
 * Woo: Remove product category base, keep hierarchy, ALWAYS trailing slash
 */

/*======================
= 1) REWRITE RULES     =
======================*/
add_action('init', function () {
    if (!taxonomy_exists('product_cat')) return;

    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ]);
    if (is_wp_error($terms) || empty($terms)) return;

    foreach ($terms as $term) {
        // path = parent1/parent2/child (KHÔNG kèm slash ở đây)
        $ancestors = get_ancestors($term->term_id, 'product_cat', 'taxonomy');
        $slugs = [];
        if (!empty($ancestors)) {
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $aid) {
                $a = get_term($aid, 'product_cat');
                if ($a && !is_wp_error($a)) $slugs[] = $a->slug;
            }
        }
        $slugs[] = $term->slug;
        $path   = implode('/', $slugs);
        $path_q = preg_quote($path, '#');

        // Trang danh mục: nhận có/không slash, để ta tự canonicalize bằng redirect
        add_rewrite_rule("^{$path_q}/?$", 'index.php?product_cat=' . $term->slug, 'top');
        // Phân trang
        add_rewrite_rule("^{$path_q}/page/([0-9]{1,})/?$", 'index.php?product_cat=' . $term->slug . '&paged=$matches[1]', 'top');
        // RSS
        add_rewrite_rule("^{$path_q}/feed/(feed|rdf|rss|rss2|atom)/?$", 'index.php?product_cat=' . $term->slug . '&feed=$matches[1]', 'top');
    }
}, 11);

/*==========================================
= 2) REDIRECT CANONICAL & TỪ BASE CŨ      =
==========================================*/
add_action('template_redirect', function () {
    // Chỉ xử lý taxonomy product_cat (kể cả 404 thì không có $term để dùng, nên dựa vào main query là tốt nhất)
    if (!is_tax('product_cat')) return;

    $term = get_queried_object();
    if (!$term || is_wp_error($term)) return;

    // URL canonical BỎ base & CÓ slash cuối (tự giữ prefix ngôn ngữ)
    $canonical = mm_strip_wc_cat_base(get_term_link($term, 'product_cat'));

    // 2.1) Nếu vào từ đường cũ có base -> 301 sang canonical
    $current_uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($current_uri, '/danh-muc-san-pham/') !== false) {
        // giữ query hiện tại
        if (!empty($_GET)) $canonical = add_query_arg($_GET, $canonical);
        wp_redirect($canonical, 301);
        exit;
    }

    // 2.2) Nếu vào URL không có slash cuối -> 301 sang bản có slash
    $path_only = parse_url(home_url(add_query_arg([])), PHP_URL_PATH); // path hiện tại
    // Lấy path của canonical để so sánh đuôi '/'
    $can_path  = wp_parse_url($canonical, PHP_URL_PATH);

    if ($path_only && substr($path_only, -1) !== '/' && substr($can_path, -1) === '/') {
        if (!empty($_GET)) $canonical = add_query_arg($_GET, $canonical);
        wp_redirect($canonical, 301);
        exit;
    }
});

/*==========================================
= 3) AUTO FLUSH KHI DANH MỤC THAY ĐỔI     =
==========================================*/
add_action('created_product_cat', 'mm_flush_rewrite_rules');
add_action('edited_product_cat',  'mm_flush_rewrite_rules');
add_action('delete_product_cat',  'mm_flush_rewrite_rules');
add_action('after_switch_theme',  'mm_flush_rewrite_rules');
function mm_flush_rewrite_rules() {
    add_action('init', function () { flush_rewrite_rules(); }, 20);
}

/*===========================================================
= 4) HÀM CHUNG: LẤY BASE & BỎ BASE + THÊM SLASH CUỐI        =
===========================================================*/
function mm_get_wc_category_base() {
    if (function_exists('wc_get_permalink_structure')) {
        $permalinks = wc_get_permalink_structure();
        $base = !empty($permalinks['category_base']) ? trim($permalinks['category_base'], '/') : 'product-category';
    } else {
        $permalinks = get_option('woocommerce_permalinks', []);
        $base = !empty($permalinks['category_base']) ? trim($permalinks['category_base'], '/') : 'product-category';
    }
    return $base ?: '';
}

function mm_strip_wc_cat_base($url) {
    if (empty($url)) return $url;

    $base   = mm_get_wc_category_base();
    $parts  = wp_parse_url($url);
    $path   = $parts['path'] ?? '';
    $segs   = array_values(array_filter(explode('/', trim($path, '/'))));

    if ($base) {
        $idx = array_search($base, $segs, true);
        if ($idx !== false) unset($segs[$idx]);
    }

    // ✅ Nếu không còn segment nào (ví dụ trang chủ) thì path chỉ là '/'
    if (empty($segs)) {
        $new_path = '/';
    } else {
        $new_path = '/' . implode('/', $segs) . '/';
    }

    if (isset($parts['scheme'], $parts['host'])) {
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $new  = $parts['scheme'] . '://' . $parts['host'] . $port . $new_path;
    } else {
        $new  = home_url($new_path);
    }

    if (!empty($parts['query']))    $new .= '?' . $parts['query'];
    if (!empty($parts['fragment'])) $new .= '#' . $parts['fragment'];

    return $new;
}

/*========================================================
= 5) FILTER LINK & SEO (ƯU TIÊN CAO ĐỂ THẮNG PLUGIN KHÁC)
========================================================*/
add_filter('term_link', function ($url, $term, $taxonomy) {
    if ($taxonomy !== 'product_cat') return $url;
    return mm_strip_wc_cat_base($url);
}, 999, 3); // <- priority cao

add_filter('rank_math/frontend/canonical', function ($canonical) {
    return mm_strip_wc_cat_base($canonical);
}, 999);

add_filter('wpseo_canonical', function ($canonical) {
    return mm_strip_wc_cat_base($canonical);
}, 999);

/*===========================================================
= URL 301 + LOẠI KHỎI SITEMAP (cấu hình tại 1 chỗ)         =
===========================================================*/
/**
 * Danh sách URL cần 301 và loại khỏi sitemap.
 *
 * Key: path nguồn (vd. '/cua-hang/')
 * Value: URL đích — path tương đối ('/') hoặc URL đầy đủ
 *
 * @return array<string, string>
 */
function mm_get_permanent_redirects() {
    return [
        '/cua-hang/' => '/',
    ];
}

function mm_normalize_request_path($path) {
    $path = wp_parse_url($path, PHP_URL_PATH) ?: $path;
    $path = '/' . trim((string) $path, '/') . '/';

    return $path === '//' ? '/' : $path;
}

function mm_resolve_redirect_target($to) {
    if (preg_match('#^https?://#i', $to)) {
        return $to;
    }

    return home_url(mm_normalize_request_path($to));
}

/** Slug page đơn (1 segment) từ danh sách redirect — dùng cho sitemap/noindex. */
function mm_get_redirect_page_slugs() {
    $slugs = [];

    foreach (array_keys(mm_get_permanent_redirects()) as $path) {
        $slug = trim($path, '/');
        if ($slug !== '' && strpos($slug, '/') === false) {
            $slugs[] = $slug;
        }
    }

    return array_values(array_unique($slugs));
}

/** Kiểm tra URL có nằm trong danh sách redirect nguồn không. */
function mm_url_matches_redirect_source($url) {
    if (empty($url)) {
        return false;
    }

    $current = mm_normalize_request_path($url);

    foreach (array_keys(mm_get_permanent_redirects()) as $from) {
        if ($current === mm_normalize_request_path($from)) {
            return true;
        }
    }

    return false;
}

/** Prefix ngôn ngữ loại khỏi sitemap (vd. 'en' → /en/...). */
function mm_get_excluded_sitemap_language_prefixes() {
    return ['en'];
}

function mm_url_has_excluded_language_prefix($url) {
    if (empty($url)) {
        return false;
    }

    $path = mm_normalize_request_path($url);

    foreach (mm_get_excluded_sitemap_language_prefixes() as $lang) {
        $prefix = mm_normalize_request_path('/' . $lang . '/');
        if ($path === $prefix || str_starts_with($path, $prefix)) {
            return true;
        }
    }

    return false;
}

function mm_should_exclude_from_sitemap($url) {
    return mm_url_matches_redirect_source($url) || mm_url_has_excluded_language_prefix($url);
}

/** Gỡ các block <url> có /en/ khỏi XML sitemap (Polylang chèn trực tiếp qua wpseo_sitemap_*_content). */
function mm_filter_excluded_language_sitemap_xml($xml) {
    if (!is_string($xml) || $xml === '') {
        return $xml;
    }

    $home = preg_quote(untrailingslashit(home_url()), '#');

    foreach (mm_get_excluded_sitemap_language_prefixes() as $lang) {
        $lang = preg_quote($lang, '#');
        $xml = preg_replace(
            '#\s*<url>\s*<loc>' . $home . '/' . $lang . '(?:/[^<]*)?</loc>.*?</url>#s',
            '',
            $xml
        );
    }

    return $xml;
}

add_action('init', function () {
    foreach (['post', 'page', 'product'] as $post_type) {
        add_filter("wpseo_sitemap_{$post_type}_content", 'mm_filter_excluded_language_sitemap_xml', 999);
    }
}, 20);

add_action('template_redirect', function () {
    if (is_admin()) {
        return;
    }

    $redirects = mm_get_permanent_redirects();
    if (empty($redirects)) {
        return;
    }

    $current = mm_normalize_request_path($_SERVER['REQUEST_URI'] ?? '/');

    foreach ($redirects as $from => $to) {
        if ($current !== mm_normalize_request_path($from)) {
            continue;
        }

        $target = mm_resolve_redirect_target($to);
        if (!empty($_GET)) {
            $target = add_query_arg(wp_unslash($_GET), $target);
        }

        wp_safe_redirect($target, 301);
        exit;
    }
}, 1);

// Yoast SEO: ép noindex,follow cho archive/page đặc thù đang cần deindex khỏi Google.
add_filter('wpseo_robots', function ($robots) {
    if (is_author()) {
        return 'noindex,follow';
    }

    if (is_category()) {
        return 'noindex,follow';
    }

    if (is_tax(['bo-suu-tap', 'thuong-hieu', 'chat-lieu', 'product_tag'])) {
        return 'noindex,follow';
    }

    if (is_paged()) {
        return 'noindex,follow';
    }

    $noindex_pages = array_merge(
        ['chinh-sach-bao-hanh', 'cua-hang__trashed', 'san-pham-yeu-thich', 'wishlist'],
        mm_get_redirect_page_slugs()
    );

    if (is_page(array_values(array_unique($noindex_pages)))) {
        return 'noindex,follow';
    }

    if (isset($_GET['print']) && $_GET['print'] === 'pdf' && is_page('tuyen-dung')) {
        return 'noindex,follow';
    }

    return $robots;
});

// Yoast SEO: loại author archive khỏi sitemap.
add_filter('wpseo_sitemap_exclude_author', '__return_true');

// Yoast SEO: loại category archive khỏi sitemap.
add_filter('wpseo_sitemap_exclude_taxonomy', function ($excluded, $taxonomy) {
    if ($taxonomy === 'category') {
        return true;
    }

    return $excluded;
}, 10, 2);

// Yoast SEO: loại các page cụ thể đang cần deindex khỏi page-sitemap.xml.
add_filter('wpseo_exclude_from_sitemap_by_post_ids', function ($excluded_post_ids) {
    $slugs = array_values(array_unique(array_merge(
        ['chinh-sach-bao-hanh', 'wishlist'],
        mm_get_redirect_page_slugs()
    )));

    foreach ($slugs as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if ($page instanceof WP_Post) {
            $excluded_post_ids[] = (int) $page->ID;
        }
    }

    return array_values(array_unique($excluded_post_ids));
});

// Yoast SEO: loại URL redirect + ngôn ngữ /en/ khỏi mọi sitemap.
add_filter('wpseo_sitemap_post_type_archive_link', function ($archive_url, $post_type) {
    if ($archive_url && mm_should_exclude_from_sitemap($archive_url)) {
        return false;
    }

    return $archive_url;
}, 10, 2);

add_filter('wpseo_sitemap_post_type_first_links', function ($links, $post_type) {
    return array_values(array_filter($links, function ($link) {
        return empty($link['loc']) || !mm_should_exclude_from_sitemap($link['loc']);
    }));
}, 10, 2);

add_filter('wpseo_sitemap_entry', function ($url, $type, $object) {
    if (!empty($url['loc']) && mm_should_exclude_from_sitemap($url['loc'])) {
        return false;
    }

    return $url;
}, 10, 3);

// Loại post tiếng Anh khỏi query sitemap (Polylang gán ngôn ngữ qua taxonomy 'language').
add_filter('wpseo_posts_where', function ($sql, $post_type) {
    if (!function_exists('pll_is_translated_post_type') || !pll_is_translated_post_type($post_type)) {
        return $sql;
    }

    $excluded_langs = mm_get_excluded_sitemap_language_prefixes();
    if (empty($excluded_langs)) {
        return $sql;
    }

    global $wpdb;
    $tt_ids = [];

    foreach ($excluded_langs as $lang_slug) {
        $term = get_term_by('slug', $lang_slug, 'language');
        if ($term && !is_wp_error($term)) {
            $tt_ids[] = (int) $term->term_taxonomy_id;
        }
    }

    if (empty($tt_ids)) {
        return $sql;
    }

    return $sql . " AND {$wpdb->posts}.ID NOT IN (
        SELECT tr.object_id FROM {$wpdb->term_relationships} AS tr
        WHERE tr.term_taxonomy_id IN (" . implode(',', $tt_ids) . ")
    )";
}, 15, 2);

// Loại term tiếng Anh khỏi taxonomy sitemap.
add_filter('get_terms_args', function ($args) {
    if (empty($GLOBALS['wp_query']->query['sitemap'])) {
        return $args;
    }

    $excluded = mm_get_excluded_sitemap_language_prefixes();
    if (empty($excluded)) {
        return $args;
    }

    if (!empty($args['lang'])) {
        $langs = array_diff(array_map('trim', explode(',', $args['lang'])), $excluded);
    } elseif (function_exists('pll_languages_list')) {
        $langs = array_diff(pll_languages_list(), $excluded);
    } else {
        return $args;
    }

    if (empty($langs)) {
        $args['include'] = [0];
        unset($args['lang']);
    } else {
        $args['lang'] = implode(',', $langs);
    }

    return $args;
}, 25);

add_filter('woocommerce_get_breadcrumb', function ($crumbs) {
    foreach ($crumbs as &$crumb) {
        if (!empty($crumb[1])) {
            $crumb[1] = mm_strip_wc_cat_base($crumb[1]);
        }
    }
    return $crumbs;
}, 999, 1);

// Yoast SEO – sửa URL trong mảng breadcrumb trước khi render
add_filter('wpseo_breadcrumb_links', function ($links) {
    if (!is_array($links)) return $links;
    foreach ($links as &$item) {
        if (!empty($item['url'])) {
            $item['url'] = mm_strip_wc_cat_base($item['url']); // hàm bạn đã có ở trên
        }
    }
    return $links;
}, 999);

// Fallback: nếu theme/Yoast render trực tiếp HTML, thay thế ngay trong output thẻ <a>
add_filter('wpseo_breadcrumb_single_link', function ($link_output, $link) {
    if (!empty($link['url'])) {
        $fixed = mm_strip_wc_cat_base($link['url']);
        if ($fixed !== $link['url']) {
            $link_output = str_replace($link['url'], $fixed, $link_output);
        }
    }
    return $link_output;
}, 999, 2);

function restrict_search_to_posts_and_products( $query ) {
    if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        $query->set( 'post_type', array( 'post', 'product' ) );
    }
}
add_action( 'pre_get_posts', 'restrict_search_to_posts_and_products' );

add_action( 'elementor/query/query_args', function( $query_args ) {
    if ( ! is_search() ) return $query_args;

    $paged = max(
        1,
        get_query_var( 'paged' ),
        get_query_var( 'page' )
    );

    $query_args['paged'] = $paged;

    return $query_args;
}, 10, 1 );
?>
