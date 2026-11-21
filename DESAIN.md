# DESAIN: Pedoman Tampilan UI Platform Web Pertanian (WiraTani)

Patokan dan konteks tampilan untuk seluruh halaman. Arah desain: **hijau hutan futuristik yang tetap sederhana dan bernuansa pertanian**.

Referensi:
- Tata letak: dashboard dengan sidebar kiri dan kartu-kartu bergaya kaca (glass).
- Warna dan karakter: dashboard dengan sidebar hijau gelap, kartu terang, aksen hijau segar, dan banner CTA hijau.

> **Batasan native (wajib):** semua gaya ditulis sendiri di `assets/css/style.css`. Tanpa Tailwind, Bootstrap, font eksternal, ikon library, atau gambar dari luar. Lihat `PRD-web-pertanian.md` bagian 9.

**Revisi dari versi sebelumnya:**
1. Dashboard sekarang **layar penuh (full-bleed)**: tidak ada bingkai membulat, margin, atau efek kaca pada kerangka utama.
2. **Sudut membulat dan efek kaca hanya dipakai pada komponen kartu.**
3. Palet diganti dari teal-lavender menjadi **palet pertanian** (hijau hutan, hijau segar, kuning padi, tanah, air).

---

## 1. Karakter Visual

| Ciri | Penerapan |
|---|---|
| Kerangka | Penuh satu layar: sidebar hijau gelap setinggi layar di kiri, area konten di kanan. Sudut kerangka tegas (tanpa radius) |
| Area konten | Gradasi mint terang dengan dua semburat cahaya buram (hijau segar dan biru air) supaya kartu kaca terlihat |
| Kartu | Satu-satunya elemen yang membulat (20px) dan bergaya kaca: putih transparan, blur latar, garis tepi terang, bayangan hijau lembut |
| Sidebar | Hijau hutan gelap bergradasi; menu aktif diberi penanda hijau segar yang sedikit menyala (glow) |
| Aksen | Hijau segar sebagai warna aksi utama; kuning padi dan tanah (terakota) sebagai pendamping |
| Sentuhan futuristik (secukupnya) | Glow hijau pada elemen aktif, progress berbentuk cincin, mini grafik batang, angka dengan lebar seragam, label kecil huruf kapital |
| Kesan | Bersih, tenang, modern, dan terasa "tanaman": hijau, tanah, air, matahari |

Prinsip komposisi warna (60-30-10):
- **60%** permukaan terang (latar mint dan kartu putih transparan)
- **30%** hijau hutan gelap (sidebar, teks judul, kartu sorotan)
- **10%** aksen (hijau segar, kuning padi, terakota, biru air)

## 2. Palet Warna

Nilai hijau gelap dan hijau segar diambil dari sampel gambar referensi, lalu dilengkapi warna pertanian.

### 2.1 Inti

| Peran | Nama | Hex | Dipakai untuk |
|---|---|---|---|
| Hijau hutan gelap | `--forest-900` | `#0a3639` | Sidebar, judul, kartu sorotan gelap |
| Hijau hutan lebih gelap | `--forest-950` | `#052124` | Dasar gradasi sidebar, tombol gelap, kartu sorotan |
| Hijau hutan sedang | `--forest-800` | `#094345` | Gradasi sidebar |
| Hijau zamrud | `--emerald-800` | `#0b5846` | Banner CTA (gradasi) |
| Hijau segar | `--green-500` | `#43d980` | Aksen utama, tombol utama, ikon aktif, glow |
| Hijau tua untuk teks | `--green-800` | `#0f6b3e` | Tautan dan teks aksen di atas latar terang |
| Hijau limau | `--lime-400` | `#c6f36b` | Highlight kecil dan glow (dipakai hemat) |

### 2.2 Pendamping bertema pertanian

| Peran | Nama | Hex | Makna |
|---|---|---|---|
| Kuning padi | `--grain-400` | `#f2b84b` | Panen, peringatan ringan, status menunggu |
| Terakota (tanah) | `--soil-500` | `#e2672e` | Penurunan, rugi, bahaya, hapus/batal |
| Biru air | `--water-400` | `#4cc9f0` | Informasi, irigasi, status aktif |

### 2.3 Netral

