<?php
/* Kios: pesanan masuk dari petani (S12/S25, Marvian). */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('kios');
require_once __DIR__ . '/../includes/saprotan_helper.php';
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('pesanan.php');
    $aksi  = input_teks($_POST, 'aksi');
    $pesanan_id = (int) input_teks($_POST, 'id');
    if ($pesanan_id > 0 && isset(['setujui' => 1, 'tolak' => 1, 'selesai' => 1][$aksi])) {
        $koneksi->begin_transaction();
        try {
            $lock = $koneksi->prepare(
                'SELECT ps.*, p.user_id AS kios_user_id, p.nama_produk, p.kategori,
                        p.satuan, p.stok AS stok_produk, u.nama AS nama_petani
                 FROM pesanan_saprotan ps
                 JOIN produk_saprotan p ON p.id = ps.produk_saprotan_id
                 JOIN users u ON u.id = ps.user_id
                 WHERE ps.id = ? FOR UPDATE'
            );
            $lock->bind_param('i', $pesanan_id);
            $lock->execute();
            $pesanan = $lock->get_result()->fetch_assoc();

            if (!$pesanan || (int) $pesanan['kios_user_id'] !== (int) $_SESSION['user_id']) {
                throw new RuntimeException('Pesanan tidak ditemukan atau bukan milik kios Anda.');
            }

            if ($aksi === 'setujui') {
                if ($pesanan['status'] !== 'menunggu') {
                    throw new RuntimeException('Pesanan sudah tidak berstatus menunggu.');
                }
                if ((int) $pesanan['jumlah'] > (int) $pesanan['stok_produk']) {
                    throw new RuntimeException('Stok tidak cukup untuk menyetujui pesanan.');
                }
                $kurang = $koneksi->prepare(
                    'UPDATE produk_saprotan SET stok = stok - ? WHERE id = ? AND stok >= ?'
                );
                $kurang->bind_param(
                    'iii',
                    $pesanan['jumlah'],
                    $pesanan['produk_saprotan_id'],
                    $pesanan['jumlah']
                );
                $kurang->execute();
                if ($kurang->affected_rows !== 1) {
                    throw new RuntimeException('Stok berubah saat pesanan diproses.');
                }
                $ubah = $koneksi->prepare(
                    'UPDATE pesanan_saprotan SET status = "disetujui" WHERE id = ?'
                );
                $ubah->bind_param('i', $pesanan_id);
                $ubah->execute();
                set_flash('success', 'Pesanan disetujui dan stok berhasil dikurangi.');
            } elseif ($aksi === 'tolak') {
                if ($pesanan['status'] !== 'menunggu') {
                    throw new RuntimeException('Pesanan sudah diproses dan tidak dapat ditolak.');
                }
                $ubah = $koneksi->prepare(
                    'UPDATE pesanan_saprotan SET status = "ditolak" WHERE id = ?'
                );
                $ubah->bind_param('i', $pesanan_id);
                $ubah->execute();
                set_flash('success', 'Pesanan ditolak.');
            } elseif ($aksi === 'selesai') {
                if ($pesanan['status'] !== 'disetujui') {
                    throw new RuntimeException('Pesanan harus berstatus disetujui sebelum diselesaikan.');
                }
                $tanggal = date('Y-m-d');
                $jumlah_satuan = $pesanan['kategori'] === 'sewa_alat'
                    ? 'unit'
                    : $pesanan['satuan'];
                $catat = catat_transaksi(
                    $koneksi,
                    (int) $pesanan['musim_tanam_id'],
                    'pengeluaran',
                    kategori_keuangan($pesanan['kategori']),
                    (float) $pesanan['total_harga'],
                    $tanggal,
                    'Pembayaran saprotan: ' . $pesanan['nama_produk'] . ' ' .
                        (int) $pesanan['jumlah'] . ' ' . $jumlah_satuan,
                    null,
                    $pesanan_id
                );
                if (!$catat) {
                    throw new RuntimeException('Biaya pesanan sudah pernah tercatat atau tidak valid.');
                }
                $ubah = $koneksi->prepare(
                    'UPDATE pesanan_saprotan SET status = "selesai" WHERE id = ?'
                );
                $ubah->bind_param('i', $pesanan_id);
                $ubah->execute();
                set_flash(
                    'success',
                    'Pesanan ' . $pesanan['nama_produk'] . ' untuk ' . $pesanan['nama_petani'] . ' selesai diproses.'
                );
            }
            $koneksi->commit();
        } catch (Throwable $ex) {
            $koneksi->rollback();
            set_flash('error', $ex instanceof RuntimeException ? $ex->getMessage() : 'Gagal memproses pesanan.');
        }
    }
    redirect('pesanan.php');
}

$status = input_teks($_GET, 'status');
if (!isset(STATUS_PESANAN[$status])) {
    $status = '';
}

$sql = 'SELECT ps.*, p.nama_produk, p.kategori, p.satuan, p.stok,
               u.nama AS nama_petani
        FROM pesanan_saprotan ps
        JOIN produk_saprotan p ON p.id = ps.produk_saprotan_id
        JOIN users u ON u.id = ps.user_id
        WHERE p.user_id = ?';
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
                      $stok_cukup = (int) $ps['jumlah'] <= (int) $ps['stok'];
                    ?>
                    <tr>
                      <td data-label="Tanggal"><?= e(format_tanggal($ps['created_at'])) ?></td>
                      <td data-label="Petani"><?= e($ps['nama_petani']) ?></td>
                      <td data-label="Produk">
                        <?= e($ps['nama_produk']) ?>
                        <?php if ($ps['kategori'] === 'sewa_alat'): ?>
                          <br><small>Mulai sewa <?= e(format_tanggal($ps['tanggal_mulai_sewa'])) ?>, <?= (int) $ps['durasi_hari'] ?> hari</small>
                        <?php endif; ?>
                        <?php if ($ps['catatan'] !== ''): ?>
                          <br><small>Catatan: <?= e($ps['catatan']) ?></small>
                        <?php endif; ?>
                      </td>
                      <td data-label="Jumlah"><?= (int) $ps['jumlah'] ?> <?= e($ps['kategori'] === 'sewa_alat' ? 'unit' : $ps['satuan']) ?></td>
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
                            <small class="form-error">Stok tersisa <?= (int) $ps['stok'] ?> <?= e($ps['kategori'] === 'sewa_alat' ? 'unit' : $ps['satuan']) ?>, kurang dari jumlah pesanan.</small>
                          <?php endif; ?>
                        <?php elseif ($ps['status'] === 'disetujui'): ?>
                          <form method="post" action="" data-confirm="Pastikan petani sudah melakukan pembayaran sebelum menandai pesanan ini selesai.">
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
