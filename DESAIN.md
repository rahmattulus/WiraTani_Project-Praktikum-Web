# DESAIN: Pedoman Tampilan UI Platform Web Pertanian

Patokan dan konteks tampilan untuk seluruh halaman. Referensi visual: dashboard bergaya **soft glass** berwarna teal dengan sidebar kiri, kartu membulat, dan banner ajakan di sudut kanan bawah (gambar referensi "CoachPro").

> **Batasan native (wajib):** semua gaya ditulis sendiri di `assets/css/style.css`. Tanpa Tailwind, Bootstrap, font eksternal, ikon library, atau gambar 3D dari luar. Lihat `PRD-web-pertanian.md` bagian 9.

---

## 1. Karakter Visual

| Ciri | Penerapan |
|---|---|
| Latar | Gradasi lembut teal keabu-abuan dengan semburat lavender di sudut kanan atas |
| Bingkai aplikasi | Satu panel besar membulat, semi-transparan (efek kaca) di atas latar |
| Kartu | Permukaan terang kehijauan, sudut besar, bayangan lembut ala *soft UI* |
| Aksen | Teal sebagai warna utama; pink/merah muda untuk penanda kontras |
| Menu aktif | Pil berwarna teal dengan teks putih |
| Tipografi | Judul besar dan tebal berwarna hijau tua; label kecil kapital berwarna abu |
| Kesan | Tenang, bersih, lega (banyak ruang kosong), mudah dipindai |

## 2. Design Token

Salin blok ini ke bagian atas `assets/css/style.css`. Semua warna, jarak, dan bayangan wajib memakai variabel, bukan nilai tetap di halaman.

Nilai warna di bawah diambil dari sampel gambar referensi, lalu disesuaikan untuk keterbacaan.

```css
:root {
  /* Latar */
  --bg-teal:      #adcfd1;
  --bg-lavender:  #adb6d7;
  --bg-light:     #d2e4e6;

  /* Permukaan */
  --frame:        rgba(255, 255, 255, 0.30);
  --frame-line:   rgba(255, 255, 255, 0.65);
  --surface:      #dbe9ea;
  --surface-soft: #e6f1f2;
  --surface-hover:#d2e4e6;

  /* Warna utama (teal) */
  --primary:        #17869a;
  --primary-dark:   #0d6f7d;
  --primary-deep:   #116661;
  --primary-soft:   #cfe6e8;
  --primary-gradient: linear-gradient(135deg, #17869a 0%, #1f9aa3 100%);
  --banner-gradient:  linear-gradient(135deg, #0d6f7d 0%, #1b9aa5 100%);

  /* Teks */
  --ink:       #0f2e25;   /* judul dan isi utama */
  --ink-soft:  #3f5c58;   /* teks sekunder */
  --muted:     #6f8a8a;   /* label kecil, placeholder */
  --on-primary:#ffffff;

  /* Garis */
  --line: rgba(15, 46, 37, 0.12);

  /* Aksen dan status */
  --accent:   #f27c8a;    /* pink, penanda kontras */
  --success:  #2f9e77;
  --warning:  #e9a23b;
  --danger:   #e5566a;
  --info:     #17869a;

  /* Warna ikon pada kartu statistik */
  --tile-violet: #8f7cf0;
  --tile-pink:   #f06fa6;
  --tile-orange: #f5a24a;
  --tile-teal:   #2f9aa3;

  /* Bentuk */
  --radius-frame: 28px;
  --radius-card:  20px;
  --radius-pill:  14px;
  --radius-icon:  12px;

  /* Bayangan lembut (soft UI) */
  --shadow-card: 6px 6px 16px rgba(60, 100, 110, 0.14),
                -4px -4px 12px rgba(255, 255, 255, 0.55);
  --shadow-pill: 0 6px 14px rgba(23, 134, 154, 0.35);

  /* Jarak (kelipatan 4) */
  --space-1: 4px;  --space-2: 8px;  --space-3: 12px; --space-4: 16px;
  --space-5: 20px; --space-6: 24px; --space-8: 32px; --space-10: 40px;

  /* Tipografi: font sistem, tanpa memuat dari luar */
  --font: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --fs-title: clamp(1.75rem, 1.2rem + 2.2vw, 2.5rem);  /* judul halaman */
  --fs-h2:    1.0625rem;                               /* judul kartu */
  --fs-stat:  clamp(1.5rem, 1.1rem + 1.4vw, 2rem);     /* angka statistik */
  --fs-body:  0.9375rem;
  --fs-small: 0.8125rem;
  --fs-label: 0.6875rem;                               /* label kapital */
}
```

