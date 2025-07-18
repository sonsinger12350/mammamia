<?php
/**
 * Plugin Name: Website Config
 * Plugin URI: 
 * Description: A simple plugin to manage website configuration settings.
 * Version: 1.0.0
 * Author: Lucas
 * License: GPL v2 or later
 * Text Domain: website-config
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WEBSITE_CONFIG_VERSION', '1.0.0');
define('WEBSITE_CONFIG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WEBSITE_CONFIG_PLUGIN_PATH', plugin_dir_path(__FILE__));

class WebsiteConfig {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Website Config',
            'Website Config',
            'manage_options',
            'website-config',
            array($this, 'admin_page'),
            'dashicons-admin-generic',
            30
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('website_config_options', 'website_config_phone');
        register_setting('website_config_options', 'website_config_email');
		register_setting('website_config_options', 'website_config_zalo');
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        // Save settings if form is submitted
        if (isset($_POST['submit'])) {
            update_option('website_config_phone', sanitize_text_field($_POST['website_config_phone']));
            update_option('website_config_email', sanitize_email($_POST['website_config_email']));
			update_option('website_config_zalo', sanitize_text_field($_POST['website_config_zalo']));
            echo '<div class="notice notice-success"><p>Cấu hình đã được lưu thành công!</p></div>';
        }
        
        // Get current values
        $phone = get_option('website_config_phone', '');
        $email = get_option('website_config_email', '');
		$zalo = get_option('website_config_zalo', '');
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
                </table>
                
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Add any frontend scripts if needed
    }
}

// Helper functions for theme use
function get_website_phone() {
    return get_option('website_config_phone', '');
}

function get_website_email() {
    return get_option('website_config_email', '');
}

function get_website_zalo() {
    return get_option('website_config_zalo', '');
}

// Initialize the plugin
new WebsiteConfig(); 