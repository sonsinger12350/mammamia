<?php

use Elementor\Icons_Manager;

class Astra_Child_Custom_Widget_Footer_Contact_Info extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			'astra_child_custom_widget_footer_contact_info',
			__('Footer Contact Info', 'astra-child'),
			array('description' => __('Footer Contact Info', 'astra-child'))
		);
	}

	public function form($instance)
	{
?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('address')); ?>"><?php _e('Địa chỉ:', 'astra-child'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('address')); ?>"
				name="<?php echo esc_attr($this->get_field_name('address')); ?>" type="text"
				value="<?php echo esc_attr($instance['address']); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('hotline')); ?>"><?php _e('Hotline:', 'astra-child'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('hotline')); ?>"
				name="<?php echo esc_attr($this->get_field_name('hotline')); ?>" type="text"
				value="<?php echo esc_attr($instance['hotline']); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('email')); ?>"><?php _e('Email:', 'astra-child'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('email')); ?>"
				name="<?php echo esc_attr($this->get_field_name('email')); ?>" type="text"
				value="<?php echo esc_attr($instance['email']); ?>">
		</p>
<?php
	}

	public function update($new_instance, $old_instance)
	{
		return [
			'address' => !empty($new_instance['address']) ? sanitize_text_field($new_instance['address']) : '',
			'hotline' => !empty($new_instance['hotline']) ? sanitize_text_field($new_instance['hotline']) : '',
			'email' => !empty($new_instance['email']) ? sanitize_text_field($new_instance['email']) : '',
		];
	}

	public function widget($args, $instance)
	{
		echo $args['before_widget'];

		echo '<h3 class="widget-title">Trụ sở</h3>';

		if (!empty($instance['address'])) {
			echo '<div class="item">';
			echo '<span>' . esc_html($instance['address']) . '</span>';
			echo '</div>';
		}

		if (!empty($instance['hotline'])) {
			echo '<div class="item">';
			echo '<span>Hotline: <a href="tel:' . esc_html($instance['hotline']) . '">' . esc_html($instance['hotline']) . '</a></span>';
			echo '</div>';
		}

		if (!empty($instance['email'])) {
			echo '<div class="item">';
			echo '<span>Email: <a href="mailto:' . esc_html($instance['email']) . '">' . esc_html($instance['email']) . '</a></span>';
			echo '</div>';
		}

		echo $args['after_widget'];
	}
}
