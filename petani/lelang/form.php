<?php
/* Petani: tambah / ubah lelang (S10). UI memakai data dummy sampai Fase 3. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../includes/dummy_lelang.php';

$id = (int) input_teks($_GET, 'id');
$hasil = [
    'komoditas' => '',
    'estimasi_tonase' => '',
    'lokasi' => '',
    'tanggal_panen' => '',
    'harga_dasar' => '',
    'deskripsi' => '',
];

if ($id > 0) {
    $ditemukan = null;
    foreach ($dummy_hasil_panen as $item) {
        if ($item['id'] === $id && $item['user_id'] === $dummy_lelang_petani_id) {
            $ditemukan = $item;
            break;
        }
    }

    if (!$ditemukan) {
        set_flash('error', 'Data lelang tidak ditemukan.');
        redirect('index.php');
    }

    $jumlah_tawaran = count(array_filter($dummy_penawaran_lelang, function ($penawaran) use ($id) {
        return $penawaran['hasil_panen_id'] === $id;
    }));
    if ($ditemukan['status'] !== 'aktif' || $jumlah_tawaran > 0) {
        set_flash('error', 'Lelang tidak dapat diubah setelah menerima tawaran atau selesai.');
        redirect('detail.php?id=' . $id);
    }
    $hasil = array_merge($hasil, $ditemukan);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('form.php' . ($id > 0 ? '?id=' . $id : ''));
    // TODO S17: validasi lalu INSERT / UPDATE hasil_panen milik petani.
    set_flash('info', ($id > 0 ? 'Perubahan lelang' : 'Lelang baru') . ' belum tersambung ke database (mode tampilan dengan data dummy).');
    redirect('index.php');
}

$judul_halaman = $id > 0 ? 'Ubah Lelang' : 'Tambah Lelang';
$menu_aktif = 'lelang';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <section class="card">
        <div class="card-head">
          <h2 class="card-title"><?= $id > 0 ? 'Perbarui informasi hasil panen' : 'Informasi hasil panen' ?></h2>
        </div>
        <p class="text-muted">Isi data panen yang akan ditawarkan. Satuan estimasi mengikuti skema database: kilogram.</p>

        <form method="post" action="">
          <?= csrf_field() ?>
          <div class="form-grid">
            <div class="form-group">
              <label for="komoditas">Komoditas</label>
              <input class="input" type="text" id="komoditas" name="komoditas" maxlength="100"
                     value="<?= e($hasil['komoditas']) ?>" placeholder="Padi Ciherang" required>
            </div>

            <div class="form-group">
              <label for="estimasi_tonase">Estimasi hasil panen (kg)</label>
              <input class="input" type="number" id="estimasi_tonase" name="estimasi_tonase"
                     min="0.01" step="0.01" value="<?= e($hasil['estimasi_tonase']) ?>" required>
            </div>

            <div class="form-group">
              <label for="lokasi">Lokasi panen</label>
              <input class="input" type="text" id="lokasi" name="lokasi" maxlength="150"
                     value="<?= e($hasil['lokasi']) ?>" placeholder="Desa Jatinom, Klaten" required>
            </div>

            <div class="form-group">
              <label for="tanggal_panen">Tanggal panen</label>
              <input class="input" type="date" id="tanggal_panen" name="tanggal_panen"
                     value="<?= e($hasil['tanggal_panen']) ?>"<?= $id === 0 ? ' min="' . e(date('Y-m-d')) . '"' : '' ?> required>
            </div>

            <div class="form-group">
              <label for="harga_dasar">Harga dasar (Rp/kg)</label>
              <input class="input" type="number" id="harga_dasar" name="harga_dasar"
                     min="0.01" step="0.01" value="<?= e($hasil['harga_dasar']) ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label for="deskripsi">Keterangan panen (opsional)</label>
            <textarea class="textarea" id="deskripsi" name="deskripsi" maxlength="2000"
                      placeholder="Jelaskan kualitas hasil panen atau ketentuan pengambilan."><?= e($hasil['deskripsi']) ?></textarea>
          </div>

          <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $id > 0 ? 'Simpan perubahan' : 'Buat lelang' ?></button>
            <a class="btn btn-ghost" href="<?= BASE_URL ?>/petani/lelang/index.php">Batal</a>
          </div>
        </form>
      </section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
