<?php
/* Tengkulak: pasar lelang dengan pencarian (S11). UI memakai data dummy. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('tengkulak');
require_once __DIR__ . '/../../includes/dummy_lelang.php';

$q = input_teks($_GET, 'q');
$user_id = user_id_login();
$lelang_aktif = array_values(array_filter($dummy_hasil_panen, function ($hasil) {
    return $hasil['status'] === 'aktif';
}));
$lelang_tampil = array_values(array_filter($lelang_aktif, function ($hasil) use ($q, $dummy_nama_petani) {
    $nama_petani = $dummy_nama_petani[$hasil['user_id']] ?? '';
    return $q === '' || stripos($hasil['komoditas'] . ' ' . $hasil['lokasi'] . ' ' . $nama_petani, $q) !== false;
}));
$penawaran_saya = array_values(array_filter($dummy_penawaran_lelang, function ($penawaran) use ($user_id) {
    return $penawaran['user_id'] === $user_id;
}));

$judul_halaman = 'Pasar Lelang';
$menu_aktif = 'pasar';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <div class="dash-r1">
          <article class="stat">
            <p class="stat-label">Lelang aktif</p>
            <p class="stat-value"><?= count($lelang_aktif) ?></p>
          </article>
          <article class="stat">
            <p class="stat-label">Tawaran saya</p>
            <p class="stat-value"><?= count($penawaran_saya) ?></p>
          </article>
          <article class="stat">
            <p class="stat-label">Komoditas tersedia</p>
            <p class="stat-value"><?= count(array_unique(array_column($lelang_aktif, 'komoditas'))) ?></p>
          </article>
        </div>

        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Jelajahi hasil panen</h2>
          </div>

          <form class="filter-bar" method="get" action="">
            <div class="form-group">
              <label for="q">Cari komoditas, lokasi, atau petani</label>
              <input class="input" type="search" id="q" name="q" value="<?= e($q) ?>"
                     placeholder="Padi, Klaten, Budi Santoso">
            </div>
            <button class="btn btn-ghost" type="submit">Cari lelang</button>
          </form>

          <?php if (!$lelang_tampil): ?>
            <div class="empty-state">
              <p><?= $q !== '' ? 'Tidak ada lelang yang cocok dengan pencarian Anda.' : 'Belum ada lelang aktif saat ini.' ?></p>
              <?php if ($q !== ''): ?>
                <a class="btn btn-ghost" href="<?= BASE_URL ?>/tengkulak/pasar/index.php">Hapus pencarian</a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Komoditas / Petani</th>
                    <th>Tanggal panen</th>
                    <th>Estimasi</th>
                    <th>Harga dasar</th>
                    <th>Tawaran tertinggi</th>
                    <th>Jumlah tawaran</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lelang_tampil as $hasil): ?>
                    <?php
                    $tawaran_hasil = array_values(array_filter($dummy_penawaran_lelang, function ($penawaran) use ($hasil) {
                        return $penawaran['hasil_panen_id'] === $hasil['id'];
                    }));
                    usort($tawaran_hasil, function ($a, $b) {
                        return $b['harga_tawar'] <=> $a['harga_tawar'];
                    });
                    $tawaran_tertinggi = $tawaran_hasil[0] ?? null;
                    ?>
                    <tr>
                      <td data-label="Komoditas / Petani">
                        <strong><?= e($hasil['komoditas']) ?></strong><br>
                        <small><?= e($dummy_nama_petani[$hasil['user_id']] ?? 'Petani') ?> · <?= e($hasil['lokasi']) ?></small>
                      </td>
                      <td data-label="Tanggal panen"><?= e(format_tanggal($hasil['tanggal_panen'])) ?></td>
                      <td data-label="Estimasi"><?= e(number_format((float) $hasil['estimasi_tonase'], 0, ',', '.')) ?> kg</td>
                      <td data-label="Harga dasar"><?= e(format_rupiah($hasil['harga_dasar'])) ?>/kg</td>
                      <td data-label="Tawaran tertinggi">
                        <?= $tawaran_tertinggi ? e(format_rupiah($tawaran_tertinggi['harga_tawar'])) . '/kg' : '<span class="text-muted">Belum ada</span>' ?>
                      </td>
                      <td data-label="Jumlah tawaran"><?= count($tawaran_hasil) ?></td>
                      <td data-label="Aksi">
                        <a class="btn btn-primary btn-sm"
                           href="<?= BASE_URL ?>/tengkulak/pasar/detail.php?id=<?= (int) $hasil['id'] ?>">Lihat detail</a>
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
