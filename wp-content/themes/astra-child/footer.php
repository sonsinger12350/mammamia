<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<?php astra_content_bottom(); ?>
	</div> <!-- ast-container -->
	</div><!-- #content -->
<?php
	astra_content_after();

	astra_footer_before();

	astra_footer();

	astra_footer_after();
?>
	</div><!-- #page -->
<?php
	astra_body_bottom();
	wp_footer();
?>
	</body>
	<?php 
		include get_stylesheet_directory() . '/modals.php';
		$email = get_option('website_config_email', '');
		$phone = get_option('website_config_phone', '');
	?>
	<div class="footer-button">
		<a class="btn-scroll-top" href="javascript:void(0)" style="display: none;" rel="nofollow">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				<image x="0px" y="0px" xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAAD0AAAAkCAMAAAANZcKIAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAh1BMVEUGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKykGKyn///9GrhIYAAAAK3RSTlMAH5zpP+9C9kFAN/H3kUMq3Cc2HDMt6yDkxiQbOOIxMAz5Xmb7WLL6sB4rM3TfDQAAAAFiS0dELLrdcasAAAAHdElNRQfpBxMTOAuTPrzRAAAA7UlEQVRIx6XVyRKCMBAE0FFAVMANF1BUwAWU//8/41aVhCwz0Kd017xzAPQZDJ3GGQ6gU1yvecdzu+CR33zjj3rgLpzDdD7mMeMTCp4GjZhg2gNTuALjuRJjuQbjuBZjuAHbuRHbuAWbuYTDaDafRSGSS3ix/KzLBYqvJBz/9ljiaxXebNW4xbcbCkZwE7ZyM7ZwGzZyOzZwDNZyHNbwnYPD7G9JhEtnx7YUiwH2Ik8BDnjc4gfI+Ho0Y5ln4HIt2YMtAnfhRMIiP0NekDDPixygpGGOl+92uZLwn4e3bzvdw6p+oDHAo678+skeL07EiKL8vE2bAAAAAElFTkSuQmCC" />
			</svg>
			<?= __('Đầu trang', 'astra-child'); ?>
		</a>
		<?php if($phone): ?>
			<a class="btn-contact-phone" href="tel:<?php echo $phone; ?>">
				<span><?php echo $phone; ?></span>
				<i class="fa fa-phone" aria-hidden="true"></i>
			</a>
		<?php endif; ?>
		<?php if($email): ?>
			<a class="btn-contact-email" href="mailto:<?php echo $email; ?>" aria-label="Email">
				<i class="fa fa-envelope" aria-hidden="true"></i>
			</a>
		<?php endif; ?>
	</div>
	<div class="footer-contact-form">
		<button class="show-footer-contact-form" type="button">Liên hệ tư vấn <i class="fa fa-angle-up" aria-hidden="true"></i></button>
		<div class="contact-form-content">
			<?= do_shortcode('[contact-form-7 id="4a8e70b" title="Form liên hệ 1"]') ?>
		</div>
	</div>
</html>