| Peran | Nama | Hex |
|---|---|---|
| Latar mint (atas) | `--mint-50` | `#f4fbf8` |
| Latar mint (bawah) | `--mint-100` | `#e6f4ef` |
| Teks utama | `--ink` | `#08282a` |
| Teks sekunder | `--ink-soft` | `#3d5c5a` |
| Teks redup / label | `--muted` | `#6f8c8a` |
| Garis | `--line` | `rgba(10, 54, 57, 0.12)` |

### 2.4 Teks di atas sidebar gelap

| Peran | Hex |
|---|---|
| Menu biasa | `#bfe0d6` |
| Menu aktif | `#ffffff` |
| Label bagian ("NAVIGASI") | `#6fa39a` |

### 2.5 Aturan kontras
- Teks isi memakai `--ink`; jangan memakai hijau segar untuk teks di atas latar terang (kontras rendah).
- Di atas tombol hijau segar, teks memakai warna gelap `--forest-950` (bukan putih).
- Di atas latar gelap (sidebar, kartu sorotan), teks putih atau `#bfe0d6`.
- Status tidak boleh hanya dibedakan warna: selalu sertakan teks ("Aktif", "Selesai").

## 3. Design Token

Salin ke bagian atas `assets/css/style.css`. Semua warna, jarak, dan bayangan wajib memakai variabel.

```css
:root {
  /* Hijau hutan */
  --forest-950: #052124;
  --forest-900: #0a3639;
  --forest-800: #094345;
  --emerald-800:#0b5846;

  /* Hijau aksen */
  --green-500: #43d980;
  --green-600: #2cb866;
  --green-800: #0f6b3e;
  --lime-400:  #c6f36b;

  /* Pendamping pertanian */
  --grain-400: #f2b84b;
  --soil-500:  #e2672e;
  --water-400: #4cc9f0;

  /* Netral */
  --mint-50:   #f4fbf8;
  --mint-100:  #e6f4ef;
  --ink:       #08282a;
  --ink-soft:  #3d5c5a;
  --muted:     #6f8c8a;
  --line:      rgba(10, 54, 57, 0.12);

  /* Status */
  --success: #1fa35c;
  --warning: #d99a1f;
  --danger:  #d9522a;
  --info:    #1b9cc4;

  /* Sidebar */
  --nav-text:   #bfe0d6;
  --nav-label:  #6fa39a;
  --nav-active-bg: rgba(67, 217, 128, 0.14);

  /* Kartu kaca */
  --glass-bg:     rgba(255, 255, 255, 0.55);
  --glass-border: rgba(255, 255, 255, 0.80);
  --glass-solid:  #f3faf7;  /* cadangan tanpa blur */
  --shadow-card:  0 8px 30px rgba(10, 54, 57, 0.08),
                  inset 0 1px 0 rgba(255, 255, 255, 0.9);
  --shadow-card-hover: 0 12px 36px rgba(10, 54, 57, 0.14),
                  inset 0 1px 0 rgba(255, 255, 255, 0.9);
  --glow-green:   0 0 14px rgba(67, 217, 128, 0.55);
  --shadow-btn:   0 6px 18px rgba(67, 217, 128, 0.35);

  /* Bentuk: radius HANYA untuk komponen, bukan kerangka */
  --radius-card: 20px;
  --radius-ctrl: 12px;
  --radius-pill: 999px;

  /* Jarak (kelipatan 4) */
  --space-1: 4px;  --space-2: 8px;  --space-3: 12px; --space-4: 16px;
  --space-5: 20px; --space-6: 24px; --space-8: 32px; --space-10: 40px;

  /* Tipografi: font sistem, tanpa memuat dari luar */
  --font: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --fs-title: clamp(1.75rem, 1.2rem + 2.2vw, 2.5rem);
  --fs-h2:    1.0625rem;
  --fs-stat:  clamp(1.5rem, 1.1rem + 1.4vw, 2.125rem);
  --fs-body:  0.9375rem;
  --fs-small: 0.8125rem;
  --fs-label: 0.6875rem;
}

*, *::before, *::after { box-sizing: border-box; }

body {
  margin: 0;
  font-family: var(--font);
  font-size: var(--fs-body);
  color: var(--ink);
  background: var(--mint-50);
}
```

