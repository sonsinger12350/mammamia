<?php

class Astra_Child_Custom_Widget_Footer_Socials extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			'astra_child_custom_widget_footer_socials',
			__('Footer Socials', 'astra-child'),
			array('description' => __('Footer Socials', 'astra-child'))
		);
	}

	public function form($instance)
	{
?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('facebook')); ?>"><?php _e('Facebook:', 'astra-child'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('facebook')); ?>"
				name="<?php echo esc_attr($this->get_field_name('facebook')); ?>" type="text"
				value="<?php echo esc_attr($instance['facebook']); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('instagram')); ?>"><?php _e('Instagram:', 'astra-child'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('instagram')); ?>"
				name="<?php echo esc_attr($this->get_field_name('instagram')); ?>" type="text"
				value="<?php echo esc_attr($instance['instagram']); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('pinterest')); ?>"><?php _e('pinterest:', 'astra-child'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('pinterest')); ?>"
				name="<?php echo esc_attr($this->get_field_name('pinterest')); ?>" type="text"
				value="<?php echo esc_attr($instance['pinterest']); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('linkedin')); ?>"><?php _e('Linkedin:', 'astra-child'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('linkedin')); ?>"
				name="<?php echo esc_attr($this->get_field_name('linkedin')); ?>" type="text"
				value="<?php echo esc_attr($instance['linkedin']); ?>">
		</p>
<?php
	}

	public function update($new_instance, $old_instance)
	{
		return [
			'facebook' => !empty($new_instance['facebook']) ? sanitize_text_field($new_instance['facebook']) : '',
			'instagram' => !empty($new_instance['instagram']) ? sanitize_text_field($new_instance['instagram']) : '',
			'pinterest' => !empty($new_instance['pinterest']) ? sanitize_text_field($new_instance['pinterest']) : '',
			'linkedin' => !empty($new_instance['linkedin']) ? sanitize_text_field($new_instance['linkedin']) : '',
		];
	}

	public function widget($args, $instance)
	{
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$custom_logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		$site_name = get_bloginfo('name');
		echo $args['before_widget'];

		echo '<div class="footer-logo"><img src="' . esc_url($custom_logo_url) . '" alt="' . esc_html($site_name) . '"></div>';

		echo '<div class="footer-socials">';

		foreach ($instance as $key => $value) {
			if (!empty($value)) {
				echo '<a class="item" href="' . esc_url($value) . '">';
				echo '<img src="' . get_stylesheet_directory_uri() . '/assets/icon/' . $key . '.png" alt="' . $key . '">';
				echo '</a>';
			}
		}

		echo '</div>';
		echo $args['after_widget'];
	}
}
