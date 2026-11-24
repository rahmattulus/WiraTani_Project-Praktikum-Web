<?php
/* Landing page WiraTani (S08). Bagian dari rencana Agung, dibuat oleh Marvian
   agar alamat utama tidak lagi kosong. Mengikuti DESAIN.md: hero masalah + solusi,
   tiga kartu manfaat, tombol Masuk dan Daftar, serta kaitan dengan SDG 8. */
require_once __DIR__ . '/includes/auth_guard.php';

// Pengguna yang sudah masuk langsung diteruskan ke dashboard role-nya.
if (sudah_login()) {
  redirect(url_dashboard($_SESSION['role']));
}

if (!function_exists('wt_ikon')) {
    function wt_ikon(string $k): string {
        static $p = [
            'tag'    => '<path d="M3.5 4.5A1.5 1.5 0 0 1 5 3h6.6a2 2 0 0 1 1.4.6l7.4 7.4a2 2 0 0 1 0 2.8l-6.6 6.6a2 2 0 0 1-2.8 0L3.6 13A2 2 0 0 1 3 11.6z"/><circle cx="7.5" cy="7.5" r="1.3"/>',
            'doc'    => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
            'worker' => '<path d="M8.5 8a3.5 3.5 0 0 1 7 0"/><path d="M6 8h12"/><path d="M9 8v1.5a3 3 0 0 0 6 0V8"/><path d="M5 20c0-3.3 3.1-5.5 7-5.5s7 2.2 7 5.5"/>',
            'cart'   => '<path d="M3 4h2.5l2 11h10l2-8H6.5"/><circle cx="9" cy="19" r="1.3"/><circle cx="17" cy="19" r="1.3"/>',
            'sprout' => '<path d="M12 21v-9"/><path d="M12 12c0-4 2.5-6.5 7-6.5 0 4-2.5 6.5-7 6.5z"/><path d="M12 14.5c0-3-2-5-5.5-5 0 3 2 5 5.5 5z"/>',
            'gavel'  => '<rect x="9" y="5.5" width="12" height="7" rx="1.5" transform="rotate(45 15 9)"/><line x1="4.5" y1="19.5" x2="12.5" y2="11.5"/><line x1="3" y1="21" x2="11" y2="21"/>',
            'box'    => '<path d="M12 3l8 4.2v9.6L12 21l-8-4.2V7.2z"/><path d="M4 7.2l8 4.3 8-4.3M12 11.5V21"/>',
            'eye'    => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'lock'   => '<rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
            'chart'  => '<path d="M4 20V10M10 20V4M16 20v-7M2 20h20"/>',
            'check'  => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
            'users'  => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 5.2a3.2 3.2 0 0 1 0 5.6M18 14.8c1.8.7 3 2.4 3 5.2"/>',
        ];
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$k] ?? '') . '</svg>';
    }
}

/* ---------- DATA (edit teks di sini) ---------- */

// 1. Masalah
$wt_masalah = [
    ['tag',    '',      'Harga ditentukan sepihak',    'Petani sering hanya punya satu pembeli, sehingga harga panen sulit ditawar.'],
    ['doc',    'blue',  'Biaya tanam tidak tercatat',  'Tanpa catatan modal, petani tidak tahu apakah satu musim tanam untung atau rugi.'],
    ['worker', 'amber', 'Upah buruh tidak jelas',      'Buruh tani mencari kerja dari mulut ke mulut, dengan upah dan lokasi yang tidak pasti.'],
    ['cart',   '',      'Sarana tani sulit dicari',    'Pupuk, bibit, dan sewa alat tersebar di banyak kios tanpa katalog yang mudah dibandingkan.'],
];

// 2. Peran
$wt_peran = [
    ['sprout', '',      'Petani',    ['Membuka lelang hasil panen', 'Mencatat keuangan tiap musim tanam', 'Memesan saprotan dan membuka lowongan'], 'Daftar sebagai petani',    'auth/register.php'],
    ['gavel',  'blue',  'Tengkulak', ['Menjelajahi panen yang sedang dilelang', 'Mengajukan harga tawar per kilogram', 'Melihat tawaran lain secara terbuka'], 'Daftar sebagai tengkulak', 'auth/register.php'],
    ['box',    'amber', 'Kios Tani', ['Mengelola katalog pupuk dan alat', 'Memperbarui stok produk', 'Menerima pesanan dan sewa alat'],    'Daftar sebagai kios',      'auth/register.php'],
    ['worker', '',      'Buruh Tani', ['Melihat lowongan tanpa perlu akun', 'Mengetahui upah harian di awal', 'Menghubungi petani secara langsung'], 'Lihat bursa kerja', 'publik/bursa.php'],
];
$wt_alur = ['Petani membuka lelang', 'Tengkulak menawar', 'Pendapatan tercatat di ERP', 'Petani memesan saprotan', 'Petani membuka lowongan'];

