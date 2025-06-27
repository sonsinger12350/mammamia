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
				<table class="custom-field">
					<tbody>
						<?php foreach ($attributes as $k => $v): ?>
							<tr>
								<td><b><?php echo esc_html( $v['label'] ); ?>:</b></td>
								<td class="attribute-<?= $k ?>"><?php echo esc_html( is_array($v['value']) ? implode(', ', $v['value']) : $v['value'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<hr>
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
			<?php if (!empty($product_color)): ?>
				<hr>
				<div class="block-content">
					<p class="block-title"><?php echo esc_html( 'Chọn màu khác' ); ?></p>
					<div class="list-color">
						<?php foreach ($product_color as $k => $v): ?>
							<span data-color="<?= $v['slug'] ?>" data-name="<?php echo esc_attr( $v['name'] ); ?>" class="<?= $k == 0 ? 'active' : '' ?>" style="background-color: <?= $v['color'] ?>"></span>
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
