<?php
/**
 * Wishlist page template - Standard Layout
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Wishlist\Templates\Wishlist\View
 * @version 3.0.0
 */

if ( ! defined( 'YITH_WCWL' ) ) {
	exit;
} // Exit if accessed directly
?>
<style>
	.woocommerce-js table.shop_table {
		border: 0px;
	}

	.woocommerce-js table.shop_table thead td {
		border-right: 0px;
		border-top: 0px;
		border-bottom: 1px solid #000 !important;
		color: #000 !important;
		font-weight: 600;
		text-transform: uppercase;
	}

	.woocommerce-js table.shop_table .wishlist-items-wrapper .product-name a {
		color: var(--color-blue);
		text-transform: uppercase;
		font-weight: 400;
	}

	.woocommerce-js a.remove {
		color: #cc0013;
		opacity: 1;
	}

	.woocommerce-js a.remove:before {
		top: 2px;
		border-color: #cc0013;
	}
</style>
<!-- WISHLIST TABLE -->
<table
	class="shop_table cart wishlist_table wishlist_view traditional responsive <?php echo $no_interactions ? 'no-interactions' : ''; ?> <?php echo $enable_drag_n_drop ? 'sortable' : ''; ?> "
	data-pagination="<?php echo esc_attr( $pagination ); ?>" data-per-page="<?php echo esc_attr( $per_page ); ?>" data-page="<?php echo esc_attr( $current_page ); ?>"
	data-id="<?php echo esc_attr( $wishlist_id ); ?>" data-token="<?php echo esc_attr( $wishlist_token ); ?>">

	<?php $column_count = 4; ?>

	<thead>
	<tr>
		<td>Hình ảnh</td>
		<td>Mã sản phẩm</td>
		<td>Tên</td>
		<td>Hủy</td>
	</tr>
	</thead>

	<tbody class="wishlist-items-wrapper">
		<?php
		if ( $wishlist && $wishlist->has_items() ) :
			foreach ( $wishlist_items as $item ) :
				global $product;

				$product = $item->get_product();

				if ( $product && $product->exists() ) :
					?>
					<tr id="yith-wcwl-row-<?php echo esc_attr( $item->get_product_id() ); ?>" data-row-id="<?php echo esc_attr( $item->get_product_id() ); ?>">
						<td class="product-thumbnail">
							<?php
								do_action( 'yith_wcwl_table_before_product_thumbnail', $item, $wishlist );
							?>

							<a href="<?php echo esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $item->get_product_id() ) ) ); ?>">
								<?php echo wp_kses_post( $product->get_image() ); ?>
							</a>

							<?php
								do_action( 'yith_wcwl_table_after_product_thumbnail', $item, $wishlist );
							?>
						</td>
						<td>
							<?php echo wp_kses_post( $product->get_sku() ); ?>
						</td>
						<td class="product-name">
							<?php
								do_action( 'yith_wcwl_table_before_product_name', $item, $wishlist );
							?>

							<a href="<?php echo esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $item->get_product_id() ) ) ); ?>">
								<?php echo wp_kses_post( apply_filters( 'woocommerce_in_cartproduct_obj_title', $product->get_title(), $product ) ); ?>
							</a>

							<?php
								if ( $show_variation && $product->is_type( 'variation' ) ) {
									echo wp_kses_post( wc_get_formatted_variation( $product ) );
								}
							?>

							<?php
								do_action( 'yith_wcwl_table_after_product_name', $item, $wishlist );
							?>
						</td>
						<td class="product-remove">
							<div>
								<a href="<?php echo esc_url( $item->get_remove_url() ); ?>" class="remove remove_from_wishlist" title="<?php echo esc_html( apply_filters( 'yith_wcwl_remove_product_wishlist_message_title', __( 'Xóa sản phẩm khỏi danh sách yêu thích', 'yith-woocommerce-wishlist' ) ) ); ?>">&times;</a>
							</div>
						</td>
					</tr>
					<?php
				endif;
			endforeach;
		else :
			?>
			<tr>
				<td colspan="<?php echo esc_attr( $column_count ); ?>" class="wishlist-empty"><?php echo esc_html( apply_filters( 'yith_wcwl_no_product_to_remove_message', __( 'Không có sản phẩm nào trong danh sách yêu thích', 'yith-woocommerce-wishlist' ), $wishlist ) ); ?></td>
			</tr>
			<?php
		endif;

		if ( ! empty( $page_links ) ) :
			?>
			<tr class="pagination-row wishlist-pagination">
				<td colspan="<?php echo esc_attr( $column_count ); ?>">
					<?php echo wp_kses_post( $page_links ); ?>
				</td>
			</tr>
		<?php endif ?>
	</tbody>

</table>
