<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aplikasi Split Bill Keluarga">
    <meta name="author" content="Deni Rusandi">
    
    <title><?= $title ?? 'Split Bill Keluarga' ?></title>
    <link rel="icon" href="<?= base_url('template/backend/dist/img/AdminLTELogo.png') ?>" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="<?= base_url('template/backend/dist/img/AdminLTELogo.png') ?>">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/fontawesome-free/css/all.min.css') ?>">
    
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('template/backend/dist/css/adminlte.min.css') ?>">
    
    <!-- DataTables -->
    <!-- DataTables -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') ?>">
    
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/select2/css/select2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') ?>">
    
    <!-- Custom CSS -->
    <style>
        /* =============================================
           MOBILE-FIRST GLOBAL STYLES
           ============================================= */

        /* Dark Mode Support */
        .dark-mode {
            --bg-primary: #343a40;
            --bg-secondary: #3f474e;
            --text-primary: #fff;
        }
        
        /* Card Styles */
        .small-box {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        /* Sidebar Active */
        .nav-sidebar .nav-link.active {
            background-color: #007bff !important;
            color: #fff !important;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* =============================================
           BOTTOM NAVIGATION BAR (Mobile Only)
           ============================================= */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #343a40;
            z-index: 1030;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.25);
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .mobile-bottom-nav .nav-items {
            display: flex;
            height: 100%;
            align-items: stretch;
        }
        .mobile-bottom-nav .nav-item-btn {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            font-size: 10px;
            font-weight: 500;
            transition: color 0.2s, background 0.2s;
            border: none;
            background: transparent;
            cursor: pointer;
            padding: 4px 2px;
            position: relative;
        }
        .mobile-bottom-nav .nav-item-btn i {
            font-size: 18px;
            margin-bottom: 3px;
            display: block;
        }
        .mobile-bottom-nav .nav-item-btn.active {
            color: #4fc3f7;
        }
        .mobile-bottom-nav .nav-item-btn.active::after {
            content: '';
            position: absolute;
            top: 0;
            left: 20%;
            right: 20%;
            height: 3px;
            background: #4fc3f7;
            border-radius: 0 0 4px 4px;
        }
        .mobile-bottom-nav .nav-item-btn:hover {
            color: #90caf9;
            background: rgba(255,255,255,0.06);
        }


        /* =============================================
           MOBILE CONTENT PADDING
           ============================================= */
        @media (max-width: 767.98px) {
            .mobile-bottom-nav {
                display: block;
            }

            /* Padding bawah agar konten tidak tertutup bottom nav */
            .content-wrapper,
            section.content {
                padding-bottom: 85px !important;
            }
            .main-footer {
                display: none !important;
            }
            
            /* Kompakkan header content */
            .content-header {
                padding: 8px 12px 6px !important;
            }
            .content-header h1 {
                font-size: 1.1rem !important;
            }
            .content-header .breadcrumb {
                display: none !important;
            }

            /* Container fluid lebih ringkas */
            .container-fluid {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            /* Card mobile */
            .card {
                border-radius: 10px !important;
                margin-bottom: 12px !important;
                overflow: hidden;
            }
            .card-header {
                padding: 10px 14px !important;
            }
            .card-body {
                padding: 12px !important;
            }

            /* Info-box mobile */
            .info-box {
                min-height: 64px !important;
                border-radius: 10px !important;
                margin-bottom: 10px !important;
            }
            .info-box-icon {
                width: 60px !important;
                line-height: 60px !important;
                font-size: 1.5rem !important;
            }
            .info-box-content {
                padding: 8px 10px !important;
            }
            .info-box-text {
                font-size: 0.72rem !important;
            }
            .info-box-number {
                font-size: 1rem !important;
            }

            /* Modal mobile - full screen */
            .modal-dialog {
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                height: 100% !important;
            }
            .modal-content {
                border-radius: 0 !important;
                min-height: 100vh !important;
                display: flex;
                flex-direction: column;
            }
            .modal-body {
                flex: 1;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            .modal-footer {
                padding: 10px 14px !important;
                padding-bottom: calc(10px + env(safe-area-inset-bottom)) !important;
            }

            /* Override for Image Preview Modal on mobile to prevent cut-off */
            #modalViewImage.modal {
                padding-right: 0 !important;
            }
            #modalViewImage .modal-dialog {
                margin: 12px !important;
                max-width: calc(100% - 24px) !important;
                width: calc(100% - 24px) !important;
                height: auto !important;
                min-height: auto !important;
            }
            #modalViewImage .modal-content {
                border-radius: 12px !important;
                min-height: auto !important;
                height: auto !important;
            }
            #modalViewImage .modal-body {
                flex: none !important;
                height: 50vh !important;
                min-height: 280px !important;
            }

            /* Form controls mobile - touch friendly */
            .form-control,
            .custom-select {
                height: 44px !important;
                font-size: 16px !important; /* Prevent zoom on iOS */
                border-radius: 8px !important;
            }
            textarea.form-control {
                height: auto !important;
            }
            .select2-container .select2-selection--single {
                height: 44px !important;
            }
            .select2-container .select2-selection--single .select2-selection__rendered {
                line-height: 44px !important;
                font-size: 16px !important;
            }
            .select2-container .select2-selection--single .select2-selection__arrow {
                height: 44px !important;
            }

            /* Btn mobile - lebih besar touch target */
            .btn {
                min-height: 38px;
                padding: 6px 14px;
            }
            .btn-block {
                height: 46px;
                font-size: 1rem;
            }
            
            /* Buttons dalam card-header */
            .card-tools .btn {
                font-size: 0.75rem;
                padding: 5px 10px;
            }

            /* Small-box mobile */
            .small-box {
                margin-bottom: 10px !important;
            }
            .small-box h3 {
                font-size: 1.6rem !important;
            }

            /* Navbar sembunyikan item tidak penting */
            .navbar .d-none.d-sm-inline-block {
                display: none !important;
            }

            /* Sembunyikan sidebar di mobile (pakai bottom nav) */
            .main-sidebar {
                transform: translateX(-250px);
            }
            body:not(.sidebar-open) .main-sidebar {
                transform: translateX(-250px) !important;
            }

            /* Badge pill lebih mudah dibaca di mobile */
            .badge {
                font-size: 0.72rem;
            }
        }

        /* =============================================
           TRANSACTION PAGE MOBILE CARDS
           ============================================= */
        /* Transaction card list (pengganti table di mobile) */
        .txn-mobile-card {
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 10px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
            border-left: 4px solid #007bff;
            position: relative;
        }
        .txn-mobile-card.type-shared {
            border-left-color: #28a745;
        }
        .txn-mobile-card.type-individual {
            border-left-color: #17a2b8;
        }
        .txn-mobile-card .txn-amount {
            font-size: 1.15rem;
            font-weight: 700;
            color: #212529;
        }
        .txn-mobile-card .txn-meta {
            font-size: 0.78rem;
            color: #6c757d;
        }
        .txn-mobile-card .txn-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }
        .txn-mobile-card .txn-actions .btn {
            flex: 1;
            font-size: 0.8rem;
        }
        
        /* Dark mode untuk txn-mobile-card */
        .dark-mode .txn-mobile-card {
            background: #3f474e;
            color: #fff;
        }
        .dark-mode .txn-mobile-card .txn-meta {
            color: #adb5bd;
        }

        /* =============================================
           CAMERA CAPTURE UPLOAD AREA
           ============================================= */
        .receipt-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 16px;
            background: #f8f9fa;
            transition: border-color 0.2s, background 0.2s;
        }
        .receipt-upload-area:hover,
        .receipt-upload-area.dragover {
            border-color: #007bff;
            background: #e8f0fe;
        }
        .receipt-upload-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .receipt-upload-actions .btn-capture,
        .receipt-upload-actions .btn-gallery {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px 8px;
            border-radius: 10px;
            border: 1.5px solid;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            background: #fff;
            min-height: 70px;
            text-decoration: none;
        }
        .receipt-upload-actions .btn-capture {
            border-color: #28a745;
            color: #28a745;
        }
        .receipt-upload-actions .btn-capture:hover,
        .receipt-upload-actions .btn-capture:focus {
            background: #28a745;
            color: #fff;
        }
        .receipt-upload-actions .btn-gallery {
            border-color: #007bff;
            color: #007bff;
        }
        .receipt-upload-actions .btn-gallery:hover,
        .receipt-upload-actions .btn-gallery:focus {
            background: #007bff;
            color: #fff;
        }
        .receipt-upload-actions .btn-capture i,
        .receipt-upload-actions .btn-gallery i {
            font-size: 22px;
            margin-bottom: 4px;
            display: block;
        }
        .receipt-preview-img {
            max-width: 100%;
            max-height: 160px;
            border-radius: 8px;
            object-fit: cover;
            display: block;
            margin: 0 auto;
        }
        .receipt-preview-container {
            position: relative;
            display: none;
            text-align: center;
        }
        .receipt-preview-container .btn-remove-receipt {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #dc3545;
            color: white;
            border: none;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1;
        }
        .dark-mode .receipt-upload-area {
            background: #2d3238;
            border-color: #495057;
        }
        .dark-mode .receipt-upload-actions .btn-capture,
        .dark-mode .receipt-upload-actions .btn-gallery {
            background: #3f474e;
        }

        /* =============================================
           FILTER COLLAPSE MOBILE
           ============================================= */
        .mobile-filter-toggle {
            display: none;
        }
        @media (max-width: 767.98px) {
            .mobile-filter-toggle {
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
            .filter-collapsible {
                /* show as collapsible on mobile */
            }

            /* Tabel rekap - sembunyikan kolom tidak penting di mobile */
            .table-rekap-mobile-hide {
                display: none;
            }

            /* Settlement cards - full width */
            .settlement-card {
                margin-bottom: 8px;
            }
        }

        /* =============================================
           TOUCH IMPROVEMENTS
           ============================================= */
        /* Bigger tap targets */
        .list-group-item {
            padding: 12px 16px;
        }
        @media (max-width: 767.98px) {
            .list-group-item {
                padding: 14px 16px !important;
                font-size: 0.95rem;
            }
            /* Sembunyikan tabel transaksi desktop, tampilkan card mobile */
            div.txn-desktop-table, table.txn-desktop-table { display: none !important; }
            .txn-mobile-list { display: block; }

            /* Rekap table - horizontal scroll */
            .rekap-table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 8px;
            }
        }
        @media (min-width: 768px) {
            /* Tampilkan tabel desktop, sembunyikan card mobile */
            div.txn-desktop-table { display: block !important; }
            table.txn-desktop-table { display: table !important; }
            .txn-mobile-list { display: none; }
        }
    </style>
</head>
