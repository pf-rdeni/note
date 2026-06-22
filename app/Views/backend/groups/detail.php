<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<!-- Alert Flash Data -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle mr-2"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- List Members Card -->
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-users mr-1"></i> Anggota Kelompok
                </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status Keaktifan</th>
                                <th class="text-center" style="width: 160px;">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $m): ?>
                                <tr>
                                    <td class="align-middle">
                                        <i class="fas fa-user text-muted mr-2"></i><?= esc($m['username']) ?>
                                    </td>
                                    <td class="align-middle"><?= esc($m['email']) ?></td>
                                    <td class="align-middle">
                                        <?php if ($m['role'] === 'admin'): ?>
                                            <span class="badge badge-warning">Group Admin</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Member</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($m['is_active']): ?>
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($currentMembership['role'] === 'admin'): ?>
                                        <td class="text-center align-middle">
                                            <?php 
                                            // Cek apakah ini user sendiri (current admin)
                                            $isSelf = ((int)$m['user_id'] === (int)user_id());

                                            // Cek apakah boleh hapus
                                            $canRemove = true;
                                            if ($isSelf) {
                                                $adminCount = 0;
                                                foreach ($members as $mem) {
                                                    if ($mem['role'] === 'admin') $adminCount++;
                                                }
                                                if ($adminCount <= 1) $canRemove = false;
                                            }

                                            // Cek apakah boleh ganti role:
                                            // Tidak bisa mengubah diri sendiri jika satu-satunya admin
                                            $canChangeRole = !($isSelf && $m['role'] === 'admin' && !$canRemove);
                                            ?>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($canChangeRole && !$isSelf): ?>
                                                    <?php if ($m['role'] === 'admin'): ?>
                                                        <a href="<?= base_url('backend/groups/update-role/' . $group['id'] . '/' . $m['user_id'] . '/member') ?>" 
                                                           class="btn btn-warning btn-change-role"
                                                           title="Turunkan ke Member"
                                                           data-name="<?= esc($m['username']) ?>"
                                                           data-new-role="Member"
                                                           data-current-role="Admin">
                                                            <i class="fas fa-user-minus"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('backend/groups/update-role/' . $group['id'] . '/' . $m['user_id'] . '/admin') ?>" 
                                                           class="btn btn-info btn-change-role"
                                                           title="Jadikan Admin"
                                                           data-name="<?= esc($m['username']) ?>"
                                                           data-new-role="Admin"
                                                           data-current-role="Member">
                                                            <i class="fas fa-user-shield"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if ($canRemove): ?>
                                                    <a href="<?= base_url('backend/groups/remove-member/' . $group['id'] . '/' . $m['user_id']) ?>" 
                                                       class="btn btn-danger btn-delete-member" 
                                                       title="Keluarkan dari Grup"
                                                       data-name="<?= esc($m['username']) ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small" title="Tidak bisa dihapus">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
    </div>

    <!-- Add Member Side Card (Admin Only) -->
    <?php if ($currentMembership['role'] === 'admin'): ?>
        <div class="col-lg-4">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-user-plus mr-1"></i>Tambah Anggota</h3>
                </div>
                <form action="<?= base_url('backend/groups/add-member/' . $group['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <?php if (empty($availableUsers)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle mr-1"></i> Semua pengguna yang terdaftar di aplikasi sudah ditambahkan ke grup ini.
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <label for="user_id">Pilih Pengguna</label>
                                <select class="form-control select2" style="width: 100%;" id="user_id" name="user_id" required>
                                    <option value="" disabled selected>-- Cari Pengguna --</option>
                                    <?php foreach ($availableUsers as $au): ?>
                                        <option value="<?= $au['id'] ?>"><?= esc($au['username']) ?> (<?= esc($au['email']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="role">Hak Akses Grup</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="member" selected>Member (Hanya input/lihat transaksi)</option>
                                    <option value="admin">Group Admin (Dapat edit trip, kelola anggota, & tandai lunas)</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <?php if (!empty($availableUsers)): ?>
                            <button type="submit" class="btn btn-success btn-block">Tambah ke Group</button>
                        <?php endif; ?>
                        <a href="<?= base_url('backend/groups') ?>" class="btn btn-default btn-block">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize Select2 Elements
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Konfirmasi ganti role
    $('.btn-change-role').on('click', function(e) {
        e.preventDefault();
        const url         = $(this).attr('href');
        const name        = $(this).data('name');
        const newRole     = $(this).data('new-role');
        const currentRole = $(this).data('current-role');
        const isUpgrade   = (newRole === 'Admin');

        Swal.fire({
            title: isUpgrade ? 'Jadikan Admin?' : 'Turunkan ke Member?',
            html: `Apakah Anda yakin ingin mengubah role <strong>${name}</strong> dari <em>${currentRole}</em> menjadi <strong>${newRole}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isUpgrade ? '#17a2b8' : '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `<i class="fas fa-check mr-1"></i> Ya, Ubah Role!`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    // Konfirmasi hapus member
    $('.btn-delete-member').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Keluarkan dari Grup?',
            text: `Apakah Anda yakin ingin mengeluarkan "${name}" dari grup ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Keluarkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