## 4. Tipografi

| Elemen | Gaya |
|---|---|
| Sapaan kecil di atas judul | `--fs-small`, `--green-800`, berat 600 |
| Judul halaman | `--fs-title`, berat 700, `--ink`, `letter-spacing: -0.01em` |
| Judul kartu | `--fs-h2`, berat 600 |
| Tautan kartu ("Lihat semua") | `--fs-small`, `--green-800`, berat 600 |
| Label statistik / header tabel | `--fs-label`, kapital, `letter-spacing: .06em`, `--muted` |
| Angka statistik | `--fs-stat`, berat 600, `font-variant-numeric: tabular-nums`; satuan (Rp, kg, %) lebih kecil dan `--muted` |

Font memakai font sistem, jadi tampilan sedikit berbeda di tiap perangkat. Ini disengaja demi batasan native.

## 5. Kerangka Layout (Full-Bleed)

```
┌──────────────┬─────────────────────────────────────────────────┐
│ ▓▓▓▓▓▓▓▓▓▓▓▓ │  Selamat datang kembali, Rahmat        🔔 (R)   │
│ ▓ WiraTani ▓ │  Dashboard Petani                               │
│ ▓▓▓▓▓▓▓▓▓▓▓▓ │ ───────────────────────────────────────────────  │
│ ▓ ● Dash   ▓ │  ╭───────╮ ╭───────╮ ╭───────╮                  │
│ ▓   Lelang ▓ │  │ kartu │ │ kartu │ │ kartu │  ← hanya kartu   │
│ ▓   ERP    ▓ │  ╰───────╯ ╰───────╯ ╰───────╯    yang membulat │
│ ▓   ...    ▓ │  ╭───────────────╮ ╭───────────╮                │
│ ▓▓▓▓▓▓▓▓▓▓▓▓ │  │     kartu     │ │   kartu   │                │
│ ▓ Keluar   ▓ │  ╰───────────────╯ ╰───────────╯                │
└──────────────┴─────────────────────────────────────────────────┘
 sidebar gelap,                 area konten terang,
 sudut tegas, setinggi layar    mengisi sisa layar sampai tepi
```

| Bagian | Spesifikasi |
|---|---|
| `.app` | `min-height: 100vh`, grid dua kolom (sidebar + konten), **tanpa margin, tanpa radius, tanpa border, tanpa blur** |
| `.sidebar` | Lebar 250px, `position: sticky; top: 0; height: 100vh`, gradasi hijau hutan, bisa discroll bila menu panjang |
| `.main` | Latar gradasi mint dengan dua semburat cahaya, mengisi seluruh sisa layar |
| `.topbar` | Datar (tanpa kartu, tanpa kaca), garis tipis di bawah; sapaan + judul kiri, notifikasi dan avatar kanan |
| `.content` | Padding 24px sampai 32px; lebar penuh (batas `max-width: 1600px` di layar sangat lebar, rata kiri) |

```css
.app { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }

.sidebar {
  position: sticky; top: 0; height: 100vh; overflow-y: auto;
  display: flex; flex-direction: column;
  padding: var(--space-6) var(--space-4);
  color: var(--nav-text);
  background: linear-gradient(180deg, var(--forest-900) 0%, var(--forest-800) 55%, var(--forest-950) 100%);
}

.main {
  min-height: 100vh;
  background:
    radial-gradient(600px 420px at 92% 0%,  rgba(67, 217, 128, 0.20), transparent 70%),
    radial-gradient(520px 420px at 0% 100%, rgba(76, 201, 240, 0.16), transparent 70%),
    linear-gradient(180deg, var(--mint-50), var(--mint-100));
  background-attachment: fixed;
}

.topbar  { display: flex; justify-content: space-between; align-items: center;
           padding: var(--space-6) var(--space-8) var(--space-4);
           border-bottom: 1px solid var(--line); }
.content { padding: var(--space-6) var(--space-8) var(--space-10); max-width: 1600px; }
```

> Kerangka (`.app`, `.sidebar`, `.main`, `.topbar`) **dilarang** memakai `border-radius`, `backdrop-filter`, atau bayangan besar. Efek tersebut hanya untuk komponen kartu.

## 6. Komponen

