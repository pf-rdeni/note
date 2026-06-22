<?= $this->extend($config->viewLayout) ?>
<?= $this->section('main') ?>

<div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
        <div class="card-header text-center py-4">
            <a href="<?= base_url() ?>" class="h2 font-weight-bold text-dark">
                <i class="fas fa-wallet text-primary mr-2"></i><b>Split Bill</b>
            </a>
            <p class="text-muted mb-0 mt-1">Sign in to manage your balances</p>
        </div>
        <div class="card-body login-card-body">
            
            <?= view('App\Views\Auth\_message_block') ?>

            <form action="<?= url_to('login') ?>" method="post">
                <?= csrf_field() ?>

                <?php if ($config->validFields === ['email']): ?>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control <?php if (session('errors.login')) : ?>is-invalid<?php endif ?>"
                               name="login" placeholder="<?=lang('Auth.email')?>" value="<?= old('login') ?>" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        <div class="invalid-feedback">
                            <?= session('errors.login') ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control <?php if (session('errors.login')) : ?>is-invalid<?php endif ?>"
                               name="login" placeholder="<?=lang('Auth.emailOrUsername')?>" value="<?= old('login') ?>" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                        <div class="invalid-feedback">
                            <?= session('errors.login') ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="input-group mb-3">
                    <input type="password" name="password" id="passwordInput" class="form-control <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>" 
                           placeholder="<?=lang('Auth.password')?>" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" style="border: 1px solid #ced4da; border-left: none; background-color: #fff; color: #495057; border-top-right-radius: .25rem; border-bottom-right-radius: .25rem;">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">
                        <?= session('errors.password') ?>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-8">
                        <?php if ($config->allowRemembering): ?>
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" name="remember" <?php if (old('remember')) : ?> checked <?php endif ?>>
                                <label for="remember" style="font-weight: normal; user-select: none;">
                                    <?=lang('Auth.rememberMe')?>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block"><?=lang('Auth.loginAction')?></button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <?php if ($config->allowRegistration) : ?>
                    <p class="mb-1">
                        <a href="<?= url_to('register') ?>" class="text-sm"><?=lang('Auth.needAnAccount')?></a>
                    </p>
                <?php endif; ?>
                <?php if ($config->activeResetter): ?>
                    <p class="mb-0">
                        <a href="<?= url_to('forgot') ?>" class="text-sm"><?=lang('Auth.forgotYourPassword')?></a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
<!-- /.login-box -->

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
$(document).ready(function() {
    $('#togglePasswordBtn').on('click', function() {
        const passwordInput = $('#passwordInput');
        const icon = $('#togglePasswordIcon');
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});
</script>
<?= $this->endSection() ?>
