<?php
/* Bursa kerja publik: hanya lowongan berstatus buka. */
require_once __DIR__ . '/../includes/helper.php';
require_once __DIR__ . '/../config/koneksi.php';

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

$label_pekerjaan = [
    'tanam' => 'Tanam',
    'panen' => 'Panen',
    'lainnya' => 'Lainnya',
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
     ORDER BY l.tanggal_mulai ASC, l.created_at DESC"
);
$stmt->bind_param('ssss', $jenis_pekerjaan, $jenis_pekerjaan, $lokasi, $lokasi_cari);
$stmt->execute();
$lowongan_tampil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$layout = 'publik';
$judul_halaman = 'Bursa Kerja Buruh Tani';
include __DIR__ . '/../includes/header.php';
?>
    <div class="dash">
      <section class="card">
        <h1><?= e($judul_halaman) ?></h1>
        <p class="text-muted">Temukan pekerjaan tani harian dengan lokasi dan upah yang jelas.</p>

        <form class="filter-bar" method="get" action="">
          <div class="form-group">
            <label for="jenis_pekerjaan">Jenis pekerjaan</label>
            <select class="select" id="jenis_pekerjaan" name="jenis_pekerjaan">
              <option value="">Semua jenis</option>
              <?php foreach ($label_pekerjaan as $nilai => $label): ?>
                <option value="<?= e($nilai) ?>" <?= $jenis_pekerjaan === $nilai ? 'selected' : '' ?>>
                  <?= e($label) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="lokasi">Lokasi</label>
            <input class="input" type="search" id="lokasi" name="lokasi" value="<?= e($lokasi) ?>"
                   placeholder="Cari desa atau kecamatan">
          </div>
          <button class="btn btn-primary" type="submit">Cari lowongan</button>
          <?php if ($jenis_pekerjaan !== '' || $lokasi !== ''): ?>
            <a class="btn btn-ghost" href="<?= BASE_URL ?>/publik/bursa.php">Hapus filter</a>
          <?php endif; ?>
        </form>
      </section>

      <?php if (!$lowongan_tampil): ?>
        <section class="card empty-state">
          <p>Belum ada lowongan terbuka yang cocok dengan pencarian ini.</p>
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/publik/bursa.php">Tampilkan semua lowongan</a>
        </section>
      <?php else: ?>
        <section class="feature-grid" aria-label="Daftar lowongan terbuka">
          <?php foreach ($lowongan_tampil as $lowongan): ?>
            <article class="card">
              <div class="card-head">
                <span class="badge badge-success">Buka</span>
                <span class="badge badge-info"><?= e($label_pekerjaan[$lowongan['jenis_pekerjaan']]) ?></span>
              </div>
              <h2 class="card-title"><?= e($lowongan['judul']) ?></h2>
              <p class="text-muted"><?= e($lowongan['lokasi']) ?></p>
              <p>
                <strong><?= e(format_rupiah($lowongan['upah_harian'])) ?></strong> / hari<br>
                <?= (int) $lowongan['jumlah_buruh'] ?> orang ·
                <?= (int) $lowongan['durasi_hari'] ?> hari<br>
                Mulai <?= e(format_tanggal($lowongan['tanggal_mulai'])) ?>
              </p>
              <p><?= e($lowongan['deskripsi']) ?></p>
              <p class="text-muted">Dibuka oleh <?= e($lowongan['nama_petani']) ?></p>
              <a class="btn btn-primary" href="<?= BASE_URL ?>/publik/detail_lowongan.php?id=<?= (int) $lowongan['id'] ?>">
                Lihat detail dan kontak
              </a>
            </article>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>
    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
