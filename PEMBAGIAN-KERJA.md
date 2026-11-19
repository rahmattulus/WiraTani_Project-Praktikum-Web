# Pembagian Kerja dan Urutan Pengerjaan

Pelengkap `PRD-web-pertanian.md`. Dokumen ini menjawab tiga hal: **siapa mengerjakan apa**, **dalam urutan apa**, dan **kapan dianggap selesai**.

---

## 1. Anggota dan Peran

| Anggota | Peran | Fokus |
|---|---|---|
| **Rahmat** | Lead teknis (pekerjaan rumit dan kompleks) | Alur lelang end-to-end, logika transaksi database, helper keuangan, repo dan README |
| **Marvian** | Saprotan dan autentikasi | Auth multi-role, katalog kios, pesanan saprotan, video presentasi |
| **Agung** | ERP, tenaga kerja, dan tampilan | Layout bersama, ERP keuangan, lowongan, bursa kerja, Project Showcase |

**Prinsip pembagian**
1. Pekerjaan yang rumit (transaksi database, validasi bertingkat, integrasi antar modul) dipegang Rahmat.
2. Setiap anggota membawa 3 sampai 4 modul, dan semua memegang bagian backend dan UI-nya sendiri.
3. Semua anggota wajib memahami seluruh alur (syarat Buku Panduan), maka ada code review silang dan demo lintas modul di video.

## 2. Pembagian Modul dan Tabel

| Modul (sesuai PRD) | PIC | Tabel |
|---|---|---|
| 1. Autentikasi multi-role | Marvian | `users` |
| 2. Manajemen lelang panen | **Rahmat** | `hasil_panen` |
| 3. Eksplorasi pasar lelang | **Rahmat** | `hasil_panen` |
| 4. Bidding | **Rahmat** | `penawaran_lelang` |
| 5. Penetapan pemenang lelang | **Rahmat** | `hasil_panen`, `penawaran_lelang`, `transaksi_keuangan` |
| 6. ERP keuangan dan musim tanam | Agung | `musim_tanam`, `transaksi_keuangan` |
| 7. Pemesanan saprotan | Marvian | `pesanan_saprotan` |
| 8. Katalog, stok, dan pesanan kios | Marvian | `produk_saprotan`, `pesanan_saprotan` |
| 9. Lowongan kerja buruh tani | Agung | `lowongan_kerja` |
| 10. Bursa kerja publik | Agung | `lowongan_kerja` |

**Komponen bersama**

| Komponen | PIC | Catatan |
|---|---|---|
| Repo GitHub, struktur folder, aturan branch | Rahmat | |
| `schema.sql`, `config/koneksi.php`, helper dasar | Rahmat | Helper: flash message, redirect, format rupiah |
| Helper keuangan `catat_transaksi()` | Rahmat | Dipakai Rahmat (pemenang lelang) dan Marvian (pesanan selesai) |
| Layout bersama (header, sidebar, footer, CSS) | Agung | Satu desain untuk semua halaman |
| `auth_guard.php` (pembatasan akses per role) | Marvian | |
| `seed.sql` (data dummy realistis) | Rahmat (menggabungkan) | Tiap anggota menyumbang data untuk modulnya |

## 3. Kontrak Antar Anggota (harus disepakati sebelum Hari 7)

Dua modul bergantung pada kode orang lain. Kontraknya:

**a. `catat_transaksi()`** (dibuat Rahmat, dipakai Marvian)

```php
catat_transaksi($koneksi, $musim_tanam_id, $jenis, $kategori,
                $nominal, $tanggal, $keterangan = null,
                $hasil_panen_id = null, $pesanan_saprotan_id = null);
```
- Fungsi ini **tidak memulai transaksi sendiri**. Pemanggil yang memegang `begin_transaction` / `commit`, supaya perubahan status dan pencatatan keuangan berhasil atau gagal bersama.
- Mengembalikan `true` / `false`. Bila `hasil_panen_id` atau `pesanan_saprotan_id` sudah pernah tercatat (kolom `UNIQUE`), fungsi mengembalikan `false` dan tidak menggandakan.

**b. Guard role** (dibuat Marvian, dipakai semua)

```php
wajib_login('petani');   // atau 'tengkulak', 'kios'
```
- Bila belum login atau role salah, diarahkan ke login dengan flash message.
- Session menyimpan `$_SESSION['user_id']`, `['nama']`, `['role']`.

## 4. Urutan Pengerjaan Step by Step

Kolom **Selesai bila** adalah definisi selesai yang dicek sebelum lanjut.

### Fase 0: Persiapan (Hari 1)

