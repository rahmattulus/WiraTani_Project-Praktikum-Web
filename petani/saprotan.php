<?php
/* Petani: katalog saprotan dari semua kios (S13, Marvian). UI dengan data dummy. */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../includes/saprotan_helper.php';
require_once __DIR__ . '/../includes/dummy_saprotan.php';

$q        = input_teks($_GET, 'q');
$lokasi   = input_teks($_GET, 'lokasi');
$kategori = input_teks($_GET, 'kategori');
if (!isset(KATEGORI_SAPROTAN[$kategori])) {
    $kategori = '';
}

// TODO S22: SELECT p.*, u.nama AS nama_kios, u.alamat AS alamat_kios
//           FROM produk_saprotan p JOIN users u ON u.id = p.user_id
//           WHERE (p.nama_produk LIKE ? OR u.nama LIKE ?) AND u.alamat LIKE ? [AND p.kategori = ?]
$produk = array_filter($dummy_produk, function ($p) use ($q, $lokasi, $kategori) {
    if ($kategori !== '' && $p['kategori'] !== $kategori) {
        return false;
    }
    if ($q !== '' && stripos($p['nama_produk'] . ' ' . $p['nama_kios'], $q) === false) {
        return false;
    }
    if ($lokasi !== '' && stripos($p['alamat_kios'], $lokasi) === false) {
        return false;
    }
    return true;
});

$judul_halaman = 'Saprotan';
$menu_aktif    = 'saprotan';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Katalog saprotan</h2>
            <a class="btn btn-ghost btn-sm" href="saprotan_riwayat.php">Riwayat pesanan</a>
          </div>

          <form class="filter-bar" method="get" action="">
            <div class="form-group">
              <label for="q">Produk atau kios</label>
              <input class="input" type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="urea, Kios Tani Subur">
            </div>
            <div class="form-group">
              <label for="kategori">Kategori</label>
              <select class="select" id="kategori" name="kategori">
                <option value="">Semua kategori</option>
                <?php foreach (KATEGORI_SAPROTAN as $nilai => $label): ?>
                  <option value="<?= e($nilai) ?>"<?= $kategori === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="lokasi">Lokasi kios</label>
              <input class="input" type="search" id="lokasi" name="lokasi" value="<?= e($lokasi) ?>" placeholder="Klaten, Magelang">
            </div>
            <button class="btn btn-ghost" type="submit">Cari</button>
          </form>

          <?php if (!$produk): ?>
            <div class="empty-state">
              <p>Tidak ada produk yang cocok dengan pencarian Anda.</p>
              <a class="btn btn-ghost" href="saprotan.php">Tampilkan semua produk</a>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Produk</th>
                    <th>Kios</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($produk as $p): ?>
                    <tr>
                      <td data-label="Produk">
                        <?= e($p['nama_produk']) ?>
                        <span class="badge badge-muted"><?= e(label_kategori($p['kategori'])) ?></span>
                      </td>
                      <td data-label="Kios"><?= e($p['nama_kios']) ?><br><small><?= e($p['alamat_kios']) ?></small></td>
                      <td data-label="Harga"><?= e(format_rupiah($p['harga'])) ?> / <?= e($p['satuan']) ?></td>
                      <td data-label="Stok"><?= e(teks_stok($p)) ?></td>
                      <td data-label="Aksi">
                        <?php if ($p['stok'] > 0): ?>
                          <a class="btn btn-primary btn-sm" href="saprotan_pesan.php?produk_id=<?= (int) $p['id'] ?>">Pesan</a>
                        <?php else: ?>
                          <span class="badge badge-danger">Habis</span>
                        <?php endif; ?>
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
