<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<div class="row">
    <!-- Trip Info Sidebar -->
    <div class="col-lg-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
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
            <!-- /.card-body -->
        </div>

        <!-- Add Period Card (Admin Only) -->
        <?php if ($currentMembership['role'] === 'admin'): ?>
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-calendar-plus mr-1"></i> Tambah Periode Baru</h3>
                </div>
                <form action="<?= base_url('backend/trips/add-period/' . $trip['id']) ?>" method="post">
                    <?= csrf_field() ?>
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
                </form>
            </div>
        <?php endif; ?>
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
            <div class="accordion" id="periodsAccordion">
                <?php foreach ($periods as $index => $p): ?>
                    <?php 
                    $isActiveList = $activeMembersPerPeriod[$p['id']] ?? [];
                    $isActiveCount = count($isActiveList);
                    $isOpen = ($index === 0) ? 'show' : '';
                    $isCollapsed = ($index === 0) ? '' : 'collapsed';
                    ?>
                    <div class="card card-outline card-info mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center py-3" 
                             style="cursor: pointer;" 
                             data-toggle="collapse" 
                             data-target="#collapse-<?= $p['id'] ?>" 
                             aria-expanded="<?= ($index === 0) ? 'true' : 'false' ?>">
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
                            <div class="ml-auto d-flex align-items-center" style="gap: 15px;">
                                <span class="badge badge-info py-2 px-3 elevation-1">
                                    <i class="fas fa-users mr-1"></i> <?= $isActiveCount ?> Anggota Aktif
                                </span>
                                <i class="fas fa-chevron-down text-muted accordion-arrow"></i>
                            </div>
                        </div>

                        <div id="collapse-<?= $p['id'] ?>" class="collapse <?= $isOpen ?>" data-parent="#periodsAccordion">
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
                                        <div class="mt-4 pt-3 border-top text-right">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save mr-1"></i> Simpan Keaktifan Periode
                                            </button>
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
<?= $this->endSection() ?>
