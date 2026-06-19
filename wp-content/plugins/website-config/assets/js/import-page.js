jQuery(document).ready(function($) {
	var progressInterval;
	var currentJobId = null;

	// Enhanced file upload area
	const uploadArea = $('#upload-area');
	const fileInput = $('#excel_file');
	const fileInfo = $('#file-info');
	const importButton = $('#import-submit');

	// Drag and drop functionality
	uploadArea.on('dragover', function(e) {
		e.preventDefault();
		$(this).addClass('dragover');
	});

	uploadArea.on('dragleave', function(e) {
		e.preventDefault();
		$(this).removeClass('dragover');
	});

	uploadArea.on('drop', function(e) {
		e.preventDefault();
		$(this).removeClass('dragover');
		
		const files = e.originalEvent.dataTransfer.files;
		if (files.length > 0) {
			handleFileSelect(files[0]);
		}
	});

	// Click to browse
	uploadArea.on('click', function(e) {
		// Prevent clicking on file input or file info from triggering this
		if ($(e.target).closest('#excel_file, #file-info').length > 0) {
			return;
		}
		e.preventDefault();
		e.stopPropagation();
		fileInput.click();
	});

	fileInput.on('change', function(e) {
		e.stopPropagation();
		if (this.files.length > 0) {
			handleFileSelect(this.files[0]);
		}
	});

	// Prevent file input click from bubbling up
	fileInput.on('click', function(e) {
		e.stopPropagation();
	});

	function handleFileSelect(file) {
		const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
		
		if (!allowedTypes.includes(file.type)) {
			alert('Vui lòng chọn file Excel hợp lệ (.xlsx hoặc .xls)');
			return;
		}

		const fileSize = (file.size / 1024 / 1024).toFixed(2);
		
		$('#file-name').text(file.name);
		$('#file-size').text('(' + fileSize + ' MB)');
		fileInfo.show();
		uploadArea.addClass('has-file');
		importButton.prop('disabled', false);
	}

	// Auto-check for running imports on page load
	$('#check-running-imports').click();

	// Check running imports functionality
	$('#check-running-imports').on('click', function() {
		var $button = $(this);
		var $status = $('#running-imports-status');
		
		$button.prop('disabled', true);
		$button.find('.dashicons').addClass('spin');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'check_running_imports',
				nonce: window.websiteConfigNonce
			},
			success: function(response) {
				if (response.success) {
					var data = response.data;
					var html = '';
					
					if (data.length === 0) {
						html = '<div class="status-message no-imports">' +
							'<span class="dashicons dashicons-yes-alt"></span>' +
							'<p>Không có quá trình import nào đang chạy.</p>' +
							'</div>';
						$('#global-import-progress').hide();
					} else {
						html = '<div class="active-imports">';
						data.forEach(function(job) {
							var percentage = job.total_products > 0 ? Math.round((job.processed_products / job.total_products) * 100) : 0;
							var statusClass = 'status-' + job.status;
							
							html += '<div class="import-job-card ' + statusClass + '">';
							html += '<div class="job-header">';
							html += '<h4>Nhiệm vụ Import: ' + job.job_id.substring(0, 8) + '...</h4>';
							html += '<div class="job-actions">';
							html += '<span class="status-badge ' + job.status + '">' + job.status.toUpperCase() + '</span>';
							if (job.status === 'pending' || job.status === 'processing') {
								html += '<button class="button button-small cancel-import-btn" data-job-id="' + job.job_id + '">Hủy</button>';
							}
							html += '</div>';
							html += '</div>';
							html += '<div class="job-progress">';
							html += '<div class="progress-bar-bg"><div class="progress-bar-fill" style="width: ' + percentage + '%"></div></div>';
							html += '<span class="progress-percentage">' + percentage + '%</span>';
							html += '</div>';
							html += '<div class="job-stats">';
							html += '<span>Tiến độ: ' + job.processed_products + '/' + job.total_products + '</span>';
							html += '<span>Đã tạo: ' + job.created_products + '</span>';
							html += '<span>Đã cập nhật: ' + job.updated_products + '</span>';
							html += '<span>Lỗi: ' + job.failed_products + '</span>';
							html += '</div>';
							html += '</div>';

							// Update global progress if this is the current job
							if (job.status === 'processing') {
								updateGlobalProgress(job);
							}
						});
						html += '</div>';
					}
					
					$status.html(html);
				} else {
					$status.html('<div class="status-message error">' +
						'<span class="dashicons dashicons-warning"></span>' +
						'<p>Lỗi khi kiểm tra import đang chạy: ' + response.data + '</p>' +
						'</div>');
				}
			},
			error: function() {
				$status.html('<div class="status-message error">' +
					'<span class="dashicons dashicons-warning"></span>' +
					'<p>Không thể kiểm tra import đang chạy.</p>' +
					'</div>');
			},
			complete: function() {
				$button.prop('disabled', false);
				$button.find('.dashicons').removeClass('spin');
			}
		});
	});

	function updateGlobalProgress(job) {
		var percentage = job.total_products > 0 ? Math.round((job.processed_products / job.total_products) * 100) : 0;
		
		$('#global-import-progress').show();
		$('.progress-bar-fill').css('width', percentage + '%');
		$('.progress-text').text(percentage + '% Hoàn thành');
		$('#global-processed').text(job.processed_products);
		$('#global-total').text(job.total_products);
		$('#global-created').text(job.created_products);
		$('#global-updated').text(job.updated_products);
		$('#global-failed').text(job.failed_products);
	}

	// Excel import form submission
	$('#excel-import-form').on('submit', function(e) {
		e.preventDefault();

		var formData = new FormData(this);
		formData.append('action', 'import_products_excel');
		formData.append('nonce', window.websiteConfigNonce);

		$('#import-submit').prop('disabled', true).html('<span class="spinner is-active"></span> Đang chuẩn bị Import...');
		$('#import-results').html('');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			timeout: 120000, // 2 minutes timeout for file parsing
			success: function(response) {
				if (response.success) {
					currentJobId = response.data.job_id;
					
					$('#import-results').html(
						'<div class="notice notice-success">' +
						'<h3><span class="dashicons dashicons-yes"></span> Import Đã Bắt Đầu Thành Công!</h3>' +
						'<p>File của bạn đã được xử lý và thêm vào hàng đợi import.</p>' +
						'<ul>' +
						'<li><strong>Job ID:</strong> ' + response.data.job_id + '</li>' +
						'<li><strong>Tổng sản phẩm:</strong> ' + response.data.total_products + '</li>' +
						'</ul>' +
						'<p>Import sẽ bắt đầu xử lý sớm. Bạn có thể theo dõi tiến độ ở trên.</p>' +
						'</div>'
					);

					// Start checking for progress
					startProgressTracking(currentJobId);
					
					// Auto-refresh running imports
					setTimeout(function() {
						$('#check-running-imports').click();
					}, 2000);
				} else {
					$('#import-submit').prop('disabled', false).html('<span class="dashicons dashicons-database-import"></span> Bắt Đầu Import');
					$('#import-results').html(
						'<div class="notice notice-error">' +
						'<h3><span class="dashicons dashicons-warning"></span> Import Thất Bại</h3>' +
						'<p>' + response.data + '</p>' +
						'</div>'
					);
				}
			},
			error: function(xhr, status, error) {
				$('#import-submit').prop('disabled', false).html('<span class="dashicons dashicons-database-import"></span> Bắt Đầu Import');
				
				var errorMessage = 'Không thể bắt đầu quá trình import.';
				if (status === 'timeout') {
					errorMessage = 'Phân tích file quá thời gian. File có thể quá lớn hoặc bị hỏng.';
				} else if (xhr.responseJSON && xhr.responseJSON.data) {
					errorMessage = xhr.responseJSON.data;
				}

				$('#import-results').html(
					'<div class="notice notice-error">' +
					'<h3><span class="dashicons dashicons-warning"></span> Import Thất Bại</h3>' +
					'<p>' + errorMessage + '</p>' +
					'</div>'
				);
			}
		});
	});

	function startProgressTracking(jobId) {
		progressInterval = setInterval(function() {
			checkImportProgress(jobId);
		}, 5000); // Check every 5 seconds
	}

	function checkImportProgress(jobId) {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'check_import_progress',
				job_id: jobId,
				nonce: window.websiteConfigNonce
			},
			success: function(response) {
				if (response.success) {
					var data = response.data;
					
					// Update global progress display
					updateGlobalProgress(data);

					// Check if import is completed or failed
					if (data.status === 'completed') {
						clearInterval(progressInterval);
						$('#import-submit').prop('disabled', false).html('<span class="dashicons dashicons-database-import"></span> Bắt Đầu Import');
						
						$('#import-results').html(
							'<div class="notice notice-success">' +
							'<h3><span class="dashicons dashicons-yes"></span> Import Hoàn Thành Thành Công!</h3>' +
							'<div class="import-summary">' +
							'<div class="summary-item"><span class="count">' + data.created_products + '</span><span class="label">Sản phẩm đã tạo</span></div>' +
							'<div class="summary-item"><span class="count">' + data.updated_products + '</span><span class="label">Sản phẩm đã cập nhật</span></div>' +
							'<div class="summary-item"><span class="count">' + data.failed_products + '</span><span class="label">Lỗi</span></div>' +
							'</div>' +
							(data.failed_products > 0 ? '<p><strong>Lưu ý:</strong> Một số sản phẩm import thất bại. Kiểm tra log để xem chi tiết.</p>' : '') +
							'</div>'
						);
						
						// Refresh the import jobs table and running imports
						setTimeout(function() {
							location.reload();
						}, 2000);
					} else if (data.status === 'failed') {
						clearInterval(progressInterval);
						$('#import-submit').prop('disabled', false).html('<span class="dashicons dashicons-database-import"></span> Bắt Đầu Import');

						$('#import-results').html(
							'<div class="notice notice-error">' +
							'<h3><span class="dashicons dashicons-warning"></span> Import Thất Bại</h3>' +
							'<p>' + (data.error_message || 'Đã xảy ra lỗi không xác định') + '</p>' +
							'</div>'
						);
					}
				}
			},
			error: function() {
				// Continue trying unless too many failures
				console.log('Kiểm tra tiến độ thất bại, đang thử lại...');
			}
		});
	}

	// Cleanup interval on page unload
	$(window).on('beforeunload', function() {
		if (progressInterval) {
			clearInterval(progressInterval);
		}
	});

	// Handle cancel import button clicks
	$(document).on('click', '.cancel-import-btn', function() {
		var jobId = $(this).data('job-id');
		var $button = $(this);
		
		if (!confirm('Bạn có chắc chắn muốn hủy import này?')) {
			return;
		}
		
		$button.prop('disabled', true).text('Đang hủy...');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'cancel_import',
				job_id: jobId,
				nonce: window.websiteConfigNonce
			},
			success: function(response) {
				if (response.success) {
					alert('Import đã được hủy thành công!');
					// Refresh the running imports display
					$('#check-running-imports').click();
				} else {
					alert('Lỗi khi hủy import: ' + response.data);
					$button.prop('disabled', false).text('Hủy');
				}
			},
			error: function() {
				alert('Lỗi khi hủy import. Vui lòng thử lại.');
				$button.prop('disabled', false).text('Hủy');
			}
		});
	});

	// Auto-refresh running imports every 30 seconds
	setInterval(function() {
		$('#check-running-imports').click();
	}, 30000);

	function renderMigrateResults(response, dryRun) {
		const $container = $('#migrate-feature-images-results');
		const results = response.results || {};
		const details = results.details || [];
		let html = '<div class="notice notice-' + (dryRun ? 'info' : 'success') + '"><p>' + response.message + '</p></div>';

		html += '<ul style="margin:12px 0;">';
		html += '<li>Đã quét: <strong>' + (results.scanned_products || 0) + '</strong> sản phẩm</li>';
		html += '<li>Sản phẩm cập nhật: <strong>' + (results.updated_products || 0) + '</strong></li>';
		html += '<li>Ảnh cập nhật: <strong>' + (results.updated_images || 0) + '</strong></li>';
		html += '<li>Ảnh không đổi: <strong>' + (results.unchanged_images || 0) + '</strong></li>';
		html += '<li>Ảnh không resolve: <strong>' + (results.unresolved_images || 0) + '</strong></li>';
		html += '</ul>';

		if (details.length > 0) {
			html += '<table class="widefat striped"><thead><tr>';
			html += '<th>ID</th><th>Sản phẩm</th><th>Trạng thái</th><th>URL cũ</th><th>URL mới</th>';
			html += '</tr></thead><tbody>';

			details.forEach(function(item) {
				html += '<tr>';
				html += '<td>' + item.product_id + '</td>';
				html += '<td>' + item.product_title + '</td>';
				html += '<td>' + item.status + '</td>';
				html += '<td><code style="word-break:break-all;">' + (item.old_url || '-') + '</code></td>';
				html += '<td><code style="word-break:break-all;">' + (item.new_url || '-') + '</code></td>';
				html += '</tr>';
			});

			html += '</tbody></table>';
		}

		$container.html(html);
	}

	function runFeatureImageMigration(dryRun) {
		const $previewBtn = $('#migrate-feature-images-preview');
		const $runBtn = $('#migrate-feature-images-run');
		const $container = $('#migrate-feature-images-results');

		if (!dryRun && !confirm('Bạn có chắc muốn cập nhật đường dẫn ảnh USP trên các sản phẩm?')) {
			return;
		}

		$previewBtn.prop('disabled', true);
		$runBtn.prop('disabled', true);
		$container.html('<p>Đang xử lý...</p>');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'mamma_mia_migrate_feature_images',
				nonce: window.mammaMiaMigrateNonce,
				dry_run: dryRun ? 1 : 0
			},
			success: function(response) {
				if (response.success) {
					renderMigrateResults(response.data, dryRun);
				} else {
					$container.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
				}
			},
			error: function() {
				$container.html('<div class="notice notice-error"><p>Lỗi khi migrate. Vui lòng thử lại.</p></div>');
			},
			complete: function() {
				$previewBtn.prop('disabled', false);
				$runBtn.prop('disabled', false);
			}
		});
	}

	$('#migrate-feature-images-preview').on('click', function() {
		runFeatureImageMigration(true);
	});

	$('#migrate-feature-images-run').on('click', function() {
		runFeatureImageMigration(false);
	});
}); 