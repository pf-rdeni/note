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

    <!-- Side Card (Informasi & Pengaturan Grup, Tambah Anggota) -->
    <div class="col-lg-4">
        <!-- Informasi & Pengaturan Grup -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-info-circle mr-1"></i> Informasi Grup
                </h3>
            </div>
            
            <?php if ($currentMembership['role'] === 'admin'): ?>
                <form action="<?= base_url('backend/groups/update/' . $group['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <?php if (session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['name'])): ?>
                            <div class="alert alert-danger p-2 mb-3 small">
                                <i class="fas fa-exclamation-circle mr-1"></i> <?= session()->getFlashdata('errors')['name'] ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="name">Nama Grup</label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control <?= (session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['name'])) ? 'is-invalid' : '' ?>" 
                                   value="<?= old('name', esc($group['name'])) ?>" 
                                   required 
                                   placeholder="Masukkan nama grup">
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="text-muted small">
                            <p class="mb-2"><i class="fas fa-user mr-1 text-secondary"></i> <strong>Dibuat oleh:</strong> <?= esc($group['creator_name'] ?? 'Tidak diketahui') ?></p>
                            <p class="mb-2"><i class="fas fa-calendar-alt mr-1 text-secondary"></i> <strong>Dibuat pada:</strong> <?= date('d M Y, H:i', strtotime($group['created_at'])) ?></p>
                            <?php if (!empty($group['updated_at'])): ?>
                                <p class="mb-0"><i class="fas fa-edit mr-1 text-secondary"></i> <strong>Diperbarui pada:</strong> <?= date('d M Y, H:i', strtotime($group['updated_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-block mt-2 btn-delete-group" data-id="<?= $group['id'] ?>">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus Grup
                        </button>
                    </div>
                </form>
                <form id="delete-group-form" action="<?= base_url('backend/groups/delete/' . $group['id']) ?>" method="post" style="display:none;">
                    <?= csrf_field() ?>
                </form>
            <?php else: ?>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Grup</label>
                        <p class="form-control-plaintext font-weight-bold text-lg mb-0"><?= esc($group['name']) ?></p>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="text-muted small">
                        <p class="mb-2"><i class="fas fa-user mr-1 text-secondary"></i> <strong>Dibuat oleh:</strong> <?= esc($group['creator_name'] ?? 'Tidak diketahui') ?></p>
                        <p class="mb-2"><i class="fas fa-calendar-alt mr-1 text-secondary"></i> <strong>Dibuat pada:</strong> <?= date('d M Y, H:i', strtotime($group['created_at'])) ?></p>
                        <?php if (!empty($group['updated_at'])): ?>
                            <p class="mb-0"><i class="fas fa-edit mr-1 text-secondary"></i> <strong>Diperbarui pada:</strong> <?= date('d M Y, H:i', strtotime($group['updated_at'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Member Side Card (Admin Only) -->
        <?php if ($currentMembership['role'] === 'admin'): ?>
            <div class="card card-success card-outline mt-3">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-user-plus mr-1"></i>Tambah Anggota</h3>
                </div>
                <form action="<?= base_url('backend/groups/add-member/' . $group['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <?php if (empty($availableUsers)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle mr-1"></i> Semua pengguna sudah bergabung ke grup ini.
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
                                    <option value="admin">Group Admin (Dapat edit kegiatan, kelola anggota, & tandai lunas)</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <?php if (!empty($availableUsers)): ?>
                            <button type="submit" class="btn btn-success btn-block mb-2">Tambah ke Group</button>
                        <?php endif; ?>
                        <a href="<?= base_url('backend/groups') ?>" class="btn btn-default btn-block">Kembali</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="mt-3">
                <a href="<?= base_url('backend/groups') ?>" class="btn btn-default btn-block">Kembali</a>
            </div>
        <?php endif; ?>
    </div>
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

    // Konfirmasi hapus grup
    $('.btn-delete-group').on('click', function() {
        const groupId = $(this).data('id');
        
        Swal.fire({
            title: 'Mempersiapkan pratinjau...',
            text: 'Sedang menghitung data yang terpengaruh...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `<?= base_url('backend/groups/delete-preview') ?>/${groupId}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                Swal.close();
                if (res.success) {
                    Swal.fire({
                        title: 'Hapus Grup Permanen?',
                        html: `
                            <div class="text-left border p-3 rounded mb-3 bg-light" style="font-size: 0.9rem;">
                                <p class="mb-2 text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Tindakan ini tidak dapat dibatalkan!</p>
                                <p class="mb-2">Menghapus grup <strong>${res.group_name}</strong> juga akan menghapus secara permanen data berikut:</p>
                                <ul class="pl-4 mb-0">
                                    <li><strong>${res.members}</strong> Anggota Grup</li>
                                    <li><strong>${res.trips}</strong> Kegiatan</li>
                                    <li><strong>${res.periods}</strong> Periode Pengeluaran</li>
                                    <li><strong>${res.transactions}</strong> Catatan Transaksi</li>
                                    <li><strong>${res.settlements}</strong> Riwayat Settlement</li>
                                    <li><strong>${res.files}</strong> Lampiran Nota/Bukti Transfer</li>
                                </ul>
                            </div>
                            <span class="text-dark">Ketik kata <strong>HAPUS</strong> untuk mengonfirmasi tindakan ini:</span>
                            <input type="text" id="confirm-delete-text" class="form-control mt-2 text-center text-bold" placeholder="Ketik HAPUS di sini" style="text-transform: uppercase;">
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus Semua!',
                        cancelButtonText: 'Batal',
                        preConfirm: () => {
                            const confirmText = Swal.getPopup().querySelector('#confirm-delete-text').value;
                            if (confirmText.trim().toUpperCase() !== 'HAPUS') {
                                Swal.showValidationMessage('Anda harus mengetik kata HAPUS untuk melanjutkan!');
                            }
                            return true;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Sedang menghapus...',
                                text: 'Mohon tunggu sementara kami membersihkan data...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            $('#delete-group-form').submit();
                        }
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal mengambil pratinjau.', 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire('Gagal', 'Terjadi kesalahan pada server saat memuat pratinjau.', 'error');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
