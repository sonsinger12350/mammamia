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

		$attributes = get_all_attributes_with_images($product);

		$default_color = !empty($attributes) ? key($attributes) : '';
		$default_size = '';
		$default_images = [];

		if (!empty($default_color)) {
			$default_size = !empty($attributes[$default_color]['size']) ? key($attributes[$default_color]['size']) : '';

			if (!empty($default_size)) {
				$default_images = $attributes[$default_color]['size'][$default_size]['images'];
			}
			else {
				$default_images = $attributes[$default_color]['images'];
			}
		}

		$brands = get_the_terms($product->get_id(), 'product_brand');
		$brand_names = (!empty($brands) && !is_wp_error($brands)) ? wp_list_pluck($brands, 'name') : [];
		$features = get_post_meta($product_id, '_custom_product_features', true);
		$sku = $product->get_sku();

		?>
			<div class="custom-product-content">
				<script>
					let product_attributes = <?= json_encode(!empty($attributes) ? $attributes : []) ?>;
				</script>
				<div class="body">
					<div class="product-slide-image">
						<?php if (!empty($default_images)): ?>
							<div class="owl-carousel owl-theme">
								<?php foreach ($default_images as $k => $v): ?>
									<div class="item">
										<img src="<?= $v ?>" alt="gallery-image-<?= $k ?>">
									</div>
								<?php endforeach; ?>
							</div>
							<div class="slide-dots">
								<div class="list">
									<?php foreach ($default_images as $k => $v): ?>
										<div class="item <?= $k == 0 ? 'active' : '' ?>" data-slide="<?= $k ?>">
											<img src="<?= $v ?>" alt="gallery-image-<?= $k ?>">
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
						<?php if (!empty($attributes)): ?>
							<hr>
							<div class="block-content">
								<p class="block-title"><?php echo esc_html( 'Chọn màu khác' ); ?></p>
								<div class="list-color">
									<?php foreach ($attributes as $k => $v): ?>
										<span data-color="<?= $v['slug'] ?>" class="<?= $v['slug'] == $default_color ? 'active' : '' ?>" style="background-color: <?= $v['color'] ?>" title="<?= $v['name'] ?>"></span>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if (!empty($default_size)): ?>
							<hr>
							<div class="block-content">
								<p class="block-title"><?php echo esc_html( 'Kích thước' ); ?></p>
								<div class="list-size">
									<?php foreach ($attributes[$default_color]['size'] as $k => $v): ?>
										<div class="item">
											<input type="radio" name="size" id="size-<?= $k ?>" value="<?= $k ?>" <?= $k == $default_size ? 'checked' : '' ?>>
											<label for="size-<?= $k ?>"><?php echo esc_attr( $v['name'] ); ?></label>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<hr>
						<form class="variations_form cart" method="post" enctype='multipart/form-data'
							data-product_id="<?= get_the_ID(); ?>">
							
							<!-- Chọn thuộc tính màu sắc, kích cỡ,... (WooCommerce sẽ render tự động) -->

							<!-- Nút thêm vào giỏ hàng và yêu thích -->
							<div class="block-content add-to-wishlist">
								<div class="d-flex">
									<?= do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>
									<p class="block-title">Thêm vào danh sách yêu thích</p>
								</div>
							</div>
						</form>

					</div>
				</div>
			</div>
		<?php
	}
}
