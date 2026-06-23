<!-- Navbar - Navigasi Atas -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Tombol Sidebar (Left) -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url('/backend/dashboard') ?>" class="nav-link">Dashboard</a>
        </li>
    </ul>

    <!-- Navbar Kanan -->
    <ul class="navbar-nav ml-auto">
        <!-- Toggle Dark Mode -->
        <li class="nav-item">
            <a class="nav-link" href="#" id="darkModeToggle" role="button" title="Mode Gelap/Terang">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </a>
        </li>
        
        <!-- Help -->
        <li class="nav-item">
            <a class="nav-link" href="#" data-toggle="modal" data-target="#helpModal" title="Bantuan">
                <i class="fas fa-question-circle"></i>
            </a>
        </li>
        
        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user-circle mr-1"></i>
                <span class="d-none d-md-inline"><?= user()->username ?? 'User' ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?= base_url('backend/profil') ?>">
                    <i class="fas fa-user mr-2"></i> Profil
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- Modal Help -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">
                    <i class="fas fa-question-circle text-info mr-2"></i>
                    Bantuan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6><strong>Aplikasi Split Bill Keluarga</strong></h6>
                <p>Mengelola pembagian pengeluaran bersama (Shared) dan penyesuaian saldo pribadi secara real-time dan transparan.</p>
                <hr>
                <p><strong>Fitur Utama:</strong></p>
                <ul>
                    <li><i class="fas fa-tachometer-alt text-primary mr-2"></i> Dashboard - Ringkasan saldo dan pengeluaran</li>
                    <li><i class="fas fa-users text-success mr-2"></i> Group & Anggota - Kelola anggota kelompok</li>
                    <li><i class="fas fa-clipboard-list text-warning mr-2"></i> Kegiatan & Periode - Kelola kegiatan dan bulanan</li>
                    <li><i class="fas fa-file-invoice-dollar text-info mr-2"></i> Transaksi - Catat pengeluaran shared / individual</li>
                    <li><i class="fas fa-hand-holding-usd text-danger mr-2"></i> Settlement - Rekomendasi pelunasan transfer</li>
                </ul>
                <hr>
                <p class="text-muted small">
                    Versi 1.0.0 | &copy; <?= date('Y') ?> Split Bill Keluarga
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
