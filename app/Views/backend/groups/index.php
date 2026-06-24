<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="text-muted mb-0">Daftar kelompok perjalanan atau pengeluaran bersama Anda.</h5>
        <a href="<?= base_url('backend/groups/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Buat Group Baru
        </a>
    </div>
</div>

<div class="row">
    <?php if (empty($groups)): ?>
        <div class="col-12">
            <div class="card card-outline card-warning text-center py-5">
                <div class="card-body">
                    <i class="fas fa-users-slash text-warning fa-3x mb-3"></i>
                    <h4>Belum Ada Group</h4>
                    <p class="text-muted">Anda belum terdaftar atau membuat group mana pun saat ini.</p>
                    <a href="<?= base_url('backend/groups/create') ?>" class="btn btn-primary mt-3">
                        <i class="fas fa-plus mr-1"></i> Buat Group Sekarang
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($groups as $g): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-widget widget-user-2 shadow-sm">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-gradient-primary">
                        <div class="widget-user-image">
                            <div class="img-circle elevation-2 d-flex justify-content-center align-items-center bg-white text-primary font-weight-bold" 
                                 style="width: 65px; height: 65px; font-size: 1.5rem; user-select: none;">
                                <?= strtoupper(substr($g['name'], 0, 2)) ?>
                            </div>
                        </div>
                        <!-- /.widget-user-image -->
                        <h3 class="widget-user-username font-weight-bold"><?= esc($g['name']) ?></h3>
                        <h5 class="widget-user-desc">
                            <?php if ($g['role'] === 'admin'): ?>
                                <span class="badge badge-warning">Group Admin</span>
                            <?php else: ?>
                                <span class="badge badge-light">Anggota</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-footer p-0">
                        <ul class="nav flex-column">
                            <?php if (!empty($g['members'])): ?>
                                <li class="nav-item px-3 py-3 border-bottom">
                                    <span class="text-muted small font-weight-bold d-block mb-2">
                                        <i class="fas fa-users mr-1"></i> Anggota (<?= count($g['members']) ?>)
                                    </span>
                                    <div class="d-flex flex-wrap" style="gap: 6px;">
                                        <?php foreach ($g['members'] as $m): ?>
                                            <?php 
                                            $nameToShow = !empty($m['fullname']) ? $m['fullname'] : $m['username'];
                                            ?>
                                            <span class="badge border py-1 px-2 font-weight-normal text-primary" style="font-size: 0.8rem; border-radius: 12px; background-color: #e8f0fe; border-color: #d2e3fc !important;" title="<?= esc($m['username']) ?>">
                                                <i class="far fa-user mr-1 text-primary"></i><?= esc($nameToShow) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a href="<?= base_url('backend/groups/detail/' . $g['id']) ?>" class="nav-link text-center text-primary font-weight-bold py-3">
                                    Kelola & Lihat Detail <i class="fas fa-arrow-circle-right ml-1"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
