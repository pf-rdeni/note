<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autentikasi | Split Bill Keluarga</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('template/backend/dist/css/adminlte.min.css') ?>">
    
    <style>
        body.login-page, body.register-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
        }
        .login-box, .register-box {
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
            border: none;
        }
        .card-header {
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
            background-color: #ffffff;
            border-bottom: 1px solid #f4f6f9;
        }
        .btn-primary {
            background-color: #667eea;
            border-color: #667eea;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #5a6fd6;
            border-color: #5a6fd6;
            transform: translateY(-1px);
        }
    </style>
    <?= $this->renderSection('pageStyles') ?>
</head>
<body class="hold-transition login-page">

    <?= $this->renderSection('main') ?>

<!-- jQuery -->
<script src="<?= base_url('template/backend/plugins/jquery/jquery.min.js') ?>"></script>
<!-- Bootstrap 4 -->
<script src="<?= base_url('template/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- AdminLTE App -->
<script src="<?= base_url('template/backend/dist/js/adminlte.min.js') ?>"></script>
<?= $this->renderSection('pageScripts') ?>
</body>
</html>
