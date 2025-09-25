<?php

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

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

		// --- STYLE: TAB TITLE ---
		$this->start_controls_section(
			'style_tab_title_section',
			[
				'label' => __('Kiểu chữ', 'astra-child'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => __('Tiêu đề tab', 'astra-child'),
				'selector' => '{{WRAPPER}} .custom-product-content-tab .tabs-title .tab-title',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'description_typography',
				'label'    => __('Mô tả sản phẩm', 'astra-child'),
				'selector' => '{{WRAPPER}} .custom-product-content-tab .tabs-content #tab-description .description',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'heading_description_typography',
				'label'    => __('Heading mô tả sản phẩm', 'astra-child'),
				'selector' => '{{WRAPPER}} .custom-product-content-tab .tabs-content #tab-description .description h1, {{WRAPPER}} .custom-product-content-tab .tabs-content #tab-description .description h2, {{WRAPPER}} .custom-product-content-tab .tabs-content #tab-description .description h3, {{WRAPPER}} .custom-product-content-tab .tabs-content #tab-description .description h4, {{WRAPPER}} .custom-product-content-tab .tabs-content #tab-description .description h5, {{WRAPPER}} .custom-product-content-tab .tabs-content #tab-description .description h6',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'detail_information_typography',
				'label'    => __('Thông tin chi tiết', 'astra-child'),
				'selector' => '{{WRAPPER}} .custom-product-content-tab .tabs-content #tab-detail table tr td',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'warranty_policy_title_typography',
				'label'    => __('Tiêu đề chính sách bảo hành', 'astra-child'),
				'selector' => '{{WRAPPER}} .custom-product-content-tab .tabs-content #tab-detail .block-title',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'warranty_policy_content_typography',
				'label'    => __('Nội dung chính sách bảo hành', 'astra-child'),
				'selector' => '{{WRAPPER}} .custom-product-content-tab .tabs-content #tab-detail .warranty-policy li',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'download_file_title_typography',
				'label'    => __('Download', 'astra-child'),
				'selector' => '{{WRAPPER}} .custom-product-content-tab .tabs-content #tab-file .list-file .file .file-title',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$product_id = get_the_ID();
		$product = wc_get_product( $product_id );
		if (empty($product)) return null;

		$custom_data = get_all_custom_field_values($product_id);
		$design_file = get_field('design_file', $product_id);
		$design_file_label = get_design_file_fields_label($product_id);
		$download_design_file = json_decode(get_option('website_config_download_design_file', ''), true);
		$technical_image = get_field('technical_image', $product_id);

		if (!empty($technical_image)) {
			$technical_image = [
				'label' => $technical_image['filename'],
				'value' => $technical_image['url'],
			];
		}

		// Generate table of contents from product description
		$description = $product->get_description();
		$table_of_contents = $this->generate_table_of_contents($description);
		
		// Add IDs to headings in description for table of contents linking
		$description_with_ids = $this->add_ids_to_headings($description);

		?>
			<div class="custom-product-content-tab">
				<div class="tabs-title">
					<a class="tab-title active" href="#tab-description">Mô tả sản phẩm</a>
					<a class="tab-title" href="#tab-detail">Thông tin chi tiết</a>
					<a class="tab-title" href="#tab-file">Download</a>
				</div>
				<div class="tabs-content">
					<div class="tab-content active" id="tab-description">
						<?php if (!empty($table_of_contents)): ?>
							<div class="table-content mb-4">
								<button type="button" class="btn-table-content">Mục lục <img src="<?= get_stylesheet_directory_uri() . '/assets/icon/angle-down.svg' ?>" alt="angle-down"></button>
								<div class="table-content-list">
									<ul>
										<?php foreach ($table_of_contents as $item): ?>
											<li class="toc-item toc-level-<?= $item['level'] ?>">
												<a href="#<?= $item['id'] ?>" class="toc-link"><?= $item['text'] ?></a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						<?php endif; ?>
						<div class="description">
							<?= $description_with_ids ?>
						</div>
					</div>
					<div class="tab-content" id="tab-detail">
						<div class="left">
							<table>
								<tbody>
									<?php foreach ($custom_data['custom_field'] as $k => $v): ?>
										<tr>
											<td><b><?php echo esc_html( $v['label'] ); ?>:</b></td>
											<td class="attribute-<?= $k ?>"><?php echo esc_html( is_array($v['value']) ? implode(', ', $v['value']) : $v['value'] ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<?php if (!empty($custom_data['custom_taxonomies']['chinh-sach-bao-hanh']['value'])): ?>
								<div class="block-content">
									<p class="block-title"><?php echo esc_html( $custom_data['custom_taxonomies']['chinh-sach-bao-hanh']['label'] ); ?></p>
									<ul class="warranty-policy">
										<?php foreach ($custom_data['custom_taxonomies']['chinh-sach-bao-hanh']['value'] as $v): ?>
											<li><span><i class="fa fa-angle-right" aria-hidden="true"></i></span> <?php echo esc_html( $v['label'] ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						</div>
						
						<?php if (!empty($technical_image)): ?>
							<div class="technical-image">
								<img src="<?= $technical_image['value'] ?>" alt="<?= $technical_image['label'] ?>">
							</div>
						<?php endif; ?>
					</div>
					<div class="tab-content" id="tab-file">
						<?php if (!empty($design_file)): ?>
							<div class="list-file">
								<?php
									foreach ($design_file as $k => $v):
										if(empty($v)) continue;

										$download = !empty($download_design_file[$k]) ? $download_design_file[$k] : 1;
										$onclick = '';

										if ($download == 2) {
											$onclick = 'onclick="showCustomModal(\'#modal-login\')"';

											if (is_user_logged_in()) {
												$onclick = '';
												$download = 1;
											}
										}
										elseif ($download == 3) {
											$onclick = 'onclick="showCustomModal(\'#modal-contact-download\')"';
										}
								?>
									<a href="<?= $download == 1 ? $v : 'javascript:void(0)' ?>" class="file download-level-<?= $download ?>" <?= $onclick ?> download rel="nofollow">
										<div class="left">
											<img src="<?= get_stylesheet_directory_uri() . '/assets/icon/folder.svg' ?>">
										</div>
										<div class="right">
											<p class="file-title"><?= $design_file_label[$k] ?></p>
											<img src="<?= get_stylesheet_directory_uri() . '/assets/icon/download.svg' ?>" class="icon-download">
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php
	}

	private function generate_table_of_contents($html) {
		$toc = [];
		$id_counter = 0;

		// Use regex to find headings
		preg_match_all('/<h([1-6])([^>]*)>(.*?)<\/h[1-6]>/', $html, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$tag = $match[1];
			$attributes = $match[2];
			$text = strip_tags($match[3]); // Remove any HTML tags from the heading text
			$id = 'toc-' . $id_counter++;

			// Determine level (h1 = 0, h2 = 1, etc.)
			$level = $tag - 1;
			if ($level < 0) $level = 0;

			$toc[] = [
				'level' => $level,
				'text' => $text,
				'id' => $id,
			];
		}

		return $toc;
	}

	private function add_ids_to_headings($html) {
		$id_counter = 0;
		
		// Use regex to find and replace heading tags with IDs
		$html_with_ids = preg_replace_callback(
			'/<h([1-6])([^>]*)>(.*?)<\/h[1-6]>/',
			function($matches) use (&$id_counter) {
				$tag = $matches[1];
				$attributes = $matches[2];
				$content = $matches[3];
				$id = 'toc-' . $id_counter++;
				
				// Check if ID already exists
				if (strpos($attributes, 'id=') !== false) {
					return $matches[0]; // Keep existing heading as is
				}
				
				return "<h{$tag}{$attributes} id=\"{$id}\">{$content}</h{$tag}>";
			},
			$html
		);
		
		return $html_with_ids;
	}
}
