<?php
/* Daftar musim tanam petani (S14), menggunakan data dummy. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../dummy_musim.php';

$status_filter = input_teks($_GET, 'status');
if (!in_array($status_filter, ['', 'berjalan', 'selesai'], true)) {
    $status_filter = '';
}

$musim_tampil = array_filter($demo_musim_tanam, function ($musim) use ($status_filter, $demo_user_id) {
    return $musim['user_id'] === $demo_user_id
        && ($status_filter === '' || $musim['status'] === $status_filter);
});

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
