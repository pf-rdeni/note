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
    <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box shadow-xs hover-card">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted">Kelompok Aktif</span>
                <span class="info-box-number text-lg text-dark"><?= esc($numGroups) ?> Kelompok</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box shadow-xs hover-card">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-clipboard-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted">Kegiatan</span>
                <span class="info-box-number text-lg text-dark"><?= esc($numTrips) ?> Kegiatan</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
        <div class="info-box shadow-xs hover-card">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-wallet text-white"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted text-warning">Total Belanja</span>
                <span class="info-box-number text-lg text-dark">Rp <?= number_format($totalExpenses, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Spend Trend Chart -->
    <div class="col-lg-7">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header border-0 py-3">
                <h3 class="card-title font-weight-bold text-primary">
                    <i class="fas fa-chart-bar mr-1"></i> Tren Pengeluaran per Periode
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($spendingChartData)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="far fa-chart-bar fa-3x mb-3 text-secondary"></i>
                        <p class="mb-0">Belum ada data transaksi periodik untuk membuat grafik belanja.</p>
                    </div>
                <?php else: ?>
                    <div class="chart-container" style="position: relative; height: 280px; width: 100%;">
                        <canvas id="spendingChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-lg-5">
        <div class="card card-info card-outline shadow-sm">
            <div class="card-header border-0 py-3">
                <h3 class="card-title font-weight-bold text-info">
                    <i class="fas fa-receipt mr-1"></i> 5 Transaksi Terbaru
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/transactions') ?>" class="btn btn-xs btn-info font-weight-bold shadow-sm" style="border-radius: 6px; padding: 4px 8px;">
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
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentTransactions as $t): ?>
                            <div class="list-group-item py-3">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 font-weight-bold text-dark text-truncate" style="max-width: 65%;">
                                        <?= esc($t['description']) ?>
                                    </h6>
                                    <span class="font-weight-bold text-dark">
                                        Rp <?= number_format($t['amount'], 0, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center text-xs text-muted">
                                    <span>
                                        <i class="fas fa-clipboard-list mr-1 text-xs"></i><?= esc($t['trip_name']) ?>
                                    </span>
                                    <span>
                                        <?= date('d M Y', strtotime($t['date'])) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="text-xs text-secondary">
                                        <i class="far fa-user text-xs mr-1"></i>Payer: <strong><?= esc($t['paid_by_name']) ?></strong>
                                    </span>
                                    <?php if ($t['type'] === 'shared'): ?>
                                        <span class="badge badge-success text-xs"><i class="fas fa-divide mr-1"></i> Shared</span>
                                    <?php else: ?>
                                        <span class="badge badge-info text-xs"><i class="fas fa-user-tag mr-1"></i> Individual</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center bg-white border-0 py-3">
                <a href="<?= base_url('backend/transactions') ?>" class="btn btn-sm btn-outline-info font-weight-bold">
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
    <?php if (!empty($spendingChartData)): ?>
    // Data setup for Chart.js
    const labels = <?= json_encode(array_column($spendingChartData, 'label')) ?>;
    const dataValues = <?= json_encode(array_column($spendingChartData, 'amount')) ?>;

    const ctx = document.getElementById('spendingChart').getContext('2d');
    
    // Create soft gradient for bars
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, '#007bff');
    gradient.addColorStop(1, '#6610f2');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pengeluaran (Rp)',
                data: dataValues,
                backgroundColor: gradient,
                borderColor: '#4e73df',
                borderWidth: 1,
                borderRadius: 5,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw;
                            return 'Total: Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    },
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<style>
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
</style>
<?= $this->endSection() ?>
