<?php
/* Kios: daftar produk saya (S12, Marvian). UI dengan data dummy. */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('kios');
require_once __DIR__ . '/../includes/saprotan_helper.php';
require_once __DIR__ . '/../includes/dummy_saprotan.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('produk.php');
    // TODO S18: DELETE ... WHERE id = ? AND user_id = ?; tolak bila produk sudah punya pesanan (RESTRICT).
    aksi_mode_ui('Hapus produk', 'produk.php');
}

$kategori = input_teks($_GET, 'kategori');
if (!isset(KATEGORI_SAPROTAN[$kategori])) {
    $kategori = '';
}

// TODO S18: SELECT * FROM produk_saprotan WHERE user_id = ? [AND kategori = ?]
$produk = array_filter($dummy_produk, function ($p) use ($dummy_user_kios, $kategori) {
    return $p['user_id'] === $dummy_user_kios && ($kategori === '' || $p['kategori'] === $kategori);
});

$judul_halaman = 'Produk Saya';
$menu_aktif    = 'produk';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Katalog produk</h2>
            <a class="btn btn-primary btn-sm" href="produk_form.php">Tambah produk</a>
          </div>

          <form class="filter-bar" method="get" action="">
            <div class="form-group">
              <label for="kategori">Kategori</label>
              <select class="select" id="kategori" name="kategori">
                <option value="">Semua kategori</option>
                <?php foreach (KATEGORI_SAPROTAN as $nilai => $label): ?>
                  <option value="<?= e($nilai) ?>"<?= $kategori === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button class="btn btn-ghost" type="submit">Terapkan</button>
          </form>

          <?php if (!$produk): ?>
            <div class="empty-state">
              <p>Belum ada produk<?= $kategori !== '' ? ' di kategori ' . e(label_kategori($kategori)) : '' ?>.</p>
              <a class="btn btn-primary" href="produk_form.php">Tambah produk pertama</a>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($produk as $p): ?>
                    <tr>
                      <td data-label="Produk"><?= e($p['nama_produk']) ?></td>
                      <td data-label="Kategori"><span class="badge badge-muted"><?= e(label_kategori($p['kategori'])) ?></span></td>
                      <td data-label="Harga"><?= e(format_rupiah($p['harga'])) ?> / <?= e($p['satuan']) ?></td>
                      <td data-label="Stok">
                        <?= e(teks_stok($p)) ?>
                        <?php if ($p['stok'] <= STOK_MENIPIS): ?>
                          <span class="badge badge-warning">Menipis</span>
                        <?php endif; ?>
                      </td>
                      <td data-label="Aksi">
                        <div class="table-actions">
                          <a class="btn btn-ghost btn-sm" href="produk_form.php?id=<?= (int) $p['id'] ?>">Ubah</a>
                          <form method="post" action="" data-confirm="Hapus produk <?= e($p['nama_produk']) ?>? Tindakan ini tidak bisa dibatalkan.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </section>
      </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