| ID | Langkah | PIC | Pendukung | Selesai bila |
|---|---|---|---|---|
| S01 | Buat repo GitHub public, struktur folder sesuai PRD bagian 10, `.gitignore`, branch `main` | Rahmat | Semua clone | Ketiganya bisa clone dan push |
| S02 | Review `ERD-web-pertanian.md` dan `schema.sql`, sepakati perubahan, import ke MySQL lokal | Rahmat | Marvian, Agung | Ketiga database lokal identik |
| S03 | Buat `config/koneksi.php` dan helper dasar | Rahmat | | Halaman uji berhasil query ke tabel `users` |
| S04 | Sepakati desain tanpa framework: palet warna (variabel CSS `:root`), font sistem, ukuran jarak, gaya tombol/tabel/form/kartu/notifikasi | Agung | Semua setuju | Ada catatan keputusan di README sementara |

### Fase 1: Fondasi (Hari 2 sampai 3)

| ID | Langkah | PIC | Pendukung | Selesai bila |
|---|---|---|---|---|
| S05 | Layout bersama: header, sidebar per role, footer, dan `assets/css/style.css` buatan sendiri (grid/flexbox, kelas komponen: `.btn`, `.card`, `.table`, `.form-group`, `.alert`) | Agung | | Satu halaman contoh memakai `include` layout dan tampil benar; semua komponen dapat dipakai ulang oleh Rahmat dan Marvian |
| S06 | Register dan login (`password_hash`, `password_verify`), logout | Marvian | | Tiga role bisa daftar dan masuk |
| S07 | `auth_guard.php` dan `wajib_login()` sesuai kontrak | Marvian | Rahmat review | Akses halaman role lain ditolak |
| S08 | Landing page dan dashboard kosong per role | Agung | | Setiap role mendarat di dashboard-nya |
| S09 | Akun dummy per role untuk pengujian | Marvian | | Minimal 2 petani, 2 tengkulak, 2 kios |

### Fase 2: UI per modul dengan data dummy (Hari 4 sampai 6)

Semua halaman dibuat sebagai file `.php` dengan array data dummy. **Nama input form = nama kolom tabel.**

| ID | Langkah | PIC | Selesai bila |
|---|---|---|---|
| S10 | UI lelang petani: daftar, form tambah/ubah, detail lelang + daftar tawaran + tombol "Pilih pemenang" (dengan dropdown musim tanam) | Rahmat | Halaman bisa diklik dari menu |
| S11 | UI tengkulak: daftar pasar + pencarian, detail lelang, form penawaran | Rahmat | Idem |
| S12 | UI kios: daftar produk, form produk, daftar pesanan masuk dengan tombol aksi | Marvian | Idem |
| S13 | UI petani saprotan: katalog, form pesan (dengan dropdown musim tanam), riwayat pesanan | Marvian | Idem |
| S14 | UI ERP: daftar musim tanam, form musim, daftar transaksi, form biaya manual, kartu rekap laba/rugi | Agung | Idem |
| S15 | UI lowongan petani dan bursa kerja publik (daftar + detail + filter) | Agung | Idem |
| S16 | **Checkpoint Hari 6:** semua halaman terhubung lewat menu, dicoba bersama (demo internal) | Semua | Tidak ada tautan mati; daftar revisi UI tercatat |

### Fase 3: Backend per modul (Hari 7 sampai 10)

| Hari | ID | Langkah | PIC | Selesai bila |
|---|---|---|---|---|
| 7 | S17 | CRUD lelang petani; ubah/hapus hanya jika belum ada penawaran | Rahmat | Tersambung ke `hasil_panen`, cek kepemilikan `user_id` |
| 7 | S18 | CRUD produk kios | Marvian | Tersambung ke `produk_saprotan` |
| 7 | S19 | CRUD musim tanam dan CRUD lowongan | Agung | Dua tabel tersambung |
| 8 | S20 | Pasar lelang (daftar, pencarian, detail) dan bidding dengan validasi: harga ≥ harga dasar, lebih tinggi dari tawaran sendiri sebelumnya, lelang harus `aktif` | Rahmat | Penawaran tidak sah ditolak dengan pesan jelas |
| 8 | S21 | Helper `catat_transaksi()` sesuai kontrak, diserahkan ke Marvian **akhir Hari 8** | Rahmat | Diuji dengan insert manual; tidak menggandakan |
| 8 | S22 | Pembuatan pesanan saprotan oleh petani (snapshot harga, hitung total, sewa alat pakai durasi) dan riwayat | Marvian | Pesanan masuk dengan status `menunggu` |
| 8 | S23 | Bursa kerja publik (query, filter, kontak petani) dan transaksi manual ERP | Agung | Halaman publik tanpa login berjalan |
| 9 | S24 | **Penetapan pemenang** dalam satu transaksi: tawaran terpilih `menang`, lainnya `kalah`, lelang `selesai`, pendapatan dicatat lewat `catat_transaksi()`; plus pembatalan lelang | Rahmat | Gagal di tengah = tidak ada perubahan sebagian (diuji dengan sengaja) |
| 9 | S25 | Pesanan masuk kios: setujui (kurangi stok, tolak bila stok kurang), tolak, tandai selesai (memanggil `catat_transaksi()`) dalam transaksi | Marvian | Stok dan ERP konsisten |
| 9 | S26 | Rekap ERP per musim: total pendapatan, total biaya, laba/rugi, filter musim dan jenis | Agung | Angka cocok dengan hitungan manual dari data dummy |
| 10 | S27 | Uji **Alur 1** (lelang ke ERP) bersama; perbaikan bug | Rahmat | PRD bagian 12 alur 1 lulus |
| 10 | S28 | Uji **Alur 2** (saprotan ke ERP); perbaikan bug | Marvian | PRD bagian 12 alur 2 lulus |
| 10 | S29 | Uji **Alur 3** (lowongan ke bursa publik); perbaikan bug | Agung | PRD bagian 12 alur 3 lulus |

