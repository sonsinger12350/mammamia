<?php

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class Custom_Elementor_Widget_Filter_Product extends \Elementor\Widget_Base {

	public function get_name() {
		return 'custom_widget_filter_product';
	}

	public function get_title() {
		return __('Filter Product', 'astra-child');
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
				'label' => __('Tiêu đề', 'astra-child'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => __('Kiểu chữ', 'astra-child'),
				'selector' => '{{WRAPPER}} .custom-filter-product .list-item .item label',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		if (!is_tax('product_cat')) return false;
		$currentCat = get_queried_object();

		$selectField = [
			'brand' => [
				'title' => 'Thương hiệu',
				'options' => getAllBrandByCat($currentCat),
			],
			'bo-suu-tap' => [
				'title' => 'Bộ sưu tập',
				'options' => getTaxonomyTermsByCat($currentCat, 'bo-suu-tap'),
			],
			'price_type' => [
				'title' => 'Giá',
				'options' => getTaxonomyTermsByCat($currentCat, 'price_type'),
			],
			'set_up' => [
				'title' => 'Lắp đặt',
				'options' => getOptionsCustomFieldByCat($currentCat, 'set_up'),
			],
		];

		$content = '
			<form class="custom-filter-product">
				<input type="hidden" name="category" value="' . $currentCat->term_id . '">
				<div class="list-item">
			';
			foreach ($selectField as $field => $data) {
				$content .= '
					<div class="item">
						<label for="' . $field . '">' . esc_html($data['title']) . '</label>
						<select id="' . $field . '" name="' . $field . '" class="filter-select" data-field="' . $field . '">
							<option value="">' . esc_html('Tất cả') . '</option>
					';
					if (!empty($data['options'])) {
						foreach ($data['options'] as $key => $value) {
							if (in_array($field, ['brand', 'bo-suu-tap', 'set_up'])) {
								$content .= '<option value="' . esc_attr($key) . '">' . esc_html($value) . '</option>';
							}
							else {
								$content .= '<option value="' . esc_attr($value) . '">' . esc_html($value) . '</option>';
							}
						}
					}

				$content .= '
						</select>
					</div>
				';
			}

		$content .= '
				</div>
				<div class="filter-footer">
					<button type="submit" class="button-primary">Xem</button>
				</div>
			</form>
		';

		echo $content;
	}
}
