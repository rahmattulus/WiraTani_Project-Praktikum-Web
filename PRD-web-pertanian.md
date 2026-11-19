# PRD: WiraTani-Platform Web Pertanian (SDG 8)

> Dokumen ini adalah konteks utama untuk tahap coding. Setiap keputusan di sini mengikat: nama tabel, nama field, alur status, dan aturan bisnis harus dipakai konsisten di UI, backend, dan database.

\---

## 1\. Ringkasan Proyek

|Item|Isi|
|-|-|
|Mata kuliah|Praktikum Pemrograman Web I (Responsi 2, Proyek Akhir)|
|Tim|3 orang|
|Durasi|14 hari (batas kumpul: 2 minggu setelah asistensi, 23.49 WIB)|
|Stack|PHP Native, MySQL/MariaDB, HTML5, CSS3 murni, JavaScript vanilla|
|Batasan|**Sepenuhnya native: tanpa library dan tanpa framework** (lihat bagian 9)|
|SDG|SDG 8: Decent Work and Economic Growth|

**Deskripsi.** Aplikasi web yang menghubungkan petani, tengkulak, dan kios tani dalam satu platform: lelang hasil panen terbuka, pencatatan keuangan musim tanam (ERP sederhana), pemesanan sarana produksi pertanian (saprotan), dan bursa lowongan kerja harian untuk buruh tani.

## 2\. Latar Belakang dan Masalah

1. Harga panen sering ditentukan sepihak oleh tengkulak karena petani tidak punya pembanding harga.
2. Rantai distribusi panjang dan tidak transparan, sehingga margin petani kecil.
3. Petani tidak mencatat modal dan pendapatan secara rapi, sehingga laba/rugi per musim tidak diketahui.
4. Akses ke pupuk, bibit, dan sewa alat bergantung pada kios terdekat tanpa katalog yang jelas.
5. Buruh tani musiman mencari kerja lewat informasi dari mulut ke mulut, upah tidak transparan.

## 3\. Tujuan dan Manfaat

**Tujuan**

* Menyediakan lelang terbuka agar harga panen terbentuk dari persaingan penawaran.
* Menyediakan pencatatan keuangan sederhana per musim tanam.
* Mempermudah pemesanan saprotan ke kios tani.
* Menyediakan papan lowongan kerja harian yang terbuka dan transparan.

**Manfaat**

* Petani: harga lebih adil, tahu laba/rugi, kemudahan mencari buruh dan saprotan.
* Tengkulak: akses langsung ke daftar panen aktif.
* Kios tani: katalog digital dan pesanan terkelola.
* Buruh tani: informasi pekerjaan dan upah yang jelas.

**Keterkaitan SDG 8:** pekerjaan layak (bursa kerja dengan upah transparan), pertumbuhan ekonomi pedesaan (harga panen adil, efisiensi distribusi), dan pencatatan keuangan yang mendorong usaha tani yang berkelanjutan.

## 4\. Peran Pengguna (Roles)

|Role|Akun|Hak akses utama|
|-|-|-|
|`petani`|Ya|Lelang panen, pilih pemenang, ERP, pesan saprotan, buat lowongan|
|`tengkulak`|Ya|Lihat pasar lelang, kirim penawaran|
|`kios`|Ya|Kelola katalog/stok, kelola pesanan masuk|
|Publik (buruh tani)|Tidak perlu|Lihat bursa kerja dan kontak petani|

Satu akun hanya punya satu role. Role dipilih saat registrasi.

## 5\. Daftar Modul (10 Modul)

