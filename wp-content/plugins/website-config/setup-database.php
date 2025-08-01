<?php
/**
 * Manual database setup script for MammaMia import system
 * Run this file directly to create the database tables if they don't exist
 */

// Include WordPress
require_once('../../../wp-config.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You need administrator privileges to run this script.');
}

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();

// Import jobs table
$table_name = $wpdb->prefix . 'mamma_mia_import_jobs';
$sql = "CREATE TABLE $table_name (
    id int(11) NOT NULL AUTO_INCREMENT,
    job_id varchar(50) NOT NULL,
    status enum('pending','processing','completed','failed') DEFAULT 'pending',
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
    status enum('pending','processing','completed','failed') DEFAULT 'pending',
    error_message text,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY job_id (job_id),
    KEY status (status),
    KEY product_type (product_type)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

echo "<h2>Setting up MammaMia Import Database Tables</h2>";

// Create tables
$result1 = dbDelta($sql);
$result2 = dbDelta($sql2);

echo "<h3>Import Jobs Table:</h3>";
echo "<pre>" . print_r($result1, true) . "</pre>";

echo "<h3>Import Queue Table:</h3>";
echo "<pre>" . print_r($result2, true) . "</pre>";

// Schedule cron job
if (!wp_next_scheduled('mamma_mia_process_import_queue')) {
    // Add custom cron interval first
    add_filter('cron_schedules', function($schedules) {
        $schedules['mamma_mia_every_30_seconds'] = array(
            'interval' => 30,
            'display'  => esc_html__('Every 30 Seconds'),
        );
        return $schedules;
    });
    
    wp_schedule_event(time(), 'mamma_mia_every_30_seconds', 'mamma_mia_process_import_queue');
    echo "<p><strong>✅ Cron job scheduled successfully!</strong></p>";
} else {
    echo "<p><strong>ℹ️ Cron job already scheduled.</strong></p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p>The database tables have been created and the cron job has been scheduled.</p>";
echo "<p><a href='/wp-admin/admin.php?page=mamma-mia'>← Back to MammaMia Settings</a></p>";
?> 