### Latar halaman

```css
body {
  margin: 0;
  font-family: var(--font);
  font-size: var(--fs-body);
  color: var(--ink);
  background:
    radial-gradient(circle at 95% 8%,  var(--bg-lavender) 0, transparent 45%),
    radial-gradient(circle at 0% 100%, var(--bg-light)    0, transparent 50%),
    var(--bg-teal);
  background-attachment: fixed;
  min-height: 100vh;
}
```

### Aturan warna dan keterbacaan
- Isi teks memakai `--ink`. Jangan memakai teal untuk paragraf panjang.
- Teal (`--primary-dark`) dipakai untuk tautan, label menu, dan tombol; ukuran minimal 14px dan tebal sedang.
- Teks di atas pil/tombol teal selalu putih (`--on-primary`).
- Status tidak boleh hanya dibedakan warna: selalu sertakan teks (mis. "Aktif", "Selesai").

## 3. Tipografi

| Elemen | Gaya |
|---|---|
| Sapaan ("Selamat datang kembali, ...") | `--fs-small`, `--primary-dark`, tebal sedang |
| Judul halaman (Dashboard) | `--fs-title`, berat 600, `--ink` |
| Judul kartu | `--fs-h2`, berat 600 |
| Tautan kartu ("Lihat semua") | `--fs-small`, `--primary-dark`, rata kanan |
| Label statistik | `--fs-label`, huruf kapital, `letter-spacing: .06em`, `--muted` |
| Angka statistik | `--fs-stat`, berat 500, `--ink`; satuan (Rp, kg) lebih kecil |
| Header tabel | `--fs-label`, kapital, `--muted` |

Font memakai font sistem, jadi tampilan sedikit berbeda di tiap perangkat. Ini disengaja demi batasan native.

## 4. Kerangka Layout (App Shell)

```
┌───────────────────────────────────────────────────────────────┐
│ ┌──────────┬────────────────────────────────────────────────┐ │
│ │ LOGO     │  Selamat datang kembali, Budi 👋   🔍  🔔  (B) │ │
│ │          │  Dashboard                                     │ │
│ │ ▣ Dash   │ ────────────────────────────────────────────── │ │
│ │   Lelang │                                                │ │
│ │   ERP    │             area konten (grid kartu)           │ │
│ │   ...    │                                                │ │
│ └──────────┴────────────────────────────────────────────────┘ │
└───────────────────────────────────────────────────────────────┘
        bingkai kaca membulat di atas latar gradasi
```

| Bagian | Spesifikasi |
|---|---|
| Bingkai `.app` | Lebar maks 1280px, di tengah, margin 24px, `border-radius: var(--radius-frame)`, latar `--frame`, `backdrop-filter: blur(14px)`, garis `--frame-line` |
| Sidebar `.sidebar` | Lebar 240px, garis pemisah kanan, logo di atas, menu vertikal, menu sekunder dipisah garis |
| Topbar `.topbar` | Sapaan + judul di kiri; pencarian (opsional), ikon notifikasi, avatar inisial + nama di kanan |
| Konten `.content` | Padding 24px sampai 32px, berisi grid kartu |

```css
.app {
  display: grid;
  grid-template-columns: 240px 1fr;
  max-width: 1280px;
  margin: var(--space-6) auto;
  border-radius: var(--radius-frame);
  background: var(--frame);
  border: 1px solid var(--frame-line);
  -webkit-backdrop-filter: blur(14px);
  backdrop-filter: blur(14px);
  overflow: hidden;
}
```

> Cadangan bila `backdrop-filter` tidak didukung: tambahkan `@supports not (backdrop-filter: blur(1px)) { .app { background: rgba(230,241,242,.85); } }`.

## 5. Komponen

Nama kelas di bawah adalah **kontrak bersama**. Rahmat dan Marvian memakai kelas ini saat membuat halaman, dan komponen baru ditambahkan oleh Agung ke `style.css`.

### 5.1 Sidebar dan menu

