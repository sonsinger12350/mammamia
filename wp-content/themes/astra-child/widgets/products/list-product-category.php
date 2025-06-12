<?php
class Custom_Elementor_Widget_Product_List_By_Category extends \Elementor\Widget_Base {

	public function get_name() {
		return 'custom_widget_product_list_by_category';
	}

	public function get_title() {
		return __('Product List By Category', 'astra-child');
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
		if (!is_tax('product_cat')) return false;
		$current_category = get_queried_object(); // Trả về đối tượng của taxonomy hiện tại
		$current_cat_id = $current_category->term_id;

		// Lấy danh sách category con
		$child_categories = get_terms([
			'taxonomy'   => 'product_cat',
			'parent'     => $current_cat_id,
			'hide_empty' => true,
		]);

		$content = '<div class="product-list-by-category">';

		if (!empty($child_categories) && !is_wp_error($child_categories)) {
			foreach ($child_categories as $category) {
				$link = get_term_link($category);
				$content .= '
					<div class="item">
						<div class="item-header">
							<h3 class="item-title">' . esc_html($category->name) . '</h3>
							<p class="item-description">' . esc_html($category->description) . '</p>
						</div>
						<hr>
						<div class="item-body">
							'. get_product_list_by_category($category->term_id) .'
						</div>
					</div>
				';
			}
			
		}
		else {
			$content .= '<p align="center">' . esc_html__('Nội dung đang cập nhật.', 'astra-child') . '</p>';
		}

		$content .= '</div>';
		echo $content;
	}
}
