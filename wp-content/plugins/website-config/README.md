# Website Config Plugin

A WordPress plugin to manage website configuration settings and import products from Excel files with support for variants and ACF fields.

## Features

- Admin interface to manage website contact information
- Excel import functionality for WooCommerce products
- Support for product variants
- Google Drive image integration
- ACF (Advanced Custom Fields) support
- Secure form handling with proper sanitization
- Clean and user-friendly interface

## Installation

1. Upload the `website-config` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Website Config' in the admin menu to configure your settings

## Usage

### Website Configuration

1. Navigate to **Website Config** in your WordPress admin menu
2. Enter your business phone number, email, and Zalo link
3. Configure download design file settings
4. Click "Save Settings" to store the information

### Excel Product Import

1. Navigate to **Website Config > Import Products** in your WordPress admin menu
2. Prepare your Excel file with the required columns (see format below)
3. Upload the Excel file
4. Choose whether to update existing products or create new ones
5. Click "Import Products" to start the import process

#### Excel File Format

The Excel file should contain the following columns:

| Column | Field | Required | Description |
|--------|-------|----------|-------------|
| A | SKU | Yes | Product SKU (unique identifier) |
| B | Parent SKU | No | Parent SKU for variants (leave empty for main products) |
| C | Name | Yes | Product name |
| D | Description | No | Product description |
| E | Price | No | Product price |
| F | Regular Price | No | Regular price (for sale products) |
| G | Stock | No | Stock quantity |
| H | Category | No | Product category (comma-separated for multiple) |
| I | Image URL | No | Google Drive image link |
| J | Gallery Images | No | Comma-separated Google Drive image links |
| K | Status | No | publish, draft, private |
| L+ | ACF Fields | No | Any additional ACF fields |

#### Example Excel Data

```
SKU,Parent SKU,Name,Description,Price,Regular Price,Stock,Category,Image URL,Gallery Images,Status
PROD001,,Sample Product,This is a sample product,100,120,50,Electronics,https://drive.google.com/file/d/123456/view,https://drive.google.com/file/d/789012/view,publish
PROD001-VAR1,PROD001,Red Variant,Red color variant,110,130,25,Electronics,https://drive.google.com/file/d/345678/view,,publish
PROD001-VAR2,PROD001,Blue Variant,Blue color variant,110,130,25,Electronics,https://drive.google.com/file/d/901234/view,,publish
```

#### Google Drive Image Integration

The plugin supports importing images from Google Drive links. Supported URL formats:

- `https://drive.google.com/file/d/FILE_ID/view`
- `https://drive.google.com/open?id=FILE_ID`

The plugin will automatically convert these to direct download URLs and import the images to your WordPress media library.

#### Product Variants

To create product variants:

1. Create a main product row with a unique SKU
2. Create variant rows with the same Parent SKU as the main product
3. The plugin will automatically convert the main product to a variable product
4. Variants will be created as product variations

#### ACF Fields Support

Any additional columns in your Excel file beyond the standard WooCommerce fields will be treated as ACF fields and automatically imported if the Advanced Custom Fields plugin is active.

### Theme Integration

You can display the saved configuration values in your theme using these methods:

#### Method 1: Direct WordPress Options
```php
<?php echo get_option('website_config_phone'); ?>
<?php echo get_option('website_config_email'); ?>
<?php echo get_option('website_config_zalo'); ?>
```

#### Method 2: Helper Functions (Recommended)
```php
<?php echo get_website_phone(); ?>
<?php echo get_website_email(); ?>
```

### Example Usage in Theme Files

```php
<!-- Display contact information -->
<div class="contact-info">
    <p>Call us: <a href="tel:<?php echo get_website_phone(); ?>"><?php echo get_website_phone(); ?></a></p>
    <p>Email us: <a href="mailto:<?php echo get_website_email(); ?>"><?php echo get_website_email(); ?></a></p>
    <p>Zalo: <a href="<?php echo get_option('website_config_zalo'); ?>">Chat on Zalo</a></p>
</div>
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- WooCommerce 3.0 or higher (for product import functionality)
- Advanced Custom Fields (optional, for ACF field support)

## Dependencies

For optimal Excel import functionality, the plugin will work with:
- PhpSpreadsheet library (if available)
- Fallback to simple CSV parsing if PhpSpreadsheet is not available

## Security Features

- Input sanitization for all form fields
- Email validation for email addresses
- Proper WordPress nonce handling
- Capability checks for admin access
- File type validation for Excel uploads
- Secure AJAX handling

## Troubleshooting

### Import Issues

1. **File not uploading**: Check file size limits and ensure the file is a valid Excel format (.xlsx or .xls)
2. **Images not importing**: Verify Google Drive links are publicly accessible and in the correct format
3. **Variants not creating**: Ensure Parent SKU matches exactly with the main product SKU
4. **ACF fields not importing**: Make sure Advanced Custom Fields plugin is active

### Common Errors

- **"Parent product not found"**: The main product must be imported before its variants
- **"Product already exists"**: Use the "Update Existing Products" option or change SKUs
- **"Invalid file type"**: Ensure you're uploading an Excel file (.xlsx or .xls)

## Support

For support or feature requests, please contact the plugin developer.

## License

This plugin is licensed under the GPL v2 or later. 