### Fase 4: Integrasi dan penyempurnaan (Hari 11 sampai 12)

| Hari | ID | Langkah | PIC | Selesai bila |
|---|---|---|---|---|
| 11 | S30 | Code review silang (lihat bagian 5) dan perbaikan temuan | Semua | Semua pull request ter-merge, tidak ada temuan terbuka |
| 11 | S31 | Uji menyeluruh tiap role dari awal sampai akhir; catat dan perbaiki bug | Semua | Daftar bug kosong atau hanya sisa kosmetik |
| 11 | S32 | `seed.sql` data dummy realistis (komoditas, harga, nama desa nyata), digabung Rahmat dari sumbangan tiap anggota | Rahmat | Database baru + seed langsung siap demo |
| 12 | S33 | Polishing UI: konsistensi, media query untuk ponsel, pesan error/sukses | Agung | Semua halaman tampil rapi di layar laptop dan ponsel tanpa library CSS |
| 12 | S34 | README final: deskripsi, latar belakang dan tujuan, daftar modul, teknologi, instalasi dan konfigurasi, cara menjalankan | Rahmat | Orang lain bisa menjalankan hanya dengan README |
| 12 | S35 | Skrip dan urutan bicara video; latihan demo penuh | Marvian | Latihan berjalan dalam kisaran 7 sampai 10 menit |
| 12 | S36 | Mulai desain Project Showcase | Agung | Kerangka slide dan palet siap |

### Fase 5: Publikasi dan pengumpulan (Hari 13 sampai 14)

| Hari | ID | Langkah | PIC | Selesai bila |
|---|---|---|---|---|
| 13 | S37 | Rekam video presentasi online (minimal 7 menit, ketiganya tampil), edit, unggah ke YouTube | Marvian | Tautan YouTube dapat dibuka tanpa login |
| 13 | S38 | Kumpulkan screenshot aplikasi dari data dummy final | Semua (tiap orang untuk modulnya) | Screenshot dikirim ke Agung |
| 13 | S39 | Selesaikan Project Showcase (PDF atau tautan desain): identitas aplikasi, masalah, solusi dan manfaat, highlight fitur, screenshot, profil tim, keterkaitan SDG 8 | Agung | PDF/tautan dapat diakses |
| 14 | S40 | Masing-masing mengunggah Showcase ke Instagram atau LinkedIn (3 unggahan publik), simpan tautan | Rahmat, Marvian, Agung | Tiga tautan terkumpul |
| 14 | S41 | Cek akhir: repo public + README, video bisa dibuka, tautan sosmed publik; kumpulkan tiga komponen ke asisten | Rahmat | Pengumpulan sebelum **23.49 WIB**; usahakan selesai sehari sebelumnya |

## 5. Code Review Silang

| Reviewer | Mereview | Fokus |
|---|---|---|
| Rahmat | Agung | Query rekap ERP, validasi input, keamanan |
| Agung | Marvian | Alur UI, konsistensi nama field dengan kolom |
| Marvian | Rahmat | Memahami alur lelang dan transaksi (sekaligus syarat "paham seluruh proyek") |

Selain itu, **Rahmat memeriksa ulang** kode transaksi stok di S25 karena menyangkut konsistensi data.