| Kelas | Keterangan |
|---|---|
| `.sidebar` | Wadah menu |
| `.brand` | Logo teks di atas (nama aplikasi, tebal) |
| `.nav-item` | Satu menu: ikon 20px + teks `--primary-dark`, tinggi 44px, padding horizontal 16px |
| `.nav-item.active` | Pil `--primary-gradient`, teks putih, `--shadow-pill`, `--radius-pill` |
| `.nav-divider` | Garis pemisah menu utama dan menu sekunder |

Menu per role:

| Role | Menu utama | Menu sekunder |
|---|---|---|
| Petani | Dashboard, Lelang Panen, ERP Keuangan, Musim Tanam, Saprotan, Lowongan | Profil, Keluar |
| Tengkulak | Dashboard, Pasar Lelang, Penawaran Saya | Profil, Keluar |
| Kios | Dashboard, Produk, Pesanan Masuk | Profil, Keluar |
| Publik | (tanpa sidebar) bar atas sederhana: Beranda, Bursa Kerja, Masuk | |

### 5.2 Kartu

```css
.card {
  background: var(--surface);
  border-radius: var(--radius-card);
  box-shadow: var(--shadow-card);
  padding: var(--space-5) var(--space-6);
}
.card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); }
.card-title { font-size: var(--fs-h2); font-weight: 600; margin: 0; }
.card-link  { font-size: var(--fs-small); color: var(--primary-dark); text-decoration: none; }
```

### 5.3 Kartu statistik

Ikon dalam kotak berwarna di kiri atas, label kapital, lalu angka besar.

```
┌───────────────────────┐
│ [▣]  TOTAL PENDAPATAN │
│      Rp 12.4 jt       │
└───────────────────────┘
```

| Kelas | Keterangan |
|---|---|
| `.stat` | Kartu kecil, padding 16px |
| `.stat-icon` | Kotak 44px, `--radius-icon`, latar warna `--tile-*`, ikon SVG putih |
| `.stat-label` | `--fs-label`, kapital, `--muted` |
| `.stat-value` | `--fs-stat`, `--ink` |

### 5.4 Progress bar bertingkat (ringkasan lelang)

Satu batang dibagi tiga segmen sesuai status, dengan angka di bawahnya.

| Segmen | Warna |
|---|---|
| Aktif | `--primary` |
| Selesai | `--success` |
| Dibatalkan | `--accent` |

Lebar segmen dihitung PHP sebagai persen (`style="width: 60%"`), tanpa library grafik.

### 5.5 Tabel

| Kelas | Keterangan |
|---|---|
| `.table` | Tanpa garis vertikal, hanya garis horizontal tipis `--line` |
| `.table th` | Label kapital kecil `--muted` |
| `.table td` | Tinggi baris ±52px, baris pertama/terbaik boleh tebal |
| `.table-wrap` | Pembungkus `overflow-x: auto` untuk layar sempit |

### 5.6 Badge status

Pil kecil, teks tebal 12px, latar warna status 15 persen dengan teks warna status gelap.

| Data | Status → kelas badge |
|---|---|
| Lelang | `aktif` → `.badge-info`, `selesai` → `.badge-success`, `dibatalkan` → `.badge-danger` |
| Penawaran | `menunggu` → `.badge-warning`, `menang` → `.badge-success`, `kalah` → `.badge-muted` |
| Pesanan | `menunggu` → `.badge-warning`, `disetujui` → `.badge-info`, `selesai` → `.badge-success`, `ditolak` → `.badge-danger` |
| Lowongan | `buka` → `.badge-success`, `ditutup` → `.badge-muted` |
| Musim tanam | `berjalan` → `.badge-info`, `selesai` → `.badge-muted` |

### 5.7 Tombol

| Kelas | Gaya |
|---|---|
| `.btn` | Dasar: tinggi 44px, `--radius-pill`, padding 0 20px, berat 600 |
| `.btn-primary` | `--primary-gradient`, teks putih |
| `.btn-ghost` | Latar putih transparan, teks `--primary-dark`, garis `--line` |
| `.btn-danger` | `--danger`, teks putih (hapus/batalkan) |
| `.btn-sm` | Tinggi 36px, untuk aksi di dalam tabel |

Tombol aksi permanen (hapus, batalkan lelang, pilih pemenang) wajib meminta konfirmasi (`confirm()` JavaScript vanilla).

### 5.8 Banner ajakan (CTA)

