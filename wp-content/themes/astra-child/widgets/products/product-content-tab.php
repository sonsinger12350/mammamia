<?php
class Custom_Elementor_Widget_Product_Content_Tab extends \Elementor\Widget_Base {

	public function get_name() {
		return 'custom_widget_product_content_tab';
	}

	public function get_title() {
		return __('Custom Product Content Tab', 'astra-child');
	}

	public function get_icon() {
		return 'eicon-code';
	}

	public function get_categories() {
		return ['widget-custom'];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __('Content', 'astra-child'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$product_id = get_the_ID();
		$product = wc_get_product( $product_id );
		if (empty($product)) return null;

		$image_id = $product->get_image_id(); // ID của ảnh đại diện
		$image_url = $image_id ? wp_get_attachment_url( $image_id ) : null;

		$product_color = get_product_color($product);
		$attributes = [];
		$custom_field = get_all_acf_fields($product_id);
		// echo '<pre>';print_r($custom_field);exit;
		$show_field = ['size', 'water_capacity', 'material', 'faucet_included'];
		if (!empty($product_color)) $attributes['color'] = ['label' => 'Màu sắc', 'value' => $product_color[0]['name']];

		foreach ($custom_field as $k => $v) {
			if (in_array($k, $show_field)) {
				$attributes[$k] = $v;
			}
		}

		$template_file = get_stylesheet_directory() . '/template/product-content-tab.php';

		if ( file_exists( $template_file ) ) {
			include $template_file;
		}
	}
}
