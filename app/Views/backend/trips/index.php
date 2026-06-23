<?= $this->extend('backend/template/template') ?>
<?= $this->section('content') ?>

<style>
.trip-card {
    border: none;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-left: 4px solid #007bff; /* Accent border color to match primary styling */
}
.dark-mode .trip-card {
    background: #1e293b;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    border-left-color: #3b82f6;
}
.trip-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}
.trip-group-badge {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 4px 10px;
    border-radius: 30px;
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
    display: inline-block;
    margin-bottom: 12px;
}
.dark-mode .trip-group-badge {
    background-color: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
}
.trip-title {
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #1e293b;
    line-height: 1.3;
}
.dark-mode .trip-title {
    color: #f1f5f9;
}
.trip-meta-item {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dark-mode .trip-meta-item {
    color: #94a3b8;
}
.trip-notes {
    font-size: 0.82rem;
    color: #64748b;
    background-color: #f8fafc;
    padding: 10px;
    border-radius: 8px;
    margin-top: 12px;
    margin-bottom: 15px;
    border-left: 2px solid #cbd5e1;
    flex-grow: 1;
    min-height: 54px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.dark-mode .trip-notes {
    background-color: #0f172a;
    color: #94a3b8;
    border-left-color: #475569;
}
.trip-card-footer {
    padding-top: 15px;
    border-top: 1px solid #f1f5f9;
    margin-top: auto;
}
.dark-mode .trip-card-footer {
    border-top-color: #334155;
}
.group-card {
    border: none;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    margin-bottom: 24px;
    overflow: hidden;
}
.dark-mode .group-card {
    background: #1e293b;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.group-card-header {
    padding: 16px 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background-color 0.2s ease;
    user-select: none;
    background-color: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
}
.dark-mode .group-card-header {
    background-color: #0f172a;
    border-bottom-color: #334155;
}
.group-card-header:hover {
    background-color: #f1f5f9;
}
.dark-mode .group-card-header:hover {
    background-color: #1e293b;
}
.group-card-header[aria-expanded="true"] .collapse-chevron {
    transform: rotate(180deg);
}
.collapse-chevron {
    transition: transform 0.3s ease;
    font-size: 1rem;
}
.group-card-body {
    background-color: #fafbfc;
    padding: 24px;
}
.dark-mode .group-card-body {
    background-color: #0f172a;
}
</style>

<div class="row mb-4 align-items-center">
    <div class="col-12 col-sm-7 col-md-8 mb-2 mb-sm-0">
        <h5 class="text-muted mb-0" style="font-size: 0.95rem;">Daftar kegiatan atau proyek pengeluaran bersama keluarga / grup Anda.</h5>
    </div>
    <div class="col-12 col-sm-5 col-md-4 text-sm-right">
        <a href="<?= base_url('backend/trips/create') ?>" class="btn btn-primary font-weight-bold btn-block d-sm-inline-block w-sm-auto shadow-sm" style="border-radius: 8px; padding: 8px 16px;">
            <i class="fas fa-plus mr-1"></i> Buat Kegiatan Baru
        </a>
    </div>
</div>

<?php if (empty($trips)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-warning text-center py-5 shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <i class="fas fa-clipboard-list text-warning fa-3x mb-3"></i>
                    <h4>Belum Ada Kegiatan</h4>
                    <p class="text-muted">Buat kegiatan atau proyek pengeluaran bersama untuk mulai mencatat pengeluaran grup Anda.</p>
                    <a href="<?= base_url('backend/trips/create') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-plus mr-1"></i> Buat Kegiatan Pertama
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php
    // Group trips by group name
    $groupedTrips = [];
    foreach ($trips as $t) {
        $groupName = $t['group_name'] ?? 'Lainnya';
        $groupedTrips[$groupName][] = $t;
    }

    // Color list palette for groups
    $colors = [
        '#007bff', // blue
        '#28a745', // green
        '#e83e8c', // pink
        '#fd7e14', // orange
        '#17a2b8', // cyan
        '#6f42c1', // purple
        '#20c997', // teal
        '#ffc107', // yellow
        '#dc3545', // red
    ];

    $groupColors = [];
    $colorIndex = 0;
    foreach (array_keys($groupedTrips) as $gName) {
        $groupColors[$gName] = $colors[$colorIndex % count($colors)];
        $colorIndex++;
    }
    ?>

    <?php foreach ($groupedTrips as $gName => $tripList): ?>
        <div class="card group-card">
            <div class="group-card-header collapsed" data-toggle="collapse" data-target="#collapse-<?= md5($gName) ?>" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <h5 class="font-weight-bold d-inline-block py-1 px-3 shadow-xs" style="background-color: <?= $groupColors[$gName] ?>12; color: <?= $groupColors[$gName] ?>; border-radius: 8px; font-size: 0.95rem; border-left: 4px solid <?= $groupColors[$gName] ?>; margin-bottom: 0;">
                        <i class="fas fa-users mr-1"></i> <?= esc($gName) ?> 
                        <span class="badge ml-2" style="background-color: <?= $groupColors[$gName] ?>; color: #fff; font-size: 0.72rem; vertical-align: middle; border-radius: 20px; padding: 2px 8px;">
                            <?= count($tripList) ?> Kegiatan
                        </span>
                    </h5>
                </div>
                <i class="fas fa-chevron-down collapse-chevron" style="color: <?= $groupColors[$gName] ?>;"></i>
            </div>
            
            <div id="collapse-<?= md5($gName) ?>" class="collapse">
                <div class="group-card-body">
                    <div class="row">
                        <?php foreach ($tripList as $t): ?>
                            <div class="col-12 col-md-6 col-lg-4 mb-4">
                                <div class="card trip-card shadow-sm h-100" style="border-left: 4px solid <?= $groupColors[$gName] ?>;">
                                    <div class="card-body p-4 d-flex flex-column h-100">
                                        <div>
                                            <span class="trip-group-badge" style="background-color: <?= $groupColors[$gName] ?>15; color: <?= $groupColors[$gName] ?>;">
                                                <i class="fas fa-layer-group fa-xs mr-1"></i> <?= esc($t['group_name']) ?>
                                            </span>
                                            <h4 class="trip-title"><?= esc($t['name']) ?></h4>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <div class="trip-meta-item">
                                                <i class="far fa-calendar-alt text-primary"></i>
                                                <span>
                                                    <?php if ($t['start_date'] || $t['end_date']): ?>
                                                        <?= $t['start_date'] ? date('d M Y', strtotime($t['start_date'])) : 'Mulai' ?>
                                                        &ndash;
                                                        <?= $t['end_date'] ? date('d M Y', strtotime($t['end_date'])) : 'Selesai' ?>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Tanggal belum diatur</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="trip-notes">
                                            <?= !empty($t['notes']) ? esc($t['notes']) : '<em class="text-muted">Tidak ada catatan kegiatan.</em>' ?>
                                        </div>

                                        <div class="trip-card-footer">
                                            <a href="<?= base_url('backend/trips/detail/' . $t['id']) ?>" class="btn btn-info btn-block font-weight-bold" style="border-radius: 8px; padding: 8px; background-color: <?= $groupColors[$gName] ?>; border-color: <?= $groupColors[$gName] ?>; color: #fff;">
                                                <i class="fas fa-eye mr-1"></i> Detail &amp; Periode
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
