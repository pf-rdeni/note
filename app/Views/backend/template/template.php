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
    </div>
    
    <!-- Scripts -->
    <?= $this->include('backend/template/js'); ?>
    
    <!-- Additional Scripts from Child Views -->
    <?= $this->renderSection('scripts'); ?>
</body>
</html>
