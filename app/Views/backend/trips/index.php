<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="text-muted mb-0">Daftar trip perjalanan atau proyek pengeluaran bersama.</h5>
        <a href="<?= base_url('backend/trips/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Buat Trip Baru
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-plane-departure mr-1"></i> Daftar Perjalanan</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama Trip</th>
                                <th>Nama Group</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Catatan</th>
                                <th class="text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($trips)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-exclamation-circle fa-2x mb-2 d-block text-warning"></i>
                                        Belum ada trip perjalanan yang dibuat.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($trips as $t): ?>
                                    <tr>
                                        <td class="align-middle font-weight-bold">
                                            <i class="fas fa-map-marked-alt text-muted mr-2"></i><?= esc($t['name']) ?>
                                        </td>
                                        <td class="align-middle"><?= esc($t['group_name']) ?></td>
                                        <td class="align-middle">
                                            <?= $t['start_date'] ? date('d M Y', strtotime($t['start_date'])) : '<span class="text-muted small">-</span>' ?>
                                        </td>
                                        <td class="align-middle">
                                            <?= $t['end_date'] ? date('d M Y', strtotime($t['end_date'])) : '<span class="text-muted small">-</span>' ?>
                                        </td>
                                        <td class="align-middle text-truncate" style="max-width: 250px;">
                                            <?= esc($t['notes'] ?? '-') ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="<?= base_url('backend/trips/detail/' . $t['id']) ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye mr-1"></i> Detail & Periode
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>

<?= $this->endSection() ?>
