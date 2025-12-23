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

		// Enqueue scripts for widget editor
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
	}

	public function enqueue_admin_scripts($hook)
	{
		if ('widgets.php' !== $hook && 'customize.php' !== $hook) {
			return;
		}

		wp_enqueue_script('jquery');
		wp_enqueue_media();
		wp_enqueue_editor();
		
		// Enqueue WordPress editor scripts
		wp_enqueue_script('editor');
		wp_enqueue_script('quicktags');
		wp_enqueue_script('wp-util');
		
		// Add inline script to initialize editor using WordPress method
		wp_add_inline_script('editor', '
			jQuery(document).ready(function($) {
				// Debounce function to limit how often a function can be called
				function debounce(func, wait) {
					var timeout;
					return function() {
						var context = this;
						var args = arguments;
						clearTimeout(timeout);
						timeout = setTimeout(function() {
							func.apply(context, args);
						}, wait);
					};
				}
				
				function initWidgetEditor() {
					$(".widget").each(function() {
						var widget = $(this);
						var textarea = widget.find("textarea.widget-editor-content");
						if (textarea.length) {
							var editorId = textarea.attr("id");
							if (editorId) {
								// Wait for wp.oldEditor to be available
								if (typeof wp !== "undefined" && wp.oldEditor) {
									// Remove existing editor if any
									if (typeof tinyMCE !== "undefined" && tinyMCE.get(editorId)) {
										wp.oldEditor.remove(editorId);
									}
									
									// Initialize editor using WordPress method
									wp.oldEditor.initialize(editorId, {
										tinymce: {
											wpautop: true,
											height: 300,
											toolbar1: "bold,italic,bullist,numlist,link,unlink",
											toolbar2: ""
										},
										quicktags: true,
										mediaButtons: false
									});
									
									// Wait for TinyMCE to be initialized, then attach event handlers
									setTimeout(function() {
										if (typeof tinyMCE !== "undefined" && tinyMCE.get(editorId)) {
											var editor = tinyMCE.get(editorId);
											
											// Function to trigger change event on textarea
											function triggerTextareaChange() {
												editor.save();
												textarea.trigger("change");
											}
											
											// Debounced version - wait 500ms after last change
											var debouncedTriggerChange = debounce(triggerTextareaChange, 500);
											
											// Track when dialog opens
											editor.on("OpenWindow", function(e) {
												// Prevent widget collapse when dialog is open
												$(document).on("click.tinymce-dialog-prevent", function(e) {
													if ($(e.target).closest(".mce-window, .mce-container, .mce-dialog").length > 0) {
														e.stopImmediatePropagation();
														return false;
													}
												});
												
												// Also prevent on mousedown (fired before click)
												$(document).on("mousedown.tinymce-dialog-prevent", function(e) {
													if ($(e.target).closest(".mce-window, .mce-container, .mce-dialog").length > 0) {
														e.stopImmediatePropagation();
														return false;
													}
												});
											});
											
											editor.on("CloseWindow", function(e) {
												// Remove the prevention when dialog closes
												setTimeout(function() {
													$(document).off("click.tinymce-dialog-prevent mousedown.tinymce-dialog-prevent");
													// Trigger change immediately when dialog closes (link might have been added)
													triggerTextareaChange();
												}, 100);
											});
											
											// Trigger change event when content changes (with debounce)
											editor.on("keyup NodeChange", function() {
												debouncedTriggerChange();
											});
											
											// For change event, use debounce but shorter delay
											editor.on("change", function() {
												debouncedTriggerChange();
											});
											
											// Also trigger on paste (with debounce)
											editor.on("paste", function() {
												setTimeout(function() {
													debouncedTriggerChange();
												}, 100);
											});
										}
									}, 500);
								} else if (typeof tinymce !== "undefined") {
									// Fallback: initialize TinyMCE directly
									if (!tinymce.get(editorId)) {
										tinymce.init({
											selector: "#" + editorId,
											height: 300,
											menubar: false,
											plugins: "lists,link,wordpress",
											toolbar: "bold italic | bullist numlist | link unlink",
											wpautop: true,
											setup: function(editor) {
												// Function to trigger change event on textarea
												function triggerTextareaChange() {
													editor.save();
													textarea.trigger("change");
												}
												
												// Debounced version - wait 500ms after last change
												var debouncedTriggerChange = debounce(triggerTextareaChange, 500);
												
												// Trigger change event when content changes (with debounce)
												editor.on("keyup NodeChange", function() {
													debouncedTriggerChange();
												});
												
												// For change event, use debounce
												editor.on("change", function() {
													debouncedTriggerChange();
												});
												
												// Also trigger on paste (with debounce)
												editor.on("paste", function() {
													setTimeout(function() {
														debouncedTriggerChange();
													}, 100);
												});
												
												// Prevent widget collapse when TinyMCE dialog is open
												editor.on("OpenWindow", function(e) {
													// Prevent widget collapse when dialog is open
													$(document).on("click.tinymce-dialog-prevent", function(e) {
														if ($(e.target).closest(".mce-window, .mce-container, .mce-dialog").length > 0) {
															e.stopImmediatePropagation();
															return false;
														}
													});
													
													// Also prevent on mousedown (fired before click)
													$(document).on("mousedown.tinymce-dialog-prevent", function(e) {
														if ($(e.target).closest(".mce-window, .mce-container, .mce-dialog").length > 0) {
															e.stopImmediatePropagation();
															return false;
														}
													});
												});
												
												editor.on("CloseWindow", function(e) {
													// Remove the prevention when dialog closes
													setTimeout(function() {
														$(document).off("click.tinymce-dialog-prevent mousedown.tinymce-dialog-prevent");
														// Trigger change when dialog closes (link might have been added)
														triggerTextareaChange();
													}, 100);
												});
											}
										});
									}
								}
							}
						}
					});
				}

				// Additional protection: prevent widget collapse for any TinyMCE dialog elements
				// This catches cases where the editor events might not fire
				$(document).on("mousedown.tinymce-fallback click.tinymce-fallback", ".mce-window, .mce-container, .mce-dialog, .mce-window *, .mce-container *, .mce-dialog *", function(e) {
					e.stopPropagation();
					e.stopImmediatePropagation();
					return true;
				});
				
				// Also trigger change event for QuickTags (text mode) and direct textarea changes (with debounce)
				$(document).on("keyup input", "textarea.widget-editor-content", function() {
					var textarea = $(this);
					// Get or create debounced function for this textarea
					var debouncedTrigger = textarea.data("debounced-change");
					if (!debouncedTrigger) {
						debouncedTrigger = debounce(function() {
							textarea.trigger("change");
						}, 500);
						textarea.data("debounced-change", debouncedTrigger);
					}
					debouncedTrigger();
				});
				
				// For change event on textarea (when switching modes), trigger immediately
				$(document).on("change", "textarea.widget-editor-content", function() {
					$(this).trigger("change");
				});

				// Initialize on page load with delay to ensure scripts are loaded
				setTimeout(initWidgetEditor, 1500);

				// Re-initialize when widget is added/updated
				$(document).on("widget-added widget-updated", function(e, widget) {
					setTimeout(function() {
						initWidgetEditor();
					}, 1500);
				});
			});
		');
	}

	public function form($instance)
	{
		$content = isset($instance['content']) ? $instance['content'] : '';
		$field_id = $this->get_field_id('content');
		$field_name = $this->get_field_name('content');
		
		// Format content for editor
		if (user_can_richedit()) {
			add_filter('the_editor_content', 'format_for_editor', 10, 2);
			$default_editor = 'tinymce';
		} else {
			$default_editor = 'html';
		}
		
		$text = apply_filters('the_editor_content', $content, $default_editor);
		
		if (user_can_richedit()) {
			remove_filter('the_editor_content', 'format_for_editor');
		}
		
		$escaped_text = preg_replace('#</textarea#i', '&lt;/textarea', $text);
?>
		<p>
			<label for="<?php echo esc_attr($field_id); ?>"><?php _e('Nội dung:', 'astra-child'); ?></label>
			<textarea id="<?php echo esc_attr($field_id); ?>" 
				name="<?php echo esc_attr($field_name); ?>" 
				class="widefat widget-editor-content" 
				rows="10"><?php echo $escaped_text; ?></textarea>
		</p>
<?php
	}

	public function update($new_instance, $old_instance)
	{
		return [
			'content' => !empty($new_instance['content']) ? wp_kses_post($new_instance['content']) : '',
		];
	}

	public function widget($args, $instance)
	{
		echo $args['before_widget'];

		echo '<h3 class="widget-title">Trụ sở</h3>';

		if (!empty($instance['content'])) {
			echo '<div class="item">';
			echo wp_kses_post(wpautop(wp_unslash($instance['content'])));
			echo '</div>';
		}

		echo $args['after_widget'];
	}
}
