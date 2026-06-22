<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<!-- Alert Flash Data -->
<div class="row">
    <div class="col-12">
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="icon fas fa-check mr-2"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="icon fas fa-ban mr-2"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('errors')) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="icon fas fa-ban mr-2"></i>
                <ul class="mb-0 pl-3">
                    <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Left Column: User Card -->
    <div class="col-lg-4 mb-4">
        <div class="card card-primary card-outline shadow-sm text-center py-4">
            <div class="card-body">
                <!-- Avatar Display -->
                <div class="d-flex justify-content-center mb-3">
                    <?php if (!empty($user->user_image) && file_exists(FCPATH . $user->user_image)): ?>
                        <img src="<?= base_url($user->user_image) . '?t=' . time() ?>" 
                             id="profileCardImage" 
                             class="img-circle elevation-2 shadow-xs" 
                             alt="User Image" 
                             style="width: 110px; height: 110px; object-fit: cover; border: 3px solid #dee2e6;">
                    <?php else: ?>
                        <!-- Initials Fallback -->
                        <?php
                        $sourceName = !empty($user->fullname) ? $user->fullname : $user->username;
                        $textOnly = preg_replace('/[0-9]+$/', '', $sourceName);
                        $textOnly = str_replace(['_', '.', '-'], ' ', $textOnly);
                        $words    = preg_split('/\s+/', trim($textOnly), -1, PREG_SPLIT_NO_EMPTY);
                        $initials = '';
                        if (count($words) >= 2) {
                            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                        } elseif (!empty($words)) {
                            $initials = strtoupper(substr($words[0], 0, 2));
                        } else {
                            $initials = 'US';
                        }
                        ?>
                        <div id="profileCardInitials" 
                             class="img-circle elevation-2 d-flex justify-content-center align-items-center bg-info text-white font-weight-bold shadow-xs" 
                             style="width: 110px; height: 110px; font-size: 2.5rem; user-select: none; border: 3px solid #dee2e6; margin: 0 auto;">
                            <?= $initials ?>
                        </div>
                        <img src="" 
                             id="profileCardImage" 
                             class="img-circle elevation-2 shadow-xs d-none" 
                             alt="User Image" 
                             style="width: 110px; height: 110px; object-fit: cover; border: 3px solid #dee2e6;">
                    <?php endif; ?>
                </div>

                <h4 class="font-weight-bold mb-1 text-dark"><?= esc(!empty($user->fullname) ? $user->fullname : $user->username) ?></h4>
                <p class="text-muted text-sm mb-3">@<?= esc($user->username) ?></p>

                <hr class="my-3">

                <!-- Account Meta Details -->
                <div class="text-left px-2">
                    <div class="mb-2">
                        <small class="text-muted d-block">Alamat Email</small>
                        <span class="font-weight-bold text-dark text-break"><i class="far fa-envelope mr-1 text-secondary"></i> <?= esc($user->email) ?></span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Status Akun</small>
                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Aktif</span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Bergabung Sejak</small>
                        <span class="text-secondary text-xs"><i class="far fa-calendar-alt mr-1"></i> <?= date('d F Y', strtotime($user->created_at)) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Profile Update Form -->
    <div class="col-lg-8">
        <form action="<?= base_url('backend/profil/update') ?>" method="post" enctype="multipart/form-data" id="profileForm">
            <?= csrf_field() ?>
            <!-- Card 1: Informasi Profil -->
            <div class="card card-primary card-outline shadow-sm mb-3">
                <div class="card-header py-3">
                    <h3 class="card-title font-weight-bold text-primary">
                        <i class="fas fa-user-edit mr-1"></i> Informasi Akun
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="username">Username <small class="text-muted">(Tidak dapat diubah)</small></label>
                            <input type="text" id="username" class="form-control" value="<?= esc($user->username) ?>" disabled>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="email">Email <small class="text-muted">(Tidak dapat diubah)</small></label>
                            <input type="email" id="email" class="form-control" value="<?= esc($user->email) ?>" disabled>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="fullname">Nama Lengkap</label>
                        <input type="text" name="fullname" id="fullname" class="form-control" 
                               placeholder="Masukkan nama lengkap Anda" 
                               value="<?= esc($user->fullname ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Card 2: Foto Profil -->
            <div class="card card-info card-outline shadow-sm mb-3">
                <div class="card-header py-3">
                    <h3 class="card-title font-weight-bold text-info">
                        <i class="fas fa-image mr-1"></i> Foto Profil
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label>Unggah Foto Baru</label>
                        
                        <!-- Drag & Drop Upload Area -->
                        <div class="receipt-upload-area text-center" id="avatarDropArea">
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" class="d-none">
                            
                            <!-- Custom Dual Button UI (Senada dengan Struk Upload) -->
                            <div class="receipt-upload-actions justify-content-center">
                                <label for="avatarInput" class="btn-gallery mb-0 w-100" style="max-width: 250px;">
                                    <i class="fas fa-folder-open text-primary"></i>
                                    <span>Pilih Gambar</span>
                                </label>
                            </div>
                            
                            <!-- Preview Area -->
                            <div class="receipt-preview-container mt-2" id="avatarPreviewContainer">
                                <button type="button" class="btn-remove-receipt" id="btnRemoveAvatar" title="Batalkan Pilihan">
                                    <i class="fas fa-times"></i>
                                </button>
                                <img src="" id="avatarPreviewImg" class="receipt-preview-img shadow-xs img-circle" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            
                            <p class="text-xs text-muted mt-2 mb-0">Format: JPG, PNG, WEBP (Maks 2MB)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Keamanan / Ubah Password -->
            <div class="card card-warning card-outline shadow-sm mb-4">
                <div class="card-header py-3">
                    <h3 class="card-title font-weight-bold text-warning">
                        <i class="fas fa-lock mr-1"></i> Keamanan / Ubah Password
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted text-xs mb-3"><i class="fas fa-info-circle"></i> Biarkan kosong jika Anda tidak ingin mengubah password saat ini.</p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="password">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 8 karakter">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label for="pass_confirm">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="pass_confirm" id="pass_confirm" class="form-control" placeholder="Ulangi password baru">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="pass_confirm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Action Submit Buttons -->
            <div class="row mb-5">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm" style="height: 48px; border-radius: 8px;">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    const avatarInput = $('#avatarInput');
    const avatarPreviewContainer = $('#avatarPreviewContainer');
    const avatarPreviewImg = $('#avatarPreviewImg');
    const btnRemoveAvatar = $('#btnRemoveAvatar');
    const dropArea = $('#avatarDropArea');

    // 1. Live Preview when choosing file
    avatarInput.on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (!file.type.match('image.*')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Hanya file gambar yang diperbolehkan!'
                });
                avatarInput.val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(evt) {
                avatarPreviewImg.attr('src', evt.target.result);
                avatarPreviewContainer.fadeIn(150);
            };
            reader.readAsDataURL(file);
        }
    });

    // 2. Remove chosen file
    btnRemoveAvatar.on('click', function() {
        avatarInput.val('');
        avatarPreviewContainer.fadeOut(150, function() {
            avatarPreviewImg.attr('src', '');
        });
    });

    // 3. Toggle Password Visibility
    $('.toggle-pass').on('click', function() {
        const btn = $(this);
        const targetId = btn.data('target');
        const input = $('#' + targetId);
        const icon = btn.find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // 4. Drag and Drop Support
    dropArea.on('dragover', function(e) {
        e.preventDefault();
        dropArea.addClass('dragover');
    });

    dropArea.on('dragleave', function(e) {
        e.preventDefault();
        dropArea.removeClass('dragover');
    });

    dropArea.on('drop', function(e) {
        e.preventDefault();
        dropArea.removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            avatarInput[0].files = files;
            avatarInput.trigger('change');
        }
    });
});
</script>
<?= $this->endSection() ?>
