<?php

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
		$content = isset($instance['content']) ? $instance['content'] : '';
?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('content')); ?>"><?php _e('Nội dung:', 'astra-child'); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('content')); ?>"
				name="<?php echo esc_attr($this->get_field_name('content')); ?>" rows="5"><?php echo esc_textarea($content); ?></textarea>
		</p>
<?php
	}

	public function update($new_instance, $old_instance)
	{
		return [
			'content' => !empty($new_instance['content']) ? sanitize_textarea_field($new_instance['content']) : '',
		];
	}

	public function widget($args, $instance)
	{
		echo $args['before_widget'];

		echo '<h3 class="widget-title">Trụ sở</h3>';

		if (!empty($instance['content'])) {
			echo '<div class="item">';
			echo wp_kses_post(nl2br(esc_html($instance['content'])));
			echo '</div>';
		}

		echo $args['after_widget'];
	}
}
