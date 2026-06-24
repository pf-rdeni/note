<?php 
$layoutTemplate = logged_in() ? 'backend/template/template' : 'public/help_layout';
?>
<?= $this->extend($layoutTemplate) ?>
<?= $this->section('content') ?>

<!-- Help Page Header -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-info text-white border-0 shadow-sm mb-4 animate-fade-in" style="border-radius: 12px; overflow: hidden; position: relative;">
            <div style="position: absolute; right: -50px; bottom: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.06); border-radius: 50%; pointer-events: none;"></div>
            <div style="position: absolute; right: 80px; top: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none;"></div>
            
            <div class="card-body p-4 p-md-5 d-flex align-items-center">
                <div style="flex: 1;">
                    <span class="badge badge-light px-3 py-2 text-info font-weight-bold mb-3 shadow-xs">
                        <i class="fas fa-book-reader mr-1"></i> Pusat Panduan
                    </span>
                    <h2 class="font-weight-bold mb-2">Panduan & Bantuan Penggunaan</h2>
                    <p class="mb-0 text-white-50" style="font-size: 1.05rem; max-width: 700px;">
                        Pelajari cara kerja sistem, ikuti langkah penggunaan bertahap, dan cari jawaban untuk pertanyaan umum tentang **Split Bill Keluarga**.
                    </p>
                </div>
                <div class="d-none d-lg-block ml-4 text-center">
                    <i class="fas fa-info-circle fa-5x text-white-50 shadow-sm" style="opacity: 0.5; transform: rotate(-10deg);"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Content Area -->
