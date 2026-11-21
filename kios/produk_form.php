<?php
/* Kios: tambah / ubah produk (S12, Marvian). UI dengan data dummy.
   Tambah: produk_form.php   Ubah: produk_form.php?id=1 */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('kios');
require_once __DIR__ . '/../includes/saprotan_helper.php';
require_once __DIR__ . '/../includes/dummy_saprotan.php';

$id     = (int) input_teks($_GET, 'id');
$produk = ['nama_produk' => '', 'kategori' => '', 'harga' => '', 'satuan' => '', 'stok' => '', 'deskripsi' => ''];

if ($id > 0) {
    // TODO S18: SELECT ... WHERE id = ? AND user_id = ? (hanya pemilik yang boleh mengubah)
    $ditemukan = cari_produk($id);
    if (!$ditemukan || $ditemukan['user_id'] !== $dummy_user_kios) {
        set_flash('error', 'Produk tidak ditemukan.');
        redirect('produk.php');
    }
    $produk = array_merge($produk, $ditemukan);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('produk_form.php' . ($id > 0 ? '?id=' . $id : ''));
    // TODO S18: validasi (harga > 0, stok >= 0, kategori valid) lalu INSERT / UPDATE.
    aksi_mode_ui($id > 0 ? 'Simpan perubahan produk' : 'Simpan produk baru', 'produk.php');
}

$judul_halaman = $id > 0 ? 'Ubah Produk' : 'Tambah Produk';
$menu_aktif    = 'produk';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
      <section class="card">
        <div class="card-head">
          <h2 class="card-title"><?= $id > 0 ? 'Data produk' : 'Produk baru' ?></h2>
        </div>

        <form method="post" action="">
          <?= csrf_field() ?>
          <div class="form-grid">
            <div class="form-group">
              <label for="nama_produk">Nama produk</label>
              <input class="input" type="text" id="nama_produk" name="nama_produk" maxlength="150"
                     value="<?= e($produk['nama_produk']) ?>" required>
            </div>

            <div class="form-group">
              <label for="kategori">Kategori</label>
              <select class="select" id="kategori" name="kategori" required>
                <option value="">Pilih kategori</option>
                <?php foreach (KATEGORI_SAPROTAN as $nilai => $label): ?>
                  <option value="<?= e($nilai) ?>"<?= $produk['kategori'] === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="harga">Harga (Rp)</label>
              <input class="input" type="number" id="harga" name="harga" min="1" step="100"
                     value="<?= e($produk['harga']) ?>" required>
            </div>

            <div class="form-group">
              <label for="satuan">Satuan</label>
              <input class="input" type="text" id="satuan" name="satuan" maxlength="20"
                     value="<?= e($produk['satuan']) ?>" placeholder="sak, kg, botol, hari" required>
            </div>

            <div class="form-group">
              <label for="stok">Stok</label>
              <input class="input" type="number" id="stok" name="stok" min="0" step="1"
                     value="<?= e($produk['stok']) ?>" required>
            </div>
          </div>

          <p class="text-muted">Untuk sewa alat: harga adalah tarif per hari dan stok adalah jumlah unit yang tersedia.</p>

          <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea class="textarea" id="deskripsi" name="deskripsi" maxlength="500"><?= e($produk['deskripsi']) ?></textarea>
          </div>

          <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $id > 0 ? 'Simpan perubahan' : 'Simpan produk' ?></button>
            <a class="btn btn-ghost" href="produk.php">Batal</a>
          </div>
        </form>
      </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
