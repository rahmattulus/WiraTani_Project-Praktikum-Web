<?php
/* Bursa kerja publik: hanya lowongan berstatus buka. */
require_once __DIR__ . '/../includes/helper.php';
require_once __DIR__ . '/../config/koneksi.php';

/* ---------- Helper tampilan khusus halaman ini ---------- */
if (!function_exists('bk_mulai')) {
    /** Label relatif tanggal mulai: "Mulai hari ini", "Mulai 3 hari lagi", "Sudah berjalan". */
    function bk_mulai(string $tanggal): string {
        $t = strtotime($tanggal);
        if (!$t) return '';
        $selisih = (int) floor(($t - strtotime(date('Y-m-d'))) / 86400);
        if ($selisih < 0)   return 'Sudah berjalan';
        if ($selisih === 0) return 'Mulai hari ini';
        return "Mulai $selisih hari lagi";
    }
}
if (!function_exists('bk_ikon')) {
    function bk_ikon(string $k): string {
        static $p = [
            'pin'      => '<path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
            'users'    => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 5.2a3.2 3.2 0 0 1 0 5.6M18 14.8c1.8.7 3 2.4 3 5.2"/>',
            'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M3 10h18M8 3v4M16 3v4"/>',
            'search'   => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
            'sprout'   => '<path d="M12 21v-9"/><path d="M12 12c0-4 2.5-6.5 7-6.5 0 4-2.5 6.5-7 6.5z"/><path d="M12 14.5c0-3-2-5-5.5-5 0 3 2 5 5.5 5z"/>',
            'wheat'    => '<path d="M12 21V9"/><path d="M12 9c-2.2 0-3.2-1.4-3.2-3.4 2.2 0 3.2 1.4 3.2 3.4zM12 9c2.2 0 3.2-1.4 3.2-3.4-2.2 0-3.2 1.4-3.2 3.4z"/><path d="M12 15c-2.2 0-3.2-1.4-3.2-3.4 2.2 0 3.2 1.4 3.2 3.4zM12 15c2.2 0 3.2-1.4 3.2-3.4-2.2 0-3.2 1.4-3.2 3.4z"/>',
            'worker'   => '<path d="M8.5 8a3.5 3.5 0 0 1 7 0"/><path d="M6 8h12"/><path d="M9 8v1.5a3 3 0 0 0 6 0V8"/><path d="M5 20c0-3.3 3.1-5.5 7-5.5s7 2.2 7 5.5"/>',
            'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        ];
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$k] ?? '') . '</svg>';
    }
}

/* ---------- Filter (logika asli) ---------- */
$jenis_pekerjaan = $_GET['jenis_pekerjaan'] ?? '';
if (!is_string($jenis_pekerjaan)) {
    $jenis_pekerjaan = '';
} else {
    $jenis_pekerjaan = trim($jenis_pekerjaan);
}
if (!in_array($jenis_pekerjaan, ['', 'tanam', 'panen', 'lainnya'], true)) {
    $jenis_pekerjaan = '';
}
$lokasi = $_GET['lokasi'] ?? '';
if (!is_string($lokasi)) {
    $lokasi = '';
} else {
    $lokasi = trim($lokasi);
}
$lokasi_cari = '%' . $lokasi . '%';

/* ---------- Urutan (baru, memakai daftar putih agar aman dari SQL injection) ---------- */
$urut = $_GET['urut'] ?? 'mulai';
if (!is_string($urut) || !in_array($urut, ['mulai', 'terbaru', 'upah'], true)) {
    $urut = 'mulai';
}
$daftar_urut = [
    'mulai'   => 'l.tanggal_mulai ASC, l.created_at DESC',   // perilaku asli
    'terbaru' => 'l.created_at DESC',
    'upah'    => 'l.upah_harian DESC, l.tanggal_mulai ASC',
];
$order_by = $daftar_urut[$urut];

