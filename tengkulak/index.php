<?php
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('tengkulak');
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/lelang_helper.php';

$user_id = user_id_login();
$database_error = '';
$ringkasan = [
    'lelang_aktif' => 0,
    'menunggu' => 0,
    'menang' => 0,
    'kalah' => 0,
];
$lelang_terbaru = [];
$lelang_dimenangkan = [];
$tawaran_unggul = null;

try {
    $stmt = $koneksi->prepare(
        "SELECT
            (SELECT COUNT(*) FROM hasil_panen WHERE status = 'aktif') AS lelang_aktif,
            (SELECT COUNT(*) FROM penawaran_lelang WHERE user_id = ? AND status = 'menunggu') AS menunggu,
            (SELECT COUNT(*) FROM penawaran_lelang WHERE user_id = ? AND status = 'menang') AS menang,
            (SELECT COUNT(*) FROM penawaran_lelang WHERE user_id = ? AND status = 'kalah') AS kalah"
    );
    $stmt->bind_param('iii', $user_id, $user_id, $user_id);
    $stmt->execute();
    $ringkasan = $stmt->get_result()->fetch_assoc() ?: $ringkasan;
    $stmt->close();

    $stmt = $koneksi->prepare(
        "SELECT h.id, h.komoditas, h.lokasi, h.tanggal_panen, h.estimasi_tonase, h.harga_dasar,
                u.nama AS nama_petani,
                (SELECT COUNT(*) FROM penawaran_lelang p WHERE p.hasil_panen_id = h.id) AS jumlah_tawaran,
                (SELECT MAX(p.harga_tawar) FROM penawaran_lelang p WHERE p.hasil_panen_id = h.id) AS tawaran_tertinggi,
                (SELECT MAX(p.harga_tawar) FROM penawaran_lelang p WHERE p.hasil_panen_id = h.id AND p.user_id = ?) AS tawaran_saya
         FROM hasil_panen h
         INNER JOIN users u ON u.id = h.user_id
         WHERE h.status = 'aktif'
         ORDER BY h.created_at DESC, h.id DESC
         LIMIT 5"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $lelang_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $koneksi->prepare(
        "SELECT h.id, h.komoditas, h.lokasi, h.tanggal_panen, h.estimasi_tonase,
                u.nama AS nama_petani, p.harga_tawar, p.created_at
         FROM penawaran_lelang p
         INNER JOIN hasil_panen h ON h.id = p.hasil_panen_id
         INNER JOIN users u ON u.id = h.user_id
         WHERE p.user_id = ? AND p.status = 'menang'
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 5"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $lelang_dimenangkan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $koneksi->prepare(
        "SELECT h.id, h.komoditas, h.lokasi, h.estimasi_tonase, h.harga_dasar,
                u.nama AS nama_petani,
                p.harga_tawar,
                (SELECT MAX(p2.harga_tawar) FROM penawaran_lelang p2 WHERE p2.hasil_panen_id = h.id) AS tawaran_tertinggi
         FROM penawaran_lelang p
         INNER JOIN hasil_panen h ON h.id = p.hasil_panen_id
         INNER JOIN users u ON u.id = h.user_id
         WHERE p.user_id = ? AND p.status = 'menunggu' AND h.status = 'aktif'
           AND p.harga_tawar = (SELECT MAX(p2.harga_tawar) FROM penawaran_lelang p2 WHERE p2.hasil_panen_id = h.id)
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 1"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $tawaran_unggul = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    error_log('Gagal memuat dashboard tengkulak: ' . $exception->getMessage());
    $database_error = 'Dashboard tengkulak gagal dimuat. Silakan muat ulang halaman.';
}

$judul_halaman = 'Dashboard Tengkulak';
$menu_aktif    = 'dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
      <div class="dash">
        <?php if ($database_error !== ''): ?>
          <div class="alert alert-error" role="alert"><?= e($database_error) ?></div>
        <?php else: ?>
          <div class="stat-grid">
            <article class="stat">
              <p class="stat-label">Lelang aktif</p>
              <p class="stat-value"><?= (int) $ringkasan['lelang_aktif'] ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Penawaran menunggu</p>
              <p class="stat-value"><?= (int) $ringkasan['menunggu'] ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Menang</p>
              <p class="stat-value"><?= (int) $ringkasan['menang'] ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Kalah</p>
              <p class="stat-value"><?= (int) $ringkasan['kalah'] ?></p>
            </article>
          </div>

          <section class="card">
            <div class="card-head">
              <h2 class="card-title">Lelang yang dimenangkan</h2>
              <span class="badge badge-success"><?= (int) $ringkasan['menang'] ?> lelang</span>
            </div>

            <?php if (!$lelang_dimenangkan): ?>
              <div class="empty-state">
                <p>Belum ada lelang yang berhasil Anda menangkan.</p>
              </div>
            <?php else: ?>
              <ul class="won-auction-list">
                <?php foreach ($lelang_dimenangkan as $lelang_menang): ?>
                  <li class="won-auction-item">
                    <div>
                      <strong><?= e($lelang_menang['komoditas']) ?></strong>
                      <p><?= e($lelang_menang['nama_petani']) ?> · <?= e($lelang_menang['lokasi']) ?></p>
                      <small>
                        <?= e(number_format((float) $lelang_menang['estimasi_tonase'], 0, ',', '.')) ?> kg
                        · Panen <?= e(format_tanggal($lelang_menang['tanggal_panen'])) ?>
                      </small>
                    </div>
                    <div class="won-auction-price">
                      <span class="stat-label">Tawaran menang</span>
                      <strong><?= e(format_rupiah($lelang_menang['harga_tawar'])) ?>/kg</strong>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </section>

          <section class="card">
            <div class="card-head">
              <h2 class="card-title">Lelang aktif terbaru</h2>
              <a class="card-link" href="<?= BASE_URL ?>/tengkulak/pasar/index.php">Lihat semua</a>
            </div>

            <?php if (!$lelang_terbaru): ?>
              <div class="empty-state">
                <p>Belum ada lelang aktif saat ini.</p>
              </div>
            <?php else: ?>
              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Komoditas / Petani</th>
                      <th>Estimasi</th>
                      <th>Harga dasar</th>
                      <th>Tawaran tertinggi</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lelang_terbaru as $lelang): ?>
                      <tr>
                        <td data-label="Komoditas / Petani">
                          <strong><?= e($lelang['komoditas']) ?></strong><br>
                          <small><?= e($lelang['nama_petani']) ?> · <?= e($lelang['lokasi']) ?></small>
                        </td>
                        <td data-label="Estimasi"><?= e(number_format((float) $lelang['estimasi_tonase'], 0, ',', '.')) ?> kg</td>
                        <td data-label="Harga dasar"><?= e(format_rupiah($lelang['harga_dasar'])) ?>/kg</td>
                        <td data-label="Tawaran tertinggi">
                          <?= $lelang['tawaran_tertinggi'] !== null ? e(format_rupiah($lelang['tawaran_tertinggi'])) . '/kg' : '<span class="text-muted">Belum ada</span>' ?>
                        </td>
                        <td data-label="Aksi">
                          <a class="btn btn-primary btn-sm"
                             href="<?= BASE_URL ?>/tengkulak/pasar/detail.php?id=<?= (int) $lelang['id'] ?>">Lihat</a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </section>

          <?php if ($tawaran_unggul): ?>
            <section class="card-dark">
              <p class="stat-label" style="color: var(--nav-text);">Tawaran saya yang sedang unggul</p>
              <h2><?= e($tawaran_unggul['komoditas']) ?></h2>
              <p><?= e($tawaran_unggul['nama_petani']) ?> · <?= e($tawaran_unggul['lokasi']) ?></p>
              <p><strong><?= e(format_rupiah($tawaran_unggul['harga_tawar'])) ?>/kg</strong></p>
              <a class="btn btn-primary btn-sm"
                 href="<?= BASE_URL ?>/tengkulak/pasar/detail.php?id=<?= (int) $tawaran_unggul['id'] ?>">Lihat tawaran</a>
            </section>
          <?php else: ?>
            <section class="card-dark">
              <p class="stat-label" style="color: var(--nav-text);">Tawaran saya yang sedang unggul</p>
              <h2>Belum ada tawaran unggul</h2>
              <p>Ikuti lelang aktif dan ajukan penawaran terbaik Anda.</p>
            </section>
          <?php endif; ?>

          <section class="cta">
            <div class="cta-content">
              <h2>Jelajahi pasar lelang</h2>
              <p>Temukan hasil panen dan ajukan tawaran baru.</p>
              <a class="btn" href="<?= BASE_URL ?>/tengkulak/pasar/index.php">Jelajahi pasar lelang</a>
            </div>
            <span class="cta-decoration" aria-hidden="true"></span>
          </section>
        <?php endif; ?>
      </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
