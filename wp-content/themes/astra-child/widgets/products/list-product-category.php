<?php

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

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

		// --- STYLE: PRODUCT TITLE ---
		$this->start_controls_section(
			'style_title_section',
			[
				'label' => __('Tiêu đề sản phẩm', 'astra-child'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => __('Kiểu chữ', 'astra-child'),
				'selector' => '{{WRAPPER}} .product-list-by-category .item .list-product .product .product-title',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		if (!is_tax('product_cat')) return false;

		$currentCat = get_queried_object();
		$child_categories = get_terms([
			'taxonomy'   => 'product_cat',
			'parent'     => $currentCat->term_id,
			'hide_empty' => true,
		]);

		$content = '<div class="product-list-by-category">';

		if (!empty($child_categories) && !is_wp_error($child_categories)) {
			foreach ($child_categories as $category) {
				$content .= '
					<div class="item">
						<div class="item-header">
							<h3 class="item-title">' . esc_html($category->name) . '</h3>
						</div>
						<hr>
						<div class="item-body">
							'. getProductListByConditions(['category' => $category]) .'
						</div>
					</div>
				';
			}
		}
		else {
			$category = $category = get_term($currentCat->term_id, 'product_cat');
			$content .= '
				<div class="item">
					<div class="item-header">
						<h3 class="item-title">' . esc_html($category->name) . '</h3>
					</div>
					<hr>
					<div class="item-body">
						'. getProductListByConditions(['category' => $category]) .'
					</div>
				</div>
			';
		}

		$content .= '</div>';
		echo $content;
	}
}
