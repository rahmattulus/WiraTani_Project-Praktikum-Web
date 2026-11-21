<?php
/* Petani: riwayat pesanan saprotan (S13/S22, Marvian). */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../includes/saprotan_helper.php';
require_once __DIR__ . '/../../config/koneksi.php';

$status = input_teks($_GET, 'status');
if (!isset(STATUS_PESANAN[$status])) {
  $status = '';
}

$sql = 'SELECT ps.*, p.nama_produk, p.kategori, p.satuan, k.nama AS nama_kios
        FROM pesanan_saprotan ps
        JOIN produk_saprotan p ON p.id = ps.produk_saprotan_id
        JOIN users k ON k.id = p.user_id
        WHERE ps.user_id = ?';
$params = [$_SESSION['user_id']];
$types = 'i';
if ($status !== '') {
    $sql .= ' AND ps.status = ?';
    $params[] = $status;
    $types .= 's';
}
$sql .= ' ORDER BY ps.created_at DESC, ps.id DESC';
$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$judul_halaman = 'Riwayat Pesanan Saprotan';
$menu_aktif    = 'saprotan';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="dash">
  <section class="card">
    <div class="card-head">
      <h2 class="card-title">Pesanan saya</h2>
      <a class="btn btn-ghost btn-sm" href="index.php">Kembali ke katalog</a>
    </div>

    <form class="filter-bar" method="get" action="">
      <div class="form-group">
        <label for="status">Status</label>
        <select class="select" id="status" name="status">
          <option value="">Semua status</option>
          <?php foreach (STATUS_PESANAN as $nilai => $label): ?>
            <option value="<?= e($nilai) ?>" <?= $status === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-ghost" type="submit">Terapkan</button>
    </form>

    <?php if (!$pesanan): ?>
      <div class="empty-state">
        <p>Belum ada pesanan<?= $status !== '' ? ' dengan status ' . e(strtolower(STATUS_PESANAN[$status])) : '' ?>.</p>
        <a class="btn btn-primary" href="index.php">Lihat katalog saprotan</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Produk</th>
              <th>Kios</th>
              <th>Jumlah</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pesanan as $ps): ?>
              <tr>
                <td data-label="Tanggal"><?= e(format_tanggal($ps['created_at'])) ?></td>
                <td data-label="Produk">
                  <?= e($ps['nama_produk']) ?>
                  <?php if ($ps['kategori'] === 'sewa_alat'): ?>
                    <br><small>Mulai sewa <?= e(format_tanggal($ps['tanggal_mulai_sewa'])) ?>, <?= (int) $ps['durasi_hari'] ?> hari</small>
                  <?php endif; ?>
                </td>
                <td data-label="Kios"><?= e($ps['nama_kios']) ?></td>
                <td data-label="Jumlah"><?= (int) $ps['jumlah'] ?> <?= e($ps['kategori'] === 'sewa_alat' ? 'unit' : $ps['satuan']) ?></td>
                <td data-label="Total"><?= e(format_rupiah($ps['total_harga'])) ?></td>
                <td data-label="Status"><?= badge_pesanan($ps['status']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>