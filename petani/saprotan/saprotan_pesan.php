<?php
/* Petani: form pesan saprotan (S13, Marvian). UI dengan data dummy.
   Dibuka dari katalog: saprotan_pesan.php?produk_id=1 */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../includes/saprotan_helper.php';
require_once __DIR__ . '/../../includes/dummy_saprotan.php';

$produk = cari_produk((int) input_teks($_GET, 'produk_id'));
if (!$produk || $produk['stok'] <= 0) {
    set_flash('error', 'Produk tidak ditemukan atau stok habis.');
    redirect('saprotan.php');
}
$sewa = $produk['kategori'] === 'sewa_alat';

// TODO S22: SELECT id, nama_musim FROM musim_tanam WHERE user_id = ? AND status = 'berjalan'
$musim_berjalan = array_filter($dummy_musim, function ($m) use ($dummy_user_petani) {
    return $m['user_id'] === $dummy_user_petani && $m['status'] === 'berjalan';
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('saprotan_pesan.php?produk_id=' . (int) $produk['id']);
    // TODO S22: validasi jumlah <= stok, musim milik petani & berjalan, sewa wajib tanggal + durasi;
    //   harga_satuan dan total_harga disalin dari produk saat ini (snapshot),
    //   total = hitung_total_pesanan(...); status awal 'menunggu'.
    aksi_mode_ui('Kirim pesanan', 'saprotan_riwayat.php');
}

$judul_halaman = 'Pesan Saprotan';
$menu_aktif    = 'saprotan';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <h2 class="card-title"><?= e($produk['nama_produk']) ?></h2>
            <span class="badge badge-muted"><?= e(label_kategori($produk['kategori'])) ?></span>
          </div>
          <p><?= e($produk['deskripsi']) ?></p>
          <p>
            <strong><?= e(format_rupiah($produk['harga'])) ?> / <?= e($produk['satuan']) ?></strong><br>
            <?= e($produk['nama_kios']) ?>, <?= e($produk['alamat_kios']) ?><br>
            Stok tersedia: <?= e(teks_stok($produk)) ?>
          </p>
        </section>

        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Detail pesanan</h2>
          </div>

          <?php if (!$musim_berjalan): ?>
            <div class="empty-state">
              <p>Pesanan harus dikaitkan dengan musim tanam yang sedang berjalan, dan Anda belum punya.</p>
              <a class="btn btn-primary" href="musim.php">Buat musim tanam</a>
            </div>
          <?php else: ?>
            <form method="post" action="" data-form-pesan
                  data-harga="<?= (float) $produk['harga'] ?>" data-sewa="<?= $sewa ? '1' : '0' ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="produk_saprotan_id" value="<?= (int) $produk['id'] ?>">

              <div class="form-grid">
                <div class="form-group">
                  <label for="musim_tanam_id">Untuk musim tanam</label>
                  <select class="select" id="musim_tanam_id" name="musim_tanam_id" required>
                    <?php foreach ($musim_berjalan as $m): ?>
                      <option value="<?= (int) $m['id'] ?>"><?= e($m['nama_musim']) ?> (<?= e($m['komoditas']) ?>)</option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="jumlah"><?= $sewa ? 'Jumlah unit' : 'Jumlah (' . e($produk['satuan']) . ')' ?></label>
                  <input class="input" type="number" id="jumlah" name="jumlah" min="1" max="<?= (int) $produk['stok'] ?>"
                         step="1" value="1" required>
                </div>

                <?php if ($sewa): ?>
                  <div class="form-group">
                    <label for="tanggal_mulai_sewa">Tanggal mulai sewa</label>
                    <input class="input" type="date" id="tanggal_mulai_sewa" name="tanggal_mulai_sewa"
                           min="<?= date('Y-m-d') ?>" required>
                  </div>

                  <div class="form-group">
                    <label for="durasi_hari">Durasi (hari)</label>
                    <input class="input" type="number" id="durasi_hari" name="durasi_hari" min="1" step="1" value="1" required>
                  </div>
                <?php endif; ?>
              </div>

              <div class="form-group">
                <label for="catatan">Catatan untuk kios (opsional)</label>
                <textarea class="textarea" id="catatan" name="catatan" maxlength="255"></textarea>
              </div>

              <p>Perkiraan total: <strong data-total><?= e(format_rupiah($produk['harga'])) ?></strong></p>

              <div class="form-actions">
                <button class="btn btn-primary" type="submit">Kirim pesanan</button>
                <a class="btn btn-ghost" href="saprotan.php">Batal</a>
              </div>
            </form>
          <?php endif; ?>
        </section>
      </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
