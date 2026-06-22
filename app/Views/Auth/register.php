<?= $this->extend($config->viewLayout) ?>
<?= $this->section('main') ?>

<div class="register-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center py-4">
            <a href="<?= base_url() ?>" class="h2 font-weight-bold text-dark">
                <i class="fas fa-wallet text-primary mr-2"></i><b>Split Bill</b>
            </a>
            <p class="text-muted mb-0 mt-1">Register a new membership</p>
        </div>
        <div class="card-body register-card-body">
            
            <?= view('App\Views\Auth\_message_block') ?>

            <form action="<?= url_to('register') ?>" method="post">
                <?= csrf_field() ?>

                <div class="input-group mb-3">
                    <input type="email" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>"
                           name="email" placeholder="<?=lang('Auth.email')?>" value="<?= old('email') ?>" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        <?= session('errors.email') ?>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="text" class="form-control <?php if (session('errors.username')) : ?>is-invalid<?php endif ?>" 
                           name="username" placeholder="<?=lang('Auth.username')?>" value="<?= old('username') ?>" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        <?= session('errors.username') ?>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>" 
                           placeholder="<?=lang('Auth.password')?>" autocomplete="off" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        <?= session('errors.password') ?>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="pass_confirm" class="form-control <?php if (session('errors.pass_confirm')) : ?>is-invalid<?php endif ?>" 
                           placeholder="<?=lang('Auth.repeatPassword')?>" autocomplete="off" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        <?= session('errors.pass_confirm') ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block"><?=lang('Auth.register')?></button>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <a href="<?= url_to('login') ?>" class="text-sm"><?=lang('Auth.alreadyRegistered')?> <?=lang('Auth.signIn')?></a>
            </div>
        </div>
        <!-- /.form-box -->
    </div><!-- /.card -->
</div>
<!-- /.register-box -->

<?= $this->endSection() ?>
