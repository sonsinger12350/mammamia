<?php
class Custom_Elementor_Widget_Product_Image_Slide extends \Elementor\Widget_Base {

	public function get_name() {
		return 'custom_widget_product_image_slide';
	}

	public function get_title() {
		return __('Custom Product Image Slide', 'astra-child');
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

		$gallery_image_ids = $product->get_gallery_image_ids();
		if (empty($gallery_image_ids)) return null;

		$gallery_image_urls = [];
		foreach ($gallery_image_ids as $image_id) {
			$gallery_image_urls[] = wp_get_attachment_url($image_id);
		}

		$content = '<div class="custom-product-image-slide"><div class="list owl-carousel owl-theme">';

		foreach ($gallery_image_urls as $k => $v) {
			$content .= '
				<div class="item">
					<img src="' . $v . '" alt="' . $k . '">
				</div>
			';
		}

		$content .= '</div></div>';

		echo $content;
	}
}
