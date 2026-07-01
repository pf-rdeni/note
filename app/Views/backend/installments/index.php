<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>


<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-hand-holding-usd mr-2 text-primary"></i>Cicilan</h1>
        <p class="text-muted small mb-0">Kelola cicilan pinjaman anggota &amp; kartu kredit pribadi</p>
    </div>
    <div class="col-sm-6 text-right d-flex justify-content-end align-items-center flex-wrap">
        <!-- Switch Role View Toggle -->
        <div class="btn-group btn-group-toggle shadow-sm mr-3" role="group">
            <a href="<?= base_url('backend/installments?role=borrower') ?>" class="btn btn-sm <?= $role === 'borrower' ? 'btn-primary active' : 'btn-outline-primary' ?>">
                <i class="fas fa-file-invoice-dollar mr-1"></i> Sebagai Peminjam
            </a>
            <a href="<?= base_url('backend/installments?role=lender') ?>" class="btn btn-sm <?= $role === 'lender' ? 'btn-primary active' : 'btn-outline-primary' ?>">
                <i class="fas fa-hand-holding-usd mr-1"></i> Sebagai Pemberi Pinjaman
            </a>
        </div>

        <?php 
        $btnStyle = empty($selectedTripId) ? 'display: none;' : '';
        ?>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btnTambahCicilan" data-toggle="modal" data-target="#modalTambahCicilan" style="<?= $btnStyle ?>">
            <i class="fas fa-plus mr-1"></i> Tambah Cicilan
        </button>
    </div>
</div>

