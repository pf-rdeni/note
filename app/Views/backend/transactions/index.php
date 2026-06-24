<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<!-- Alert Flash Data -->
<div class="row">
    <div class="col-12">
        <?php if (session()->getFlashdata('success')) : ?>
            <!-- Success alert is handled via SweetAlert2 at the bottom of the page -->
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

<?php
// Capture filter panel HTML in a variable to avoid duplication
ob_start();
?>
<!-- Mobile filter toggle — tampilkan pilihan terakhir -->
<button class="btn btn-outline-primary btn-sm w-100 mobile-filter-toggle d-lg-none mb-2" type="button"
        data-toggle="collapse" data-target="#filterPanel"
        aria-expanded="false" aria-controls="filterPanel" id="filterToggleBtn">
    <i class="fas fa-filter mr-1"></i>
    <span id="filterToggleLabel">
        <?php
        if (!empty($selectedTrip)) {
            $currentPeriodLabel = 'Pilih Periode';
            if (!empty($selectedPeriodId)) {
                foreach ($periods as $pItem) {
                    if ((int)$pItem['id'] === (int)$selectedPeriodId) {
                        $currentPeriodLabel = $pItem['label'];
                        break;
                    }
                }
            }
            echo esc($selectedTrip['name']) . ' &middot; ' . esc($currentPeriodLabel);
        } elseif (!empty($selectedGroup)) {
            echo 'Grup: ' . esc($selectedGroup['name']);
        } else {
            echo 'Filter Kegiatan &amp; Periode';
        }
        ?>
    </span>
    <i class="fas fa-chevron-down ml-1 fa-xs"></i>
</button>

<div class="<?= (!empty($selectedTripId) || !empty($selectedGroupId)) ? 'collapse' : 'collapse show' ?> d-lg-block mb-3" id="filterPanel">
    <div class="card card-primary card-outline shadow-sm mb-0">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold mb-0 text-sm" style="line-height: 1.8;">
                <i class="fas fa-filter mr-1"></i> Pilihan Kegiatan &amp; Periode
            </h3>
            <?php if (!empty($selectedTripId)): ?>
                <div class="card-tools d-lg-none">
                    <button type="button" class="btn btn-tool" id="btnCollapseFilter">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-body p-3">
            <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                <label class="mb-0 font-weight-bold text-secondary" style="font-size: 0.95rem;">Filter Kegiatan &amp; Periode:</label>
                <div class="dropdown custom-tree-dropdown flex-fill" style="position: relative; max-width: 500px;">
                    <?php 
                    $selectedLabel = 'Pilih Kegiatan atau Periode...';
                    if (!empty($selectedTrip)) {
                        $selectedLabel = esc($selectedTrip['name']);
                        if (!empty($selectedPeriodId)) {
                            foreach ($periods as $p) {
                                if ((int)$p['id'] === (int)$selectedPeriodId) {
                                    $selectedLabel .= ' / ' . esc($p['label']);
                                    break;
                                }
                            }
                        }
                    } elseif (!empty($selectedGroup)) {
                        $selectedLabel = 'Grup: ' . esc($selectedGroup['name']);
                    }
                    ?>
                    <button class="btn btn-outline-secondary dropdown-toggle text-left w-100 font-weight-bold d-flex justify-content-between align-items-center shadow-xs" 
                            type="button" 
                            id="filterTreeDropdownBtn" 
                            data-toggle="dropdown" 
                            aria-haspopup="true" 
                            aria-expanded="false" 
                            style="border-color: #ced4da; border-radius: 8px; height: calc(2.25rem + 10px); background: #fff; color: #495057; font-size: 0.9rem;">
                        <span id="selectedFilterLabel"><?= $selectedLabel ?></span>
                    </button>
                    <div class="dropdown-menu p-3 shadow-lg border-0" 
                         aria-labelledby="filterTreeDropdownBtn" 
                         style="max-height: 400px; overflow-y: auto; border-radius: 12px; min-width: 320px; width: 100%;">
                        
                        <!-- Search Input -->
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0" style="border-radius: 8px 0 0 8px;">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                            </div>
                            <input type="text" id="filterTreeSearchInput" class="form-control border-left-0" placeholder="Cari grup, kegiatan, atau periode..." style="border-radius: 0 8px 8px 0;">
                        </div>

                        <!-- Tree List -->
                        <ul class="list-unstyled mb-0" id="filterDropdownTreeList">
                            <?php foreach ($filterHierarchy as $gid => $gInfo): ?>
                                <li class="group-node mb-2" data-name="<?= esc(strtolower($gInfo['name'])) ?>">
                                    <div class="d-flex align-items-center py-1 font-weight-bold text-dark node-header-wrapper" 
                                         style="cursor: pointer; justify-content: space-between; font-size: 0.95rem; gap: 8px;">
                                        <div class="d-flex align-items-center node-header-click" style="gap: 8px;">
                                            <i class="fas fa-chevron-right text-muted node-arrow"></i>
                                            <i class="fas fa-users text-primary"></i>
                                            <span><?= esc($gInfo['name']) ?></span>
                                        </div>
                                        <a href="<?= base_url('backend/transactions?group_id=' . $gid) ?>" 
                                           class="btn btn-xs btn-outline-primary py-0 px-2 ml-auto select-group-node"
                                           data-id="<?= $gid ?>"
                                           data-label="<?= esc($gInfo['name']) ?>"
                                           style="font-size: 0.72rem; border-radius: 4px;">
                                            Pilih Semua
                                        </a>
                                    </div>
                                    <ul class="list-unstyled pl-4 d-none nested-list mt-1">
                                        <?php foreach ($gInfo['trips'] as $tid => $tInfo): ?>
                                            <li class="trip-node mb-2" data-name="<?= esc(strtolower($tInfo['name'])) ?>">
                                                <div class="d-flex align-items-center py-1 font-weight-bold text-secondary node-header-wrapper" 
                                                     style="cursor: pointer; justify-content: space-between; font-size: 0.88rem; gap: 8px;">
                                                    <div class="d-flex align-items-center node-header-click" style="gap: 8px;">
                                                        <i class="fas fa-chevron-right text-muted node-arrow"></i>
                                                        <i class="fas fa-suitcase-rolling text-success"></i>
                                                        <span><?= esc($tInfo['name']) ?></span>
                                                    </div>
                                                    <a href="<?= base_url('backend/transactions?trip_id=' . $tid) ?>" 
                                                       class="btn btn-xs btn-outline-primary py-0 px-2 ml-auto select-trip-node"
                                                       data-id="<?= $tid ?>"
                                                       data-label="<?= esc($tInfo['name']) ?>"
                                                       style="font-size: 0.72rem; border-radius: 4px;">
                                                        Pilih Semua
                                                    </a>
                                                </div>
                                                <ul class="list-unstyled pl-4 d-none nested-list mt-1">
                                                    <?php 
                                                    $openPeriodsList = array_filter($tInfo['periods'], fn($p) => (($p['status'] ?? 'open') === 'open'));
                                                    $settledPeriodsList = array_filter($tInfo['periods'], fn($p) => (($p['status'] ?? 'open') === 'settled'));
                                                    ?>

                                                    <?php if (!empty($openPeriodsList)): ?>
                                                        <li class="status-node mb-1">
                                                            <div class="d-flex align-items-center py-1 font-weight-bold text-success node-header" 
                                                                 style="cursor: pointer; gap: 8px; font-size: 0.8rem;">
                                                                <i class="fas fa-chevron-right text-muted node-arrow"></i>
                                                                <i class="fas fa-unlock text-success"></i>
                                                                <span>Periode Terbuka (Open)</span>
                                                            </div>
                                                            <ul class="list-unstyled pl-3 d-none nested-list mt-1">
                                                                <?php foreach ($openPeriodsList as $p): ?>
                                                                    <li class="period-node py-1 px-2 rounded hover-item d-flex justify-content-between align-items-center" 
                                                                        data-id="<?= $p['id'] ?>" 
                                                                        data-trip-id="<?= $tid ?>"
                                                                        data-label="<?= esc($tInfo['name'] . ' / ' . $p['label']) ?>"
                                                                        data-name="<?= esc(strtolower($p['label'])) ?>"
                                                                        style="cursor: pointer; font-size: 0.85rem; transition: background-color 0.15s;">
                                                                        <div>
                                                                            <i class="far fa-calendar-alt text-info mr-2"></i>
                                                                            <span class="text-dark font-weight-bold"><?= esc($p['label']) ?></span>
                                                                        </div>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </li>
                                                    <?php endif; ?>

                                                    <?php if (!empty($settledPeriodsList)): ?>
                                                        <li class="status-node mb-1">
                                                            <div class="d-flex align-items-center py-1 font-weight-bold text-secondary node-header" 
                                                                 style="cursor: pointer; gap: 8px; font-size: 0.8rem;">
                                                                <i class="fas fa-chevron-right text-muted node-arrow"></i>
                                                                <i class="fas fa-lock text-secondary"></i>
                                                                <span>Periode Selesai (Settled)</span>
                                                            </div>
                                                            <ul class="list-unstyled pl-3 d-none nested-list mt-1">
                                                                <?php foreach ($settledPeriodsList as $p): ?>
                                                                    <li class="period-node py-1 px-2 rounded hover-item d-flex justify-content-between align-items-center" 
                                                                        data-id="<?= $p['id'] ?>" 
                                                                        data-trip-id="<?= $tid ?>"
                                                                        data-label="<?= esc($tInfo['name'] . ' / ' . $p['label']) ?>"
                                                                        data-name="<?= esc(strtolower($p['label'])) ?>"
                                                                        style="cursor: pointer; font-size: 0.85rem; transition: background-color 0.15s;">
                                                                        <div>
                                                                            <i class="fas fa-lock text-secondary mr-2"></i>
                                                                            <span class="text-dark font-weight-bold"><?= esc($p['label']) ?></span>
                                                                        </div>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div id="noFilterTreeResults" class="text-center py-3 text-muted d-none" style="font-size: 0.85rem;">
                            <i class="fas fa-search mb-2 fa-lg d-block"></i>
                            Tidak ada kecocokan
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($selectedTripId) || !empty($selectedGroupId)): ?>
                    <a href="<?= base_url('backend/transactions?reset=1') ?>" class="btn btn-outline-danger btn-sm btn-reset-filter" style="border-radius: 8px;">
                        <i class="fas fa-redo mr-1"></i> Reset Filter
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$filterPanelHtml = ob_get_clean();
?>

