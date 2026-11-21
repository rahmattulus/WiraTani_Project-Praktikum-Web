<?php
/* ERP petani (S14): rekap dan daftar transaksi memakai data dummy. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../dummy_musim.php';

$musim_filter = input_teks($_GET, 'musim_tanam_id');
if ($musim_filter !== '' && (!ctype_digit($musim_filter) || (int) $musim_filter < 1)) {
    $musim_filter = '';
}
$jenis_filter = input_teks($_GET, 'jenis');
if (!in_array($jenis_filter, ['', 'pendapatan', 'pengeluaran'], true)) {
    $jenis_filter = '';
}

$nama_musim = [];
foreach ($demo_musim_tanam as $musim) {
    $nama_musim[$musim['id']] = $musim['nama_musim'];
}

$data_rekap = array_filter($demo_transaksi, function ($transaksi) use ($musim_filter) {
    return $musim_filter === '' || $transaksi['musim_tanam_id'] === (int) $musim_filter;
});

$total_pendapatan = 0;
$total_pengeluaran = 0;
foreach ($data_rekap as $transaksi) {
    if ($transaksi['jenis'] === 'pendapatan') {
        $total_pendapatan += $transaksi['nominal'];
    } else {
        $total_pengeluaran += $transaksi['nominal'];
    }
}
$laba_rugi = $total_pendapatan - $total_pengeluaran;

$transaksi_tampil = array_filter($data_rekap, function ($transaksi) use ($jenis_filter) {
    return $jenis_filter === '' || $transaksi['jenis'] === $jenis_filter;
});
usort($transaksi_tampil, function ($a, $b) {
    return strcmp($b['tanggal'], $a['tanggal']);
});

$label_kategori = [
    'hasil_lelang' => 'Hasil lelang',
    'bibit' => 'Bibit',
    'pupuk' => 'Pupuk',
    'pestisida' => 'Pestisida',
    'alat' => 'Alat',
    'tenaga_kerja' => 'Tenaga kerja',
    'lainnya' => 'Lainnya',
];

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
              <p class="text-muted">
                <?= $musim_filter !== '' && isset($nama_musim[(int) $musim_filter])
                    ? e($nama_musim[(int) $musim_filter])
                    : 'Semua musim tanam' ?>
              </p>
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
              <p class="stat-value"><?= count($data_rekap) ?></p>
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
                <?php foreach ($demo_musim_tanam as $musim): ?>
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
              <a class="btn btn-ghost" href="index.php">Tampilkan semua transaksi</a>
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
                      <td data-label="Musim"><?= e($nama_musim[$transaksi['musim_tanam_id']] ?? 'Musim tidak diketahui') ?></td>
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
