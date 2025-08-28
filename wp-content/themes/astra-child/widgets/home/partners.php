<?php

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

class Custom_Elementor_Widget_Partners extends \Elementor\Widget_Base {

	public function get_name() {
		return 'custom_widget_partners';
	}

	public function get_title() {
		return __('Custom Widget Partners', 'astra-child');
	}

	public function get_icon() {
		return 'eicon-code';
	}

	public function get_categories() {
		return ['widget-custom'];
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
			'image',
			[
				'label' => __( 'Hình ảnh đối tác', 'astra-child' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => '',
				],
				'media_type' => 'image',
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
						'image' => [
							'url' => '',
						],
						'link' => "",
						'top' => 10,
						'left' => 20,
					],
				],
				'title_field' => '{{{ image.url }}}',
			]
		);

		$this->end_controls_section();

		// --- STYLE: Partners ---
		$this->start_controls_section(
			'style_partners_section',
			[
				'label' => __('Đối tác', 'astra-child'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => __('Kiểu chữ', 'astra-child'),
				'selector' => '{{WRAPPER}} .partners-widget .item span',
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
				$image_url = !empty($item['image']['url']) ? $item['image']['url'] : '';
				$image_alt = !empty($item['image']['alt']) ? $item['image']['alt'] : __('Đối tác', 'astra-child');
				
				$output .= '<a href="' . $item['link']['url'] . '" target="_blank" class="item" style="
				top: ' . $item['top']['size'] . '%; 
				left: ' . $item['left']['size'] . '%;
				background-image: url(' . get_stylesheet_directory_uri() . '/assets/background/partner.png);
				">';
				
				if (!empty($image_url)) {
					$output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" class="partner-image">';
				}
				
				$output .= '</a>';
			}

			$output .= '</div>';
		}

		echo $output;
	}
}
