<?php
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
	}

	protected function render() {
		if (!is_tax('product_cat')) return false;
		$currentCat = get_queried_object();
		$selectField = [
			'brand' => [
				'title' => 'Thương hiệu',
				'options' => getAllBrandByCat($currentCat),
			],
			'collection' => [
				'title' => 'Bộ sưu tập',
				'options' => getAllCustomFieldValueByCat($currentCat, 'collection'),
			],
			'price_type' => [
				'title' => 'Giá',
				'options' => getAllCustomFieldValueByCat($currentCat, 'price_type'),
			],
			'set_up' => [
				'title' => 'Lắp đặt',
				'options' => getAllCustomFieldValueByCat($currentCat, 'set_up'),
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
						<select id="' . $field . '" name="' . $field . '">
							<option value="">' . esc_html('Tất cả') . '</option>
					';
							foreach ($data['options'] as $key => $value) {
								if ($field == 'brand') $content .= '<option value="' . esc_attr($key) . '">' . esc_html($value) . '</option>';
								else $content .= '<option value="' . esc_attr($value) . '">' . esc_html($value) . '</option>';
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
