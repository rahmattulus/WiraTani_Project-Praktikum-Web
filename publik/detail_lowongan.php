<?php
/* Detail lowongan publik (S15); pengunjung tidak perlu login. */
require_once __DIR__ . '/../includes/helper.php';
require_once __DIR__ . '/../petani/dummy_musim.php';

$id_input = $_GET['id'] ?? '';
if (!is_string($id_input)) {
    $id_input = '';
} else {
    $id_input = trim($id_input);
}
$lowongan_detail = null;
if (ctype_digit($id_input) && (int) $id_input > 0) {
    foreach ($demo_lowongan as $lowongan) {
        if ($lowongan['id'] === (int) $id_input && $lowongan['status'] === 'buka') {
            $lowongan_detail = $lowongan;
            break;
        }
    }
}

$label_pekerjaan = [
    'tanam' => 'Tanam',
    'panen' => 'Panen',
    'lainnya' => 'Lainnya',
];
$kontak_petani = $lowongan_detail
    ? ($demo_petani[$lowongan_detail['user_id']] ?? null)
    : null;
$nomor_telepon = $kontak_petani
    ? preg_replace('/[^0-9+]/', '', $kontak_petani['no_telepon'])
    : '';

$layout = 'publik';
$judul_halaman = $lowongan_detail ? $lowongan_detail['judul'] : 'Lowongan Tidak Tersedia';
include __DIR__ . '/../includes/header.php';
?>
    <div class="dash">
      <?php if (!$lowongan_detail): ?>
        <section class="card empty-state">
          <h1>Lowongan tidak tersedia</h1>
          <p>Lowongan ini tidak ditemukan atau sudah ditutup.</p>
          <a class="btn btn-primary" href="<?= BASE_URL ?>/publik/bursa.php">Kembali ke bursa kerja</a>
        </section>
      <?php else: ?>
        <section class="card">
          <div class="card-head">
            <span class="badge badge-success">Buka</span>
            <span class="badge badge-info"><?= e($label_pekerjaan[$lowongan_detail['jenis_pekerjaan']]) ?></span>
          </div>
          <h1><?= e($lowongan_detail['judul']) ?></h1>
          <p class="text-muted"><?= e($lowongan_detail['lokasi']) ?></p>

          <div class="stat-grid">
            <article class="stat">
              <p class="stat-label">Upah harian</p>
              <p class="stat-value"><?= e(format_rupiah($lowongan_detail['upah_harian'])) ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Kebutuhan buruh</p>
              <p class="stat-value"><?= (int) $lowongan_detail['jumlah_buruh'] ?> orang</p>
            </article>
            <article class="stat">
              <p class="stat-label">Tanggal mulai</p>
              <p class="stat-value"><?= e(format_tanggal($lowongan_detail['tanggal_mulai'])) ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Durasi</p>
              <p class="stat-value"><?= (int) $lowongan_detail['durasi_hari'] ?> hari</p>
            </article>
          </div>
        </section>

        <section class="card">
          <h2 class="card-title">Deskripsi pekerjaan</h2>
          <p><?= e($lowongan_detail['deskripsi']) ?></p>
        </section>

        <section class="card">
          <h2 class="card-title">Kontak petani</h2>
          <?php if (!$kontak_petani): ?>
            <p>Informasi kontak belum tersedia.</p>
          <?php else: ?>
            <p>
              <strong><?= e($kontak_petani['nama']) ?></strong><br>
              <?= e($kontak_petani['alamat']) ?><br>
              <?= e($kontak_petani['no_telepon']) ?>
            </p>
            <a class="btn btn-primary" href="tel:<?= e($nomor_telepon) ?>">Hubungi petani</a>
          <?php endif; ?>
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/publik/bursa.php">Kembali ke bursa kerja</a>
        </section>
      <?php endif; ?>
    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
