<?php
/**
 * Column Mapping Functions for MammaMia Plugin
 * This file contains all column name mappings used throughout the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get column name mappings
 * This function centralizes all column name mappings used throughout the plugin
 */
function mamma_mia_get_column_mappings() {
    return array(
        // Standard WooCommerce fields
        'name' => 'Tên sản phẩm',
        'sku' => 'SKU',
        'parent_sku' => 'Parent SKU',
        'short_description' => 'Mô tả ngắn',
        'description' => 'Mô tả chi tiết',
        'image_url' => 'Ảnh đại diện',
        'gallery_images' => 'Thư viện ảnh',

        'tags' => 'Tags',
        'brand' => 'Thương hiệu',
        'categories' => 'Danh mục',

        'meta_title' => 'Meta Title',
        'meta_description' => 'Meta Description',

        'bo-suu-tap' => 'Bộ sưu tập',
        'chat-lieu' => 'Chất liệu',
        'chinh-sach-bao-hanh' => 'Chính sách bảo hành',

        'mau-sac' => 'Màu sắc',
        'kich-thuoc' => 'Kích thước (biến thể)',

        'catalogue' => 'Catalogue',
        'brochure' => 'Brochure',
        'instructions' => 'Hướng dẫn lắp đặt',
        'user_guide' => 'Hướng dẫn sử dụng',
        '3d' => '3D',
        'spec' => 'SPEC',
        'autocad' => 'AutoCAD',

        'features_text' => 'TEXT USP',
        'features_image' => 'HÌNH ẢNH USP',
        'size' => 'Kích thước',
        'weight' => 'Trọng lượng',
        'wels_rating' => 'Đánh giá WELS',
        'wels_registration_number' => 'Mã số đăng ký WELS',
        'watermark_license' => 'Giấy phép Watermark',
        'operating_temperature' => 'Nhiệt độ định mức',
        'operating_pressure' => 'Áp suất định mức',
        'technology' => 'Công nghệ',
        'discharge_mode' => 'Chế độ xả',
        'accessories' => 'Phụ kiện đi kèm',
        'water_tank' => 'Két nước',
        'water_capacity' => 'Lượng nước',
        'operating_water_pressure' => 'Áp lực nước vận hành',
        'power_source' => 'Nguồn cấp điện',
        'origin' => 'Xuất xứ',
        'technical_image' => 'Hình ảnh cho phần thông tin chi tiết',
        'price_type' => 'Giá',
    );
}

/**
 * Get column key by name (reverse lookup)
 */
function mamma_mia_get_column_key($name, $lower = true) {
    $mappings = mamma_mia_get_column_mappings();
    $key = !empty($mappings[$name]) ? $mappings[$name] : null;

    return $lower ? mb_strtolower($key) : $key;
}