<div class="row">
    <div class="col-12 mb-4">
        <!-- Interactive Search Bar -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
            <div class="card-body p-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #ced4da;">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                    <input type="text" id="docsSearch" class="form-control border-left-0 font-weight-bold text-dark" placeholder="Ketik kata kunci untuk mencari bantuan (misal: shared, transfer, whatsapp)..." style="border-radius: 0 8px 8px 0; border-color: #ced4da; height: 45px; font-size: 1rem;">
                </div>
            </div>
        </div>

        <!-- Documentation Card -->
        <div class="card card-info card-outline card-outline-tabs shadow-sm" style="border-radius: 10px; overflow: hidden;">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs nav-justified font-weight-bold" id="help-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="workflow-tab" data-toggle="pill" href="#workflow-content" role="tab" aria-controls="workflow-content" aria-selected="true">
                            <i class="fas fa-project-diagram mr-1 text-info"></i> Alur Kerja Sistem
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tutorial-tab" data-toggle="pill" href="#tutorial-content" role="tab" aria-controls="tutorial-content" aria-selected="false">
                            <i class="fas fa-list-ol mr-1 text-success"></i> Langkah demi Langkah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="faq-tab" data-toggle="pill" href="#faq-content" role="tab" aria-controls="faq-content" aria-selected="false">
                            <i class="fas fa-comments mr-1 text-warning"></i> Tanya Jawab (FAQ)
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4">
                <div class="tab-content" id="help-tabsContent">
                    
                    <!-- TAB 1: ALUR KERJA SISTEM -->
                    <div class="tab-pane fade show active" id="workflow-content" role="tabpanel" aria-labelledby="workflow-tab">
                        <div class="searchable-section">
                            <h4 class="font-weight-bold text-dark mb-3"><i class="fas fa-project-diagram mr-2 text-info"></i>Bagaimana Sistem Split Bill Bekerja?</h4>
                            <p class="text-secondary mb-4">
                                Aplikasi ini dirancang untuk menyelesaikan pencatatan keuangan bersama agar transparan dan adil. Sistem mengalirkan data secara runtut dari pembentukan kelompok hingga penyelesaian transfer akhir.
                            </p>

                            <!-- Custom HTML/CSS Flowchart -->
                            <div class="flowchart-container py-4 px-3 mb-4 text-center bg-light border" style="border-radius: 12px; overflow-x: auto;">
                                <div class="d-flex flex-column flex-md-row align-items-center justify-content-center flex-wrap" style="gap: 15px;">
                                    
                                    <!-- Step 1 -->
                                    <div class="flow-step-box p-3 bg-white shadow-xs border-top-info" style="width: 170px; border-radius: 8px; border-top: 3px solid #17a2b8;">
                                        <span class="badge badge-info mb-2">1. Kelompok</span>
                                        <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 0.9rem;">Buat Kelompok</h6>
                                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Tambahkan anggota keluarga.</p>
                                    </div>

                                    <div class="flow-arrow text-muted d-none d-md-block"><i class="fas fa-arrow-right fa-lg"></i></div>
                                    <div class="flow-arrow text-muted d-block d-md-none py-1"><i class="fas fa-arrow-down"></i></div>

                                    <!-- Step 2 -->
                                    <div class="flow-step-box p-3 bg-white shadow-xs border-top-success" style="width: 170px; border-radius: 8px; border-top: 3px solid #28a745;">
                                        <span class="badge badge-success mb-2">2. Kegiatan</span>
                                        <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 0.9rem;">Buat Kegiatan</h6>
                                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Tentukan event/kebutuhan.</p>
                                    </div>

                                    <div class="flow-arrow text-muted d-none d-md-block"><i class="fas fa-arrow-right fa-lg"></i></div>
                                    <div class="flow-arrow text-muted d-block d-md-none py-1"><i class="fas fa-arrow-down"></i></div>

                                    <!-- Step 3 -->
                                    <div class="flow-step-box p-3 bg-white shadow-xs border-top-warning" style="width: 170px; border-radius: 8px; border-top: 3px solid #ffc107;">
                                        <span class="badge badge-warning mb-2 text-dark">3. Periode</span>
                                        <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 0.9rem;">Atur Periode</h6>
                                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Membatasi transaksi bulanan.</p>
                                    </div>

                                    <div class="flow-arrow text-muted d-none d-md-block"><i class="fas fa-arrow-right fa-lg"></i></div>
                                    <div class="flow-arrow text-muted d-block d-md-none py-1"><i class="fas fa-arrow-down"></i></div>

                                    <!-- Step 4 -->
                                    <div class="flow-step-box p-3 bg-white shadow-xs border-top-primary" style="width: 170px; border-radius: 8px; border-top: 3px solid #007bff;">
                                        <span class="badge badge-primary mb-2">4. Transaksi</span>
                                        <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 0.9rem;">Catat Belanja</h6>
                                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Kalkulasi bagi rata otomatis.</p>
                                    </div>

                                    <div class="flow-arrow text-muted d-none d-md-block"><i class="fas fa-arrow-right fa-lg"></i></div>
                                    <div class="flow-arrow text-muted d-block d-md-none py-1"><i class="fas fa-arrow-down"></i></div>

                                    <!-- Step 5 -->
                                    <div class="flow-step-box p-3 bg-white shadow-xs border-top-danger" style="width: 170px; border-radius: 8px; border-top: 3px solid #dc3545;">
                                        <span class="badge badge-danger mb-2">5. Settlement</span>
                                        <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 0.9rem;">Bagi & Selesai</h6>
                                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Transfer saldo & lunas.</p>
                                    </div>

                                </div>
                            </div>

                            <h5 class="font-weight-bold text-dark mt-4 mb-3"><i class="fas fa-cogs mr-2 text-secondary"></i>Logika Utama Perhitungan Saldo</h5>
                            <div class="card shadow-none border bg-light" style="border-radius: 8px;">
                                <div class="card-body">
                                    <ul class="mb-0 text-secondary" style="padding-left: 20px; line-height: 1.6;">
                                        <li class="mb-2"><strong>Total Pengeluaran:</strong> Akumulasi dari semua transaksi yang dicatat dalam suatu periode kegiatan.</li>
                                        <li class="mb-2"><strong>Bagi Rata (Shared):</strong> Total biaya transaksi tipe shared dibagi rata dengan jumlah anggota aktif pada periode tersebut.</li>
                                        <li class="mb-2"><strong>Beban Kustom (Individual):</strong> Tagihan kustom yang dibebankan hanya kepada anggota tertentu saja (tidak dibagi rata ke semua).</li>
                                        <li class="mb-2"><strong>Saldo Akhir:</strong> Dihitung dengan rumus: <code class="bg-white px-2 py-1 text-primary border" style="border-radius: 4px; font-size: 0.9rem;">Saldo = Total Dibayarkan - (Bagi Rata + Beban Kustom)</code>.</li>
                                        <li><strong>Indikator Saldo:</strong>
                                            <ul>
                                                <li>Jika Saldo <strong class="text-success">Positif (+)</strong>, artinya anggota tersebut menalangi uang lebih banyak dan **harus menerima transfer**.</li>
                                                <li>Jika Saldo <strong class="text-danger">Negatif (-)</strong>, artinya anggota tersebut memiliki utang talangan dan **wajib mengirim transfer**.</li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: LANGKAH DEMI LANGKAH -->
                    <div class="tab-pane fade" id="tutorial-content" role="tabpanel" aria-labelledby="tutorial-tab">
                        <div class="searchable-section">
                            <h4 class="font-weight-bold text-dark mb-4"><i class="fas fa-list-ol mr-2 text-success"></i>Tahapan Menggunakan Aplikasi</h4>

                            <div class="timeline-tutorial">
                                
                                <!-- Step 1 -->
                                <div class="card shadow-none border mb-3 animate-slide-up" style="border-radius: 8px;">
                                    <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center">
                                        <span class="btn btn-sm btn-info font-weight-bold mr-2" style="border-radius: 50%; width: 28px; height: 28px; padding: 2px 0; pointer-events: none;">1</span>
                                        <h6 class="font-weight-bold mb-0 text-dark">Mengelola Kelompok & Anggota</h6>
                                    </div>
                                    <div class="card-body p-3 text-secondary" style="font-size: 0.9rem; line-height: 1.5;">
                                        <p class="mb-2">Sebelum mencatat pengeluaran, Anda harus mengelompokkan keluarga/teman dalam satu grup:</p>
                                        <ol class="mb-0" style="padding-left: 20px;">
                                            <li>Buka menu <strong>Kelompok</strong> dari menu navigasi kiri.</li>
                                            <li>Klik tombol <strong>Tambah Kelompok Baru</strong>, masukkan nama kelompok (misal: "Keluarga Utama").</li>
                                            <li>Setelah kelompok terbuat, buka detail kelompok tersebut, lalu tambahkan anggota keluarga berdasarkan email/username terdaftar.</li>
                                        </ol>
                                    </div>
                                </div>

                                <!-- Step 2 -->
                                <div class="card shadow-none border mb-3 animate-slide-up" style="border-radius: 8px;">
                                    <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center">
                                        <span class="btn btn-sm btn-success font-weight-bold mr-2" style="border-radius: 50%; width: 28px; height: 28px; padding: 2px 0; pointer-events: none;">2</span>
                                        <h6 class="font-weight-bold mb-0 text-dark">Mengatur Kegiatan & Periode</h6>
                                    </div>
                                    <div class="card-body p-3 text-secondary" style="font-size: 0.9rem; line-height: 1.5;">
                                        <p class="mb-2">Kegiatan memayungi kumpulan transaksi pengeluaran. Kegiatan memiliki periode aktif (misal bulanan):</p>
                                        <ol class="mb-0" style="padding-left: 20px;">
                                            <li>Buka menu <strong>Kegiatan & Periode</strong>.</li>
                                            <li>Klik <strong>Tambah Kegiatan</strong>, masukkan nama kegiatan (misal: "Uang Belanja Bulanan" atau "Liburan Jogja") dan pilih kelompok penanggung jawabnya.</li>
                                            <li>Di halaman detail kegiatan, tambahkan **Periode Baru** (misal: "Juni 2026") beserta rentang tanggal aktifnya.</li>
                                        </ol>
                                    </div>
                                </div>

                                <!-- Step 3 -->
                                <div class="card shadow-none border mb-3 animate-slide-up" style="border-radius: 8px;">
                                    <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center">
                                        <span class="btn btn-sm btn-warning font-weight-bold mr-2 text-dark" style="border-radius: 50%; width: 28px; height: 28px; padding: 2px 0; pointer-events: none;">3</span>
                                        <h6 class="font-weight-bold mb-0 text-dark">Mencatat Transaksi Pengeluaran</h6>
                                    </div>
                                    <div class="card-body p-3 text-secondary" style="font-size: 0.9rem; line-height: 1.5;">
                                        <p class="mb-2">Mulai catat transaksi pengeluaran belanja:</p>
                                        <ol class="mb-0" style="padding-left: 20px;">
                                            <li>Buka menu <strong>Transaksi</strong>, lalu pilih Kegiatan dan Periode aktif di bagian atas.</li>
                                            <li>Klik tombol **Tambah Transaksi**. Isi Tanggal, Deskripsi Belanja, dan Nominal Uang.</li>
                                            <li>Pilih pembayar di kolom **Dibayar Oleh (Payer)**.</li>
                                            <li>Tentukan Tipe Transaksi:
                                                <ul>
                                                    <li><strong>Shared (Bagi Rata):</strong> Total biaya dibagi rata secara otomatis ke semua anggota kelompok yang aktif.</li>
                                                    <li><strong>Individual (Kustom):</strong> Anda dapat menetapkan nominal tagihan kustom yang berbeda untuk setiap anggota.</li>
                                                </ul>
                                            </li>
                                            <li>Unggah foto struk belanjaan untuk bukti transparansi, lalu klik **Simpan**.</li>
                                        </ol>
                                    </div>
                                </div>

                                <!-- Step 4 -->
                                <div class="card shadow-none border mb-0 animate-slide-up" style="border-radius: 8px;">
                                    <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center">
                                        <span class="btn btn-sm btn-danger font-weight-bold mr-2" style="border-radius: 50%; width: 28px; height: 28px; padding: 2px 0; pointer-events: none;">4</span>
                                        <h6 class="font-weight-bold mb-0 text-dark">Penyelesaian Saldo (Settlement)</h6>
                                    </div>
                                    <div class="card-body p-3 text-secondary" style="font-size: 0.9rem; line-height: 1.5;">
                                        <p class="mb-2">Setelah semua transaksi dicatat dan periode ditutup/dikunci, selesaikan pembayaran talangan:</p>
                                        <ol class="mb-0" style="padding-left: 20px;">
                                            <li>Di bagian bawah halaman Transaksi, lihat bagian **Rekomendasi Transfer (Settlement)**.</li>
                                            <li>Sistem akan menyusun instruksi yang efisien (siapa harus membayar berapa ke siapa).</li>
                                            <li>Anda juga bisa membagikan rincian tagihan ini langsung ke grup WhatsApp dengan mengklik tombol **Bagikan ke WhatsApp** di header kartu rekap.</li>
                                            <li>Bagi anggota yang berutang, kirimkan uang ke pembayar, buka menu **Settlement**, unggah bukti transfer, lalu klik **Konfirmasi Bayar**.</li>
                                            <li>Penerima transfer wajib memverifikasi bukti tersebut lalu mengklik tombol **Konfirmasi Terima** agar status transfer berubah menjadi **Lunas**.</li>
                                        </ol>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: FAQ (TANYA JAWAB) -->
                    <div class="tab-pane fade" id="faq-content" role="tabpanel" aria-labelledby="faq-tab">
                        <div class="searchable-section">
                            <h4 class="font-weight-bold text-dark mb-3"><i class="fas fa-question-circle mr-2 text-warning"></i>Pertanyaan yang Sering Diajukan</h4>
                            
                            <div class="accordion" id="faqAccordion">
                                
                                <!-- FAQ 1 -->
                                <div class="card shadow-none border mb-2 faq-item" style="border-radius: 6px; overflow: hidden;">
                                    <div class="card-header bg-white p-2" id="headingOne">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left font-weight-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne" style="text-decoration: none; font-size: 0.95rem;">
                                                <i class="far fa-question-circle text-warning mr-2"></i> Apa perbedaan tipe transaksi Shared dan Individual?
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#faqAccordion">
                                        <div class="card-body text-secondary" style="font-size: 0.9rem; line-height: 1.5; border-top: 1px solid #e9ecef;">
                                            <strong>Shared (Bagi Rata)</strong> membagi rata nominal belanjaan ke semua anggota kelompok yang aktif pada periode tersebut secara otomatis. <br>
                                            Sementara <strong>Individual (Beban Kustom)</strong> digunakan jika barang yang dibeli hanya dikonsumsi/dibebankan kepada anggota tertentu. Anda bebas memasukkan nominal beban yang berbeda untuk masing-masing orang.
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 2 -->
                                <div class="card shadow-none border mb-2 faq-item" style="border-radius: 6px; overflow: hidden;">
                                    <div class="card-header bg-white p-2" id="headingTwo">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left font-weight-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" style="text-decoration: none; font-size: 0.95rem;">
                                                <i class="far fa-question-circle text-warning mr-2"></i> Mengapa nominal saldo saya di halaman transaksi bernilai negatif (-)?
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#faqAccordion">
                                        <div class="card-body text-secondary" style="font-size: 0.9rem; line-height: 1.5; border-top: 1px solid #e9ecef;">
                                            Nilai negatif (-) berarti total uang yang Anda bayarkan/talangi lebih kecil dari total beban belanjaan Anda di periode tersebut. Dengan kata lain, Anda **memiliki utang talangan** kepada anggota lain yang saldonya positif, dan Anda harus mengirim transfer pelunasan sesuai instruksi settlement.
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 3 -->
                                <div class="card shadow-none border mb-2 faq-item" style="border-radius: 6px; overflow: hidden;">
                                    <div class="card-header bg-white p-2" id="headingThree">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left font-weight-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree" style="text-decoration: none; font-size: 0.95rem;">
                                                <i class="far fa-question-circle text-warning mr-2"></i> Bagaimana cara menyelesaikan/melunasi saldo (Settlement)?
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                                        <div class="card-body text-secondary" style="font-size: 0.9rem; line-height: 1.5; border-top: 1px solid #e9ecef;">
                                            Sistem secara cerdas menyusun baris **Rekomendasi Transfer**. Silakan transfer sejumlah uang tertera ke rekening tujuan, lalu buka menu **Settlement**, unggah bukti transfer dan klik kirim. Penerima transfer kemudian harus mengklik **Konfirmasi Terima** di akun mereka agar status transfer diubah menjadi lunas.
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 4 -->
                                <div class="card shadow-none border mb-2 faq-item" style="border-radius: 6px; overflow: hidden;">
                                    <div class="card-header bg-white p-2" id="headingFour">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left font-weight-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour" style="text-decoration: none; font-size: 0.95rem;">
                                                <i class="far fa-question-circle text-warning mr-2"></i> Siapa saja yang bisa menambah, mengubah, dan menghapus transaksi?
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#faqAccordion">
                                        <div class="card-body text-secondary" style="font-size: 0.9rem; line-height: 1.5; border-top: 1px solid #e9ecef;">
                                            Setiap anggota kelompok yang statusnya aktif dapat mencatat transaksi baru. Namun, perubahan atau penghapusan transaksi hanya dapat dilakukan oleh **pembuat transaksi** tersebut atau **administrator kelompok** demi keamanan data.
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 5 -->
                                <div class="card shadow-none border mb-0 faq-item" style="border-radius: 6px; overflow: hidden;">
                                    <div class="card-header bg-white p-2" id="headingFive">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left font-weight-bold text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive" style="text-decoration: none; font-size: 0.95rem;">
                                                <i class="far fa-question-circle text-warning mr-2"></i> Bagaimana cara mengunci (settle) suatu periode agar tidak bisa diubah lagi?
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#faqAccordion">
                                        <div class="card-body text-secondary" style="font-size: 0.9rem; line-height: 1.5; border-top: 1px solid #e9ecef;">
                                            Penguncian periode dilakukan oleh **Administrator Kelompok** lewat menu **Kegiatan & Periode**. Di dalam detail kegiatan, klik tombol toggle status pada periode terkait untuk mengubahnya menjadi **Settled (Terkunci)**. Setelah terkunci, tidak ada anggota yang bisa menambah atau mengedit transaksi lagi pada periode tersebut.
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Real-time search filter
    $('#docsSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        
        // Filter searchable text blocks in workflow & step-by-step tutorial tabs
        $('.searchable-section').each(function() {
            // Find all blocks that can be matched
            $(this).find('.card, p, li').each(function() {
                const text = $(this).text().toLowerCase();
                if (text.indexOf(value) > -1) {
                    $(this).show();
                    // If it's a sub-item, make sure parent is shown
                    $(this).parents('.card').show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Filter FAQ Accordion items
        $('.faq-item').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.indexOf(value) > -1) {
                $(this).show();
                // Auto-expand FAQ collapse if searching specifically
                if (value.length > 2) {
                    $(this).find('.collapse').addClass('show');
                } else {
                    $(this).find('.collapse').removeClass('show');
                }
            } else {
                $(this).hide();
            }
        });
    });

    // Handle tab change reset search
    $('a[data-toggle="pill"]').on('shown.bs.tab', function() {
        $('#docsSearch').val('');
        $('.searchable-section').find('.card, p, li').show();
        $('.faq-item').show().find('.collapse').removeClass('show');
    });
});
</script>

<style>
/* CSS Animations */
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
.animate-slide-up {
    animation: slideUp 0.3s ease-out both;
}
.animate-slide-up:nth-child(1) { animation-delay: 0.05s; }
.animate-slide-up:nth-child(2) { animation-delay: 0.1s; }
.animate-slide-up:nth-child(3) { animation-delay: 0.15s; }
.animate-slide-up:nth-child(4) { animation-delay: 0.2s; }

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom Flowchart Arrows and Spacing */
.flow-arrow {
    font-size: 1.2rem;
}
.flow-step-box {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.flow-step-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06) !important;
}

/* Custom styles for select2 items inside centers */
.faq-item .btn-link:focus, .faq-item .btn-link:active {
    box-shadow: none;
}

/* Responsive justified tabs for mobile */
@media (max-width: 575.98px) {
    #help-tabs .nav-link {
        padding: 10px 4px !important;
        font-size: 0.82rem;
        text-align: center;
    }
    #help-tabs .nav-link i {
        display: block;
        margin-bottom: 4px;
        font-size: 1.1rem;
        margin-right: 0 !important;
    }
}
</style>
<?= $this->endSection() ?>
