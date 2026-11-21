<?php
/* Tengkulak: form penawaran lelang (S11). UI memakai data dummy sampai Fase 3. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('tengkulak');
require_once __DIR__ . '/../../includes/dummy_lelang.php';

$hasil_panen_id = (int) input_teks($_GET, 'hasil_panen_id');
$hasil = null;
foreach ($dummy_hasil_panen as $item) {
    if ($item['id'] === $hasil_panen_id && $item['status'] === 'aktif') {
        $hasil = $item;
        break;
    }
}

if (!$hasil) {
    set_flash('error', 'Lelang aktif tidak ditemukan.');
    redirect('index.php');
}

$tawaran_hasil = array_values(array_filter($dummy_penawaran_lelang, function ($penawaran) use ($hasil_panen_id) {
    return $penawaran['hasil_panen_id'] === $hasil_panen_id;
}));
$tawaran_tertinggi = null;
$tawaran_saya = [];
foreach ($tawaran_hasil as $penawaran) {
    if ($tawaran_tertinggi === null || $penawaran['harga_tawar'] > $tawaran_tertinggi['harga_tawar']) {
        $tawaran_tertinggi = $penawaran;
    }
    if ($penawaran['user_id'] === user_id_login()) {
        $tawaran_saya[] = $penawaran;
    }
}
usort($tawaran_saya, function ($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});

$harga_minimum = (float) $hasil['harga_dasar'];
if ($tawaran_saya) {
    $harga_minimum = max($harga_minimum, (float) $tawaran_saya[0]['harga_tawar'] + 0.01);
}
$harga_saran = max(
    $harga_minimum,
    $tawaran_tertinggi ? (float) $tawaran_tertinggi['harga_tawar'] + 100 : (float) $hasil['harga_dasar']
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('penawaran.php?hasil_panen_id=' . $hasil_panen_id);
    // TODO S20: validasi harga, status lelang, dan tawaran sebelumnya sebelum INSERT.
    set_flash('info', 'Penawaran belum tersambung ke database (mode tampilan dengan data dummy).');
    redirect('detail.php?id=' . $hasil_panen_id);
}

$judul_halaman = 'Buat Penawaran';
$menu_aktif = 'pasar';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <h2 class="card-title"><?= e($hasil['komoditas']) ?></h2>
            <span class="badge <?= e(kelas_status_lelang($hasil['status'])) ?>">
              <?= e(label_status_lelang($hasil['status'])) ?>
            </span>
          </div>
          <p>
            <?= e($dummy_nama_petani[$hasil['user_id']] ?? 'Petani') ?> · <?= e($hasil['lokasi']) ?><br>
            Panen <?= e(format_tanggal($hasil['tanggal_panen'])) ?> ·
            <?= e(number_format((float) $hasil['estimasi_tonase'], 0, ',', '.')) ?> kg
          </p>
          <div class="form-grid">
            <div class="form-group">
              <span class="text-muted">Harga dasar</span>
              <strong><?= e(format_rupiah($hasil['harga_dasar'])) ?>/kg</strong>
            </div>
            <div class="form-group">
              <span class="text-muted">Tawaran tertinggi saat ini</span>
              <strong><?= $tawaran_tertinggi ? e(format_rupiah($tawaran_tertinggi['harga_tawar'])) . '/kg' : 'Belum ada' ?></strong>
            </div>
            <?php if ($tawaran_saya): ?>
              <div class="form-group">
                <span class="text-muted">Tawaran terakhir saya</span>
                <strong><?= e(format_rupiah($tawaran_saya[0]['harga_tawar'])) ?>/kg</strong>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Detail penawaran</h2>
          </div>
          <p class="text-muted">
            Tawaran harus minimal sebesar harga dasar dan lebih tinggi dari tawaran Anda sebelumnya.
          </p>

          <form method="post" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="hasil_panen_id" value="<?= (int) $hasil['id'] ?>">

            <div class="form-grid">
              <div class="form-group">
                <label for="harga_tawar">Harga tawar (Rp/kg)</label>
                <input class="input" type="number" id="harga_tawar" name="harga_tawar"
                       min="<?= e(number_format($harga_minimum, 2, '.', '')) ?>"
                       step="0.01" value="<?= e(number_format($harga_saran, 2, '.', '')) ?>" required>
              </div>

              <div class="form-group">
                <label for="metode_pembayaran">Metode pembayaran</label>
                <input class="input" type="text" id="metode_pembayaran" name="metode_pembayaran"
                       maxlength="50" placeholder="Tunai saat pengambilan" required>
              </div>

              <div class="form-group">
                <label for="armada_angkut">Armada angkut</label>
                <input class="input" type="text" id="armada_angkut" name="armada_angkut"
                       maxlength="100" placeholder="Truk engkel" required>
              </div>
            </div>

            <div class="form-group">
              <label for="catatan">Catatan untuk petani (opsional)</label>
              <textarea class="textarea" id="catatan" name="catatan" maxlength="2000"
                        placeholder="Jadwal pengambilan atau informasi tambahan."></textarea>
            </div>

            <div class="form-actions">
              <button class="btn btn-primary" type="submit">Kirim tawaran</button>
              <a class="btn btn-ghost"
                 href="<?= BASE_URL ?>/tengkulak/pasar/detail.php?id=<?= (int) $hasil['id'] ?>">Batal</a>
            </div>
          </form>
        </section>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
