<?php
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
	}

	protected function render() {
		$product_id = get_the_ID();
		$product = wc_get_product( $product_id );
		if (empty($product)) return null;

		
		$custom_data = get_all_custom_field_values($product_id);
		$design_file = get_field('design_file', $product_id);
		$download_design_file = json_decode(get_option('website_config_download_design_file', ''), true);
		$technical_image = get_field('technical_image', $product_id);

		if (!empty($technical_image)) {
			$technical_image = [
				'label' => $technical_image['filename'],
				'value' => $technical_image['url'],
			];
		}

		?>
			<div class="custom-product-content-tab">
				<div class="tabs-title">
					<a class="tab-title active" href="#tab-description">Mô tả sản phẩm</a>
					<a class="tab-title" href="#tab-detail">Thông tin chi tiết</a>
					<a class="tab-title" href="#tab-file">Download</a>
				</div>
				<div class="tabs-content">
					<div class="tab-content active" id="tab-description">
						<div class="description">
							<?= $product->get_description() ?>
						</div>
						<div class="table-content">
							<button type="button" class="btn-table-content">Mục lục <img src="<?= get_stylesheet_directory_uri() . '/assets/icon/angle-down.svg' ?>" alt="angle-down"></button>
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
									<a href="<?= $download == 1 ? $v : 'javascript:void(0)' ?>" class="file download-level-<?= $download ?>" <?= $onclick ?> download>
										<div class="left">
											<img src="<?= get_stylesheet_directory_uri() . '/assets/icon/folder.svg' ?>">
										</div>
										<div class="right">
											<p class="file-title"><?= $k ?></p>
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
}
