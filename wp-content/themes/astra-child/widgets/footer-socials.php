<?php
/**
 * Footer Socials Widget (Minimal: URL + Icon only)
 * Path: wp-content/themes/your-child-theme/inc/footer-socials-widget.php
 */
if ( ! defined('ABSPATH') ) exit;

class Astra_Child_Custom_Widget_Footer_Socials extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'astra_child_custom_widget_footer_socials',
			__('Footer Socials', 'astra-child'),
			array('description' => __('Footer Socials', 'astra-child'))
		);

		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
		add_action('customize_controls_enqueue_scripts', function(){ wp_enqueue_media(); });
	}

	public function enqueue_admin_assets($hook){
		if ($hook !== 'widgets.php' && $hook !== 'customize.php') return;
		wp_enqueue_media();
		wp_enqueue_script(
			'footer-socials',
			get_stylesheet_directory_uri() . '/js/footer-socials.js',
			['jquery'],
			'1.0.0',
			true
		);
	}

	private function get_items($instance){
		$items = isset($instance['items']) && is_array($instance['items']) ? $instance['items'] : [];
		return array_values(array_filter($items, function($it){
			return !empty($it['url']) || !empty($it['icon_id']);
		}));
	}

	public function form($instance){
		$items = $this->get_items($instance);
		$base  = $this->get_field_name('items');
		?>
		<style>
			.footer-socials-repeater .fsr-list {
				padding: 0;
				list-style: none;
			}

			.footer-socials-repeater .fsr-item {
				background: #fff;
				border: 1px solid #ddd;
				padding: 8px 12px;
				margin: 8px 0;
				position: relative;
			}

			.footer-socials-repeater .fsr-item p {
				margin-bottom: 4px !important;
				margin-top: 0px !important;
				display: flex;
				align-items: center;
				gap: 8px;
			}

			.footer-socials-repeater .fsr-media .fsr-preview {
				display: inline-block;
				vertical-align: middle;
				background-color: #062b29;
			}

			.footer-socials-repeater .fsr-choose span {
				display: block;
			}

			.footer-socials-repeater .fsr-delete {
				position: absolute;
				top: 8px;
				right: 8px;
				text-decoration: none !important;
			}

			.footer-socials-repeater .fsr-delete span {
				color: #d63638;
			}
		</style>
		<div class="footer-socials-repeater" data-base="<?php echo esc_attr($base); ?>">
			<ul class="fsr-list">
			<?php foreach($items as $i => $it):
				$url     = isset($it['url']) ? $it['url'] : '';
				$icon_id = isset($it['icon_id']) ? absint($it['icon_id']) : 0;
				$thumb   = $icon_id ? wp_get_attachment_image_url($icon_id, 'thumbnail') : '';
			?>
				<li class="fsr-item">
					<p class="fsr-media">
						<label><?php _e('Icon', 'astra-child'); ?></label>
						<input class="fsr-icon-id" type="hidden"
							name="<?php echo esc_attr($this->get_field_name('items')); ?>[<?php echo esc_attr($i); ?>][icon_id]"
							value="<?php echo esc_attr($icon_id); ?>">
						<img class="fsr-preview" src="<?php echo esc_url($thumb); ?>" style="max-width:48px;max-height:48px;<?php echo $thumb ? '' : 'display:none;'; ?>">
						<button type="button" class="button fsr-choose"><span class="dashicons dashicons-upload"></span></button>
					</p>

					<p>
						<label><?php _e('URL', 'astra-child'); ?></label>
						<input class="widefat"
							name="<?php echo esc_attr($this->get_field_name('items')); ?>[<?php echo esc_attr($i); ?>][url]"
							type="url" value="<?php echo esc_attr($url); ?>">
					</p>

					<a href="javascript:void(0)" class="fsr-delete"><span class="dashicons dashicons-trash"></span></a>
				</li>
			<?php endforeach; ?>
			</ul>

			<script type="text/html" class="fsr-template">
				<li class="fsr-item">
					<p class="fsr-media">
						<label><?php _e('Icon', 'astra-child'); ?></label>
						<input class="fsr-icon-id" type="hidden" name="<?php echo esc_attr($this->get_field_name('items')); ?>[{{i}}][icon_id]" value="">
						<img class="fsr-preview" src="" style="max-width:48px;max-height:48px;display:none;">
						<button type="button" class="button fsr-choose"><span class="dashicons dashicons-upload"></span></button>
					</p>

					<p>
						<label><?php _e('URL', 'astra-child'); ?></label>
						<input class="widefat" name="<?php echo esc_attr($this->get_field_name('items')); ?>[{{i}}][url]" type="url" value="">
					</p>

					<a href="javascript:void(0)" class="fsr-delete"><span class="dashicons dashicons-trash"></span></a>
				</li>
			</script>

			<p><button type="button" class="button button-primary fsr-add"><?php _e('Thêm mục', 'astra-child'); ?></button></p>
		</div>
		<?php
	}

	public function update($new_instance, $old_instance){
		$out = ['items' => []];

		if (!empty($new_instance['items']) && is_array($new_instance['items'])){
			foreach ($new_instance['items'] as $it){
				$url     = !empty($it['url']) ? esc_url_raw($it['url']) : '';
				$icon_id = !empty($it['icon_id']) ? absint($it['icon_id']) : 0;

				if (empty($url) && !$icon_id) continue; // bỏ hàng trống
				$out['items'][] = [
					'url'     => $url,
					'icon_id' => $icon_id,
				];
			}
		}
		return $out;
	}

	public function widget($args, $instance){
		$items = $this->get_items($instance);

		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$custom_logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		$site_name = get_bloginfo('name');
		echo $args['before_widget'];

		echo '<div class="footer-logo"><img src="' . esc_url($custom_logo_url) . '" alt="' . esc_html($site_name) . '"></div>';

		echo '<div class="footer-socials">';

		foreach($items as $it){
			$url     = !empty($it['url']) ? $it['url'] : '';
			$icon_id = !empty($it['icon_id']) ? absint($it['icon_id']) : 0;
			if (!$url) continue;

			echo '<a class="item" href="'. esc_url($url) .'" target="_blank" rel="noopener nofollow">';

			if ($icon_id){
				echo wp_get_attachment_image($icon_id, 'full', false, [
					'class'    => '',
					'alt'      => $it['url'],
					'loading'  => 'lazy',
					'decoding' => 'async',
				]);
			}

			echo '</a>';
		}
	}
}

// Register widget
add_action('widgets_init', function(){
	register_widget('Astra_Child_Custom_Widget_Footer_Socials');
});
