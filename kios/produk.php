<?php
/* Kios: daftar produk milik kios (S12/S18, Marvian). */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('kios');
require_once __DIR__ . '/../includes/saprotan_helper.php';
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('produk.php');
    $id = (int) input_teks($_POST, 'id');
    if ($id > 0) {
        $cek = $koneksi->prepare(
            'SELECT id FROM produk_saprotan WHERE id = ? AND user_id = ?'
        );
        $cek->bind_param('ii', $id, $_SESSION['user_id']);
        $cek->execute();
        if (!$cek->get_result()->fetch_assoc()) {
            set_flash('error', 'Produk tidak ditemukan atau bukan milik kios Anda.');
        } else {
            $pakai = $koneksi->prepare(
                'SELECT id FROM pesanan_saprotan WHERE produk_saprotan_id = ? LIMIT 1'
            );
            $pakai->bind_param('i', $id);
            $pakai->execute();
            if ($pakai->get_result()->fetch_assoc()) {
                set_flash('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat pesanan.');
            } else {
                $hapus = $koneksi->prepare(
                    'DELETE FROM produk_saprotan WHERE id = ? AND user_id = ?'
                );
                $hapus->bind_param('ii', $id, $_SESSION['user_id']);
                $hapus->execute();
                set_flash('success', 'Produk berhasil dihapus.');
            }
        }
    }
    redirect('produk.php');
}

$kategori = input_teks($_GET, 'kategori');
if (!isset(KATEGORI_SAPROTAN[$kategori])) {
    $kategori = '';
}

if ($kategori === '') {
    $stmt = $koneksi->prepare(
        'SELECT * FROM produk_saprotan WHERE user_id = ? ORDER BY created_at DESC, id DESC'
    );
    $stmt->bind_param('i', $_SESSION['user_id']);
} else {
    $stmt = $koneksi->prepare(
        'SELECT * FROM produk_saprotan WHERE user_id = ? AND kategori = ? ORDER BY created_at DESC, id DESC'
    );
    $stmt->bind_param('is', $_SESSION['user_id'], $kategori);
}
$stmt->execute();
$produk = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
