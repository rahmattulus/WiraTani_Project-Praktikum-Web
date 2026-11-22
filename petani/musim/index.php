<?php
/* Daftar musim tanam milik petani. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../config/koneksi.php';

$user_id = user_id_login();
$status_filter = input_teks($_GET, 'status');
if (!in_array($status_filter, ['', 'berjalan', 'selesai'], true)) {
    $status_filter = '';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && input_teks($_POST, 'aksi') === 'hapus') {
    wajib_csrf('index.php');
    $id_input = input_teks($_POST, 'id');
    if (!ctype_digit($id_input) || (int) $id_input < 1) {
        set_flash('error', 'Musim tanam tidak valid.');
        redirect('index.php');
    }

    try {
        $stmt = $koneksi->prepare('DELETE FROM musim_tanam WHERE id = ? AND user_id = ?');
        $id = (int) $id_input;
        $stmt->bind_param('ii', $id, $user_id);
        $stmt->execute();
        if ($stmt->affected_rows === 1) {
            set_flash('success', 'Musim tanam berhasil dihapus.');
        } else {
            set_flash('error', 'Musim tanam tidak ditemukan atau bukan milik Anda.');
        }
    } catch (mysqli_sql_exception $e) {
        error_log('Gagal menghapus musim_tanam: ' . $e->getMessage());
        if ((int) $e->getCode() === 1451) {
            set_flash('error', 'Musim tanam tidak dapat dihapus karena sudah dipakai transaksi atau pesanan.');
        } else {
            set_flash('error', 'Musim tanam gagal dihapus. Silakan coba lagi.');
        }
    }
    redirect('index.php');
}

$sql = 'SELECT id, nama_musim, komoditas, tanggal_mulai, tanggal_selesai, status
        FROM musim_tanam WHERE user_id = ?';
if ($status_filter !== '') {
    $sql .= ' AND status = ?';
}
$sql .= ' ORDER BY tanggal_mulai DESC, id DESC';
$stmt = $koneksi->prepare($sql);
if ($status_filter !== '') {
    $stmt->bind_param('is', $user_id, $status_filter);
} else {
    $stmt->bind_param('i', $user_id);
}
$stmt->execute();
$musim_tampil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$judul_halaman = 'Musim Tanam';
$menu_aktif = 'musim';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <div>
              <h2 class="card-title">Musim tanam saya</h2>
              <p class="text-muted">Kelola siklus tanam dan pantau catatan keuangannya.</p>
            </div>
            <a class="btn btn-primary" href="form.php">+ Tambah musim</a>
          </div>

          <form class="filter-bar" method="get" action="">
            <div class="form-group">
              <label for="status">Status musim</label>
              <select class="select" id="status" name="status">
                <option value="">Semua status</option>
                <option value="berjalan" <?= $status_filter === 'berjalan' ? 'selected' : '' ?>>Berjalan</option>
                <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
              </select>
            </div>
            <button class="btn btn-ghost" type="submit">Terapkan</button>
          </form>

          <?php if (!$musim_tampil): ?>
            <div class="empty-state">
              <p>Belum ada musim tanam yang cocok dengan pilihan ini.</p>
              <a class="btn btn-primary" href="form.php">Buat musim tanam</a>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Nama musim</th>
                    <th>Komoditas</th>
                    <th>Tanggal mulai</th>
                    <th>Tanggal selesai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($musim_tampil as $musim): ?>
                    <tr>
                      <td data-label="Nama musim"><?= e($musim['nama_musim']) ?></td>
                      <td data-label="Komoditas"><?= e($musim['komoditas']) ?></td>
                      <td data-label="Tanggal mulai"><?= e(format_tanggal($musim['tanggal_mulai'])) ?></td>
                      <td data-label="Tanggal selesai"><?= e(format_tanggal($musim['tanggal_selesai'])) ?></td>
                      <td data-label="Status">
                        <span class="badge <?= $musim['status'] === 'berjalan' ? 'badge-info' : 'badge-muted' ?>">
                          <?= $musim['status'] === 'berjalan' ? 'Berjalan' : 'Selesai' ?>
                        </span>
                      </td>
                      <td data-label="Aksi">
                        <div class="table-actions">
                          <a class="btn btn-ghost btn-sm" href="form.php?id=<?= (int) $musim['id'] ?>">Ubah</a>
                          <a class="btn btn-ghost btn-sm" href="../erp/index.php?musim_tanam_id=<?= (int) $musim['id'] ?>">Keuangan</a>
                          <form method="post" action="" data-confirm="Hapus musim tanam ini?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="id" value="<?= (int) $musim['id'] ?>">
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