Kartu lebar dengan gradasi teal (`--banner-gradient`), teks putih, tombol putih, dan hiasan bentuk bulat/kapsul dari CSS murni (lingkaran hijau limau, cincin pink, kotak putih) di sisi kanan sebagai pengganti ilustrasi 3D. Contoh isi: "Jangan lupa. Buat lelang panen baru" + tombol "Buat lelang".

### 5.9 Formulir

| Kelas | Keterangan |
|---|---|
| `.form-group` | Label di atas input, jarak 16px |
| `.input`, `.select`, `.textarea` | Latar `--surface-soft`, garis `--line`, radius 12px, tinggi 44px, fokus: garis `--primary` + bayangan `0 0 0 3px rgba(23,134,154,.25)` |
| `.form-error` | Teks `--danger` kecil di bawah input |
| `.form-grid` | Dua kolom di desktop, satu kolom di ponsel |

Nama atribut `name` pada input **sama persis dengan nama kolom tabel** (lihat `schema.sql`).

### 5.10 Notifikasi (flash message)

`.alert`, `.alert-success`, `.alert-error`, `.alert-info`: kartu tipis dengan garis kiri 4px berwarna status, tampil di atas konten setelah setiap aksi.

### 5.11 Keadaan kosong

Bila tabel/daftar kosong, tampilkan teks ramah dan tombol aksi, bukan tabel kosong. Contoh: "Belum ada lelang. Buat lelang panen pertamamu."

### 5.12 Ikon

Tanpa library ikon. Pakai **SVG inline** sederhana (garis 1.75px, tanpa isian) yang disimpan di `includes/icons.php` sebagai fungsi `ikon('nama')`. Daftar minimal: `dashboard`, `panen`, `keuangan`, `kalender` (musim), `keranjang` (saprotan), `pekerja`, `lonceng`, `cari`, `keluar`. Emoji boleh dipakai sebagai cadangan.

### 5.13 Avatar

Lingkaran 40px berisi inisial nama pada latar `--primary-soft`, teks `--primary-deep` (tanpa foto).

## 6. Dashboard Petani (prioritas responsif)

Dashboard petani adalah halaman yang paling dipoles dan paling diuji di berbagai ukuran layar. Susunan mengikuti gambar referensi.

### 6.1 Pemetaan widget

| Posisi di referensi | Widget di platform kita | Sumber data |
|---|---|---|
| "Next game" | **Panen terdekat**: komoditas, estimasi tonase, tanggal panen, harga dasar, tawaran tertinggi saat ini | `hasil_panen` status `aktif`, tanggal panen terdekat; `MAX(penawaran_lelang.harga_tawar)` |
| "Games statistic" | **Ringkasan lelang**: progress bar aktif / selesai / dibatalkan + angkanya | `COUNT(*)` per status di `hasil_panen` |
| "Standings" | **Tawaran terbaru**: tabel (lelang, penawar, harga/kg, waktu, status) | `penawaran_lelang` JOIN `hasil_panen`, 5 baris terbaru |
| Kartu statistik (4 buah) | **Pendapatan musim berjalan**, **Total biaya**, **Laba/rugi bersih**, **Pesanan saprotan diproses** | `transaksi_keuangan` per `musim_tanam` berstatus `berjalan`; `pesanan_saprotan` status `menunggu`/`disetujui` |
| Banner "Setup training" | **Banner CTA**: "Buat lelang panen baru" + tombol | tautan ke form lelang |

Tambahan di bawah (baris ketiga, opsional bila waktu cukup): kartu **Pesanan saprotan terbaru** dan kartu **Lowongan saya yang masih buka**.

Laba/rugi bernilai negatif ditampilkan dengan warna `--danger` dan tanda minus; positif dengan `--success`.

### 6.2 Tata letak desktop (≥ 1024px)

```
┌─────────────────────────────┬─────────────────────────────┐
│ PANEN TERDEKAT              │ RINGKASAN LELANG            │
│ Padi · 2,5 ton · 12 Des     │ ████████████░░░░▒▒▒         │
│ Tawaran tertinggi Rp 6.800  │ Aktif 4 | Selesai 6 | Batal 1│
├─────────────────────────────┼──────────────┬──────────────┤
│ TAWARAN TERBARU             │ PENDAPATAN   │ TOTAL BIAYA  │
│ lelang  penawar  harga  ... │ Rp 12,4 jt   │ Rp 5,1 jt    │
│ ...                         ├──────────────┼──────────────┤
│ ...                         │ LABA BERSIH  │ PESANAN      │
│                             │ Rp 7,3 jt    │ DIPROSES  2  │
│                             ├──────────────┴──────────────┤
│                             │ Buat lelang panen baru  [▶] │
└─────────────────────────────┴─────────────────────────────┘
```

