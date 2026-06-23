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
        <!-- Trip Selector Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Pilih Kegiatan
                </h3>
            </div>
            <div class="card-body">
                <form action="<?= base_url('backend/settlements') ?>" method="get" id="tripFilterForm">
                    <div class="form-group mb-0">
                        <label for="trip_select">Kegiatan:</label>
                        <select class="form-control select2" id="trip_select" name="trip_id" onchange="this.form.submit()">
                            <?php if (empty($availableTrips)): ?>
                                <option value="" disabled selected>Belum ada kegiatan</option>
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
                        <?php if (empty($periods)): ?>
                            <div class="p-3 text-muted text-center small">
                                <i class="fas fa-calendar-times mb-1 d-block text-warning"></i>
                                Belum ada periode dibuat.
                            </div>
                        <?php else: ?>
                            <?php foreach ($periods as $p): ?>
                                <?php $pSettled = ($p['status'] ?? 'open') === 'settled'; ?>
                                <a href="<?= base_url('backend/settlements?trip_id=' . $selectedTripId . '&period_id=' . $p['id']) ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= (int)$p['id'] === (int)$selectedPeriodId ? 'active' : '' ?> <?= $pSettled ? 'text-muted' : '' ?>">
                                    <span>
                                        <?php if ($pSettled): ?>
                                            <i class="fas fa-lock fa-xs mr-1 text-secondary"></i>
                                        <?php endif; ?>
                                        <?= esc($p['label']) ?>
                                    </span>
                                    <?php if ($pSettled): ?>
                                        <span class="badge badge-secondary badge-pill" title="Periode sudah ditutup">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success badge-pill" title="Periode aktif">
                                            <i class="fas fa-unlock-alt"></i>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Settlements Main Panel -->
    <div class="col-lg-9">
        <?php if (empty($selectedTripId)): ?>
            <div class="card card-outline card-warning text-center py-5">
                <div class="card-body">
                    <i class="fas fa-clipboard-list text-warning fa-3x mb-3"></i>
                    <h4>Pilih Kegiatan Terlebih Dahulu</h4>
                    <p class="text-muted">Untuk menyelesaikan saldo (settlement), pastikan Anda telah memilih Kegiatan yang aktif.</p>
                </div>
            </div>
        <?php elseif (empty($selectedPeriodId)): ?>
            <div class="card card-outline card-info text-center py-5">
                <div class="card-body">
                    <i class="far fa-calendar-alt text-info fa-3x mb-3"></i>
                    <h4>Pilih Periode Pengeluaran</h4>
                    <p class="text-muted">Pilih salah satu periode di kolom sebelah kiri untuk memproses penyelesaian saldo.</p>
                </div>
            </div>
        <?php else: ?>
            
            <!-- Recommendations Card -->
            <div class="card card-warning card-outline shadow-sm">
                <div class="card-header border-0 py-3">
                    <h3 class="card-title font-weight-bold text-warning">
                        <i class="fas fa-comments-dollar mr-1"></i> Rekomendasi Penyelesaian (Settlement)
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($calculationResult) || empty($calculationResult['settlements'])): ?>
                        <div class="alert alert-success mb-0 py-4 font-weight-bold">
                            <i class="fas fa-check-circle mr-2"></i> Semua saldo sudah seimbang untuk periode ini! Tidak ada transfer yang diperlukan.
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-4">Untuk menyeimbangkan saldo pengeluaran periode ini, anggota berutang (debitur) harus melakukan transfer kepada anggota piutang (kreditur) berikut:</p>
                        
                        <div class="row">
                            <?php foreach ($calculationResult['settlements'] as $index => $s): ?>
                                <?php
                                // Cari status transfer yang tercatat di database untuk pasangan ini
                                $recordedStatus = null;
                                $recordedId = null;
                                foreach ($settlementHistory as $sh) {
                                    if ((int)$sh['from_user_id'] === (int)$s['from_user_id'] && 
                                        (int)$sh['to_user_id'] === (int)$s['to_user_id'] && 
                                        (int)$sh['amount'] === (int)$s['amount']) {
                                        $recordedStatus = $sh['status'];
                                        $recordedId = $sh['id'];
                                        break;
                                    }
                                }
                                ?>
                                <div class="col-md-6 mb-4">
                                    <div class="p-3 border rounded bg-white shadow-xs d-flex flex-column h-100 justify-content-between">
                                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                            <div>
                                                <span class="badge badge-danger font-weight-bold py-1 px-2 mb-1">Debitur</span>
                                                <h6 class="font-weight-bold mb-0 text-dark"><?= esc($s['from_username']) ?></h6>
                                            </div>
                                            <div class="text-center px-2">
                                                <i class="fas fa-long-arrow-alt-right text-warning fa-lg"></i>
                                                <div class="font-weight-bold text-md text-primary mt-1">Rp <?= number_format($s['amount'], 0, ',', '.') ?></div>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-success font-weight-bold py-1 px-2 mb-1">Kreditur</span>
                                                <h6 class="font-weight-bold mb-0 text-dark"><?= esc($s['to_username']) ?></h6>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-2">
                                            <?php if ($recordedStatus === 'paid'): ?>
                                                <button type="button" class="btn btn-success btn-block font-weight-bold" disabled>
                                                    <i class="fas fa-check-circle mr-1"></i> Selesai / Lunas
                                                </button>
                                            <?php elseif ($recordedStatus === 'pending'): ?>
                                                <button type="button" class="btn btn-warning btn-block font-weight-bold text-white" disabled>
                                                    <i class="fas fa-clock mr-1"></i> Menunggu Verifikasi
                                                </button>
                                            <?php else: ?>
                                                <?php 
                                                // Hanya pengirim bersangkutan atau admin yang dapat konfirmasi
                                                $canPay = ((int)user_id() === (int)$s['from_user_id'] || $currentMembership['role'] === 'admin');
                                                ?>
                                                <?php if ($canPay): ?>
                                                    <button type="button" 
                                                            class="btn btn-primary btn-block font-weight-bold btn-pay-modal" 
                                                            data-from-id="<?= $s['from_user_id'] ?>"
                                                            data-from-name="<?= esc($s['from_username']) ?>"
                                                            data-to-id="<?= $s['to_user_id'] ?>"
                                                            data-to-name="<?= esc($s['to_username']) ?>"
                                                            data-amount="<?= $s['amount'] ?>"
                                                            data-amount-formatted="Rp <?= number_format($s['amount'], 0, ',', '.') ?>">
                                                        <i class="fas fa-file-upload mr-1"></i> Konfirmasi Transfer
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-secondary btn-block font-weight-bold" disabled>
                                                        <i class="fas fa-lock mr-1"></i> Menunggu Pembayaran
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Settlement History Card -->
            <div class="card card-success card-outline shadow-sm">
                <div class="card-header border-0 py-3">
                    <h3 class="card-title font-weight-bold text-success">
                        <i class="fas fa-history mr-1"></i> Riwayat Konfirmasi Transfer
                    </h3>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($settlementHistory)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x mb-3 d-block text-warning"></i>
                            Belum ada riwayat transfer pembayaran untuk periode ini.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($settlementHistory as $sh): ?>
                                <?php
                                // Tombol setujui aktif untuk: kreditur (penerima) ATAU admin
                                $canApprove = ((int)user_id() === (int)$sh['to_user_id'] || $currentMembership['role'] === 'admin');
                                ?>
                                <div class="col-12 col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100 border shadow-xs" style="border-radius: 12px; overflow: hidden;">
                                        <!-- Header: Status & Waktu -->
                                        <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center bg-light border-bottom">
                                            <span class="text-xs text-muted font-weight-bold">
                                                <i class="far fa-clock mr-1"></i><?= date('d/m/Y H:i', strtotime($sh['created_at'])) ?>
                                            </span>
                                            <div>
                                                <?php if ($sh['status'] === 'paid'): ?>
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Lunas</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning px-2 py-1 text-white"><i class="fas fa-clock mr-1"></i>Pending</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Body: Pengirim & Penerima -->
                                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                                <div style="flex: 1;">
                                                    <span class="text-xs text-muted d-block text-uppercase font-weight-bold">Pengirim</span>
                                                    <h6 class="font-weight-bold mb-0 text-dark text-truncate" style="max-width: 80px;" title="<?= esc($sh['sender_name']) ?>"><?= esc($sh['sender_name']) ?></h6>
                                                </div>
                                                <div class="text-center px-1" style="flex: 1.5;">
                                                    <i class="fas fa-long-arrow-alt-right text-muted fa-sm"></i>
                                                    <div class="font-weight-bold text-sm text-primary mt-1">Rp <?= number_format($sh['amount'], 0, ',', '.') ?></div>
                                                </div>
                                                <div class="text-right" style="flex: 1;">
                                                    <span class="text-xs text-muted d-block text-uppercase font-weight-bold">Penerima</span>
                                                    <h6 class="font-weight-bold mb-0 text-dark text-truncate" style="max-width: 80px;" title="<?= esc($sh['receiver_name']) ?>"><?= esc($sh['receiver_name']) ?></h6>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="text-xs text-secondary mb-1">
                                                    <i class="far fa-comment-alt mr-1"></i>Catatan:
                                                </div>
                                                <div class="p-2 bg-light rounded text-xs text-dark font-italic border-left" style="border-left: 2px solid #ced4da; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= esc($sh['note'] ?: '-') ?>">
                                                    <?= esc($sh['note'] ?: '-') ?>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between text-xs">
                                                <span>Bukti Transfer:</span>
                                                <?php if ($sh['proof_image']): ?>
                                                    <a href="<?= base_url($sh['proof_image']) ?>" class="btn btn-outline-info btn-xs font-weight-bold view-image-popup py-1 px-2" style="border-radius: 6px;">
                                                        <i class="fas fa-image mr-1"></i>Lihat Gambar
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted font-italic">Tidak diunggah</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Footer: Actions -->
                                        <div class="card-footer py-2 px-3 bg-light border-top text-center">
                                            <?php if ($sh['status'] === 'pending' && $canApprove): ?>
                                                <?php
                                                $shPeriod = null;
                                                foreach ($periods as $pp) {
                                                    if ((int)$pp['id'] === (int)$sh['period_id']) { $shPeriod = $pp; break; }
                                                }
                                                $shPeriodLabel  = $shPeriod ? esc($shPeriod['label']) : '';
                                                $shPeriodSettled = $shPeriod ? (($shPeriod['status'] ?? 'open') === 'settled') : false;
                                                ?>
                                                <a href="<?= base_url('backend/settlements/approve/' . $sh['id']) ?>" 
                                                   class="btn btn-success btn-xs btn-block font-weight-bold btn-approve-settle py-2"
                                                   style="border-radius: 8px;"
                                                   data-sender="<?= esc($sh['sender_name']) ?>"
                                                   data-receiver="<?= esc($sh['receiver_name']) ?>"
                                                   data-amount="Rp <?= number_format($sh['amount'], 0, ',', '.') ?>"
                                                   data-period-label="<?= $shPeriodLabel ?>"
                                                   data-period-settled="<?= $shPeriodSettled ? '1' : '0' ?>">
                                                    <i class="fas fa-check-circle mr-1"></i>Konfirmasi Terima
                                                </a>
                                            <?php elseif ($sh['status'] === 'pending' && !$canApprove): ?>
                                                <span class="text-muted text-xs font-weight-bold d-block py-1">
                                                    <i class="fas fa-hourglass-half text-warning mr-1"></i>Menunggu Penerima
                                                </span>
                                            <?php else: ?>
                                                <span class="text-success text-xs font-weight-bold d-block py-1">
                                                    <i class="fas fa-check-circle mr-1"></i>Lunas pada <?= date('d/m H:i', strtotime($sh['paid_at'])) ?>
                                                </span>
                                            <?php endif; ?>
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

