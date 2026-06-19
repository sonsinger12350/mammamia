<?php

/**
 * Google Drive image import, reuse, and USP feature image path migration.
 */

if (!defined('ABSPATH')) {
	exit;
}

class Mamma_Mia_Drive_Image_Handler
{
	private static $instance = null;

	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Extract Google Drive file ID from a sharing URL.
	 */
	public function extract_drive_file_id_from_url($drive_url)
	{
		if (empty($drive_url) || !is_string($drive_url)) {
			return null;
		}

		if (strpos($drive_url, 'drive.google.com/file/d/') !== false) {
			if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $drive_url, $matches)) {
				return $matches[1];
			}
		}

		if (strpos($drive_url, 'drive.google.com/open?id=') !== false) {
			if (preg_match('/id=([a-zA-Z0-9_-]+)/', $drive_url, $matches)) {
				return $matches[1];
			}
		}

		return null;
	}

	/**
	 * Extract Drive file ID from an uploaded image path (drive_{file_id}.ext).
	 */
	public function extract_drive_file_id_from_image_path($image_path)
	{
		if (empty($image_path) || !is_string($image_path)) {
			return null;
		}

		if (preg_match('/drive_([a-zA-Z0-9_-]+)\.(jpg|jpeg|png|gif|webp|bmp)$/i', $image_path, $matches)) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Find an attachment post by stored Google Drive file ID.
	 */
	public function get_attachment_by_drive_id($file_id)
	{
		$existing = get_posts([
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'meta_query'     => [
				[
					'key'     => '_drive_file_id',
					'value'   => $file_id,
					'compare' => '=',
				],
			],
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'DESC',
		]);

		return !empty($existing) ? (int) $existing[0]->ID : false;
	}

	/**
	 * Check whether the attachment file exists on disk.
	 */
	public function attachment_file_exists_on_disk($attachment_id)
	{
		$file = get_attached_file($attachment_id);

		return !empty($file) && file_exists($file);
	}

	/**
	 * Import or reuse a Drive image. Always reuses existing attachment when possible.
	 */
	public function import_image_from_drive($drive_url, $return_type = 'id')
	{
		$cache_key = 'drive_image_' . md5($drive_url);
		$cached_result = wp_cache_get($cache_key);

		if ($cached_result !== false) {
			return $cached_result;
		}

		$file_id = $this->extract_drive_file_id_from_url($drive_url);

		if (!$file_id) {
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		$existing_attachment = $this->get_attachment_by_drive_id($file_id);

		if ($existing_attachment) {
			if ($this->attachment_file_exists_on_disk($existing_attachment)) {
				$result = $return_type === 'url'
					? wp_get_attachment_url($existing_attachment)
					: $existing_attachment;

				wp_cache_set($cache_key, $result, '', 3600);
				return $result;
			}

			// File missing on disk: restore to original path instead of deleting attachment.
			if ($this->restore_attachment_file_from_drive($existing_attachment, $file_id, $drive_url)) {
				$result = $return_type === 'url'
					? wp_get_attachment_url($existing_attachment)
					: $existing_attachment;

				wp_cache_set($cache_key, $result, '', 3600);
				return $result;
			}
		}

		$attachment_id = $this->download_and_create_drive_attachment($drive_url, $file_id);

		if (!$attachment_id) {
			wp_cache_set($cache_key, false, '', 3600);
			return false;
		}

		$result = $return_type === 'url'
			? wp_get_attachment_url($attachment_id)
			: $attachment_id;

		wp_cache_set($cache_key, $result, '', 3600);

		return $result;
	}

	/**
	 * Resolve the canonical attachment URL for a stored image path/URL.
	 */
	public function resolve_canonical_image_url($image_url)
	{
		if (empty($image_url) || !is_string($image_url)) {
			return null;
		}

		$drive_file_id = $this->extract_drive_file_id_from_image_path($image_url);

		if ($drive_file_id) {
			$attachment_id = $this->get_attachment_by_drive_id($drive_file_id);

			if ($attachment_id && $this->attachment_file_exists_on_disk($attachment_id)) {
				return wp_get_attachment_url($attachment_id);
			}
		}

		$full_url = $this->normalize_image_url($image_url);
		$attachment_id = attachment_url_to_postid($full_url);

		if ($attachment_id && $this->attachment_file_exists_on_disk($attachment_id)) {
			return wp_get_attachment_url($attachment_id);
		}

		return null;
	}

	/**
	 * Migrate broken or outdated USP image paths on all products.
	 */
	public function migrate_product_feature_image_paths($dry_run = false)
	{
		$results = [
			'scanned_products'  => 0,
			'updated_products'  => 0,
			'updated_images'    => 0,
			'unchanged_images'  => 0,
			'unresolved_images' => 0,
			'dry_run'           => (bool) $dry_run,
			'details'           => [],
		];

		$products = get_posts([
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		]);

		foreach ($products as $product_id) {
			$features = get_post_meta($product_id, '_custom_product_features', true);

			if (!is_array($features) || empty($features)) {
				continue;
			}

			$results['scanned_products']++;
			$changed = false;

			foreach ($features as $index => $feature) {
				if (empty($feature['image']) || !is_string($feature['image'])) {
					continue;
				}

				$current_url = $feature['image'];
				$canonical_url = $this->resolve_canonical_image_url($current_url);

				if (empty($canonical_url)) {
					$results['unresolved_images']++;
					$results['details'][] = [
						'product_id'    => $product_id,
						'product_title' => get_the_title($product_id),
						'feature_index' => $index,
						'old_url'       => $current_url,
						'new_url'       => null,
						'status'        => 'unresolved',
					];
					continue;
				}

				if ($this->normalize_image_url($current_url) === $this->normalize_image_url($canonical_url)) {
					$results['unchanged_images']++;
					continue;
				}

				$results['updated_images']++;
				$changed = true;

				$results['details'][] = [
					'product_id'    => $product_id,
					'product_title' => get_the_title($product_id),
					'feature_index' => $index,
					'old_url'       => $current_url,
					'new_url'       => $canonical_url,
					'status'        => $dry_run ? 'would_update' : 'updated',
				];

				if (!$dry_run) {
					$features[$index]['image'] = $canonical_url;
				}
			}

			if ($changed && !$dry_run) {
				update_post_meta($product_id, '_custom_product_features', $features);

				if (function_exists('update_field')) {
					update_field('_custom_product_features', $features, $product_id);
				}

				$results['updated_products']++;
			} elseif ($changed && $dry_run) {
				$results['updated_products']++;
			}
		}

		return $results;
	}

	/**
	 * Re-download a Drive file and restore it to the attachment's original path.
	 */
	private function restore_attachment_file_from_drive($attachment_id, $file_id, $drive_url)
	{
		$relative_path = get_post_meta($attachment_id, '_wp_attached_file', true);

		if (empty($relative_path)) {
			return false;
		}

		$tmp = $this->download_drive_file_to_temp($file_id);

		if (is_wp_error($tmp)) {
			return false;
		}

		$upload_dir = wp_upload_dir();
		$dest_path = $upload_dir['basedir'] . '/' . $relative_path;
		$dest_dir = dirname($dest_path);

		if (!wp_mkdir_p($dest_dir)) {
			@unlink($tmp);
			return false;
		}

		if (!@copy($tmp, $dest_path)) {
			@unlink($tmp);
			return false;
		}

		@unlink($tmp);

		require_once ABSPATH . 'wp-admin/includes/image.php';

		$metadata = wp_generate_attachment_metadata($attachment_id, $dest_path);
		wp_update_attachment_metadata($attachment_id, $metadata);

		return true;
	}

	/**
	 * Download a new Drive image and create a media library attachment.
	 */
	private function download_and_create_drive_attachment($drive_url, $file_id)
	{
		$tmp = $this->download_drive_file_to_temp($file_id);

		if (is_wp_error($tmp)) {
			return false;
		}

		$filename = $this->get_filename_from_drive_url($drive_url, $file_id);

		if (empty($filename) || !$this->is_valid_image($filename)) {
			@unlink($tmp);
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$file_array = [
			'name'     => $filename,
			'tmp_name' => $tmp,
		];

		$attachment_id = media_handle_sideload($file_array, 0);

		if (is_wp_error($attachment_id)) {
			@unlink($tmp);
			return false;
		}

		update_post_meta($attachment_id, '_drive_file_id', $file_id);

		return (int) $attachment_id;
	}

	/**
	 * Download a Drive file to a temporary path.
	 */
	private function download_drive_file_to_temp($file_id)
	{
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$direct_url = 'https://drive.google.com/uc?export=download&id=' . $file_id;

		return download_url($direct_url, 30);
	}

	/**
	 * Build a predictable filename for Drive images.
	 */
	private function get_filename_from_drive_url($drive_url, $file_id)
	{
		if (preg_match('/[^\/\?]+\.(jpg|jpeg|png|gif|webp|bmp)$/i', $drive_url, $matches)) {
			return sanitize_file_name($matches[0]);
		}

		return 'drive_' . $file_id . '.jpg';
	}

	/**
	 * Validate image file extension.
	 */
	private function is_valid_image($filename)
	{
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$valid_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

		return in_array($ext, $valid_exts, true);
	}

	/**
	 * Normalize relative or absolute image URLs for comparison.
	 */
	private function normalize_image_url($image_url)
	{
		$image_url = trim($image_url);

		if (strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0) {
			return untrailingslashit($image_url);
		}

		return untrailingslashit(home_url($image_url));
	}
}

/**
 * Migrate USP feature image paths across all products.
 *
 * @param bool $dry_run When true, only reports changes without saving.
 * @return array
 */
function mamma_mia_migrate_product_feature_image_paths($dry_run = false)
{
	return Mamma_Mia_Drive_Image_Handler::instance()->migrate_product_feature_image_paths($dry_run);
}