|No|Modul|Role|Ringkasan|
|-|-|-|-|
|1|Autentikasi multi-role|Semua|Register, login, logout, pembatasan akses per role|
|2|Manajemen lelang panen|Petani|CRUD hasil panen yang dilelang|
|3|Eksplorasi pasar lelang|Tengkulak|Daftar dan detail panen aktif|
|4|Bidding|Tengkulak|Kirim penawaran, lihat daftar tawaran transparan|
|5|Penetapan pemenang lelang|Petani|Pilih tawaran pemenang, tutup lelang|
|6|ERP keuangan dan musim tanam|Petani|Biaya, pendapatan, laba/rugi per musim|
|7|Pemesanan saprotan|Petani|Pesan produk/sewa alat ke kios|
|8|Katalog, stok, dan pesanan kios|Kios|CRUD produk, kelola pesanan masuk|
|9|Lowongan kerja buruh tani|Petani|CRUD lowongan|
|10|Bursa kerja publik|Publik|Daftar dan detail lowongan, kontak petani|

> Catatan: Modul 5 ditambahkan agar jumlah modul tidak pas di batas minimal (9) dan karena ERP membutuhkan penanda "lelang berhasil".

## 6\. Kebutuhan Fungsional per Modul

### 6.1 Autentikasi

* Register: nama, email (unik), password, nomor telepon, alamat/desa, role.
* Login memakai email + password; password disimpan dengan `password\_hash()`, diverifikasi dengan `password\_verify()`.
* Session menyimpan `user\_id`, `nama`, `role`.
* Setiap halaman terproteksi memanggil guard role; akses salah role diarahkan ke halaman login/dashboard dengan pesan.
* Logout menghancurkan session.

### 6.2 Manajemen Lelang Panen (Petani)

* Tambah, lihat, ubah, hapus hasil panen milik sendiri.
* Field: komoditas, estimasi tonase (kg), lokasi, tanggal panen, harga dasar (per kg), deskripsi (opsional), status.
* Ubah/hapus hanya boleh saat belum ada penawaran masuk.
* Petani melihat daftar lelang miliknya beserta jumlah penawaran.

### 6.3 Eksplorasi Pasar Lelang (Tengkulak)

* Menampilkan lelang berstatus `aktif` dari semua petani.
* Pencarian berdasarkan komoditas dan lokasi; urut terbaru atau harga dasar.
* Halaman detail: info panen, info petani, tawaran tertinggi saat ini, daftar tawaran.

### 6.4 Bidding (Tengkulak)

* Form penawaran: harga tawar per kg, metode pembayaran, armada angkut, catatan.
* Aturan: harga tawar >= harga dasar; lelang harus `aktif`.
* Satu tengkulak boleh menawar lagi, tetapi harga baru harus lebih tinggi dari tawarannya sebelumnya.
* Daftar tawaran terlihat transparan: nama tengkulak, harga per kg, waktu, catatan, urut harga tertinggi.

### 6.5 Penetapan Pemenang Lelang (Petani)

* Di halaman detail lelang milik petani: daftar tawaran + tombol "Pilih pemenang".
* Saat dipilih (dalam satu transaksi database):

  1. Tawaran terpilih berstatus `menang`, tawaran lain `kalah`.
  2. Lelang berstatus `selesai`.
  3. Pendapatan otomatis tercatat di `transaksi\_keuangan` (jenis `pendapatan`, nominal = harga tawar x estimasi tonase).
* Petani bisa membatalkan lelang (`dibatalkan`) bila belum ada pemenang.

### 6.6 ERP Keuangan dan Musim Tanam (Petani)

* CRUD musim tanam: nama musim, komoditas, tanggal mulai, tanggal selesai (opsional), status (`berjalan`/`selesai`).
* Catat biaya modal manual: kategori (bibit, pupuk, alat, tenaga kerja, lainnya), nominal, tanggal, keterangan, terhubung ke satu musim tanam.
* Pendapatan dari lelang sukses masuk otomatis (lihat 6.5) dan dikaitkan ke musim tanam yang dipilih petani.
* Biaya dari pesanan saprotan berstatus `selesai` masuk otomatis sebagai pengeluaran.
* Rekap per musim: total pendapatan, total biaya, laba/rugi bersih = pendapatan - biaya.
* Daftar transaksi dengan filter musim dan jenis.

### 6.7 Pemesanan Saprotan (Petani)

