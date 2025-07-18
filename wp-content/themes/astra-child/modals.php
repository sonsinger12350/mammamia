<!-- Modal Login -->
<div id="modal-login" class="modal custom-modal" tabindex="-1">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="text-end"><i class="fa fa-times-circle" data-bs-dismiss="modal"></i></div>
				<p class="modal-title"><?= __('Đăng nhập', 'astra-child') ?></p>
				<p class="modal-description"><?= __('Để tải tài liệu, vui lòng đăng nhập hoặc đăng ký.', 'astra-child') ?></p>
				<div class="form-login-register">
					<form class="form-login" novalidate name="login_user" data-nonce="<?= wp_create_nonce('login_user') ?>">
						<p class="form-title"><?= __('Đăng nhập', 'astra-child') ?></p>
						<div class="form-group mb-3">
							<label for="email"><?= __('Email', 'astra-child') ?></label>
							<input class="form-control" type="email" name="email" id="email" placeholder="Nhập email" required>
						</div>
						<div class="form-group mb-3">
							<label for="password"><?= __('Mật khẩu', 'astra-child') ?></label>
							<input class="form-control" type="password" name="password" id="password" placeholder="Nhập mật khẩu" required>
						</div>
						<button type="submit" class="btn btn-outline-primary mb-2 mt-3"><?= __('Đăng nhập', 'astra-child') ?></button>
						<div class="form-forgot">
							Quên mật khẩu? <a href="javascript:void(0)"><?= __('Nhấp vào đây', 'astra-child') ?></a>
						</div>
					</form>
					<form class="form-register" novalidate name="register_user" data-nonce="<?= wp_create_nonce('register_user') ?>">
						<p class="form-title"><?= __('Đăng ký', 'astra-child') ?></p>
						<div class="form-group mb-3">
							<label for="email"><?= __('Email', 'astra-child') ?></label>
							<input class="form-control" type="email" name="email" id="email" placeholder="Nhập email" required>
						</div>
						<div class="form-group mb-3">
							<label for="password"><?= __('Mật khẩu', 'astra-child') ?></label>
							<input class="form-control" type="password" name="password" id="password" placeholder="Nhập mật khẩu" required>
						</div>
						<div class="form-group mb-3">
							<label for="confirm-password"><?= __('Nhập lại mật khẩu', 'astra-child') ?></label>
							<input class="form-control" type="password" name="confirm_password" id="confirm-password" placeholder="Nhập lại mật khẩu" required>
						</div>
						<button type="submit" class="btn btn-outline-primary mt-3"><?= __('Đăng ký', 'astra-child') ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal Contact Download -->
<?php
	$zalo = get_option('website_config_zalo', '');
	$size = 200;
	$qr_url = "https://api.qrserver.com/v1/create-qr-code/?data=".urlencode($zalo)."&size=200x200";
?>
<div id="modal-contact-download" class="modal custom-modal" tabindex="-1">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="text-end"><i class="fa fa-times-circle" data-bs-dismiss="modal"></i></div>
				<p class="modal-title"><?= __('liên hệ để nhận tài liệu', 'astra-child') ?></p>
				<p class="modal-description"><?= __('Vui lòng liên hệ trực tiếp với chúng tôi qua thông tin bên dưới để nhận được tài liệu chi tiết.', 'astra-child') ?></p>
				<img src="<?= $qr_url ?>" alt="QR Zalo">
				<a href="<?= $zalo ?>" target="_blank"><?= $zalo ?></a>
			</div>
		</div>
	</div>
</div>