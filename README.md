# WiraTani - Platform Web Pertanian

## 1. Nama dan Deskripsi Proyek

**WiraTani** adalah aplikasi web yang menghubungkan petani, tengkulak, kios tani, dan buruh tani dalam satu platform. Aplikasi ini mendukung lelang hasil panen, pencatatan keuangan per musim tanam, pemesanan sarana produksi pertanian, serta publikasi lowongan kerja harian.

Proyek ini dibuat untuk Praktikum Pemrograman Web I dan mendukung **SDG 8: Pekerjaan Layak dan Pertumbuhan Ekonomi**.

## 2. Latar Belakang dan Tujuan

Petani sering memiliki keterbatasan informasi pembanding harga panen, pencatatan modal yang belum rapi, dan akses terbatas ke katalog sarana produksi. Sementara itu, informasi pekerjaan buruh tani musiman sering tersebar dari mulut ke mulut dan upahnya tidak selalu jelas.

WiraTani bertujuan untuk:

- Membantu membentuk harga panen melalui penawaran yang terbuka.
- Membantu petani mencatat pendapatan dan biaya serta melihat laba/rugi per musim.
- Mempermudah petani mencari dan memesan produk atau layanan saprotan dari kios.
- Menyediakan informasi lowongan kerja tani dan upah harian secara terbuka.

## 3. Fitur dan Modul

Ruang lingkup proyek terdiri dari modul-modul berikut:

1. **Autentikasi multi-role** - registrasi, login, logout, dan pembatasan akses untuk petani, tengkulak, dan kios.
2. **Manajemen lelang panen** - petani mengelola hasil panen yang akan dilelang.
3. **Pasar lelang** - tengkulak mencari dan melihat detail panen yang aktif.
4. **Penawaran lelang (bidding)** - tengkulak mengirim dan melihat penawaran.
5. **Penetapan pemenang** - petani memilih penawaran dan menyelesaikan lelang.
6. **ERP dan musim tanam** - petani mengelola musim, mencatat biaya, serta melihat rekap pendapatan dan laba/rugi.
7. **Pemesanan saprotan** - petani melihat katalog dan mengirim pesanan ke kios.
8. **Katalog dan pesanan kios** - kios mengelola produk, stok, dan pesanan masuk.
9. **Lowongan kerja** - petani membuat dan mengelola lowongan buruh tani.
10. **Bursa kerja publik** - masyarakat melihat lowongan terbuka, detail pekerjaan, dan kontak petani tanpa perlu login.

## 4. Teknologi yang Digunakan

- **PHP Native** untuk backend.
- **MySQL atau MariaDB** untuk basis data.
- **HTML5**, **CSS3 murni**, dan **JavaScript vanilla** untuk antarmuka.
- Apache sebagai web server (disediakan oleh XAMPP).

Proyek ini tidak menggunakan framework atau library pihak ketiga. Skema basis data mendukung MySQL 5.7+ atau MariaDB 10.3+.

## 5. Instalasi dan Konfigurasi

### Persyaratan

- XAMPP dengan Apache, PHP, ekstensi `mysqli`, dan MySQL/MariaDB.
- Git jika ingin mengambil proyek melalui repository.

### Langkah instalasi

1. Tempatkan atau clone folder proyek ke dalam direktori `htdocs` XAMPP. Contoh:

   ```text
   C:\xampp\htdocs\WiraTani_Project-Praktikum-Web
   ```

2. Buka XAMPP Control Panel, lalu jalankan **Apache** dan **MySQL**.
3. Buka phpMyAdmin melalui `http://localhost/phpmyadmin`.
4. Pilih menu **Import**, lalu impor `database/schema.sql`. Skrip ini membuat database `db_pertanian`.
5. Sesuaikan koneksi database di `config/koneksi.php` dengan pengaturan XAMPP:

   ```php
   $host = 'localhost';
   $username = 'root';
   $password = '';
   $database = 'db_pertanian';
   ```

   Ubah nilai tersebut jika instalasi MySQL/MariaDB menggunakan akun, kata sandi, atau nama database yang berbeda.

> **Peringatan:** `database/schema.sql` menghapus lalu membuat ulang tabel di `db_pertanian`. Gunakan pada database baru/kosong atau buat cadangan data sebelum mengimpornya kembali.

### Akun demo (opsional)

Untuk membuat akun uji, impor `database/seed_akun.sql` setelah `schema.sql`. Semua akun demo menggunakan kata sandi `password123`.

| Peran | Email |
|---|---|
| Petani | `budi@wiratani.test` |
| Petani | `siti@wiratani.test` |
| Tengkulak | `hendra@wiratani.test` |
| Tengkulak | `agus@wiratani.test` |
| Kios | `tanisubur@wiratani.test` |
| Kios | `sarimakmur@wiratani.test` |

Akun dan kata sandi tersebut hanya untuk pengujian lokal, bukan untuk penggunaan produksi.

## 6. Menjalankan Proyek

1. Pastikan Apache dan MySQL aktif di XAMPP.
2. Buka alamat proyek di browser. Jika folder proyek berada langsung di `htdocs`, gunakan:

   ```text
   http://localhost/WiraTani_Project-Praktikum-Web/
   ```

   Jika folder proyek berada di `htdocs\prak`, gunakan:

   ```text
   http://localhost/prak/WiraTani_Project-Praktikum-Web/
   ```

3. Daftar akun melalui halaman registrasi atau masuk menggunakan akun demo.
4. Gunakan menu sesuai peran akun. Halaman bursa kerja publik dapat dibuka tanpa login melalui:

   ```text
   http://localhost/prak/WiraTani_Project-Praktikum-Web/publik/bursa.php
   ```

   Sesuaikan bagian URL tersebut dengan lokasi folder proyek di `htdocs`.