Susunan dibuat dengan dua baris grid, tiap baris dua kolom (tanpa `grid-template-areas`). Nilai dasar di bawah adalah versi ponsel (satu kolom); lebar kolom untuk tablet dan desktop diatur di bagian 6.3.

```css
.dash        { display: grid; gap: var(--space-5); grid-template-columns: 1fr; }
.dash-row-1  { display: grid; gap: var(--space-5); grid-template-columns: 1fr; }
.dash-row-2  { display: grid; gap: var(--space-5); grid-template-columns: 1fr; }
.stat-grid   { display: grid; gap: var(--space-4); grid-template-columns: repeat(2, 1fr); }
```

Struktur HTML: `.dash` berisi `.dash-row-1` (kartu Panen terdekat + kartu Ringkasan lelang) dan `.dash-row-2` (kartu Tabel tawaran + kolom kanan berisi `.stat-grid` dan banner CTA).

### 6.3 Aturan responsif

Pendekatan **mobile-first**: gaya dasar untuk ponsel, lalu diperluas dengan `min-width`.

| Breakpoint | Lebar | Perilaku |
|---|---|---|
| Ponsel | < 640px | Satu kolom. Sidebar jadi laci (drawer) dengan tombol ☰. Kartu statistik 2×2. Tabel berubah menjadi daftar kartu. Banner CTA di atas statistik. Topbar: sapaan dan avatar saja, judul diperkecil lewat `clamp()` |
| Tablet | 640px sampai 1023px | Sidebar tetap laci. Dua kolom untuk "Panen terdekat" dan "Ringkasan lelang". Tabel penuh lebar. Kartu statistik 4 dalam satu baris atau 2×2 |
| Laptop | 1024px sampai 1279px | Sidebar tampil tetap (lebar 220px). Grid dua kolom seperti 6.2 |
| Desktop | ≥ 1280px | Bingkai `.app` berlebar maks 1280px dengan margin sekeliling, seperti gambar referensi |

```css
/* Dasar: ponsel */
.app { display: block; margin: 0; border-radius: 0; }
.sidebar {
  position: fixed; inset: 0 auto 0 0; width: 260px; z-index: 50;
  transform: translateX(-100%); transition: transform .25s ease;
  background: var(--surface-soft);
}
.sidebar.open { transform: translateX(0); }
.overlay { display: none; position: fixed; inset: 0; background: rgba(15,46,37,.35); z-index: 40; }
.overlay.show { display: block; }
.menu-btn { display: inline-flex; }

/* Tablet */
@media (min-width: 640px) {
  .dash-row-1 { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-5); }
  .stat-grid  { grid-template-columns: repeat(4, 1fr); }
}

/* Laptop ke atas */
@media (min-width: 1024px) {
  .app { display: grid; grid-template-columns: 220px 1fr; margin: var(--space-6) auto; border-radius: var(--radius-frame); }
  .sidebar { position: static; transform: none; background: transparent; width: auto; }
  .menu-btn, .overlay { display: none !important; }
  .dash-row-2 { grid-template-columns: 7fr 5fr; }
  .stat-grid  { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1280px) { .app { grid-template-columns: 240px 1fr; } }
```

Buka/tutup laci dengan JavaScript vanilla (±10 baris di `assets/js/app.js`):

```js
const sidebar = document.querySelector('.sidebar');
const overlay = document.querySelector('.overlay');
document.querySelector('.menu-btn').addEventListener('click', () => {
  sidebar.classList.toggle('open');
  overlay.classList.toggle('show');
});
overlay.addEventListener('click', () => {
  sidebar.classList.remove('open');
  overlay.classList.remove('show');
});
```

Tabel menjadi kartu di ponsel (setiap `<td>` membawa `data-label`):

