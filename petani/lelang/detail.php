<?php
/* Petani: detail lelang, daftar tawaran, dan UI pilih pemenang (S10). */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../includes/dummy_lelang.php';

$id = (int) input_teks($_GET, 'id');
$hasil = null;
foreach ($dummy_hasil_panen as $item) {
    if ($item['id'] === $id && $item['user_id'] === $dummy_lelang_petani_id) {
        $hasil = $item;
        break;
    }
}

if (!$hasil) {
    set_flash('error', 'Data lelang tidak ditemukan.');
    redirect('index.php');
}

$penawaran_hasil = array_values(array_filter($dummy_penawaran_lelang, function ($penawaran) use ($id) {
    return $penawaran['hasil_panen_id'] === $id;
}));
usort($penawaran_hasil, function ($a, $b) {
    return $b['harga_tawar'] <=> $a['harga_tawar'];
});
$musim_berjalan = array_values(array_filter($dummy_musim_tanam_lelang, function ($musim) use ($dummy_lelang_petani_id) {
    return $musim['user_id'] === $dummy_lelang_petani_id && $musim['status'] === 'berjalan';
}));
$boleh_ubah = $hasil['status'] === 'aktif' && count($penawaran_hasil) === 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('detail.php?id=' . $id);
    // TODO S17: validasi kepemilikan tawaran dan musim, lalu tetapkan pemenang.
    set_flash('info', 'Penetapan pemenang belum tersambung ke database (mode tampilan dengan data dummy).');
    redirect('detail.php?id=' . $id);
}

