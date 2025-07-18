# Website Config Plugin

A simple WordPress plugin to manage website configuration settings like phone number and email address.

## Features

- Admin interface to manage website contact information
- Secure form handling with proper sanitization
- Helper functions for easy theme integration
- Clean and user-friendly interface

## Installation

1. Upload the `website-config` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Website Config' in the admin menu to configure your settings

## Usage

### Admin Interface

1. Navigate to **Website Config** in your WordPress admin menu
2. Enter your business phone number and email address
3. Click "Save Settings" to store the information

### Theme Integration

You can display the saved values in your theme using these methods:

#### Method 1: Direct WordPress Options
```php
<?php echo get_option('website_config_phone'); ?>
<?php echo get_option('website_config_email'); ?>
```

#### Method 2: Helper Functions (Recommended)
```php
<?php echo get_website_phone(); ?>
<?php echo get_website_email(); ?>
```

### Example Usage in Theme Files

```php
<!-- Display phone number -->
<div class="contact-info">
    <p>Call us: <a href="tel:<?php echo get_website_phone(); ?>"><?php echo get_website_phone(); ?></a></p>
</div>

<!-- Display email -->
<div class="contact-info">
    <p>Email us: <a href="mailto:<?php echo get_website_email(); ?>"><?php echo get_website_email(); ?></a></p>
</div>
```

## Security Features

- Input sanitization for phone numbers
- Email validation for email addresses
- Proper WordPress nonce handling
- Capability checks for admin access

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## Support

For support or feature requests, please contact the plugin developer.

## License

This plugin is licensed under the GPL v2 or later. 