Nama kelas di bawah adalah **kontrak bersama**. Rahmat dan Marvian memakai kelas ini saat membuat halaman; komponen baru ditambahkan oleh Agung.

### 6.1 Sidebar dan menu

| Kelas | Keterangan |
|---|---|
| `.brand` | Logo daun (SVG inline) + nama "WiraTani" putih, tebal |
| `.nav-label` | Label bagian kecil kapital (`--nav-label`), mis. "NAVIGASI" |
| `.nav-item` | Tinggi 44px, padding 0 14px, radius 12px, teks `--nav-text`, ikon SVG monoline 20px |
| `.nav-item:hover` | Latar `rgba(255,255,255,.06)`, teks putih |
| `.nav-item.active` | Latar `--nav-active-bg`, teks putih, ikon `--green-500`, garis kiri 3px `--green-500` dengan `--glow-green` |
| `.nav-divider` | Garis `rgba(255,255,255,.10)` pemisah menu utama dan sekunder (Keluar) |

Radius 12px pada `.nav-item` termasuk komponen, jadi boleh. Menu per role:

| Role | Menu utama | Menu sekunder |
|---|---|---|
| Petani | Dashboard, Lelang Panen, ERP Keuangan, Musim Tanam, Saprotan, Lowongan | Keluar |
| Tengkulak | Dashboard, Pasar Lelang, Penawaran Saya | Keluar |
| Kios | Dashboard, Produk, Pesanan Masuk | Keluar |
| Publik | Tanpa sidebar: bar atas datar (logo, Beranda, Bursa Kerja, Masuk) | |

### 6.2 Kartu kaca (komponen utama)

```css
.card {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-card);
  box-shadow: var(--shadow-card);
  -webkit-backdrop-filter: blur(16px) saturate(140%);
  backdrop-filter: blur(16px) saturate(140%);
  padding: var(--space-5) var(--space-6);
  transition: transform .2s ease, box-shadow .2s ease;
}
.card:hover { transform: translateY(-2px); box-shadow: var(--shadow-card-hover); }

@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
  .card { background: var(--glass-solid); }
}

.card-head  { display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); }
.card-title { font-size: var(--fs-h2); font-weight: 600; margin: 0; }
.card-link  { font-size: var(--fs-small); font-weight: 600; color: var(--green-800); text-decoration: none; }
```

**Kartu sorotan gelap** (`.card-dark`): latar `--forest-950`, teks putih, tanpa blur, radius sama. Dipakai untuk satu info terpenting di halaman, mis. "Tawaran tertinggi saat ini".

### 6.3 Kartu statistik dengan mini grafik

```
┌───────────────────────────────┐
│ PENDAPATAN MUSIM INI          │
│ Rp 12,4 jt            ▂ ▄ ▃ ▆ │
│ Musim Tanam 1 2026            │
└───────────────────────────────┘
```

| Kelas | Keterangan |
|---|---|
| `.stat` | Kartu kaca kecil |
| `.stat-label` | `--fs-label`, kapital, `--muted` |
| `.stat-value` | `--fs-stat`, tabular-nums |
| `.stat-sub` | `--fs-small`, `--muted` (mis. nama musim tanam) |
| `.spark` | Mini grafik batang dari `<i>` berisi tinggi relatif (tanpa library grafik) |
| `.stat-up` / `.stat-down` | Warna `--success` / `--soil-500` untuk laba positif / rugi |

```css
.spark   { display: flex; align-items: flex-end; gap: 4px; height: 36px; }
.spark i { width: 6px; height: calc(var(--h) * 1%); border-radius: 3px; background: var(--green-500); }
.stat-down .spark i { background: var(--soil-500); }
```

Tinggi batang diisi PHP lewat `style="--h:60"` (nilai dinamis, diperbolehkan).

### 6.4 Progress cincin (gauge)

Pengganti grafik pai, dibuat dengan `conic-gradient` (CSS murni).

```css
.ring {
  --p: 0;                      /* persen, diisi PHP: style="--p:72" */
  position: relative; width: 120px; aspect-ratio: 1; border-radius: 50%;
  background: conic-gradient(var(--green-500) calc(var(--p) * 1%), rgba(10, 54, 57, 0.10) 0);
  display: grid; place-items: center;
}
.ring::after {
  content: attr(data-label);   /* mis. "72%" */
  width: 78%; aspect-ratio: 1; border-radius: 50%;
  display: grid; place-items: center;
  background: var(--glass-solid); font-size: 1.5rem; font-weight: 600;
}
```