$judul_halaman = 'Detail Lelang';
$menu_aktif = 'lelang';
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

          <div class="form-grid">
            <div class="form-group">
              <span class="text-muted">Estimasi panen</span>
              <strong><?= e(number_format((float) $hasil['estimasi_tonase'], 0, ',', '.')) ?> kg</strong>
            </div>
            <div class="form-group">
              <span class="text-muted">Tanggal panen</span>
              <strong><?= e(format_tanggal($hasil['tanggal_panen'])) ?></strong>
            </div>
            <div class="form-group">
              <span class="text-muted">Lokasi</span>
              <strong><?= e($hasil['lokasi']) ?></strong>
            </div>
            <div class="form-group">
              <span class="text-muted">Harga dasar</span>
              <strong><?= e(format_rupiah($hasil['harga_dasar'])) ?>/kg</strong>
            </div>
          </div>

          <?php if ($hasil['deskripsi'] !== ''): ?>
            <p><?= nl2br(e($hasil['deskripsi'])) ?></p>
          <?php endif; ?>

          <div class="form-actions">
            <a class="btn btn-ghost" href="<?= BASE_URL ?>/petani/lelang/index.php">Kembali ke daftar</a>
            <?php if ($boleh_ubah): ?>
              <a class="btn btn-primary" href="<?= BASE_URL ?>/petani/lelang/form.php?id=<?= (int) $hasil['id'] ?>">Ubah lelang</a>
            <?php endif; ?>
          </div>
        </section>

        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Daftar tawaran</h2>
            <span class="text-muted"><?= count($penawaran_hasil) ?> tawaran</span>
          </div>

          <?php if (!$penawaran_hasil): ?>
            <div class="empty-state">
              <p>Belum ada tengkulak yang mengirim tawaran untuk hasil panen ini.</p>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Tengkulak</th>
                    <th>Harga tawar</th>
                    <th>Pembayaran</th>
                    <th>Waktu</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($penawaran_hasil as $penawaran): ?>
                    <tr>
                      <td data-label="Tengkulak"><?= e($penawaran['nama_tengkulak']) ?></td>
                      <td data-label="Harga tawar"><strong><?= e(format_rupiah($penawaran['harga_tawar'])) ?>/kg</strong></td>
                      <td data-label="Pembayaran"><?= e($penawaran['metode_pembayaran']) ?></td>
                      <td data-label="Waktu"><?= e(format_tanggal(substr($penawaran['created_at'], 0, 10))) ?></td>
                      <td data-label="Status">
                        <div class="table-status-action">
                          <span class="badge <?= e(kelas_status_penawaran($penawaran['status'])) ?>">
                            <?= e(label_status_penawaran($penawaran['status'])) ?>
                          </span>
                          <button
                            class="btn btn-ghost btn-sm"
                            type="button"
                            data-offer-detail
                            data-offer-id="<?= (int) $penawaran['id'] ?>"
                            data-offer-name="<?= e($penawaran['nama_tengkulak']) ?>"
                            data-offer-price="<?= e(format_rupiah($penawaran['harga_tawar'])) ?>/kg"
                            data-offer-payment="<?= e($penawaran['metode_pembayaran']) ?>"
                            data-offer-time="<?= e(format_tanggal(substr($penawaran['created_at'], 0, 10))) ?>"
                            data-offer-status="<?= e(label_status_penawaran($penawaran['status'])) ?>"
                            data-offer-status-class="<?= e(kelas_status_penawaran($penawaran['status'])) ?>"
                            data-offer-armada="<?= e($penawaran['armada_angkut']) ?>"
                            data-offer-catatan="<?= e($penawaran['catatan'] !== '' ? $penawaran['catatan'] : 'Tidak ada catatan.') ?>"
                            data-can-select="<?= $hasil['status'] === 'aktif' && $penawaran['status'] === 'menunggu' && $musim_berjalan ? '1' : '0' ?>">
                            Detail
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </section>

        <?php if ($penawaran_hasil): ?>
          <dialog class="offer-modal" data-offer-dialog aria-labelledby="offer-modal-title">
            <div class="offer-modal-content">
              <div class="offer-modal-header">
                <h2 id="offer-modal-title">Detail tawaran</h2>
                <button class="btn btn-ghost btn-sm" type="button" data-modal-close>Tutup</button>
              </div>

              <div class="offer-modal-summary">
                <strong data-modal-name></strong>
                <span class="badge" data-modal-status></span>
              </div>

              <dl class="offer-modal-details">
                <div>
                  <dt>Harga tawar</dt>
                  <dd data-modal-price></dd>
                </div>
                <div>
                  <dt>Metode pembayaran</dt>
                  <dd data-modal-payment></dd>
                </div>
                <div>
                  <dt>Waktu penawaran</dt>
                  <dd data-modal-time></dd>
                </div>
                <div>
                  <dt>Armada angkut</dt>
                  <dd data-modal-armada></dd>
                </div>
                <div class="offer-modal-note">
                  <dt>Catatan tengkulak</dt>
                  <dd data-modal-catatan></dd>
                </div>
              </dl>

              <?php if ($hasil['status'] === 'aktif'): ?>
                <?php if (!$musim_berjalan): ?>
                  <div class="alert alert-info" role="status">
                    Belum ada musim tanam yang berjalan. Buat musim tanam sebelum menetapkan pemenang.
                  </div>
                <?php else: ?>
                  <p class="text-muted" data-modal-unavailable hidden>
                    Tawaran ini tidak lagi dapat dipilih sebagai pemenang.
                  </p>
                  <form class="offer-winner-form" method="post" action="" data-winner-form
                        data-confirm="Tetapkan tawaran ini sebagai pemenang lelang?" hidden>
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="">
                    <div class="form-group">
                      <label for="modal_musim_tanam_id">Catat hasil lelang ke musim tanam</label>
                      <select class="select" id="modal_musim_tanam_id" name="musim_tanam_id" required>
                        <option value="">Pilih musim tanam</option>
                        <?php foreach ($musim_berjalan as $musim): ?>
                          <option value="<?= (int) $musim['id'] ?>">
                            <?= e($musim['nama_musim']) ?> (<?= e($musim['komoditas']) ?>)
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Konfirmasi pemenang lelang</button>
                  </form>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </dialog>
        <?php endif; ?>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
