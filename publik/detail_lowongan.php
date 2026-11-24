<?php
/* Detail lowongan publik; hanya menampilkan lowongan yang masih buka. */
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
            'wallet'   => '<rect x="3" y="5.5" width="18" height="13" rx="2.5"/><path d="M21 10.5h-4.5a2.5 2.5 0 0 0 0 5H21"/><circle cx="16.8" cy="13" r="0.6" fill="currentColor"/>',
            'sprout'   => '<path d="M12 21v-9"/><path d="M12 12c0-4 2.5-6.5 7-6.5 0 4-2.5 6.5-7 6.5z"/><path d="M12 14.5c0-3-2-5-5.5-5 0 3 2 5 5.5 5z"/>',
            'wheat'    => '<path d="M12 21V9"/><path d="M12 9c-2.2 0-3.2-1.4-3.2-3.4 2.2 0 3.2 1.4 3.2 3.4zM12 9c2.2 0 3.2-1.4 3.2-3.4-2.2 0-3.2 1.4-3.2 3.4z"/><path d="M12 15c-2.2 0-3.2-1.4-3.2-3.4 2.2 0 3.2 1.4 3.2 3.4zM12 15c2.2 0 3.2-1.4 3.2-3.4-2.2 0-3.2 1.4-3.2 3.4z"/>',
            'worker'   => '<path d="M8.5 8a3.5 3.5 0 0 1 7 0"/><path d="M6 8h12"/><path d="M9 8v1.5a3 3 0 0 0 6 0V8"/><path d="M5 20c0-3.3 3.1-5.5 7-5.5s7 2.2 7 5.5"/>',
            'phone'    => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
            'chat'     => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1.1-4.6A8 8 0 1 1 21 12z"/>',
            'back'     => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
            'search'   => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
            'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        ];
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$k] ?? '') . '</svg>';
    }
}

/* ---------- Ambil data (logika asli) ---------- */
$id_input = $_GET['id'] ?? '';
if (!is_string($id_input)) {
    $id_input = '';
} else {
    $id_input = trim($id_input);
}