### 6.5 Progress batang bertingkat (ringkasan lelang)

| Segmen | Warna |
|---|---|
| Aktif | `--water-400` |
| Selesai | `--green-500` |
| Dibatalkan | `--soil-500` |

Lebar segmen dihitung PHP sebagai persen.

### 6.6 Tabel

| Kelas | Keterangan |
|---|---|
| `.table` | Hanya garis horizontal tipis `--line`, tanpa garis vertikal |
| `.table th` | Label kapital kecil `--muted` |
| `.table td` | Tinggi baris ±52px; baris terbaik/teratas boleh tebal |
| `.table-wrap` | Pembungkus `overflow-x: auto` |

### 6.7 Badge status

Pil kecil (`--radius-pill`), teks tebal 12px, latar warna status 15 persen, teks warna status yang lebih gelap.

| Data | Status → kelas badge |
|---|---|
| Lelang | `aktif` → `.badge-info` (air), `selesai` → `.badge-success` (hijau), `dibatalkan` → `.badge-danger` (terakota) |
| Penawaran | `menunggu` → `.badge-warning` (padi), `menang` → `.badge-success`, `kalah` → `.badge-muted` |
| Pesanan | `menunggu` → `.badge-warning`, `disetujui` → `.badge-info`, `selesai` → `.badge-success`, `ditolak` → `.badge-danger` |
| Lowongan | `buka` → `.badge-success`, `ditutup` → `.badge-muted` |
| Musim tanam | `berjalan` → `.badge-info`, `selesai` → `.badge-muted` |

### 6.8 Tombol

| Kelas | Gaya |
|---|---|
| `.btn` | Dasar: tinggi 44px, `--radius-ctrl`, padding 0 20px, berat 600 |
| `.btn-primary` | Gradasi `--green-600` ke `--green-500`, **teks gelap `--forest-950`**, `--shadow-btn` |
| `.btn-dark` | Latar `--forest-900`, teks putih |
| `.btn-ghost` | Latar putih transparan, garis `--line`, teks `--forest-900` |
| `.btn-danger` | Latar `--soil-500`, teks putih |
| `.btn-sm` | Tinggi 36px, untuk aksi dalam tabel |

Aksi permanen (hapus, batalkan lelang, pilih pemenang) wajib meminta konfirmasi `confirm()` (JavaScript vanilla).

### 6.9 Banner ajakan (CTA)

Kartu lebar bergradasi `--forest-900` ke `--emerald-800` (dengan semburat `--green-500` di sudut), teks putih, tombol `.btn-primary`. Hiasan sisi kanan dibuat dari CSS murni: lingkaran dan bentuk daun (elemen dengan `border-radius: 0 100% 0 100%`) bertumpuk dalam warna hijau segar dan limau yang agak transparan, sebagai pengganti ilustrasi gambar. Contoh isi: "Siap panen? Buat lelang panen baru" + tombol "Buat lelang".

### 6.10 Formulir

| Kelas | Keterangan |
|---|---|
| `.form-group` | Label di atas input, jarak 16px |
| `.input`, `.select`, `.textarea` | Latar putih transparan, garis `--line`, radius 12px, tinggi 44px; fokus: garis `--green-500` + `0 0 0 3px rgba(67,217,128,.28)` |
| `.form-error` | Teks `--danger` kecil di bawah input |
| `.form-grid` | Dua kolom di desktop, satu kolom di ponsel |

Nama atribut `name` pada input **sama persis dengan nama kolom tabel** (lihat `schema.sql`).

### 6.11 Notifikasi (flash message)

`.alert`, `.alert-success`, `.alert-error`, `.alert-info`, `.alert-warning`: kartu tipis (kaca, radius 14px) dengan garis kiri 4px berwarna status, tampil di atas konten setelah setiap aksi.

### 6.12 Keadaan kosong

Bila daftar kosong, tampilkan teks ramah dan tombol aksi. Contoh: "Belum ada lelang. Buat lelang panen pertamamu."

