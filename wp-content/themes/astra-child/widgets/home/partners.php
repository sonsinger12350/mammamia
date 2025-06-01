<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Custom_Elementor_Widget_Partners extends \Elementor\Widget_Base {

    public function get_name() {
        return 'custom_widget_partners';
    }

    public function get_title() {
        return __('Custom Widget Partners', 'astra-child');
    }

    public function get_icon() {
        return 'eicon-code'; // Icon cho widget
    }

    public function get_categories() {
        return ['widget-custom']; // Danh mục widget
    }

    protected function register_controls() {

		$this->start_controls_section(
			'section_items',
			[
				'label' => __( 'Danh sách đối tác', 'astra-child' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'title',
			[
				'label' => __( 'Tên đối tác', 'astra-child' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Tên đối tác', 'astra-child' ),
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => __( 'Link', 'astra-child' ),
				'type' => Controls_Manager::URL,
				'default' => [
					'url' => '',
				],
			]
		);

		$repeater->add_control(
			'top',
			[
				'label' => __( 'Vị trí dọc', 'astra-child' ),
				'type' => Controls_Manager::SLIDER,
				'units' => ['%'],
				'default' => [
					'size' => 0,
					'units' => '%',
				],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
			]
		);

		$repeater->add_control(
			'left',
			[
				'label' => __( 'Vị trí ngang', 'astra-child' ),
				'type' => Controls_Manager::SLIDER,
				'units' => ['%'],
				'default' => [
					'size' => 0,
					'units' => '%',
				],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
			]
		);

		$this->add_control(
			'items',
			[
				'label' => __( 'Danh sách', 'astra-child' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'title' => __( 'Đối tác 1', 'astra-child' ),
						'link' => "",
						'top' => 10,
						'left' => 20,
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();
	}

    protected function render() {
		$settings = $this->get_settings_for_display();
		$output = '';

		if ( ! empty( $settings['items'] ) ) {
			$output = '<div class="partners-widget">';

			foreach ( $settings['items'] as $item ) {
				$output .= '<a href="' . $item['link']['url'] . '" target="_blank" class="item" style="
				top: ' . $item['top']['size'] . '%; 
				left: ' . $item['left']['size'] . '%;
				background-image: url(' . get_stylesheet_directory_uri() . '/assets/background/partner.png);
				">
				<span>' . $item['title'] . '</span></a>';
			}

			$output .= '</div>';
		}

		echo $output;
    }
}
