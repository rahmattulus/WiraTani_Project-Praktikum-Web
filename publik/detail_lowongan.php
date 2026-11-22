<?php
/* Detail lowongan publik; hanya menampilkan lowongan yang masih buka. */
require_once __DIR__ . '/../includes/helper.php';
require_once __DIR__ . '/../config/koneksi.php';

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
$nomor_telepon = $lowongan_detail
    ? preg_replace('/[^0-9+]/', '', $lowongan_detail['no_telepon'])
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
          <p><?= e($lowongan_detail['deskripsi'] ?: 'Tidak ada deskripsi tambahan.') ?></p>
        </section>

        <section class="card">
          <h2 class="card-title">Kontak petani</h2>
          <p>
            <strong><?= e($lowongan_detail['nama_petani']) ?></strong><br>
            <?= e($lowongan_detail['alamat']) ?><br>
            <?= e($lowongan_detail['no_telepon']) ?>
          </p>
          <div class="form-actions">
            <a class="btn btn-primary" href="tel:<?= e($nomor_telepon) ?>">Hubungi petani</a>
            <a class="btn btn-ghost" href="<?= BASE_URL ?>/publik/bursa.php">Kembali ke bursa kerja</a>
          </div>
        </section>
      <?php endif; ?>
    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
