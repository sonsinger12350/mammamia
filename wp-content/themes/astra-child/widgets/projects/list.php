<?php
class Custom_Elementor_Widget_Projects_List extends \Elementor\Widget_Base {

	public function get_name() {
		return 'custom_widget_projects_list';
	}

	public function get_title() {
		return __('Custom Widget Projects List', 'astra-child');
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
		echo '<div class="projects-list-widget">';
		echo get_projects_list_html();
		echo '</div>';
	}
}