### 6.13 Ikon

Tanpa library ikon dan **tanpa emoji berwarna di menu** (warnanya tidak seragam dengan palet). Pakai **SVG inline monoline** (garis 1.75px, tanpa isian, `stroke: currentColor`) yang disimpan di `includes/icons.php` sebagai fungsi `ikon('nama')`. Dengan `currentColor`, ikon otomatis mengikuti warna menu (abu kehijauan biasa, hijau segar saat aktif).

Daftar minimal: `dashboard`, `panen` (tunas/daun), `keuangan`, `musim` (kalender), `saprotan` (keranjang), `pekerja`, `lonceng`, `cari`, `keluar`, `panah`.

### 6.14 Avatar

Lingkaran 40px berisi inisial pada latar `rgba(67,217,128,.18)`, teks `--green-800`, berat 700 (tanpa foto).

## 7. Dashboard Petani (prioritas responsif)

Dashboard petani adalah halaman yang paling dipoles dan paling diuji di berbagai ukuran layar.

### 7.1 Pemetaan widget

| Baris | Widget | Sumber data |
|---|---|---|
| 1 | **Pendapatan musim berjalan**: `.stat` + mini grafik | `SUM(nominal)` jenis `pendapatan` pada `musim_tanam` berstatus `berjalan` |
| 1 | **Total biaya**: `.stat` + mini grafik | Idem, jenis `pengeluaran` |
| 1 | **Laba/rugi bersih**: `.stat`, hijau bila positif, terakota bila negatif | Pendapatan dikurangi biaya |
| 2 | **Lelang selesai**: `.ring` dengan persentase lelang `selesai` dari seluruh lelang | `COUNT(*)` per status di `hasil_panen` |
| 2 | **Ringkasan lelang**: daftar status (titik berwarna + jumlah) dan batang bertingkat | `COUNT(*)` per status di `hasil_panen` |
| 2 | **Panen terdekat** (`.card-dark`): komoditas, tonase, tanggal panen, harga dasar, tawaran tertinggi | `hasil_panen` aktif dengan tanggal panen terdekat; `MAX(harga_tawar)` |
| 3 | **Tawaran terbaru**: tabel (lelang, penawar, harga/kg, waktu, status) | `penawaran_lelang` JOIN `hasil_panen`, 5 baris terbaru |
| 3 | **Banner CTA** "Buat lelang panen baru" | Tautan ke form lelang |
| 3 | Dua kartu kecil: **Pesanan saprotan diproses** dan **Lowongan masih buka** | `pesanan_saprotan` status `menunggu`/`disetujui`; `lowongan_kerja` status `buka` |

Laba/rugi bernilai negatif ditampilkan dengan warna terakota dan tanda minus (`.stat-down`).

### 7.2 Tata letak desktop (≥ 1024px)

```
┌──────────────┬──────────────┬──────────────┐
│ PENDAPATAN   │ TOTAL BIAYA  │ LABA BERSIH  │   baris 1: 3 kartu statistik
│ Rp 12,4 jt ▂▄│ Rp 5,1 jt ▃▂ │ Rp 7,3 jt ▄▆ │
└──────────────┴──────────────┴──────────────┘
┌────────────┬───────────────────┬────────────────────────┐
│  ◔ 72%     │ RINGKASAN LELANG  │ ▓ PANEN TERDEKAT ▓     │   baris 2
│  Lelang    │ ● Aktif       4   │ ▓ Padi · 2,5 ton ▓     │
│  selesai   │ ● Selesai     6   │ ▓ Tertinggi Rp 6.800▓  │
│            │ ● Dibatalkan  1   │ ▓ (kartu gelap)    ▓   │
└────────────┴───────────────────┴────────────────────────┘
┌──────────────────────────────┬───────────────────────────┐
│ TAWARAN TERBARU              │ ▓ Siap panen?            ▓│   baris 3
│ lelang  penawar  harga  ...  │ ▓ Buat lelang baru [▶]   ▓│
│ ...                          ├─────────────┬─────────────┤
│ ...                          │ Pesanan  2  │ Lowongan 3  │
└──────────────────────────────┴─────────────┴─────────────┘
```