// 3. Cara kerja
$wt_langkah = [
    ['Buat akun',        'Daftar dengan email dan pilih satu peran yang sesuai dengan usahamu.'],
    ['Lengkapi aktivitas', 'Petani mengisi data panen, tengkulak memilih lelang, kios menambah produk.'],
    ['Bertransaksi terbuka', 'Penawaran, pesanan, dan lowongan tampil jelas bagi pihak yang berkepentingan.'],
    ['Pantau hasilnya',  'Pendapatan, biaya, dan laba per musim tanam terekap di dashboard.'],
];

// 5. SDG 8
$wt_sdg = [
    ['users',  'Pekerjaan layak',        'Upah harian, lokasi, dan durasi kerja ditampilkan terbuka di bursa kerja.'],
    ['chart',  'Pertumbuhan ekonomi',    'Persaingan penawaran membuat harga panen terbentuk lebih adil bagi petani.'],
    ['doc',    'Dukungan usaha kecil',   'Pencatatan keuangan membantu petani menghitung modal dan laba tiap musim.'],
    ['eye',    'Akses informasi',        'Buruh tani dapat melihat lowongan tanpa harus mendaftar lebih dulu.'],
];

// 6. Pembeda
$wt_pembeda = [
    ['eye',   'Penawaran terbuka',   'Tengkulak melihat daftar tawaran yang bersaing, bukan tawar-menawar tertutup.'],
    ['lock',  'Peran terkunci',      'Satu akun untuk satu peran, jadi hak akses tiap pengguna tetap jelas.'],
    ['users', 'Lowongan untuk umum', 'Bursa kerja bisa dibuka siapa saja tanpa membuat akun.'],
    ['chart', 'Pencatatan otomatis', 'Pendapatan dari lelang yang berhasil masuk ke rekap keuangan musim tanam.'],
];

// 7. FAQ  (EDIT: sesuaikan jawaban dengan aturan bisnis final kalian)
$wt_faq = [
    ['Bisakah satu akun memiliki dua peran?', 'Tidak. Satu akun hanya untuk satu peran, dan peran tidak dapat diubah setelah mendaftar. Jika ingin berperan lain, buat akun baru dengan email yang berbeda.'],
    ['Apakah buruh tani harus mendaftar?', 'Tidak perlu. Siapa pun dapat membuka halaman bursa kerja, melihat detail lowongan, dan menghubungi petani yang membukanya.'],
    ['Bagaimana pembayaran setelah lelang selesai?', 'Metode pembayaran dan armada angkut dituliskan tengkulak pada catatan penawaran, lalu disepakati langsung dengan petani. WiraTani belum memproses pembayaran.'],
    ['Siapa yang bisa melihat penawaran lelang?', 'Tengkulak dapat melihat daftar tawaran yang masuk pada sebuah lelang, sedangkan petani memilih penawaran terbaik untuk hasil panennya.'],
    ['Dari mana pendapatan di catatan keuangan berasal?', 'Pendapatan terekap otomatis dari lelang panen yang berhasil. Biaya seperti pupuk, bibit, dan sewa alat dicatat petani pada musim tanam yang bersangkutan.'],
];

// 8. Tim  (EDIT: ganti dengan nama dan peran asli)
$wt_tim = [
    ['A', 'Nama Anggota 1', 'Peran, misalnya Modul Petani dan ERP'],
    ['B', 'Nama Anggota 2', 'Peran, misalnya Modul Tengkulak dan Lelang'],
    ['C', 'Nama Anggota 3', 'Peran, misalnya Modul Kios Tani dan Bursa Kerja'],
];


$layout        = 'publik';
$judul_halaman = 'Panen dihargai adil, usaha tani tercatat rapi';
include __DIR__ . '/includes/header.php';
?>

