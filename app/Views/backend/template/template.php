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
                <a href="<?= base_url('backend/groups') ?>" 
                   class="nav-item-btn <?= strpos(uri_string(), 'backend/groups') !== false ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Grup</span>
                </a>
                <a href="<?= base_url('backend/trips') ?>" 
                   class="nav-item-btn <?= strpos(uri_string(), 'backend/trips') !== false ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Kegiatan</span>
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

        <!-- Modal View Struk / Bukti -->
        <div class="modal fade" id="modalViewImage" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1070;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
                    <div class="modal-header border-bottom-0 bg-light d-flex align-items-center justify-content-between py-2 px-3">
                        <h5 class="modal-title font-weight-bold text-dark text-sm mb-0">
                            <i class="fas fa-receipt mr-1 text-primary"></i> <span class="d-none d-sm-inline">Preview Lampiran</span>
                        </h5>
                        <div class="d-flex align-items-center">
                            <!-- Zoom Controls -->
                            <div class="btn-group mr-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary px-2" id="btnZoomOut" title="Zoom Out">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary px-2" id="btnZoomReset" title="Reset Zoom">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary px-2" id="btnZoomIn" title="Zoom In">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <a href="#" id="btnDownloadImage" class="btn btn-sm btn-outline-primary px-2" title="Unduh Gambar" download>
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                            <button type="button" class="close py-1 px-2 m-0" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem; line-height: 1;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body d-flex align-items-center justify-content-center p-0" style="overflow: hidden; background-color: #1e1e1e; position: relative; height: 60vh; min-height: 350px;">
                        <img src="" id="modalViewImageTarget" class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: contain; transition: transform 0.15s ease-out; transform-origin: center center; user-select: none; -webkit-user-drag: none; cursor: default;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <?= $this->include('backend/template/js'); ?>
    
    <!-- Additional Scripts from Child Views -->
    <?= $this->renderSection('scripts'); ?>
</body>
</html>