$label_pekerjaan = [
    'tanam' => 'Tanam',
    'panen' => 'Panen',
    'lainnya' => 'Lainnya',
];
$ikon_pekerjaan = [
    'tanam' => 'sprout',
    'panen' => 'wheat',
    'lainnya' => 'worker',
];

$stmt = $koneksi->prepare(
    "SELECT l.id, l.judul, l.jenis_pekerjaan, l.lokasi, l.jumlah_buruh,
            l.upah_harian, l.tanggal_mulai, l.durasi_hari, l.deskripsi,
            u.nama AS nama_petani
     FROM lowongan_kerja l
     INNER JOIN users u ON u.id = l.user_id
     WHERE l.status = 'buka'
       AND (? = '' OR l.jenis_pekerjaan = ?)
       AND (? = '' OR l.lokasi LIKE ?)
     ORDER BY $order_by"
);
$stmt->bind_param('ssss', $jenis_pekerjaan, $jenis_pekerjaan, $lokasi, $lokasi_cari);
$stmt->execute();
$lowongan_tampil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ---------- Statistik hero: seluruh lowongan buka, tidak terpengaruh filter ---------- */
$ringkas = $koneksi->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(jumlah_buruh), 0) AS total_buruh,
            COALESCE(AVG(upah_harian), 0) AS rata_upah
     FROM lowongan_kerja
     WHERE status = 'buka'"
)->fetch_assoc();

/* URL chip jenis: pertahankan lokasi & urutan */
$url_bursa = BASE_URL . '/publik/bursa.php';
$url_chip = function (string $jenis) use ($url_bursa, $lokasi, $urut): string {
    $q = array_filter(
        ['jenis_pekerjaan' => $jenis, 'lokasi' => $lokasi, 'urut' => $urut === 'mulai' ? '' : $urut],
        fn($v) => $v !== ''
    );
    return $url_bursa . ($q ? '?' . http_build_query($q) : '');
};

