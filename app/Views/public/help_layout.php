<!DOCTYPE html>
<html lang="id">
<?= $this->include('backend/template/meta'); ?>
<body class="hold-transition layout-top-nav">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white shadow-sm py-2">
            <div class="container">
                <a href="<?= base_url('/') ?>" class="navbar-brand">
                    <span class="brand-text font-weight-bold text-dark"><i class="fas fa-wallet text-primary mr-2"></i>Split Bill Keluarga</span>
                </a>
                
                <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
                    <li class="nav-item">
                        <?php if (logged_in()): ?>
                            <a href="<?= base_url('backend/dashboard') ?>" class="btn btn-outline-primary btn-sm rounded-pill font-weight-bold px-3" style="border-width: 2px;">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('login') ?>" class="btn btn-primary btn-sm rounded-pill font-weight-bold px-4" style="box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);">
                                <i class="fas fa-sign-in-alt mr-1"></i> Masuk / Login
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper py-5 bg-light">
            <section class="content">
                <div class="container">
                    <?= $this->renderSection('content'); ?>
                </div>
            </section>
        </div>
        
        <!-- Footer -->
        <footer class="main-footer text-center ml-0 py-3 bg-white border-top">
            <strong>Copyright &copy; <?= date('Y') ?> <a href="<?= base_url('/') ?>">Split Bill Keluarga</a>.</strong> All rights reserved.
        </footer>
    </div>
    
    <!-- Scripts -->
    <?= $this->include('backend/template/js'); ?>
    <?= $this->renderSection('scripts'); ?>
</body>
</html>
