<?php
/* Petani: daftar lelang panen (S10, S17). */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/lelang_helper.php';

$user_id = user_id_login();
$q = input_teks($_GET, 'q');
$status = input_teks($_GET, 'status');
if (!in_array($status, ['', 'aktif', 'selesai', 'dibatalkan'], true)) {
    $status = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('index.php');
    $aksi = input_teks($_POST, 'aksi');
    $id_input = input_teks($_POST, 'id');
    if ($aksi !== 'hapus' || !ctype_digit($id_input) || (int) $id_input < 1) {
        set_flash('error', 'Permintaan hapus lelang tidak valid.');
        redirect('index.php');
    }

    $hasil_hapus = hapus_hasil_panen($koneksi, (int) $id_input, $user_id);
    $pesan_hapus = [
        'deleted' => ['success', 'Lelang berhasil dihapus.'],
        'not_found' => ['error', 'Lelang tidak ditemukan atau bukan milik Anda.'],
        'has_offers' => ['error', 'Lelang tidak dapat dihapus karena sudah menerima penawaran.'],
        'error' => ['error', 'Lelang gagal dihapus. Silakan coba lagi.'],
    ][$hasil_hapus] ?? ['error', 'Lelang gagal dihapus. Silakan coba lagi.'];
    set_flash($pesan_hapus[0], $pesan_hapus[1]);
    redirect('index.php');
}

$database_error = '';
try {
    $ringkasan = ambil_ringkasan_hasil_panen_petani($koneksi, $user_id);
    $lelang_tampil = ambil_daftar_hasil_panen_petani($koneksi, $user_id, $q, $status);
} catch (mysqli_sql_exception $exception) {
    error_log('Gagal memuat daftar lelang petani: ' . $exception->getMessage());
    $database_error = 'Daftar lelang gagal dimuat. Silakan muat ulang halaman.';
    $ringkasan = null;
    $lelang_tampil = null;
}

$judul_halaman = 'Lelang Panen';
$menu_aktif = 'lelang';
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
              <p class="stat-value"><?= (int) $ringkasan['aktif'] ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Tawaran menunggu</p>
              <p class="stat-value"><?= (int) $ringkasan['tawaran_menunggu'] ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Lelang selesai</p>
              <p class="stat-value"><?= (int) $ringkasan['selesai'] ?></p>
            </article>
          </div>

          <section class="card">
            <div class="card-head">
              <h2 class="card-title">Daftar hasil panen</h2>
              <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/petani/lelang/form.php">Tambah lelang</a>
            </div>

            <form class="filter-bar" method="get" action="">
              <div class="form-group">
                <label for="q">Cari komoditas atau lokasi</label>
                <input class="input" type="search" id="q" name="q" value="<?= e($q) ?>"
                       placeholder="Padi, Klaten">
              </div>
              <div class="form-group">
                <label for="status">Status</label>
                <select class="select" id="status" name="status">
                  <option value="">Semua status</option>
                  <?php foreach (['aktif' => 'Aktif', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $nilai => $label): ?>
                    <option value="<?= e($nilai) ?>"<?= $status === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button class="btn btn-ghost" type="submit">Terapkan</button>
            </form>

            <?php if (!$lelang_tampil): ?>
              <div class="empty-state">
                <p>Belum ada lelang yang sesuai dengan pencarian.</p>
                <?php if ($q !== '' || $status !== ''): ?>
                  <a class="btn btn-ghost" href="<?= BASE_URL ?>/petani/lelang/index.php">Hapus filter</a>
                <?php else: ?>
                  <a class="btn btn-primary" href="<?= BASE_URL ?>/petani/lelang/form.php">Buat lelang pertama</a>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Komoditas</th>
                      <th>Panen</th>
                      <th>Estimasi</th>
                      <th>Harga dasar</th>
                      <th>Tawaran</th>
                      <th>Status</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lelang_tampil as $hasil): ?>
                      <?php
                      $jumlah_tawaran_hasil = (int) $hasil['jumlah_tawaran'];
                      $belum_ada_tawaran = $jumlah_tawaran_hasil === 0;
                      $boleh_ubah = $hasil['status'] === 'aktif' && $belum_ada_tawaran;
                      ?>
                      <tr>
                        <td data-label="Komoditas">
                          <strong><?= e($hasil['komoditas']) ?></strong><br>
                          <small><?= e($hasil['lokasi']) ?></small>
                        </td>
                        <td data-label="Panen"><?= e(format_tanggal($hasil['tanggal_panen'])) ?></td>
                        <td data-label="Estimasi"><?= e(number_format((float) $hasil['estimasi_tonase'], 0, ',', '.')) ?> kg</td>
                        <td data-label="Harga dasar"><?= e(format_rupiah($hasil['harga_dasar'])) ?>/kg</td>
                        <td data-label="Tawaran"><?= $jumlah_tawaran_hasil ?></td>
                        <td data-label="Status">
                          <span class="badge <?= e(kelas_status_lelang($hasil['status'])) ?>">
                            <?= e(label_status_lelang($hasil['status'])) ?>
                          </span>
                        </td>
                        <td data-label="Aksi">
                          <div class="table-actions">
                            <a class="btn btn-ghost btn-sm"
                               href="<?= BASE_URL ?>/petani/lelang/detail.php?id=<?= (int) $hasil['id'] ?>">Detail</a>
                            <?php if ($boleh_ubah): ?>
                              <a class="btn btn-ghost btn-sm"
                                 href="<?= BASE_URL ?>/petani/lelang/form.php?id=<?= (int) $hasil['id'] ?>">Ubah</a>
                            <?php endif; ?>
                            <?php if ($belum_ada_tawaran): ?>
                              <form method="post" action=""
                                    data-confirm="Hapus lelang <?= e($hasil['komoditas']) ?>? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= (int) $hasil['id'] ?>">
                                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                              </form>
                            <?php endif; ?>
                          </div>
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
