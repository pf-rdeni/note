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
    <!-- Filter Sidebar Column -->
    <div class="col-lg-3">
        <!-- Mobile filter toggle -->
        <button class="btn btn-outline-primary btn-sm mobile-filter-toggle" type="button" 
                data-toggle="collapse" data-target="#filterPanel" 
                aria-expanded="false" aria-controls="filterPanel">
            <i class="fas fa-filter mr-1"></i> Filter Trip & Periode
        </button>

        <div class="collapse show" id="filterPanel">
        <!-- Trip Selector Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Pilih Trip
                </h3>
            </div>
            <div class="card-body">
                <form action="<?= base_url('backend/transactions') ?>" method="get" id="tripFilterForm">
                    <div class="form-group mb-0">
                        <label for="trip_select">Trip Perjalanan:</label>
                        <select class="form-control select2" id="trip_select" name="trip_id" onchange="this.form.submit()">
                            <?php if (empty($availableTrips)): ?>
                                <option value="" disabled selected>Belum ada trip</option>
                            <?php else: ?>
                                <?php foreach ($availableTrips as $at): ?>
                                    <option value="<?= $at['id'] ?>" <?= (int)$at['id'] === (int)$selectedTripId ? 'selected' : '' ?>>
                                        <?= esc($at['group_name']) ?> - <?= esc($at['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Period Selector Card -->
        <?php if (!empty($selectedTripId)): ?>
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="far fa-calendar-alt mr-1"></i> Periode Pengeluaran
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="<?= base_url('backend/transactions?trip_id=' . $selectedTripId) ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= empty($selectedPeriodId) ? 'active' : '' ?>">
                            <span>Semua Periode</span>
                            <span class="badge badge-secondary badge-pill">
                                <i class="fas fa-globe"></i>
                            </span>
                        </a>
                        <?php if (empty($periods)): ?>
                            <div class="p-3 text-muted text-center small">
                                <i class="fas fa-calendar-times mb-1 d-block text-warning"></i>
                                Belum ada periode dibuat.
                            </div>
                        <?php else: ?>
                            <?php foreach ($periods as $p): ?>
                                <a href="<?= base_url('backend/transactions?trip_id=' . $selectedTripId . '&period_id=' . $p['id']) ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= (int)$p['id'] === (int)$selectedPeriodId ? 'active' : '' ?>">
                                    <span><?= esc($p['label']) ?></span>
                                    <span class="badge badge-light badge-pill">
                                        <i class="far fa-clock"></i>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        </div><!-- /.collapse -->
    </div>

    <!-- Transactions List Column -->
    <div class="col-lg-9">
        <?php if (empty($selectedTripId)): ?>
            <div class="card card-outline card-warning text-center py-5">
                <div class="card-body">
                    <i class="fas fa-plane-departure text-warning fa-3x mb-3"></i>
                    <h4>Pilih Trip Terlebih Dahulu</h4>
                    <p class="text-muted">Untuk mencatat transaksi, pastikan Anda telah membuat atau bergabung ke suatu Group dan Trip.</p>
                    <a href="<?= base_url('backend/trips') ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-right mr-1"></i> Buka Manajemen Trip
                    </a>
                </div>
            </div>
        <?php else: ?>
            
            <?php if (!empty($calculationResult)): ?>
                <!-- Summary Widgets -->
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="info-box bg-gradient-primary">
                            <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Belanja</span>
                                <span class="info-box-number">Rp <?= number_format($calculationResult['summary']['total_transactions'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fas fa-divide"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Beban Shared</span>
                                <span class="info-box-number">Rp <?= number_format($calculationResult['summary']['total_shared'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="info-box bg-gradient-warning text-white">
                            <span class="info-box-icon text-white"><i class="fas fa-user-friends"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-white">Bagi Rata (Split)</span>
                                <span class="info-box-number text-white">Rp <?= number_format($calculationResult['summary']['split_rata'], 0, ',', '.') ?> <small>/org</small></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="info-box bg-gradient-info">
                            <span class="info-box-icon"><i class="fas fa-user-tag"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Beban Kustom</span>
                                <span class="info-box-number">Rp <?= number_format($calculationResult['summary']['total_individual'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Excel-style Rekap Table Card -->
                <div class="card card-success card-outline shadow-sm">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center py-3">
                        <h3 class="card-title font-weight-bold text-success mb-0">
                            <i class="fas fa-table mr-1"></i> <span class="d-none d-sm-inline">Rekapitulasi Pembagian Saldo</span><span class="d-sm-none">Rekap Saldo</span>
                        </h3>
                        <div class="card-tools ml-auto d-flex align-items-center" style="gap: 6px;">
                            <button type="button" class="btn btn-xs btn-outline-success font-weight-bold btn-export-excel">
                                <i class="fas fa-file-excel mr-1"></i> <span class="d-none d-sm-inline">Ekspor </span>Excel
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-info font-weight-bold btn-print-rekap">
                                <i class="fas fa-print mr-1"></i> <span class="d-none d-sm-inline">Cetak </span>PDF
                            </button>
                            <span class="badge badge-success py-2 px-2 font-weight-bold ml-1 d-none d-sm-inline">Periode: <?= esc($calculationResult['period']['label']) ?></span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="rekap-table-wrapper">
                            <table class="table table-bordered table-striped mb-0 text-center" id="rekapTable">
                                <thead class="bg-light text-secondary text-sm">
                                    <tr>
                                        <th class="text-left py-3">Nama</th>
                                        <th class="py-3 table-rekap-mobile-hide">Status</th>
                                        <th class="text-right py-3">Total (A)<br><small class="text-muted d-none d-sm-block">Paid Out of Pocket</small></th>
                                        <th class="text-right py-3 table-rekap-mobile-hide">Shared (B)<br><small class="text-muted">Split Rata</small></th>
                                        <th class="text-right py-3 table-rekap-mobile-hide">Selisih<br><small class="text-muted">(A-B)</small></th>
                                        <th class="text-right py-3 table-rekap-mobile-hide">Kustom (C)<br><small class="text-muted">Individual</small></th>
                                        <th class="text-right py-3">Saldo Akhir<br><small class="text-muted">(A-B-C)</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($calculationResult['participants'] as $p): ?>
                                        <?php
                                        $selisihAwal = $p['total_paid'] - $p['shared_share'];
                                        $netBalance = $p['net_balance'];
                                        ?>
                                        <tr>
                                            <td class="text-left font-weight-bold align-middle py-3">
                                                <i class="far fa-user text-muted mr-1"></i><?= esc($p['username']) ?>
                                            </td>
                                            <td class="align-middle table-rekap-mobile-hide">
                                                <?php if ($p['is_active_member']): ?>
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-times mr-1"></i>Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right align-middle font-weight-bold text-dark py-3">
                                                Rp <?= number_format($p['total_paid'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-right align-middle text-muted py-3 table-rekap-mobile-hide">
                                                Rp <?= number_format($p['shared_share'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-right align-middle font-weight-bold py-3 table-rekap-mobile-hide <?= $selisihAwal >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= $selisihAwal >= 0 ? '+' : '-' ?> Rp <?= number_format(abs($selisihAwal), 0, ',', '.') ?>
                                            </td>
                                            <td class="text-right align-middle text-info py-3 table-rekap-mobile-hide">
                                                Rp <?= number_format($p['individual_charge'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-right align-middle font-weight-bold py-3 <?= $netBalance >= 0 ? 'text-success' : 'text-danger' ?>" style="font-size: 1.05rem; background-color: <?= $netBalance >= 0 ? 'rgba(40, 167, 69, 0.08)' : 'rgba(220, 53, 69, 0.08)' ?>;">
                                                <?= $netBalance >= 0 ? '+' : '-' ?> Rp <?= number_format(abs($netBalance), 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Settlement Recommendations Card -->
                <div class="card card-warning card-outline shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold text-warning">
                            <i class="fas fa-hand-holding-usd mr-1"></i> Rekomendasi Settlement
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($calculationResult['settlements'])): ?>
                            <div class="alert alert-success mb-0 py-3 font-weight-bold">
                                <i class="fas fa-check-circle mr-2"></i> Semua saldo seimbang! Tidak ada transaksi transfer yang perlu dilakukan.
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-3">Untuk menyeimbangkan seluruh saldo di atas, berikut rincian transfer yang disarankan:</p>
                            <div class="row">
                                <?php foreach ($calculationResult['settlements'] as $s): ?>
                                    <div class="col-md-6 col-12 mb-3 settlement-card">
                                        <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-between shadow-xs">
                                            <div style="flex: 1;">
                                                <span class="badge badge-danger font-weight-bold mb-1">Bayar</span>
                                                <h6 class="font-weight-bold mb-0 text-dark"><?= esc($s['from_username']) ?></h6>
                                            </div>
                                            <div class="text-center px-2" style="flex: 1.5;">
                                                <i class="fas fa-long-arrow-alt-right text-warning fa-lg"></i>
                                                <div class="font-weight-bold text-md text-primary mt-1">Rp <?= number_format($s['amount'], 0, ',', '.') ?></div>
                                            </div>
                                            <div class="text-right" style="flex: 1;">
                                                <span class="badge badge-success font-weight-bold mb-1">Terima</span>
                                                <h6 class="font-weight-bold mb-0 text-dark"><?= esc($s['to_username']) ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card card-primary card-outline">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h3 class="card-title font-weight-bold mb-0 align-middle">
                        <i class="fas fa-file-invoice-dollar text-primary mr-1"></i> 
                        <span class="d-none d-sm-inline">Transaksi: <?= esc($selectedTrip['name']) ?></span>
                        <span class="d-sm-none">Transaksi</span>
                    </h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-success font-weight-bold" data-toggle="modal" data-target="#modalTransaction">
                            <i class="fas fa-plus mr-1"></i> <span class="d-none d-sm-inline">Catat </span>Transaksi
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    
                    <!-- DESKTOP TABLE (tersembunyi di mobile) -->
                    <div class="table-responsive txn-desktop-table">
                        <table class="table table-hover table-striped mb-0 txn-desktop-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th>Periode</th>
                                    <th>Tipe</th>
                                    <th>Pembayar (Payer)</th>
                                    <th class="text-right">Nominal</th>
                                    <th class="text-center" style="width: 70px;">Struk</th>
                                    <th class="text-center" style="width: 110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-receipt fa-2x mb-2 d-block text-warning"></i>
                                            Belum ada transaksi tercatat untuk trip/periode terpilih.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $t): ?>
                                        <tr>
                                            <td class="align-middle">
                                                <?= date('d M Y', strtotime($t['date'])) ?>
                                            </td>
                                            <td class="align-middle">
                                                <span class="font-weight-bold"><?= esc($t['description']) ?></span>
                                                <small class="text-muted d-block">
                                                    Dicatat oleh: <?= esc($t['creator_name']) ?> pada <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                                                </small>
                                                
                                                <!-- Detail Custom Split jika tipe individual -->
                                                <?php if ($t['type'] === 'individual' && !empty($t['adjustments'])): ?>
                                                    <div class="mt-2 pl-2 border-left" style="border-width: 3px !important; border-color: #17a2b8 !important;">
                                                        <span class="text-xs font-weight-bold text-info"><i class="fas fa-info-circle"></i> Beban Anggota:</span>
                                                        <ul class="list-unstyled mb-0 pl-1 text-xs">
                                                            <?php foreach ($t['adjustments'] as $adj): ?>
                                                                <li>
                                                                    <i class="far fa-user text-muted mr-1"></i><?= esc($adj['username']) ?>: 
                                                                    <span class="font-weight-bold">Rp <?= number_format($adj['amount'], 0, ',', '.') ?></span>
                                                                    <?= $adj['note'] ? '<span class="text-muted small">(' . esc($adj['note']) . ')</span>' : '' ?>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= esc($t['period_label'] ?? 'Umum / Non-Periode') ?>
                                            </td>
                                            <td class="align-middle">
                                                <?php if ($t['type'] === 'shared'): ?>
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-divide mr-1"></i> Shared</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info px-2 py-1"><i class="fas fa-user-tag mr-1"></i> Individual</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <i class="fas fa-user-circle text-muted mr-1"></i><?= esc($t['paid_by_name']) ?>
                                            </td>
                                            <td class="align-middle text-right font-weight-bold text-dark">
                                                Rp <?= number_format($t['amount'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php if ($t['receipt_image']): ?>
                                                    <a href="<?= base_url($t['receipt_image']) ?>" target="_blank" 
                                                       class="btn btn-outline-success btn-sm" title="Lihat Struk">
                                                        <i class="fas fa-receipt"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-warning btn-sm btn-edit-trans"
                                                            title="Edit Transaksi"
                                                            data-id="<?= $t['id'] ?>">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                    <?php if ($currentMembership['role'] === 'admin'): ?>
                                                        <a href="<?= base_url('backend/transactions/delete/' . $t['id']) ?>" 
                                                           class="btn btn-danger btn-sm btn-delete-trans"
                                                           title="Hapus Transaksi"
                                                           data-desc="<?= esc($t['description']) ?>"
                                                           data-amount="Rp <?= number_format($t['amount'], 0, ',', '.') ?>">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- MOBILE CARD LIST (tersembunyi di desktop) -->
                    <div class="txn-mobile-list p-2">
                        <?php if (empty($transactions)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-receipt fa-3x mb-3 d-block text-warning"></i>
                                <p>Belum ada transaksi tercatat.</p>
                                <button class="btn btn-success" data-toggle="modal" data-target="#modalTransaction">
                                    <i class="fas fa-plus mr-1"></i> Catat Transaksi Pertama
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($transactions as $t): ?>
                                <div class="txn-mobile-card type-<?= $t['type'] ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div style="flex:1; min-width:0;">
                                            <span class="font-weight-bold d-block" style="font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                <?= esc($t['description']) ?>
                                            </span>
                                            <span class="txn-meta">
                                                <?= date('d M Y', strtotime($t['date'])) ?>
                                                &bull; <?= esc($t['paid_by_name']) ?>
                                            </span>
                                        </div>
                                        <div class="text-right ml-2">
                                            <div class="txn-amount">Rp <?= number_format($t['amount'], 0, ',', '.') ?></div>
                                            <?php if ($t['type'] === 'shared'): ?>
                                                <span class="badge badge-success" style="font-size:0.68rem;"><i class="fas fa-divide mr-1"></i>Shared</span>
                                            <?php else: ?>
                                                <span class="badge badge-info" style="font-size:0.68rem;"><i class="fas fa-user-tag mr-1"></i>Individual</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($t['period_label'])): ?>
                                        <div class="txn-meta mb-1">
                                            <i class="far fa-calendar-alt mr-1"></i><?= esc($t['period_label']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($t['type'] === 'individual' && !empty($t['adjustments'])): ?>
                                        <div class="mt-1 mb-1 pl-2 border-left border-info">
                                            <span class="text-xs text-info font-weight-bold"><i class="fas fa-info-circle mr-1"></i>Beban:</span>
                                            <?php foreach ($t['adjustments'] as $adj): ?>
                                                <span class="txn-meta d-inline-block mr-2"><?= esc($adj['username']) ?>: <strong>Rp <?= number_format($adj['amount'], 0, ',', '.') ?></strong></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="txn-actions">
                                        <?php if ($t['receipt_image']): ?>
                                            <a href="<?= base_url($t['receipt_image']) ?>" target="_blank" 
                                               class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-receipt mr-1"></i>Struk
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" 
                                                class="btn btn-warning btn-sm btn-edit-trans"
                                                data-id="<?= $t['id'] ?>">
                                            <i class="fas fa-pencil-alt mr-1"></i>Edit
                                        </button>
                                        <?php if ($currentMembership['role'] === 'admin'): ?>
                                            <a href="<?= base_url('backend/transactions/delete/' . $t['id']) ?>" 
                                               class="btn btn-danger btn-sm btn-delete-trans"
                                               data-desc="<?= esc($t['description']) ?>"
                                               data-amount="Rp <?= number_format($t['amount'], 0, ',', '.') ?>">
                                                <i class="fas fa-trash-alt mr-1"></i>Hapus
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- /.txn-mobile-list -->

                </div>
                <!-- /.card-body -->
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Form Input Transaksi -->
<?php if (!empty($selectedTripId)): ?>
    <div class="modal fade" id="modalTransaction" tabindex="-1" role="dialog" aria-labelledby="modalTransactionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white font-weight-bold" id="modalTransactionLabel">
                        <i class="fas fa-receipt mr-1"></i> Catat Transaksi Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?= base_url('backend/transactions/store') ?>" method="post" id="formTransaction" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="trip_id" value="<?= $selectedTripId ?>">
                    
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left form parameters -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Deskripsi / Pengeluaran <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="description" name="description" placeholder="Contoh: Beli tiket feri, Makan malam" required minlength="3" maxlength="255">
                                </div>
                                <div class="form-group">
                                    <label for="period_id">Periode Pengeluaran <span class="text-muted">(Opsional)</span></label>
                                    <select class="form-control select2-modal" id="period_id" name="period_id" style="width: 100%;">
                                        <option value="" selected>-- Pilih Periode (Bisa diisi nanti) --</option>
                                        <?php foreach ($periods as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= (int)$p['id'] === (int)$selectedPeriodId ? 'selected' : '' ?>>
                                                <?= esc($p['label']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Membagi tagihan rata (Shared) berdasarkan anggota aktif pada periode terpilih.</small>
                                </div>
                            </div>

                            <!-- Right form parameters -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Nominal Uang (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="amount" name="amount" placeholder="Contoh: 150000" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label for="paid_by">Dibayar Oleh (Payer) <span class="text-danger">*</span></label>
                                    <select class="form-control select2-modal" id="paid_by" name="paid_by" style="width: 100%;" required>
                                        <option value="" disabled selected>-- Pilih Pembayar --</option>
                                        <?php foreach ($groupMembers as $gm): ?>
                                            <option value="<?= $gm['user_id'] ?>" <?= (int)$gm['user_id'] === (int)user_id() ? 'selected' : '' ?>>
                                                <?= esc($gm['username']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="type">Tipe Distribusi Biaya <span class="text-danger">*</span></label>
                                    <select class="form-control" id="type" name="type" required>
                                        <option value="shared" selected>Shared (Dibagi rata ke anggota aktif periode)</option>
                                        <option value="individual">Individual (Beban kustom per anggota)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Struk Pembelian - Mobile Friendly -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-camera mr-1 text-secondary"></i>
                                Struk / Bukti Pembelian <span class="text-muted">(Opsional)</span>
                            </label>
                            <!-- Hidden file inputs -->
                            <input type="file" class="d-none receipt-file-input" id="receipt_image" name="receipt_image" 
                                   accept="image/*" capture="environment">
                            <input type="file" class="d-none receipt-gallery-input" id="receipt_image_gallery" 
                                   accept="image/*">
                            
                            <div class="receipt-upload-area" id="receiptUploadArea">
                                <!-- Pilihan tombol -->
                                <div class="receipt-upload-actions" id="receiptUploadActions">
                                    <label for="receipt_image" class="btn-capture mb-0" title="Ambil foto struk langsung dengan kamera">
                                        <i class="fas fa-camera"></i>
                                        Foto Struk
                                    </label>
                                    <label for="receipt_image_gallery" class="btn-gallery mb-0" title="Pilih dari galeri foto">
                                        <i class="fas fa-images"></i>
                                        Dari Galeri
                                    </label>
                                </div>
                                <!-- Preview gambar -->
                                <div class="receipt-preview-container" id="receiptPreviewContainer">
                                    <button type="button" class="btn-remove-receipt" id="btnRemoveReceipt" title="Hapus foto">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <img src="" alt="Preview Struk" class="receipt-preview-img" id="receiptPreviewImg">
                                    <small class="text-muted d-block mt-1" id="receiptFileName"></small>
                                </div>
                            </div>
                            <small class="form-text text-muted">Format JPG/PNG, maks 5MB. Upload sebagai bukti transparansi pengeluaran.</small>
                        </div>

                        <!-- Panel Pembagian Biaya Kustom (Tipe = Individual) -->
                        <div id="individualSplitSection" style="display: none;" class="card card-outline card-info p-3 mt-2">
                            <h6 class="font-weight-bold text-info"><i class="fas fa-users-cog mr-2"></i> Rincian Pembagian Individual</h6>
                            <p class="text-muted small">Tentukan berapa nominal beban yang ditanggung masing-masing anggota. Jumlah total alokasi harus persis sama dengan total nominal transaksi.</p>
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered mb-2">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="text-center" style="width: 60px;">Beban?</th>
                                            <th>Nama Anggota</th>
                                            <th style="width: 200px;">Nominal Beban (Rp)</th>
                                            <th>Catatan / Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($groupMembers as $gm): ?>
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <div class="icheck-primary d-inline">
                                                        <input type="checkbox" 
                                                               class="target-user-checkbox" 
                                                               id="target-user-<?= $gm['user_id'] ?>" 
                                                               name="target_user[]" 
                                                               value="<?= $gm['user_id'] ?>">
                                                        <label for="target-user-<?= $gm['user_id'] ?>"></label>
                                                    </div>
                                                </td>
                                                <td class="align-middle font-weight-bold">
                                                    <?= esc($gm['username']) ?>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control form-control-sm target-amount-input" 
                                                           name="target_amount[]" 
                                                           placeholder="0" 
                                                           min="0" 
                                                           disabled>
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control form-control-sm target-note-input" 
                                                           name="target_note[]" 
                                                           placeholder="Contoh: Talangan makan" 
                                                           disabled>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="row pt-2 border-top">
                                <div class="col-sm-6 text-sm">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Total Transaksi:</span>
                                        <span class="font-weight-bold text-dark" id="displayTotalTransaction">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1 text-primary">
                                        <span>Total Teralokasi:</span>
                                        <span class="font-weight-bold" id="displayTotalAllocated">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Selisih (Sisa):</span>
                                        <span class="font-weight-bold text-danger" id="displayAllocationDiff">Rp 0</span>
                                    </div>
                                </div>
                                <div class="col-sm-6 d-flex align-items-center justify-content-end">
                                    <div id="allocationBadge" class="alert alert-warning py-1 px-3 mb-0 text-center font-weight-bold" style="font-size: 0.9rem; width: 100%;">
                                        Belum Alokasi
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold" id="submitTransBtn">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Edit Transaksi -->
<?php if (!empty($selectedTripId)): ?>
    <div class="modal fade" id="modalEditTransaction" tabindex="-1" role="dialog" aria-labelledby="modalEditTransactionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white font-weight-bold" id="modalEditTransactionLabel">
                        <i class="fas fa-pencil-alt mr-1"></i> Edit Transaksi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="editModalLoadingState" class="text-center p-5">
                    <div class="spinner-border text-warning" role="status"><span class="sr-only">Loading...</span></div>
                    <p class="mt-2 text-muted">Memuat data transaksi...</p>
                </div>
                <form action="" method="post" id="formEditTransaction" style="display:none;" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="trip_id" value="<?= $selectedTripId ?>">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_date">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_date" name="date" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_description">Deskripsi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_description" name="description" required minlength="3" maxlength="255">
                                </div>
                                <div class="form-group">
                                    <label for="edit_period_id">Periode <span class="text-muted">(Opsional)</span></label>
                                    <select class="form-control select2-edit-modal" id="edit_period_id" name="period_id" style="width:100%;">
                                        <option value="">-- Tanpa Periode --</option>
                                        <?php foreach ($periods as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= esc($p['label']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_amount">Nominal (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_amount" name="amount" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_paid_by">Dibayar Oleh (Payer) <span class="text-danger">*</span></label>
                                    <select class="form-control select2-edit-modal" id="edit_paid_by" name="paid_by" style="width:100%;" required>
                                        <option value="" disabled>-- Pilih Pembayar --</option>
                                        <?php foreach ($groupMembers as $gm): ?>
                                            <option value="<?= $gm['user_id'] ?>"><?= esc($gm['username']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit_type">Tipe Distribusi <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_type" name="type" required>
                                        <option value="shared">Shared (Dibagi rata)</option>
                                        <option value="individual">Individual (Beban kustom)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Upload Struk - Mobile Friendly -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-camera mr-1 text-secondary"></i>
                                Struk / Bukti Pembelian <span class="text-muted">(Ganti jika perlu)</span>
                            </label>
                            <!-- Hidden file inputs -->
                            <input type="file" class="d-none" id="edit_receipt_image" name="receipt_image" 
                                   accept="image/*" capture="environment">
                            <input type="file" class="d-none" id="edit_receipt_image_gallery" 
                                   accept="image/*">
                            
                            <div class="receipt-upload-area" id="editReceiptUploadArea">
                                <!-- Tombol pilih -->
                                <div class="receipt-upload-actions" id="editReceiptUploadActions">
                                    <label for="edit_receipt_image" class="btn-capture mb-0" title="Ambil foto struk langsung">
                                        <i class="fas fa-camera"></i>
                                        Foto Struk
                                    </label>
                                    <label for="edit_receipt_image_gallery" class="btn-gallery mb-0" title="Pilih dari galeri">
                                        <i class="fas fa-images"></i>
                                        Dari Galeri
                                    </label>
                                </div>
                                <!-- Preview struk tersimpan / baru -->
                                <div class="receipt-preview-container" id="editReceiptPreviewContainer">
                                    <button type="button" class="btn-remove-receipt" id="editBtnRemoveReceipt" title="Hapus foto">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <img src="" alt="Preview Struk" class="receipt-preview-img" id="editReceiptPreviewImg">
                                    <small class="text-muted d-block mt-1" id="editReceiptFileName"></small>
                                    <a href="#" id="edit_receipt_link" target="_blank" class="btn btn-xs btn-outline-info mt-1 d-none" id="editReceiptViewLink">
                                        <i class="fas fa-external-link-alt mr-1"></i> Lihat Full
                                    </a>
                                </div>
                            </div>
                            <small class="form-text text-muted">Format JPG/PNG, maks 5MB.</small>
                        </div>

                        <!-- Panel Individual Edit -->
                        <div id="editIndividualSplitSection" style="display:none;" class="card card-outline card-info p-3 mt-2">
                            <h6 class="font-weight-bold text-info"><i class="fas fa-users-cog mr-2"></i> Rincian Pembagian Individual</h6>
                            <p class="text-muted small">Jumlah total alokasi harus persis sama dengan nominal transaksi.</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered mb-2">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="text-center" style="width:60px;">Beban?</th>
                                            <th>Nama Anggota</th>
                                            <th style="width:200px;">Nominal Beban (Rp)</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($groupMembers as $gm): ?>
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <div class="icheck-primary d-inline">
                                                        <input type="checkbox" 
                                                               class="edit-target-user-checkbox" 
                                                               id="edit-target-user-<?= $gm['user_id'] ?>" 
                                                               name="target_user[]" 
                                                               value="<?= $gm['user_id'] ?>">
                                                        <label for="edit-target-user-<?= $gm['user_id'] ?>"></label>
                                                    </div>
                                                </td>
                                                <td class="align-middle font-weight-bold">
                                                    <?= esc($gm['username']) ?>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control form-control-sm edit-target-amount-input" 
                                                           name="target_amount[]" 
                                                           placeholder="0" min="0" disabled
                                                           data-user-id="<?= $gm['user_id'] ?>">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control form-control-sm edit-target-note-input" 
                                                           name="target_note[]" 
                                                           placeholder="Catatan" disabled
                                                           data-user-id="<?= $gm['user_id'] ?>">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row pt-2 border-top">
                                <div class="col-sm-6 text-sm">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Total Transaksi:</span>
                                        <span class="font-weight-bold text-dark" id="editDisplayTotal">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1 text-primary">
                                        <span>Total Teralokasi:</span>
                                        <span class="font-weight-bold" id="editDisplayAllocated">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Selisih:</span>
                                        <span class="font-weight-bold text-danger" id="editDisplayDiff">Rp 0</span>
                                    </div>
                                </div>
                                <div class="col-sm-6 d-flex align-items-center justify-content-end">
                                    <div id="editAllocationBadge" class="alert alert-warning py-1 px-3 mb-0 text-center font-weight-bold" style="font-size:0.9rem;width:100%;">
                                        Belum Alokasi
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-white font-weight-bold" id="submitEditTransBtn">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Inisialisasi Select2 di Modal (butuh container parent agar scrollable/render pas)
    $('.select2-modal').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalTransaction')
    });

    // =============================================
    // RECEIPT UPLOAD: Kamera & Galeri Handler (Modal Tambah)
    // =============================================
    function setupReceiptUpload(cameraInputId, galleryInputId, previewContainerId, previewImgId, fileNameId, removeBtn) {
        const cameraInput  = document.getElementById(cameraInputId);
        const galleryInput = document.getElementById(galleryInputId);
        const previewCont  = document.getElementById(previewContainerId);
        const previewImg   = document.getElementById(previewImgId);
        const fileNameEl   = document.getElementById(fileNameId);
        const removeBtnEl  = document.getElementById(removeBtn);

        function handleFile(file, targetInput) {
            if (!file) return;
            // Sinkronkan ke input utama (cameraInput) untuk submit form
            // Buat DataTransfer untuk copy file ke input lain
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                cameraInput.files = dt.files;
            } catch(e) { /* Safari fallback - file tetap pada input aslinya */ }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewCont.style.display = 'block';
                if (fileNameEl) fileNameEl.textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
            };
            reader.readAsDataURL(file);
        }

        if (cameraInput) {
            cameraInput.addEventListener('change', function() {
                if (this.files && this.files[0]) handleFile(this.files[0], this);
            });
        }
        if (galleryInput) {
            galleryInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleFile(this.files[0], this);
                    // Copy file ke cameraInput untuk submit
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(this.files[0]);
                        cameraInput.files = dt.files;
                    } catch(e) {}
                }
            });
        }
        if (removeBtnEl) {
            removeBtnEl.addEventListener('click', function() {
                if (cameraInput)  cameraInput.value  = '';
                if (galleryInput) galleryInput.value = '';
                previewImg.src = '';
                previewCont.style.display = 'none';
                if (fileNameEl) fileNameEl.textContent = '';
            });
        }
    }

    // Setup untuk modal Tambah Transaksi
    setupReceiptUpload(
        'receipt_image',
        'receipt_image_gallery',
        'receiptPreviewContainer',
        'receiptPreviewImg',
        'receiptFileName',
        'btnRemoveReceipt'
    );
    // Setup untuk modal Edit Transaksi
    setupReceiptUpload(
        'edit_receipt_image',
        'edit_receipt_image_gallery',
        'editReceiptPreviewContainer',
        'editReceiptPreviewImg',
        'editReceiptFileName',
        'editBtnRemoveReceipt'
    );

    // =============================================
    // MOBILE: Auto collapse filter panel on small screen
    // =============================================
    if (window.innerWidth < 768) {
        $('#filterPanel').collapse('hide');
    }

    // Event Handler Hapus Transaksi (Konfirmasi SweetAlert2)
    $('.btn-delete-trans').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const desc = $(this).data('desc');
        const amount = $(this).data('amount');

        Swal.fire({
            title: 'Hapus Transaksi?',
            html: `Apakah Anda yakin ingin menghapus transaksi <strong>"${desc}"</strong> sebesar <strong>${amount}</strong>?<br><span class="text-danger small">Tindakan ini akan mempengaruhi rekapitulasi saldo grup.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    // Logic Form Modal
    const typeSelect = $('#type');
    const individualSection = $('#individualSplitSection');
    const amountInput = $('#amount');
    const submitBtn = $('#submitTransBtn');

    // Menampilkan/menyembunyikan bagian pembagian kustom
    typeSelect.on('change', function() {
        if ($(this).val() === 'individual') {
            individualSection.slideDown();
            validateAllocation();
        } else {
            individualSection.slideUp();
            submitBtn.prop('disabled', false); // Kembalikan default
        }
    });

    // Jalankan pengecekan saat modal terbuka kembali
    $('#modalTransaction').on('shown.bs.modal', function () {
        if (typeSelect.val() === 'individual') {
            individualSection.show();
            validateAllocation();
        } else {
            individualSection.hide();
            submitBtn.prop('disabled', false);
        }
    });

    // Event check checkbox target user
    $('.target-user-checkbox').on('change', function() {
        const row = $(this).closest('tr');
        const amountField = row.find('.target-amount-input');
        const noteField = row.find('.target-note-input');

        if ($(this).is(':checked')) {
            amountField.prop('disabled', false).attr('required', true).focus();
            noteField.prop('disabled', false);
            // Isi nominal default jika kosong (misal sisa alokasi)
            if (!amountField.val() || parseInt(amountField.val()) === 0) {
                const total = parseInt(amountInput.val()) || 0;
                const currentAllocated = calculateAllocatedSum();
                const remainder = total - currentAllocated;
                if (remainder > 0) {
                    amountField.val(remainder);
                }
            }
        } else {
            amountField.prop('disabled', true).removeAttr('required').val('');
            noteField.prop('disabled', true).val('');
        }
        validateAllocation();
    });

    // Event input nominal berubah
    amountInput.on('input', function() {
        validateAllocation();
    });

    $('.target-amount-input').on('input', function() {
        validateAllocation();
    });

    // Fungsi menghitung jumlah nominal yang teralokasi
    function calculateAllocatedSum() {
        let sum = 0;
        $('.target-amount-input').each(function() {
            const val = parseInt($(this).val()) || 0;
            if (!$(this).prop('disabled')) {
                sum += val;
            }
        });
        return sum;
    }

    // Fungsi memvalidasi alokasi nominal
    function validateAllocation() {
        // Hanya validasi jika tipe individual
        if (typeSelect.val() !== 'individual') {
            submitBtn.prop('disabled', false);
            return;
        }

        const totalAmount = parseInt(amountInput.val()) || 0;
        const allocatedSum = calculateAllocatedSum();
        const difference = totalAmount - allocatedSum;

        // Tampilkan info angka
        $('#displayTotalTransaction').text('Rp ' + formatRupiah(totalAmount));
        $('#displayTotalAllocated').text('Rp ' + formatRupiah(allocatedSum));
        $('#displayAllocationDiff').text('Rp ' + formatRupiah(difference));

        const badge = $('#allocationBadge');

        if (totalAmount <= 0) {
            badge.removeClass('alert-success alert-danger').addClass('alert-warning')
                 .text('Masukkan Total Transaksi');
            submitBtn.prop('disabled', true);
            return;
        }

        // Cek keaktifan minimal 1 checkbox
        const checkedCount = $('.target-user-checkbox:checked').length;
        if (checkedCount === 0) {
            badge.removeClass('alert-success alert-danger').addClass('alert-warning')
                 .text('Pilih Minimal 1 Anggota');
            submitBtn.prop('disabled', true);
            return;
        }

        if (difference === 0) {
            // Jumlah alokasi cocok!
            badge.removeClass('alert-warning alert-danger').addClass('alert-success')
                 .html('<i class="fas fa-check-circle mr-1"></i> Cocok / Valid');
            submitBtn.prop('disabled', false);
            $('#displayAllocationDiff').removeClass('text-danger').addClass('text-success');
        } else {
            // Belum cocok
            badge.removeClass('alert-warning alert-success').addClass('alert-danger')
                 .html('<i class="fas fa-times-circle mr-1"></i> Selisih Alokasi');
            submitBtn.prop('disabled', true);
            $('#displayAllocationDiff').removeClass('text-success').addClass('text-danger');
        }
    }

    // Helper format ribuan rupiah
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // Ekspor Excel Client-side
    $('.btn-export-excel').on('click', function() {
        const table = document.getElementById('rekapTable');
        if (!table) return;

        let html = table.outerHTML;
        const template = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="UTF-8">
                <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Rekap Saldo</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
                <style>
                    table { border-collapse: collapse; width: 100%; font-family: sans-serif; }
                    th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
                    th { background-color: #28a745; color: white; font-weight: bold; }
                    .text-right { text-align: right; }
                    .text-left { text-align: left; }
                    .font-weight-bold { font-weight: bold; }
                </style>
            </head>
            <body>
                <h3 style="text-align: center; margin-bottom: 20px;">Rekapitulasi Pembagian Saldo Keluarga - Periode: <?= esc($calculationResult['period']['label'] ?? '') ?></h3>
                ${html}
            </body>
            </html>
        `;

        const blob = new Blob([template], { type: 'application/vnd.ms-excel' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'rekap_saldo_<?= esc($calculationResult['period']['label'] ?? 'periode') ?>.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // =============================================
    // Edit Modal Logic
    // =============================================

    // Inisialisasi Select2 di Edit Modal
    $('.select2-edit-modal').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalEditTransaction')
    });

    const editTypeSelect  = $('#edit_type');
    const editAmountInput = $('#edit_amount');
    const editSubmitBtn   = $('#submitEditTransBtn');
    const editIndividualSection = $('#editIndividualSplitSection');

    // Toggle edit individual section
    editTypeSelect.on('change', function() {
        if ($(this).val() === 'individual') {
            editIndividualSection.slideDown();
            validateEditAllocation();
        } else {
            editIndividualSection.slideUp();
            editSubmitBtn.prop('disabled', false);
        }
    });

    // Re-check on modal shown
    $('#modalEditTransaction').on('shown.bs.modal', function() {
        if (editTypeSelect.val() === 'individual') {
            editIndividualSection.show();
            validateEditAllocation();
        } else {
            editIndividualSection.hide();
            editSubmitBtn.prop('disabled', false);
        }
    });

    // Reset modal on hidden
    $('#modalEditTransaction').on('hidden.bs.modal', function() {
        $('#editModalLoadingState').show();
        $('#formEditTransaction').hide();
        // Reset all checkboxes & inputs
        $('.edit-target-user-checkbox').prop('checked', false);
        $('.edit-target-amount-input').prop('disabled', true).val('');
        $('.edit-target-note-input').prop('disabled', true).val('');
        editIndividualSection.hide();
        // Reset receipt preview
        const editPreviewCont = document.getElementById('editReceiptPreviewContainer');
        const editPreviewImg  = document.getElementById('editReceiptPreviewImg');
        if (editPreviewImg)  editPreviewImg.src = '';
        if (editPreviewCont) editPreviewCont.style.display = 'none';
    });

    // Edit button click – AJAX fetch
    $(document).on('click', '.btn-edit-trans', function() {
        const transId = $(this).data('id');
        $('#modalEditTransaction').modal('show');

        // Reset loading state
        $('#editModalLoadingState').show();
        $('#formEditTransaction').hide();

        $.ajax({
            url: '<?= base_url('backend/transactions/get/') ?>' + transId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (!data || data.status === 'error') {
                    Swal.fire('Error', data.message || 'Gagal memuat data transaksi.', 'error');
                    $('#modalEditTransaction').modal('hide');
                    return;
                }

                const t = data.transaction;

                // Set form action
                $('#formEditTransaction').attr('action', '<?= base_url('backend/transactions/update/') ?>' + t.id);

                // Fill basic fields
                $('#edit_date').val(t.date);
                $('#edit_description').val(t.description);
                $('#edit_amount').val(t.amount);
                $('#edit_type').val(t.type).trigger('change.select2');
                $('#edit_period_id').val(t.period_id || '').trigger('change.select2');
                $('#edit_paid_by').val(data.transaction.paid_by).trigger('change');
                $('#edit_type').val(data.transaction.type).trigger('change');

                // Handle Receipt Image Preview (new UI)
                const editPreviewCont = document.getElementById('editReceiptPreviewContainer');
                const editPreviewImg  = document.getElementById('editReceiptPreviewImg');
                const editReceiptLink = document.getElementById('edit_receipt_link');
                const editFileNameEl  = document.getElementById('editReceiptFileName');

                if (data.transaction.receipt_image) {
                    const receiptUrl = '<?= base_url() ?>/' + data.transaction.receipt_image;
                    editPreviewImg.src = receiptUrl;
                    editPreviewCont.style.display = 'block';
                    if (editFileNameEl) editFileNameEl.textContent = 'Struk tersimpan';
                    if (editReceiptLink) {
                        editReceiptLink.href = receiptUrl;
                        editReceiptLink.classList.remove('d-none');
                    }
                } else {
                    editPreviewImg.src = '';
                    editPreviewCont.style.display = 'none';
                    if (editFileNameEl) editFileNameEl.textContent = '';
                    if (editReceiptLink) editReceiptLink.classList.add('d-none');
                }

                // Handle individual adjustments
                if (t.type === 'individual') {
                    editIndividualSection.show();

                    // Reset all rows first
                    $('.edit-target-user-checkbox').prop('checked', false);
                    $('.edit-target-amount-input').prop('disabled', true).val('').removeAttr('required');
                    $('.edit-target-note-input').prop('disabled', true).val('');

                    // Fill rows from adjustments
                    if (data.adjustments && data.adjustments.length > 0) {
                        $.each(data.adjustments, function(i, adj) {
                            const uid  = adj.target_user_id;
                            const cbx  = $('#edit-target-user-' + uid);
                            const amt  = $('[data-user-id="' + uid + '"].edit-target-amount-input');
                            const note = $('[data-user-id="' + uid + '"].edit-target-note-input');

                            cbx.prop('checked', true);
                            amt.prop('disabled', false).attr('required', true).val(adj.amount);
                            note.prop('disabled', false).val(adj.note || '');
                        });
                    }

                    editTypeSelect.val('individual').trigger('change.select2');
                    validateEditAllocation();
                } else {
                    editIndividualSection.hide();
                    editTypeSelect.val('shared').trigger('change.select2');
                    editSubmitBtn.prop('disabled', false);
                }

                $('#editModalLoadingState').hide();
                $('#formEditTransaction').show();
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan saat mengambil data transaksi.', 'error');
                $('#modalEditTransaction').modal('hide');
            }
        });
    });

    // Edit checkbox toggle
    $(document).on('change', '.edit-target-user-checkbox', function() {
        const row       = $(this).closest('tr');
        const amtField  = row.find('.edit-target-amount-input');
        const noteField = row.find('.edit-target-note-input');

        if ($(this).is(':checked')) {
            amtField.prop('disabled', false).attr('required', true).focus();
            noteField.prop('disabled', false);
            if (!amtField.val() || parseInt(amtField.val()) === 0) {
                const total = parseInt(editAmountInput.val()) || 0;
                const currentAllocated = calculateEditAllocatedSum();
                const remainder = total - currentAllocated;
                if (remainder > 0) amtField.val(remainder);
            }
        } else {
            amtField.prop('disabled', true).removeAttr('required').val('');
            noteField.prop('disabled', true).val('');
        }
        validateEditAllocation();
    });

    editAmountInput.on('input', function() { validateEditAllocation(); });
    $(document).on('input', '.edit-target-amount-input', function() { validateEditAllocation(); });

    function calculateEditAllocatedSum() {
        let sum = 0;
        $('.edit-target-amount-input').each(function() {
            if (!$(this).prop('disabled')) sum += parseInt($(this).val()) || 0;
        });
        return sum;
    }

    function validateEditAllocation() {
        if (editTypeSelect.val() !== 'individual') {
            editSubmitBtn.prop('disabled', false);
            return;
        }

        const totalAmount  = parseInt(editAmountInput.val()) || 0;
        const allocatedSum = calculateEditAllocatedSum();
        const difference   = totalAmount - allocatedSum;

        $('#editDisplayTotal').text('Rp ' + formatRupiah(totalAmount));
        $('#editDisplayAllocated').text('Rp ' + formatRupiah(allocatedSum));
        $('#editDisplayDiff').text('Rp ' + formatRupiah(difference));

        const badge = $('#editAllocationBadge');
        const checkedCount = $('.edit-target-user-checkbox:checked').length;

        if (totalAmount <= 0) {
            badge.removeClass('alert-success alert-danger').addClass('alert-warning').text('Masukkan Total Transaksi');
            editSubmitBtn.prop('disabled', true);
            return;
        }
        if (checkedCount === 0) {
            badge.removeClass('alert-success alert-danger').addClass('alert-warning').text('Pilih Minimal 1 Anggota');
            editSubmitBtn.prop('disabled', true);
            return;
        }
        if (difference === 0) {
            badge.removeClass('alert-warning alert-danger').addClass('alert-success')
                 .html('<i class="fas fa-check-circle mr-1"></i> Cocok / Valid');
            editSubmitBtn.prop('disabled', false);
            $('#editDisplayDiff').removeClass('text-danger').addClass('text-success');
        } else {
            badge.removeClass('alert-warning alert-success').addClass('alert-danger')
                 .html('<i class="fas fa-times-circle mr-1"></i> Selisih Alokasi');
            editSubmitBtn.prop('disabled', true);
            $('#editDisplayDiff').removeClass('text-success').addClass('text-danger');
        }
    }

    // =============================================
    // Cetak PDF/Cetak Card
    // =============================================

    // Cetak PDF/Cetak Card
    $('.btn-print-rekap').on('click', function() {
        const table = document.getElementById('rekapTable');
        if (!table) return;

        const printWindow = window.open('', '_blank', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Cetak Rekapitulasi</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
        printWindow.document.write('<style>');
        printWindow.document.write('body { padding: 30px; font-family: sans-serif; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
        printWindow.document.write('th, td { border: 1px solid #dee2e6; padding: 12px; text-align: center; vertical-align: middle; }');
        printWindow.document.write('th { background-color: #28a745 !important; color: white !important; font-weight: bold; }');
        printWindow.document.write('.badge-success { background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }');
        printWindow.document.write('.badge-secondary { background-color: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }');
        printWindow.document.write('.text-success { color: #28a745 !important; }');
        printWindow.document.write('.text-danger { color: #dc3545 !important; }');
        printWindow.document.write('.text-right { text-align: right !important; }');
        printWindow.document.write('.text-left { text-align: left !important; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="text-center mb-4">');
        printWindow.document.write('<h2>Aplikasi Split Bill Keluarga</h2>');
        printWindow.document.write('<h5>Rekapitulasi Pembagian Saldo</h5>');
        printWindow.document.write('<p class="text-muted">Periode: <?= esc($calculationResult['period']['label'] ?? "") ?></p>');
        printWindow.document.write('</div>');
        printWindow.document.write(table.outerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        setTimeout(function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }, 500);
    });
});
</script>
<?= $this->endSection() ?>