* Melihat katalog dari seluruh kios (filter kategori, nama kios, lokasi).
* Membuat pesanan: pilih produk, jumlah, untuk sewa alat tambahkan tanggal mulai dan durasi (hari), catatan.
* Pesanan mengurangi/mereservasi stok saat disetujui kios (lihat 6.8).
* Petani melihat riwayat dan status pesanannya.

### 6.8 Katalog, Stok, dan Pesanan Kios

* CRUD produk: nama, kategori (`pupuk`, `bibit`, `pestisida`, `sewa\_alat`), harga satuan, satuan (kg/sak/hari), stok, deskripsi.
* Pesanan masuk: daftar pesanan dengan aksi `setujui`, `tolak`, `tandai selesai`.
* Menyetujui pesanan mengurangi stok; jika stok tidak cukup, aksi ditolak sistem.
* Pesanan `selesai` memicu pencatatan biaya ke ERP petani (lihat 6.6).

### 6.9 Lowongan Kerja Buruh Tani (Petani)

* CRUD lowongan milik sendiri.
* Field: judul, jenis pekerjaan (tanam, panen, lainnya), lokasi, jumlah buruh dibutuhkan, upah harian, tanggal mulai, durasi (hari), deskripsi, status (`buka`/`ditutup`).

### 6.10 Bursa Kerja Publik

* Halaman tanpa login: daftar lowongan `buka`, filter jenis pekerjaan dan lokasi.
* Detail lowongan menampilkan upah harian dan kontak langsung petani (nama, nomor telepon).

## 7\. Aturan Bisnis dan Alur Status

**Lelang (`hasil\_panen.status`):** `aktif` -> `selesai` (ada pemenang) atau `dibatalkan`.

**Penawaran (`penawaran\_lelang.status`):** `menunggu` -> `menang` atau `kalah`.

**Pesanan saprotan (`pesanan\_saprotan.status`):** `menunggu` -> `disetujui` -> `selesai`; atau `menunggu` -> `ditolak`.

**Lowongan (`lowongan\_kerja.status`):** `buka` <-> `ditutup`.

**Aturan lain**

* Data milik petani/kios/tengkulak hanya dapat diubah oleh pemiliknya (cek `user\_id` di setiap query ubah/hapus).
* Lelang yang sudah `selesai` atau `dibatalkan` tidak menerima penawaran.
* Nominal uang disimpan sebagai `DECIMAL(15,2)`; tonase sebagai `DECIMAL(10,2)`.
* Penghapusan produk/lelang yang sudah punya relasi ditolak (atau memakai status nonaktif), jangan meninggalkan data yatim.

## 8\. Entitas Database (tingkat tinggi)

Skema lengkap dibuat pada tahap berikutnya. Daftar tabel yang disepakati:

|Tabel|Isi|
|-|-|
|`users`|Akun semua role|
|`hasil\_panen`|Lelang panen|
|`penawaran\_lelang`|Tawaran tengkulak pada lelang|
|`musim\_tanam`|Siklus musim tanam petani|
|`transaksi\_keuangan`|Pendapatan dan pengeluaran per musim (ERP)|
|`produk\_saprotan`|Katalog kios|
|`pesanan\_saprotan`|Pesanan petani ke kios|
|`lowongan\_kerja`|Lowongan buruh tani|

Relasi utama: `users` 1-N `hasil\_panen`; `hasil\_panen` 1-N `penawaran\_lelang`; `users` 1-N `musim\_tanam`; `musim\_tanam` 1-N `transaksi\_keuangan`; `users(kios)` 1-N `produk\_saprotan`; `produk\_saprotan` 1-N `pesanan\_saprotan`; `users(petani)` 1-N `lowongan\_kerja`.

## 9\. Kebutuhan Non-Fungsional

