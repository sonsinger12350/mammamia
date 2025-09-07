<?php

/**
 * Plugin Name: MammaMia
 * Plugin URI: 
 * Description: A simple plugin to for MammaMia
 * Version: 1.0.0
 * Author: Lucas
 * License: GPL v2 or later
 * Text Domain: mamma-mia
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('WEBSITE_CONFIG_VERSION', '1.0.1');
define('WEBSITE_CONFIG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WEBSITE_CONFIG_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include column mappings
require_once WEBSITE_CONFIG_PLUGIN_PATH . 'column-mappings.php';
require_once WEBSITE_CONFIG_PLUGIN_PATH . '/vendor/autoload.php';


class WebsiteConfig
{
	public $taxonomies_mappings = [
		'tags' => 'product_tag',
		'brand' => 'product_brand',
	];

	public $custom_taxonomies_mappings = [
		'chat-lieu' => 'chat-lieu',
		'bo-suu-tap' => 'bo-suu-tap',
		'chinh-sach-bao-hanh' => 'chinh-sach-bao-hanh',
	];

	public $design_file_fields = [
		'catalogue',
		'brochure',
		'instructions',
		'user_guide',
		'3d',
		'spec',
		'autocad',
	];

	public $acf_fields = [
		'size',
		'weight',
		'wels_rating',
		'wels_registration_number',
		'watermark_license',
		'operating_temperature',
		'operating_pressure',
		'technology',
		'discharge_mode',
		'accessories',
		'water_tank',
		'water_capacity',
		'operating_water_pressure',
		'power_source',
		'origin',
		'warranty_policy',
		'price_type',
	];

	public function __construct()
	{
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'init_settings'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

		// Add AJAX handlers for Excel import
		add_action('wp_ajax_import_products_excel', array($this, 'handle_excel_import'));
		add_action('wp_ajax_nopriv_import_products_excel', array($this, 'handle_excel_import'));

		// Add AJAX handlers for import progress check
		add_action('wp_ajax_check_import_progress', array($this, 'check_import_progress'));
		add_action('wp_ajax_nopriv_check_import_progress', array($this, 'check_import_progress'));

		// Add AJAX handlers for checking running imports
		add_action('wp_ajax_check_running_imports', array($this, 'check_running_imports'));
		add_action('wp_ajax_nopriv_check_running_imports', array($this, 'check_running_imports'));

		// Add AJAX handlers for cancel import
		add_action('wp_ajax_cancel_import', array($this, 'cancel_import'));
		add_action('wp_ajax_nopriv_cancel_import', array($this, 'cancel_import'));

		// Hook for plugin activation
		register_activation_hook(__FILE__, array($this, 'create_import_tables'));

		// Add cron job hooks
		add_action('mamma_mia_process_import_queue', array($this, 'process_import_queue'));
		add_action('init', array($this, 'schedule_cron_jobs'));
	}

	/**
	 * Create database tables for import queue
	 */
	public function create_import_tables()
	{
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Import jobs table
		$table_name = $wpdb->prefix . 'mamma_mia_import_jobs';
		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			job_id varchar(50) NOT NULL,
			status enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
			total_products int(11) DEFAULT 0,
			processed_products int(11) DEFAULT 0,
			created_products int(11) DEFAULT 0,
			updated_products int(11) DEFAULT 0,
			failed_products int(11) DEFAULT 0,
			update_existing tinyint(1) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			error_message text,
			PRIMARY KEY (id),
			UNIQUE KEY job_id (job_id),
			KEY status (status)
		) $charset_collate;";

		// Import queue table
		$queue_table = $wpdb->prefix . 'mamma_mia_import_queue';
		$sql2 = "CREATE TABLE $queue_table (
			id int(11) NOT NULL AUTO_INCREMENT,
			job_id varchar(50) NOT NULL,
			product_type enum('main','variant') DEFAULT 'main',
			product_data longtext NOT NULL,
			status enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
			error_message text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY job_id (job_id),
			KEY status (status),
			KEY product_type (product_type)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		dbDelta($sql2);
	}

	/**
	 * Schedule cron jobs
	 */
	public function schedule_cron_jobs()
	{
		if (!wp_next_scheduled('mamma_mia_process_import_queue')) {
			wp_schedule_event(time(), 'every_minute', 'mamma_mia_process_import_queue');
		}

		// Add custom cron interval
		add_filter('cron_schedules', array($this, 'add_cron_intervals'));
	}

	/**
	 * Add custom cron intervals
	 */
	public function add_cron_intervals($schedules)
	{
		$schedules['mamma_mia_every_30_seconds'] = array(
			'interval' => 30,
			'display'  => esc_html__('Every 30 Seconds'),
		);
		return $schedules;
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu()
	{
		add_menu_page(
			'Mamma Mia',
			'Mamma Mia',
			'manage_options',
			'mamma-mia',
			array($this, 'admin_page'),
			'dashicons-admin-generic',
			30
		);

		add_submenu_page(
			'mamma-mia',
			'Import Products',
			'Import Products',
			'manage_options',
			'mamma-mia-import',
			array($this, 'import_page')
		);
	}

	/**
	 * Initialize settings
	 */
	public function init_settings()
	{
		register_setting('website_config_options', 'website_config_phone');
		register_setting('website_config_options', 'website_config_email');
		register_setting('website_config_options', 'website_config_zalo');
		register_setting('website_config_options', 'website_config_download_design_file');
	}

	/**
	 * Admin page content
	 */
	public function admin_page()
	{
		// Save settings if form is submitted
		if (isset($_POST['submit'])) {
			update_option('website_config_phone', sanitize_text_field($_POST['website_config_phone']));
			update_option('website_config_email', sanitize_email($_POST['website_config_email']));
			update_option('website_config_zalo', sanitize_text_field($_POST['website_config_zalo']));
			update_option('website_config_download_design_file', json_encode($_POST['website_config_download_design_file']));
			echo '<div class="notice notice-success"><p>Cấu hình đã được lưu thành công!</p></div>';
		}

		$download_options = [
			1 => "Không cần đăng nhập",
			2 => "Bắt buộc đăng nhập",
			3 => "Liên hệ trực tiếp",
		];

		$download_file_type = array_map(function ($item) {
			return [
				'label' => mamma_mia_get_column_key($item, false),
				'value' => $item,
			];
		}, $this->design_file_fields);

		// Get current values
		$phone = get_option('website_config_phone', '');
		$email = get_option('website_config_email', '');
		$zalo = get_option('website_config_zalo', '');
		$download_design_file = json_decode(get_option('website_config_download_design_file', ''), true);
		?>
			<div class="wrap">
				<h1>Cấu hình website</h1>
				<p>Quản lý cấu hình website.</p>

				<form method="post" action="">
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="website_config_phone">Số điện thoại</label>
							</th>
							<td>
								<input type="text"
									id="website_config_phone"
									name="website_config_phone"
									value="<?php echo esc_attr($phone); ?>"
									class="regular-text"
									placeholder="+1 (555) 123-4567" />
								<p class="description">Nhập số điện thoại của bạn.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="website_config_email">Email</label>
							</th>
							<td>
								<input type="email"
									id="website_config_email"
									name="website_config_email"
									value="<?php echo esc_attr($email); ?>"
									class="regular-text"
									placeholder="info@yourwebsite.com" />
								<p class="description">Nhập email của bạn.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="website_config_zalo">Liên kết Zalo</label>
							</th>
							<td>
								<input type="text"
									id="website_config_zalo"
									name="website_config_zalo"
									value="<?php echo esc_attr($zalo); ?>"
									class="regular-text"
									placeholder="https://zalo.me/0123456789" />
								<p class="description">Nhập liên kết Zalo của bạn.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="website_config_download_design_file">Cấu hình tải file thiết kế</label>
							</th>
							<td>
								<?php foreach ($download_file_type as $v): ?>
									<div class="form-group" style="margin-bottom: 10px;">
										<label style="min-width: 150px; display: inline-block;" for="website_config_download_design_file"><?php echo esc_html($v['label']); ?></label>
										<select name="website_config_download_design_file[<?php echo esc_attr($v['value']); ?>]" id="website_config_download_design_file">
											<?php foreach ($download_options as $key => $value): ?>
												<option value="<?php echo esc_attr($key); ?>" <?php !empty($download_design_file[$v['value']]) && selected($download_design_file[$v['value']], $key); ?>><?php echo esc_html($value); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								<?php endforeach; ?>
							</td>
						</tr>
					</table>

					<?php submit_button('Save Settings'); ?>
				</form>
			</div>
		<?php
	}

	/**
	 * Import page content
	 */
	public function import_page()
	{
		// Hardcode import settings (no longer show in admin)
		?>
			<div class="wrap">
				<h1>Import Products from Excel</h1>
				<p>Upload an Excel file to import products with variants. The system will process your file in the background.</p>

				<!-- Import Process Status with Enhanced UI -->
				<div class="card import-status-card" style="max-width: 100%; margin-bottom: 20px;">
					<h2><span class="dashicons dashicons-admin-tools"></span> Import Process Status</h2>
					<div id="running-imports-container">
						<div class="import-status-header">
							<button type="button" id="check-running-imports" class="button button-secondary">
								<span class="dashicons dashicons-update"></span> Check Active Imports
							</button>
							<div class="status-indicators">
								<span class="indicator-dot pending" title="Pending"></span>
								<span class="indicator-dot processing" title="Processing"></span>
								<span class="indicator-dot completed" title="Completed"></span>
								<span class="indicator-dot failed" title="Failed"></span>
							</div>
						</div>
						<div id="running-imports-status" class="import-status-display"></div>
						<div id="global-import-progress" style="display: none;">
							<div class="global-progress-container">
								<h3>Current Import Progress</h3>
								<div class="progress-wrapper">
									<div class="progress-bar-bg">
										<div class="progress-bar-fill" style="width: 0%"></div>
									</div>
									<div class="progress-text">0% Complete</div>
								</div>
								<div class="progress-details">
									<div class="detail-item">
										<span class="label">Processed:</span>
										<span id="global-processed">0</span> / <span id="global-total">0</span>
									</div>
									<div class="detail-item">
										<span class="label">Created:</span>
										<span id="global-created">0</span>
									</div>
									<div class="detail-item">
										<span class="label">Updated:</span>
										<span id="global-updated">0</span>
									</div>
									<div class="detail-item">
										<span class="label">Failed:</span>
										<span id="global-failed">0</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Upload Excel File Section with Enhanced UI -->
				<div class="card upload-card" style="max-width: 800px; margin-bottom: 20px;">
					<h2><span class="dashicons dashicons-upload"></span> Upload Excel File</h2>
					<form id="excel-import-form" enctype="multipart/form-data">
						<div class="upload-area" id="upload-area">
							<div class="upload-content">
								<span class="dashicons dashicons-cloud-upload upload-icon"></span>
								<h3>Drag & Drop your Excel file here</h3>
								<p>or <span class="browse-link">browse to select a file</span></p>
								<input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls" required style="display: none;" />
								<div class="file-info" id="file-info" style="display: none;">
									<span class="dashicons dashicons-media-spreadsheet"></span>
									<span id="file-name"></span>
									<span id="file-size"></span>
								</div>
							</div>
						</div>
						
						<div class="import-options">
							<label class="checkbox-wrapper">
								<input type="checkbox" id="update_existing" name="update_existing" value="1" />
								<span class="checkmark"></span>
								<span class="option-text">Update existing products (instead of creating new ones)</span>
							</label>
						</div>

						<div class="submit-wrapper">
							<button type="submit" class="button button-primary button-hero" id="import-submit" disabled>
								<span class="dashicons dashicons-database-import"></span> Start Import
							</button>
						</div>
					</form>

					<div id="import-results" class="import-results"></div>
				</div>

				<!-- Import Jobs History Section -->
				<div class="card" style="max-width: 100%; margin-bottom: 20px;">
					<h2><span class="dashicons dashicons-list-view"></span> Import History</h2>
					<?php $this->display_import_jobs_status(); ?>
				</div>
			</div>

			<script>
				// Pass nonce to JavaScript
				window.websiteConfigNonce = '<?php echo wp_create_nonce('excel_import_nonce'); ?>';
			</script>
		<?php
	}

	/**
	 * Handle Excel import via AJAX with queue system
	 */
	public function handle_excel_import()
	{
		// Verify nonce
		if (!wp_verify_nonce($_POST['nonce'], 'excel_import_nonce')) {
			wp_die('Security check failed');
		}

		// Check if file was uploaded
		if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
			wp_send_json_error('No file uploaded or upload error occurred');
		}

		$file = $_FILES['excel_file'];
		$update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] === '1';

		// Check file type
		$allowed_types = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
		if (!in_array($file['type'], $allowed_types)) {
			wp_send_json_error('Invalid file type. Please upload an Excel file.');
		}

		// Include PhpSpreadsheet library
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Try to parse Excel data
		if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
			$result = $this->parse_excel_to_queue($file, $update_existing);
		} else {
			$result = $this->parse_csv_to_queue($file, $update_existing);
		}

		if ($result['success']) {
			wp_send_json_success(array(
				'job_id' => $result['job_id'],
				'total_products' => $result['total_products'],
				'message' => 'Import job created successfully. Processing will begin shortly.'
			));
		} else {
			wp_send_json_error($result['message']);
		}
	}

	/**
	 * Parse Excel data and add to queue
	 */
	private function parse_excel_to_queue($file, $update_existing)
	{
		try {
			// Set memory limit for parsing
			ini_set('memory_limit', '512M');
			ini_set('max_execution_time', 60); // Only 60 seconds for parsing

			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
			$worksheet = $spreadsheet->getActiveSheet();
			$rows = $worksheet->toArray(null, true, true, true);
			$filtered_rows = array_filter($rows, function ($row) {
				return !empty(trim($row['C'] ?? ''));
			});

			// Clear memory
			$spreadsheet->disconnectWorksheets();
			unset($spreadsheet);
			
			return $this->add_data_to_queue($filtered_rows, $update_existing);
		} catch (Exception $e) {
			return array(
				'success' => false,
				'message' => 'Error parsing Excel file: ' . $e->getMessage()
			);
		}
	}

	/**
	 * Parse CSV data and add to queue
	 */
	private function parse_csv_to_queue($file, $update_existing)
	{
		// Set memory limit for parsing
		ini_set('memory_limit', '512M');
		ini_set('max_execution_time', 60);

		$csv_data = array();

		// Read file content and convert encoding
		$content = file_get_contents($file['tmp_name']);
		$encoding = mb_detect_encoding($content, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1', 'Windows-1252'], true);
		if ($encoding !== 'UTF-8') {
			$content = mb_convert_encoding($content, 'UTF-8', $encoding);
		}

		// Create temporary file
		$tmpFile = tmpfile();
		fwrite($tmpFile, $content);
		rewind($tmpFile);

		// Read CSV data
		$row_count = 0;
		while (($data = fgetcsv($tmpFile, 1000, ",")) !== FALSE) {
			$csv_data[] = $data;
			$row_count++;
		}

		fclose($tmpFile);

		return $this->add_data_to_queue($csv_data, $update_existing);
	}

	/**
	 * Add parsed data to import queue
	 */
	private function add_data_to_queue($rows, $update_existing)
	{
		global $wpdb;

		if (empty($rows) || count($rows) < 2) {
			return array(
				'success' => false,
				'message' => 'No data found in Excel file'
			);
		}

		$headers = array_map('mb_strtolower', $rows[1]);
		$data_rows = array_slice($rows, 1);

		$products = array();
		$variants = array();

		// Separate main products and variants
		foreach ($data_rows as $row) {
			$row_data = array_combine($headers, $row);

			if (!empty($row_data[mamma_mia_get_column_key('sku')])) {
				if (!empty($row_data[mamma_mia_get_column_key('parent_sku')])) {
					$variants[] = $row_data;
				} else {
					$products[] = $row_data;
				}
			}
		}

		$total_products = count($products) + count($variants);

		if ($total_products === 0) {
			return array(
				'success' => false,
				'message' => 'No valid products found in the file'
			);
		}

		// Create import job
		$job_id = wp_generate_uuid4();
		$jobs_table = $wpdb->prefix . 'mamma_mia_import_jobs';
		$queue_table = $wpdb->prefix . 'mamma_mia_import_queue';

		$job_inserted = $wpdb->insert(
			$jobs_table,
			array(
				'job_id' => $job_id,
				'status' => 'pending',
				'total_products' => $total_products,
				'update_existing' => $update_existing ? 1 : 0
			),
			array('%s', '%s', '%d', '%d')
		);

		if (!$job_inserted) {
			return array(
				'success' => false,
				'message' => 'Failed to create import job',
				'error' => $wpdb->last_error
			);
		}

		// Add main products to queue first
		foreach ($products as $product_data) {
			$wpdb->insert(
				$queue_table,
				array(
					'job_id' => $job_id,
					'product_type' => 'main',
					'product_data' => wp_json_encode($product_data),
					'status' => 'pending'
				),
				array('%s', '%s', '%s', '%s')
			);
		}

		// Add variants to queue after main products
		foreach ($variants as $variant_data) {
			$wpdb->insert(
				$queue_table,
				array(
					'job_id' => $job_id,
					'product_type' => 'variant',
					'product_data' => wp_json_encode($variant_data),
					'status' => 'pending'
				),
				array('%s', '%s', '%s', '%s')
			);
		}

		return array(
			'success' => true,
			'job_id' => $job_id,
			'total_products' => $total_products
		);
	}

	/**
	 * Check import progress via AJAX
	 */
	public function check_import_progress()
	{
		if (!isset($_POST['job_id']) || !wp_verify_nonce($_POST['nonce'], 'excel_import_nonce')) {
			wp_send_json_error('Invalid request');
		}

		global $wpdb;
		$job_id = sanitize_text_field($_POST['job_id']);
		$jobs_table = $wpdb->prefix . 'mamma_mia_import_jobs';

		$job = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $jobs_table WHERE job_id = %s",
			$job_id
		));

		if (!$job) {
			wp_send_json_error('Job not found');
		}

		wp_send_json_success(array(
			'status' => $job->status,
			'total_products' => $job->total_products,
			'processed_products' => $job->processed_products,
			'created_products' => $job->created_products,
			'updated_products' => $job->updated_products,
			'failed_products' => $job->failed_products,
			'error_message' => $job->error_message,
			'progress_percentage' => $job->total_products > 0 ? round(($job->processed_products / $job->total_products) * 100, 2) : 0
		));
	}

	/**
	 * Check running imports via AJAX
	 */
	public function check_running_imports()
	{
		if (!wp_verify_nonce($_POST['nonce'], 'excel_import_nonce')) {
			wp_send_json_error('Invalid request');
		}

		global $wpdb;
		$jobs_table = $wpdb->prefix . 'mamma_mia_import_jobs';

		$running_jobs = $wpdb->get_results(
			"SELECT * FROM $jobs_table WHERE status IN ('pending', 'processing') ORDER BY created_at DESC"
		);

		wp_send_json_success($running_jobs);
	}

	/**
	 * Cancel import via AJAX
	 */
	public function cancel_import()
	{
		if (!isset($_POST['job_id']) || !wp_verify_nonce($_POST['nonce'], 'excel_import_nonce')) {
			wp_send_json_error('Invalid request');
		}

		global $wpdb;
		$job_id = sanitize_text_field($_POST['job_id']);
		$jobs_table = $wpdb->prefix . 'mamma_mia_import_jobs';
		$queue_table = $wpdb->prefix . 'mamma_mia_import_queue';

		// Check if job exists and is cancellable
		$job = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $jobs_table WHERE job_id = %s AND status IN ('pending', 'processing')",
			$job_id
		));

		if (!$job) {
			wp_send_json_error('Job not found or cannot be cancelled');
		}

		// Update job status to cancelled
		$updated = $wpdb->update(
			$jobs_table,
			array('status' => 'cancelled'),
			array('job_id' => $job_id),
			array('%s'),
			array('%s')
		);

		if ($updated === false) {
			wp_send_json_error('Failed to cancel job');
		}

		// Mark all pending queue items as cancelled
		$wpdb->update(
			$queue_table,
			array('status' => 'cancelled'),
			array(
				'job_id' => $job_id,
				'status' => 'pending'
			),
			array('%s', '%s'),
			array('%s', '%s')
		);

		wp_send_json_success('Import cancelled successfully');
	}

	/**
	 * Process import queue (called by cron)
	 */
	public function process_import_queue()
	{
		global $wpdb;

		$jobs_table = $wpdb->prefix . 'mamma_mia_import_jobs';
		$queue_table = $wpdb->prefix . 'mamma_mia_import_queue';

		// Get the next pending job
		$job = $wpdb->get_row(
			"SELECT * FROM $jobs_table WHERE status IN ('pending', 'processing') ORDER BY created_at ASC LIMIT 1"
		);

		if (!$job) {
			return; // No jobs to process
		}

		// Update job status to processing
		$wpdb->update(
			$jobs_table,
			array('status' => 'processing'),
			array('job_id' => $job->job_id),
			array('%s'),
			array('%s')
		);

		// Get next pending item from queue
		$queue_item = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $queue_table WHERE job_id = %s AND status = 'pending' ORDER BY product_type ASC, id ASC LIMIT 1",
			$job->job_id
		));

		if (!$queue_item) {
			// No more items to process, mark job as completed
			$wpdb->update(
				$jobs_table,
				array('status' => 'completed'),
				array('job_id' => $job->job_id),
				array('%s'),
				array('%s')
			);
			return;
		}

		// Mark current item as processing
		$wpdb->update(
			$queue_table,
			array('status' => 'processing'),
			array('id' => $queue_item->id),
			array('%s'),
			array('%d')
		);

		// Process the item
		$product_data = json_decode($queue_item->product_data, true);
		$result = false;

		try {
			if ($queue_item->product_type === 'main') {
				$result = $this->create_or_update_product($product_data, $job->update_existing);
			} else {
				$result = $this->create_or_update_variant($product_data, $job->update_existing);
			}
		} catch (Exception $e) {
			$result = array(
				'success' => false,
				'message' => $e->getMessage()
			);
		}

		// Update queue item status
		if ($result && $result['success']) {
			$wpdb->update(
				$queue_table,
				array('status' => 'completed'),
				array('id' => $queue_item->id),
				array('%s'),
				array('%d')
			);

		// Update job counters - count both main products and variants
		if ($queue_item->product_type === 'main') {
			if (isset($result['action']) && $result['action'] === 'created') {
				$wpdb->query($wpdb->prepare(
					"UPDATE $jobs_table SET processed_products = processed_products + 1, created_products = created_products + 1 WHERE job_id = %s",
					$job->job_id
				));
			} else {
				$wpdb->query($wpdb->prepare(
					"UPDATE $jobs_table SET processed_products = processed_products + 1, updated_products = updated_products + 1 WHERE job_id = %s",
					$job->job_id
				));
			}
		} else {
			// Count variants as well
			if (isset($result['action']) && $result['action'] === 'created') {
				$wpdb->query($wpdb->prepare(
					"UPDATE $jobs_table SET processed_products = processed_products + 1, created_products = created_products + 1 WHERE job_id = %s",
					$job->job_id
				));
			} else {
				$wpdb->query($wpdb->prepare(
					"UPDATE $jobs_table SET processed_products = processed_products + 1, updated_products = updated_products + 1 WHERE job_id = %s",
					$job->job_id
				));
			}
		}
		} else {
			$error_message = $result['message'] ?? 'Unknown error';
			$wpdb->update(
				$queue_table,
				array(
					'status' => 'failed',
					'error_message' => $error_message
				),
				array('id' => $queue_item->id),
				array('%s', '%s'),
				array('%d')
			);

			// Update job error counter
			$wpdb->query($wpdb->prepare(
				"UPDATE $jobs_table SET processed_products = processed_products + 1, failed_products = failed_products + 1 WHERE job_id = %s",
				$job->job_id
			));
		}
	}

	/**
	 * Create or update a product with optimized performance
	 */
	private function create_or_update_product($data, $update_existing)
	{
		$sku = sanitize_text_field($data[mamma_mia_get_column_key('sku')]);

		// Check if product exists
		$existing_product = null;
		$existing_product_id = wc_get_product_id_by_sku($sku);
		if ($existing_product_id) {
			$existing_product = wc_get_product($existing_product_id);
		}

		if ($existing_product && !$update_existing) {
			return array('success' => false, 'message' => 'Product with SKU ' . $sku . ' already exists');
		}

		$product_data = array(
			'name' => sanitize_text_field($data[mamma_mia_get_column_key('name')]),
			'sku' => $sku,
			'description' => wp_kses_post($data[mamma_mia_get_column_key('description')] ?? ''),
			'short_description' => wp_kses_post($data[mamma_mia_get_column_key('short_description')] ?? ''),
			'status' => 'publish',
			'catalog_visibility' => 'visible',
			'backorders' => 'no',
			'parent_id' => 0,
		);

		// Check for variations
		$has_variation = false;
		$variation_flags = [
			mamma_mia_get_column_key('mau-sac'),
			mamma_mia_get_column_key('kich-thuoc'),
		];

		foreach ($variation_flags as $column_key) {
			if (!empty($data[$column_key]) && (int)$data[$column_key] === 1) {
				$has_variation = true;
				break;
			}
		}

		// Create or update product
		if ($existing_product) {
			$product = $existing_product;

			if ($has_variation && !($product instanceof WC_Product_Variable)) {
				wp_set_object_terms($product->get_id(), 'variable', 'product_type');
				$product = new WC_Product_Variable($product->get_id());
			}

			$product->set_props($product_data);

			// Set variation attributes if needed
			if ($product instanceof WC_Product_Variable && $has_variation) $this->set_variation_attributes($product, $data);

			$product->save();
			$action = 'updated';
		}
		else {
			$product = $has_variation ? new WC_Product_Variable() : new WC_Product_Simple();
			$product->set_props($product_data);
			$product_id = $product->save();
			$product = wc_get_product($product_id);
			$action = 'created';

			// Set variation attributes to parent product if needed
			if ($has_variation) {
				$this->set_variation_attributes($product, $data);
				$product->save();
			}
		}

		// Handle images with reduced processing
		$this->handle_product_images($product, $data);

		// Set categories and taxonomies in batch
		$this->set_product_taxonomies_batch($product->get_id(), $data);

		// Set Yoast SEO meta
		$this->set_yoast_meta($product->get_id(), $data);

		// Handle ACF fields with reduced processing
		$this->handle_acf_fields($product->get_id(), $data);

		return array('success' => true, 'action' => $action);
	}

	/**
	 * Set variation attributes for variable products
	 */
	private function set_variation_attributes($product, $data)
	{
		$attributes = [];
		$color_attribute_name = 'pa_mau-sac';
		$size_attribute_name = 'pa_kich-thuoc';
		$color_attribute_id = wc_attribute_taxonomy_id_by_name($color_attribute_name);
		$size_attribute_id = wc_attribute_taxonomy_id_by_name($size_attribute_name);

		if ((int)$data[mamma_mia_get_column_key('mau-sac')] === 1) {
			$attribute = new WC_Product_Attribute();
			$attribute->set_name($color_attribute_name);
			$attribute->set_options([]);
			$attribute->set_visible(true);
			$attribute->set_variation(true);
			$attribute->set_id($color_attribute_id);
			$attributes[$color_attribute_name] = $attribute;
		}

		if ((int)$data[mamma_mia_get_column_key('kich-thuoc')] === 1) {
			$attribute = new WC_Product_Attribute();
			$attribute->set_name($size_attribute_name);
			$attribute->set_options([]);
			$attribute->set_visible(true);
			$attribute->set_variation(true);
			$attribute->set_id($size_attribute_id);
			$attributes[$size_attribute_name] = $attribute;
		}

		if (!empty($attributes)) {
			$product->set_attributes($attributes);
		}
	}

	/**
	 * Create or update a product variant
	 */
	private function create_or_update_variant($data, $update_existing)
	{
		$sku = sanitize_text_field($data[mamma_mia_get_column_key('sku')]);
		$parent_sku = sanitize_text_field($data[mamma_mia_get_column_key('parent_sku')]);

		// Tìm sản phẩm cha
		$parent_id = wc_get_product_id_by_sku($parent_sku);
		if (!$parent_id) return array('success' => false, 'message' => 'Parent product with SKU ' . $parent_sku . ' not found');

		// Kiểm tra biến thể đã tồn tại chưa
		$existing_variant_id = wc_get_product_id_by_sku($sku);
		if ($existing_variant_id && !$update_existing) return array('success' => false, 'message' => 'Variant with SKU ' . $sku . ' already exists');

		$variant_data = array(
			'status'       => 'publish',
			'stock_status' => 'instock',
			'sku'          => $sku,
		);

		// Tạo hoặc cập nhật biến thể
		$action = '';
		if ($existing_variant_id) {
			$variant = wc_get_product($existing_variant_id);
			$variant->set_props($variant_data);
			$variant->save();
			$action = 'updated';
		}
		else {
			$variant = new WC_Product_Variation();
			$variant->set_props($variant_data);
			$variant->set_parent_id($parent_id);
			$variant_id = $variant->save();
			$variant = wc_get_product($variant_id);
			$action = 'created';
		}

		// ===== ẢNH ĐẠI DIỆN CHO BIẾN THỂ =====
		$image_url = trim($data[mamma_mia_get_column_key('image_url')] ?? '');

		if (!empty($image_url)) {
			$attachment_id = $this->import_image_from_drive($image_url);

			if ($attachment_id && !is_wp_error($attachment_id)) {
				$variant->set_image_id($attachment_id);
				$variant->save();
			}
		}

		// ===== GALLERY ẢNH BIẾN THỂ =====
		$gallery_field = trim($data[mamma_mia_get_column_key('gallery_images')] ?? '');

		if (!empty($gallery_field)) {
			$gallery_urls = array_map('trim', explode(';', $gallery_field));
			$gallery_ids = [];

			foreach ($gallery_urls as $gallery_url) {
				if (!empty($gallery_url)) {
					if ($attachment_id && !is_wp_error($attachment_id)) $gallery_ids[] = $this->import_image_from_drive($gallery_url);
				}
			}

			if (!empty($gallery_ids)) {
				update_post_meta($variant->get_id(), 'woo_variation_gallery_images', $gallery_ids); // Dạng mảng
			}
		}

		// ===== THUỘC TÍNH BIẾN THỂ =====
		$attributes = [];

		// Màu sắc: định dạng "Tên | Giá trị"
		$color_raw = sanitize_text_field($data[mamma_mia_get_column_key('mau-sac')] ?? '');

		if (!empty($color_raw) && strpos($color_raw, '|') !== false) {
			list($color_name, $color_hex) = array_map('trim', explode('|', $color_raw, 2));

			$taxonomy = 'pa_mau-sac';
			// Đảm bảo sản phẩm cha có khai báo thuộc tính này
			$this->ensure_parent_has_attribute_term($parent_id, $taxonomy, $color_name);

			// Tạo term nếu chưa có
			$term = term_exists($color_name, $taxonomy);
			if (!$term) $term = wp_insert_term($color_name, $taxonomy);

			if (!is_wp_error($term)) {
				$term_id = is_array($term) ? $term['term_id'] : $term;

				// Cập nhật kiểu hiển thị swatch và mã màu (dành cho plugin Variation Swatches)
				update_term_meta($term_id, 'product_attribute_color', $color_hex);

				// Lấy slug của term
				$term_obj = get_term_by('id', $term_id, $taxonomy);
    			$term_slug = $term_obj ? $term_obj->slug : sanitize_title($color_name);

				// Gán term cho biến thể
				wp_set_object_terms($variant->get_id(), $term_slug, $taxonomy);
				$attributes[$taxonomy] = $term_slug;
			}
		}

		// Kích thước: giá trị là tên luôn (vd: 600mm)
		$size_value = sanitize_text_field($data[mamma_mia_get_column_key('kich-thuoc')] ?? '');

		if (!empty($size_value)) {
			$taxonomy = 'pa_kich-thuoc';
			// Đảm bảo sản phẩm cha có khai báo thuộc tính này
			$this->ensure_parent_has_attribute_term($parent_id, $taxonomy, $size_value);
			if (!term_exists($size_value, $taxonomy)) wp_insert_term($size_value, $taxonomy);

			wp_set_object_terms($variant->get_id(), $size_value, $taxonomy);
			$attributes[$taxonomy] = $size_value;
		}

		// Gán thuộc tính cho biến thể
		if (!empty($attributes)) {
			$variant->set_attributes($attributes);
			$variant->save();
		}

		return array('success' => true, 'action' => $action);
	}

	private function ensure_parent_has_attribute_term($parent_id, $taxonomy, $term_value) {
		// Gán term cho sản phẩm cha để biến thể kế thừa được
		$existing_terms = wp_get_object_terms($parent_id, $taxonomy, ['fields' => 'names']);
		if (!in_array($term_value, $existing_terms)) {
			wp_set_object_terms($parent_id, array_merge($existing_terms, [$term_value]), $taxonomy);
		}
	}

	/**
	 * Handle product images with batch processing
	 */
	private function handle_product_images($product, $data)
	{
		$image_ids = [];
		$gallery_ids = [];

		// Process main image
		if (!empty($data[mamma_mia_get_column_key('image_url')])) {
			$image_id = $this->import_image_from_drive($data[mamma_mia_get_column_key('image_url')]);
			if ($image_id) {
				$product->set_image_id($image_id);
			}
		}

		// Process gallery images in batch
		if (!empty($data[mamma_mia_get_column_key('gallery_images')])) {
			$gallery_urls = explode(';', $data[mamma_mia_get_column_key('gallery_images')]);
			
			// Process up to 5 gallery images to avoid timeout
			$gallery_urls = array_slice($gallery_urls, 0, 5);
			
			foreach ($gallery_urls as $url) {
				$url = trim($url);
				if (!empty($url)) {
					$image_id = $this->import_image_from_drive($url);
					if ($image_id) {
						$gallery_ids[] = $image_id;
					}
				}
			}

			if (!empty($gallery_ids)) {
				$product->set_gallery_image_ids($gallery_ids);
			}
		}

		$product->save();
	}

	/**
	 * Check if URL is accessible (not 404)
	 */
	private function is_url_accessible($url) {
		$response = wp_remote_head($url, array(
			'timeout' => 10,
			'redirection' => 3
		));

		if (is_wp_error($response)) return false;

		$response_code = wp_remote_retrieve_response_code($response);
		return $response_code === 200;
	}

	/**
	 * Import image from Google Drive link with caching and optimization
	 */
	private function import_image_from_drive($drive_url, $returnType = 'id')
	{
		// Cache key for this URL
		$cache_key = 'drive_image_' . md5($drive_url);
		$cached_result = wp_cache_get($cache_key);
		if ($cached_result !== false) {
			return $cached_result;
		}

		$file_id = null;

		// Extract file ID from URL
		if (strpos($drive_url, 'drive.google.com/file/d/') !== false) {
			preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $drive_url, $matches);
			if (isset($matches[1])) $file_id = $matches[1];
		}
		elseif (strpos($drive_url, 'drive.google.com/open?id=') !== false) {
			preg_match('/id=([a-zA-Z0-9_-]+)/', $drive_url, $matches);
			if (isset($matches[1])) $file_id = $matches[1];
		}

		if (!$file_id) {
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		// Check if we already have this file in media library by file ID
		$existing_attachment = $this->get_attachment_by_drive_id($file_id);

		if ($existing_attachment) {
			$result = $returnType === 'url' ? wp_get_attachment_url($existing_attachment) : $existing_attachment;
			$urlAttachment = $returnType === 'url' ? $result : wp_get_attachment_url($existing_attachment);
			
			// Check if the URL is still valid (not 404)
			if (!$this->is_url_accessible($urlAttachment)) {
				// URL is 404, remove from cache and continue to re-download
				wp_cache_delete($cache_key);
				// Also delete the attachment from media library
				wp_delete_attachment($existing_attachment, true);
			}
			else {
				wp_cache_set($cache_key, $result, '', 3600);
				return $result;
			}
		}

		// Use direct download URL (faster than confirm token method)
		$direct_url = "https://drive.google.com/uc?export=download&id=$file_id";

		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		// Download with shorter timeout
		$tmp = download_url($direct_url, 30);
		if (is_wp_error($tmp)) {
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		// Get filename from content disposition or use file ID
		$filename = $this->get_filename_from_drive_url($drive_url, $file_id);
		
		if (empty($filename) || !$this->is_valid_image($filename)) {
			@unlink($tmp);
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		// Create file array
		$file_array = [
			'name'     => $filename,
			'tmp_name' => $tmp,
		];

		// Upload to media library
		$attachment_id = media_handle_sideload($file_array, 0);

		if (is_wp_error($attachment_id)) {
			@unlink($tmp);
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		// Store drive file ID for future reference
		update_post_meta($attachment_id, '_drive_file_id', $file_id);

		$result = $returnType === 'url' ? wp_get_attachment_url($attachment_id) : $attachment_id;
		wp_cache_set($cache_key, $result, '', 3600);
		
		return $result;
	}

	/**
	 * Get attachment by Google Drive file ID
	 */
	private function get_attachment_by_drive_id($file_id)
	{
		$existing = get_posts([
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_query'  => [
				[
					'key'     => '_drive_file_id',
					'value'   => $file_id,
					'compare' => '=',
				]
			],
			'posts_per_page' => 1,
		]);

		return !empty($existing) ? $existing[0]->ID : false;
	}

	/**
	 * Get filename from drive URL or generate one
	 */
	private function get_filename_from_drive_url($drive_url, $file_id)
	{
		// Try to extract filename from URL
		if (preg_match('/[^\/\?]+\.(jpg|jpeg|png|gif|webp|bmp)$/i', $drive_url, $matches)) {
			return sanitize_file_name($matches[0]);
		}

		// Generate filename from file ID
		return 'drive_' . $file_id . '.jpg';
	}

	/**
	 * Import file from Google Drive link with caching
	 */
	private function import_file_from_drive($drive_url, $returnType = 'id')
	{
		// Cache key for this URL
		$cache_key = 'drive_file_' . md5($drive_url);
		$cached_result = wp_cache_get($cache_key);
		if ($cached_result !== false) return $cached_result;

		$file_id = null;

		// Extract file ID from URL
		if (strpos($drive_url, 'drive.google.com/file/d/') !== false) {
			preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $drive_url, $matches);
			if (isset($matches[1])) $file_id = $matches[1];
		}
		elseif (strpos($drive_url, 'drive.google.com/open?id=') !== false) {
			preg_match('/id=([a-zA-Z0-9_-]+)/', $drive_url, $matches);
			if (isset($matches[1])) $file_id = $matches[1];
		}

		if (!$file_id) {
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		// Check if we already have this file
		$existing_attachment = $this->get_attachment_by_drive_id($file_id);

		if ($existing_attachment) {
			$result = $returnType === 'url' ? wp_get_attachment_url($existing_attachment) : $existing_attachment;
			wp_cache_set($cache_key, $result, '', 3600);
			return $result;
		}

		// Use direct download URL
		$direct_url = "https://drive.google.com/uc?export=download&id=$file_id";

		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		// 🟡 Lấy tên file gốc từ header (nếu có)
		$response = wp_remote_get($direct_url, ['timeout' => 15, 'redirection' => 5]);
		$filename = '';

		if (!is_wp_error($response)) {
			$headers = wp_remote_retrieve_headers($response);
			if (!empty($headers['content-disposition'])) {
				preg_match('/filename[^;=\n]*=(["\']?)(.*?)\1/', $headers['content-disposition'], $matches);
				if (!empty($matches[2])) {
					$filename = sanitize_file_name($matches[2]);
				}
			}
		}

		if (empty($filename)) {
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		// Download with shorter timeout
		$tmp = download_url($direct_url, 30);
		if (is_wp_error($tmp)) {
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		// Create file array
		$file_array = [
			'name'     => $filename,
			'tmp_name' => $tmp,
		];

		// Upload to media library
		$attachment_id = media_handle_sideload($file_array, 0);

		if (is_wp_error($attachment_id)) {
			@unlink($tmp);
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		// Store drive file ID
		update_post_meta($attachment_id, '_drive_file_id', $file_id);

		$result = $returnType === 'url' ? wp_get_attachment_url($attachment_id) : $attachment_id;
		wp_cache_set($cache_key, $result, '', 3600);
		
		return $result;
	}

	function is_valid_image($filename) {
		// Lấy phần mở rộng file (không phân biệt hoa thường)
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	
		// Danh sách đuôi ảnh hợp lệ
		$valid_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
	
		return in_array($ext, $valid_exts);
	}

	/**
	 * Set product categories
	 */
	private function set_product_categories($product_id, $category_raw)
	{
		$categories = explode('>', $category_raw); // Tách theo dấu >
		$parent_id = 0;
		$term_ids = [];

		foreach ($categories as $cat_name) {
			$cat_name = trim($cat_name);

			// Kiểm tra category đã tồn tại chưa
			$term = term_exists($cat_name, 'product_cat', $parent_id);

			if (!$term) {
				// Nếu chưa có, tạo mới với parent
				$term = wp_insert_term($cat_name, 'product_cat', [
					'parent' => $parent_id,
				]);
			}

			if (!is_wp_error($term)) {
				$term_id = (int) (is_array($term) ? $term['term_id'] : $term);
				$term_ids[] = $term_id;
				$parent_id = $term_id; // Gán lại parent cho cấp kế tiếp
			}
		}
		
		wp_set_object_terms($product_id, $term_ids, 'product_cat');
	}

	private function set_custom_taxonomy_terms($product_id, $terms_string, $taxonomy_slug) {
		$terms_raw = explode(';', $terms_string);
		$term_ids = [];
	
		foreach ($terms_raw as $term_name) {
			$term_name = trim($term_name);
			if (empty($term_name)) continue;
	
			// Kiểm tra term đã tồn tại chưa (dưới taxonomy đã chỉ định)
			$term = term_exists($term_name, $taxonomy_slug);
	
			if (!$term) {
				$term = wp_insert_term($term_name, $taxonomy_slug);
			}
	
			if (!is_wp_error($term)) {
				$term_ids[] = (int) (is_array($term) ? $term['term_id'] : $term);
			}
		}
	
		if (!empty($term_ids)) {
			wp_set_object_terms($product_id, $term_ids, $taxonomy_slug);
		}
	}

	/**
	 * Handle ACF fields with optimized processing
	 */
	private function handle_acf_fields($post_id, $data)
	{
		// ACF not active
		if (!function_exists('update_field')) return;

		if (!empty($data[mamma_mia_get_column_key('features_text')])) {
			$usp_texts = array_map('trim', explode(';', $data[mamma_mia_get_column_key('features_text')]));
			$usp_images = array_map('trim', explode(';', $data[mamma_mia_get_column_key('features_image')]));

			$custom_features = [];

			for ($i = 0; $i < count($usp_texts); $i++) {
				$feature = $usp_texts[$i] ?? '';
				$image = $usp_images[$i] ?? '';

				if (!empty($feature) || !empty($image)) {
					$custom_features[] = [
						'feature' => $feature,
						'image' => $this->import_image_from_drive($image, 'url'),
					];
				}
			}

			update_field('_custom_product_features', $custom_features, $post_id);
		}

		$design_file_group = [];

		foreach ($this->design_file_fields as $key) {
			if (!empty($data[mamma_mia_get_column_key($key)])) {
				$file = $this->import_file_from_drive($data[mamma_mia_get_column_key($key)], 'id');
				if (!empty($file)) {
					$design_file_group[$key] = $file;
				}
			}
		}

		if (!empty($design_file_group)) {
			update_field('design_file', $design_file_group, $post_id);
		}

		// Process technical image
		if (!empty($data[mamma_mia_get_column_key('technical_image')])) {
			$image_url = $this->import_image_from_drive($data[mamma_mia_get_column_key('technical_image')], 'id');
			if ($image_url) {
				update_field('technical_image', $image_url, $post_id);
			}
		}

		// Process ACF fields
		foreach ($this->acf_fields as $key) {
			if (!empty($data[mamma_mia_get_column_key($key)])) {
				update_field($key, $data[mamma_mia_get_column_key($key)], $post_id);
			}
		}
	}

	/**
	 * Set product taxonomies in batch
	 */
	private function set_product_taxonomies_batch($product_id, $data)
	{
		// Set categories
		if (!empty($data[mamma_mia_get_column_key('categories')])) {
			$this->set_product_categories($product_id, $data[mamma_mia_get_column_key('categories')]);
		}

		// Set taxonomies in batch
		$taxonomy_terms = array();
		
		foreach ($this->taxonomies_mappings as $mapping => $taxonomy) {
			if (!empty($data[mamma_mia_get_column_key($mapping)])) {
				$terms = array_map('trim', explode(';', $data[mamma_mia_get_column_key($mapping)]));
				if (!empty($terms)) {
					$taxonomy_terms[$taxonomy] = $terms;
				}
			}
		}

		// Set custom taxonomies
		foreach ($this->custom_taxonomies_mappings as $mapping => $taxonomy) {
			if (!empty($data[mamma_mia_get_column_key($mapping)])) {
				$this->set_custom_taxonomy_terms($product_id, $data[mamma_mia_get_column_key($mapping)], $taxonomy);
			}
		}

		// Set all taxonomies at once
		foreach ($taxonomy_terms as $taxonomy => $terms) {
			wp_set_object_terms($product_id, $terms, $taxonomy);
		}
	}

	/**
	 * Set Yoast SEO meta
	 */
	private function set_yoast_meta($product_id, $data)
	{
		if (!empty($data[mamma_mia_get_column_key('meta_title')])) {
			update_post_meta($product_id, '_yoast_wpseo_title', sanitize_text_field($data[mamma_mia_get_column_key('meta_title')]));
		}

		if (!empty($data[mamma_mia_get_column_key('meta_description')])) {
			update_post_meta($product_id, '_yoast_wpseo_metadesc', sanitize_textarea_field($data[mamma_mia_get_column_key('meta_description')]));
		}
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts()
	{
		// Add any frontend scripts if needed
	}

	/**
	 * Admin enqueue scripts
	 */
	public function admin_enqueue_scripts($hook)
	{
		if (strpos($hook, 'mamma-mia') !== false) {
			wp_enqueue_script('jquery');
			
			// Load external files for import page
			if (strpos($hook, 'mamma-mia-import') !== false) {
				// Enqueue CSS file
				wp_enqueue_style(
					'mamma-mia-import-css',
					WEBSITE_CONFIG_PLUGIN_URL . 'assets/css/import-page.css',
					array(),
					WEBSITE_CONFIG_VERSION
				);
				
				// Enqueue JavaScript file
				wp_enqueue_script(
					'mamma-mia-import-js',
					WEBSITE_CONFIG_PLUGIN_URL . 'assets/js/import-page.js',
					array('jquery'),
					WEBSITE_CONFIG_VERSION,
					true
				);
			}
		}
	}

	public function plugin_activate()
	{
		$this->create_import_tables();
		$this->schedule_cron_jobs();
	}

	public function plugin_deactivate()
	{
		// Clear scheduled cron jobs
		wp_clear_scheduled_hook('mamma_mia_process_import_queue');
	}

	/**
	 * Display recent import jobs status
	 */
	private function display_import_jobs_status()
	{
		global $wpdb;
		$jobs_table = $wpdb->prefix . 'mamma_mia_import_jobs';

		$jobs = $wpdb->get_results(
			"SELECT * FROM $jobs_table ORDER BY created_at DESC LIMIT 10"
		);

		if (empty($jobs)) {
			echo '<p>Không có nhiệm vụ import nào được thực hiện.</p>';
			return;
		}

		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>Job ID</th><th>Tổng sản phẩm</th><th>Đã xử lý</th><th>Tạo mới</th><th>Cập nhật</th><th>Lỗi</th><th>Trạng thái</th><th>Thời gian</th></tr></thead>';
		echo '<tbody>';

		foreach ($jobs as $job) {
			$status_class = '';
			$status_text = '';

			switch ($job->status) {
				case 'pending':
					$status_class = 'warning';
					$status_text = 'Đang chờ';
					break;
				case 'processing':
					$status_class = 'info';
					$status_text = 'Đang xử lý';
					break;
				case 'completed':
					$status_class = 'success';
					$status_text = 'Hoàn tất';
					break;
				case 'failed':
					$status_class = 'error';
					$status_text = 'Thất bại';
					break;
				case 'cancelled':
					$status_class = 'warning';
					$status_text = 'Đã hủy';
					break;
			}

			echo '<tr class="' . esc_attr($status_class) . '">';
			echo '<td>' . esc_html($job->job_id) . '</td>';
			echo '<td>' . esc_html($job->total_products) . '</td>';
			echo '<td>' . esc_html($job->processed_products) . '</td>';
			echo '<td>' . esc_html($job->created_products) . '</td>';
			echo '<td>' . esc_html($job->updated_products) . '</td>';
			echo '<td>' . esc_html($job->failed_products) . '</td>';
			echo '<td>' . esc_html($status_text) . '</td>';
			echo '<td>' . esc_html(date('Y-m-d H:i:s', strtotime($job->created_at))) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}
}

// Initialize the plugin
$website_config = new WebsiteConfig();

// Plugin activation hook
register_activation_hook(__FILE__, array($website_config, 'plugin_activate'));
register_deactivation_hook(__FILE__, array($website_config, 'plugin_deactivate'));
