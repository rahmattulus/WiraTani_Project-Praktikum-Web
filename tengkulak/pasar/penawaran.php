<?php
/* Tengkulak: form penawaran lelang (S11, S20). */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('tengkulak');
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/lelang_helper.php';

$hasil_panen_id = (int) input_teks($_GET, 'hasil_panen_id');
$user_id = user_id_login();
$database_error = '';
$error = [];
$form_data = [
    'harga_tawar' => '',
    'metode_pembayaran' => '',
    'armada_angkut' => '',
    'catatan' => '',
];
try {
    $hasil = ambil_detail_pasar_lelang($koneksi, $hasil_panen_id);
    if (!$hasil) {
        set_flash('error', 'Lelang aktif tidak ditemukan.');
        redirect('index.php');
    }
    $tawaran_saya = ambil_penawaran_tengkulak($koneksi, $hasil_panen_id, $user_id);
} catch (mysqli_sql_exception $exception) {
    error_log('Gagal memuat form penawaran: ' . $exception->getMessage());
    $database_error = 'Data lelang gagal dimuat. Silakan muat ulang halaman.';
    $hasil = null;
    $tawaran_saya = [];
}

$harga_minimum = $hasil ? (float) $hasil['harga_dasar'] : 0;
foreach ($tawaran_saya as $tawaran) {
    $harga_minimum = max($harga_minimum, (float) $tawaran['harga_tawar'] + 0.01);
}
$harga_saran = max(
    $harga_minimum,
    $hasil && $hasil['tawaran_tertinggi'] !== null
        ? (float) $hasil['tawaran_tertinggi'] + 100
        : $harga_minimum
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $database_error === '') {
    wajib_csrf('penawaran.php?hasil_panen_id=' . $hasil_panen_id);
    $form_data = [
        'harga_tawar' => input_teks($_POST, 'harga_tawar'),
        'metode_pembayaran' => input_teks($_POST, 'metode_pembayaran'),
        'armada_angkut' => input_teks($_POST, 'armada_angkut'),
        'catatan' => input_teks($_POST, 'catatan'),
    ];
    $error = validasi_input_penawaran($form_data);

    if (!$error) {
        $status_penawaran = buat_penawaran_lelang($koneksi, $hasil_panen_id, $user_id, $form_data);
        if ($status_penawaran === 'created') {
            set_flash('success', 'Penawaran berhasil dikirim.');
            redirect('detail.php?id=' . $hasil_panen_id);
        }

        $pesan_penawaran = [
            'below_base' => 'Harga tawar tidak boleh lebih rendah dari harga dasar.',
            'not_higher' => 'Harga tawar harus lebih tinggi dari tawaran Anda sebelumnya.',
            'not_active' => 'Lelang sudah tidak aktif dan tidak menerima penawaran.',
            'invalid_input' => 'Data penawaran tidak valid. Periksa kembali isian Anda.',
            'error' => 'Penawaran gagal disimpan. Silakan coba lagi.',
        ][$status_penawaran] ?? 'Penawaran gagal disimpan. Silakan coba lagi.';
        if (in_array($status_penawaran, ['below_base', 'not_higher'], true)) {
            $error['harga_tawar'] = $pesan_penawaran;
        } else {
            $error['form'] = $pesan_penawaran;
        }
    }
}

