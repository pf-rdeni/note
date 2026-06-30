<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>


<!-- Page Header -->
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-credit-card mr-2 text-primary"></i>Cicilan</h1>
        <p class="text-muted small mb-0">Kelola cicilan pinjaman anggota &amp; kartu kredit pribadi</p>
    </div>
    <div class="col-sm-6 text-right">
        <?php if (!empty($selectedTripId)) : ?>
            <button type="button" class="btn btn-primary btn-sm" id="btnTambahCicilan" data-toggle="modal" data-target="#modalTambahCicilan">
                <i class="fas fa-plus mr-1"></i> Tambah Cicilan
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Trip -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-body py-2 px-3">
                <form method="GET" action="<?= base_url('backend/installments') ?>" class="form-inline flex-wrap" id="filterForm">
                    <label class="mr-2 font-weight-bold"><i class="fas fa-route mr-1 text-primary"></i>Kegiatan:</label>
                    <select name="trip_id" class="form-control form-control-sm mr-2" id="tripSelect" onchange="this.form.submit()">
                        <option value="">-- Pilih Kegiatan --</option>
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

<?php if (empty($selectedTripId)) : ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Pilih kegiatan untuk melihat daftar cicilan.</p>
                </div>
            </div>
        </div>
    </div>

<?php elseif (empty($groupedData)) : ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted mb-0">Belum ada cicilan di kegiatan ini.</p>
                    <p class="text-muted small">Klik <strong>Tambah Cicilan</strong> untuk mulai mencatat.</p>
                </div>
            </div>
        </div>
    </div>