```css
.dash     { display: grid; gap: var(--space-5); grid-template-columns: 1fr; }
.dash-r1  { display: grid; gap: var(--space-4); grid-template-columns: 1fr; }
.dash-r2  { display: grid; gap: var(--space-5); grid-template-columns: 1fr; }
.dash-r3  { display: grid; gap: var(--space-5); grid-template-columns: 1fr; }
.mini-grid{ display: grid; gap: var(--space-4); grid-template-columns: 1fr 1fr; }
```

Nilai dasar adalah versi ponsel (satu kolom); lebar kolom untuk tablet dan desktop diatur di bagian 8.

Struktur HTML: `.dash` berisi `.dash-r1` (3 `.stat`), `.dash-r2` (kartu ring, kartu ringkasan, `.card-dark` panen terdekat), dan `.dash-r3` (kartu tabel + kolom kanan berisi banner dan `.mini-grid`).

## 8. Responsif

Pendekatan **mobile-first**: gaya dasar untuk ponsel, diperluas dengan `min-width`. Karena kerangka penuh layar, di semua ukuran konten mengisi sampai tepi layar.

| Breakpoint | Lebar | Perilaku |
|---|---|---|
| Ponsel | < 640px | Satu kolom. Sidebar jadi laci dengan tombol ☰ (SVG) di topbar. Padding konten 16px. Kartu statistik ditumpuk satu per baris. Tabel berubah menjadi daftar kartu. Banner CTA berada tepat setelah baris 1 |
| Tablet | 640px sampai 1023px | Sidebar tetap laci. Baris 1: 3 kartu sejajar. Baris 2: ring dan ringkasan sejajar, kartu panen terdekat di bawahnya. Baris 3: satu kolom |
| Laptop | 1024px sampai 1279px | Sidebar tampil tetap (lebar 230px). Baris 2: 3 kolom. Baris 3: 7fr dan 5fr |
| Desktop | ≥ 1280px | Sidebar 250px, konten penuh, `max-width: 1600px` pada `.content` |

```css
/* Dasar: ponsel. Kerangka menumpuk, sidebar menjadi laci */
.app { display: block; }
.sidebar {
  position: fixed; inset: 0 auto 0 0; width: 270px; height: 100vh; z-index: 50;
  transform: translateX(-100%); transition: transform .25s ease;
}
.sidebar.open { transform: translateX(0); }
.overlay { display: none; position: fixed; inset: 0; background: rgba(5, 33, 36, .5); z-index: 40; }
.overlay.show { display: block; }
.menu-btn { display: inline-flex; width: 44px; height: 44px; align-items: center; justify-content: center; }
.topbar  { padding: var(--space-4); }
.content { padding: var(--space-4); }

/* Tablet */
@media (min-width: 640px) {
  .dash-r1 { grid-template-columns: repeat(3, 1fr); }
  .dash-r2 { grid-template-columns: 1fr 2fr; }
  .dash-r2 > .card-dark { grid-column: 1 / -1; }
  .topbar  { padding: var(--space-6) var(--space-6) var(--space-4); }
  .content { padding: var(--space-6); }
}

/* Laptop ke atas: sidebar tampil tetap */
@media (min-width: 1024px) {
  .app { display: grid; grid-template-columns: 230px 1fr; }
  .sidebar { position: sticky; transform: none; width: auto; }
  .menu-btn, .overlay { display: none !important; }
  .dash-r2 { grid-template-columns: 1fr 1.4fr 1.6fr; }
  .dash-r2 > .card-dark { grid-column: auto; }
  .dash-r3 { grid-template-columns: 7fr 5fr; }
  .topbar  { padding: var(--space-6) var(--space-8) var(--space-4); }
  .content { padding: var(--space-6) var(--space-8) var(--space-10); }
}
@media (min-width: 1280px) { .app { grid-template-columns: 250px 1fr; } }
```

JavaScript vanilla untuk laci menu (`assets/js/app.js`):

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

