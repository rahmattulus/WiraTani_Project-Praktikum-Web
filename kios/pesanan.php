<?php
/* Kios: pesanan masuk dari petani (S12, Marvian). UI dengan data dummy. */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('kios');
require_once __DIR__ . '/../includes/saprotan_helper.php';
require_once __DIR__ . '/../includes/dummy_saprotan.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('pesanan.php');
    $aksi  = input_teks($_POST, 'aksi');
    $nama  = ['setujui' => 'Setujui pesanan', 'tolak' => 'Tolak pesanan', 'selesai' => 'Tandai pesanan selesai'];
    if (isset($nama[$aksi])) {
        // TODO S21 (satu transaksi database):
        //   setujui: cek stok cukup, kurangi stok, status = 'disetujui'
        //   tolak:   status = 'ditolak'
        //   selesai: status = 'selesai' + catat_transaksi() pengeluaran ke ERP petani
        aksi_mode_ui($nama[$aksi], 'pesanan.php');
    }
    redirect('pesanan.php');
}

$status = input_teks($_GET, 'status');
if (!isset(STATUS_PESANAN[$status])) {
    $status = '';
}

// TODO S21: JOIN pesanan_saprotan, produk_saprotan, users WHERE produk_saprotan.user_id = ?
$pesanan = [];
foreach ($dummy_pesanan as $ps) {
    $produk = cari_produk($ps['produk_saprotan_id']);
    if (!$produk || $produk['user_id'] !== $dummy_user_kios) {
        continue;
    }
    if ($status !== '' && $ps['status'] !== $status) {
        continue;
    }
    $ps['produk'] = $produk;
    $pesanan[]    = $ps;
}

$judul_halaman = 'Pesanan Masuk';
$menu_aktif    = 'pesanan';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Pesanan dari petani</h2>
          </div>

          <form class="filter-bar" method="get" action="">
            <div class="form-group">
              <label for="status">Status</label>
              <select class="select" id="status" name="status">
                <option value="">Semua status</option>
                <?php foreach (STATUS_PESANAN as $nilai => $label): ?>
                  <option value="<?= e($nilai) ?>"<?= $status === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button class="btn btn-ghost" type="submit">Terapkan</button>
          </form>

          <?php if (!$pesanan): ?>
            <div class="empty-state">
              <p>Belum ada pesanan<?= $status !== '' ? ' dengan status ' . e(strtolower(STATUS_PESANAN[$status])) : '' ?>.</p>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Tanggal</th>
                    <th>Petani</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pesanan as $ps): ?>
                    <?php
                      $p          = $ps['produk'];
                      $stok_cukup = $ps['jumlah'] <= $p['stok'];
                    ?>
                    <tr>
                      <td data-label="Tanggal"><?= e(format_tanggal($ps['created_at'])) ?></td>
                      <td data-label="Petani"><?= e($ps['nama_petani']) ?></td>
                      <td data-label="Produk">
                        <?= e($p['nama_produk']) ?>
                        <?php if ($p['kategori'] === 'sewa_alat'): ?>
                          <br><small>Mulai sewa <?= e(format_tanggal($ps['tanggal_mulai_sewa'])) ?>, <?= (int) $ps['durasi_hari'] ?> hari</small>
                        <?php endif; ?>
                        <?php if ($ps['catatan'] !== ''): ?>
                          <br><small>Catatan: <?= e($ps['catatan']) ?></small>
                        <?php endif; ?>
                      </td>
                      <td data-label="Jumlah"><?= (int) $ps['jumlah'] ?> <?= e($p['kategori'] === 'sewa_alat' ? 'unit' : $p['satuan']) ?></td>
                      <td data-label="Total"><?= e(format_rupiah($ps['total_harga'])) ?></td>
                      <td data-label="Status"><?= badge_pesanan($ps['status']) ?></td>
                      <td data-label="Aksi">
                        <?php if ($ps['status'] === 'menunggu'): ?>
                          <div class="table-actions">
                            <form method="post" action="" data-confirm="Setujui pesanan ini? Stok akan berkurang.">
                              <?= csrf_field() ?>
                              <input type="hidden" name="id" value="<?= (int) $ps['id'] ?>">
                              <input type="hidden" name="aksi" value="setujui">
                              <button class="btn btn-primary btn-sm" type="submit"<?= $stok_cukup ? '' : ' disabled' ?>>Setujui</button>
                            </form>
                            <form method="post" action="" data-confirm="Tolak pesanan ini?">
                              <?= csrf_field() ?>
                              <input type="hidden" name="id" value="<?= (int) $ps['id'] ?>">
                              <input type="hidden" name="aksi" value="tolak">
                              <button class="btn btn-danger btn-sm" type="submit">Tolak</button>
                            </form>
                          </div>
                          <?php if (!$stok_cukup): ?>
                            <small class="form-error">Stok tersisa <?= e(teks_stok($p)) ?>, kurang dari jumlah pesanan.</small>
                          <?php endif; ?>
                        <?php elseif ($ps['status'] === 'disetujui'): ?>
                          <form method="post" action="" data-confirm="Tandai selesai? Biaya akan tercatat di keuangan petani.">
                            <?= csrf_field() ?>
                              <input type="hidden" name="id" value="<?= (int) $ps['id'] ?>">
                            <input type="hidden" name="aksi" value="selesai">
                            <button class="btn btn-primary btn-sm" type="submit">Tandai selesai</button>
                          </form>
                        <?php else: ?>
                          <span class="text-muted">Tidak ada aksi</span>
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