$lowongan_detail = null;
if (ctype_digit($id_input) && (int) $id_input > 0) {
    $id = (int) $id_input;
    $stmt = $koneksi->prepare(
        "SELECT l.id, l.judul, l.jenis_pekerjaan, l.lokasi, l.jumlah_buruh,
                l.upah_harian, l.tanggal_mulai, l.durasi_hari, l.deskripsi,
                u.nama AS nama_petani, u.no_telepon, u.alamat
         FROM lowongan_kerja l
         INNER JOIN users u ON u.id = l.user_id
         WHERE l.id = ? AND l.status = 'buka'"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $lowongan_detail = $stmt->get_result()->fetch_assoc();
}

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
$nomor_telepon = $lowongan_detail
    ? preg_replace('/[^0-9+]/', '', $lowongan_detail['no_telepon'])
    : '';

/* ---------- Turunan untuk tampilan baru ---------- */
$url_bursa = BASE_URL . '/publik/bursa.php';
$url_wa = '';
$total_estimasi = 0;
$tanggal_selesai = '';
if ($lowongan_detail) {
    // Nomor WhatsApp format internasional: 0812... -> 62812...
    $digit = preg_replace('/\D+/', '', (string) $lowongan_detail['no_telepon']);
    if ($digit !== '') {
        if (substr($digit, 0, 1) === '0')       $digit = '62' . substr($digit, 1);
        elseif (substr($digit, 0, 2) !== '62')  $digit = '62' . $digit;
        $pesan = 'Halo ' . $lowongan_detail['nama_petani'] . ', saya melihat lowongan "'
               . $lowongan_detail['judul'] . '" di WiraTani dan tertarik untuk bekerja. '
               . 'Apakah masih membutuhkan tenaga?';
        $url_wa = 'https://wa.me/' . $digit . '?text=' . rawurlencode($pesan);
    }
    $total_estimasi = (float) $lowongan_detail['upah_harian'] * (int) $lowongan_detail['durasi_hari'];
    $mulai_ts = strtotime($lowongan_detail['tanggal_mulai']);
    if ($mulai_ts && (int) $lowongan_detail['durasi_hari'] > 0) {
        $tanggal_selesai = date('Y-m-d', strtotime('+' . ((int) $lowongan_detail['durasi_hari'] - 1) . ' days', $mulai_ts));
    }
}

$layout = 'publik';
$judul_halaman = $lowongan_detail ? $lowongan_detail['judul'] : 'Lowongan Tidak Tersedia';
include __DIR__ . '/../includes/header.php';
?>
    <!-- EDIT: kalau header.php punya tempat khusus CSS tambahan, pindahkan baris ini ke sana -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/detail_lowongan.css">

    <div class="bd-page">
      <a class="bd-back" href="<?= e($url_bursa) ?>"><?= bk_ikon('back') ?> Kembali ke bursa kerja</a>

      <?php if (!$lowongan_detail): ?>
        <section class="bd-empty">
          <div class="bd-empty-ico"><?= bk_ikon('search') ?></div>
          <h1>Lowongan tidak tersedia</h1>
          <p>Lowongan ini tidak ditemukan atau sudah ditutup.</p>
          <a class="bd-btn" href="<?= e($url_bursa) ?>">Kembali ke bursa kerja</a>
        </section>
      <?php else: ?>

        <!-- HERO -->
        <header class="bd-hero">
          <div class="bd-hero-main">
            <div class="bd-badges">
              <span class="bd-badge bd-badge--buka">Buka</span>
              <span class="bd-badge bd-badge--jenis">
                <?= bk_ikon($ikon_pekerjaan[$lowongan_detail['jenis_pekerjaan']] ?? 'worker') ?>
                <?= e($label_pekerjaan[$lowongan_detail['jenis_pekerjaan']]) ?>
              </span>
            </div>
            <h1><?= e($lowongan_detail['judul']) ?></h1>
            <p class="bd-lokasi"><?= bk_ikon('pin') ?> <?= e($lowongan_detail['lokasi']) ?></p>
            <p class="bd-mulai"><?= e(bk_mulai($lowongan_detail['tanggal_mulai'])) ?></p>
          </div>
          <div class="bd-upah">
            <small>Upah harian</small>
            <strong><?= e(format_rupiah($lowongan_detail['upah_harian'])) ?></strong>
            <span>per orang per hari</span>
          </div>
        </header>

        <div class="bd-layout">
          <div class="bd-main">

            <!-- RINGKASAN -->
            <section class="bd-tiles" aria-label="Ringkasan lowongan">
              <article class="bd-tile">
                <div class="bd-tile-ico"><?= bk_ikon('users') ?></div>
                <p class="bd-tile-label">Kebutuhan buruh</p>
                <p class="bd-tile-value"><?= (int) $lowongan_detail['jumlah_buruh'] ?> orang</p>
              </article>
              <article class="bd-tile">
                <div class="bd-tile-ico"><?= bk_ikon('calendar') ?></div>
                <p class="bd-tile-label">Tanggal mulai</p>
                <p class="bd-tile-value"><?= e(format_tanggal($lowongan_detail['tanggal_mulai'])) ?></p>
              </article>
              <article class="bd-tile">
                <div class="bd-tile-ico"><?= bk_ikon('clock') ?></div>
                <p class="bd-tile-label">Durasi</p>
                <p class="bd-tile-value"><?= (int) $lowongan_detail['durasi_hari'] ?> hari</p>
                <?php if ($tanggal_selesai): ?>
                  <p class="bd-tile-sub">Selesai sekitar <?= e(format_tanggal($tanggal_selesai)) ?></p>
                <?php endif; ?>
              </article>
              <article class="bd-tile bd-tile--hl">
                <div class="bd-tile-ico"><?= bk_ikon('wallet') ?></div>
                <p class="bd-tile-label">Estimasi penghasilan</p>
                <p class="bd-tile-value"><?= e(format_rupiah($total_estimasi)) ?></p>
                <p class="bd-tile-sub">Jika bekerja penuh selama <?= (int) $lowongan_detail['durasi_hari'] ?> hari</p>
              </article>
            </section>

            <!-- DESKRIPSI -->
            <section class="bd-card">
              <h2>Deskripsi pekerjaan</h2>
              <p class="bd-desk"><?= nl2br(e($lowongan_detail['deskripsi'] ?: 'Tidak ada deskripsi tambahan.')) ?></p>
            </section>
          </div>

          <!-- KONTAK -->
          <aside class="bd-card bd-contact">
            <h2>Kontak petani</h2>
            <div class="bd-petani">
              <span class="bd-avatar"><?= e(strtoupper(mb_substr($lowongan_detail['nama_petani'], 0, 1))) ?></span>
              <div>
                <strong><?= e($lowongan_detail['nama_petani']) ?></strong>
                <small>Pemilik lowongan</small>
              </div>
            </div>

            <ul class="bd-info">
              <?php if (!empty($lowongan_detail['alamat'])): ?>
                <li><?= bk_ikon('pin') ?> <span><?= e($lowongan_detail['alamat']) ?></span></li>
              <?php endif; ?>
              <?php if (!empty($lowongan_detail['no_telepon'])): ?>
                <li><?= bk_ikon('phone') ?> <span><?= e($lowongan_detail['no_telepon']) ?></span></li>
              <?php endif; ?>
            </ul>

            <?php if ($nomor_telepon !== ''): ?>
              <div class="bd-actions">
                <?php if ($url_wa !== ''): ?>
                  <a class="bd-btn" href="<?= e($url_wa) ?>" target="_blank" rel="noopener"><?= bk_ikon('chat') ?> Chat WhatsApp</a>
                <?php endif; ?>
                <a class="bd-btn bd-btn--ghost" href="tel:<?= e($nomor_telepon) ?>"><?= bk_ikon('phone') ?> Hubungi petani</a>
              </div>
            <?php else: ?>
              <p class="bd-muted">Nomor telepon petani belum tersedia.</p>
            <?php endif; ?>

            <div class="bd-tips">
              <h3>Yang bisa ditanyakan</h3>
              <ul>
                <li><?= bk_ikon('check') ?> Tempat dan jam berkumpul</li>
                <li><?= bk_ikon('check') ?> Alat dan makan siang disediakan atau tidak</li>
                <li><?= bk_ikon('check') ?> Cara dan waktu pembayaran upah</li>
              </ul>
            </div>
          </aside>
        </div>
      <?php endif; ?>
    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>