Aturan umum responsif:
- Viewport wajib: `<meta name="viewport" content="width=device-width, initial-scale=1">` di `includes/header.php`.
- Area sentuh minimal **44×44px** (menu, tombol, ikon).
- Teks tidak di bawah 13px; input 16px agar tidak memicu zoom otomatis di iOS.
- Tidak ada scroll horizontal pada halaman; tabel lebar dibungkus `.table-wrap`.
- Satuan relatif (`rem`, `%`, `fr`, `clamp()`); hindari lebar piksel tetap pada kartu.
- Efek blur dibatasi pada kartu saja (hemat kinerja di ponsel).
- Uji minimal di tiga lebar: **375px**, **768px**, **1366px**, memakai mode responsif DevTools.

## 9. Dashboard Role Lain (ringkas)

| Role | Susunan |
|---|---|
| Tengkulak | Baris statistik (lelang aktif, penawaran menunggu, menang, kalah); tabel lelang aktif terbaru; kartu gelap "Tawaran saya yang sedang unggul"; banner CTA "Jelajahi pasar lelang" |
| Kios | Baris statistik (jumlah produk, stok menipis, pesanan menunggu, selesai bulan ini); tabel pesanan masuk terbaru; banner CTA "Tambah produk" |
| Publik | Tanpa sidebar. Bar atas datar (logo, Beranda, Bursa Kerja, Masuk). Halaman Bursa Kerja: filter di atas, lalu grid kartu lowongan `repeat(auto-fill, minmax(280px, 1fr))` berisi judul, lokasi, upah harian, tanggal mulai, tombol "Lihat kontak" |

Landing page (`index.php`): hero tegas dengan latar gradasi hijau hutan (penuh lebar, tanpa bingkai), judul masalah dan solusi, tiga kartu kaca manfaat (lelang adil, catat keuangan, bursa kerja), tombol Masuk dan Daftar, serta keterkaitan SDG 8.

## 10. Pemetaan File

| Berkas | Isi | PIC |
|---|---|---|
| `assets/css/style.css` | Token, kerangka, komponen, responsif | Agung (perubahan lewat permintaan atau pull request) |
| `assets/js/app.js` | Toggle laci menu, konfirmasi aksi, penutup flash message | Agung |
| `includes/header.php` | `<head>`, viewport, tautan CSS, pembuka `.app` | Agung |
| `includes/sidebar.php` | Menu sesuai `$_SESSION['role']` | Agung |
| `includes/footer.php` | Penutup dan skrip JS | Agung |
| `includes/icons.php` | Fungsi `ikon('nama')` berisi SVG monoline | Agung |

## 11. Aturan untuk Semua Anggota

1. Jangan menulis `style="..."` selain untuk nilai dinamis (`--p`, `--h`, lebar segmen progress).
2. Jangan menambah file CSS lain atau blok `<style>` per halaman; semua gaya di `style.css`.
3. Gunakan kelas komponen di bagian 6. Butuh komponen baru: minta ke Agung dahulu agar tidak ada gaya ganda.
4. Radius dan efek kaca hanya untuk komponen. Jangan menambahkannya ke kerangka halaman.
5. Satu halaman = satu `<h1>` (judul halaman); judul kartu memakai `<h2>`.
6. Semua halaman terproteksi diawali `include` header dan sidebar, diakhiri footer.
7. Setelah selesai membuat halaman, uji di 375px, 768px, dan 1366px.

## 12. Checklist Desain

- [ ] Token `:root` terpasang dan dipakai di semua komponen
- [ ] Kerangka penuh layar: tidak ada radius, margin, atau blur pada `.app`, `.sidebar`, `.main`, `.topbar`
- [ ] Sidebar hijau hutan bergradasi dengan menu aktif bersinar hijau
- [ ] Area konten bergradasi mint dengan dua semburat cahaya
- [ ] Kartu kaca (radius 20px, blur, garis tepi terang) dipakai konsisten
- [ ] Kartu statistik dengan mini grafik, cincin progress, badge, tombol, form, alert tersedia sebagai kelas
- [ ] Ikon SVG monoline menggantikan emoji berwarna di menu
- [ ] Dashboard petani sesuai pemetaan bagian 7.1
- [ ] Sidebar menjadi laci di bawah 1024px dan berfungsi
- [ ] Tabel menjadi kartu di bawah 640px
- [ ] Tidak ada scroll horizontal di 375px
- [ ] Tidak ada library, font, atau ikon dari luar
- [ ] Teks isi memiliki kontras cukup; teks di tombol hijau berwarna gelap