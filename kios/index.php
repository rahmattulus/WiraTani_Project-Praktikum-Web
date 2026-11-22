<?php
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('kios');
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/saprotan_helper.php';

$user_id = user_id_login();
$database_error = '';
$ringkasan = [
    'jumlah_produk' => 0,
    'stok_menipis' => 0,
    'pesanan_menunggu' => 0,
    'selesai_bulan_ini' => 0,
];
$pesanan_terbaru = [];

try {
    $stmt = $koneksi->prepare(
        "SELECT
            (SELECT COUNT(*) FROM produk_saprotan WHERE user_id = ?) AS jumlah_produk,
            (SELECT COUNT(*) FROM produk_saprotan WHERE user_id = ? AND stok <= ?) AS stok_menipis,
            (SELECT COUNT(*) FROM pesanan_saprotan ps
             INNER JOIN produk_saprotan p ON p.id = ps.produk_saprotan_id
             WHERE p.user_id = ? AND ps.status = 'menunggu') AS pesanan_menunggu,
            (SELECT COUNT(*) FROM pesanan_saprotan ps
             INNER JOIN produk_saprotan p ON p.id = ps.produk_saprotan_id
             WHERE p.user_id = ? AND ps.status = 'selesai'
               AND ps.created_at >= ?) AS selesai_bulan_ini"
    );
    $awal_bulan = date('Y-m-01');
    $batas_stok = STOK_MENIPIS;
    $stmt->bind_param(
        'iiiiis',
        $user_id,
        $user_id,
        $batas_stok,
        $user_id,
        $user_id,
        $awal_bulan
    );
    $stmt->execute();
    $ringkasan = $stmt->get_result()->fetch_assoc() ?: $ringkasan;
    $stmt->close();

    $stmt = $koneksi->prepare(
        "SELECT ps.id, ps.created_at, ps.jumlah, ps.total_harga, ps.status,
                p.nama_produk, p.kategori, p.satuan,
                u.nama AS nama_petani
         FROM pesanan_saprotan ps
         INNER JOIN produk_saprotan p ON p.id = ps.produk_saprotan_id
         INNER JOIN users u ON u.id = ps.user_id
         WHERE p.user_id = ?
         ORDER BY ps.created_at DESC, ps.id DESC
         LIMIT 5"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $pesanan_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    error_log('Gagal memuat dashboard kios: ' . $exception->getMessage());
    $database_error = 'Dashboard kios gagal dimuat. Silakan muat ulang halaman.';
}

$judul_halaman = 'Dashboard Kios';
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
              <p class="stat-label">Jumlah produk</p>
              <p class="stat-value"><?= (int) $ringkasan['jumlah_produk'] ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Stok menipis</p>
              <p class="stat-value"><?= (int) $ringkasan['stok_menipis'] ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Pesanan menunggu</p>
              <p class="stat-value"><?= (int) $ringkasan['pesanan_menunggu'] ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Selesai bulan ini</p>
              <p class="stat-value"><?= (int) $ringkasan['selesai_bulan_ini'] ?></p>
            </article>
          </div>

          <section class="card">
            <div class="card-head">
              <h2 class="card-title">Pesanan masuk terbaru</h2>
              <a class="card-link" href="<?= BASE_URL ?>/kios/pesanan.php">Lihat semua</a>
            </div>

            <?php if (!$pesanan_terbaru): ?>
              <div class="empty-state">
                <p>Belum ada pesanan masuk.</p>
              </div>
            <?php else: ?>
              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Petani</th>
                      <th>Produk</th>
                      <th>Jumlah</th>
                      <th>Total</th>
                      <th>Status</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pesanan_terbaru as $pesanan): ?>
                      <tr>
                        <td data-label="Tanggal"><?= e(format_tanggal($pesanan['created_at'])) ?></td>
                        <td data-label="Petani"><?= e($pesanan['nama_petani']) ?></td>
                        <td data-label="Produk"><?= e($pesanan['nama_produk']) ?></td>
                        <td data-label="Jumlah">
                          <?= (int) $pesanan['jumlah'] ?>
                          <?= e($pesanan['kategori'] === 'sewa_alat' ? 'unit' : $pesanan['satuan']) ?>
                        </td>
                        <td data-label="Total"><?= e(format_rupiah($pesanan['total_harga'])) ?></td>
                        <td data-label="Status"><?= badge_pesanan($pesanan['status']) ?></td>
                        <td data-label="Aksi">
                          <a class="btn btn-ghost btn-sm"
                             href="<?= BASE_URL ?>/kios/pesanan.php">Kelola</a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </section>

          <section class="cta">
            <div class="cta-content">
              <h2>Tambah produk</h2>
              <p>Perbarui katalog saprotan agar petani dapat menemukan produk Anda.</p>
              <a class="btn" href="<?= BASE_URL ?>/kios/produk_form.php">Tambah produk</a>
            </div>
            <span class="cta-decoration" aria-hidden="true"></span>
          </section>
        <?php endif; ?>
      </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
