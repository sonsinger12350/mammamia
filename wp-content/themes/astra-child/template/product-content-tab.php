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
			<?php if (!empty($custom_field['technical_image']['value'])): ?>
				<div class="technical-image">
					<img src="<?= $custom_field['technical_image']['value']['url'] ?>" alt="<?= $custom_field['technical_image']['value']['name'] ?>">
				</div>
			<?php endif; ?>
		</div>
		<div class="tab-content" id="tab-file">
			<?php if (!empty($custom_field['design_file']['value'])): ?>
				<div class="list-file">
					<?php foreach ($custom_field['design_file']['value'] as $k => $v): ?>
						<a href="<?= $v ?>" class="file" download>
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
