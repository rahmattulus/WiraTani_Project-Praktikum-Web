<?php
/* Rekap dan daftar transaksi ERP per musim tanam. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../config/koneksi.php';

$user_id = user_id_login();
$musim_filter = input_teks($_GET, 'musim_tanam_id');
if ($musim_filter !== '' && (!ctype_digit($musim_filter) || (int) $musim_filter < 1)) {
    set_flash('error', 'Filter musim tanam tidak valid.');
    redirect('index.php');
}
$jenis_filter = input_teks($_GET, 'jenis');
if (!in_array($jenis_filter, ['', 'pendapatan', 'pengeluaran'], true)) {
    $jenis_filter = '';
}

$stmt = $koneksi->prepare(
    'SELECT id, nama_musim, komoditas, status
     FROM musim_tanam WHERE user_id = ? ORDER BY tanggal_mulai DESC, id DESC'
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$musim_tanam = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$nama_musim = [];
foreach ($musim_tanam as $musim) {
    $nama_musim[(int) $musim['id']] = $musim['nama_musim'];
}

$filter_id = $musim_filter === '' ? 0 : (int) $musim_filter;
if ($filter_id > 0 && !isset($nama_musim[$filter_id])) {
    set_flash('error', 'Musim tanam tidak ditemukan atau bukan milik Anda.');
    redirect('index.php');
}

$stmt = $koneksi->prepare(
    "SELECT
        COALESCE(SUM(CASE WHEN t.jenis = 'pendapatan' THEN t.nominal ELSE 0 END), 0) AS total_pendapatan,
        COALESCE(SUM(CASE WHEN t.jenis = 'pengeluaran' THEN t.nominal ELSE 0 END), 0) AS total_pengeluaran,
        COUNT(t.id) AS jumlah_transaksi
     FROM musim_tanam m
     LEFT JOIN transaksi_keuangan t ON t.musim_tanam_id = m.id
     WHERE m.user_id = ? AND (? = 0 OR t.musim_tanam_id = ? OR t.id IS NULL)"
);
$stmt->bind_param('iii', $user_id, $filter_id, $filter_id);
$stmt->execute();
$rekap = $stmt->get_result()->fetch_assoc();
$total_pendapatan = (float) $rekap['total_pendapatan'];
$total_pengeluaran = (float) $rekap['total_pengeluaran'];
$jumlah_transaksi = (int) $rekap['jumlah_transaksi'];
$laba_rugi = $total_pendapatan - $total_pengeluaran;

$stmt = $koneksi->prepare(
    "SELECT t.id, t.musim_tanam_id, t.jenis, t.kategori, t.nominal, t.tanggal, t.keterangan
     FROM transaksi_keuangan t
     INNER JOIN musim_tanam m ON m.id = t.musim_tanam_id
     WHERE m.user_id = ?
       AND (? = 0 OR t.musim_tanam_id = ?)
       AND (? = '' OR t.jenis = ?)
     ORDER BY t.tanggal DESC, t.id DESC"
);
$stmt->bind_param('iiiss', $user_id, $filter_id, $filter_id, $jenis_filter, $jenis_filter);
$stmt->execute();
$transaksi_tampil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$label_kategori = [
    'hasil_lelang' => 'Hasil lelang',
    'bibit' => 'Bibit',
    'pupuk' => 'Pupuk',
    'pestisida' => 'Pestisida',
    'alat' => 'Alat',
    'tenaga_kerja' => 'Tenaga kerja',
    'lainnya' => 'Lainnya',
];

$judul_ringkasan = $filter_id > 0 ? $nama_musim[$filter_id] : 'Semua musim tanam';
$judul_halaman = 'ERP Keuangan';
$menu_aktif = 'erp';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <div>
              <h2 class="card-title">Ringkasan keuangan</h2>
              <p class="text-muted"><?= e($judul_ringkasan) ?></p>
            </div>
            <a class="btn btn-primary" href="transaksi_form.php">+ Catat biaya</a>
          </div>

          <div class="stat-grid">
            <article class="stat">
              <p class="stat-label">Total pendapatan</p>
              <p class="stat-value"><?= e(format_rupiah($total_pendapatan)) ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Total biaya</p>
              <p class="stat-value"><?= e(format_rupiah($total_pengeluaran)) ?></p>
            </article>
            <article class="stat">
              <p class="stat-label">Laba / rugi bersih</p>
              <p class="stat-value <?= $laba_rugi >= 0 ? 'stat-up' : 'stat-down' ?>">
                <?= $laba_rugi < 0 ? '-' : '' ?><?= e(format_rupiah(abs($laba_rugi))) ?>
              </p>
            </article>
            <article class="stat">
              <p class="stat-label">Jumlah transaksi</p>
              <p class="stat-value"><?= $jumlah_transaksi ?></p>
            </article>
          </div>
        </section>

        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Transaksi keuangan</h2>
            <a class="card-link" href="../musim/index.php">Kelola musim tanam</a>
          </div>

          <form class="filter-bar" method="get" action="">
            <div class="form-group">
              <label for="musim_tanam_id">Musim tanam</label>
              <select class="select" id="musim_tanam_id" name="musim_tanam_id">
                <option value="">Semua musim</option>
                <?php foreach ($musim_tanam as $musim): ?>
                  <option value="<?= (int) $musim['id'] ?>"
                    <?= $musim_filter === (string) $musim['id'] ? 'selected' : '' ?>>
                    <?= e($musim['nama_musim']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="jenis">Jenis transaksi</label>
              <select class="select" id="jenis" name="jenis">
                <option value="">Semua jenis</option>
                <option value="pendapatan" <?= $jenis_filter === 'pendapatan' ? 'selected' : '' ?>>Pendapatan</option>
                <option value="pengeluaran" <?= $jenis_filter === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
              </select>
            </div>
            <button class="btn btn-ghost" type="submit">Terapkan filter</button>
          </form>

          <?php if (!$transaksi_tampil): ?>
            <div class="empty-state">
              <p>Belum ada transaksi untuk filter ini.</p>
              <?php if (!$musim_tanam): ?>
                <a class="btn btn-primary" href="../musim/form.php">Buat musim tanam terlebih dahulu</a>
              <?php else: ?>
                <a class="btn btn-ghost" href="index.php">Tampilkan semua transaksi</a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Tanggal</th>
                    <th>Musim</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th>Nominal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($transaksi_tampil as $transaksi): ?>
                    <tr>
                      <td data-label="Tanggal"><?= e(format_tanggal($transaksi['tanggal'])) ?></td>
                      <td data-label="Musim"><?= e($nama_musim[(int) $transaksi['musim_tanam_id']] ?? 'Musim tidak diketahui') ?></td>
                      <td data-label="Jenis">
                        <span class="badge <?= $transaksi['jenis'] === 'pendapatan' ? 'badge-success' : 'badge-warning' ?>">
                          <?= $transaksi['jenis'] === 'pendapatan' ? 'Pendapatan' : 'Pengeluaran' ?>
                        </span>
                      </td>
                      <td data-label="Kategori"><?= e($label_kategori[$transaksi['kategori']] ?? 'Lainnya') ?></td>
                      <td data-label="Keterangan"><?= e($transaksi['keterangan'] ?: '-') ?></td>
                      <td data-label="Nominal"><?= e(format_rupiah($transaksi['nominal'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </section>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