<div class="dash">
  <section class="hero">
    <p><strong>Ekosistem usaha tani digital</strong></p>
    <h1>Panen dihargai adil,<br>usaha tani tercatat rapi</h1>
    <p>
      WiraTani mempertemukan petani, tengkulak, kios tani, dan buruh tani
      dalam satu platform untuk membuat transaksi lebih terbuka, modal usaha
      mudah tercatat, dan pekerjaan tani lebih transparan.
    </p>
    <div class="form-actions hero-actions">
      <a class="btn btn-primary" href="<?= BASE_URL ?>/auth/register.php">Mulai sekarang</a>
      <a class="btn btn-ghost" href="<?= BASE_URL ?>/publik/bursa.php">Lihat bursa kerja</a>
    </div>
  </section>

  <section class="feature-grid" aria-label="Manfaat WiraTani">
    <article class="card">
      <span class="badge badge-info">Lelang</span>
      <h2 class="card-title">Lelang panen yang adil</h2>
      <p>
        Petani membuka lelang hasil panen. Tengkulak mengirim penawaran, dan petani
        memilih penawaran terbaik, sehingga harga tidak lagi ditentukan sepihak.
      </p>
      <a class="card-link" href="<?= BASE_URL ?>/auth/login.php">Kelola lelang</a>
    </article>

    <article class="card">
      <span class="badge badge-success">ERP</span>
      <h2 class="card-title">Catat keuangan tiap musim</h2>
      <p>
        Pendapatan dari lelang dan biaya pupuk, bibit, serta sewa alat dari kios masuk
        ke catatan musim tanam, lengkap dengan laba atau rugi.
      </p>
      <a class="card-link" href="<?= BASE_URL ?>/auth/login.php">Lihat pencatatan</a>
    </article>

    <article class="card">
      <span class="badge badge-warning">Bursa kerja</span>
      <h2 class="card-title">Bursa kerja buruh tani</h2>
      <p>
        Petani memasang lowongan kerja harian dengan upah yang jelas. Buruh tani bisa
        melihat dan menghubungi petani tanpa perlu daftar.
      </p>
      <a class="card-link" href="<?= BASE_URL ?>/publik/bursa.php">Lihat lowongan</a>
    </article>
  </section>

  <section class="cta" aria-label="Kaitan dengan SDG 8">
    <div class="cta-content">
      <h2>Mendukung SDG 8: pekerjaan layak dan pertumbuhan ekonomi</h2>
      <p>
        Harga yang lebih adil dan peluang kerja yang terbuka membantu pelaku usaha tani
        kecil tumbuh dengan penghasilan yang lebih layak.
      </p>
      <a class="btn" href="<?= BASE_URL ?>/auth/register.php">Mulai sekarang</a>
    </div>
    <span class="cta-decoration" aria-hidden="true"></span>
  </section>
</div>

