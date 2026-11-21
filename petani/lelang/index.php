<?php
/* Petani: daftar lelang panen (S10). UI memakai data dummy sampai Fase 3. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../includes/dummy_lelang.php';

$q = input_teks($_GET, 'q');
$status = input_teks($_GET, 'status');
if (!in_array($status, ['', 'aktif', 'selesai', 'dibatalkan'], true)) {
    $status = '';
}

$lelang_petani = array_values(array_filter($dummy_hasil_panen, function ($hasil) use ($dummy_lelang_petani_id) {
    return $hasil['user_id'] === $dummy_lelang_petani_id;
}));
$lelang_tampil = array_values(array_filter($lelang_petani, function ($hasil) use ($q, $status) {
    $cocok_pencarian = $q === '' || stripos($hasil['komoditas'] . ' ' . $hasil['lokasi'], $q) !== false;
    $cocok_status = $status === '' || $hasil['status'] === $status;
    return $cocok_pencarian && $cocok_status;
}));
$jumlah_aktif = count(array_filter($lelang_petani, function ($hasil) {
    return $hasil['status'] === 'aktif';
}));
$jumlah_tawaran = count(array_filter($dummy_penawaran_lelang, function ($penawaran) use ($lelang_petani) {
    foreach ($lelang_petani as $hasil) {
        if ($penawaran['hasil_panen_id'] === $hasil['id'] && $penawaran['status'] === 'menunggu') {
            return true;
        }
    }
    return false;
}));
$jumlah_selesai = count(array_filter($lelang_petani, function ($hasil) {
    return $hasil['status'] === 'selesai';
}));
$jumlah_tawaran_per_lelang = [];
foreach ($dummy_penawaran_lelang as $penawaran) {
    $id_hasil = $penawaran['hasil_panen_id'];
    $jumlah_tawaran_per_lelang[$id_hasil] = ($jumlah_tawaran_per_lelang[$id_hasil] ?? 0) + 1;
}

$judul_halaman = 'Lelang Panen';
$menu_aktif = 'lelang';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <div class="dash-r1">
          <article class="stat">
            <p class="stat-label">Lelang aktif</p>
            <p class="stat-value"><?= (int) $jumlah_aktif ?></p>
          </article>
          <article class="stat">
            <p class="stat-label">Tawaran menunggu</p>
            <p class="stat-value"><?= (int) $jumlah_tawaran ?></p>
          </article>
          <article class="stat">
            <p class="stat-label">Lelang selesai</p>
            <p class="stat-value"><?= (int) $jumlah_selesai ?></p>
          </article>
        </div>

        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Daftar hasil panen</h2>
            <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/petani/lelang/form.php">Tambah lelang</a>
          </div>

          <form class="filter-bar" method="get" action="">
            <div class="form-group">
              <label for="q">Cari komoditas atau lokasi</label>
              <input class="input" type="search" id="q" name="q" value="<?= e($q) ?>"
                     placeholder="Padi, Klaten">
            </div>
            <div class="form-group">
              <label for="status">Status</label>
              <select class="select" id="status" name="status">
                <option value="">Semua status</option>
                <?php foreach (['aktif' => 'Aktif', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $nilai => $label): ?>
                  <option value="<?= e($nilai) ?>"<?= $status === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button class="btn btn-ghost" type="submit">Terapkan</button>
          </form>

          <?php if (!$lelang_tampil): ?>
            <div class="empty-state">
              <p>Belum ada lelang yang sesuai dengan pencarian.</p>
              <?php if ($q !== '' || $status !== ''): ?>
                <a class="btn btn-ghost" href="<?= BASE_URL ?>/petani/lelang/index.php">Hapus filter</a>
              <?php else: ?>
                <a class="btn btn-primary" href="<?= BASE_URL ?>/petani/lelang/form.php">Buat lelang pertama</a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Komoditas</th>
                    <th>Panen</th>
                    <th>Estimasi</th>
                    <th>Harga dasar</th>
                    <th>Tawaran</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lelang_tampil as $hasil): ?>
                    <?php
                    $jumlah_tawaran_hasil = $jumlah_tawaran_per_lelang[$hasil['id']] ?? 0;
                    $boleh_ubah = $hasil['status'] === 'aktif' && $jumlah_tawaran_hasil === 0;
                    ?>
                    <tr>
                      <td data-label="Komoditas">
                        <strong><?= e($hasil['komoditas']) ?></strong><br>
                        <small><?= e($hasil['lokasi']) ?></small>
                      </td>
                      <td data-label="Panen"><?= e(format_tanggal($hasil['tanggal_panen'])) ?></td>
                      <td data-label="Estimasi"><?= e(number_format((float) $hasil['estimasi_tonase'], 0, ',', '.')) ?> kg</td>
                      <td data-label="Harga dasar"><?= e(format_rupiah($hasil['harga_dasar'])) ?>/kg</td>
                      <td data-label="Tawaran"><?= (int) $jumlah_tawaran_hasil ?></td>
                      <td data-label="Status">
                        <span class="badge <?= e(kelas_status_lelang($hasil['status'])) ?>">
                          <?= e(label_status_lelang($hasil['status'])) ?>
                        </span>
                      </td>
                      <td data-label="Aksi">
                        <div class="table-actions">
                          <a class="btn btn-ghost btn-sm"
                             href="<?= BASE_URL ?>/petani/lelang/detail.php?id=<?= (int) $hasil['id'] ?>">Detail</a>
                          <?php if ($boleh_ubah): ?>
                            <a class="btn btn-ghost btn-sm"
                               href="<?= BASE_URL ?>/petani/lelang/form.php?id=<?= (int) $hasil['id'] ?>">Ubah</a>
                          <?php endif; ?>
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