<div class="row">
    <div class="col-lg-12">
        <?php if (empty($selectedTripId) && empty($selectedGroupId)): ?>
            
            <!-- Tampilkan Filter Selection di Atas jika belum pilih kegiatan -->
            <?= $filterPanelHtml ?>
            
            <div class="card card-outline card-warning text-center py-5 mt-3">
                <div class="card-body">
                    <i class="fas fa-clipboard-list text-warning fa-3x mb-3"></i>
                    <h4>Pilih Kegiatan Terlebih Dahulu</h4>
                    <p class="text-muted">Untuk mencatat transaksi, pastikan Anda telah membuat atau bergabung ke suatu Group dan Kegiatan.</p>
                    <a href="<?= base_url('backend/trips') ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-right mr-1"></i> Buka Manajemen Kegiatan
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            
            <!-- 1. Summary Widgets -->
            <?php if (!empty($calculationResult)): ?>
                <?php
                $waSummaryText = '';
                if (empty($calculationResult['is_all_periods'])) {
                    // Generate WhatsApp Markdown Summary
                    $tripName = $selectedTrip['name'] ?? '';
                    $periodLabel = $calculationResult['period']['label'] ?? '';
                    $totalBelanja = number_format($calculationResult['summary']['total_transactions'], 0, ',', '.');

                    // WhatsApp Markdown (Detailed per-participant calculation format)
                    $waSummaryText = "🟢 *REKAPITULASI PEMBAGIAN SALDO*\n";
                    $waSummaryText .= "*Agenda:* " . $tripName . "\n";
                    $waSummaryText .= "*Periode:* " . $periodLabel . "\n";
                    $waSummaryText .= "*Total Pengeluaran:* Rp " . $totalBelanja . "\n";
                    
                    $activeMemberCount = 0;
                    foreach ($calculationResult['participants'] as $p) {
                        if ($p['is_active_member']) {
                            $activeMemberCount++;
                        }
                    }
                    $splitRataVal = number_format($calculationResult['summary']['split_rata'], 0, ',', '.');
                    $waSummaryText .= "*Bagi Rata/Orang:* Rp " . $splitRataVal . "\n";
                    $waSummaryText .= "----------------------------------------\n\n";
                    
                    $waSummaryText .= "*Rincian Per Anggota:*\n";
                    foreach ($calculationResult['participants'] as $p) {
                        $bal = $p['net_balance'];
                        $sign = $bal >= 0 ? '+' : '-';
                        $status = $bal >= 0 ? 'Terima Saldo' : 'Bayar/Hutang';
                        
                        $waSummaryText .= "👤 *" . $p['username'] . "*\n";
                        $waSummaryText .= "  - Total Bayar: Rp " . number_format($p['total_paid'], 0, ',', '.') . "\n";
                        $waSummaryText .= "  - Bagi Rata: Rp " . number_format($p['shared_share'], 0, ',', '.') . "\n";
                        $waSummaryText .= "  - Murni Pribadi: Rp " . number_format($p['individual_charge'], 0, ',', '.') . "\n";
                        $waSummaryText .= "  - Saldo Akhir: *" . $sign . "Rp " . number_format(abs($bal), 0, ',', '.') . "* (" . $status . ")\n\n";
                    }
                    
                    $waSummaryText .= "----------------------------------------\n";
                    $waSummaryText .= "🤝 *REKOMENDASI PELUNASAN*\n";
                    if (empty($calculationResult['settlements'])) {
                        $waSummaryText .= "Semua saldo seimbang! Tidak ada transfer yang diperlukan.\n";
                    } else {
                        foreach ($calculationResult['settlements'] as $s) {
                            $waSummaryText .= "👉 *" . $s['from_username'] . "* transfer ke *" . $s['to_username'] . "* sebesar *Rp " . number_format($s['amount'], 0, ',', '.') . "*\n";
                        }
                    }
                    $waSummaryText .= "\nDetail selengkapnya lihat di aplikasi: https://note.simpedis.com";
                }
                ?>
                <div class="row" style="gap: 0;">
                    <div class="col-6 col-md-3 mb-3 px-1 px-md-2">
                        <div class="summary-stat-card" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                            <div class="summary-stat-icon"><i class="fas fa-wallet"></i></div>
                            <div class="summary-stat-label">Total Pengeluaran</div>
                            <div class="summary-stat-value">Rp <?= number_format($calculationResult['summary']['total_transactions'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3 px-1 px-md-2">
                        <div class="summary-stat-card" style="background: linear-gradient(135deg, #22c55e, #15803d);">
                            <div class="summary-stat-icon"><i class="fas fa-divide"></i></div>
                            <div class="summary-stat-label">Beban Shared</div>
                            <div class="summary-stat-value">Rp <?= number_format($calculationResult['summary']['total_shared'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3 px-1 px-md-2">
                        <div class="summary-stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <div class="summary-stat-icon"><i class="fas fa-user-friends"></i></div>
                            <div class="summary-stat-label">Bagi Rata / Orang</div>
                            <div class="summary-stat-value">Rp <?= number_format($calculationResult['summary']['split_rata'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3 px-1 px-md-2">
                        <div class="summary-stat-card" style="background: linear-gradient(135deg, #06b6d4, #0e7490);">
                            <div class="summary-stat-icon"><i class="fas fa-user-tag"></i></div>
                            <div class="summary-stat-label">Beban Kustom</div>
                            <div class="summary-stat-value">Rp <?= number_format($calculationResult['summary']['total_individual'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 2. Filter Panel di bawah Summary Card -->
            <?= $filterPanelHtml ?>

            <?php if (!empty($calculationResult) && empty($calculationResult['is_all_periods'])): ?>
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
                                <i class="fas fa-file-pdf mr-1"></i> <span class="d-none d-sm-inline">Unduh </span>PDF
                            </button>
                            <button type="button" class="btn btn-xs font-weight-bold btn-share-wa" style="background-color: #25D366; border-color: #25D366; color: #fff;">
                                <i class="fab fa-whatsapp mr-1"></i> <span class="d-none d-sm-inline">Bagikan ke </span>WhatsApp
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

            <?php
            // === Peta status periode: period_id => status ===
            $periodStatusMap = [];
            foreach ($periods as $pItem) {
                $periodStatusMap[(int)$pItem['id']] = $pItem['status'] ?? 'open';
            }

            // Apakah periode terpilih sudah settled?
            $selectedPeriodSettled = !empty($selectedPeriodId)
                && isset($periodStatusMap[(int)$selectedPeriodId])
                && $periodStatusMap[(int)$selectedPeriodId] === 'settled';
            ?>

            <div class="card card-primary card-outline">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h3 class="card-title font-weight-bold mb-0 align-middle">
                        <i class="fas fa-file-invoice-dollar text-primary mr-1"></i> 
                        <?php if (!empty($selectedTrip)): ?>
                            <span class="d-none d-sm-inline">Transaksi: <?= esc($selectedTrip['name']) ?></span>
                        <?php elseif (!empty($selectedGroup)): ?>
                            <span class="d-none d-sm-inline">Transaksi Grup: <?= esc($selectedGroup['name']) ?></span>
                        <?php endif; ?>
                        <span class="d-sm-none">Transaksi</span>
                        <?php if ($selectedPeriodSettled): ?>
                            <span class="badge badge-secondary ml-2 py-1 px-2" style="font-size:0.72rem;">
                                <i class="fas fa-lock mr-1"></i>Periode Terkunci
                            </span>
                        <?php endif; ?>
                    </h3>
                    <div class="card-tools ml-auto">
                        <?php if (!empty($selectedGroupId)): ?>
                            <span class="btn btn-secondary font-weight-bold disabled" title="Pilih Kegiatan terlebih dahulu untuk mencatat transaksi baru">
                                <i class="fas fa-plus mr-1"></i> Catat Transaksi
                            </span>
                        <?php elseif ($selectedPeriodSettled): ?>
                            <span class="btn btn-secondary font-weight-bold disabled" title="Periode sudah ditutup, tidak bisa tambah transaksi baru">
                                <i class="fas fa-lock mr-1"></i> <span class="d-none d-sm-inline">Periode </span>Terkunci
                            </span>
                        <?php else: ?>
                            <button type="button" class="btn btn-success font-weight-bold" data-toggle="modal" data-target="#modalTransaction">
                                <i class="fas fa-plus mr-1"></i> <span class="d-none d-sm-inline">Catat </span>Transaksi
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    
                    <!-- DESKTOP TABLE (tersembunyi di mobile) -->
                    <div class="table-responsive txn-desktop-table">
                        <table class="table table-hover table-striped mb-0 txn-desktop-table" id="txnTable">
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
                                    <tr class="no-data">
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-receipt fa-2x mb-2 d-block text-warning"></i>
                                            Belum ada transaksi tercatat untuk kegiatan/periode terpilih.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $t): ?>
                                        <tr>
                                            <td class="align-middle" data-order="<?= strtotime($t['date']) ?>">
                                                <?= date('d M Y', strtotime($t['date'])) ?>
                                            </td>
                                            <td class="align-middle">
                                                <span class="font-weight-bold"><?= esc($t['description']) ?></span>
                                                <small class="text-muted d-block">
                                                    Dicatat oleh: <?= esc($t['creator_name']) ?> pada <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                                                </small>
                                                <?php if (!empty($selectedGroupId) && !empty($t['trip_name'])): ?>
                                                    <small class="text-secondary d-block mt-1 font-weight-bold">
                                                        <i class="fas fa-clipboard-list mr-1"></i>Kegiatan: <?= esc($t['trip_name']) ?>
                                                    </small>
                                                <?php endif; ?>
                                                
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
                                            <td class="align-middle" data-search="<?= $t['type'] === 'shared' ? 'Shared' : 'Individual' ?>">
                                                <?php if ($t['type'] === 'shared'): ?>
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-divide mr-1"></i> Shared</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info px-2 py-1"><i class="fas fa-user-tag mr-1"></i> Individual</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle" data-search="<?= esc($t['paid_by_name']) ?>">
                                                <i class="fas fa-user-circle text-muted mr-1"></i><?= esc($t['paid_by_name']) ?>
                                            </td>
                                            <td class="align-middle text-right font-weight-bold text-dark" data-order="<?= $t['amount'] ?>">
                                                Rp <?= number_format($t['amount'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php if ($t['receipt_image']): ?>
                                                    <a href="<?= base_url($t['receipt_image']) ?>" 
                                                       class="btn btn-outline-success btn-sm view-image-popup" title="Lihat Struk">
                                                        <i class="fas fa-receipt"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php
                                                // Cek apakah periode transaksi ini sudah settled
                                                $tPeriodId     = (int)($t['period_id'] ?? 0);
                                                $tPeriodLocked = $tPeriodId > 0 && isset($periodStatusMap[$tPeriodId]) && $periodStatusMap[$tPeriodId] === 'settled';
                                                ?>
                                                <?php if ($tPeriodLocked || !empty($selectedGroupId)): ?>
                                                    <span class="badge badge-secondary px-2 py-1" title="<?= !empty($selectedGroupId) ? 'Buka detail Kegiatan terlebih dahulu untuk mengedit/menghapus transaksi' : 'Periode sudah terkunci — tidak bisa edit/hapus' ?>">
                                                        <i class="fas fa-lock mr-1"></i> Terkunci
                                                    </span>
                                                <?php else: ?>
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
                                                <?php endif; ?>
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
                                <?php if (!$selectedPeriodSettled): ?>
                                    <button class="btn btn-success" data-toggle="modal" data-target="#modalTransaction">
                                        <i class="fas fa-plus mr-1"></i> Catat Transaksi Pertama
                                    </button>
                                <?php else: ?>
                                    <span class="badge badge-secondary px-3 py-2">
                                        <i class="fas fa-lock mr-1"></i> Periode terkunci — tidak bisa tambah transaksi
                                    </span>
                                <?php endif; ?>
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
                                            <?php if (!empty($selectedGroupId) && !empty($t['trip_name'])): ?>
                                                <small class="text-secondary d-block mt-1 font-weight-bold" style="font-size: 0.75rem;">
                                                    <i class="fas fa-clipboard-list mr-1"></i><?= esc($t['trip_name']) ?>
                                                </small>
                                            <?php endif; ?>
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
                                        <?php
                                        // Cek periode locked untuk mobile card
                                        $tPeriodIdM     = (int)($t['period_id'] ?? 0);
                                        $tPeriodLockedM = $tPeriodIdM > 0 && isset($periodStatusMap[$tPeriodIdM]) && $periodStatusMap[$tPeriodIdM] === 'settled';
                                        ?>
                                        <?php if ($t['receipt_image']): ?>
                                            <a href="<?= base_url($t['receipt_image']) ?>" 
                                               class="btn btn-outline-success btn-sm view-image-popup">
                                                <i class="fas fa-receipt mr-1"></i>Struk
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($tPeriodLockedM || !empty($selectedGroupId)): ?>
                                            <span class="badge badge-secondary px-2 py-1" title="<?= !empty($selectedGroupId) ? 'Buka detail Kegiatan terlebih dahulu' : 'Periode sudah terkunci' ?>">
                                                <i class="fas fa-lock mr-1"></i> Terkunci
                                            </span>
                                        <?php else: ?>
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
        <!-- Hidden print table for Excel/PDF exports containing all transaction details -->
        <table id="rekapTransactionsPrintTable" style="display: none;">
            <thead>
                <tr>
                    <th style="background-color: #007bff; color: white; font-weight: bold; text-align: center;">Tanggal</th>
                    <th style="background-color: #007bff; color: white; font-weight: bold; text-align: left;">Deskripsi / Detail Beban</th>
                    <th style="background-color: #007bff; color: white; font-weight: bold; text-align: center;">Periode</th>
                    <th style="background-color: #007bff; color: white; font-weight: bold; text-align: center;">Tipe</th>
                    <th style="background-color: #007bff; color: white; font-weight: bold; text-align: center;">Pembayar (Payer)</th>
                    <th style="background-color: #007bff; color: white; font-weight: bold; text-align: right;">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #777; padding: 20px;">Belum ada transaksi.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td style="vertical-align: top; text-align: center; font-size: 0.85rem; border: 1px solid #ddd; padding: 8px;"><?= date('d M Y', strtotime($t['date'])) ?></td>
                            <td style="vertical-align: top; text-align: left; font-size: 0.85rem; border: 1px solid #ddd; padding: 8px;">
                                <strong><?= esc($t['description']) ?></strong>
                                <div style="font-size: 0.75rem; color: #666; margin-top: 2px;">
                                    Dicatat oleh: <?= esc($t['creator_name']) ?> pada <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                                </div>
                                <?php if ($t['type'] === 'individual' && !empty($t['adjustments'])): ?>
                                    <div style="margin-top: 6px; padding-left: 8px; border-left: 2px solid #17a2b8; font-size: 0.75rem;">
                                        <div style="font-weight: bold; color: #17a2b8; margin-bottom: 2px;">Beban Anggota:</div>
                                        <ul style="margin: 0; padding-left: 15px; list-style-type: disc;">
                                            <?php foreach ($t['adjustments'] as $adj): ?>
                                                <li>
                                                    <?= esc($adj['username']) ?>: <strong>Rp <?= number_format($adj['amount'], 0, ',', '.') ?></strong>
                                                    <?= $adj['note'] ? '<span style="color: #666;">(' . esc($adj['note']) . ')</span>' : '' ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="vertical-align: top; text-align: center; font-size: 0.85rem; border: 1px solid #ddd; padding: 8px;"><?= esc($t['period_label'] ?? 'Umum / Non-Periode') ?></td>
                            <td style="vertical-align: top; text-align: center; font-size: 0.85rem; border: 1px solid #ddd; padding: 8px;"><?= $t['type'] === 'shared' ? 'Shared' : 'Individual' ?></td>
                            <td style="vertical-align: top; text-align: center; font-size: 0.85rem; border: 1px solid #ddd; padding: 8px;"><?= esc($t['paid_by_name']) ?></td>
                            <td style="vertical-align: top; text-align: right; font-weight: bold; font-size: 0.85rem; border: 1px solid #ddd; padding: 8px;">Rp <?= number_format($t['amount'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
                                    <label for="amount">Nominal Uang (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="amount" name="amount" placeholder="Contoh: 150000" min="1" required>
                                    <small id="amount_terbilang" class="form-text text-primary font-italic" style="font-size: 0.8rem; min-height: 1.2rem; display: block; margin-top: 4px;"></small>
                                </div>
                            </div>

                            <!-- Right form parameters -->
                            <div class="col-md-6">
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

                        <!-- Periode Pengeluaran -->
                        <div class="form-group">
                            <label for="period_id">Periode Pengeluaran <span class="text-muted">(Opsional)</span></label>
                            <select class="form-control select2-modal" id="period_id" name="period_id" style="width: 100%;">
                                <option value="" selected>-- Pilih Periode (Bisa diisi nanti) --</option>
                                <?php if (empty($openPeriods)): ?>
                                    <option value="" disabled>-- Semua periode sudah ditutup --</option>
                                <?php else: ?>
                                    <?php foreach ($openPeriods as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= (int)$p['id'] === (int)$selectedPeriodId ? 'selected' : '' ?>>
                                            <?= esc($p['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Hanya periode yang masih <strong>Open</strong> yang dapat dipilih. Membagi tagihan rata (Shared) berdasarkan anggota aktif pada periode terpilih.</small>
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
                                    <label for="edit_amount">Nominal (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_amount" name="amount" min="1" required>
                                    <small id="edit_amount_terbilang" class="form-text text-primary font-italic" style="font-size: 0.8rem; min-height: 1.2rem; display: block; margin-top: 4px;"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
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

                        <!-- Periode Pengeluaran -->
                        <div class="form-group">
                            <label for="edit_period_id">Periode <span class="text-muted">(Opsional)</span></label>
                            <select class="form-control select2-edit-modal" id="edit_period_id" name="period_id" style="width:100%;">
                                <option value="">-- Tanpa Periode --</option>
                                <?php if (empty($openPeriods)): ?>
                                    <option value="" disabled>-- Semua periode sudah ditutup --</option>
                                <?php else: ?>
                                    <?php foreach ($openPeriods as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= esc($p['label']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Hanya periode <strong>Open</strong> yang dapat dipilih.</small>
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
                                    <a href="#" id="edit_receipt_link" class="btn btn-xs btn-outline-info mt-1 d-none view-image-popup">
                                        <i class="fas fa-image mr-1"></i> Lihat Full
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
    // Custom Filter Tree Dropdown logic
    $('#filterDropdownTreeList').on('click', '.node-header', function(e) {
        e.stopPropagation();
        const $header = $(this);
        const $arrow = $header.find('.node-arrow');
        const $list = $header.next('.nested-list');

        $arrow.toggleClass('expanded');
        $list.toggleClass('d-none');
    });

    $('#filterDropdownTreeList').on('click', '.node-header-click', function(e) {
        e.stopPropagation();
        const $header = $(this);
        const $arrow = $header.find('.node-arrow');
        const $list = $header.closest('li').find('> .nested-list');

        $arrow.toggleClass('expanded');
        $list.toggleClass('d-none');
    });

    $('#filterDropdownTreeList').on('click', '.period-node', function(e) {
        e.stopPropagation();
        const $period = $(this);
        const periodId = $period.data('id');
        const tripId = $period.data('trip-id');
        const label = $period.data('label');

        $('#selectedFilterLabel').text(label);
        
        window.location.href = `<?= base_url('backend/transactions') ?>?trip_id=${tripId}&period_id=${periodId}`;
    });

    $('#filterDropdownTreeList').on('click', '.select-trip-node', function(e) {
        const label = $(this).data('label');
        $('#selectedFilterLabel').text(label);
    });

    $('#filterDropdownTreeList').on('click', '.select-group-node', function(e) {
        const label = $(this).data('label');
        $('#selectedFilterLabel').text('Grup: ' + label);
    });

    $('.custom-tree-dropdown .dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });

    const currentGroupId = "<?= $selectedGroupId ?? '' ?>";
    const currentTripId = "<?= $selectedTripId ?? '' ?>";
    const currentPeriodId = "<?= $selectedPeriodId ?? '' ?>";

    if (currentTripId) {
        const $tripNode = $('#filterDropdownTreeList').find(`.trip-node .select-trip-node[data-id="${currentTripId}"]`).closest('.trip-node');
        if ($tripNode.length) {
            $tripNode.parents('.nested-list').removeClass('d-none');
            $tripNode.parents('li').find('> .node-header-wrapper .node-arrow').addClass('expanded');
            
            if (currentPeriodId) {
                const $periodNode = $tripNode.find(`.period-node[data-id="${currentPeriodId}"]`);
                if ($periodNode.length) {
                    $periodNode.addClass('bg-primary text-white font-weight-bold').removeClass('text-dark');
                    $tripNode.find('> .nested-list').removeClass('d-none');
                    $tripNode.find('> .node-header-wrapper .node-arrow').addClass('expanded');
                    
                    const $statusNode = $periodNode.closest('.status-node');
                    if ($statusNode.length) {
                        $statusNode.find('> .nested-list').removeClass('d-none');
                        $statusNode.find('> .node-header .node-arrow').addClass('expanded');
                    }
                }
            } else {
                $tripNode.find('> .node-header-wrapper').addClass('bg-primary text-white font-weight-bold rounded px-1');
                $tripNode.find('> .node-header-wrapper .text-secondary').removeClass('text-secondary').addClass('text-white');
                $tripNode.find('.select-trip-node').removeClass('btn-outline-primary').addClass('btn-outline-light text-white');
            }
        }
    } else if (currentGroupId) {
        const $groupNode = $('#filterDropdownTreeList').find(`.group-node .select-group-node[data-id="${currentGroupId}"]`).closest('.group-node');
        if ($groupNode.length) {
            $groupNode.find('> .node-header-wrapper').addClass('bg-primary text-white font-weight-bold rounded px-1');
            $groupNode.find('> .node-header-wrapper .text-dark').removeClass('text-dark').addClass('text-white');
            $groupNode.find('.select-group-node').removeClass('btn-outline-primary').addClass('btn-outline-light text-white');
        }
    }

    // Custom Filter Tree Search
    $('#filterTreeSearchInput').on('keyup input', function(e) {
        const query = $(this).val().toLowerCase().trim();
        const $tree = $('#filterDropdownTreeList');
        const $noResults = $('#noFilterTreeResults');

        if (query === '') {
            $tree.find('.nested-list').addClass('d-none');
            $tree.find('.node-arrow').removeClass('expanded');
            $tree.find('li').show();
            $noResults.addClass('d-none');

            if (currentTripId) {
                const $tripNode = $tree.find(`.trip-node .select-trip-node[data-id="${currentTripId}"]`).closest('.trip-node');
                if ($tripNode.length) {
                    $tripNode.parents('.nested-list').removeClass('d-none');
                    $tripNode.parents('li').find('> .node-header-wrapper .node-arrow').addClass('expanded');
                    if (currentPeriodId) {
                        $tripNode.find('> .nested-list').removeClass('d-none');
                        $tripNode.find('> .node-header-wrapper .node-arrow').addClass('expanded');
                        
                        const $periodNode = $tripNode.find(`.period-node[data-id="${currentPeriodId}"]`);
                        const $statusNode = $periodNode.closest('.status-node');
                        if ($statusNode.length) {
                            $statusNode.find('> .nested-list').removeClass('d-none');
                            $statusNode.find('> .node-header .node-arrow').addClass('expanded');
                        }
                    }
                }
            } else if (currentGroupId) {
                const $groupNode = $tree.find(`.group-node .select-group-node[data-id="${currentGroupId}"]`).closest('.group-node');
                if ($groupNode.length) {
                    // Group is root level, no parents to expand
                }
            }
            return;
        }

        let anyMatch = false;

        $tree.find('.nested-list').addClass('d-none');
        $tree.find('.node-arrow').removeClass('expanded');
        $tree.find('li').hide();

        $tree.find('.group-node').each(function() {
            const $group = $(this);
            const groupName = $group.data('name') || '';
            let groupMatched = groupName.includes(query);
            let groupHasVisibleChild = false;

            $group.find('.trip-node').each(function() {
                const $trip = $(this);
                const tripName = $trip.data('name') || '';
                let tripMatched = tripName.includes(query);
                let tripHasVisibleChild = false;

                $trip.find('.status-node').each(function() {
                    const $statusNode = $(this);
                    let statusNodeHasVisibleChild = false;

                    $statusNode.find('.period-node').each(function() {
                        const $period = $(this);
                        const periodLabel = $period.data('name') || '';
                        if (periodLabel.includes(query) || tripMatched || groupMatched) {
                            $period.show();
                            statusNodeHasVisibleChild = true;
                            anyMatch = true;
                        }
                    });

                    if (statusNodeHasVisibleChild || tripMatched || groupMatched) {
                        $statusNode.show();
                        $statusNode.find('> .nested-list').removeClass('d-none');
                        $statusNode.find('> .node-header .node-arrow').addClass('expanded');
                        tripHasVisibleChild = true;
                    } else {
                        $statusNode.hide();
                    }
                });

                if (tripMatched || tripHasVisibleChild) {
                    $trip.show();
                    $trip.find('> .nested-list').removeClass('d-none');
                    $trip.find('> .node-header-wrapper .node-arrow').addClass('expanded');
                    groupHasVisibleChild = true;
                    anyMatch = true;
                }
            });

            if (groupMatched || groupHasVisibleChild) {
                $group.show();
                $group.find('> .nested-list').removeClass('d-none');
                $group.find('> .node-header .node-arrow').addClass('expanded');
                anyMatch = true;
            }
        });

        if (anyMatch) {
            $noResults.addClass('d-none');
        } else {
            $noResults.removeClass('d-none');
        }
    });

    // Inisialisasi Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Inisialisasi Select2 di Modal (butuh container parent agar scrollable/render pas)
    $('.select2-modal').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalTransaction')
    });

    // Save and restore last paid_by selection in Add Transaction form using localStorage
    const currentUserId = <?= function_exists('user_id') ? (user_id() ?? 'null') : 'null' ?>;
    const lastPaidByKey = 'txn_last_paid_by_' + currentUserId;

    if (currentUserId) {
        const savedPaidBy = localStorage.getItem(lastPaidByKey);
        if (savedPaidBy && $('#paid_by option[value="' + savedPaidBy + '"]').length > 0) {
            $('#paid_by').val(savedPaidBy).trigger('change');
        }
    }

    $('#paid_by').on('change', function() {
        const val = $(this).val();
        if (val && currentUserId) {
            localStorage.setItem(lastPaidByKey, val);
        }
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
        const printTable = document.getElementById('rekapTransactionsPrintTable');
        if (!table) return;

        let html = table.outerHTML;
        let printTableHtml = '';
        if (printTable) {
            // Bersihkan properti inline display:none agar tabel detail tampil di excel
            printTableHtml = `
                <br><br>
                <h3 style="text-align: center; margin-bottom: 20px;">Rincian Transaksi Detail - Periode: <?= esc($calculationResult['period']['label'] ?? '') ?></h3>
                ` + printTable.outerHTML.replace('display: none;', 'display: table; width: 100%;');
        }

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
                ${printTableHtml}
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
                $('#edit_amount').val(t.amount).trigger('input');
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

    $('.btn-print-rekap').on('click', function() {
        const periodId = '<?= $selectedPeriodId ?? "" ?>';
        if (!periodId) {
            Swal.fire('Info', 'Silakan pilih periode pengeluaran terlebih dahulu sebelum mengunduh PDF.', 'info');
            return;
        }

        // Langsung arahkan ke URL ekspor PDF Dompdf di backend
        window.location.href = '<?= base_url() ?>/backend/transactions/pdf?period_id=' + periodId;
    });

    // Inisialisasi DataTables untuk tabel transaksi desktop jika ada data transaksi
    if ($.fn.DataTable && $('#txnTable tbody tr.no-data').length === 0 && $('#txnTable tbody tr').length > 0) {
        if ($.fn.DataTable.isDataTable('#txnTable')) {
            $('#txnTable').DataTable().destroy();
        }

        $('#txnTable').DataTable({
            order: [[0, 'desc']], 
            columnDefs: [
                { targets: [6, 7], orderable: false, searchable: false } 
            ],
            dom: "<'row p-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row p-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                search: "Cari:",
                searchPlaceholder: "Cari transaksi..."
            }
        });
    }

    // Flash Alert success handler using SweetAlert2
    <?php if (session()->getFlashdata('success')) : ?>
        const successMsg = <?= json_encode(session()->getFlashdata('success')) ?>;
        if (successMsg === 'Transaksi berhasil dicatat.') {
            Swal.fire({
                title: 'Berhasil!',
                text: successMsg,
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#007bff',
                confirmButtonText: 'Tutup',
                cancelButtonText: '<i class="fas fa-plus mr-1"></i> Catat Transaksi Lain',
                timer: 5000,
                timerProgressBar: true
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    $('#modalTransaction').modal('show');
                }
            });
        } else {
            Swal.fire({
                title: 'Berhasil!',
                text: successMsg,
                icon: 'success',
                confirmButtonColor: '#28a745',
                timer: 3000,
                timerProgressBar: true
            });
        }
    <?php endif; ?>
});
</script>

<style>
/* =============================================
   Summary Stat Cards — Mobile-friendly, Equal Height
   ============================================= */
.summary-stat-card {
    border-radius: 14px;
    padding: 14px 12px;
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: space-between;
    min-height: 100px;
    height: 100%;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.summary-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}
.summary-stat-card::after {
    content: '';
    position: absolute;
    right: -18px;
    bottom: -18px;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}
.summary-stat-icon {
    font-size: 1.4rem;
    opacity: 0.9;
    margin-bottom: 6px;
    line-height: 1;
}
.summary-stat-label {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    opacity: 0.88;
    line-height: 1.2;
    margin-bottom: 4px;
}
.summary-stat-value {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.25;
    word-break: break-word;
}
@media (min-width: 768px) {
    .summary-stat-value {
        font-size: 1.15rem;
    }
}
@media (max-width: 400px) {
    .summary-stat-card {
        min-height: 90px;
        padding: 12px 10px;
    }
    .summary-stat-icon { font-size: 1.1rem; }
    .summary-stat-value { font-size: 0.9rem; }
}
</style>

<style>
/* Custom Tree Dropdown style rules */
.custom-tree-dropdown .hover-item:hover {
    background-color: #f1f5f9;
}
.dark-mode .custom-tree-dropdown .hover-item:hover {
    background-color: #1e293b;
}
.node-arrow {
    transition: transform 0.2s ease;
    font-size: 0.75rem;
    width: 10px;
}
.node-arrow.expanded {
    transform: rotate(90deg);
}

/* Filter sidebar interactive styles */
.trip-select-btn.active {
    background-color: #e8f4fd !important;
    color: #1a56db !important;
    border-left: 3px solid #1a56db !important;
    font-weight: 600;
}
.trip-select-btn:last-child {
    border-bottom: none !important;
}
.period-nav-item {
    transition: background-color 0.1s;
}
.period-nav-item.active {
    background-color: #1a56db !important;
    color: #fff !important;
    border-color: #1a56db !important;
}
.period-nav-item.active .badge {
    background-color: rgba(255,255,255,0.3) !important;
    color: #fff !important;
}
#filterPanel {
    overflow: visible !important;
}
#filterPanel .card {
    border-radius: 10px;
    overflow: visible !important;
}
#periodStep {
    animation: fadeSlideIn 0.2s ease;
}
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.mobile-filter-toggle {
    border-radius: 8px;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 6px;
}
.collapse-chevron {
    transition: transform 0.2s ease;
}
button[aria-expanded="true"] .collapse-chevron {
    transform: rotate(180deg);
}
</style>

<script>
(function () {
    const currentUserId = <?= function_exists('user_id') ? (user_id() ?? 'null') : 'null' ?>;
    const lastTripKey = 'txn_last_trip_id_' + currentUserId;
    const lastPeriodKey = 'txn_last_period_id_' + currentUserId;
    const lastGroupKey = 'txn_last_group_id_' + currentUserId;
    const lastFilterTypeKey = 'txn_last_filter_type_' + currentUserId;

    // Data semua periode per trip dari server (PHP)
    const allPeriods = <?= $allPeriodsJson ?? '{}' ?>;
    const baseUrl    = '<?= base_url('backend/transactions') ?>';
    const activeTripId   = '<?= $selectedTripId ?? '' ?>';
    const activePeriodId = '<?= $selectedPeriodId ?? '' ?>';
    const activeGroupId  = '<?= $selectedGroupId ?? '' ?>';
    const waSummaryText  = <?= json_encode($waSummaryText ?? '') ?>;

    // Cek parameter dari URL
    const urlParams = new URLSearchParams(window.location.search);
    const hasTrip = urlParams.has('trip_id');
    const hasPeriod = urlParams.has('period_id');
    const hasGroup = urlParams.has('group_id');

    if (currentUserId) {
        if (hasGroup) {
            // Simpan pilihan filter grup ke localStorage saat diakses dengan parameter group_id
            localStorage.setItem(lastGroupKey, urlParams.get('group_id') || '');
            localStorage.removeItem(lastTripKey);
            localStorage.removeItem(lastPeriodKey);
            localStorage.setItem(lastFilterTypeKey, 'group');
        } else if (hasTrip) {
            // Simpan pilihan filter trip ke localStorage saat diakses dengan parameter trip_id
            localStorage.setItem(lastTripKey, urlParams.get('trip_id') || '');
            localStorage.setItem(lastPeriodKey, urlParams.get('period_id') || '');
            localStorage.removeItem(lastGroupKey);
            localStorage.setItem(lastFilterTypeKey, 'trip');
        } else {
            // URL tidak memiliki parameter (akses langsung)
            const lastFilterType = localStorage.getItem(lastFilterTypeKey);
            if (lastFilterType === 'group') {
                const savedGroup = localStorage.getItem(lastGroupKey);
                if (savedGroup) {
                    window.location.href = `${baseUrl}?group_id=${savedGroup}`;
                    return;
                }
            } else {
                const savedTrip = localStorage.getItem(lastTripKey);
                const savedPeriod = localStorage.getItem(lastPeriodKey);
                if (savedTrip) {
                    let redirectUrl = `${baseUrl}?trip_id=${savedTrip}`;
                    if (savedPeriod) {
                        redirectUrl += `&period_id=${savedPeriod}`;
                    }
                    window.location.href = redirectUrl;
                    return;
                }
            }

            // Jika belum ada di localStorage, simpan default group atau trip yang dimuat PHP ke localStorage
            if (activeGroupId) {
                localStorage.setItem(lastGroupKey, activeGroupId);
                localStorage.setItem(lastFilterTypeKey, 'group');
            } else if (activeTripId) {
                localStorage.setItem(lastTripKey, activeTripId);
                localStorage.setItem(lastPeriodKey, activePeriodId);
                localStorage.setItem(lastFilterTypeKey, 'trip');
            }
        }
    }

    // Reset Filter click handler to clear localStorage
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-reset-filter');
        if (btn) {
            localStorage.removeItem(lastTripKey);
            localStorage.removeItem(lastPeriodKey);
            localStorage.removeItem(lastGroupKey);
            localStorage.removeItem(lastFilterTypeKey);
        }
    });

    // Tombol close filter (mobile)
    const btnClose = document.getElementById('btnCollapseFilter');
    if (btnClose) {
        btnClose.addEventListener('click', function () {
            const panel = document.getElementById('filterPanel');
            if (panel) $(panel).collapse('hide');
        });
    }

    // WhatsApp Share Button
    const btnShareWa = document.querySelector('.btn-share-wa');
    if (btnShareWa) {
        btnShareWa.addEventListener('click', function() {
            if (!waSummaryText) return;
            const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(waSummaryText);
            window.open(waUrl, '_blank');
        });
    }
    // Realtime Terbilang Helper & Event Handlers
    function terbilang(angka) {
        angka = Math.floor(Math.abs(angka));
        if (isNaN(angka) || angka === 0) return '';
        
        const susunan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        let hasil = '';
        
        if (angka < 12) {
            hasil = ' ' + susunan[angka];
        } else if (angka < 20) {
            hasil = terbilang(angka - 10) + ' belas ';
        } else if (angka < 100) {
            hasil = terbilang(Math.floor(angka / 10)) + ' puluh ' + terbilang(angka % 10);
        } else if (angka < 200) {
            hasil = ' seratus ' + terbilang(angka - 100);
        } else if (angka < 1000) {
            hasil = terbilang(Math.floor(angka / 100)) + ' ratus ' + terbilang(angka % 100);
        } else if (angka < 2000) {
            hasil = ' seribu ' + terbilang(angka - 1000);
        } else if (angka < 1000000) {
            hasil = terbilang(Math.floor(angka / 1000)) + ' ribu ' + terbilang(angka % 1000);
        } else if (angka < 1000000000) {
            hasil = terbilang(Math.floor(angka / 1000000)) + ' juta ' + terbilang(angka % 1000000);
        } else if (angka < 1000000000000) {
            hasil = terbilang(Math.floor(angka / 1000000000)) + ' milyar ' + terbilang(angka % 1000000000);
        } else if (angka < 1000000000000000) {
            hasil = terbilang(Math.floor(angka / 1000000000000)) + ' triliun ' + terbilang(angka % 1000000000000);
        }
        
        return hasil.replace(/\s+/g, ' ').trim();
    }

    function bindTerbilangInput(inputId, labelId) {
        const inputEl = document.getElementById(inputId);
        const labelEl = document.getElementById(labelId);
        if (inputEl && labelEl) {
            const handler = function() {
                const val = parseInt(inputEl.value, 10);
                if (val && val > 0) {
                    const text = terbilang(val);
                    labelEl.textContent = text ? 'Terbilang: ' + text + ' rupiah' : '';
                } else {
                    labelEl.textContent = '';
                }
            };
            inputEl.addEventListener('input', handler);
            inputEl.addEventListener('change', handler);
        }
    }

    bindTerbilangInput('amount', 'amount_terbilang');
    bindTerbilangInput('edit_amount', 'edit_amount_terbilang');
})();
</script>
<?= $this->endSection() ?>