<div class="wt-sec">

  <!-- 1. MASALAH -->
  <section id="masalah" class="wt-block">
    <div class="wt-head">
      <span class="wt-eyebrow">Latar belakang</span>
      <h2>Kenapa WiraTani ada</h2>
      <p class="wt-lead">Banyak usaha tani kecil tumbuh lambat bukan karena kurang kerja keras, tetapi karena harga, pencatatan, dan informasi pekerjaan tidak berpihak kepada mereka.</p>
    </div>
    <div class="wt-grid wt-grid-4">
      <?php foreach ($wt_masalah as [$ic, $warna, $judul, $isi]): ?>
        <article class="wt-card">
          <div class="wt-ico <?= $warna ? 'wt-ico--' . $warna : '' ?>"><?= wt_ikon($ic) ?></div>
          <h3><?= $judul ?></h3>
          <p><?= $isi ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 2. PERAN -->
  <section id="peran" class="wt-block">
    <div class="wt-head">
      <span class="wt-eyebrow">Ekosistem</span>
      <h2>Satu platform, empat peran yang saling terhubung</h2>
      <p class="wt-lead">Setiap pihak punya ruang kerja sendiri, tetapi hasil kerjanya saling melengkapi.</p>
    </div>
    <div class="wt-grid wt-grid-4">
      <?php foreach ($wt_peran as [$ic, $warna, $nama, $poin, $label, $url]): ?>
        <article class="wt-card wt-role">
          <div class="wt-ico <?= $warna ? 'wt-ico--' . $warna : '' ?>"><?= wt_ikon($ic) ?></div>
          <h3><?= $nama ?></h3>
          <ul>
            <?php foreach ($poin as $p): ?><li><?= $p ?></li><?php endforeach; ?>
          </ul>
          <a class="wt-link" href="<?= $url ?>"><?= $label ?> &rarr;</a>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="wt-flow" aria-label="Alur ekosistem WiraTani">
      <?php foreach ($wt_alur as $a): ?><span><?= $a ?></span><?php endforeach; ?>
    </div>
  </section>

  <!-- 3. CARA KERJA -->
  <section id="cara-kerja" class="wt-block">
    <div class="wt-head">
      <span class="wt-eyebrow">Cara kerja</span>
      <h2>Mulai dalam empat langkah</h2>
    </div>
    <ol class="wt-steps">
      <?php foreach ($wt_langkah as $i => [$judul, $isi]): ?>
        <li class="wt-card">
          <span class="wt-step-no"><?= $i + 1 ?></span>
          <h3><?= $judul ?></h3>
          <p><?= $isi ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </section>

  <!-- 4. FITUR UNGGULAN -->
  <section id="fitur" class="wt-block">
    <div class="wt-head">
      <span class="wt-eyebrow">Fitur unggulan</span>
      <h2>Dua hal yang paling mengubah cara kerja petani</h2>
    </div>

    <div class="wt-feature">
      <div class="wt-feature-text">
        <h3>Lelang panen yang transparan</h3>
        <p>Petani mempublikasikan komoditas, estimasi tonase, lokasi, tanggal panen, dan harga dasar. Tengkulak mengirim harga tawar per kilogram beserta catatan pembayaran dan armada angkut. Semua tawaran tampil berdampingan sehingga petani bisa memilih yang terbaik.</p>
        <ul class="wt-checks">
          <li><?= wt_ikon('check') ?> Harga dasar ditetapkan petani</li>
          <li><?= wt_ikon('check') ?> Tawaran bersaing terlihat terbuka</li>
          <li><?= wt_ikon('check') ?> Catatan pembayaran dan angkutan ikut disertakan</li>
        </ul>
      </div>
      <!-- EDIT: ganti blok .wt-mock ini dengan <img src="assets/img/lelang.png" alt="Halaman lelang"> kalau sudah ada screenshot -->
      <div class="wt-feature-visual">
        <div class="wt-mock">
          <span class="wt-mock-tag">Contoh ilustrasi</span>
          <h4>Padi · 2 ton</h4>
          <small>Harga dasar Rp 6.000 / kg</small>
          <div class="wt-bid wt-bid--top"><span>Tawaran 1</span><strong>Rp 6.350 / kg</strong></div>
          <div class="wt-bid"><span>Tawaran 2</span><strong>Rp 6.200 / kg</strong></div>
          <div class="wt-bid"><span>Tawaran 3</span><strong>Rp 6.050 / kg</strong></div>
        </div>
      </div>
    </div>

    <div class="wt-feature wt-feature--rev">
      <div class="wt-feature-text">
        <h3>Catatan keuangan tiap musim tanam</h3>
        <p>Biaya pupuk, bibit, dan sewa alat dicatat per musim tanam, sementara pendapatan dari lelang yang berhasil terekap otomatis. Petani langsung melihat estimasi laba atau rugi bersih setiap siklus.</p>
        <ul class="wt-checks">
          <li><?= wt_ikon('check') ?> Modal operasional tercatat rapi</li>
          <li><?= wt_ikon('check') ?> Pendapatan terisi dari lelang yang berhasil</li>
          <li><?= wt_ikon('check') ?> Laba atau rugi dihitung per musim</li>
        </ul>
      </div>
      <!-- EDIT: ganti dengan screenshot halaman ERP -->
      <div class="wt-feature-visual">
        <div class="wt-mock">
          <span class="wt-mock-tag">Contoh ilustrasi</span>
          <h4>Musim tanam ke-1</h4>
          <div class="wt-row"><span>Pendapatan</span><strong>Rp 18.500.000</strong></div>
          <div class="wt-row"><span>Biaya modal</span><strong>Rp 11.200.000</strong></div>
          <div class="wt-row wt-row--sum"><span>Laba bersih</span><strong>Rp 7.300.000</strong></div>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. DAMPAK SDG 8 -->
  <section id="sdg8" class="wt-block">
    <div class="wt-dark">
      <div class="wt-head">
        <span class="wt-eyebrow">SDG 8</span>
        <h2>Pekerjaan layak dan pertumbuhan ekonomi, dimulai dari ladang</h2>
        <p class="wt-lead">WiraTani menerjemahkan tujuan pembangunan berkelanjutan ke dalam fitur yang bisa dipakai sehari-hari.</p>
      </div>
      <div class="wt-grid wt-grid-4">
        <?php foreach ($wt_sdg as [$ic, $judul, $isi]): ?>
          <article class="wt-sdg-item">
            <div class="wt-ico wt-ico--lime"><?= wt_ikon($ic) ?></div>
            <h3><?= $judul ?></h3>
            <p><?= $isi ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- 6. PEMBEDA -->
  <section id="pembeda" class="wt-block">
    <div class="wt-head">
      <span class="wt-eyebrow">Keunggulan</span>
      <h2>Kenapa WiraTani layak dipercaya</h2>
    </div>
    <div class="wt-grid wt-grid-4">
      <?php foreach ($wt_pembeda as [$ic, $judul, $isi]): ?>
        <article class="wt-trust">
          <div class="wt-ico wt-ico--sm"><?= wt_ikon($ic) ?></div>
          <div>
            <h3><?= $judul ?></h3>
            <p><?= $isi ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 7. FAQ -->
  <section id="faq" class="wt-block wt-block--narrow">
    <div class="wt-head">
      <span class="wt-eyebrow">FAQ</span>
      <h2>Pertanyaan yang sering muncul</h2>
    </div>
    <div class="wt-faq">
      <?php foreach ($wt_faq as [$tanya, $jawab]): ?>
        <details>
          <summary><?= $tanya ?></summary>
          <p><?= $jawab ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </section>


</div>
<?php include __DIR__ . '/includes/footer.php'; ?>