<?php else : ?>

    <?php
    // Calculate monthly summary for current user as borrower (what needs to be paid)
    $summaryMonthlyLoan = [];
    $summaryMonthlyCC   = [];
    $summaryMonthlyPaid = [];
    $summaryHasBill     = false;

    foreach ($monthColumns as $col) {
        $summaryMonthlyLoan[$col] = 0;
        $summaryMonthlyCC[$col] = 0;
        $summaryMonthlyPaid[$col] = true;
    }

    foreach ($groupedData as $groupKey => $group) {
        $isLoan = ($group['source_type'] === 'member_loan');
        $isSelf = ((int)$group['borrower_user_id'] === (int)user_id());
        
        if (!$isSelf) {
            continue;
        }

        foreach ($group['installments'] as $inst) {
            foreach ($monthColumns as $col) {
                $payment = $inst['payments'][$col] ?? null;
                if ($payment) {
                    $summaryHasBill = true;
                    if ($isLoan) {
                        $summaryMonthlyLoan[$col] += (int)$payment['due_amount'];
                    } else {
                        $summaryMonthlyCC[$col] += (int)$payment['due_amount'];
                    }
                    if ($payment['status'] !== 'paid') {
                        $summaryMonthlyPaid[$col] = false;
                    }
                }
            }
        }
    }
    ?>

    <?php if ($summaryHasBill) : ?>
        <!-- Card Ringkasan Pembayaran Bulanan -->
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-header py-2 px-3">
                <h3 class="card-title font-weight-bold text-info" style="font-size:0.95rem;">
                    <i class="fas fa-calculator mr-2"></i>Ringkasan Tagihan Bulanan Saya
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered mb-0" style="font-size: 0.82rem;">
                        <thead class="thead-light">
                            <tr>
                                <th>Bulan</th>
                                <th class="text-right">Pinjaman Anggota</th>
                                <th class="text-right">Kartu Kredit Pribadi</th>
                                <th class="text-right bg-info-light">Total Pembayaran</th>
                                <th class="text-center" style="width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grandSummaryTotal = 0;
                            foreach ($monthColumns as $col) : 
                                $loanAmt = $summaryMonthlyLoan[$col] ?? 0;
                                $ccAmt   = $summaryMonthlyCC[$col] ?? 0;
                                $totalAmt = $loanAmt + $ccAmt;
                                if ($totalAmt <= 0) continue;
                                $grandSummaryTotal += $totalAmt;
                                $isPaid = $summaryMonthlyPaid[$col] ?? false;
                            ?>
                                <tr>
                                    <td><strong><?= date('F Y', strtotime($col)) ?></strong></td>
                                    <td class="text-right text-primary">
                                        <?= $loanAmt > 0 ? 'Rp ' . number_format($loanAmt, 0, ',', '.') : '—' ?>
                                    </td>
                                    <td class="text-right text-success">
                                        <?= $ccAmt > 0 ? 'Rp ' . number_format($ccAmt, 0, ',', '.') : '—' ?>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark" style="background-color: rgba(23, 162, 184, 0.04);">
                                        Rp <?= number_format($totalAmt, 0, ',', '.') ?>
                                    </td>
                                    <td class="text-center text-xs">
                                        <?php if ($isPaid) : ?>
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Lunas</span>
                                        <?php else : ?>
                                            <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Belum Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($groupedData as $groupKey => $group) : ?>
        <?php
        $isLoan = ($group['source_type'] === 'member_loan');
        $isSelf = ((int)$group['borrower_user_id'] === (int)user_id());
        $isLender = ($isLoan && (int)($group['lender_user_id'] ?? 0) === (int)user_id());
        $cardBorderClass = $isLoan ? 'card-primary' : 'card-success';
        $badgeClass      = $isLoan ? 'badge-primary' : 'badge-success';
        $badgeLabel      = $isLoan ? 'Pinjaman Anggota' : 'Kartu Kredit';
        $badgeIcon       = $isLoan ? 'fa-users' : 'fa-credit-card';
        ?>

        <div class="card card-outline <?= $cardBorderClass ?> shadow-sm mb-4">
            <div class="card-header d-flex align-items-center flex-wrap">
                <div class="flex-grow-1">
                    <span class="badge <?= $badgeClass ?> mr-2">
                        <i class="fas <?= $badgeIcon ?> mr-1"></i><?= $badgeLabel ?>
                    </span>
                    <?php if ($isLoan) : ?>
                        <?php if ($isSelf) : ?>
                            <strong>Hutang ke: <?= esc($group['lender_name']) ?></strong>
                        <?php else : ?>
                            <strong>Piutang dari: <?= esc($group['borrower_name']) ?></strong>
                        <?php endif; ?>
                    <?php else : ?>
                        <strong>Kartu Kredit Pribadi – <?= esc($group['borrower_name']) ?></strong>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body p-0">
                <!-- Tabel Cicilan -->
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.82rem;">
                        <thead class="thead-light">
                            <tr>
                                <th style="min-width:180px;">Uraian</th>
                                <th class="text-right" style="min-width:110px;">Total</th>
                                <?php foreach ($monthColumns as $col) : ?>
                                    <th class="text-right" style="min-width:105px;">
                                        <?= date('M\'y', strtotime($col)) ?>
                                    </th>
                                <?php endforeach; ?>
                                <th class="text-center" style="min-width:60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $grandTotal      = 0;
                            $colTotals       = array_fill_keys($monthColumns, 0);
                            $colPaidStatus   = array_fill_keys($monthColumns, 'na'); // na / paid / unpaid
                            ?>
                            <?php foreach ($group['installments'] as $inst) : ?>
                                <?php
                                $grandTotal += (int)$inst['total_amount'];
                                $allDone = ($inst['status'] === 'completed');
                                ?>
                                <tr>
                                    <td>
                                        <?= esc($inst['description']) ?>
                                        <?php if ($allDone) : ?>
                                            <span class="badge badge-success ml-1" style="font-size:0.7rem;">Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        <?= 'Rp ' . number_format($inst['total_amount'], 0, ',', '.') ?>
                                    </td>
                                    <?php foreach ($monthColumns as $col) : ?>
                                        <?php
                                        $payment = $inst['payments'][$col] ?? null;
                                        if ($payment) {
                                            $colTotals[$col] += (int)$payment['due_amount'];
                                        }
                                        ?>
                                        <td class="text-right">
                                            <?php if ($payment) : ?>
                                                <?php if ($payment['status'] === 'paid') : ?>
                                                    <span class="text-success">
                                                        <?= 'Rp ' . number_format($payment['due_amount'], 0, ',', '.') ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-secondary">
                                                        <?= 'Rp ' . number_format($payment['due_amount'], 0, ',', '.') ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-center">
                                        <?php if ($isSelf && $inst['status'] !== 'completed') : ?>
                                            <button class="btn btn-xs btn-outline-danger btn-delete-inst"
                                                data-id="<?= $inst['id'] ?>"
                                                data-desc="<?= esc($inst['description']) ?>"
                                                data-trip="<?= $inst['trip_id'] ?>"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                        <!-- Footer: Total per bulan + tombol bayar -->
                        <tfoot class="font-weight-bold" style="background:#f8f9fa;">
                            <tr>
                                <td>Total</td>
                                <td class="text-right text-primary">
                                    Rp <?= number_format($grandTotal, 0, ',', '.') ?>
                                </td>
                                <?php foreach ($monthColumns as $col) : ?>
                                    <?php
                                    $colTotal = $colTotals[$col];
                                    // Cek apakah bulan ini sudah dibayar (ada group payment)
                                    $gpKey = ($group['lender_user_id'] ?? 'null') . '|' . $group['borrower_user_id'] . '|' . $group['source_type'] . '|' . $col;
                                    // Status akan dihitung di bawah
                                    ?>
                                    <td class="text-right <?= $colTotal > 0 ? 'text-primary' : 'text-muted' ?>">
                                        <?= $colTotal > 0 ? 'Rp ' . number_format($colTotal, 0, ',', '.') : '—' ?>
                                    </td>
                                <?php endforeach; ?>
                                <td></td>
                            </tr>
                            <!-- Baris status lunas + tombol bayar -->
                            <tr>
                                <td class="text-muted small">Status Lunas</td>
                                <td></td>
                                <?php foreach ($monthColumns as $col) : ?>
                                    <?php
                                    $colTotal = $colTotals[$col];
                                    if ($colTotal <= 0) {
                                        echo '<td></td>';
                                        continue;
                                    }

                                    // Cari group payment untuk bulan ini
                                    $groupPaid = null;
                                    foreach ($group['installments'] as $inst) {
                                        // ambil dari groupPaymentMap yang dikirim controller
                                        // Kita pakai array sederhana: cek semua installment payments bulan ini
                                    }

                                    // Check apakah semua installment_payments di bulan ini sudah paid
                                    $monthPaid = true;
                                    $monthHasData = false;
                                    foreach ($group['installments'] as $inst) {
                                        $p = $inst['payments'][$col] ?? null;
                                        if ($p) {
                                            $monthHasData = true;
                                            if ($p['status'] !== 'paid') {
                                                $monthPaid = false;
                                            }
                                        }
                                    }

                                    if (!$monthHasData) {
                                        echo '<td></td>';
                                        continue;
                                    }
                                    ?>
                                    <td class="text-center">
                                        <?php if ($monthPaid) : ?>
                                            <span class="badge badge-success" style="font-size:0.7rem;">
                                                <i class="fas fa-check mr-1"></i>Lunas
                                            </span>
                                        <?php else : ?>
                                            <?php if ($isSelf) : ?>
                                                <?php if ($isLoan) : ?>
                                                    <button class="btn btn-xs btn-primary btn-pay-month"
                                                        data-trip="<?= $selectedTripId ?>"
                                                        data-lender="<?= $group['lender_user_id'] ?>"
                                                        data-borrower="<?= $group['borrower_user_id'] ?>"
                                                        data-month="<?= $col ?>"
                                                        data-total="<?= $colTotal ?>"
                                                        data-label="<?= date('M Y', strtotime($col)) ?>"
                                                        data-lender-name="<?= esc($group['lender_name']) ?>"
                                                        data-source="member_loan">
                                                        <i class="fas fa-paper-plane mr-1"></i>Bayar
                                                    </button>
                                                <?php else : ?>
                                                    <button class="btn btn-xs btn-success btn-pay-month"
                                                        data-trip="<?= $selectedTripId ?>"
                                                        data-lender=""
                                                        data-borrower="<?= $group['borrower_user_id'] ?>"
                                                        data-month="<?= $col ?>"
                                                        data-total="<?= $colTotal ?>"
                                                        data-label="<?= date('M Y', strtotime($col)) ?>"
                                                        data-lender-name=""
                                                        data-source="credit_card">
                                                        <i class="fas fa-check mr-1"></i>Bayar CC
                                                    </button>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="badge badge-warning" style="font-size:0.7rem;">Belum</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    <?php endforeach; ?>
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
                                    <i class="fas fa-credit-card mr-1 text-success"></i> Kartu Kredit Pribadi
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
                                <?php if ((int)$m['user_id'] !== (int)user_id()) : ?>
                                    <option value="<?= $m['user_id'] ?>"><?= esc($m['username']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
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
                    <h5 class="modal-title"><i class="fas fa-credit-card mr-2"></i>Bayar Tagihan CC</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Kartu Kredit Pribadi</div>
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
                            Tidak ada transfer ke pihak lain. Ini mencatat bukti bahwa tagihan kartu kredit sudah dibayar.
                        </small>
                    </div>

                    <!-- Tanggal Bayar -->
                    <div class="form-group">
                        <label for="paycc_paid_at">Tanggal Bayar <span class="text-danger">*</span></label>
                        <input type="date" name="paid_at" id="paycc_paid_at" class="form-control" required>
                    </div>

                    <!-- Bukti Bayar CC (WAJIB) -->
                    <div class="form-group">
                        <label for="paycc_proof">
                            <i class="fas fa-paperclip mr-1"></i> Bukti Pembayaran CC
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
                            Contoh: screenshot app bank, foto tagihan kartu kredit
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

<!-- ===================== MODAL: Konfirmasi Hapus ===================== -->
<div class="modal fade" id="modalHapusCicilan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <form method="POST" id="formHapusCicilan">
                <?= csrf_field() ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Cicilan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Yakin hapus cicilan <strong id="deleteInstDesc">ini</strong>?</p>
                    <p class="text-muted small">Semua jadwal pembayaran yang belum dibayar akan ikut dihapus.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash mr-1"></i>Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    // ---- Helpers ----
    function formatRp(n) {
        return 'Rp ' + parseInt(n).toLocaleString('id-ID');
    }

    // ---- Source type toggle ----
    $('input[name="source_type"]').on('change', function () {
        if ($(this).val() === 'member_loan') {
            $('#lenderWrapper').show();
            $('#inst_lender').prop('required', true);
        } else {
            $('#lenderWrapper').hide();
            $('#inst_lender').prop('required', false).val('');
        }
    });

    // ---- Calc mode toggle ----
    $('input[name="calc_mode"]').on('change', function () {
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

    $('#inst_total, #inst_monthly, #inst_months, #inst_start').on('input change', runSimulate);

    function doSimulate() {
        var mode    = $('input[name="calc_mode"]:checked').val();
        var months  = parseInt($('#inst_months').val()) || 0;
        var startRaw = $('#inst_start').val();
        var total   = parseInt($('#inst_total').val()) || 0;
        var monthly = parseInt($('#inst_monthly').val()) || 0;

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

    // ---- Hapus Cicilan ----
    $(document).on('click', '.btn-delete-inst', function () {
        var id   = $(this).data('id');
        var desc = $(this).data('desc');
        var trip = $(this).data('trip');
        $('#deleteInstDesc').text('"' + desc + '"');
        $('#formHapusCicilan').attr('action', '<?= base_url('backend/installments/delete/') ?>' + id);
        $('#modalHapusCicilan').modal('show');
    });

    // ---- Default tanggal start bulan ini ----
    var now = new Date();
    var ym = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    $('#inst_start').val(ym);

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
