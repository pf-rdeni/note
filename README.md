# Split Bill Keluarga & Catatan Keuangan Kegiatan

Aplikasi berbasis web untuk mencatat pengeluaran bersama keluarga, membagi beban tagihan (*split bill*), dan melacak cicilan/hutang secara transparan dan teratur. Dibuat menggunakan **CodeIgniter 4**, **Bootstrap 4**, dan **Chart.js**.

---

## 🌟 Fitur Utama
1. **Manajemen Grup & Kegiatan (*Groups & Trips*)**
   - Mengelompokkan anggota keluarga dalam grup.
   - Membuat kegiatan/trip perjalanan (misal: *Ngopi*, *Libur Sekolah*, *Belanja Bulanan*).
2. **Pencatatan Transaksi & Pembagian Beban (*Split Bill*)**
   - Mencatat pengeluaran per kegiatan.
   - Pilihan pembagian rata (*shared*) ke seluruh anggota yang ikut atau nominal khusus (*individual*).
3. **Fitur Cicilan & Manajemen Tagihan [NEW]**
   - **Dua Tipe Sumber**: Pinjaman Anggota (antar anggota grup) dan Pinjaman Pribadi (bersumber dari eksternal/bank/kartu kredit).
   - **Pembagian Nominal Fleksibel**: Bagi rata (*equal split*) atau nominal sama per orang (*fixed amount*).
   - **Penanganan Bagian Mandiri (Self-Portion)**: Jika pembuat cicilan membagi tagihan ke dirinya sendiri, sistem otomatis memisahkan bagian dirinya menjadi **Pinjaman Pribadi** (hutang eksternal bank) sedangkan bagian anggota lain dicatat sebagai **Pinjaman Anggota** (piutang Indra ke anggota tersebut).
   - **Role Toggle Switcher (Tampilan Peran)**: Kemudahan beralih tampilan antara **Sebagai Peminjam** (daftar kewajiban bayar Anda) dan **Sebagai Pemberi Pinjaman** (daftar piutang masuk Anda) dengan visualisasi dasbor kartu statistik yang adaptif.
   - **Filter Cerdas & Sinkron**: Dropdown filter kegiatan utama dan tabel proyeksi bulanan disinkronkan secara *client-side* menggunakan JQuery, dengan penyimpanan preferensi saringan filter terakhir pada `localStorage`.
   - **Edit & Rekalkulasi Otomatis**: Jika cicilan belum memiliki riwayat bayar, nilai nominal, bulan mulai, dan durasi dapat diubah bebas dan sistem akan menghitung ulang jadwal. Jika sudah ada angsuran terbayar, perubahan dibatasi pada catatan untuk melindungi integritas saldo historis.
4. **Dasbor Analisis Interaktif & Grafik Proyeksi**
   - Dasbor statistik global sisa pinjaman & piutang aktif, lengkap dengan rincian kewajiban bulan ini dan proyeksi bulan berikutnya (otomatis menampilkan status **Lunas** berwarna hijau jika bernilai Rp 0).
   - **Proyeksi Arus Kas 6 Bulan**: Grafik garis proyeksi arus kas masa depan (Tagihan Keluar vs Piutang Masuk).
   - **Tren Cicilan Per Item**: Grafik batang bertumpuk (*stacked bar chart*) interaktif untuk melihat rincian alokasi nominal cicilan Anda berdasarkan deskripsi item bulan demi bulan.

---

## 🗄️ Skema Database & Migrasi
Fitur cicilan didukung oleh tiga tabel utama:
1. **`installments`**: Menyimpan data induk cicilan (Nama, Tipe Sumber, Total Nominal, Peminjam, Pemberi Pinjaman, Kegiatan, Durasi, dsb).
2. **`installment_payments`**: Menyimpan jadwal pembayaran/angsuran per bulan (Tanggal Jatuh Tempo, Jumlah Angsuran, Status Bayar).
3. **`installment_group_payments`**: Menyimpan histori transaksi pelunasan bersama (menghubungkan pembayaran beberapa item sekaligus dalam satu kali transfer/aksi bayar bulanan).

Untuk membuat skema tabel secara otomatis, jalankan migrasi spark:
```bash
php spark migrate
```

---

## ⚙️ Persyaratan Sistem
- PHP v8.2 atau lebih tinggi
- MySQL / MariaDB v5.7 atau lebih tinggi
- Extension PHP yang wajib diaktifkan: `intl`, `mbstring`, `curl`, `mysqli`

---

## 🚀 Langkah Instalasi & Setup

1. **Clone & Persiapan File**
   Buka folder project di web server lokal (seperti Laragon atau XAMPP).
   
2. **Atur Environment Variables (`.env`)**
   Salin file `env` menjadi `.env` di root direktori:
   ```bash
   cp env .env
   ```
   Buka `.env` dan atur konfigurasi database serta baseURL Anda:
   ```env
   database.default.hostname = localhost
   database.default.database = note
   database.default.username = root
   database.default.password = 
   database.default.DBDriver = MySQLi
   
   app.baseURL = 'http://localhost/note/public/'
   app.forceGlobalSecureRequests = false
   ```

3. **Jalankan Instalasi Dependency**
   ```bash
   composer install
   ```

4. **Jalankan Database Migrations**
   ```bash
   php spark migrate
   ```

5. **Akses Aplikasi**
   Arahkan virtual host server lokal Anda ke direktori `/public` dari project ini (misal: `http://localhost/note/public/`). Login menggunakan akun anggota yang terdaftar di database.

---

## 📅 Pengaturan Zona Waktu
Aplikasi disetel menggunakan Zona Waktu Indonesia Barat (**WIB**) dengan wilayah `'Asia/Jakarta'`. Seluruh pencatatan transaksi, tanggal pembuatan data, dan pelunasan angsuran mengacu pada tanggal lokal Indonesia secara akurat. Pengaturan ini dapat disesuaikan pada berkas [App.php](app/Config/App.php):
```php
public string $appTimezone = 'Asia/Jakarta';
```