* **Keamanan:** prepared statement (mysqli/PDO) di semua query; `htmlspecialchars()` untuk semua output dari input pengguna; guard role di setiap halaman; validasi di server (bukan hanya di browser).
* **Konsistensi:** nama input form = nama kolom tabel.
* **Tampilan:** responsif (media query CSS buatan sendiri), satu desain bersama untuk semua halaman.
* **Batasan native (wajib):** tidak boleh memakai library atau framework apa pun, baik di backend maupun frontend.

  * Backend: hanya fitur bawaan PHP (`mysqli` atau `PDO`, session, `password\_hash`). Tanpa Composer, tanpa framework (Laravel dan sejenisnya), tanpa library pihak ketiga.
  * Frontend: CSS ditulis sendiri (tanpa Tailwind, Bootstrap, atau sejenisnya); JavaScript vanilla (tanpa jQuery dan sejenisnya); ikon memakai emoji atau SVG inline buatan sendiri (tanpa Font Awesome dan sejenisnya); grafik, bila ada, dibuat dengan HTML/CSS (mis. batang dari `div`), tanpa Chart.js dan sejenisnya.
  * Font memakai font sistem (`system-ui, sans-serif`), tanpa memuat dari layanan luar.
  * Kode hasil salin dari library tidak dipakai; semua kode ditulis oleh tim.
* **Kinerja:** cukup untuk demo (puluhan hingga ratusan baris data); tidak perlu optimasi lanjutan.
* **Pesan sistem:** notifikasi sukses/gagal setelah setiap aksi (flash message).

## 10\. Struktur Direktori

```
project-root/
├── config/
│   └── koneksi.php
├── includes/            # header, sidebar, footer, auth\_guard, helper
├── assets/              # css, js, img
├── auth/                # login, register, logout
├── petani/              # lelang, erp, saprotan, lowongan
├── tengkulak/           # pasar, bidding
├── kios/                # produk, pesanan
├── publik/              # bursa kerja
├── database/
│   └── schema.sql
├── index.php            # landing page
└── README.md
```

## 11\. Pembagian Kerja dan Urutan Pengerjaan

Dipisahkan ke berkas tersendiri (`PEMBAGIAN-KERJA.md`): pembagian peran per anggota dan urutan pengerjaan step by step.

## 12\. Alur Integrasi yang Wajib Berjalan (untuk demo)

1. **Lelang:** Petani buat panen -> Tengkulak menawar -> Petani pilih pemenang -> pendapatan otomatis muncul di ERP.
2. **Saprotan:** Kios tambah produk -> Petani memesan -> Kios menyetujui lalu menandai selesai -> biaya otomatis muncul di ERP.
3. **Tenaga kerja:** Petani buat lowongan -> halaman publik menampilkan lowongan dan kontak petani.

## 13\. Keluaran Akhir (sesuai Buku Panduan)

1. Repository GitHub public + `README.md` (nama dan deskripsi, latar belakang dan tujuan, daftar modul, teknologi, panduan instalasi dan konfigurasi, panduan menjalankan).
2. Video presentasi YouTube, minimal 7 menit, seluruh anggota tampil (profil tim, latar belakang, tujuan dan manfaat, modul dan fitur, demo, keterkaitan SDGs, kesimpulan).
3. Project Showcase (PDF atau tautan desain) + 3 unggahan publik di Instagram/LinkedIn (satu per anggota).

## 14\. Kriteria Selesai (Acceptance)

* \[ ] Minimal 9 modul berjalan, dan targetnya 10 modul.
* \[ ] Tiga alur integrasi di bagian 12 berjalan tanpa error.
* \[ ] Setiap role hanya bisa membuka halamannya sendiri.
* \[ ] Aturan bisnis bagian 7 tervalidasi di server.
* \[ ] Tidak ada library atau framework yang dipakai (bagian 9).
* \[ ] Data dummy realistis tersedia untuk demo.
* \[ ] Semua anggota bisa menjelaskan seluruh alur, tidak hanya modulnya sendiri.

## 15\. Di Luar Cakupan (Out of Scope)

* Pembayaran online atau integrasi payment gateway.
* Notifikasi real-time, email, atau WhatsApp.
* Pengiriman/ongkir otomatis dan pelacakan armada.
* Pelamaran buruh lewat sistem (buruh langsung menghubungi petani lewat kontak).
* Batas waktu lelang otomatis (lelang ditutup manual oleh petani).
* Aplikasi mobile native.

