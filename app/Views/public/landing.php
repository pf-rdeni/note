<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Split Bill Keluarga | Kelola Keuangan Perjalanan Bersama</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- Theme style (Bootstrap + AdminLTE utility classes) -->
    <link rel="stylesheet" href="<?= base_url('template/backend/dist/css/adminlte.min.css') ?>">
    
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #ffffff;
            font-family: 'Source Sans Pro', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .landing-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            max-width: 800px;
            width: 100%;
            padding: 50px 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        .logo-icon {
            font-size: 4rem;
            color: #00e5ff;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .app-title {
            font-size: 2.8rem;
            font-weight: 700;
            letter-spacing: -1px;
            margin-bottom: 10px;
        }
        .app-subtitle {
            font-size: 1.2rem;
            color: #e0e0e0;
            margin-bottom: 40px;
            font-weight: 300;
        }
        .feature-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }
        .feature-icon {
            font-size: 1.8rem;
            color: #00e5ff;
            margin-right: 15px;
        }
        .feature-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .feature-desc {
            font-size: 0.9rem;
            color: #cccccc;
            margin-bottom: 0;
        }
        .btn-cta {
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-login {
            background-color: #00e5ff;
            color: #1e3c72;
            border: none;
        }
        .btn-login:hover {
            background-color: #00b8d4;
            color: #1e3c72;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 229, 255, 0.4);
        }
        .btn-register {
            background-color: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
        }
        .btn-register:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            transform: translateY(-2px);
        }
        .footer-text {
            margin-top: 40px;
            font-size: 0.85rem;
            color: #a0aec0;
        }
    </style>
</head>
<body>

    <div class="landing-card text-center">
        <div>
            <i class="fas fa-wallet logo-icon"></i>
        </div>
        <h1 class="app-title">Split Bill Keluarga</h1>
        <p class="app-subtitle">Solusi cerdas kelola talangan & adjustment keuangan saat trip bersama keluarga secara adil dan otomatis.</p>
        
        <div class="row text-left mt-5">
            <div class="col-md-6">
                <div class="feature-item d-flex align-items-start">
                    <i class="fas fa-users-cog feature-icon"></i>
                    <div>
                        <div class="feature-title">Multi-Tenant & Grup</div>
                        <p class="feature-desc">Pisahkan data keuangan antar grup perjalanan Anda dengan aman dan terisolasi.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-item d-flex align-items-start">
                    <i class="fas fa-calculator feature-icon"></i>
                    <div>
                        <div class="feature-title">Kalkulasi Otomatis</div>
                        <p class="feature-desc">Perhitungan Shared & Individual Adjustment yang presisi untuk nominal penagihan lunas.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-item d-flex align-items-start">
                    <i class="fas fa-history feature-icon"></i>
                    <div>
                        <div class="feature-title">Riwayat Transaksi</div>
                        <p class="feature-desc">Rekapitulasi ala Excel yang mudah dipahami lengkap dengan tanggal, deskripsi, & bukti.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-item d-flex align-items-start">
                    <i class="fas fa-check-circle feature-icon"></i>
                    <div>
                        <div class="feature-title">Konfirmasi Settlement</div>
                        <p class="feature-desc">Unggah bukti transfer dan tandai pembayaran lunas hanya dalam satu tombol.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 d-flex justify-content-center flex-wrap" style="gap: 15px;">
            <a href="<?= url_to('login') ?>" class="btn btn-cta btn-login">
                <i class="fas fa-sign-in-alt mr-2"></i>Masuk Ke Aplikasi
            </a>
            <a href="<?= url_to('register') ?>" class="btn btn-cta btn-register">
                <i class="fas fa-user-plus mr-2"></i>Daftar Akun Baru
            </a>
        </div>

        <p class="footer-text">&copy; <?= date('Y') ?> Split Bill Keluarga. Developed by Antigravity.</p>
    </div>

</body>
</html>
