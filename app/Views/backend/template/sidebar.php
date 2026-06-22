<!-- Sidebar - Menu Navigasi Kiri -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= base_url('backend/dashboard') ?>" class="brand-link">
        <img src="<?= base_url('template/backend/dist/img/AdminLTELogo.png') ?>" alt="Split Bill Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Split Bill Keluarga</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php
                $usernameParam = user()->username ?? 'User';
                $fullnameParam = user()->fullname ?? '';
                $userImage     = user()->user_image ?? '';
                
                // Logic Fallback Avatar Initials
                $sourceName = !empty($fullnameParam) ? $fullnameParam : $usernameParam;
                $textOnly = preg_replace('/[0-9]+$/', '', $sourceName);
                $textOnly = str_replace(['_', '.', '-'], ' ', $textOnly);
                $words    = preg_split('/\s+/', trim($textOnly), -1, PREG_SPLIT_NO_EMPTY);

                $initials = '';
                if (count($words) >= 2) {
                    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                } elseif (!empty($words)) {
                    $initials = strtoupper(substr($words[0], 0, 2));
                } else {
                    $initials = 'US';
                }
                ?>

                <?php if (!empty($userImage) && file_exists(FCPATH . $userImage)): ?>
                    <img src="<?= base_url($userImage) . '?t=' . time() ?>" class="img-circle elevation-2" alt="User Image" style="width: 34px; height: 34px; object-fit: cover;">
                <?php else: ?>
                    <div class="img-circle elevation-2 d-flex justify-content-center align-items-center bg-info text-white font-weight-bold" 
                         style="width: 34px; height: 34px; font-size: 0.85rem; user-select: none;">
                        <?= $initials ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="info">
                <a href="<?= base_url('backend/profil') ?>" class="d-block" style="white-space: normal;">
                    <?= esc(!empty($fullnameParam) ? $fullnameParam : $usernameParam) ?>
                </a>
                <small class="text-muted" style="display: block; margin-top: 2px;">
                    <?php 
                        $roleName = in_groups('admin') ? 'Group Admin' : 'Member';
                        echo esc($roleName . ' - ' . $usernameParam); 
                    ?>
                </small>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/dashboard') ?>" class="nav-link <?= uri_string() == 'backend/dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <li class="nav-header">MANAJEMEN KELOMPOK</li>

                <!-- Groups -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/groups') ?>" class="nav-link <?= strpos(uri_string(), 'backend/groups') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Groups & Anggota</p>
                    </a>
                </li>

                <!-- Trips & Periods -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/trips') ?>" class="nav-link <?= strpos(uri_string(), 'backend/trips') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-plane-departure"></i>
                        <p>Trips & Periode</p>
                    </a>
                </li>

                <li class="nav-header">TRANSAKSI & KEUANGAN</li>

                <!-- Transactions -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/transactions') ?>" class="nav-link <?= strpos(uri_string(), 'backend/transactions') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>Transaksi</p>
                    </a>
                </li>

                <!-- Settlements -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/settlements') ?>" class="nav-link <?= strpos(uri_string(), 'backend/settlements') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                        <p>Settlement Saldo</p>
                    </a>
                </li>

                <li class="nav-header">PENGATURAN</li>
                
                <!-- Profil -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/profil') ?>" class="nav-link <?= strpos(uri_string(), 'backend/profil') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Profil Akun</p>
                    </a>
                </li>
                
                <!-- Logout -->
                <li class="nav-item">
                    <a href="<?= base_url('logout') ?>" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                        <p class="text-danger">Logout</p>
                    </a>
                </li>
                
            </ul>
        </nav>
    </div>
</aside>