Checklist review per pull request:
- [ ] Query memakai prepared statement
- [ ] Output dari input pengguna memakai `htmlspecialchars()`
- [ ] Halaman dilindungi `wajib_login()` dengan role yang benar
- [ ] Ubah/hapus mengecek kepemilikan (`user_id`)
- [ ] Nama input form sama dengan nama kolom
- [ ] Tidak ada library atau framework (tidak ada `<script src>` / `<link href>` ke luar, tidak ada `vendor/`)

## 6. Batasan Native

Proyek **sepenuhnya native, tanpa library dan framework**. Berlaku untuk semua anggota:

- Dilarang: Composer, framework PHP, Tailwind, Bootstrap, jQuery, Font Awesome, Chart.js, dan sejenisnya, termasuk lewat CDN.
- Boleh: fitur bawaan PHP, HTML5, CSS3, JavaScript vanilla, font sistem, emoji dan SVG inline.
- Style komponen **hanya** ditambah di `assets/css/style.css` (milik Agung, lewat permintaan atau pull request). Jangan menulis CSS terpisah per halaman supaya tampilan seragam.
- Cek di setiap review pull request: tidak ada tag `<script src>` atau `<link href>` ke host luar.

## 7. Aturan Kerja Git

- Branch per fitur dengan awalan nama: `rahmat/lelang-crud`, `marvian/auth`, `agung/erp-rekap`.
- Dilarang push langsung ke `main`. Semua lewat pull request dan minimal satu reviewer.
- Pesan commit singkat dan jelas, mis. `feat: validasi harga tawar`, `fix: stok negatif saat pesanan disetujui`.
- Ambil `main` terbaru (`git pull`) sebelum mulai kerja setiap hari.
- File bersama (layout, helper, `schema.sql`) diubah hanya oleh PIC-nya; anggota lain meminta lewat chat.
- Perubahan skema database diumumkan ke semua, lalu `schema.sql` diperbarui bersamaan.

## 8. Titik Kritis Jadwal

| Risiko | Dampak | Pencegahan |
|---|---|---|
| `catat_transaksi()` terlambat | Marvian tidak bisa menyelesaikan S25 | Rahmat menyerahkan akhir Hari 8 (S21), sebelum S25 |
| Tabel berubah setelah UI selesai | Form dan query harus diubah | Skema dikunci akhir Hari 1; perubahan harus lewat kesepakatan bersama |
| Integrasi baru dites di akhir | Bug lelang ke ERP ketahuan terlambat | Uji alur tiap anggota di Hari 10, bukan Hari 11 |
| Dokumentasi menumpuk di akhir | Video dan Showcase terburu-buru | README dan skrip video mulai Hari 12; screenshot dikumpulkan Hari 13 |
| Satu anggota terhambat | Modul tidak selesai | Beri tahu di grup, anggota lain membantu; Rahmat menjadi cadangan untuk bagian rumit |

## 9. Pembagian Keluaran Akhir

| Keluaran | PIC | Keterlibatan semua |
|---|---|---|
| Repo GitHub + README | Rahmat | Marvian dan Agung menulis bagian fitur modul masing-masing |
| Video YouTube | Marvian | Ketiganya tampil dan berbicara |
| Project Showcase | Agung | Rahmat dan Marvian menyiapkan screenshot dan teks modulnya |
| Unggahan Instagram/LinkedIn | Masing-masing | Tiga unggahan publik |

**Susunan bicara di video** (urutan dari Buku Panduan):

| Bagian | Pembicara |
|---|---|
| Perkenalan tim, latar belakang dan masalah, tujuan dan manfaat | Marvian |
| Penjelasan modul lelang dan penetapan pemenang | Rahmat |
| Penjelasan modul saprotan dan kios | Marvian |
| Penjelasan modul ERP, lowongan, dan bursa kerja | Agung |
| Demo aplikasi: tiap orang mendemokan **alur modul milik orang lain**: Agung mendemokan Alur 1 (lelang ke ERP), Rahmat mendemokan Alur 2 (saprotan ke ERP), Marvian mendemokan Alur 3 (lowongan ke bursa) | Bergiliran |
| Keterkaitan dengan SDG 8 dan kesimpulan | Agung |

## 10. Pelacak Progres

Centang saat langkah selesai.

- [ ] Fase 0: S01 S02 S03 S04
- [ ] Fase 1: S05 S06 S07 S08 S09
- [ ] Fase 2: S10 S11 S12 S13 S14 S15 S16
- [ ] Fase 3: S17 S18 S19 S20 S21 S22 S23 S24 S25 S26 S27 S28 S29
- [ ] Fase 4: S30 S31 S32 S33 S34 S35 S36
- [ ] Fase 5: S37 S38 S39 S40 S41