$layout = 'publik';
$judul_halaman = 'Bursa Kerja Buruh Tani';
include __DIR__ . '/../includes/header.php';
?>
    <div class="bk-page">

      <!-- HERO + STATISTIK -->
      <header class="bk-hero">
        <div class="bk-hero-text">
          <span class="bk-eyebrow">Bursa kerja pertanian</span>
          <h1><?= e($judul_halaman) ?></h1>
          <p>Temukan pekerjaan tani harian dengan lokasi dan upah yang jelas. Lihat lowongan langsung dari petani, tanpa perlu membuat akun.</p>
        </div>
        <dl class="bk-stats">
          <div><dt>Lowongan aktif</dt><dd><?= (int) $ringkas['total'] ?></dd></div>
          <div><dt>Pekerja dibutuhkan</dt><dd><?= number_format((int) $ringkas['total_buruh'], 0, ',', '.') ?></dd></div>
          <div><dt>Rata-rata upah / hari</dt><dd><?= e(format_rupiah((float) $ringkas['rata_upah'])) ?></dd></div>
        </dl>
      </header>

      <!-- FILTER -->
      <form class="bk-filter" method="get" action="">
        <input type="hidden" name="jenis_pekerjaan" value="<?= e($jenis_pekerjaan) ?>">
        <label class="bk-field bk-field--grow">
          <span>Lokasi</span>
          <div class="bk-input">
            <?= bk_ikon('search') ?>
            <input type="search" id="lokasi" name="lokasi" value="<?= e($lokasi) ?>"
                   placeholder="Cari desa atau kecamatan">
          </div>
        </label>
        <label class="bk-field">
          <span>Urutkan</span>
          <div class="bk-input">
            <select name="urut">
              <option value="mulai"   <?= $urut === 'mulai'   ? 'selected' : '' ?>>Mulai terdekat</option>
              <option value="terbaru" <?= $urut === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
              <option value="upah"    <?= $urut === 'upah'    ? 'selected' : '' ?>>Upah tertinggi</option>
            </select>
          </div>
        </label>
        <button class="bk-btn" type="submit">Cari lowongan</button>
        <?php if ($jenis_pekerjaan !== '' || $lokasi !== '' || $urut !== 'mulai'): ?>
          <a class="bk-btn bk-btn--ghost" href="<?= e($url_bursa) ?>">Hapus filter</a>
        <?php endif; ?>
      </form>

      <!-- CHIP JENIS PEKERJAAN -->
      <nav class="bk-chips" aria-label="Jenis pekerjaan">
        <a href="<?= e($url_chip('')) ?>" class="<?= $jenis_pekerjaan === '' ? 'is-active' : '' ?>">Semua jenis</a>
        <?php foreach ($label_pekerjaan as $nilai => $label): ?>
          <a href="<?= e($url_chip($nilai)) ?>" class="<?= $jenis_pekerjaan === $nilai ? 'is-active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>

      <!-- DAFTAR LOWONGAN -->
      <?php if (!$lowongan_tampil): ?>
        <div class="bk-empty">
          <div class="bk-empty-ico"><?= bk_ikon('search') ?></div>
          <h2>Belum ada lowongan yang cocok</h2>
          <p>Belum ada lowongan terbuka yang cocok dengan pencarian ini. Coba ganti jenis pekerjaan atau kosongkan kolom lokasi.</p>
          <a class="bk-btn bk-btn--ghost" href="<?= e($url_bursa) ?>">Tampilkan semua lowongan</a>
        </div>
      <?php else: ?>
        <section class="bk-grid" aria-label="Daftar lowongan terbuka">
          <?php foreach ($lowongan_tampil as $lowongan):
              $inisial = strtoupper(mb_substr($lowongan['nama_petani'], 0, 1));
          ?>
            <article class="bk-card">
              <div class="bk-card-top">
                <div class="bk-jenis-ico"><?= bk_ikon($ikon_pekerjaan[$lowongan['jenis_pekerjaan']] ?? 'worker') ?></div>
                <div class="bk-badges">
                  <span class="bk-badge bk-badge--buka">Buka</span>
                  <span class="bk-badge bk-badge--jenis"><?= e($label_pekerjaan[$lowongan['jenis_pekerjaan']]) ?></span>
                </div>
              </div>

              <h2 class="bk-judul"><?= e($lowongan['judul']) ?></h2>
              <p class="bk-lokasi"><?= bk_ikon('pin') ?> <?= e($lowongan['lokasi']) ?></p>

              <div class="bk-upah">
                <strong><?= e(format_rupiah($lowongan['upah_harian'])) ?></strong>
                <span>/ hari</span>
              </div>

              <ul class="bk-meta">
                <li><?= bk_ikon('users') ?> <?= (int) $lowongan['jumlah_buruh'] ?> orang</li>
                <li><?= bk_ikon('clock') ?> <?= (int) $lowongan['durasi_hari'] ?> hari</li>
                <li><?= bk_ikon('calendar') ?> <?= e(format_tanggal($lowongan['tanggal_mulai'])) ?></li>
              </ul>
              <p class="bk-mulai"><?= e(bk_mulai($lowongan['tanggal_mulai'])) ?></p>

              <?php if (!empty($lowongan['deskripsi'])): ?>
                <p class="bk-desk"><?= e($lowongan['deskripsi']) ?></p>
              <?php endif; ?>

              <footer class="bk-card-foot">
                <div class="bk-petani">
                  <span class="bk-avatar"><?= e($inisial) ?></span>
                  <span><small>Dibuka oleh</small><?= e($lowongan['nama_petani']) ?></span>
                </div>
                <a class="bk-link" href="<?= BASE_URL ?>/publik/detail_lowongan.php?id=<?= (int) $lowongan['id'] ?>">
                  Detail dan kontak <?= bk_ikon('arrow') ?>
                </a>
              </footer>
            </article>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>

    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>