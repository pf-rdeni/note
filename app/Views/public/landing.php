<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Split Bill Keluarga | Kelola Keuangan Perjalanan Bersama</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- Theme style (Bootstrap utility classes) -->
    <link rel="stylesheet" href="<?= base_url('template/backend/dist/css/adminlte.min.css') ?>">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #e0e7ff;
            --dark: #0f172a;
            --gray-light: #f8fafc;
            --text-muted: #64748b;
        }

        body {
            background-color: #f8fafc;
            color: var(--dark);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Blobs in Background */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            z-index: -1;
            opacity: 0.55;
            animation: floatBlob 12s infinite alternate ease-in-out;
        }
        .blob-1 {
            width: 350px;
            height: 350px;
            background: #c7d2fe; /* Indigo soft */
            top: -100px;
            left: -100px;
        }
        .blob-2 {
            width: 450px;
            height: 450px;
            background: #dbeafe; /* Blue soft */
            bottom: -150px;
            right: -100px;
            animation-delay: 2s;
        }
        .blob-3 {
            width: 300px;
            height: 300px;
            background: #fbcfe8; /* Rose soft */
            top: 40%;
            left: 30%;
            animation-delay: 4s;
        }

        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 30px) scale(1.15); }
        }

        /* Top Header */
        header {
            padding: 24px 0;
            z-index: 10;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .brand-logo {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            letter-spacing: -0.5px;
            transition: transform 0.2s;
        }
        .brand-logo:hover {
            transform: scale(1.02);
            color: var(--dark);
            text-decoration: none;
        }
        .brand-logo i {
            background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-right: 8px;
        }

        /* Hero Wrapper */
        .hero-section {
            max-width: 1100px;
            margin: 0 auto;
            padding: 60px 24px 100px;
            text-align: center;
        }

        /* Feature Badge */
        .hero-badge {
            background-color: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.15);
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 99px;
            display: inline-flex;
            align-items: center;
            margin-bottom: 24px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Typography */
        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -1.5px;
            color: var(--dark);
            max-width: 800px;
            margin: 0 auto 20px;
        }
        .hero-title span {
            background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero-subtitle {
            font-size: 1.15rem;
            color: var(--text-muted);
            max-width: 620px;
            margin: 0 auto 40px;
            line-height: 1.6;
            font-weight: 400;
        }

        /* CTA Buttons */
        .cta-group {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-bottom: 70px;
        }
        .btn-premium {
            padding: 14px 28px;
            font-weight: 600;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
        }
        .btn-premium-primary {
            background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);
            color: #ffffff !important;
            border: none;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }
        .btn-premium-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
        }
        .btn-premium-secondary {
            background: #ffffff;
            color: var(--dark) !important;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }
        .btn-premium-secondary:hover {
            background-color: var(--gray-light);
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        /* Features Cards Grid */
        .features-grid {
            margin-top: 20px;
        }
        .feature-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            padding: 32px;
            text-align: left;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            background: #ffffff;
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.08);
            border-color: rgba(99, 102, 241, 0.2);
        }
        .feature-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: var(--primary);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .feature-card:hover .feature-icon-wrapper {
            background: var(--primary);
            color: #ffffff;
            transform: scale(1.05);
        }
        .feature-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }
        .feature-desc {
            font-size: 0.92rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 0;
        }

        /* Footer */
        footer {
            border-top: 1px solid rgba(226, 232, 240, 0.8);
            padding: 30px 24px;
            text-align: center;
            margin-top: 40px;
        }
        .footer-text {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-bottom: 0;
            font-weight: 500;
        }

        /* Responsive Mobile Adjustments */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.2rem;
                letter-spacing: -1px;
            }
            .hero-subtitle {
                font-size: 1rem;
            }
            .cta-group {
                flex-direction: column;
                gap: 12px;
                max-width: 320px;
                margin: 0 auto 50px;
            }
            .btn-premium {
                width: 100%;
                padding: 12px 24px;
            }
            .hero-section {
                padding: 40px 16px 60px;
            }
            .feature-card {
                padding: 24px;
                margin-bottom: 16px;
            }
        }
    </style>
</head>
<body>

    <!-- Blobs in background -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- Header Navigation -->
    <header>
        <div class="header-container">
            <a href="<?= base_url() ?>" class="brand-logo">
                <i class="fas fa-wallet mr-2"></i><span>Split Bill</span>
            </a>
        </div>
    </header>

    <!-- Hero & Features Content -->
    <main class="hero-section">
        <div class="hero-badge">
            <i class="fas fa-sparkles mr-1 text-warning"></i> Kelola Keuangan Praktis
        </div>
        <h1 class="hero-title">
            Kelola Talangan & <span>Split Bill</span> Keluarga Tanpa Ribet
        </h1>
        <p class="hero-subtitle">
            Solusi cerdas kalkulasi otomatis penagihan yang adil untuk perjalanan (trip) keluarga atau kelompok. Transparan, cepat, dan teratur.
        </p>

        <!-- CTA Groups -->
        <div class="cta-group">
            <a href="<?= url_to('login') ?>" class="btn-premium btn-premium-primary">
                <i class="fas fa-sign-in-alt mr-2"></i>Masuk ke Aplikasi
            </a>
            <a href="<?= url_to('register') ?>" class="btn-premium btn-premium-secondary">
                <i class="fas fa-user-plus mr-2"></i>Daftar Akun Baru
            </a>
        </div>

        <!-- Features grid (2x2 cards layout) -->
        <div class="features-grid text-left">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h4 class="feature-title">Multi-Tenant & Grup</h4>
                        <p class="feature-desc">Pisahkan data keuangan antar grup perjalanan Anda dengan aman, teratur, dan terisolasi.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <h4 class="feature-title">Kalkulasi Otomatis</h4>
                        <p class="feature-desc">Perhitungan pembagian Shared & Individual Adjustment yang presisi untuk nominal penagihan lunas secara instan.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-history"></i>
                        </div>
                        <h4 class="feature-title">Riwayat & Bukti</h4>
                        <p class="feature-desc">Rekapitulasi transaksi yang detail dan transparan, lengkap dengan tanggal, deskripsi, & preview struk.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4 class="feature-title">Konfirmasi Terima</h4>
                        <p class="feature-desc">Unggah bukti transfer Anda, tunggu verifikasi satu tombol dari penerima, dan saldo otomatis seimbang.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p class="footer-text">&copy; <?= date('Y') ?> Split Bill Keluarga. Developed by Deni Rusandi.</p>
    </footer>

</body>
</html>
