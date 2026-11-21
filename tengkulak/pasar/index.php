<?php
/* Tengkulak: pasar lelang dengan pencarian (S11, S20). */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('tengkulak');
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/lelang_helper.php';

$q = input_teks($_GET, 'q');
$user_id = user_id_login();
$database_error = '';
try {
    $ringkasan = ambil_ringkasan_pasar_lelang($koneksi, $user_id);
    $lelang_tampil = ambil_daftar_pasar_lelang($koneksi, $q);
} catch (mysqli_sql_exception $exception) {
    error_log('Gagal memuat pasar lelang: ' . $exception->getMessage());
    $database_error = 'Pasar lelang gagal dimuat. Silakan muat ulang halaman.';
    $ringkasan = null;
    $lelang_tampil = null;
}

$judul_halaman = 'Pasar Lelang';
$menu_aktif = 'pasar';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <?php if ($database_error !== ''): ?>
          <div class="alert alert-error" role="alert"><?= e($database_error) ?></div>
        <?php else: ?>
        <div class="dash-r1">
          <article class="stat">
            <p class="stat-label">Lelang aktif</p>
            <p class="stat-value"><?= (int) $ringkasan['lelang_aktif'] ?></p>
          </article>
          <article class="stat">
            <p class="stat-label">Tawaran saya</p>
            <p class="stat-value"><?= (int) $ringkasan['tawaran_saya'] ?></p>
          </article>
          <article class="stat">
            <p class="stat-label">Komoditas tersedia</p>
            <p class="stat-value"><?= (int) $ringkasan['komoditas_tersedia'] ?></p>
          </article>
        </div>

        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Jelajahi hasil panen</h2>
          </div>

          <form class="filter-bar" method="get" action="">
            <div class="form-group">
              <label for="q">Cari komoditas, lokasi, atau petani</label>
              <input class="input" type="search" id="q" name="q" value="<?= e($q) ?>"
                     placeholder="Padi, Klaten, Budi Santoso">
            </div>
            <button class="btn btn-ghost" type="submit">Cari lelang</button>
          </form>

          <?php if (!$lelang_tampil): ?>
            <div class="empty-state">
              <p><?= $q !== '' ? 'Tidak ada lelang yang cocok dengan pencarian Anda.' : 'Belum ada lelang aktif saat ini.' ?></p>
              <?php if ($q !== ''): ?>
                <a class="btn btn-ghost" href="<?= BASE_URL ?>/tengkulak/pasar/index.php">Hapus pencarian</a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Komoditas / Petani</th>
                    <th>Tanggal panen</th>
                    <th>Estimasi</th>
                    <th>Harga dasar</th>
                    <th>Tawaran tertinggi</th>
                    <th>Jumlah tawaran</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lelang_tampil as $hasil): ?>
                    <tr>
                      <td data-label="Komoditas / Petani">
                        <strong><?= e($hasil['komoditas']) ?></strong><br>
                        <small><?= e($hasil['nama_petani']) ?> · <?= e($hasil['lokasi']) ?></small>
                      </td>
                      <td data-label="Tanggal panen"><?= e(format_tanggal($hasil['tanggal_panen'])) ?></td>
                      <td data-label="Estimasi"><?= e(number_format((float) $hasil['estimasi_tonase'], 0, ',', '.')) ?> kg</td>
                      <td data-label="Harga dasar"><?= e(format_rupiah($hasil['harga_dasar'])) ?>/kg</td>
                      <td data-label="Tawaran tertinggi">
                        <?= $hasil['tawaran_tertinggi'] !== null ? e(format_rupiah($hasil['tawaran_tertinggi'])) . '/kg' : '<span class="text-muted">Belum ada</span>' ?>
                      </td>
                      <td data-label="Jumlah tawaran"><?= (int) $hasil['jumlah_tawaran'] ?></td>
                      <td data-label="Aksi">
                        <a class="btn btn-primary btn-sm"
                           href="<?= BASE_URL ?>/tengkulak/pasar/detail.php?id=<?= (int) $hasil['id'] ?>">Lihat detail</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </section>
        <?php endif; ?>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
