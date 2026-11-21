<?php
/* Petani: tambah / ubah lelang (S10, S17). */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/lelang_helper.php';

$user_id = user_id_login();
$id = (int) input_teks($_GET, 'id');
$hasil = [
    'komoditas' => '',
    'estimasi_tonase' => '',
    'lokasi' => '',
    'tanggal_panen' => '',
    'harga_dasar' => '',
    'deskripsi' => '',
];
$error = [];
$database_error = '';

if ($id > 0) {
    try {
        $ditemukan = ambil_hasil_panen_petani($koneksi, $id, $user_id);
    } catch (mysqli_sql_exception $exception) {
        error_log('Gagal memuat lelang untuk diubah: ' . $exception->getMessage());
        $database_error = 'Data lelang gagal dimuat. Silakan muat ulang halaman.';
        $ditemukan = null;
    }

    if ($database_error === '' && !$ditemukan) {
        set_flash('error', 'Lelang tidak ditemukan atau bukan milik Anda.');
        redirect('index.php');
    }
    if ($database_error === '' && (int) $ditemukan['jumlah_tawaran'] > 0) {
        set_flash('error', 'Lelang tidak dapat diubah karena sudah menerima penawaran.');
        redirect('detail.php?id=' . $id);
    }
    if ($database_error === '' && $ditemukan['status'] !== 'aktif') {
        set_flash('error', 'Hanya lelang aktif yang dapat diubah.');
        redirect('detail.php?id=' . $id);
    }
    if ($database_error === '') {
        $hasil = array_merge($hasil, $ditemukan);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $database_error === '') {
    wajib_csrf('form.php' . ($id > 0 ? '?id=' . $id : ''));
    $input = [
        'komoditas' => input_teks($_POST, 'komoditas'),
        'estimasi_tonase' => input_teks($_POST, 'estimasi_tonase'),
        'lokasi' => input_teks($_POST, 'lokasi'),
        'tanggal_panen' => input_teks($_POST, 'tanggal_panen'),
        'harga_dasar' => input_teks($_POST, 'harga_dasar'),
        'deskripsi' => input_teks($_POST, 'deskripsi'),
    ];
    $hasil = array_merge($hasil, $input);
    $error = validasi_input_hasil_panen($input);
    if (!$error && $id === 0 && $input['tanggal_panen'] < date('Y-m-d')) {
        $error['tanggal_panen'] = 'Tanggal panen tidak boleh sebelum hari ini.';
    }

    if (!$error && $id > 0) {
        $hasil_ubah = ubah_hasil_panen($koneksi, $id, $user_id, $input);
        $pesan_ubah = [
            'updated' => ['success', 'Data lelang berhasil diperbarui.'],
            'not_found' => ['error', 'Lelang tidak ditemukan atau bukan milik Anda.'],
            'not_active' => ['error', 'Hanya lelang aktif yang dapat diubah.'],
            'has_offers' => ['error', 'Lelang tidak dapat diubah karena sudah menerima penawaran.'],
            'error' => ['error', 'Lelang gagal diperbarui. Silakan coba lagi.'],
        ][$hasil_ubah] ?? ['error', 'Lelang gagal diperbarui. Silakan coba lagi.'];
        set_flash($pesan_ubah[0], $pesan_ubah[1]);
        redirect($hasil_ubah === 'updated' ? 'index.php' : 'detail.php?id=' . $id);
    }

    if (!$error && $id === 0) {
        try {
            buat_hasil_panen($koneksi, $user_id, $input);
            set_flash('success', 'Lelang baru berhasil dibuat.');
            redirect('index.php');
        } catch (mysqli_sql_exception $exception) {
            error_log('Gagal membuat lelang petani: ' . $exception->getMessage());
            $error['database'] = 'Lelang gagal disimpan. Silakan coba lagi.';
        }
    }
}

$judul_halaman = $id > 0 ? 'Ubah Lelang' : 'Tambah Lelang';
$menu_aktif = 'lelang';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <section class="card">
        <?php if ($database_error !== ''): ?>
          <div class="alert alert-error" role="alert"><?= e($database_error) ?></div>
        <?php else: ?>
          <div class="card-head">
            <h2 class="card-title"><?= $id > 0 ? 'Perbarui informasi hasil panen' : 'Informasi hasil panen' ?></h2>
          </div>
          <p class="text-muted">Isi data panen yang akan ditawarkan. Satuan estimasi mengikuti skema database: kilogram.</p>

          <?php if (isset($error['database'])): ?>
            <div class="alert alert-error" role="alert"><?= e($error['database']) ?></div>
          <?php endif; ?>

          <form method="post" action="">
            <?= csrf_field() ?>
            <div class="form-grid">
              <div class="form-group">
                <label for="komoditas">Komoditas</label>
                <input class="input" type="text" id="komoditas" name="komoditas" maxlength="100"
                       value="<?= e($hasil['komoditas']) ?>" placeholder="Padi Ciherang" required>
                <?php tampil_error($error, 'komoditas'); ?>
              </div>

              <div class="form-group">
                <label for="estimasi_tonase">Estimasi hasil panen (kg)</label>
                <input class="input" type="number" id="estimasi_tonase" name="estimasi_tonase"
                       min="0.01" max="99999999.99" step="0.01" value="<?= e($hasil['estimasi_tonase']) ?>" required>
                <?php tampil_error($error, 'estimasi_tonase'); ?>
              </div>

              <div class="form-group">
                <label for="lokasi">Lokasi panen</label>
                <input class="input" type="text" id="lokasi" name="lokasi" maxlength="150"
                       value="<?= e($hasil['lokasi']) ?>" placeholder="Desa Jatinom, Klaten" required>
                <?php tampil_error($error, 'lokasi'); ?>
              </div>

              <div class="form-group">
                <label for="tanggal_panen">Tanggal panen</label>
                <input class="input" type="date" id="tanggal_panen" name="tanggal_panen"
                       value="<?= e($hasil['tanggal_panen']) ?>"<?= $id === 0 ? ' min="' . e(date('Y-m-d')) . '"' : '' ?> required>
                <?php tampil_error($error, 'tanggal_panen'); ?>
              </div>

              <div class="form-group">
                <label for="harga_dasar">Harga dasar (Rp/kg)</label>
                <input class="input" type="number" id="harga_dasar" name="harga_dasar"
                       min="0.01" max="9999999999999.99" step="0.01" value="<?= e($hasil['harga_dasar']) ?>" required>
                <?php tampil_error($error, 'harga_dasar'); ?>
              </div>
            </div>

            <div class="form-group">
              <label for="deskripsi">Keterangan panen (opsional)</label>
              <textarea class="textarea" id="deskripsi" name="deskripsi" maxlength="2000"
                        placeholder="Jelaskan kualitas hasil panen atau ketentuan pengambilan."><?= e($hasil['deskripsi']) ?></textarea>
              <?php tampil_error($error, 'deskripsi'); ?>
            </div>

            <div class="form-actions">
              <button class="btn btn-primary" type="submit"><?= $id > 0 ? 'Simpan perubahan' : 'Buat lelang' ?></button>
              <a class="btn btn-ghost" href="<?= BASE_URL ?>/petani/lelang/index.php">Batal</a>
            </div>
          </form>
        <?php endif; ?>
      </section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