<!-- Modal Upload Bukti Transfer -->
<?php if (!empty($selectedTripId) && !empty($selectedPeriodId)): ?>
    <div class="modal fade" id="modalPaySettlement" tabindex="-1" role="dialog" aria-labelledby="modalPaySettlementLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white font-weight-bold" id="modalPaySettlementLabel">
                        <i class="fas fa-file-upload mr-1"></i> Unggah Bukti Pembayaran
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?= base_url('backend/settlements/pay') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="trip_id" value="<?= $selectedTripId ?>">
                    <input type="hidden" name="period_id" value="<?= $selectedPeriodId ?>">
                    <input type="hidden" name="from_user_id" id="modal_from_user_id">
                    <input type="hidden" name="to_user_id" id="modal_to_user_id">
                    <input type="hidden" name="amount" id="modal_amount">
                    
                    <div class="modal-body">
                        <div class="form-group bg-light p-3 rounded mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Pengirim (Debitur):</span>
                                <span class="font-weight-bold text-dark" id="display_sender"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Penerima (Kreditur):</span>
                                <span class="font-weight-bold text-dark" id="display_receiver"></span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                <span class="font-weight-bold text-secondary">Nominal Transfer:</span>
                                <span class="font-weight-bold text-lg text-primary" id="display_amount"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>File Bukti Transfer <span class="text-muted">(Opsional)</span></label>
                            <!-- Hidden file inputs -->
                            <input type="file" class="d-none proof-file-input" id="proof_image" name="proof_image" 
                                   accept="image/*" capture="environment">
                            <input type="file" class="d-none proof-gallery-input" id="proof_image_gallery" 
                                   accept="image/*">
                            
                            <div class="receipt-upload-area" id="proofUploadArea">
                                <!-- Pilihan tombol -->
                                <div class="receipt-upload-actions" id="proofUploadActions">
                                    <label for="proof_image" class="btn-capture mb-0" title="Ambil foto bukti transfer langsung dengan kamera">
                                        <i class="fas fa-camera"></i>
                                        Foto Bukti
                                    </label>
                                    <label for="proof_image_gallery" class="btn-gallery mb-0" title="Pilih dari galeri foto">
                                        <i class="fas fa-images"></i>
                                        Dari Galeri
                                    </label>
                                </div>
                                <!-- Preview gambar -->
                                <div class="receipt-preview-container" id="proofPreviewContainer">
                                    <button type="button" class="btn-remove-receipt" id="btnRemoveProof" title="Hapus foto">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <img src="" alt="Preview Bukti" class="receipt-preview-img" id="proofPreviewImg">
                                    <small class="text-muted d-block mt-1" id="proofFileName"></small>
                                </div>
                            </div>
                            <small class="form-text text-muted">Format JPG/PNG, maks 5MB. Unggah sebagai bukti pembayaran transfer.</small>
                        </div>

                        <div class="form-group">
                            <label for="note">Catatan Tambahan <span class="text-muted">(Opsional)</span></label>
                            <input type="text" class="form-control" id="note" name="note" placeholder="Contoh: Transfer Bank Mandiri, Lunas ya">
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold">Unggah Bukti</button>
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

    // =============================================
    // PROOF UPLOAD: Kamera & Galeri Handler (Modal Settlements)
    // =============================================
    function setupProofUpload(cameraInputId, galleryInputId, previewContainerId, previewImgId, fileNameId, removeBtn) {
        const cameraInput  = document.getElementById(cameraInputId);
        const galleryInput = document.getElementById(galleryInputId);
        const previewCont  = document.getElementById(previewContainerId);
        const previewImg   = document.getElementById(previewImgId);
        const fileNameEl   = document.getElementById(fileNameId);
        const removeBtnEl  = document.getElementById(removeBtn);

        function handleFile(file, targetInput) {
            if (!file) return;
            // Sinkronkan ke input utama (cameraInput) untuk submit
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                cameraInput.files = dt.files;
            } catch(e) { /* Safari fallback */ }

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

    setupProofUpload('proof_image', 'proof_image_gallery', 'proofPreviewContainer', 'proofPreviewImg', 'proofFileName', 'btnRemoveProof');

    // Reset file input dan preview saat modal ditutup
    $('#modalPaySettlement').on('hidden.bs.modal', function () {
        $('#proof_image').val('');
        $('#proof_image_gallery').val('');
        $('#proofPreviewImg').attr('src', '');
        $('#proofPreviewContainer').hide();
        $('#proofFileName').text('');
        $('#note').val('');
    });

    // Event Klik Tombol Konfirmasi Transfer
    $('.btn-pay-modal').on('click', function() {
        const fromId = $(this).data('from-id');
        const fromName = $(this).data('from-name');
        const toId = $(this).data('to-id');
        const toName = $(this).data('to-name');
        const amount = $(this).data('amount');
        const amountFormatted = $(this).data('amount-formatted');

        // Set value ke modal inputs
        $('#modal_from_user_id').val(fromId);
        $('#modal_to_user_id').val(toId);
        $('#modal_amount').val(amount);

        // Set value ke modal display
        $('#display_sender').text(fromName);
        $('#display_receiver').text(toName);
        $('#display_amount').text(amountFormatted);

        // Buka modal
        $('#modalPaySettlement').modal('show');
    });

    // Event Klik Tombol Konfirmasi Terima Settlement (Penerima / Admin)
    $('.btn-approve-settle').on('click', function(e) {
        e.preventDefault();
        const url           = $(this).attr('href');
        const sender        = $(this).data('sender');
        const receiver      = $(this).data('receiver');
        const amount        = $(this).data('amount');
        const periodLabel   = $(this).data('period-label');
        const periodSettled = $(this).data('period-settled') === 1 || $(this).data('period-settled') === '1';

        // Buat opsi lock period — hanya tampil jika periode belum settled & ada label
        const lockOption = (periodLabel && !periodSettled)
            ? `<div class="mt-3 p-2 border rounded bg-light text-left">
                   <div class="custom-control custom-checkbox">
                       <input type="checkbox" class="custom-control-input" id="swal-lock-period" checked>
                       <label class="custom-control-label font-weight-bold text-secondary" for="swal-lock-period">
                           <i class="fas fa-lock mr-1"></i> Tutup buku periode <strong>"${periodLabel}"</strong> sekaligus
                       </label>
                   </div>
                   <small class="text-muted ml-4">Setelah ditutup, transaksi baru tidak dapat ditambahkan ke periode ini.</small>
               </div>`
            : '';

        Swal.fire({
            title: 'Konfirmasi Penerimaan?',
            html: `<p>Apakah Anda sudah menerima transfer dari <strong>"${sender}"</strong> ke <strong>"${receiver}"</strong> sebesar <strong>${amount}</strong>?</p>
                   <span class="text-success small"><i class="fas fa-check-circle mr-1"></i>Transfer akan ditandai <b>Lunas</b> setelah dikonfirmasi.</span>
                   ${lockOption}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check-circle mr-1"></i> Ya, Sudah Diterima!',
            cancelButtonText: 'Batal',
            didOpen: () => {
                // Pastikan checkbox bisa diklik (SweetAlert2 intercept event)
                const cb = Swal.getPopup().querySelector('#swal-lock-period');
                if (cb) cb.addEventListener('click', (ev) => ev.stopPropagation());
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const cb = Swal.getPopup() ? Swal.getPopup().querySelector('#swal-lock-period') : null;
                const lockPeriod = cb && cb.checked ? 1 : 0;
                // Tambahkan query param lock_period jika dicentang
                const finalUrl = lockPeriod ? url + '?lock_period=1' : url;
                window.location.href = finalUrl;
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