```css
@media (max-width: 639px) {
  .table thead { display: none; }
  .table tr { display: block; padding: var(--space-3) 0; border-bottom: 1px solid var(--line); }
  .table td { display: flex; justify-content: space-between; padding: var(--space-1) 0; }
  .table td::before { content: attr(data-label); color: var(--muted); font-size: var(--fs-small); }
}
```

### 6.4 Aturan umum responsif

- Viewport wajib: `<meta name="viewport" content="width=device-width, initial-scale=1">` di `includes/header.php`.
- Area sentuh minimal **44×44px** (menu, tombol, ikon).
- Ukuran teks tidak di bawah 13px; input 16px agar tidak memicu zoom otomatis di iOS.
- Gambar dan elemen lebar memakai `max-width: 100%`.
- Tidak ada scroll horizontal pada halaman; tabel lebar dibungkus `.table-wrap`.
- Satuan relatif (`rem`, `%`, `fr`, `clamp()`), hindari lebar piksel tetap pada kartu.
- Uji minimal di tiga lebar: **375px** (ponsel), **768px** (tablet), **1366px** (laptop), memakai mode responsif di DevTools browser.

## 7. Dashboard Role Lain (ringkas)

| Role | Susunan |
|---|---|
| Tengkulak | Sapaan; kartu "Lelang aktif" (jumlah); kartu "Penawaran saya" (menunggu/menang/kalah); tabel lelang aktif terbaru; banner CTA "Jelajahi pasar lelang" |
| Kios | Sapaan; kartu statistik: jumlah produk, stok menipis, pesanan menunggu, pesanan selesai bulan ini; tabel pesanan masuk terbaru; banner CTA "Tambah produk" |
| Publik | Tanpa sidebar. Bar atas (logo, Beranda, Bursa Kerja, Masuk). Halaman Bursa Kerja: filter di atas, lalu kartu lowongan berbentuk grid (`repeat(auto-fill, minmax(280px, 1fr))`), tiap kartu menampilkan judul, lokasi, upah harian, tanggal mulai, tombol "Lihat kontak" |

Landing page (`index.php`): hero singkat dengan judul masalah dan solusi, tiga kartu manfaat (lelang adil, catat keuangan, bursa kerja), tombol Masuk dan Daftar, serta keterkaitan SDG 8.

## 8. Pemetaan File

| Berkas | Isi | PIC |
|---|---|---|
| `assets/css/style.css` | Token, komponen, layout, responsif | Agung (perubahan lewat permintaan atau pull request) |
| `assets/js/app.js` | Toggle laci menu, konfirmasi aksi, penutup flash message | Agung |
| `includes/header.php` | `<head>`, viewport, tautan CSS, pembuka `.app` | Agung |
| `includes/sidebar.php` | Menu sesuai `$_SESSION['role']` | Agung |
| `includes/footer.php` | Penutup, skrip JS | Agung |
| `includes/icons.php` | Fungsi `ikon('nama')` berisi SVG inline | Agung |

## 9. Aturan untuk Semua Anggota

1. Jangan menulis `style="..."` selain untuk nilai dinamis (lebar progress bar, warna dari data).
2. Jangan menambah file CSS lain atau blok `<style>` per halaman; semua gaya di `style.css`.
3. Gunakan kelas komponen di bagian 5. Butuh komponen baru: minta ke Agung dahulu agar tidak ada gaya ganda.
4. Satu halaman = satu `<h1>` (judul halaman), judul kartu memakai `<h2>`.
5. Semua halaman terproteksi diawali `include` header dan sidebar, diakhiri footer.
6. Setelah selesai membuat halaman, uji di 375px, 768px, dan 1366px.

## 10. Checklist Desain

- [ ] Token `:root` terpasang dan dipakai di semua komponen
- [ ] Latar gradasi teal dan lavender tampil, bingkai kaca membulat tampil
- [ ] Sidebar dengan pil aktif berwarna teal
- [ ] Kartu, kartu statistik, tabel, badge, tombol, form, alert tersedia sebagai kelas
- [ ] Dashboard petani sesuai pemetaan bagian 6.1
- [ ] Sidebar menjadi laci di bawah 1024px dan berfungsi
- [ ] Tabel menjadi kartu di bawah 640px
- [ ] Tidak ada scroll horizontal pada halaman di 375px
- [ ] Tidak ada library, font, atau ikon dari luar
- [ ] Teks isi memiliki kontras yang cukup di atas kartu
