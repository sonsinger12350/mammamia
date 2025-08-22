/* Minimal Footer Socials - Admin JS
 * Path: wp-content/themes/your-child-theme/assets/js/min-footer-socials-admin.js
 */
(function($){

	function openMedia($wrap){
	  var frame = wp.media({
		title: 'Chọn icon',
		multiple: false,
		library: { type: 'image' },
		button: { text: 'Chọn' }
	  });
	  frame.on('select', function(){
		var att = frame.state().get('selection').first().toJSON();
		$wrap.find('.fsr-icon-id').val(att.id);
		var thumb = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
		$wrap.find('.fsr-preview').attr('src', thumb).show();
	  });
	  frame.open();
	}
  
	// Đánh số lại tên input sau khi thêm/xoá để tránh lỗ hổng index
	function renumber($root){
	  var base = $root.data('base'); // ví dụ: widget-xxx[2][items]
	  if (!base) return;
  
	  $root.find('.fsr-list .fsr-item').each(function(idx){
		$(this).find('input').each(function(){
		  var name = $(this).attr('name');
		  if (!name) return;
		  var re = new RegExp(base.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\[[0-9]+\\]');
		  $(this).attr('name', name.replace(re, base + '[' + idx + ']'));
		});
	  });
	}
  
	// Delegation để hoạt động cả khi widget reload qua AJAX
	$(document)
  
	.on('click', '.footer-socials-repeater .fsr-add', function(e){
	  e.preventDefault();
	  var $root = $(this).closest('.footer-socials-repeater');
	  var $list = $root.find('.fsr-list');
	  var idx   = $list.children().length;
	  var tpl   = $root.find('.fsr-template').html().replace(/{{i}}/g, idx);
	  $list.append($(tpl));
	  renumber($root);
	})
  
	.on('click', '.footer-socials-repeater .fsr-delete', function(e){
	  e.preventDefault();
	  var $root = $(this).closest('.footer-socials-repeater');
	  $(this).closest('.fsr-item').slideUp(100, function(){
		$(this).remove();
		renumber($root);
	  });
	})
  
	.on('click', '.footer-socials-repeater .fsr-choose', function(e){
	  e.preventDefault();
	  openMedia($(this).closest('.fsr-media'));
	});
  
	// Khi widget được thêm/cập nhật qua AJAX trong Widgets/Customizer
	$(document).on('widget-added widget-updated', function(e, widget){
	  // Không cần init gì thêm vì dùng delegation
	});
  
  })(jQuery);
  