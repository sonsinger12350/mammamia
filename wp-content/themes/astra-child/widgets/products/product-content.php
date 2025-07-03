<?php
class Custom_Elementor_Widget_Product_Content extends \Elementor\Widget_Base {

	public function get_name() {
		return 'custom_widget_product_content';
	}

	public function get_title() {
		return __('Custom Product Content', 'astra-child');
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

		$image_id = $product->get_image_id(); // ID của ảnh đại diện
		$image_url = $image_id ? wp_get_attachment_url( $image_id ) : null;

		$product_color = get_product_color($product);
		$attributes = [];
		$custom_field = get_all_acf_fields($product_id);
		$show_field = ['size', 'water_capacity', 'material', 'faucet_included'];
		$brands = get_the_terms($product->get_id(), 'product_brand');
		$brand_names = (!empty($brands) && !is_wp_error($brands)) ? wp_list_pluck($brands, 'name') : [];
		$features = get_post_meta($product_id, '_custom_product_features', true);
		$sku = $product->get_sku();

		if (!empty($product_color)) {
			if (!empty($product_color[0]['sku'])) $sku = $product_color[0]['sku'];

			$attributes['color'] = ['label' => 'Màu sắc', 'value' => $product_color[0]['name']];
		}

		foreach ($custom_field as $k => $v) {
			if (in_array($k, $show_field)) {
				$attributes[$k] = $v;
			}
		}

		?>
			<div class="custom-product-content">
				<div class="body">
					<div class="product-slide-image">
						<?php if (!empty($product_color)): ?>
							<div class="owl-carousel owl-theme">
								<?php foreach ($product_color as $v): ?>
									<div class="item">
										<img src="<?= $v['image'] ?>" alt="<?php echo esc_attr( $v['name'] ); ?>">
									</div>
								<?php endforeach; ?>
							</div>
							<div class="slide-dots">
								<div class="list">
									<?php foreach ($product_color as $k => $v): ?>
										<div class="item <?= $k == 0 ? 'active' : '' ?>" data-color="<?= $v['slug'] ?>" data-slide="<?= $k ?>">
											<img src="<?= $v['image'] ?>" alt="<?php echo esc_attr( $v['name'] ); ?>">
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php else: ?>
							<div class="image-thumbnail">
								<img src="<?= $image_url ?>" alt="<?= $product->get_name() ?>">
							</div>
						<?php endif ?>
					</div>
					<div class="product-content">
						<div class="block-content">
							<h1 class="product-name"><?php echo esc_html( $product->get_name() ); ?></h1>
							<p class="product-brand">Thương hiệu: <?= implode(',', $brand_names) ?></p>
							<p class="product-sku">Mã SP: <?= $sku ?></p>
							<p class="short-description"><?= $product->get_short_description(); ?></p>
						</div>
						<?php if (!empty($features)): ?>
							<hr>
							<div class="block-content">
								<div class="product-features">
									<?php foreach ($features as $v): if (empty($v['feature'])) continue; ?>
										<div class="item">
											<img src="<?= !empty($v['image']) ? $v['image'] : '/wp-content/themes/astra-child/assets/images/no-image.jpg' ?>" alt="<?php echo esc_attr( $v['feature'] ); ?>">
											<p><?php echo esc_html( $v['feature'] ); ?></p>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if (!empty($product_color)): ?>
							<hr>
							<div class="block-content">
								<p class="block-title"><?php echo esc_html( 'Chọn màu khác' ); ?></p>
								<div class="list-color">
									<?php foreach ($product_color as $k => $v): ?>
										<span data-color="<?= $v['slug'] ?>" data-name="<?php echo esc_attr( $v['name'] ); ?>" data-sku="<?= $v['sku'] ?>" class="<?= $k == 0 ? 'active' : '' ?>" style="background-color: <?= $v['color'] ?>"></span>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<hr>
						<div class="block-content add-to-wishlist">
							<div class="d-flex">
								<?= do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>
								<p class="block-title">Thêm vào danh sách yêu thích</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
	}
}
