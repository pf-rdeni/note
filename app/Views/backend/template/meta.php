<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aplikasi Split Bill Keluarga">
    <meta name="author" content="Antigravity">
    
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
    </style>
</head>