<!-- Filter Trip -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-body py-2 px-3">
                <form method="GET" action="<?= base_url('backend/installments') ?>" class="form-inline flex-wrap" id="filterForm">
                    <input type="hidden" name="role" value="<?= esc($role) ?>">
                    <label class="mr-2 font-weight-bold"><i class="fas fa-route mr-1 text-primary"></i>Kegiatan:</label>
                    <select name="trip_id" class="form-control form-control-sm mr-2" id="tripSelect" onchange="this.form.submit()">
                        <option value="">-- Semua Kegiatan --</option>
                        <?php foreach ($availableTrips as $trip) : ?>
                            <option value="<?= $trip['id'] ?>" <?= (string)$selectedTripId === (string)$trip['id'] ? 'selected' : '' ?>>
                                <?= esc($trip['group_name']) ?> › <?= esc($trip['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($selectedTripId)) : ?>
                        <a href="<?= base_url('backend/installments?reset') ?>" class="btn btn-outline-secondary btn-sm ml-1">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Cari nama kegiatan yang sedang dipilih
$selectedTripName = '';
if (!empty($selectedTripId)) {
    foreach ($availableTrips as $trip) {
        if ((string)$trip['id'] === (string)$selectedTripId) {
            $selectedTripName = $trip['name'];
            break;
        }
    }
}

// Calculate monthly summary grouped by Month + Group (Lender/CC) dari semua kegiatan
$summaryItems = [];
$summaryHasBill = false;

// Group allInstallmentsForSummary by: source_type | lender_user_id | borrower_user_id | trip_id
$summaryGroups = [];
if (!empty($allInstallmentsForSummary)) {
    foreach ($allInstallmentsForSummary as $inst) {
        $groupKey = $inst['source_type'] . '|' . ($inst['lender_user_id'] ?? 'null') . '|' . $inst['borrower_user_id'] . '|' . $inst['trip_id'];
        if (!isset($summaryGroups[$groupKey])) {
            $summaryGroups[$groupKey] = [
                'trip_id'          => $inst['trip_id'],
                'source_type'      => $inst['source_type'],
                'lender_user_id'   => $inst['lender_user_id'],
                'lender_name'      => $inst['lender_name'] ?? null,
                'borrower_user_id' => $inst['borrower_user_id'],
                'borrower_name'    => $inst['borrower_name'] ?? '',
                'trip_name'        => $inst['trip_name'] ?? '',
                'installments'     => [],
            ];
        }
        $summaryGroups[$groupKey]['installments'][] = $inst;
    }

        foreach ($summaryMonthColumns as $col) {
            foreach ($summaryGroups as $groupKey => $group) {
                if ($role === 'borrower') {
                    $isSelf = ((int)$group['borrower_user_id'] === (int)user_id());
                    if (!$isSelf) {
                        continue;
                    }
                } else {
                    $isSelf = ((int)$group['lender_user_id'] === (int)user_id());
                    if (!$isSelf) {
                        continue;
                    }
                }

                $isLoan = ($group['source_type'] === 'member_loan');
                $groupAmount = 0;
                $groupPaid = true;
                $hasPayment = false;
                $descriptions = [];
                $instDetails = [];

                foreach ($group['installments'] as $inst) {
                    $payment = $inst['payments'][$col] ?? null;
                    if ($payment) {
                        $hasPayment = true;
                        $groupAmount += (int)$payment['due_amount'];
                        if ($payment['status'] !== 'paid') {
                            $groupPaid = false;
                        }
                        $descriptions[] = $inst['description'] . ' (' . number_format($payment['due_amount'], 0, ',', '.') . ')';
                        $instDetails[] = [
                            'id'          => $inst['id'],
                            'description' => $inst['description'],
                            'amount'      => $payment['due_amount'],
                            'status'      => $payment['status']
                        ];
                    }
                }

                if ($hasPayment) {
                    $summaryHasBill = true;
                    $summaryItems[] = [
                        'due_date'     => $col,
                        'trip_id'      => $group['trip_id'],
                        'trip_name'    => $group['trip_name'],
                        'source_type'  => $group['source_type'],
                        'lender_id'    => $group['lender_user_id'],
                        'lender_name'  => ($role === 'borrower') 
                            ? ($isLoan ? ($group['lender_name'] ?? 'Anggota') : 'Pinjaman Pribadi')
                            : ($group['borrower_name'] ?? 'Anggota'),
                        'borrower_id'  => $group['borrower_user_id'],
                        'amount'       => $groupAmount,
                        'is_paid'      => $groupPaid,
                        'desc_list'    => implode(', ', $descriptions),
                        'inst_details' => $instDetails
                    ];
                }
            }
        }
    }

    // Calculate dashboard statistics
    $statTotalLoan = 0;
    $statActiveCount = 0;
    if (!empty($allInstallmentsForSummary)) {
        foreach ($allInstallmentsForSummary as $inst) {
            if ($inst['status'] === 'active') {
                $statTotalLoan += (int)$inst['total_amount'];
                $statActiveCount++;
            }
        }
    }

    $currentMonthKey = date('Y-m-01');
    $statThisMonthDue = 0;
    $statThisMonthPaid = true;
    $statThisMonthHasDues = false;
    if (!empty($allInstallmentsForSummary)) {
        foreach ($allInstallmentsForSummary as $inst) {
            $p = $inst['payments'][$currentMonthKey] ?? null;
            if ($p) {
                $statThisMonthHasDues = true;
                $statThisMonthDue += (int)$p['due_amount'];
                if ($p['status'] !== 'paid') {
                    $statThisMonthPaid = false;
                }
            }
        }
    }

    $statusLabelThisMonth = '';
    if (!$statThisMonthHasDues) {
        $statusLabelThisMonth = 'Tidak Ada Tagihan';
    } elseif ($statThisMonthPaid) {
        $statusLabelThisMonth = 'Lunas';
    } else {
        $statusLabelThisMonth = 'Belum Lunas';
    }

    $nextMonthKey = date('Y-m-01', strtotime('+1 month'));
    $statNextMonthDue = 0;
    if (!empty($allInstallmentsForSummary)) {
        foreach ($allInstallmentsForSummary as $inst) {
            $p = $inst['payments'][$nextMonthKey] ?? null;
            if ($p) {
                $statNextMonthDue += (int)$p['due_amount'];
            }
        }
    }

    // Role-based dynamic labels
    $labelTotalActive = ($role === 'borrower') ? 'TOTAL PINJAMAN AKTIF' : 'TOTAL PIUTANG AKTIF';
    $labelThisMonth   = ($role === 'borrower') ? 'TAGIHAN BULAN INI' : 'TAGIHAN MASUK BULAN INI';
    $labelNextMonth   = ($role === 'borrower') ? 'PROYEKSI BULAN DEPAN' : 'PROYEKSI MASUK BULAN DEPAN';
    $descActiveCount  = ($role === 'borrower') ? 'cicilan berjalan' : 'piutang berjalan';
    $descUnfinished   = ($role === 'borrower') ? 'Harus dilunasi berkala' : 'Akan diterima berkala';
    ?>

    <?php if (!empty($allInstallmentsForSummary)) : ?>
        <!-- Summary Cards Dashboard -->
        <div class="row mb-4">
            <!-- Card 1: Total Pinjaman (Blue) -->
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card bg-primary text-white border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-uppercase text-white-50 font-weight-bold mb-1" style="font-size: 0.72rem; letter-spacing: 1px;"><?= $labelTotalActive ?></p>
                                <h4 class="mb-0 font-weight-bold" style="font-size: 1.35rem;">
                                    Rp <?= number_format($statTotalLoan, 0, ',', '.') ?>
                                </h4>
                            </div>
                            <div class="bg-white-10 p-2 rounded-circle" style="background: rgba(255,255,255,0.15);">
                                <i class="fas fa-wallet fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-info-circle mr-1"></i>Dari <?= $statActiveCount ?> <?= $descActiveCount ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Cicilan Bulan Ini (Green/Orange) -->
            <?php
            $card2Bg = $statThisMonthPaid ? 'bg-success' : 'bg-warning text-dark';
            $card2IconBg = $statThisMonthPaid ? 'rgba(255,255,255,0.15)' : 'rgba(0,0,0,0.08)';
            $card2TextMuted = $statThisMonthPaid ? 'text-white-50' : 'text-dark-50';
            $card2StatusClass = $statThisMonthPaid ? 'badge badge-success border border-white' : 'badge badge-danger';
            ?>
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card <?= $card2Bg ?> border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-uppercase <?= $card2TextMuted ?> font-weight-bold mb-1" style="font-size: 0.72rem; letter-spacing: 1px;"><?= $labelThisMonth ?></p>
                                <h4 class="mb-0 font-weight-bold" style="font-size: 1.35rem;">
                                    Rp <?= number_format($statThisMonthDue, 0, ',', '.') ?>
                                </h4>
                            </div>
                            <div class="p-2 rounded-circle" style="background: <?= $card2IconBg ?>;">
                                <i class="fas fa-calendar-check fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-2 small">
                            <span class="<?= $card2StatusClass ?>"><?= $statusLabelThisMonth ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Cicilan Bulan Depan (Orange) -->
            <div class="col-xl-3 col-md-6 mb-3 mb-md-0">
                <div class="card bg-orange text-white border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 12px; background-color: #fd7e14 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-uppercase text-white-50 font-weight-bold mb-1" style="font-size: 0.72rem; letter-spacing: 1px;"><?= $labelNextMonth ?></p>
                                <h4 class="mb-0 font-weight-bold" style="font-size: 1.35rem;">
                                    Rp <?= number_format($statNextMonthDue, 0, ',', '.') ?>
                                </h4>
                            </div>
                            <div class="bg-white-10 p-2 rounded-circle" style="background: rgba(255,255,255,0.15);">
                                <i class="fas fa-calendar-alt fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-clock mr-1"></i>Jatuh tempo <?= date('M Y', strtotime($nextMonthKey)) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Cicilan Belum Selesai (Teal) -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 12px; background-color: #17a2b8 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-uppercase text-white-50 font-weight-bold mb-1" style="font-size: 0.72rem; letter-spacing: 1px;">BELUM SELESAI</p>
                                <h4 class="mb-0 font-weight-bold" style="font-size: 1.35rem;">
                                    <?= $statActiveCount ?> Cicilan
                                </h4>
                            </div>
                            <div class="bg-white-10 p-2 rounded-circle" style="background: rgba(255,255,255,0.15);">
                                <i class="fas fa-hourglass-half fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-tasks mr-1"></i><?= $descUnfinished ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($summaryHasBill) : ?>
        <?php
        // Kumpulkan nilai unik untuk opsi filter dropdown
        $uniqueMonths = [];
        $uniqueTrips = [];
        $uniqueLenders = [];

        foreach ($summaryItems as $item) {
            $monthKey = $item['due_date'];
            $monthVal = date('F Y', strtotime($item['due_date']));
            $uniqueMonths[$monthKey] = $monthVal;
            
            $uniqueTrips[$item['trip_name']] = $item['trip_name'];
            $uniqueLenders[$item['lender_name']] = $item['lender_name'];
        }
        asort($uniqueMonths);
        asort($uniqueTrips);
        asort($uniqueLenders);

        // Group summaryItems by Month for parent-child collapse
        $summaryMonthGroups = [];
        foreach ($summaryItems as $item) {
            $monthKey = $item['due_date'];
            if (!isset($summaryMonthGroups[$monthKey])) {
                $summaryMonthGroups[$monthKey] = [
                    'due_date' => $item['due_date'],
                    'total'    => 0,
                    'is_paid'  => true,
                    'details'  => []
                ];
            }
            $summaryMonthGroups[$monthKey]['total'] += $item['amount'];
            if (!$item['is_paid']) {
                $summaryMonthGroups[$monthKey]['is_paid'] = false;
            }
            $summaryMonthGroups[$monthKey]['details'][] = $item;
        }
        ksort($summaryMonthGroups);
        ?>

        <!-- Card Ringkasan Pembayaran Bulanan -->
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-header py-2 px-3">
                <h3 class="card-title font-weight-bold text-info" style="font-size:0.95rem;">
                    <i class="fas fa-calculator mr-2"></i>Ringkasan Tagihan Bulanan Saya
                </h3>
            </div>
            
            <!-- Panel Filter -->
            <div class="p-3 bg-light border-bottom" style="font-size: 0.85rem;">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="font-weight-bold text-muted mb-1"><i class="fas fa-calendar-alt mr-1 text-info"></i>Saring Bulan:</label>
                        <select id="filterSummaryMonth" class="form-control form-control-sm">
                            <option value="">-- Semua Bulan --</option>
                            <?php foreach ($uniqueMonths as $k => $v) : ?>
                                <option value="<?= $k ?>"><?= esc($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="font-weight-bold text-muted mb-1"><i class="fas fa-route mr-1 text-info"></i>Saring Kegiatan:</label>
                        <select id="filterSummaryTrip" class="form-control form-control-sm">
                            <option value="">-- Semua Kegiatan --</option>
                            <?php foreach ($uniqueTrips as $t) : ?>
                                <option value="<?= esc($t) ?>" <?= $selectedTripName === $t ? 'selected' : '' ?>><?= esc($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-sm-0">
                        <label class="font-weight-bold text-muted mb-1"><i class="fas fa-hand-holding-usd mr-1 text-info"></i>Saring Pemberi / Sumber:</label>
                        <select id="filterSummaryLender" class="form-control form-control-sm">
                            <option value="">-- Semua Pemberi/Sumber --</option>
                            <?php foreach ($uniqueLenders as $l) : ?>
                                <option value="<?= esc($l) ?>"><?= esc($l) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="font-weight-bold text-muted mb-1"><i class="fas fa-info-circle mr-1 text-info"></i>Saring Status:</label>
                        <select id="filterSummaryStatus" class="form-control form-control-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="unpaid" selected>Belum Lunas</option>
                            <option value="paid">Lunas</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered mb-0" style="font-size: 0.82rem;" id="summaryTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Bulan / Rincian Cicilan</th>
                                <th>Kegiatan</th>
                                <th>Pemberi Pinjaman / Sumber</th>
                                <th class="text-right">Jumlah Tagihan</th>
                                <th class="text-center" style="width: 120px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="summaryTableBody">
                            <?php 
                            $grandSummaryTotal = 0;
                            foreach ($summaryMonthGroups as $monthKey => $groupData) : 
                                $grandSummaryTotal += $groupData['total'];
                            ?>
                                <!-- Row Parent (Month) -->
                                <tr class="month-parent-row" data-month-key="<?= $monthKey ?>" style="cursor: pointer; background-color: #f1f3f5;">
                                    <td class="font-weight-bold text-dark">
                                        <i class="fas fa-chevron-right mr-2 text-info expand-icon-month" style="transition: transform 0.2s;"></i>
                                        <?= date('F Y', strtotime($monthKey)) ?>
                                    </td>
                                    <td colspan="2" class="text-muted text-xs">
                                        <span class="parent-count"><?= count($groupData['details']) ?> tagihan</span>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark bg-light">
                                        <span class="parent-amount">Rp <?= number_format($groupData['total'], 0, ',', '.') ?></span>
                                    </td>
                                    <td class="text-center text-xs">
                                        <?php if ($groupData['is_paid']) : ?>
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Lunas</span>
                                        <?php else : ?>
                                            <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Belum Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <!-- Row Children (Details) -->
                                <?php foreach ($groupData['details'] as $item) : 
                                    $gpKey = $item['trip_id'] . '|' . ($item['lender_id'] ?? 'null') . '|' . $item['borrower_id'] . '|' . $item['source_type'] . '|' . date('Y-m-01', strtotime($item['due_date']));
                                    $paymentRecord = $allGroupPaymentMap[$gpKey] ?? null;
                                ?>
                                    <tr class="month-child-row month-detail-<?= $monthKey ?>" 
                                        style="display: none; background-color: #fafafa;"
                                        data-month="<?= $item['due_date'] ?>" 
                                        data-trip="<?= esc($item['trip_name']) ?>" 
                                        data-lender="<?= esc($item['lender_name']) ?>" 
                                        data-amount="<?= $item['amount'] ?>"
                                        data-status="<?= $item['is_paid'] ? 'paid' : 'unpaid' ?>">
                                        <td style="padding-left: 25px;" class="text-muted">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-arrow-right mr-1 text-secondary" style="font-size:0.75rem;"></i>
                                                <strong>Rincian:</strong>
                                            </div>
                                            <div class="pl-3">
                                                <?php foreach ($item['inst_details'] as $detail) : ?>
                                                    <div class="d-flex justify-content-between align-items-center border-bottom py-1" style="max-width: 320px;">
                                                        <span><?= esc($detail['description']) ?> (Rp <?= number_format($detail['amount'], 0, ',', '.') ?>)</span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td><?= esc($item['trip_name']) ?></td>
                                        <td>
                                            <?php if ($item['source_type'] === 'member_loan') : ?>
                                                <span class="text-primary"><i class="fas fa-user mr-1"></i><?= esc($item['lender_name']) ?></span>
                                            <?php else : ?>
                                                <span class="text-success"><i class="fas fa-credit-card mr-1"></i><?= esc($item['lender_name']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            Rp <?= number_format($item['amount'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center text-xs">
                                            <?php if ($item['is_paid']) : ?>
                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Lunas</span>
                                            <?php else : ?>
                                                <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Belum Lunas</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold" style="background:#f8f9fa;">
                                <td colspan="3" class="text-right">Total Keseluruhan Tagihan:</td>
                                <td class="text-right text-primary" id="summaryGrandTotal">
                                    Rp <?= number_format($grandSummaryTotal, 0, ',', '.') ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="alert alert-info shadow-sm">
            <i class="fas fa-info-circle mr-2"></i>Belum ada tagihan cicilan untuk Anda saat ini.
        </div>
    <?php endif; ?>



    <?php if (!empty($allInstallmentsForSummary)) : ?>
        <!-- Card Rincian & Proyeksi Pembayaran Bulanan -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
                <h3 class="card-title font-weight-bold text-primary mb-0" style="font-size:0.95rem;">
                    <i class="fas fa-calendar-alt mr-2"></i>Rincian &amp; Proyeksi Pembayaran Bulanan
                </h3>
                <div class="custom-control custom-switch my-1">
                    <input type="checkbox" class="custom-control-input" id="togglePaidMonths">
                    <label class="custom-control-label text-muted small font-weight-bold" for="togglePaidMonths" style="cursor: pointer;">
                        Tampilkan Bulan Lunas
                    </label>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.82rem;" id="projectionTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Uraian Cicilan</th>
                                <th>Jenis</th>
                                <th><?= $role === 'borrower' ? 'Pemberi / Sumber' : 'Peminjam' ?></th>
                                <th>Kegiatan</th>
                                <th class="text-right" style="min-width:110px;">Total Pinjaman</th>
                                <?php foreach ($summaryMonthColumns as $col) : ?>
                                    <th class="text-right" style="min-width:105px;" data-month-col="<?= $col ?>">
                                        <?= date('M\'y', strtotime($col)) ?>
                                    </th>
                                <?php endforeach; ?>
                                <?php if ($role === 'borrower') : ?>
                                    <th class="text-center" style="width: 60px;">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grandTotalAll = 0;
                            $monthlyTotals = array_fill_keys($summaryMonthColumns, 0);
                            
                            foreach ($allInstallmentsForSummary as $inst) :
                                $isLoan = ($inst['source_type'] === 'member_loan');
                                $grandTotalAll += (int)$inst['total_amount'];
                            ?>
                                <tr class="projection-row" 
                                    data-trip="<?= esc($inst['trip_name']) ?>"
                                    data-lender="<?= $isLoan ? esc($inst['lender_name']) : 'Pinjaman Pribadi' ?>"
                                    data-status-row="<?= $inst['status'] ?>"
                                    data-total-amount="<?= $inst['total_amount'] ?>">
                                    <td><strong><?= esc($inst['description']) ?></strong></td>
                                    <td>
                                        <?php if ($isLoan) : ?>
                                            <span class="badge badge-primary"><i class="fas fa-users mr-1"></i>Pinjaman Anggota</span>
                                        <?php else : ?>
                                            <span class="badge badge-success"><i class="fas fa-user mr-1"></i>Pinjaman Pribadi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($role === 'borrower') : ?>
                                            <?php if ($isLoan) : ?>
                                                <span class="text-primary"><?= esc($inst['lender_name']) ?></span>
                                            <?php else : ?>
                                                <span class="text-success">Pinjaman Pribadi</span>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="text-primary"><?= esc($inst['borrower_name']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="text-muted"><?= esc($inst['trip_name']) ?></span></td>
                                    <td class="text-right font-weight-bold text-dark">
                                        Rp <?= number_format($inst['total_amount'], 0, ',', '.') ?>
                                    </td>
                                    <?php foreach ($summaryMonthColumns as $col) : 
                                        $payment = $inst['payments'][$col] ?? null;
                                        if ($payment) {
                                            $monthlyTotals[$col] += (int)$payment['due_amount'];
                                        }
                                    ?>
                                        <td class="text-right val-cell" data-month-col="<?= $col ?>" data-amount="<?= $payment ? $payment['due_amount'] : 0 ?>">
                                            <?php if ($payment) : 
                                                $gpKey = $inst['trip_id'] . '|' . ($inst['lender_user_id'] ?? 'null') . '|' . $inst['borrower_user_id'] . '|' . $inst['source_type'] . '|' . date('Y-m-01', strtotime($col));
                                                $paymentRecord = $allGroupPaymentMap[$gpKey] ?? null;
                                            ?>
                                                <?php if ($payment['status'] === 'paid') : ?>
                                                    <span class="text-success font-weight-bold" style="white-space: nowrap;">
                                                        <i class="fas fa-check-circle text-success mr-1" title="Lunas"></i><?php if ($paymentRecord && !empty($paymentRecord['proof_image'])) : ?><a href="javascript:void(0)" class="btn-view-proof mr-1 text-info" data-image="<?= base_url($paymentRecord['proof_image']) ?>" title="Lihat Bukti Transfer"><i class="fas fa-image"></i></a><?php endif; ?>Rp <?= number_format($payment['due_amount'], 0, ',', '.') ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-secondary" style="white-space: nowrap;">
                                                        <i class="far fa-clock text-warning mr-1" title="Belum Lunas"></i>
                                                        Rp <?= number_format($payment['due_amount'], 0, ',', '.') ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <?php if ($role === 'borrower') : ?>
                                        <td class="text-center">
                                            <?php if ($inst['status'] !== 'completed') : ?>
                                                <?php
                                                $paidCountForEdit = 0;
                                                foreach ($inst['payments'] as $p) {
                                                    if ($p['status'] === 'paid') {
                                                        $paidCountForEdit++;
                                                    }
                                                }
                                                ?>
                                                <div class="d-flex justify-content-center">
                                                    <button type="button" class="btn btn-xs btn-outline-primary btn-edit-inst mr-1"
                                                        data-id="<?= $inst['id'] ?>"
                                                        data-desc="<?= esc($inst['description']) ?>"
                                                        data-source="<?= $inst['source_type'] ?>"
                                                        data-lender="<?= $inst['lender_user_id'] ?? '' ?>"
                                                        data-total="<?= $inst['total_amount'] ?>"
                                                        data-monthly="<?= $inst['monthly_amount'] ?>"
                                                        data-months="<?= $inst['installment_months'] ?>"
                                                        data-start="<?= date('Y-m', strtotime($inst['start_date'])) ?>"
                                                        data-note="<?= esc($inst['note'] ?? '') ?>"
                                                        data-paid-count="<?= $paidCountForEdit ?>"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-outline-danger btn-delete-inst"
                                                        data-id="<?= $inst['id'] ?>"
                                                        data-desc="<?= esc($inst['description']) ?>"
                                                        data-trip="<?= $inst['trip_id'] ?>"
                                                        title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="font-weight-bold" style="background:#f8f9fa;">
                            <!-- Row Total per Bulan -->
                            <tr>
                                <td colspan="4" class="text-right">Total Tagihan Bulanan:</td>
                                <td class="text-right text-primary" id="projectionGrandTotal">
                                    Rp <?= number_format($grandTotalAll, 0, ',', '.') ?>
                                </td>
                                <?php foreach ($summaryMonthColumns as $col) : ?>
                                    <td class="text-right text-primary total-cell" data-month-col="<?= $col ?>">
                                        <?= $monthlyTotals[$col] > 0 ? 'Rp ' . number_format($monthlyTotals[$col], 0, ',', '.') : '—' ?>
                                    </td>
                                <?php endforeach; ?>
                                <?php if ($role === 'borrower') : ?>
                                    <td></td>
                                <?php endif; ?>
                            </tr>
                            <!-- Row Aksi Pelunasan -->
                            <tr>
                                <td colspan="4" class="text-muted small text-right">Aksi Pelunasan:</td>
                                <td></td>
                                <?php foreach ($summaryMonthColumns as $col) : ?>
                                    <td class="text-center py-1 action-cell" data-month-col="<?= $col ?>">
                                        <?php
                                        // Kumpulkan pembayaran belum lunas di bulan ini
                                        $unpaidPayments = [];
                                        foreach ($allInstallmentsForSummary as $inst) {
                                            $payment = $inst['payments'][$col] ?? null;
                                            if ($payment && $payment['status'] !== 'paid') {
                                                $isLoan = ($inst['source_type'] === 'member_loan');
                                                $lenderNameVal = $isLoan ? ($inst['lender_name'] ?? 'Anggota') : 'Pinjaman Pribadi';
                                                $groupKey = $inst['source_type'] . '|' . ($inst['lender_user_id'] ?? 'null') . '|' . $inst['trip_id'];
                                                
                                                if (!isset($unpaidPayments[$groupKey])) {
                                                    $unpaidPayments[$groupKey] = [
                                                        'trip_id'     => $inst['trip_id'],
                                                        'trip_name'   => $inst['trip_name'] ?? '',
                                                        'lender_id'   => $inst['lender_user_id'],
                                                        'lender_name' => $lenderNameVal,
                                                        'borrower_id' => $inst['borrower_user_id'],
                                                        'borrower_name' => $inst['borrower_name'] ?? 'Anggota',
                                                        'source_type' => $inst['source_type'],
                                                        'amount'      => 0
                                                    ];
                                                }
                                                $unpaidPayments[$groupKey]['amount'] += (int)$payment['due_amount'];
                                            }
                                        }

                                        // Render unpaid buttons wrapper
                                        foreach ($unpaidPayments as $up) {
                                            if ($role === 'lender') {
                                                ?>
                                                <div class="unpaid-btn-wrapper mb-1" 
                                                     data-trip="<?= esc($up['trip_name']) ?>" 
                                                     data-lender="<?= esc($up['lender_name']) ?>" 
                                                     data-month-col="<?= $col ?>">
                                                    <span class="text-secondary small font-weight-bold" style="white-space: nowrap;">
                                                        <i class="far fa-clock text-warning mr-1"></i>Belum Lunas (<?= esc($up['borrower_name']) ?>)
                                                    </span>
                                                </div>
                                                <?php
                                            } else {
                                                $btnClass = $up['source_type'] === 'member_loan' ? 'btn-primary' : 'btn-success';
                                                $btnIcon = $up['source_type'] === 'member_loan' ? 'fa-paper-plane' : 'fa-check';
                                                $btnText = $up['source_type'] === 'member_loan' ? 'Bayar ' . esc($up['lender_name']) : 'Bayar Pribadi';
                                                ?>
                                                <div class="unpaid-btn-wrapper mb-1" 
                                                     data-trip="<?= esc($up['trip_name']) ?>" 
                                                     data-lender="<?= esc($up['lender_name']) ?>" 
                                                     data-month-col="<?= $col ?>">
                                                    <button type="button" class="btn btn-xs <?= $btnClass ?> btn-pay-month btn-block text-left px-1"
                                                        data-trip="<?= $up['trip_id'] ?>"
                                                        data-lender="<?= $up['lender_id'] ?>"
                                                        data-borrower="<?= $up['borrower_id'] ?>"
                                                        data-month="<?= $col ?>"
                                                        data-total="<?= $up['amount'] ?>"
                                                        data-label="<?= date('M Y', strtotime($col)) ?>"
                                                        data-lender-name="<?= esc($up['lender_name']) ?>"
                                                        data-source="<?= $up['source_type'] ?>"
                                                        style="font-size: 0.68rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                        title="Bayar ke <?= esc($up['lender_name']) ?>: Rp <?= number_format($up['amount'], 0, ',', '.') ?>">
                                                        <i class="fas <?= $btnIcon ?> mr-1"></i><?= $btnText ?>
                                                    </button>
                                                </div>
                                                <?php
                                            }
                                        }

                                        // Check untuk badge lunas / na
                                        $hasAnyPayment = false;
                                        foreach ($allInstallmentsForSummary as $inst) {
                                            if (isset($inst['payments'][$col])) {
                                                $hasAnyPayment = true;
                                                break;
                                            }
                                        }
                                        
                                        $badgeStyle = !empty($unpaidPayments) ? 'display: none;' : '';
                                        ?>
                                        <span class="badge badge-success text-xs lunas-badge" style="<?= $badgeStyle ?>"><i class="fas fa-check mr-1"></i>Lunas</span>
                                        <span class="text-muted na-badge" style="<?= $hasAnyPayment ? 'display: none;' : '' ?>">—</span>
                                    </td>
                                <?php endforeach; ?>
                                <?php if ($role === 'borrower') : ?>
                                    <td></td>
                                <?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

<!-- ===================== MODAL: Tambah Cicilan ===================== -->
<div class="modal fade" id="modalTambahCicilan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= base_url('backend/installments/store') ?>" id="formTambahCicilan">
                <?= csrf_field() ?>
                <input type="hidden" name="trip_id" value="<?= $selectedTripId ?>">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Tambah Cicilan Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <!-- Nama/Deskripsi -->
                    <div class="form-group">
                        <label for="inst_description">Nama / Keterangan Cicilan <span class="text-danger">*</span></label>
                        <input type="text" name="description" id="inst_description" class="form-control"
                            placeholder="Contoh: Tiket BTH-JKT, Admin Convert, ..." required>
                    </div>

                    <!-- Sumber Cicilan -->
                    <div class="form-group">
                        <label>Sumber Cicilan <span class="text-danger">*</span></label>
                        <div class="d-flex">
                            <div class="custom-control custom-radio mr-4">
                                <input type="radio" id="src_loan" name="source_type" value="member_loan"
                                    class="custom-control-input" required>
                                <label class="custom-control-label" for="src_loan">
                                    <i class="fas fa-users mr-1 text-primary"></i> Pinjaman Anggota
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="src_cc" name="source_type" value="credit_card"
                                    class="custom-control-input">
                                <label class="custom-control-label" for="src_cc">
                                    <i class="fas fa-user mr-1 text-success"></i> Pinjaman Pribadi
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown Lender (hanya muncul jika member_loan) -->
                    <div class="form-group" id="lenderWrapper" style="display:none;">
                        <label for="inst_lender">Pemberi Pinjaman <span class="text-danger">*</span></label>
                        <select name="lender_user_id" id="inst_lender" class="form-control">
                            <option value="">-- Pilih Anggota --</option>
                            <?php foreach ($groupMembers as $m) : ?>
                                <option value="<?= $m['user_id'] ?>" <?= (int)$m['user_id'] === (int)user_id() ? 'selected' : '' ?>>
                                    <?= esc($m['username']) ?> <?= (int)$m['user_id'] === (int)user_id() ? '(Saya)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Peminjam / Penerima Pinjaman (hanya muncul jika member_loan) -->
                    <div class="form-group" id="borrowerWrapper" style="display:none;">
                        <label class="font-weight-bold">Penerima Pinjaman / Peminjam <span class="text-danger">*</span></label>
                        <div class="d-flex mb-2">
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-2" id="btnSelectAllBorrowers">Pilih Semua</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" id="btnClearBorrowers">Kosongkan</button>
                        </div>
                        <div class="row pl-2">
                            <?php foreach ($groupMembers as $m) : ?>
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="borrowers[]" value="<?= $m['user_id'] ?>" 
                                            class="custom-control-input borrower-cb" id="borrower_cb_<?= $m['user_id'] ?>"
                                            <?= (int)$m['user_id'] !== (int)user_id() ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="borrower_cb_<?= $m['user_id'] ?>">
                                            <?= esc($m['username']) ?> <?= (int)$m['user_id'] === (int)user_id() ? '(Saya)' : '' ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pilih siapa saja anggota yang ikut berhutang. Catatan: Peminjam yang sama dengan Pemberi Pinjaman akan otomatis dicatat sebagai Pinjaman Pribadi.
                        </small>
                    </div>

                    <!-- Metode Pembagian (hanya muncul jika member_loan) -->
                    <div class="form-group" id="splitTypeWrapper" style="display:none;">
                        <label>Metode Pembagian Jumlah <span class="text-danger">*</span></label>
                        <div class="d-flex">
                            <div class="custom-control custom-radio mr-4">
                                <input type="radio" id="split_equal" name="split_type" value="equal"
                                    class="custom-control-input" checked>
                                <label class="custom-control-label" for="split_equal">
                                    Bagi Rata ke Seluruh Peminjam yang Dipilih
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="split_individual" name="split_type" value="individual"
                                    class="custom-control-input">
                                <label class="custom-control-label" for="split_individual">
                                    Nominal Sama per Orang
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Mode Kalkulasi -->
                    <div class="form-group">
                        <label>Mode Kalkulasi <span class="text-danger">*</span></label>
                        <div class="d-flex">
                            <div class="custom-control custom-radio mr-4">
                                <input type="radio" id="mode_total" name="calc_mode" value="total_months"
                                    class="custom-control-input" checked>
                                <label class="custom-control-label" for="mode_total">Total + Jumlah Bulan</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="mode_monthly" name="calc_mode" value="monthly_duration"
                                    class="custom-control-input">
                                <label class="custom-control-label" for="mode_monthly">Per Bulan + Durasi</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Total (muncul di mode total_months) -->
                        <div class="col-md-4" id="inputTotalWrapper">
                            <div class="form-group">
                                <label for="inst_total">Total Pinjaman (Rp)</label>
                                <input type="number" name="total_amount" id="inst_total" class="form-control"
                                    placeholder="3263675" min="1">
                            </div>
                        </div>

                        <!-- Per Bulan (muncul di mode monthly_duration) -->
                        <div class="col-md-4" id="inputMonthlyWrapper" style="display:none;">
                            <div class="form-group">
                                <label for="inst_monthly">Cicilan per Bulan (Rp)</label>
                                <input type="number" name="monthly_amount" id="inst_monthly" class="form-control"
                                    placeholder="543946" min="1">
                            </div>
                        </div>

                        <!-- Durasi -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inst_months">Durasi (Bulan) <span class="text-danger">*</span></label>
                                <input type="number" name="installment_months" id="inst_months" class="form-control"
                                    placeholder="6" min="1" max="120" required>
                            </div>
                        </div>

                        <!-- Mulai Bulan -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inst_start">Mulai Bulan <span class="text-danger">*</span></label>
                                <input type="month" name="start_date" id="inst_start" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Hasil kalkulasi -->
                    <div class="alert alert-light border" id="calcResult" style="display:none;">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-muted small">Total Pinjaman</div>
                                <div class="font-weight-bold text-primary" id="calcTotal">—</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Per Bulan</div>
                                <div class="font-weight-bold text-success" id="calcMonthly">—</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Durasi</div>
                                <div class="font-weight-bold" id="calcMonths">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Tabel Simulasi -->
                    <div id="simulasiWrapper" style="display:none;">
                        <label class="font-weight-bold"><i class="fas fa-table mr-1 text-info"></i> Preview Jadwal Cicilan</label>
                        <div class="table-responsive" style="max-height: 200px; overflow-y:auto;">
                            <table class="table table-sm table-bordered table-striped" id="simulasiTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Bulan</th>
                                        <th class="text-right">Tagihan</th>
                                    </tr>
                                </thead>
                                <tbody id="simulasiBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="form-group mt-3">
                        <label for="inst_note">Catatan (opsional)</label>
                        <input type="text" name="note" id="inst_note" class="form-control"
                            placeholder="Keterangan tambahan...">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanCicilan">
                        <i class="fas fa-save mr-1"></i> Simpan Cicilan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== MODAL: Edit Cicilan ===================== -->
<div class="modal fade" id="modalEditCicilan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="" id="formEditCicilan">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Cicilan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <!-- Warning Alert if paid months exist -->
                    <div class="alert alert-warning" id="editPaidWarning" style="display:none;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Beberapa bulan sudah dibayar. Anda hanya diperbolehkan mengubah <strong>Nama / Keterangan</strong> dan <strong>Catatan</strong> untuk menjaga konsistensi keuangan.
                    </div>

                    <!-- Nama/Deskripsi -->
                    <div class="form-group">
                        <label for="edit_inst_description">Nama / Keterangan Cicilan <span class="text-danger">*</span></label>
                        <input type="text" name="description" id="edit_inst_description" class="form-control" required>
                    </div>

                    <div id="editStructuralFields">
                        <!-- Sumber Cicilan -->
                        <div class="form-group">
                            <label>Sumber Cicilan <span class="text-danger">*</span></label>
                            <div class="d-flex">
                                <div class="custom-control custom-radio mr-4">
                                    <input type="radio" id="edit_src_loan" name="source_type" value="member_loan" class="custom-control-input" required>
                                    <label class="custom-control-label" for="edit_src_loan">
                                        <i class="fas fa-users mr-1 text-primary"></i> Pinjaman Anggota
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="edit_src_cc" name="source_type" value="credit_card" class="custom-control-input">
                                    <label class="custom-control-label" for="edit_src_cc">
                                        <i class="fas fa-user mr-1 text-success"></i> Pinjaman Pribadi
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Lender -->
                        <div class="form-group" id="editLenderWrapper" style="display:none;">
                            <label for="edit_inst_lender">Pemberi Pinjaman <span class="text-danger">*</span></label>
                            <select name="lender_user_id" id="edit_inst_lender" class="form-control">
                                <option value="">-- Pilih Anggota --</option>
                                <?php foreach ($groupMembers as $m) : ?>
                                    <option value="<?= $m['user_id'] ?>">
                                        <?= esc($m['username']) ?> <?= (int)$m['user_id'] === (int)user_id() ? '(Saya)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Cara Input -->
                        <div class="form-group">
                            <label>Cara Input Nilai <span class="text-danger">*</span></label>
                            <div class="d-flex">
                                <div class="custom-control custom-radio mr-4">
                                    <input type="radio" id="edit_mode_total" name="calc_mode" value="total_months" class="custom-control-input" checked required>
                                    <label class="custom-control-label" for="edit_mode_total">Nominal Total</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="edit_mode_monthly" name="calc_mode" value="monthly_duration" class="custom-control-input">
                                    <label class="custom-control-label" for="edit_mode_monthly">Nominal Bulanan</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Nominal Total -->
                            <div class="col-md-6 form-group" id="editInputTotalWrapper">
                                <label for="edit_inst_total">Jumlah Total Pinjaman <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                    <input type="number" name="total_amount" id="edit_inst_total" class="form-control" placeholder="0" required>
                                </div>
                            </div>

                            <!-- Nominal Bulanan -->
                            <div class="col-md-6 form-group" id="editInputMonthlyWrapper" style="display:none;">
                                <label for="edit_inst_monthly">Jumlah Cicilan / Bulan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                    <input type="number" name="monthly_amount" id="edit_inst_monthly" class="form-control" placeholder="0">
                                </div>
                            </div>

                            <!-- Durasi -->
                            <div class="col-md-6 form-group">
                                <label for="edit_inst_months">Tenor / Durasi (Bulan) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="installment_months" id="edit_inst_months" class="form-control" min="1" placeholder="Misal: 6" required>
                                    <div class="input-group-append"><span class="input-group-text">Bulan</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Mulai Bulan -->
                        <div class="form-group">
                            <label for="edit_inst_start">Mulai Bulan <span class="text-danger">*</span></label>
                            <input type="month" name="start_date" id="edit_inst_start" class="form-control" required>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="form-group">
                        <label for="edit_inst_note">Catatan (opsional)</label>
                        <textarea name="note" id="edit_inst_note" class="form-control" rows="2" placeholder="Detail belanja atau info tambahan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== MODAL: Bayar Cicilan (Loan ke Anggota) ===================== -->
<div class="modal fade" id="modalBayarLoan" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= base_url('backend/installments/pay-group') ?>"
                  enctype="multipart/form-data" id="formBayarLoan">
                <?= csrf_field() ?>
                <input type="hidden" name="trip_id" id="pay_trip_id" value="">
                <input type="hidden" name="lender_user_id" id="pay_lender_id" value="">
                <input type="hidden" name="borrower_user_id" id="pay_borrower_id" value="">
                <input type="hidden" name="due_month" id="pay_due_month" value="">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-paper-plane mr-2"></i>Bayar Cicilan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-light border mb-3" id="payLoanInfo">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Kepada</div>
                                <strong id="payLoanLenderName">—</strong>
                            </div>
                            <div class="text-right">
                                <div class="text-muted small">Bulan</div>
                                <strong id="payLoanMonth">—</strong>
                            </div>
                            <div class="text-right">
                                <div class="text-muted small">Total Transfer</div>
                                <strong class="text-primary" id="payLoanTotal">—</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Transfer -->
                    <div class="form-group">
                        <label for="pay_paid_at_loan">Tanggal Transfer <span class="text-danger">*</span></label>
                        <input type="date" name="paid_at" id="pay_paid_at_loan" class="form-control" required>
                    </div>

                    <!-- Bukti Transfer (WAJIB) -->
                    <div class="form-group">
                        <label for="pay_proof_loan">
                            <i class="fas fa-paperclip mr-1"></i> Bukti Transfer
                            <span class="text-danger">*</span>
                            <span class="badge badge-danger ml-1" style="font-size:0.7rem;">Wajib</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" name="proof_image" id="pay_proof_loan"
                                class="custom-file-input" accept="image/*" required>
                            <label class="custom-file-label" for="pay_proof_loan">Pilih foto bukti transfer...</label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Upload screenshot bukti transfer ke rekening <span id="payLoanLenderNameHint">lender</span>
                        </small>
                        <!-- Preview -->
                        <img id="previewProofLoan" src="#" alt="Preview" class="img-thumbnail mt-2"
                            style="max-height:150px; display:none;">
                    </div>

                    <!-- Catatan -->
                    <div class="form-group">
                        <label for="pay_note_loan">Catatan (opsional)</label>
                        <input type="text" name="note" id="pay_note_loan" class="form-control"
                            placeholder="Contoh: Transfer via BCA...">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check mr-1"></i> Konfirmasi Bayar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== MODAL: Bayar Tagihan CC Pribadi ===================== -->
<div class="modal fade" id="modalBayarCC" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= base_url('backend/installments/mark-self-paid') ?>"
                  enctype="multipart/form-data" id="formBayarCC">
                <?= csrf_field() ?>
                <input type="hidden" name="trip_id" id="paycc_trip_id" value="">
                <input type="hidden" name="borrower_user_id" id="paycc_borrower_id" value="">
                <input type="hidden" name="due_month" id="paycc_due_month" value="">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-user mr-2"></i>Bayar Tagihan Pribadi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Pinjaman Pribadi</div>
                                <strong>Tagihan Bulan <span id="payCCMonth">—</span></strong>
                            </div>
                            <div class="text-right">
                                <div class="text-muted small">Total Tagihan</div>
                                <strong class="text-success" id="payCCTotal">—</strong>
                            </div>
                        </div>
                        <hr class="my-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Tidak ada transfer ke pihak lain. Ini mencatat bukti bahwa tagihan pribadi / lainnya sudah dibayar.
                        </small>
                    </div>

                    <!-- Tanggal Bayar -->
                    <div class="form-group">
                        <label for="paycc_paid_at">Tanggal Bayar <span class="text-danger">*</span></label>
                        <input type="date" name="paid_at" id="paycc_paid_at" class="form-control" required>
                    </div>

                    <!-- Bukti Bayar Pribadi (WAJIB) -->
                    <div class="form-group">
                        <label for="paycc_proof">
                            <i class="fas fa-paperclip mr-1"></i> Bukti Pembayaran
                            <span class="text-danger">*</span>
                            <span class="badge badge-danger ml-1" style="font-size:0.7rem;">Wajib</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" name="proof_image" id="paycc_proof"
                                class="custom-file-input" accept="image/*" required>
                            <label class="custom-file-label" for="paycc_proof">Pilih foto / screenshot...</label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-lightbulb mr-1 text-warning"></i>
                            Contoh: screenshot app bank, foto struk transfer / nota
                        </small>
                        <img id="previewProofCC" src="#" alt="Preview" class="img-thumbnail mt-2"
                            style="max-height:150px; display:none;">
                    </div>

                    <!-- Catatan -->
                    <div class="form-group">
                        <label for="paycc_note">Catatan (opsional)</label>
                        <input type="text" name="note" id="paycc_note" class="form-control"
                            placeholder="Contoh: Tagihan BCA Jun'26...">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Konfirmasi Bayar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    // ---- localStorage Filter Persistence ----
    var STORAGE_KEYS = {
        tripId: 'inst_selected_trip_id',
        month: 'inst_filter_month',
        trip: 'inst_filter_trip',
        lender: 'inst_filter_lender',
        status: 'inst_filter_status'
    };

    var currentUrl = new URL(window.location.href);
    var urlTripId = currentUrl.searchParams.get('trip_id');

    // Handle Reset
    if (currentUrl.searchParams.has('reset')) {
        localStorage.removeItem(STORAGE_KEYS.tripId);
        localStorage.removeItem(STORAGE_KEYS.month);
        localStorage.removeItem(STORAGE_KEYS.trip);
        localStorage.removeItem(STORAGE_KEYS.lender);
        localStorage.removeItem(STORAGE_KEYS.status);
    } else {
        // Redirect if URL trip_id is empty but we have one saved in localStorage
        var savedTripId = localStorage.getItem(STORAGE_KEYS.tripId);
        if (urlTripId === null && savedTripId) {
            currentUrl.searchParams.set('trip_id', savedTripId);
            window.location.href = currentUrl.toString();
            return;
        }

        // Save selected trip_id if present in URL
        if (urlTripId) {
            localStorage.setItem(STORAGE_KEYS.tripId, urlTripId);
        }
    }

    // Populate client-side filters from localStorage
    if (!currentUrl.searchParams.has('reset')) {
        var savedMonth = localStorage.getItem(STORAGE_KEYS.month);
        var savedTrip = localStorage.getItem(STORAGE_KEYS.trip);
        var savedLender = localStorage.getItem(STORAGE_KEYS.lender);
        var savedStatus = localStorage.getItem(STORAGE_KEYS.status);

        if (savedMonth !== null) $('#filterSummaryMonth').val(savedMonth);
        if (savedLender !== null) $('#filterSummaryLender').val(savedLender);
        
        if (savedStatus !== null) {
            $('#filterSummaryStatus').val(savedStatus);
        } else {
            $('#filterSummaryStatus').val('unpaid');
        }

        // Only restore trip filter if no trip_id is in the URL
        if (urlTripId === null && savedTrip !== null) {
            $('#filterSummaryTrip').val(savedTrip);
        }
    }

    // ---- Helpers ----
    function formatRp(n) {
        return 'Rp ' + parseInt(n).toLocaleString('id-ID');
    }

    // ---- Source type toggle ----
    $('#modalTambahCicilan input[name="source_type"]').on('change', function () {
        if ($(this).val() === 'member_loan') {
            $('#lenderWrapper').show();
            $('#borrowerWrapper').show();
            $('#splitTypeWrapper').show();
            $('#inst_lender').prop('required', true);
            $('.borrower-cb').prop('disabled', false);
        } else {
            $('#lenderWrapper').hide();
            $('#borrowerWrapper').hide();
            $('#splitTypeWrapper').hide();
            $('#inst_lender').prop('required', false).val('');
            $('.borrower-cb').prop('disabled', true);
        }
        runSimulate();
    });

    // ---- Borrowers select toggles ----
    $('#btnSelectAllBorrowers').on('click', function() {
        $('.borrower-cb').prop('checked', true);
        runSimulate();
    });
    $('#btnClearBorrowers').on('click', function() {
        $('.borrower-cb').prop('checked', false);
        runSimulate();
    });

    // ---- Calc mode toggle ----
    $('#modalTambahCicilan input[name="calc_mode"]').on('change', function () {
        if ($(this).val() === 'total_months') {
            $('#inputTotalWrapper').show();
            $('#inputMonthlyWrapper').hide();
            $('#inst_total').prop('required', true);
            $('#inst_monthly').prop('required', false).val('');
        } else {
            $('#inputTotalWrapper').hide();
            $('#inputMonthlyWrapper').show();
            $('#inst_monthly').prop('required', true);
            $('#inst_total').prop('required', false).val('');
        }
        runSimulate();
    });

    // ---- Auto simulate on input change ----
    var simTimer;
    function runSimulate() {
        clearTimeout(simTimer);
        simTimer = setTimeout(doSimulate, 500);
    }

    $('#modalTambahCicilan #inst_total, #modalTambahCicilan #inst_monthly, #modalTambahCicilan #inst_months, #modalTambahCicilan #inst_start, #modalTambahCicilan .borrower-cb, #modalTambahCicilan input[name="split_type"]').on('input change', runSimulate);

    function doSimulate() {
        var mode    = $('#modalTambahCicilan input[name="calc_mode"]:checked').val();
        var months  = parseInt($('#inst_months').val()) || 0;
        var startRaw = $('#inst_start').val();
        var total   = parseInt($('#inst_total').val()) || 0;
        var monthly = parseInt($('#inst_monthly').val()) || 0;

        var sourceType = $('#modalTambahCicilan input[name="source_type"]:checked').val();
        var splitType = $('#modalTambahCicilan input[name="split_type"]:checked').val();
        var checkedBorrowers = $('.borrower-cb:checked').length || 1;

        if (sourceType === 'member_loan' && splitType === 'equal') {
            total = Math.round(total / checkedBorrowers);
            monthly = Math.round(monthly / checkedBorrowers);
        }

        if (months <= 0 || !startRaw) {
            $('#simulasiWrapper').hide();
            $('#calcResult').hide();
            return;
        }

        // Start date — convert from YYYY-MM to YYYY-MM-01
        var startDate = startRaw + '-01';

        $.ajax({
            url: '<?= base_url('backend/installments/simulate') ?>',
            data: {
                start_date: startDate,
                months:     months,
                total:      mode === 'total_months' ? total : 0,
                monthly:    mode === 'monthly_duration' ? monthly : 0
            },
            success: function (res) {
                if (res.error) return;

                $('#calcTotal').text(formatRp(res.total));
                $('#calcMonthly').text(formatRp(res.monthly));
                $('#calcMonths').text(res.months + ' bulan');
                $('#calcResult').show();

                var html = '';
                $.each(res.schedule, function (i, row) {
                    html += '<tr><td>' + (i + 1) + '</td><td>' + row.due_label + '</td><td class="text-right">' + formatRp(row.due_amount) + '</td></tr>';
                });
                $('#simulasiBody').html(html);
                $('#simulasiWrapper').show();
            }
        });
    }

    // ---- Custom file label update ----
    function fileLabel(inputId, labelSelector) {
        $(inputId).on('change', function () {
            var name = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').text(name || 'Pilih file...');
        });
    }
    fileLabel('#pay_proof_loan', null);
    fileLabel('#paycc_proof', null);

    // ---- Image preview ----
    function imagePreview(inputId, previewId) {
        $(inputId).on('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $(previewId).attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            } else {
                $(previewId).hide();
            }
        });
    }
    imagePreview('#pay_proof_loan', '#previewProofLoan');
    imagePreview('#paycc_proof', '#previewProofCC');

    // ---- Tombol Bayar bulan ----
    $(document).on('click', '.btn-pay-month', function () {
        var source   = $(this).data('source');
        var trip     = $(this).data('trip');
        var lender   = $(this).data('lender');
        var borrower = $(this).data('borrower');
        var month    = $(this).data('month');
        var total    = $(this).data('total');
        var label    = $(this).data('label');
        var lenderName = $(this).data('lender-name');
        var today    = new Date().toISOString().split('T')[0];

        if (source === 'member_loan') {
            $('#pay_trip_id').val(trip);
            $('#pay_lender_id').val(lender);
            $('#pay_borrower_id').val(borrower);
            $('#pay_due_month').val(month);
            $('#payLoanLenderName').text(lenderName);
            $('#payLoanLenderNameHint').text(lenderName);
            $('#payLoanMonth').text(label);
            $('#payLoanTotal').text(formatRp(total));
            $('#pay_paid_at_loan').val(today);
            $('#previewProofLoan').hide();
            $('#pay_proof_loan').val('').siblings('.custom-file-label').text('Pilih foto bukti transfer...');
            $('#modalBayarLoan').modal('show');
        } else {
            $('#paycc_trip_id').val(trip);
            $('#paycc_borrower_id').val(borrower);
            $('#paycc_due_month').val(month);
            $('#payCCMonth').text(label);
            $('#payCCTotal').text(formatRp(total));
            $('#paycc_paid_at').val(today);
            $('#previewProofCC').hide();
            $('#paycc_proof').val('').siblings('.custom-file-label').text('Pilih foto / screenshot...');
            $('#modalBayarCC').modal('show');
        }
    });

    // ---- Edit Cicilan ----
    $(document).on('click', '.btn-edit-inst', function () {
        var id        = $(this).data('id');
        var desc      = $(this).data('desc');
        var source    = $(this).data('source');
        var lender    = $(this).data('lender');
        var total     = $(this).data('total');
        var monthly   = $(this).data('monthly');
        var months    = $(this).data('months');
        var start     = $(this).data('start');
        var note      = $(this).data('note');
        var paidCount = parseInt($(this).data('paid-count')) || 0;

        // Set action form
        $('#formEditCicilan').attr('action', '<?= base_url('backend/installments/update/') ?>' + id);

        // Populate values
        $('#edit_inst_description').val(desc);
        $('#edit_inst_note').val(note);

        if (paidCount > 0) {
            // Sembunyikan field struktural jika ada yang terbayar
            $('#editStructuralFields').hide();
            $('#editPaidWarning').show();
            // Nonaktifkan input agar tidak di-validate HTML5
            $('#editStructuralFields').find('input, select').prop('disabled', true);
        } else {
            // Tampilkan field struktural & aktifkan kembali
            $('#editStructuralFields').show();
            $('#editPaidWarning').hide();
            $('#editStructuralFields').find('input, select').prop('disabled', false);

            // Set values untuk field struktural
            if (source === 'member_loan') {
                $('#edit_src_loan').prop('checked', true);
                $('#editLenderWrapper').show();
                $('#edit_inst_lender').val(lender).prop('required', true);
            } else {
                $('#edit_src_cc').prop('checked', true);
                $('#editLenderWrapper').hide();
                $('#edit_inst_lender').val('').prop('required', false);
            }

            // Set nominal & durasi
            $('#edit_inst_total').val(total);
            $('#edit_inst_monthly').val(monthly);
            $('#edit_inst_months').val(months);
            $('#edit_inst_start').val(start);

            // Default ke nominal total
            $('#edit_mode_total').prop('checked', true);
            $('#editInputTotalWrapper').show();
            $('#editInputMonthlyWrapper').hide();
            $('#edit_inst_total').prop('required', true);
            $('#edit_inst_monthly').prop('required', false);
        }

        $('#modalEditCicilan').modal('show');
    });

    // Toggle Sumber di Edit Modal
    $('#modalEditCicilan input[name="source_type"]').on('change', function () {
        if ($(this).val() === 'member_loan') {
            $('#editLenderWrapper').show();
            $('#edit_inst_lender').prop('required', true);
        } else {
            $('#editLenderWrapper').hide();
            $('#edit_inst_lender').prop('required', false).val('');
        }
    });

    // Toggle Cara Input di Edit Modal
    $('#modalEditCicilan input[name="calc_mode"]').on('change', function () {
        if ($(this).val() === 'total_months') {
            $('#editInputTotalWrapper').show();
            $('#editInputMonthlyWrapper').hide();
            $('#edit_inst_total').prop('required', true);
            $('#edit_inst_monthly').prop('required', false).val('');
        } else {
            $('#editInputTotalWrapper').hide();
            $('#editInputMonthlyWrapper').show();
            $('#edit_inst_monthly').prop('required', true);
            $('#edit_inst_total').prop('required', false).val('');
        }
        runEditSimulate();
    });

    // Auto-Simulasi di Edit Modal
    var editSimTimer;
    function runEditSimulate() {
        clearTimeout(editSimTimer);
        editSimTimer = setTimeout(doEditSimulate, 300);
    }

    $('#modalEditCicilan #edit_inst_total, #modalEditCicilan #edit_inst_monthly, #modalEditCicilan #edit_inst_months').on('input change', runEditSimulate);

    function doEditSimulate() {
        var mode    = $('#modalEditCicilan input[name="calc_mode"]:checked').val();
        var months  = parseInt($('#edit_inst_months').val()) || 0;
        var total   = parseInt($('#edit_inst_total').val()) || 0;
        var monthly = parseInt($('#edit_inst_monthly').val()) || 0;

        if (months > 0) {
            if (mode === 'total_months') {
                monthly = Math.round(total / months);
                $('#edit_inst_monthly').val(monthly);
            } else {
                total = monthly * months;
                $('#edit_inst_total').val(total);
            }
        }
    }

    // ---- Hapus Cicilan ----
    $(document).on('click', '.btn-delete-inst', function () {
        var id   = $(this).data('id');
        var desc = $(this).data('desc');
        var deleteUrl = '<?= base_url('backend/installments/delete/') ?>' + id;

        Swal.fire({
            title: 'Hapus Cicilan?',
            html: `Apakah Anda yakin ingin menghapus cicilan <strong>"${desc}"</strong>?<br><br>` +
                  `<span class="text-danger small"><i class="fas fa-exclamation-triangle mr-1"></i> Tindakan ini akan menghapus seluruh data pembayaran dan berkas bukti transfer terkait secara permanen!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus Permanen!',
            cancelButtonText: 'Batal',
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                var form = $('<form>', {
                    'method': 'POST',
                    'action': deleteUrl
                });
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '<?= csrf_token() ?>',
                    'value': '<?= csrf_hash() ?>'
                }));
                $('body').append(form);
                form.submit();
            }
        });
    });

    // ---- Lihat Bukti Transfer ----
    $(document).on('click', '.btn-view-proof', function () {
        var imgSrc = $(this).data('image');
        $('#modalViewImageTarget').attr('src', imgSrc);
        $('#modalViewImage').modal('show');
    });

    // ---- Default tanggal start bulan ini ----
    var now = new Date();
    var ym = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    $('#inst_start').val(ym);

    // ---- Filter & Collapse Ringkasan Tagihan Bulanan (Client-side) ----
    function updateSummaryTotal() {
        var grandTotal = 0;
        
        // Loop through each parent row
        $('.month-parent-row').each(function() {
            var monthKey = $(this).data('month-key');
            var matchingChildren = $('.month-child-row.month-detail-' + monthKey).not('.filtered-out');
            
            if (matchingChildren.length > 0) {
                $(this).show();
                // Recalculate parent total
                var parentTotal = 0;
                matchingChildren.each(function() {
                    parentTotal += parseInt($(this).data('amount')) || 0;
                });
                
                $(this).find('.parent-amount').text(formatRp(parentTotal));
                $(this).find('.parent-count').text(matchingChildren.length + ' tagihan');
                grandTotal += parentTotal;
            } else {
                $(this).hide();
            }
        });
        
        $('#summaryGrandTotal').text(formatRp(grandTotal));
    }

    // Toggle child rows on parent click
    $(document).on('click', '.month-parent-row', function() {
        var monthKey = $(this).data('month-key');
        var children = $('.month-detail-' + monthKey).not('.filtered-out');
        var icon = $(this).find('.expand-icon-month');
        
        if ($(this).hasClass('expanded')) {
            $(this).removeClass('expanded');
            $('.month-detail-' + monthKey).slideUp(200);
            icon.css('transform', 'rotate(0deg)');
        } else {
            $(this).addClass('expanded');
            children.slideDown(200);
            icon.css('transform', 'rotate(90deg)');
        }
    });

    // ---- Filter & Recalculate Projection Table ----
    function updateProjectionTable() {
        var grandTotal = 0;
        var monthTotals = {};
        
        // Initialize monthly totals
        $('.total-cell').each(function() {
            var colDate = $(this).data('month-col');
            monthTotals[colDate] = 0;
        });
        
        // Sum visible rows
        $('.projection-row:visible').each(function() {
            var amt = parseInt($(this).data('total-amount')) || 0;
            grandTotal += amt;
            
            $(this).find('.val-cell').each(function() {
                var colDate = $(this).data('month-col');
                var cellAmt = parseInt($(this).data('amount')) || 0;
                monthTotals[colDate] += cellAmt;
            });
        });
        
        // Update footer grand total
        $('#projectionGrandTotal').text(formatRp(grandTotal));
        
        // Update footer month cells
        $('.total-cell').each(function() {
            var colDate = $(this).data('month-col');
            var total = monthTotals[colDate] || 0;
            $(this).text(total > 0 ? formatRp(total) : '—');
        });

        // Filter unpaid payment buttons in projection footer
        var selectedTrip   = $('#filterSummaryTrip').val().toLowerCase();
        var selectedLender = $('#filterSummaryLender').val().toLowerCase();

        $('.unpaid-btn-wrapper').each(function() {
            var btnTrip = $(this).data('trip').toLowerCase();
            var btnLender = $(this).data('lender').toLowerCase();
            
            var matchTrip   = !selectedTrip || btnTrip.indexOf(selectedTrip) !== -1;
            var matchLender = !selectedLender || btnLender.indexOf(selectedLender) !== -1;
            
            if (matchTrip && matchLender) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Update action cell statuses (Lunas, - or Buttons)
        $('.action-cell').each(function() {
            var colDate = $(this).data('month-col');
            var visibleWrappers = $(this).find('.unpaid-btn-wrapper').filter(function() {
                return $(this).css('display') !== 'none';
            });
            
            // Check if there are visible cells in this month column
            var hasPayments = false;
            $('.projection-row:visible').each(function() {
                var amt = parseInt($(this).find('.val-cell[data-month-col="' + colDate + '"]').data('amount')) || 0;
                if (amt > 0) {
                    hasPayments = true;
                }
            });
            
            if (visibleWrappers.length > 0) {
                $(this).find('.lunas-badge, .na-badge').hide();
            } else if (hasPayments) {
                $(this).find('.lunas-badge').show();
                $(this).find('.na-badge').hide();
            } else {
                $(this).find('.lunas-badge').hide();
                $(this).find('.na-badge').show();
            }
        });
    }

    function updateMonthColumnsVisibility() {
        var showAll = $('#togglePaidMonths').is(':checked');
        
        // Loop through each month column key
        $('.total-cell').each(function() {
            var colDate = $(this).data('month-col');
            
            // Check if this month has any visible unpaid button wrapper in the actions row
            var hasUnpaid = $('.unpaid-btn-wrapper[data-month-col="' + colDate + '"]:visible').length > 0;
            
            // Check if there are any visible rows with unpaid cells in this column
            var hasUnpaidCell = false;
            $('.projection-row:visible').each(function() {
                var valCell = $(this).find('.val-cell[data-month-col="' + colDate + '"]');
                var amt = parseInt(valCell.data('amount')) || 0;
                var isPaid = valCell.find('.fa-check-circle').length > 0;
                if (amt > 0 && !isPaid) {
                    hasUnpaidCell = true;
                }
            });

            var shouldHide = !showAll && !hasUnpaid && !hasUnpaidCell;
            
            if (shouldHide) {
                $('th[data-month-col="' + colDate + '"]').hide();
                $('.val-cell[data-month-col="' + colDate + '"]').hide();
                $('.total-cell[data-month-col="' + colDate + '"]').hide();
                $('.action-cell[data-month-col="' + colDate + '"]').hide();
            } else {
                $('th[data-month-col="' + colDate + '"]').show();
                $('.val-cell[data-month-col="' + colDate + '"]').show();
                $('.total-cell[data-month-col="' + colDate + '"]').show();
                $('.action-cell[data-month-col="' + colDate + '"]').show();
            }
        });
    }

    // Trigger visibility update on switch change
    $('#togglePaidMonths').on('change', function() {
        updateMonthColumnsVisibility();
    });

    $('#filterSummaryMonth, #filterSummaryTrip, #filterSummaryLender, #filterSummaryStatus').on('change', function() {
        var selectedMonth  = $('#filterSummaryMonth').val();
        var selectedTrip   = $('#filterSummaryTrip').val().toLowerCase();
        var selectedLender = $('#filterSummaryLender').val().toLowerCase();
        var selectedStatus = $('#filterSummaryStatus').val();

        // Simpan saringan ke localStorage
        localStorage.setItem(STORAGE_KEYS.month, $('#filterSummaryMonth').val() || '');
        localStorage.setItem(STORAGE_KEYS.trip, $('#filterSummaryTrip').val() || '');
        localStorage.setItem(STORAGE_KEYS.lender, $('#filterSummaryLender').val() || '');
        localStorage.setItem(STORAGE_KEYS.status, $('#filterSummaryStatus').val() || '');

        // Sinkronisasi dengan dropdown `#tripSelect` utama di atas
        var selectedTripText = $('#filterSummaryTrip').val();
        var $resetBtn = $('#filterForm').find('.btn-outline-secondary');
        
        if (selectedTripText) {
            var found = false;
            $('#tripSelect option').each(function() {
                var optText = $(this).text();
                if (optText.indexOf(selectedTripText) !== -1) {
                    var tripIdVal = $(this).val();
                    $('#tripSelect').val(tripIdVal);
                    localStorage.setItem(STORAGE_KEYS.tripId, tripIdVal);
                    
                    // Munculkan tombol Tambah Cicilan & perbarui input form trip_id
                    $('#btnTambahCicilan').show();
                    $('input[name="trip_id"]').val(tripIdVal);
                    
                    // Munculkan tombol reset di form filter
                    if ($resetBtn.length === 0) {
                        $('#filterForm').append('<a href="<?= base_url('backend/installments?reset') ?>" class="btn btn-outline-secondary btn-sm ml-1" id="dynamicResetBtn"><i class="fas fa-times"></i> Reset</a>');
                    } else {
                        $resetBtn.show();
                    }
                    
                    found = true;
                    return false; // break loop
                }
            });
            if (!found) {
                $('#tripSelect').val('');
                localStorage.removeItem(STORAGE_KEYS.tripId);
                $('#btnTambahCicilan').hide();
                $resetBtn.hide();
                $('#dynamicResetBtn').remove();
            }
        } else {
            $('#tripSelect').val('');
            localStorage.removeItem(STORAGE_KEYS.tripId);
            $('#btnTambahCicilan').hide();
            $resetBtn.hide();
            $('#dynamicResetBtn').remove();
        }

        // 1. Filter main collapsible summary table
        $('.month-child-row').each(function() {
            var rowMonth  = $(this).data('month');
            var rowTrip   = $(this).data('trip').toLowerCase();
            var rowLender = $(this).data('lender').toLowerCase();
            var rowStatus = $(this).data('status');

            var matchMonth  = !selectedMonth || rowMonth === selectedMonth;
            var matchTrip   = !selectedTrip || rowTrip === selectedTrip;
            var matchLender = !selectedLender || rowLender === selectedLender;
            var matchStatus = !selectedStatus || rowStatus === selectedStatus;

            var $parentRow = $(this).closest('tbody').find('.month-parent-row[data-month-key="' + rowMonth + '"]');

            if (matchMonth && matchTrip && matchLender && matchStatus) {
                $(this).removeClass('filtered-out');
                if ($parentRow.hasClass('expanded')) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            } else {
                $(this).addClass('filtered-out').hide();
            }
        });

        // 2. Filter projection rows
        $('.projection-row').each(function() {
            var rowTrip   = $(this).data('trip').toLowerCase();
            var rowLender = $(this).data('lender').toLowerCase();
            var rowStatus = $(this).data('status-row');

            var matchTrip   = !selectedTrip || rowTrip.indexOf(selectedTrip) !== -1;
            var matchLender = !selectedLender || rowLender.indexOf(selectedLender) !== -1;

            var matchStatus = true;
            if (selectedStatus === 'paid') {
                matchStatus = (rowStatus === 'completed');
            } else if (selectedStatus === 'unpaid') {
                matchStatus = (rowStatus === 'active');
            }

            if (matchTrip && matchLender && matchStatus) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        updateSummaryTotal();
        updateProjectionTable();
        updateMonthColumnsVisibility();
    });

    // Jalankan filter default status (Belum Lunas) pada saat page load
    $('#filterSummaryStatus').trigger('change');

    // ---- SweetAlert2 Notifications ----
    <?php if (session()->getFlashdata('success')) : ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= esc(session()->getFlashdata('success')) ?>',
            timer: 3000,
            showConfirmButton: false
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= esc(session()->getFlashdata('error')) ?>'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')) : ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            html: '<ul class="text-left mb-0" style="list-style-type: none; padding-left: 0;"><?php foreach (session()->getFlashdata('errors') as $err) : ?><li><i class="fas fa-exclamation-circle text-danger mr-2"></i><?= esc($err) ?></li><?php endforeach; ?></ul>'
        });
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>