$judul_halaman = 'Buat Penawaran';
$menu_aktif = 'pasar';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <?php if ($database_error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= e($database_error) ?></div>
          <?php else: ?>
            <div class="card-head">
              <h2 class="card-title"><?= e($hasil['komoditas']) ?></h2>
              <span class="badge <?= e(kelas_status_lelang($hasil['status'])) ?>">
                <?= e(label_status_lelang($hasil['status'])) ?>
              </span>
            </div>
            <p>
              <?= e($hasil['nama_petani']) ?> · <?= e($hasil['lokasi']) ?><br>
              Panen <?= e(format_tanggal($hasil['tanggal_panen'])) ?> ·
              <?= e(number_format((float) $hasil['estimasi_tonase'], 0, ',', '.')) ?> kg
            </p>
            <div class="form-grid">
              <div class="form-group">
                <span class="text-muted">Harga dasar</span>
                <strong><?= e(format_rupiah($hasil['harga_dasar'])) ?>/kg</strong>
              </div>
              <div class="form-group">
                <span class="text-muted">Tawaran tertinggi saat ini</span>
                <strong><?= $hasil['tawaran_tertinggi'] !== null ? e(format_rupiah($hasil['tawaran_tertinggi'])) . '/kg' : 'Belum ada' ?></strong>
              </div>
              <?php if ($tawaran_saya): ?>
                <div class="form-group">
                  <span class="text-muted">Tawaran terakhir saya</span>
                  <strong><?= e(format_rupiah($tawaran_saya[0]['harga_tawar'])) ?>/kg</strong>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </section>

        <?php if ($database_error === ''): ?>
        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Detail penawaran</h2>
          </div>
          <p class="text-muted">
            Tawaran harus minimal sebesar harga dasar dan lebih tinggi dari tawaran Anda sebelumnya.
          </p>

          <?php if (isset($error['form'])): ?>
            <div class="alert alert-error" role="alert"><?= e($error['form']) ?></div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div hidden data-app-notice data-notice-kind="error"
                 data-notice-title="Penawaran belum dapat dikirim"
                 data-notice-message="<?= e(implode("\n", array_values($error))) ?>"></div>
          <?php endif; ?>

          <form method="post" action="" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="hasil_panen_id" value="<?= (int) $hasil['id'] ?>">

            <div class="form-grid">
              <div class="form-group">
                <label for="harga_tawar">Harga tawar (Rp/kg)</label>
                <input class="input" type="number" id="harga_tawar" name="harga_tawar"
                       min="<?= e(number_format($harga_minimum, 2, '.', '')) ?>" max="9999999999999.99"
                       <?= isset($error['harga_tawar']) ? 'aria-invalid="true" aria-describedby="error-harga_tawar"' : '' ?>
                       step="0.01" value="<?= e($form_data['harga_tawar'] !== '' ? $form_data['harga_tawar'] : number_format($harga_saran, 2, '.', '')) ?>" required>
                <?php tampil_error($error, 'harga_tawar'); ?>
              </div>

              <div class="form-group">
                <label for="metode_pembayaran">Metode pembayaran</label>
                <input class="input" type="text" id="metode_pembayaran" name="metode_pembayaran"
                       <?= isset($error['metode_pembayaran']) ? 'aria-invalid="true" aria-describedby="error-metode_pembayaran"' : '' ?>
                       maxlength="50" value="<?= e($form_data['metode_pembayaran']) ?>"
                       placeholder="Tunai saat pengambilan" required>
                <?php tampil_error($error, 'metode_pembayaran'); ?>
              </div>

              <div class="form-group">
                <label for="armada_angkut">Armada angkut</label>
                <input class="input" type="text" id="armada_angkut" name="armada_angkut"
                       <?= isset($error['armada_angkut']) ? 'aria-invalid="true" aria-describedby="error-armada_angkut"' : '' ?>
                       maxlength="100" value="<?= e($form_data['armada_angkut']) ?>"
                       placeholder="Truk engkel" required>
                <?php tampil_error($error, 'armada_angkut'); ?>
              </div>
            </div>

            <div class="form-group">
              <label for="catatan">Catatan untuk petani (opsional)</label>
              <textarea class="textarea" id="catatan" name="catatan" maxlength="2000"
                        <?= isset($error['catatan']) ? 'aria-invalid="true" aria-describedby="error-catatan"' : '' ?>
                        placeholder="Jadwal pengambilan atau informasi tambahan."><?= e($form_data['catatan']) ?></textarea>
              <?php tampil_error($error, 'catatan'); ?>
            </div>

            <div class="form-actions">
              <button class="btn btn-primary" type="submit">Kirim tawaran</button>
              <a class="btn btn-ghost"
                 href="<?= BASE_URL ?>/tengkulak/pasar/detail.php?id=<?= (int) $hasil['id'] ?>">Batal</a>
            </div>
          </form>
        </section>
        <?php endif; ?>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
