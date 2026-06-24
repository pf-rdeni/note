<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<div class="row">
    <!-- Trip Info Sidebar -->
    <div class="col-lg-4">
        <?php if (session()->getFlashdata('trip_errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3 small" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i> <strong>Gagal memperbarui kegiatan:</strong>
                <ul class="pl-3 mb-0 mt-1">
                    <?php foreach (session()->getFlashdata('trip_errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>

        <?php if ($currentMembership['role'] === 'admin'): ?>
            <form action="<?= base_url('backend/trips/update/' . $trip['id']) ?>" method="post">
                <?= csrf_field() ?>
        <?php endif; ?>

        <div id="card-detail-kegiatan" class="card card-primary card-outline collapsed-card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold" style="float: none; margin-bottom: 0;">
                    <i class="fas fa-clipboard-list mr-1"></i> Detail Kegiatan
                </h3>
                <div class="card-tools" style="float: right;">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" data-expand-icon="fa-eye" data-collapse-icon="fa-eye-slash">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="text-muted mt-1" style="clear: both; font-size: 0.85rem;">
                    <span class="mr-2"><i class="fas fa-users mr-1"></i> Kelompok: <strong><?= esc($group['name']) ?></strong></span>
                    <?php if ($trip['start_date']): ?>
                        <span>| <i class="far fa-calendar-alt ml-1 mr-1"></i> 
                            <?= date('d M Y', strtotime($trip['start_date'])) ?>
                            <?php if ($trip['end_date']): ?>
                                s/d <?= date('d M Y', strtotime($trip['end_date'])) ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($currentMembership['role'] === 'admin'): ?>
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                    </div>
                    <h3 class="profile-username text-center font-weight-bold mb-3"><?= esc($trip['name']) ?></h3>
                    <p class="text-muted text-center mb-4">Kelompok: <?= esc($group['name']) ?></p>

                    <div class="form-group">
                        <label for="trip_name">Nama Kegiatan</label>
                        <input type="text" name="name" id="trip_name" class="form-control" value="<?= old('name', esc($trip['name'])) ?>" required placeholder="Masukkan nama kegiatan">
                    </div>
                    
                    <div class="form-group">
                        <label for="trip_start_date">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="trip_start_date" class="form-control" value="<?= old('start_date', $trip['start_date']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="trip_end_date">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="trip_end_date" class="form-control" value="<?= old('end_date', $trip['end_date']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="trip_notes">Catatan / Keterangan</label>
                        <textarea name="notes" id="trip_notes" class="form-control" rows="3" placeholder="Tambahkan catatan kegiatan..."><?= old('notes', esc($trip['notes'])) ?></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex" style="gap: 10px;">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-outline-danger flex-fill btn-delete-trip" data-id="<?= $trip['id'] ?>">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="card-body box-profile">
                    <div class="text-center">
                        <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                    </div>
                    <h3 class="profile-username text-center font-weight-bold"><?= esc($trip['name']) ?></h3>
                    <p class="text-muted text-center">Kelompok: <?= esc($group['name']) ?></p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Tanggal Mulai</b> 
                            <span class="float-right font-weight-bold">
                                <?= $trip['start_date'] ? date('d M Y', strtotime($trip['start_date'])) : '-' ?>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Tanggal Selesai</b> 
                            <span class="float-right font-weight-bold">
                                <?= $trip['end_date'] ? date('d M Y', strtotime($trip['end_date'])) : '-' ?>
                            </span>
                        </li>
                    </ul>

                    <?php if ($trip['notes']): ?>
                        <strong><i class="far fa-file-alt mr-1"></i> Catatan</strong>
                        <p class="text-muted small"><?= nl2br(esc($trip['notes'])) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($currentMembership['role'] === 'admin'): ?>
            </form>
        <?php endif; ?>

        <?php if ($currentMembership['role'] === 'admin'): ?>
            <form id="delete-trip-form" action="<?= base_url('backend/trips/delete/' . $trip['id']) ?>" method="post" style="display:none;">
                <?= csrf_field() ?>
            </form>
        <?php endif; ?>

        <!-- Add Period Card (Admin Only) -->
        <?php if ($currentMembership['role'] === 'admin'): ?>
            <form action="<?= base_url('backend/trips/add-period/' . $trip['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div id="card-tambah-periode" class="card card-success card-outline collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold" style="float: none; margin-bottom: 0;">
                            <i class="fas fa-calendar-plus mr-1"></i> Tambah Periode Baru
                        </h3>
                        <div class="card-tools" style="float: right;">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-expand-icon="fa-eye" data-collapse-icon="fa-eye-slash">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="text-muted mt-1" style="clear: both; font-size: 0.85rem;">
                            <span><i class="fas fa-info-circle mr-1"></i> Buat sub-periode baru untuk pencatatan transaksi</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="label">Label Periode</label>
                            <input type="text" class="form-control" id="label" name="label" placeholder="Misal: April 2026" required>
                            <small class="form-text text-muted">Bisa berupa bulan (e.g. April) atau nama etape perjalanan.</small>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Mulai (Opsional)</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="form-group">
                            <label for="end_date">Selesai (Opsional)</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success btn-block">Tambah Periode</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <script>
            if (window.innerWidth >= 992) {
                var cards = ['#card-detail-kegiatan', '#card-tambah-periode'];
                cards.forEach(function(selector) {
                    var card = document.querySelector(selector);
                    if (card) {
                        card.classList.remove('collapsed-card');
                        var icon = card.querySelector('[data-card-widget="collapse"] i');
                        if (icon) {
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        }
                    }
                });
            }
        </script>
    </div>

    <!-- Periods & Active Members Panel -->
    <div class="col-lg-8">
        <h4 class="font-weight-bold mb-3"><i class="far fa-calendar-alt mr-2"></i> Periode Pengeluaran</h4>
        
        <?php if (empty($periods)): ?>
            <div class="card card-outline card-warning text-center py-5">
                <div class="card-body">
                    <i class="fas fa-calendar-times text-warning fa-3x mb-3"></i>
                    <h4>Belum Ada Periode</h4>
                    <p class="text-muted">Tambahkan periode baru (misalnya per bulan) untuk mulai mencatat transaksi dan membagi tagihan.</p>
                </div>
            </div>
        <?php else: ?>
            <?php
            $openPeriods = [];
            $settledPeriods = [];
            foreach ($periods as $p) {
                if (($p['status'] ?? 'open') === 'settled') {
                    $settledPeriods[] = $p;
                } else {
                    $openPeriods[] = $p;
                }
            }
            ?>
            <!-- Search Bar -->
            <div class="card shadow-sm mb-3" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-body p-3">
                    <div class="input-group mb-0">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #ced4da;">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" id="searchPeriodInput" class="form-control border-left-0" placeholder="Cari periode berdasarkan label atau tanggal..." style="border-radius: 0 8px 8px 0; height: calc(2.25rem + 10px); border-color: #ced4da;">
                    </div>
                </div>
            </div>

            <!-- No Results Alert -->
            <div class="card card-outline card-warning text-center py-5 shadow-sm mb-3" id="noPeriodResultsRow" style="display: none; border-radius: 12px;">
                <div class="card-body">
                    <i class="fas fa-search fa-3x text-warning mb-3"></i>
                    <h4>Tidak Ada Hasil</h4>
                    <p class="text-muted">Tidak ditemukan periode yang cocok dengan kata kunci pencarian Anda.</p>
                </div>
            </div>

            <!-- Open Periods Card (Default Collapsed) -->
            <div class="card card-success card-outline collapsed-card mb-3" id="card-periods-open">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold" style="float: none; margin-bottom: 0;">
                        <i class="fas fa-unlock-alt text-success mr-2"></i> Periode Terbuka (Open)
                    </h3>
                    <div class="card-tools" style="float: right;">
                        <span class="badge badge-success mr-2"><?= count($openPeriods) ?> Periode</span>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-expand-icon="fa-eye" data-collapse-icon="fa-eye-slash">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <?php if (empty($openPeriods)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2 text-gray-300"></i>
                            <p class="mb-0 small">Tidak ada periode terbuka</p>
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="accordionOpen">
                            <?php foreach ($openPeriods as $p): ?>
                                <?php 
                                $isActiveList  = $activeMembersPerPeriod[$p['id']] ?? [];
                                $isActiveCount = count($isActiveList);
                                $startDateFormatted = $p['start_date'] ? date('d M Y', strtotime($p['start_date'])) : '';
                                $endDateFormatted = $p['end_date'] ? date('d M Y', strtotime($p['end_date'])) : '';
                                $dateSearchText = strtolower($startDateFormatted . ' ' . $endDateFormatted);
                                ?>
                                <div class="card card-outline card-info mb-3 period-item"
                                     data-label="<?= esc(strtolower($p['label'])) ?>"
                                     data-date="<?= esc($dateSearchText) ?>"
                                     data-id="<?= $p['id'] ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center py-3" 
                                         style="cursor: pointer;" 
                                         data-toggle="collapse" 
                                         data-target="#collapse-<?= $p['id'] ?>" 
                                         aria-expanded="false">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-check text-info fa-lg mr-3"></i>
                                            <div>
                                                <h5 class="m-0 font-weight-bold"><?= esc($p['label']) ?></h5>
                                                <small class="text-muted">
                                                    <?= $p['start_date'] ? date('d M', strtotime($p['start_date'])) : '' ?>
                                                    <?= $p['end_date'] ? ' s/d ' . date('d M Y', strtotime($p['end_date'])) : '' ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="ml-auto d-flex align-items-center" style="gap: 10px;">
                                            <span class="badge badge-success py-2 px-3 elevation-1">
                                                <i class="fas fa-unlock-alt mr-1"></i> Open
                                            </span>
                                            <span class="badge badge-info py-2 px-3 elevation-1">
                                                <i class="fas fa-users mr-1"></i> <?= $isActiveCount ?> Anggota Aktif
                                            </span>
                                            <i class="fas fa-chevron-down text-muted accordion-arrow"></i>
                                        </div>
                                    </div>

                                    <div id="collapse-<?= $p['id'] ?>" class="collapse" data-parent="#accordionOpen">
                                        <div class="card-body">
                                            <h6><strong>Status Keaktifan Anggota:</strong></h6>
                                            <p class="text-muted small">Anggota yang dicentang adalah pembagi biaya transaksi bertipe **Shared (dibagi rata)** pada periode ini.</p>
                                            
                                            <form action="<?= base_url('backend/trips/save-active-members/' . $p['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <div class="row mt-3">
                                                    <?php foreach ($groupMembers as $gm): ?>
                                                        <?php 
                                                        $isMemberActive = in_array((int)$gm['user_id'], $isActiveList); 
                                                        $isAdmin = ($currentMembership['role'] === 'admin');
                                                        ?>
                                                        <div class="col-sm-6 col-md-4 mb-2">
                                                            <div class="icheck-primary">
                                                                <input type="checkbox" 
                                                                       id="active-<?= $p['id'] ?>-<?= $gm['user_id'] ?>" 
                                                                       name="active_users[]" 
                                                                       value="<?= $gm['user_id'] ?>" 
                                                                       <?= $isMemberActive ? 'checked' : '' ?>
                                                                       <?= !$isAdmin ? 'disabled' : '' ?>>
                                                                <label for="active-<?= $p['id'] ?>-<?= $gm['user_id'] ?>" 
                                                                       class="font-weight-normal <?= !$isMemberActive ? 'text-muted' : '' ?>"
                                                                       style="user-select: none;">
                                                                    <?= esc($gm['username']) ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                                <?php if ($currentMembership['role'] === 'admin'): ?>
                                                    <div class="mt-4 pt-3 border-top">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                                                            <div class="d-flex flex-wrap" style="gap: 6px;">
                                                                <button type="button" 
                                                                        class="btn btn-outline-warning btn-sm btn-edit-period mr-1" 
                                                                        data-id="<?= $p['id'] ?>" 
                                                                        data-label="<?= esc($p['label']) ?>" 
                                                                        data-start="<?= $p['start_date'] ?>" 
                                                                        data-end="<?= $p['end_date'] ?>">
                                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-outline-danger btn-sm btn-delete-period" 
                                                                        data-id="<?= $p['id'] ?>" 
                                                                        data-label="<?= esc($p['label']) ?>">
                                                                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-outline-secondary btn-sm btn-toggle-period-status"
                                                                        data-id="<?= $p['id'] ?>"
                                                                        data-label="<?= esc($p['label']) ?>"
                                                                        data-status="open">
                                                                    <i class="fas fa-lock mr-1"></i> Tutup Buku
                                                                </button>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-save mr-1"></i> Simpan Keaktifan
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Settled Periods Card (Default Collapsed) -->
            <div class="card card-secondary card-outline collapsed-card mb-3" id="card-periods-settled">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold text-muted" style="float: none; margin-bottom: 0;">
                        <i class="fas fa-lock text-secondary mr-2"></i> Periode Ditutup (Settled)
                    </h3>
                    <div class="card-tools" style="float: right;">
                        <span class="badge badge-secondary mr-2"><?= count($settledPeriods) ?> Periode</span>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-expand-icon="fa-eye" data-collapse-icon="fa-eye-slash">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <?php if (empty($settledPeriods)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-folder fa-2x mb-2 text-gray-300"></i>
                            <p class="mb-0 small">Tidak ada periode ditutup</p>
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="accordionSettled">
                            <?php foreach ($settledPeriods as $p): ?>
                                <?php 
                                $isActiveList  = $activeMembersPerPeriod[$p['id']] ?? [];
                                $isActiveCount = count($isActiveList);
                                $startDateFormatted = $p['start_date'] ? date('d M Y', strtotime($p['start_date'])) : '';
                                $endDateFormatted = $p['end_date'] ? date('d M Y', strtotime($p['end_date'])) : '';
                                $dateSearchText = strtolower($startDateFormatted . ' ' . $endDateFormatted);
                                ?>
                                <div class="card card-outline card-info mb-3 period-item"
                                     data-label="<?= esc(strtolower($p['label'])) ?>"
                                     data-date="<?= esc($dateSearchText) ?>"
                                     data-id="<?= $p['id'] ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center py-3" 
                                         style="cursor: pointer;" 
                                         data-toggle="collapse" 
                                         data-target="#collapse-<?= $p['id'] ?>" 
                                         aria-expanded="false">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-lock text-secondary fa-lg mr-3"></i>
                                            <div>
                                                <h5 class="m-0 font-weight-bold text-muted"><?= esc($p['label']) ?></h5>
                                                <small class="text-muted">
                                                    <?= $p['start_date'] ? date('d M', strtotime($p['start_date'])) : '' ?>
                                                    <?= $p['end_date'] ? ' s/d ' . date('d M Y', strtotime($p['end_date'])) : '' ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="ml-auto d-flex align-items-center" style="gap: 10px;">
                                            <span class="badge badge-secondary py-2 px-3 elevation-1">
                                                <i class="fas fa-lock mr-1"></i> Settled
                                            </span>
                                            <span class="badge badge-info py-2 px-3 elevation-1">
                                                <i class="fas fa-users mr-1"></i> <?= $isActiveCount ?> Anggota Aktif
                                            </span>
                                            <i class="fas fa-chevron-down text-muted accordion-arrow"></i>
                                        </div>
                                    </div>

                                    <div id="collapse-<?= $p['id'] ?>" class="collapse" data-parent="#accordionSettled">
                                        <div class="card-body">
                                            <h6><strong>Status Keaktifan Anggota:</strong></h6>
                                            <p class="text-muted small">Anggota yang dicentang adalah pembagi biaya transaksi bertipe **Shared (dibagi rata)** pada periode ini.</p>
                                            
                                            <form action="<?= base_url('backend/trips/save-active-members/' . $p['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <div class="row mt-3">
                                                    <?php foreach ($groupMembers as $gm): ?>
                                                        <?php 
                                                        $isMemberActive = in_array((int)$gm['user_id'], $isActiveList); 
                                                        $isAdmin = ($currentMembership['role'] === 'admin');
                                                        ?>
                                                        <div class="col-sm-6 col-md-4 mb-2">
                                                            <div class="icheck-primary">
                                                                <input type="checkbox" 
                                                                       id="active-<?= $p['id'] ?>-<?= $gm['user_id'] ?>" 
                                                                       name="active_users[]" 
                                                                       value="<?= $gm['user_id'] ?>" 
                                                                       <?= $isMemberActive ? 'checked' : '' ?>
                                                                       disabled>
                                                                <label for="active-<?= $p['id'] ?>-<?= $gm['user_id'] ?>" 
                                                                       class="font-weight-normal <?= !$isMemberActive ? 'text-muted' : '' ?>"
                                                                       style="user-select: none;">
                                                                    <?= esc($gm['username']) ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                                <?php if ($currentMembership['role'] === 'admin'): ?>
                                                    <div class="mt-4 pt-3 border-top">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                                                            <div class="d-flex flex-wrap" style="gap: 6px;">
                                                                <button type="button" 
                                                                        class="btn btn-outline-warning btn-sm btn-edit-period mr-1" 
                                                                        data-id="<?= $p['id'] ?>" 
                                                                        data-label="<?= esc($p['label']) ?>" 
                                                                        data-start="<?= $p['start_date'] ?>" 
                                                                        data-end="<?= $p['end_date'] ?>">
                                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-outline-danger btn-sm btn-delete-period" 
                                                                        data-id="<?= $p['id'] ?>" 
                                                                        data-label="<?= esc($p['label']) ?>">
                                                                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-outline-success btn-sm btn-toggle-period-status"
                                                                        data-id="<?= $p['id'] ?>"
                                                                        data-label="<?= esc($p['label']) ?>"
                                                                        data-status="settled">
                                                                    <i class="fas fa-unlock-alt mr-1"></i> Buka Kembali
                                                                </button>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary btn-sm" disabled title="Periode sudah ditutup">
                                                                <i class="fas fa-save mr-1"></i> Simpan Keaktifan
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Edit Periode (Admin Only) -->
<?php if ($currentMembership['role'] === 'admin'): ?>
    <div class="modal fade" id="modalEditPeriod" tabindex="-1" role="dialog" aria-labelledby="modalEditPeriodLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="modalEditPeriodLabel"><i class="fas fa-edit text-warning mr-1"></i> Edit Periode</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditPeriod" action="" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_period_label">Label Periode</label>
                            <input type="text" class="form-control" id="edit_period_label" name="label" required placeholder="Misal: April 2026">
                        </div>
                        <div class="form-group">
                            <label for="edit_period_start">Mulai (Opsional)</label>
                            <input type="date" class="form-control" id="edit_period_start" name="start_date">
                        </div>
                        <div class="form-group">
                            <label for="edit_period_end">Selesai (Opsional)</label>
                            <input type="date" class="form-control" id="edit_period_end" name="end_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Period Deletion -->
    <form id="delete-period-form" action="" method="post" style="display:none;">
        <?= csrf_field() ?>
    </form>

    <!-- Hidden Form for Toggle Period Status -->
    <form id="toggle-period-status-form" action="" method="post" style="display:none;">
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    /* Accordion Arrow Flip Effect */
    .card-header[aria-expanded="true"] .accordion-arrow {
        transform: rotate(180deg);
        transition: transform 0.2s ease;
    }
    .card-header[aria-expanded="false"] .accordion-arrow {
        transform: rotate(0deg);
        transition: transform 0.2s ease;
    }
</style>
<script>
$(document).ready(function() {
    $('.btn-delete-trip').on('click', function() {
        const tripId = $(this).data('id');
        
        Swal.fire({
            title: 'Mempersiapkan pratinjau...',
            text: 'Sedang menghitung data yang terpengaruh...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `<?= base_url('backend/trips/delete-preview') ?>/${tripId}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                Swal.close();
                if (res.success) {
                    Swal.fire({
                        title: 'Hapus Kegiatan Permanen?',
                        html: `
                            <div class="text-left border p-3 rounded mb-3 bg-light" style="font-size: 0.9rem;">
                                <p class="mb-2 text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Tindakan ini tidak dapat dibatalkan!</p>
                                <p class="mb-2">Menghapus kegiatan <strong>${res.trip_name}</strong> juga akan menghapus secara permanen data berikut:</p>
                                <ul class="pl-4 mb-0">
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
                            $('#delete-trip-form').submit();
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

    // Edit Periode
    $('.btn-edit-period').on('click', function(e) {
        e.stopPropagation(); // Mencegah accordion collapse/expand
        const periodId = $(this).data('id');
        const label = $(this).data('label');
        const start = $(this).data('start');
        const end = $(this).data('end');

        $('#formEditPeriod').attr('action', `<?= base_url('backend/trips/update-period') ?>/${periodId}`);
        $('#edit_period_label').val(label);
        $('#edit_period_start').val(start);
        $('#edit_period_end').val(end);

        $('#modalEditPeriod').modal('show');
    });

    // Konfirmasi hapus periode
    $('.btn-delete-period').on('click', function(e) {
        e.stopPropagation(); // Mencegah accordion collapse/expand
        const periodId = $(this).data('id');

        Swal.fire({
            title: 'Mempersiapkan pratinjau...',
            text: 'Sedang menghitung data yang terpengaruh...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `<?= base_url('backend/trips/delete-period-preview') ?>/${periodId}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                Swal.close();
                if (res.success) {
                    Swal.fire({
                        title: 'Hapus Periode Permanen?',
                        html: `
                            <div class="text-left border p-3 rounded mb-3 bg-light" style="font-size: 0.9rem;">
                                <p class="mb-2 text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Tindakan ini tidak dapat dibatalkan!</p>
                                <p class="mb-2">Menghapus periode <strong>${res.label}</strong> juga akan menghapus secara permanen data berikut:</p>
                                <ul class="pl-4 mb-0">
                                    <li><strong>${res.transactions}</strong> Catatan Transaksi</li>
                                    <li><strong>${res.settlements}</strong> Riwayat Settlement</li>
                                    <li><strong>${res.files}</strong> Lampiran Nota/Bukti Transfer</li>
                                </ul>
                            </div>
                            <span class="text-dark">Ketik kata <strong>HAPUS</strong> untuk mengonfirmasi tindakan ini:</span>
                            <input type="text" id="confirm-delete-period-text" class="form-control mt-2 text-center text-bold" placeholder="Ketik HAPUS di sini" style="text-transform: uppercase;">
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus Semua!',
                        cancelButtonText: 'Batal',
                        preConfirm: () => {
                            const confirmText = Swal.getPopup().querySelector('#confirm-delete-period-text').value;
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
                            $('#delete-period-form').attr('action', `<?= base_url('backend/trips/delete-period') ?>/${periodId}`);
                            $('#delete-period-form').submit();
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
    // Toggle Status Periode (Tutup Buku / Buka Kembali)
    $('.btn-toggle-period-status').on('click', function(e) {
        e.stopPropagation();
        const periodId     = $(this).data('id');
        const periodLabel  = $(this).data('label');
        const currentStatus = $(this).data('status'); // 'open' or 'settled'
        const isSettling   = (currentStatus === 'open'); // true = will become settled

        const title     = isSettling ? '🔒 Tutup Buku Periode?' : '🔓 Buka Kembali Periode?';
        const iconType  = isSettling ? 'warning' : 'question';
        const confirmBtnColor = isSettling ? '#6c757d' : '#28a745';
        const confirmBtnText  = isSettling
            ? '<i class="fas fa-lock mr-1"></i> Ya, Tutup Buku'
            : '<i class="fas fa-unlock-alt mr-1"></i> Ya, Buka Kembali';
        const htmlMsg = isSettling
            ? `<p>Anda akan <strong>menutup buku</strong> periode <strong>${periodLabel}</strong>.</p>
               <div class="alert alert-warning text-left small p-2 mb-0">
                   <i class="fas fa-exclamation-triangle mr-1"></i>
                   Setelah ditutup, transaksi baru <strong>tidak dapat ditambahkan</strong> ke periode ini.
                   Admin dapat membuka kembali kapan saja jika diperlukan.
               </div>`
            : `<p>Anda akan <strong>membuka kembali</strong> periode <strong>${periodLabel}</strong>.</p>
               <div class="alert alert-info text-left small p-2 mb-0">
                   <i class="fas fa-info-circle mr-1"></i>
                   Setelah dibuka, transaksi baru dapat ditambahkan ke periode ini kembali.
               </div>`;

        Swal.fire({
            title: title,
            html: htmlMsg,
            icon: iconType,
            showCancelButton: true,
            confirmButtonColor: confirmBtnColor,
            cancelButtonColor: '#3085d6',
            confirmButtonText: confirmBtnText,
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                $('#toggle-period-status-form').attr(
                    'action',
                    `<?= base_url('backend/trips/toggle-period-status') ?>/${periodId}`
                );
                $('#toggle-period-status-form').submit();
            }
        });
    });

    // Toggle cards by clicking anywhere on their headers
    $('#card-detail-kegiatan .card-header, #card-tambah-periode .card-header, #card-periods-open .card-header, #card-periods-settled .card-header').css('cursor', 'pointer').on('click', function(e) {
        if ($(e.target).closest('[data-card-widget="collapse"]').length > 0) {
            return;
        }
        $(this).find('[data-card-widget="collapse"]').trigger('click');
    });

    // Search periods filter logic
    $('#searchPeriodInput').on('keyup input', function() {
        const query = $(this).val().toLowerCase().trim();
        
        if (query === '') {
            // Reset state
            $('.period-item').show();
            $('#card-periods-open, #card-periods-settled').show();
            $('#noPeriodResultsRow').hide();
            
            // Collapse all period accordions
            $('.period-item .collapse').collapse('hide');
            
            // Collapse parent group cards
            ['#card-periods-open', '#card-periods-settled'].forEach(function(selector) {
                const $card = $(selector);
                if ($card.length && !$card.hasClass('collapsed-card')) {
                    $card.find('[data-card-widget="collapse"]').trigger('click');
                }
            });
            return;
        }

        let anyMatch = false;

        ['#card-periods-open', '#card-periods-settled'].forEach(function(selector) {
            const $card = $(selector);
            if (!$card.length) return;

            let groupHasMatch = false;

            $card.find('.period-item').each(function() {
                const $item = $(this);
                const label = $item.data('label') || '';
                const date = $item.data('date') || '';
                const periodId = $item.data('id');

                if (label.includes(query) || date.includes(query)) {
                    $item.show();
                    groupHasMatch = true;
                    anyMatch = true;
                    
                    // Auto expand the specific period accordion panel
                    $('#collapse-' + periodId).collapse('show');
                } else {
                    $item.hide();
                    $('#collapse-' + periodId).collapse('hide');
                }
            });

            if (groupHasMatch) {
                $card.show();
                if ($card.hasClass('collapsed-card')) {
                    $card.find('[data-card-widget="collapse"]').trigger('click');
                }
            } else {
                $card.hide();
            }
        });

        if (anyMatch) {
            $('#noPeriodResultsRow').hide();
        } else {
            $('#noPeriodResultsRow').show();
        }
    });
});
</script>
<?= $this->endSection() ?>
