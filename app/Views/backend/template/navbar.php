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
            <a class="nav-link" href="<?= base_url('backend/help') ?>" title="Panduan & Bantuan">
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
