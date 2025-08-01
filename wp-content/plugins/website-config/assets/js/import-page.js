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
			alert('Please select a valid Excel file (.xlsx or .xls)');
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
							'<p>No import processes are currently running.</p>' +
							'</div>';
						$('#global-import-progress').hide();
					} else {
						html = '<div class="active-imports">';
						data.forEach(function(job) {
							var percentage = job.total_products > 0 ? Math.round((job.processed_products / job.total_products) * 100) : 0;
							var statusClass = 'status-' + job.status;
							
							html += '<div class="import-job-card ' + statusClass + '">';
							html += '<div class="job-header">';
							html += '<h4>Import Job: ' + job.job_id.substring(0, 8) + '...</h4>';
							html += '<span class="status-badge ' + job.status + '">' + job.status.toUpperCase() + '</span>';
							html += '</div>';
							html += '<div class="job-progress">';
							html += '<div class="progress-bar-bg"><div class="progress-bar-fill" style="width: ' + percentage + '%"></div></div>';
							html += '<span class="progress-percentage">' + percentage + '%</span>';
							html += '</div>';
							html += '<div class="job-stats">';
							html += '<span>Progress: ' + job.processed_products + '/' + job.total_products + '</span>';
							html += '<span>Created: ' + job.created_products + '</span>';
							html += '<span>Updated: ' + job.updated_products + '</span>';
							html += '<span>Failed: ' + job.failed_products + '</span>';
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
						'<p>Error checking running imports: ' + response.data + '</p>' +
						'</div>');
				}
			},
			error: function() {
				$status.html('<div class="status-message error">' +
					'<span class="dashicons dashicons-warning"></span>' +
					'<p>Failed to check running imports.</p>' +
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
		$('.progress-text').text(percentage + '% Complete');
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

		$('#import-submit').prop('disabled', true).html('<span class="spinner is-active"></span> Preparing Import...');
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
						'<h3><span class="dashicons dashicons-yes"></span> Import Started Successfully!</h3>' +
						'<p>Your file has been processed and added to the import queue.</p>' +
						'<ul>' +
						'<li><strong>Job ID:</strong> ' + response.data.job_id + '</li>' +
						'<li><strong>Total Products:</strong> ' + response.data.total_products + '</li>' +
						'</ul>' +
						'<p>The import will begin processing shortly. You can monitor progress above.</p>' +
						'</div>'
					);

					// Start checking for progress
					startProgressTracking(currentJobId);
					
					// Auto-refresh running imports
					setTimeout(function() {
						$('#check-running-imports').click();
					}, 2000);
				} else {
					$('#import-submit').prop('disabled', false).html('<span class="dashicons dashicons-database-import"></span> Start Import');
					$('#import-results').html(
						'<div class="notice notice-error">' +
						'<h3><span class="dashicons dashicons-warning"></span> Import Failed</h3>' +
						'<p>' + response.data + '</p>' +
						'</div>'
					);
				}
			},
			error: function(xhr, status, error) {
				$('#import-submit').prop('disabled', false).html('<span class="dashicons dashicons-database-import"></span> Start Import');
				
				var errorMessage = 'Failed to start import process.';
				if (status === 'timeout') {
					errorMessage = 'File parsing timed out. The file may be too large or corrupted.';
				} else if (xhr.responseJSON && xhr.responseJSON.data) {
					errorMessage = xhr.responseJSON.data;
				}

				$('#import-results').html(
					'<div class="notice notice-error">' +
					'<h3><span class="dashicons dashicons-warning"></span> Import Failed</h3>' +
					'<p>' + errorMessage + '</p>' +
					'</div>'
				);
			}
		});
	});

	function startProgressTracking(jobId) {
		progressInterval = setInterval(function() {
			checkImportProgress(jobId);
		}, 3000); // Check every 3 seconds
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
						$('#import-submit').prop('disabled', false).html('<span class="dashicons dashicons-database-import"></span> Start Import');
						
						$('#import-results').html(
							'<div class="notice notice-success">' +
							'<h3><span class="dashicons dashicons-yes"></span> Import Completed Successfully!</h3>' +
							'<div class="import-summary">' +
							'<div class="summary-item"><span class="count">' + data.created_products + '</span><span class="label">Products Created</span></div>' +
							'<div class="summary-item"><span class="count">' + data.updated_products + '</span><span class="label">Products Updated</span></div>' +
							'<div class="summary-item"><span class="count">' + data.failed_products + '</span><span class="label">Errors</span></div>' +
							'</div>' +
							(data.failed_products > 0 ? '<p><strong>Note:</strong> Some products failed to import. Check the logs for details.</p>' : '') +
							'</div>'
						);
						
						// Refresh the import jobs table and running imports
						setTimeout(function() {
							location.reload();
						}, 2000);
					} else if (data.status === 'failed') {
						clearInterval(progressInterval);
						$('#import-submit').prop('disabled', false).html('<span class="dashicons dashicons-database-import"></span> Start Import');

						$('#import-results').html(
							'<div class="notice notice-error">' +
							'<h3><span class="dashicons dashicons-warning"></span> Import Failed</h3>' +
							'<p>' + (data.error_message || 'Unknown error occurred') + '</p>' +
							'</div>'
						);
					}
				}
			},
			error: function() {
				// Continue trying unless too many failures
				console.log('Progress check failed, retrying...');
			}
		});
	}

	// Cleanup interval on page unload
	$(window).on('beforeunload', function() {
		if (progressInterval) {
			clearInterval(progressInterval);
		}
	});

	// Auto-refresh running imports every 30 seconds
	setInterval(function() {
		$('#check-running-imports').click();
	}, 30000);
}); 