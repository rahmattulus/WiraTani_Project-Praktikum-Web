<?php
/* Daftar lowongan milik petani. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../config/koneksi.php';

$user_id = user_id_login();
$status_filter = input_teks($_GET, 'status');
if (!in_array($status_filter, ['', 'buka', 'ditutup'], true)) {
    $status_filter = '';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && input_teks($_POST, 'aksi') === 'hapus') {
    wajib_csrf('index.php');
    $id_input = input_teks($_POST, 'id');
    if (!ctype_digit($id_input) || (int) $id_input < 1) {
        set_flash('error', 'Lowongan tidak valid.');
        redirect('index.php');
    }

    $stmt = $koneksi->prepare('DELETE FROM lowongan_kerja WHERE id = ? AND user_id = ?');
    $id = (int) $id_input;
    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    if ($stmt->affected_rows === 1) {
        set_flash('success', 'Lowongan berhasil dihapus.');
    } else {
        set_flash('error', 'Lowongan tidak ditemukan atau bukan milik Anda.');
    }
    redirect('index.php');
}

$sql = 'SELECT id, judul, jenis_pekerjaan, lokasi, jumlah_buruh, upah_harian,
               tanggal_mulai, durasi_hari, deskripsi, status
        FROM lowongan_kerja WHERE user_id = ?';
if ($status_filter !== '') {
    $sql .= ' AND status = ?';
}
$sql .= ' ORDER BY created_at DESC, id DESC';
$stmt = $koneksi->prepare($sql);
if ($status_filter !== '') {
    $stmt->bind_param('is', $user_id, $status_filter);
} else {
    $stmt->bind_param('i', $user_id);
}
$stmt->execute();
$lowongan_tampil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$label_pekerjaan = [
    'tanam' => 'Tanam',
    'panen' => 'Panen',
    'lainnya' => 'Lainnya',
];

$judul_halaman = 'Lowongan Kerja';
$menu_aktif = 'lowongan';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <div>
              <h2 class="card-title">Lowongan saya</h2>
              <p class="text-muted">Pasang informasi kerja harian dengan jumlah buruh dan upah yang jelas.</p>
            </div>
            <a class="btn btn-primary" href="form.php">+ Buat lowongan</a>
          </div>

          <form class="filter-bar" method="get" action="">
            <div class="form-group">
              <label for="status">Status lowongan</label>
              <select class="select" id="status" name="status">
                <option value="">Semua status</option>
                <option value="buka" <?= $status_filter === 'buka' ? 'selected' : '' ?>>Buka</option>
                <option value="ditutup" <?= $status_filter === 'ditutup' ? 'selected' : '' ?>>Ditutup</option>
              </select>
            </div>
            <button class="btn btn-ghost" type="submit">Terapkan</button>
          </form>

          <?php if (!$lowongan_tampil): ?>
            <div class="empty-state">
              <p>Belum ada lowongan yang cocok dengan pilihan ini.</p>
              <a class="btn btn-primary" href="form.php">Buat lowongan pertama</a>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Lowongan</th>
                    <th>Lokasi</th>
                    <th>Buruh</th>
                    <th>Upah / hari</th>
                    <th>Mulai</th>
                    <th>Durasi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lowongan_tampil as $lowongan): ?>
                    <tr>
                      <td data-label="Lowongan">
                        <?= e($lowongan['judul']) ?><br>
                        <small><?= e($label_pekerjaan[$lowongan['jenis_pekerjaan']] ?? 'Lainnya') ?></small>
                      </td>
                      <td data-label="Lokasi"><?= e($lowongan['lokasi']) ?></td>
                      <td data-label="Buruh"><?= (int) $lowongan['jumlah_buruh'] ?> orang</td>
                      <td data-label="Upah / hari"><?= e(format_rupiah($lowongan['upah_harian'])) ?></td>
                      <td data-label="Mulai"><?= e(format_tanggal($lowongan['tanggal_mulai'])) ?></td>
                      <td data-label="Durasi"><?= (int) $lowongan['durasi_hari'] ?> hari</td>
                      <td data-label="Status">
                        <span class="badge <?= $lowongan['status'] === 'buka' ? 'badge-success' : 'badge-muted' ?>">
                          <?= $lowongan['status'] === 'buka' ? 'Buka' : 'Ditutup' ?>
                        </span>
                      </td>
                      <td data-label="Aksi">
                        <div class="table-actions">
                          <a class="btn btn-ghost btn-sm" href="form.php?id=<?= (int) $lowongan['id'] ?>">Ubah</a>
                          <form method="post" action="" data-confirm="Hapus lowongan ini?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="id" value="<?= (int) $lowongan['id'] ?>">
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
<?php include __DIR__ . '/../../includes/footer.php'; ?>
