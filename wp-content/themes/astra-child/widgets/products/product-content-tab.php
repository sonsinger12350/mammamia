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

		$product_color = get_product_color($product);
		$attributes = [];
		$custom_field = get_all_acf_fields($product_id);

		$show_field = ['size', 'water_capacity', 'material', 'faucet_included'];
		if (!empty($product_color)) $attributes['color'] = ['label' => 'Màu sắc', 'value' => $product_color[0]['name']];

		foreach ($custom_field as $k => $v) {
			if (in_array($k, $show_field)) {
				$attributes[$k] = $v;
			}
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
									<?php foreach ($attributes as $k => $v): ?>
										<tr>
											<td><b><?php echo esc_html( $v['label'] ); ?>:</b></td>
											<td class="attribute-<?= $k ?>"><?php echo esc_html( is_array($v['value']) ? implode(', ', $v['value']) : $v['value'] ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<div class="block-content">
								<p class="block-title"><?php echo esc_html( $custom_field['warranty_policy']['label'] ); ?></p>
								<?php if (!empty($custom_field['warranty_policy']['value'])): ?>
									<ul class="warranty-policy">
										<?php foreach ($custom_field['warranty_policy']['value'] as $v): ?>
											<li><span><i class="fa fa-angle-right" aria-hidden="true"></i></span> <?php echo esc_html( $v ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
						
						<?php if (!empty($custom_field['technical_image']['value'])): ?>
							<div class="technical-image">
								<img src="<?= $custom_field['technical_image']['value']['url'] ?>" alt="<?= $custom_field['technical_image']['value']['name'] ?>">
							</div>
						<?php endif; ?>
					</div>
					<div class="tab-content" id="tab-file">
						<?php if (!empty($custom_field['design_file']['value'])): ?>
							<div class="list-file">
								<?php 
									foreach ($custom_field['design_file']['value'] as $k => $v):
									$download = !empty($custom_field['download_design_file']['value'][$k]) ? $custom_field['download_design_file']['value'][$k] : 1;
									$onclick = '';

									if ($download == 2) {
										$onclick = 'onclick="showNotification(\'Đăng nhập để tải file '.strtoupper($k).'\', \'error\')"';

										if (is_user_logged_in()) {
											$onclick = '';
											$download = 1;
										}
									} elseif ($download == 3) {
										$onclick = 'onclick=""';
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
