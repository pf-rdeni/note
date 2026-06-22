<!DOCTYPE html>
<html lang="id">
<?= $this->include('backend/template/meta'); ?>
<?php
// Dark mode class dari localStorage di-handle oleh JavaScript
$bodyClass = 'hold-transition sidebar-mini layout-fixed';
?>
<body class="<?= $bodyClass ?>">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    
    <div class="wrapper">
        <!-- Navbar -->
        <?= $this->include('backend/template/navbar'); ?>
        
        <!-- Sidebar -->
        <?= $this->include('backend/template/sidebar'); ?>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Header (Page Title & Breadcrumb) -->
            <?= $this->include('backend/template/header'); ?>
            
            <!-- Main Content -->
            <section class="content">
                <div class="container-fluid">
                    <?= $this->renderSection('content'); ?>
                </div>
            </section>
        </div>
        
        <!-- Footer -->
        <?= $this->include('backend/template/footer'); ?>

        <!-- Mobile Bottom Navigation Bar -->
        <nav class="mobile-bottom-nav" id="mobileBottomNav">
            <div class="nav-items">
                <a href="<?= base_url('backend/dashboard') ?>" 
                   class="nav-item-btn <?= uri_string() == 'backend/dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= base_url('backend/trips') ?>" 
                   class="nav-item-btn <?= strpos(uri_string(), 'backend/trips') !== false ? 'active' : '' ?>">
                    <i class="fas fa-plane-departure"></i>
                    <span>Trip</span>
                </a>
                <a href="<?= base_url('backend/transactions') ?>" 
                   class="nav-item-btn <?= strpos(uri_string(), 'backend/transactions') !== false ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Transaksi</span>
                </a>
                <a href="<?= base_url('backend/settlements') ?>" 
                   class="nav-item-btn <?= strpos(uri_string(), 'backend/settlements') !== false ? 'active' : '' ?>">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Settlement</span>
                </a>
                <a href="<?= base_url('backend/profil') ?>" 
                   class="nav-item-btn <?= strpos(uri_string(), 'backend/profil') !== false ? 'active' : '' ?>">
                    <i class="fas fa-user"></i>
                    <span>Profil</span>
                </a>
            </div>
        </nav>
    </div>
    
    <!-- Scripts -->
    <?= $this->include('backend/template/js'); ?>
    
    <!-- Additional Scripts from Child Views -->
    <?= $this->renderSection('scripts'); ?>
</body>
</html>
