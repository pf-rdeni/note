<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<!-- Greeting Banner -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden; position: relative;">
            <!-- Background Accent Shapes -->
            <div style="position: absolute; right: -50px; bottom: -50px; width: 250px; height: 250px; background: rgba(255,255,255,0.08); border-radius: 50%; pointer-events: none;"></div>
            <div style="position: absolute; right: 50px; top: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none;"></div>
            
            <div class="card-body p-4 p-md-5 d-flex align-items-center">
                <div style="flex: 1;">
                    <span class="badge badge-light px-3 py-2 text-primary font-weight-bold mb-3 shadow-xs">
                        <i class="fas fa-hand-sparkles mr-1 animate-wave"></i> Welcome Back
                    </span>
                    <h2 class="font-weight-bold mb-2">Halo, <?= esc($user->username) ?>!</h2>
                    <p class="mb-0 text-white-50" style="font-size: 1.1rem; max-width: 600px;">
                        Kelola tagihan keluarga, catat pengeluaran kegiatan, dan selesaikan saldo dengan mudah di sistem **Split Bill Keluarga**.
                    </p>
                </div>
                <div class="d-none d-lg-block ml-4 text-center">
                    <i class="fas fa-wallet fa-5x text-white-50 shadow-sm" style="opacity: 0.65; transform: rotate(-10deg);"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Cards Row -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-xs hover-card">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted">Kelompok Aktif</span>
                <span class="info-box-number text-lg text-dark"><?= esc($numGroups) ?> Kelompok</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-xs hover-card">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-clipboard-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted">Kegiatan</span>
                <span class="info-box-number text-lg text-dark"><?= esc($numTrips) ?> Kegiatan</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-xs hover-card">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-wallet text-white"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted text-warning">Total Pengeluaran</span>
                <span class="info-box-number text-lg text-dark">Rp <?= number_format($totalExpenses, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-xs hover-card">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-coins text-white"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted text-danger">Pengeluaran Saya</span>
                <span class="info-box-number text-lg text-dark">Rp <?= number_format($myExpenses, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Charts Card -->
    <div class="col-lg-8 mb-4">
        <div class="card card-primary card-outline card-outline-tabs shadow-sm h-100 mb-0">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs nav-justified font-weight-bold" id="dashboard-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="total-period-tab" data-toggle="pill" href="#total-period-content" role="tab" aria-controls="total-period-content" aria-selected="true">
                            <i class="fas fa-chart-bar mr-1 text-warning"></i> Total Pengeluaran (Periode)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="avg-group-tab" data-toggle="pill" href="#avg-group-content" role="tab" aria-controls="avg-group-content" aria-selected="false">
                            <i class="fas fa-users mr-1 text-purple"></i> Beban Saya (Grup)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="avg-trip-tab" data-toggle="pill" href="#avg-trip-content" role="tab" aria-controls="avg-trip-content" aria-selected="false">
                            <i class="fas fa-suitcase-rolling mr-1 text-success"></i> Beban Saya (Kegiatan)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="avg-period-tab" data-toggle="pill" href="#avg-period-content" role="tab" aria-controls="avg-period-content" aria-selected="false">
                            <i class="far fa-calendar-alt mr-1 text-primary"></i> Beban Saya (Periode)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="trend-tab" data-toggle="pill" href="#trend-content" role="tab" aria-controls="trend-content" aria-selected="false">
                            <i class="fas fa-chart-line mr-1 text-danger"></i> Tren Item (Periode)
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="dashboard-tabsContent">
                    <!-- Tab 1: Total Pengeluaran per Periode -->
                    <div class="tab-pane fade show active" id="total-period-content" role="tabpanel" aria-labelledby="total-period-tab">
                        <div class="mb-3 text-muted" style="font-size: 0.88rem; line-height: 1.4;">
                            <i class="fas fa-info-circle text-warning mr-1"></i> 
                            Menampilkan akumulasi total belanja seluruh keluarga di setiap periode.
                        </div>
                        <?php if (empty($spendingChartData)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="far fa-chart-bar fa-3x mb-3 text-secondary"></i>
                                <p class="mb-0">Belum ada data transaksi periodik.</p>
                            </div>
                        <?php else: ?>
                            <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                                <canvas id="spendingChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Tab 2: Beban Saya per Grup -->
                    <div class="tab-pane fade" id="avg-group-content" role="tabpanel" aria-labelledby="avg-group-tab">
                        <div class="mb-3 text-muted" style="font-size: 0.88rem; line-height: 1.4;">
                            <i class="fas fa-info-circle text-purple mr-1"></i> 
                            Menampilkan total beban biaya pengeluaran Anda untuk masing-masing kelompok/grup.
                        </div>
                        <?php if (empty($avgGroupChartData)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="far fa-chart-bar fa-3x mb-3 text-secondary"></i>
                                <p class="mb-0">Belum ada data transaksi grup.</p>
                            </div>
                        <?php else: ?>
                            <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                                <canvas id="avgGroupChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Tab 3: Beban Saya per Kegiatan -->
                    <div class="tab-pane fade" id="avg-trip-content" role="tabpanel" aria-labelledby="avg-trip-tab">
                        <div class="mb-3 text-muted" style="font-size: 0.88rem; line-height: 1.4;">
                            <i class="fas fa-info-circle text-success mr-1"></i> 
                            Menampilkan total beban biaya pengeluaran Anda pada setiap kegiatan/event yang diikuti.
                        </div>
                        <?php if (empty($avgTripChartData)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="far fa-chart-bar fa-3x mb-3 text-secondary"></i>
                                <p class="mb-0">Belum ada data transaksi kegiatan.</p>
                            </div>
                        <?php else: ?>
                            <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                                <canvas id="avgTripChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Tab 4: Beban Saya per Periode -->
                    <div class="tab-pane fade" id="avg-period-content" role="tabpanel" aria-labelledby="avg-period-tab">
                        <div class="mb-3 text-muted" style="font-size: 0.88rem; line-height: 1.4;">
                            <i class="fas fa-info-circle text-primary mr-1"></i> 
                            Menampilkan total beban biaya pengeluaran Anda di setiap periode.
                        </div>
                        <?php if (empty($avgPeriodChartData)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="far fa-chart-bar fa-3x mb-3 text-secondary"></i>
                                <p class="mb-0">Belum ada data transaksi periodik.</p>
                            </div>
                        <?php else: ?>
                            <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                                <canvas id="avgPeriodChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Tab 5: Tren Item per Periode -->
                    <div class="tab-pane fade" id="trend-content" role="tabpanel" aria-labelledby="trend-tab">
                        <div class="mb-3 text-muted" style="font-size: 0.88rem; line-height: 1.4;">
                            <i class="fas fa-info-circle text-danger mr-1"></i> 
                            Menampilkan perbandingan nominal belanja per item pada periode terpilih (dari terbesar).
                        </div>
                        <div class="d-flex align-items-center mb-3 flex-wrap" style="gap: 12px;">
                            <label class="mb-0 font-weight-bold text-secondary" style="font-size: 0.95rem;">Pilih Periode:</label>
                            <div class="dropdown custom-tree-dropdown" style="min-width: 300px; position: relative;">
                                <?php 
                                $initialPeriodId = !empty($trendPeriods) ? array_key_first($trendPeriods) : '';
                                $initialPeriodLabel = !empty($trendPeriods) ? reset($trendPeriods) : 'Pilih Periode...';
                                ?>
                                <input type="hidden" id="trend-period-select" value="<?= $initialPeriodId ?>">
                                <button class="btn btn-outline-secondary dropdown-toggle text-left w-100 font-weight-bold d-flex justify-content-between align-items-center shadow-xs" 
                                        type="button" 
                                        id="treeDropdownBtn" 
                                        data-toggle="dropdown" 
                                        aria-haspopup="true" 
                                        aria-expanded="false" 
                                        style="border-color: #ced4da; border-radius: 8px; height: calc(2.25rem + 10px); background: #fff; color: #495057; font-size: 0.9rem;">
                                    <span id="selectedPeriodLabel"><?= esc($initialPeriodLabel) ?></span>
                                </button>
                                <div class="dropdown-menu p-3 shadow-lg border-0" 
                                     aria-labelledby="treeDropdownBtn" 
                                     style="max-height: 400px; overflow-y: auto; border-radius: 12px; min-width: 320px; width: 100%;">
                                    
                                    <!-- Search Input -->
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-right-0" style="border-radius: 8px 0 0 8px;">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                        </div>
                                        <input type="text" id="treeSearchInput" class="form-control border-left-0" placeholder="Cari grup, kegiatan, atau periode..." style="border-radius: 0 8px 8px 0;">
                                    </div>

                                    <!-- Tree List -->
                                    <ul class="list-unstyled mb-0" id="dropdownTreeList">
                                        <?php foreach ($trendHierarchy as $gid => $gInfo): ?>
                                            <li class="group-node mb-2" data-name="<?= esc(strtolower($gInfo['name'])) ?>">
                                                <div class="d-flex align-items-center py-1 font-weight-bold text-dark node-header" 
                                                     style="cursor: pointer; gap: 8px; font-size: 0.95rem;">
                                                    <i class="fas fa-chevron-right text-muted node-arrow"></i>
                                                    <i class="fas fa-users text-primary"></i>
                                                    <span><?= esc($gInfo['name']) ?></span>
                                                </div>
                                                <ul class="list-unstyled pl-4 d-none nested-list mt-1">
                                                    <?php foreach ($gInfo['trips'] as $tid => $tInfo): ?>
                                                        <li class="trip-node mb-1" data-name="<?= esc(strtolower($tInfo['name'])) ?>">
                                                            <div class="d-flex align-items-center py-1 font-weight-bold text-secondary node-header" 
                                                                 style="cursor: pointer; gap: 8px; font-size: 0.88rem;">
                                                                <i class="fas fa-chevron-right text-muted node-arrow"></i>
                                                                <i class="fas fa-suitcase-rolling text-success"></i>
                                                                <span><?= esc($tInfo['name']) ?></span>
                                                            </div>
                                                            <ul class="list-unstyled pl-4 d-none nested-list mt-1">
                                                                <?php foreach ($tInfo['periods'] as $pid => $pLabel): ?>
                                                                    <li class="period-node py-1 px-2 rounded hover-item" 
                                                                        data-id="<?= $pid ?>" 
                                                                        data-label="<?= esc($pLabel) ?>"
                                                                        data-name="<?= esc(strtolower($pLabel)) ?>"
                                                                        style="cursor: pointer; font-size: 0.85rem; transition: background-color 0.15s;">
                                                                        <i class="far fa-calendar-alt text-info mr-2"></i>
                                                                        <span class="text-dark font-weight-bold"><?= esc($pLabel) ?></span>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div id="noTreeResults" class="text-center py-3 text-muted d-none" style="font-size: 0.85rem;">
                                        <i class="fas fa-search mb-2 fa-lg d-block"></i>
                                        Tidak ada kecocokan
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (empty($trendPeriods)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="far fa-chart-bar fa-3x mb-3 text-secondary"></i>
                                <p class="mb-0">Belum ada data transaksi periodik.</p>
                            </div>
                        <?php else: ?>
                            <div class="chart-container" style="position: relative; height: 230px; width: 100%;">
                                <canvas id="trendItemChart"></canvas>
                            </div>
                            <div id="trend-legend" class="mt-3 text-center d-flex flex-wrap justify-content-center"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payer Contribution Chart -->
    <div class="col-lg-4 mb-4">
        <div class="card card-info card-outline shadow-sm h-100 d-flex flex-column justify-content-between mb-0">
            <div class="card-header border-0 py-3">
                <h3 class="card-title font-weight-bold text-info">
                    <i class="fas fa-chart-pie mr-1"></i> Kontribusi Pembayaran
                </h3>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <?php if (empty($memberSpendingChartData)): ?>
                    <div class="text-center py-5 text-muted w-100">
                        <i class="fas fa-chart-pie fa-3x mb-3 text-secondary"></i>
                        <p class="mb-0">Belum ada data transaksi anggota.</p>
                    </div>
                <?php else: ?>
                    <div class="mb-3 text-muted w-100 text-center" style="font-size: 0.85rem; line-height: 1.4;">
                        <i class="fas fa-info-circle text-info mr-1"></i> 
                        Menampilkan total uang yang telah ditalangi oleh masing-masing anggota.
                    </div>
                    <div class="chart-container" style="position: relative; height: 230px; width: 100%;">
                        <canvas id="memberContributionChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($installmentStats['has_installments']) : ?>
    <!-- Overview Cicilan & Tagihan Dashboard Row -->
    <div class="row">
        <!-- 1. Card Rincian Status (Left) -->
        <div class="col-lg-8 mb-4">
            <div class="card card-primary card-outline card-outline-tabs shadow-sm h-100 mb-0" style="border-radius: 12px; overflow: visible;">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs nav-justified font-weight-bold" id="installment-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="inst-overview-tab" data-toggle="pill" href="#inst-overview-content" role="tab" aria-controls="inst-overview-content" aria-selected="true">
                                <i class="fas fa-hand-holding-usd mr-1 text-primary"></i> Ringkasan Saldo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="inst-borrower-tab" data-toggle="pill" href="#inst-borrower-content" role="tab" aria-controls="inst-borrower-content" aria-selected="false">
                                <i class="fas fa-file-invoice-dollar mr-1 text-danger"></i> Tren Pinjaman Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="inst-lender-tab" data-toggle="pill" href="#inst-lender-content" role="tab" aria-controls="inst-lender-content" aria-selected="false">
                                <i class="fas fa-coins mr-1 text-success"></i> Tren Piutang Saya
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="installment-tabsContent">
                        <!-- Tab 1: Ringkasan Saldo -->
                        <div class="tab-pane fade show active" id="inst-overview-content" role="tabpanel" aria-labelledby="inst-overview-tab">
                            <div class="row">
                                <!-- Stat 1: Total Sisa Pinjaman -->
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 bg-light rounded" style="border-left: 4px solid #007bff; border-radius: 8px;">
                                        <span class="text-uppercase text-muted font-weight-bold small d-block mb-1" style="letter-spacing: 0.5px; font-size: 0.72rem;">Sisa Pinjaman (Hutang Saya)</span>
                                        <h4 class="mb-0 font-weight-bold text-primary" style="font-size: 1.35rem;">
                                            Rp <?= number_format($installmentStats['sisa_pinjaman'], 0, ',', '.') ?>
                                        </h4>
                                        <div class="mt-2 text-muted small">
                                            Tagihan bulan ini: <strong class="text-danger">Rp <?= number_format($installmentStats['tagihan_bulan_ini'], 0, ',', '.') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Stat 2: Total Sisa Piutang -->
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 bg-light rounded" style="border-left: 4px solid #28a745; border-radius: 8px;">
                                        <span class="text-uppercase text-muted font-weight-bold small d-block mb-1" style="letter-spacing: 0.5px; font-size: 0.72rem;">Sisa Piutang (Dana Masuk)</span>
                                        <h4 class="mb-0 font-weight-bold text-success" style="font-size: 1.35rem;">
                                            Rp <?= number_format($installmentStats['sisa_piutang'], 0, ',', '.') ?>
                                        </h4>
                                        <div class="mt-2 text-muted small">
                                            Piutang bulan ini: <strong class="text-success">Rp <?= number_format($installmentStats['piutang_bulan_ini'], 0, ',', '.') ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                                <span class="small text-muted">
                                    <i class="fas fa-info-circle mr-1 text-info"></i>
                                    Lihat tab di atas untuk melihat detail per item bulanan.
                                </span>
                                <a href="<?= base_url('backend/installments') ?>" class="btn btn-xs btn-outline-primary font-weight-bold shadow-sm" style="border-radius: 6px; padding: 4px 8px;">
                                    Kelola Rincian Cicilan <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Tab 2: Tren Pinjaman Saya -->
                        <div class="tab-pane fade" id="inst-borrower-content" role="tabpanel" aria-labelledby="inst-borrower-tab">
                            <div class="mb-2 text-muted small">
                                <i class="fas fa-info-circle text-primary mr-1"></i>
                                Menampilkan proyeksi tagihan bulanan Anda berdasarkan rincian item barang.
                            </div>
                            <?php if (empty($installmentStats['borrower_trends'])): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-file-invoice-dollar fa-2x mb-2 text-secondary"></i>
                                    <p class="mb-0 small">Anda tidak memiliki tagihan aktif 6 bulan ke depan.</p>
                                </div>
                            <?php else: ?>
                                <div class="chart-container" style="position: relative; height: 180px; width: 100%;">
                                    <canvas id="borrowerTrendsChart"></canvas>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab 3: Tren Piutang Saya -->
                        <div class="tab-pane fade" id="inst-lender-content" role="tabpanel" aria-labelledby="inst-lender-tab">
                            <div class="mb-2 text-muted small">
                                <i class="fas fa-info-circle text-success mr-1"></i>
                                Menampilkan proyeksi piutang dana masuk dari anggota lain berdasarkan rincian item barang.
                            </div>
                            <?php if (empty($installmentStats['lender_trends'])): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-coins fa-2x mb-2 text-secondary"></i>
                                    <p class="mb-0 small">Anda tidak memiliki piutang aktif 6 bulan ke depan.</p>
                                </div>
                            <?php else: ?>
                                <div class="chart-container" style="position: relative; height: 180px; width: 100%;">
                                    <canvas id="lenderTrendsChart"></canvas>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Card Grafik Proyeksi (Right) -->
        <div class="col-lg-4 mb-4">
            <div class="card card-outline card-success shadow-sm h-100 mb-0" style="border-radius: 12px;">
                <div class="card-header py-3">
                    <h3 class="card-title font-weight-bold text-success mb-0" style="font-size: 1rem;">
                        <i class="fas fa-chart-line mr-2"></i>Proyeksi Arus Kas (6 Bln)
                    </h3>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div class="chart-container" style="position: relative; height: 180px; width: 100%;">
                        <canvas id="installmentProjectionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Recent Transactions (Full width table) -->
    <div class="col-lg-12">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header border-0 py-3">
                <h3 class="card-title font-weight-bold text-secondary mb-0">
                    <i class="fas fa-receipt mr-1"></i> 5 Transaksi Terbaru
                </h3>
                <div class="card-tools ml-auto">
                    <a href="<?= base_url('backend/transactions') ?>" class="btn btn-xs btn-outline-secondary font-weight-bold shadow-sm" style="border-radius: 6px; padding: 4px 8px;">
                        Ke Transaksi <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentTransactions)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-receipt fa-2x mb-2 d-block text-secondary"></i>
                        Belum ada transaksi dicatat.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Deskripsi</th>
                                    <th>Kegiatan</th>
                                    <th>Tanggal</th>
                                    <th>Pembayar (Payer)</th>
                                    <th>Tipe</th>
                                    <th class="text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $t): ?>
                                    <tr>
                                        <td class="align-middle font-weight-bold text-dark">
                                            <?= esc($t['description']) ?>
                                        </td>
                                        <td class="align-middle text-sm text-secondary">
                                            <i class="fas fa-clipboard-list mr-1"></i><?= esc($t['trip_name']) ?>
                                        </td>
                                        <td class="align-middle text-sm">
                                            <?= date('d M Y', strtotime($t['date'])) ?>
                                        </td>
                                        <td class="align-middle text-sm">
                                            <i class="far fa-user mr-1 text-xs"></i><strong><?= esc($t['paid_by_name']) ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <?php if ($t['type'] === 'shared'): ?>
                                                <span class="badge badge-success text-xs"><i class="fas fa-divide mr-1"></i> Shared</span>
                                            <?php else: ?>
                                                <span class="badge badge-info text-xs"><i class="fas fa-user-tag mr-1"></i> Individual</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-right font-weight-bold text-dark">
                                            Rp <?= number_format($t['amount'], 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center bg-white border-0 py-3">
                <a href="<?= base_url('backend/transactions') ?>" class="btn btn-sm btn-outline-secondary font-weight-bold">
                    Lihat Semua Transaksi <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Include Chart.js CDN explicitly to avoid missing dependency -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Helper to format currency
    const formatCurrency = (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val);

    const currentUserId = <?= user_id() ?>;
    const lastTabKey = 'dashboard_last_tab_' + currentUserId;
    const lastTrendPeriodKey = 'dashboard_last_trend_period_' + currentUserId;

    // Custom Tree Dropdown logic
    $('#dropdownTreeList').on('click', '.node-header', function(e) {
        e.stopPropagation();
        const $header = $(this);
        const $arrow = $header.find('.node-arrow');
        const $list = $header.next('.nested-list');

        $arrow.toggleClass('expanded');
        $list.toggleClass('d-none');
    });

    $('#dropdownTreeList').on('click', '.period-node', function(e) {
        e.stopPropagation();
        const $period = $(this);
        const periodId = $period.data('id');
        const label = $period.data('label');

        $('#trend-period-select').val(periodId).trigger('change');
        $('#selectedPeriodLabel').text(label);

        // Highlight active node
        $('#dropdownTreeList .period-node').removeClass('bg-primary text-white font-weight-bold').addClass('text-dark');
        $period.addClass('bg-primary text-white font-weight-bold').removeClass('text-dark');

        // Expand path to the selected node
        $period.parents('.nested-list').removeClass('d-none');
        $period.parents('li').find('> .node-header .node-arrow').addClass('expanded');

        // Close dropdown if open
        const $menu = $('.custom-tree-dropdown .dropdown-menu');
        if ($menu.hasClass('show')) {
            $('#treeDropdownBtn').dropdown('toggle');
        }
    });

    $('.custom-tree-dropdown .dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });

    // Custom Tree Search
    $('#treeSearchInput').on('keyup input', function(e) {
        const query = $(this).val().toLowerCase().trim();
        const $tree = $('#dropdownTreeList');
        const $noResults = $('#noTreeResults');

        if (query === '') {
            $tree.find('.nested-list').addClass('d-none');
            $tree.find('.node-arrow').removeClass('expanded');
            $tree.find('li').show();
            $noResults.addClass('d-none');

            // Keep the selected/active node path open
            const currentVal = $('#trend-period-select').val();
            if (currentVal) {
                const $activeNode = $tree.find(`.period-node[data-id="${currentVal}"]`);
                if ($activeNode.length) {
                    $activeNode.parents('.nested-list').removeClass('d-none');
                    $activeNode.parents('li').find('> .node-header .node-arrow').addClass('expanded');
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

                $trip.find('.period-node').each(function() {
                    const $period = $(this);
                    const periodLabel = $period.data('name') || '';
                    if (periodLabel.includes(query) || tripMatched || groupMatched) {
                        $period.show();
                        tripHasVisibleChild = true;
                        anyMatch = true;
                    }
                });

                if (tripMatched || tripHasVisibleChild) {
                    $trip.show();
                    $trip.find('> .nested-list').removeClass('d-none');
                    $trip.find('> .node-header .node-arrow').addClass('expanded');
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

    // Bootstrap Tab Change handler to resize Chart.js properly
    $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        const targetId = $(e.target).attr('href');
        localStorage.setItem(lastTabKey, targetId); // Save last active tab

        if (targetId === '#trend-content') {
            const currentPeriod = $('#trend-period-select').val();
            if (currentPeriod) {
                renderTrendChart(currentPeriod);
            }
        } else {
            const canvas = $(targetId).find('canvas')[0];
            if (canvas) {
                const chart = Chart.getChart(canvas);
                if (chart) {
                    chart.resize();
                    chart.update();
                }
            }
        }
    });

    // =============================================
    // 1. CHART RATA-RATA PER ANGGOTA (PER PERIODE)
    // =============================================
    <?php if (!empty($avgPeriodChartData)): ?>
    (function() {
        const labels = <?= json_encode(array_column($avgPeriodChartData, 'label')) ?>;
        const dataValues = <?= json_encode(array_column($avgPeriodChartData, 'amount')) ?>;
        const ctx = document.getElementById('avgPeriodChart').getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, '#4e73df');
        gradient.addColorStop(1, '#224abe');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Beban Saya',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: '#4e73df',
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Beban Saya: ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (val) => formatCurrency(val) },
                        grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    })();
    <?php endif; ?>

    // =============================================
    // 2. CHART RATA-RATA PER ANGGOTA (PER KEGIATAN)
    // =============================================
    <?php if (!empty($avgTripChartData)): ?>
    (function() {
        const labels = <?= json_encode(array_column($avgTripChartData, 'label')) ?>;
        const dataValues = <?= json_encode(array_column($avgTripChartData, 'amount')) ?>;
        const ctx = document.getElementById('avgTripChart').getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, '#1cc88a');
        gradient.addColorStop(1, '#13855c');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Beban Saya',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: '#1cc88a',
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Beban Saya: ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (val) => formatCurrency(val) },
                        grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    })();
    <?php endif; ?>

    // =============================================
    // 2.5. CHART RATA-RATA PER ANGGOTA (PER GRUP)
    // =============================================
    <?php if (!empty($avgGroupChartData)): ?>
    (function() {
        const labels = <?= json_encode(array_column($avgGroupChartData, 'label')) ?>;
        const dataValues = <?= json_encode(array_column($avgGroupChartData, 'amount')) ?>;
        const ctx = document.getElementById('avgGroupChart').getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, '#6f42c1');
        gradient.addColorStop(1, '#4a148c');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Beban Saya',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: '#6f42c1',
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Beban Saya: ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (val) => formatCurrency(val) },
                        grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    })();
    <?php endif; ?>

    // =============================================
    // 3. CHART TOTAL PENGELUARAN (PER PERIODE)
    // =============================================
    <?php if (!empty($spendingChartData)): ?>
    (function() {
        const labels = <?= json_encode(array_column($spendingChartData, 'label')) ?>;
        const dataValues = <?= json_encode(array_column($spendingChartData, 'amount')) ?>;
        const ctx = document.getElementById('spendingChart').getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, '#f6c23e');
        gradient.addColorStop(1, '#dda20a');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Pengeluaran',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: '#f6c23e',
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Total: ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (val) => formatCurrency(val) },
                        grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    })();
    <?php endif; ?>

    // =============================================
    // 4. CHART KONTRIBUSI PEMBAYARAN ANGGOTA
    // =============================================
    <?php if (!empty($memberSpendingChartData)): ?>
    (function() {
        const labels = <?= json_encode(array_column($memberSpendingChartData, 'label')) ?>;
        const dataValues = <?= json_encode(array_column($memberSpendingChartData, 'amount')) ?>;
        const ctx = document.getElementById('memberContributionChart').getContext('2d');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: dataValues,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                        '#fd7e14', '#6f42c1', '#e83e8c', '#858796'
                    ],
                    hoverOffset: 6,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: { size: 10, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    })();
    <?php endif; ?>

    // =============================================
    // 5. CHART TREN ITEM (PER TRANSAKSI)
    // =============================================
    const trendTxData = <?= json_encode($trendTransactionsByPeriod) ?>;
    let trendChart = null;

    const userColors = {};
    const colorPalette = [
        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
        '#fd7e14', '#6f42c1', '#e83e8c', '#858796'
    ];
    let colorIndex = 0;

    function getPayerColor(name) {
        if (!userColors[name]) {
            userColors[name] = colorPalette[colorIndex % colorPalette.length];
            colorIndex++;
        }
        return userColors[name];
    }

    function renderTrendChart(periodId) {
        const txs = trendTxData[periodId] || [];
        const labels = txs.map(t => t.description);
        const amounts = txs.map(t => t.amount);
        const bgColors = txs.map(t => getPayerColor(t.paid_by_name));

        const canvasEl = document.getElementById('trendItemChart');
        if (!canvasEl) return;
        const ctx = canvasEl.getContext('2d');

        if (trendChart) {
            trendChart.destroy();
        }

        // Draw legend
        const legendContainer = $('#trend-legend');
        legendContainer.empty();
        
        // Find unique payers in these transactions
        const uniquePayers = [...new Set(txs.map(t => t.paid_by_name))];
        uniquePayers.forEach(payer => {
            const color = getPayerColor(payer);
            legendContainer.append(`
                <span class="mr-3 mb-2 font-weight-bold" style="font-size: 0.85rem; color: #495057;">
                    <span style="display: inline-block; width: 12px; height: 12px; background-color: ${color}; border-radius: 3px; margin-right: 5px; vertical-align: middle;"></span>
                    ${payer}
                </span>
            `);
        });

        trendChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nominal Pengeluaran',
                    data: amounts,
                    backgroundColor: bgColors,
                    borderRadius: 6,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const tx = txs[context.dataIndex];
                                return ` ${tx.description}: ${formatCurrency(tx.amount)} (Dibayar oleh: ${tx.paid_by_name})`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (val) => formatCurrency(val) },
                        grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Trigger on period change
    $('#trend-period-select').on('change', function() {
        const selectedVal = $(this).val();
        localStorage.setItem(lastTrendPeriodKey, selectedVal); // Save selected period
        renderTrendChart(selectedVal);
    });

    // Restore last selected period if exists, otherwise load default
    const cachedPeriod = localStorage.getItem(lastTrendPeriodKey);
    if (cachedPeriod && $(`.period-node[data-id="${cachedPeriod}"]`).length) {
        $(`.period-node[data-id="${cachedPeriod}"]`).trigger('click');
    } else {
        const initialPeriod = $('#trend-period-select').val();
        if (initialPeriod) {
            $(`.period-node[data-id="${initialPeriod}"]`).trigger('click');
        }
    }

    // Restore last active tab if exists
    const cachedTab = localStorage.getItem(lastTabKey);
    if (cachedTab && $(`a[data-toggle="pill"][href="${cachedTab}"]`).length) {
        $(`a[data-toggle="pill"][href="${cachedTab}"]`).tab('show');
    }

    // ---- Installment Projection Chart ----
    <?php if ($installmentStats['has_installments']) : ?>
    const projCtx = document.getElementById('installmentProjectionChart');
    if (projCtx) {
        new Chart(projCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($installmentStats['chart_labels']) ?>,
                datasets: [
                    {
                        label: 'Tagihan Saya',
                        data: <?= json_encode($installmentStats['chart_pay']) ?>,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.05)',
                        borderWidth: 2.5,
                        tension: 0.3,
                        pointBackgroundColor: '#007bff',
                        fill: true
                    },
                    {
                        label: 'Piutang Masuk',
                        data: <?= json_encode($installmentStats['chart_rcv']) ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.05)',
                        borderWidth: 2.5,
                        tension: 0.3,
                        pointBackgroundColor: '#28a745',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            boxWidth: 8,
                            font: { size: 9, weight: 'bold' },
                            padding: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${formatCurrency(context.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (val) => formatCurrency(val),
                            font: { size: 8 }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.03)', drawBorder: false }
                    },
                    x: {
                        ticks: { font: { size: 8 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ---- Stacked Bar Trends Charts ----
    const bTrendsCtx = document.getElementById('borrowerTrendsChart');
    if (bTrendsCtx) {
        new Chart(bTrendsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($installmentStats['chart_labels']) ?>,
                datasets: <?= json_encode($installmentStats['borrower_trends']) ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            boxWidth: 8,
                            font: { size: 8.5 },
                            padding: 6
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${formatCurrency(context.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            callback: (val) => formatCurrency(val),
                            font: { size: 8 }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.03)', drawBorder: false }
                    },
                    x: {
                        stacked: true,
                        ticks: { font: { size: 8 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    const lTrendsCtx = document.getElementById('lenderTrendsChart');
    if (lTrendsCtx) {
        new Chart(lTrendsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($installmentStats['chart_labels']) ?>,
                datasets: <?= json_encode($installmentStats['lender_trends']) ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            boxWidth: 8,
                            font: { size: 8.5 },
                            padding: 6
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${formatCurrency(context.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            callback: (val) => formatCurrency(val),
                            font: { size: 8 }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.03)', drawBorder: false }
                    },
                    x: {
                        stacked: true,
                        ticks: { font: { size: 8 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Force dispatch window resize to recalculate canvas sizes inside bootstrap tabs
    $('a[data-toggle="pill"]').on('shown.bs.tab', function () {
        window.dispatchEvent(new Event('resize'));
    });
    <?php endif; ?>
});
</script>

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

/* Prevent dropdown clipping in cards and tabs */
.card-outline-tabs,
.card-outline-tabs .card-body,
.tab-content,
.tab-pane {
    overflow: visible !important;
}
.custom-tree-dropdown .dropdown-menu {
    z-index: 1050 !important;
}

/* Micro-animations and hover cards */
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-10deg); }
    75% { transform: rotate(10deg); }
}

.animate-wave {
    display: inline-block;
    animation: wave 1.5s infinite ease-in-out;
    transform-origin: bottom right;
}

/* Responsive justified tabs for mobile */
@media (max-width: 767.98px) {
    #dashboard-tabs .nav-link {
        padding: 8px 2px !important;
        font-size: 0.7rem;
        text-align: center;
    }
    #dashboard-tabs .nav-link i {
        display: block;
        margin-bottom: 4px;
        font-size: 0.95rem;
        margin-right: 0 !important;
    }
}
</style>
<?= $this->endSection() ?>
