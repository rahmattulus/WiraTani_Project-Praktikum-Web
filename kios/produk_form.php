<?php
/* Kios: tambah / ubah produk (S12/S18, Marvian).
   Tambah: produk_form.php   Ubah: produk_form.php?id=1 */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('kios');
require_once __DIR__ . '/../includes/saprotan_helper.php';
require_once __DIR__ . '/../config/koneksi.php';

$id     = (int) input_teks($_GET, 'id');
$produk = ['nama_produk' => '', 'kategori' => '', 'harga' => '', 'satuan' => '', 'stok' => '', 'deskripsi' => ''];

if ($id > 0) {
    $stmt = $koneksi->prepare('SELECT * FROM produk_saprotan WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $_SESSION['user_id']);
    $stmt->execute();
    $ditemukan = $stmt->get_result()->fetch_assoc();
    if (!$ditemukan) {
        set_flash('error', 'Produk tidak ditemukan.');
        redirect('produk.php');
    }
    $produk = array_merge($produk, $ditemukan);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('produk_form.php' . ($id > 0 ? '?id=' . $id : ''));
    $produk = [
        'nama_produk' => input_teks($_POST, 'nama_produk'),
        'kategori' => input_teks($_POST, 'kategori'),
        'harga' => input_teks($_POST, 'harga'),
        'satuan' => input_teks($_POST, 'satuan'),
        'stok' => input_teks($_POST, 'stok'),
        'deskripsi' => input_teks($_POST, 'deskripsi'),
    ];
    $error = [];
    if ($produk['nama_produk'] === '' || mb_strlen($produk['nama_produk']) > 150) {
        $error['nama_produk'] = 'Nama produk wajib diisi maksimal 150 karakter.';
    }
    if (!isset(KATEGORI_SAPROTAN[$produk['kategori']])) {
        $error['kategori'] = 'Kategori produk tidak valid.';
    }
    $harga = filter_var($produk['harga'], FILTER_VALIDATE_FLOAT);
    if ($harga === false || $harga <= 0) {
        $error['harga'] = 'Harga harus lebih besar dari nol.';
    }
    if ($produk['satuan'] === '' || mb_strlen($produk['satuan']) > 20) {
        $error['satuan'] = 'Satuan wajib diisi maksimal 20 karakter.';
    }
    if (!ctype_digit($produk['stok'])) {
        $error['stok'] = 'Stok harus berupa angka tidak negatif.';
    }
    if (mb_strlen($produk['deskripsi']) > 500) {
        $error['deskripsi'] = 'Deskripsi maksimal 500 karakter.';
    }

    if (!$error) {
        $stok = (int) $produk['stok'];
        if ($id > 0) {
            $simpan = $koneksi->prepare(
                'UPDATE produk_saprotan
                 SET nama_produk = ?, kategori = ?, harga = ?, satuan = ?, stok = ?, deskripsi = ?
                 WHERE id = ? AND user_id = ?'
            );
            $simpan->bind_param(
                'ssdsisii',
                $produk['nama_produk'], $produk['kategori'], $harga, $produk['satuan'],
                $stok, $produk['deskripsi'], $id, $_SESSION['user_id']
            );
        } else {
            $simpan = $koneksi->prepare(
                'INSERT INTO produk_saprotan (user_id, nama_produk, kategori, harga, satuan, stok, deskripsi)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $simpan->bind_param(
                'issssis',
                $_SESSION['user_id'], $produk['nama_produk'], $produk['kategori'], $harga,
                $produk['satuan'], $stok, $produk['deskripsi']
            );
        }
        $simpan->execute();
        set_flash('success', $id > 0 ? 'Produk berhasil diperbarui.' : 'Produk baru berhasil ditambahkan.');
        redirect('produk.php');
    }
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
              <?php if (isset($error)) tampil_error($error, 'nama_produk'); ?>
            </div>

            <div class="form-group">
              <label for="kategori">Kategori</label>
              <select class="select" id="kategori" name="kategori" required>
                <option value="">Pilih kategori</option>
                <?php foreach (KATEGORI_SAPROTAN as $nilai => $label): ?>
                  <option value="<?= e($nilai) ?>"<?= $produk['kategori'] === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($error)) tampil_error($error, 'kategori'); ?>
            </div>

            <div class="form-group">
              <label for="harga">Harga (Rp)</label>
              <input class="input" type="number" id="harga" name="harga" min="1" step="1"
                     value="<?= e($produk['harga']) ?>" required>
              <?php if (isset($error)) tampil_error($error, 'harga'); ?>
            </div>

            <div class="form-group">
              <label for="satuan">Satuan</label>
              <input class="input" type="text" id="satuan" name="satuan" maxlength="20"
                     value="<?= e($produk['satuan']) ?>" placeholder="sak, kg, botol, hari" required>
              <?php if (isset($error)) tampil_error($error, 'satuan'); ?>
            </div>

            <div class="form-group">
              <label for="stok">Stok</label>
              <input class="input" type="number" id="stok" name="stok" min="0" step="1"
                     value="<?= e($produk['stok']) ?>" required>
              <?php if (isset($error)) tampil_error($error, 'stok'); ?>
            </div>
          </div>

          <p class="text-muted">Untuk sewa alat: harga adalah tarif per hari dan stok adalah jumlah unit yang tersedia.</p>

          <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea class="textarea" id="deskripsi" name="deskripsi" maxlength="500"><?= e($produk['deskripsi']) ?></textarea>
            <?php if (isset($error)) tampil_error($error, 'deskripsi'); ?>
          </div>

          <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $id > 0 ? 'Simpan perubahan' : 'Simpan produk' ?></button>
            <a class="btn btn-ghost" href="produk.php">Batal</a>
          </div>
        </form>
      </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
