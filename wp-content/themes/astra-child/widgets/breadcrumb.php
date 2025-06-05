<?php
class Custom_Elementor_Widget_Breadcrumb extends \Elementor\Widget_Base {

	public function get_name() {
		return 'custom_widget_breadcrumb';
	}

	public function get_title() {
		return __('Custom Widget Breadcrumb', 'astra-child');
	}

	public function get_icon() {
		return 'eicon-code'; // widget icon
	}

	public function get_categories() {
		return ['widget-custom']; // widget category
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
		echo yoast_breadcrumb('<nav class="breadcrumb-widget">','</nav>');
	}
}
