<?php
/* Tengkulak: detail hasil panen dan ringkasan penawaran (S11, S20). */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('tengkulak');
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/lelang_helper.php';

$id = (int) input_teks($_GET, 'id');
$user_id = user_id_login();
$database_error = '';
try {
    $hasil = ambil_detail_pasar_lelang($koneksi, $id);
    if (!$hasil) {
        set_flash('error', 'Lelang aktif tidak ditemukan.');
        redirect('index.php');
    }
    $tawaran_saya = ambil_penawaran_tengkulak($koneksi, $id, $user_id);
} catch (mysqli_sql_exception $exception) {
    error_log('Gagal memuat detail pasar lelang: ' . $exception->getMessage());
    $database_error = 'Detail lelang gagal dimuat. Silakan muat ulang halaman.';
    $hasil = null;
    $tawaran_saya = [];
}

$judul_halaman = 'Detail Lelang';
$menu_aktif = 'pasar';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <?php if ($database_error !== ''): ?>
          <div class="alert alert-error" role="alert"><?= e($database_error) ?></div>
        <?php else: ?>
        <section class="card">
          <div class="card-head">
            <h2 class="card-title"><?= e($hasil['komoditas']) ?></h2>
            <span class="badge <?= e(kelas_status_lelang($hasil['status'])) ?>">
              <?= e(label_status_lelang($hasil['status'])) ?>
            </span>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <span class="text-muted">Petani</span>
              <strong><?= e($hasil['nama_petani']) ?></strong>
            </div>
            <div class="form-group">
              <span class="text-muted">Lokasi panen</span>
              <strong><?= e($hasil['lokasi']) ?></strong>
            </div>
            <div class="form-group">
              <span class="text-muted">Tanggal panen</span>
              <strong><?= e(format_tanggal($hasil['tanggal_panen'])) ?></strong>
            </div>
            <div class="form-group">
              <span class="text-muted">Estimasi hasil</span>
              <strong><?= e(number_format((float) $hasil['estimasi_tonase'], 0, ',', '.')) ?> kg</strong>
            </div>
            <div class="form-group">
              <span class="text-muted">Harga dasar</span>
              <strong><?= e(format_rupiah($hasil['harga_dasar'])) ?>/kg</strong>
            </div>
            <div class="form-group">
              <span class="text-muted">Jumlah tawaran</span>
              <strong><?= (int) $hasil['jumlah_tawaran'] ?></strong>
            </div>
          </div>

          <?php if ($hasil['deskripsi'] !== null && $hasil['deskripsi'] !== ''): ?>
            <p><?= nl2br(e($hasil['deskripsi'])) ?></p>
          <?php endif; ?>

          <div class="form-actions">
            <a class="btn btn-ghost" href="<?= BASE_URL ?>/tengkulak/pasar/index.php">Kembali ke pasar</a>
            <a class="btn btn-primary"
               href="<?= BASE_URL ?>/tengkulak/pasar/penawaran.php?hasil_panen_id=<?= (int) $hasil['id'] ?>">
              <?= $tawaran_saya ? 'Kirim tawaran lebih tinggi' : 'Buat penawaran' ?>
            </a>
          </div>
        </section>

        <section class="card-dark">
          <h2 class="card-title">Tawaran tertinggi saat ini</h2>
          <?php if ($hasil['tawaran_tertinggi'] !== null): ?>
            <p class="stat-value"><?= e(format_rupiah($hasil['tawaran_tertinggi'])) ?><span class="stat-unit">/kg</span></p>
          <?php else: ?>
            <p>Belum ada tawaran. Jadilah yang pertama mengajukan harga.</p>
          <?php endif; ?>
        </section>

        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Tawaran saya untuk lelang ini</h2>
            <span class="text-muted"><?= count($tawaran_saya) ?> tawaran</span>
          </div>

          <?php if (!$tawaran_saya): ?>
            <div class="empty-state">
              <p>Anda belum mengirim tawaran untuk hasil panen ini.</p>
              <a class="btn btn-primary"
                 href="<?= BASE_URL ?>/tengkulak/pasar/penawaran.php?hasil_panen_id=<?= (int) $hasil['id'] ?>">Buat penawaran</a>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Harga tawar</th>
                    <th>Waktu</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tawaran_saya as $penawaran): ?>
                    <tr>
                      <td data-label="Harga tawar"><strong><?= e(format_rupiah($penawaran['harga_tawar'])) ?>/kg</strong></td>
                      <td data-label="Waktu"><?= e(format_tanggal(substr($penawaran['created_at'], 0, 10))) ?></td>
                      <td data-label="Status">
                        <span class="badge <?= e(kelas_status_penawaran($penawaran['status'])) ?>">
                          <?= e(label_status_penawaran($penawaran['status'])) ?>
                        </span>
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
