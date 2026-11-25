<?php
/* Petani: detail lelang, penetapan pemenang, dan pembatalan (S10, S17, S24). */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/lelang_helper.php';

$user_id = user_id_login();
$id = (int) input_teks($_GET, 'id');
$database_error = '';
$hasil = null;
$penawaran_hasil = [];
$musim_berjalan = [];

try {
    $hasil = ambil_hasil_panen_petani($koneksi, $id, $user_id);
    if (!$hasil) {
        set_flash('error', 'Lelang tidak ditemukan atau bukan milik Anda.');
        redirect('index.php');
    }

    $penawaran_hasil = ambil_penawaran_hasil_panen($koneksi, $id);
    $musim_berjalan = ambil_musim_tanam_berjalan($koneksi, $user_id);
} catch (mysqli_sql_exception $exception) {
    error_log('Gagal memuat detail lelang petani: ' . $exception->getMessage());
    $database_error = 'Detail lelang gagal dimuat. Silakan muat ulang halaman.';
}

$boleh_ubah = $hasil && $hasil['status'] === 'aktif' && (int) $hasil['jumlah_tawaran'] === 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $database_error === '') {
    wajib_csrf('detail.php?id=' . $id);
    $aksi = input_teks($_POST, 'aksi');
    if ($aksi === 'pilih_pemenang') {
        $penawaran_id_input = input_teks($_POST, 'id');
        $musim_tanam_id_input = input_teks($_POST, 'musim_tanam_id');
        if (!ctype_digit($penawaran_id_input) || (int) $penawaran_id_input < 1
            || !ctype_digit($musim_tanam_id_input) || (int) $musim_tanam_id_input < 1) {
            set_flash('error', 'Pilih tawaran dan musim tanam yang valid.');
            redirect('detail.php?id=' . $id);
        }

        $hasil_pilih = tetapkan_pemenang_lelang(
            $koneksi,
            $id,
            $user_id,
            (int) $penawaran_id_input,
            (int) $musim_tanam_id_input
        );
        $pesan_pilih = [
            'selected' => ['success', 'Pemenang lelang berhasil ditetapkan dan pendapatan dicatat.'],
            'not_active' => ['error', 'Lelang sudah tidak aktif atau bukan milik Anda.'],
            'invalid_offer' => ['error', 'Tawaran tidak ditemukan atau sudah tidak dapat dipilih.'],
            'invalid_season' => ['error', 'Musim tanam tidak valid atau sudah selesai.'],
            'amount_too_large' => ['error', 'Nilai pendapatan melebihi batas pencatatan keuangan.'],
            'already_recorded' => ['error', 'Transaksi hasil lelang sudah pernah dicatat. Tidak ada perubahan yang disimpan.'],
            'error' => ['error', 'Pemenang gagal ditetapkan. Tidak ada perubahan yang disimpan.'],
        ][$hasil_pilih] ?? ['error', 'Pemenang gagal ditetapkan. Tidak ada perubahan yang disimpan.'];
        set_flash($pesan_pilih[0], $pesan_pilih[1]);
        redirect('detail.php?id=' . $id);
    }

    if ($aksi === 'batalkan') {
        $hasil_batal = batalkan_lelang($koneksi, $id, $user_id);
        $pesan_batal = [
            'cancelled' => ['success', 'Lelang berhasil dibatalkan. Penawaran yang menunggu ditandai kalah.'],
            'not_active' => ['error', 'Lelang sudah tidak aktif atau bukan milik Anda.'],
            'error' => ['error', 'Lelang gagal dibatalkan. Tidak ada perubahan yang disimpan.'],
        ][$hasil_batal] ?? ['error', 'Lelang gagal dibatalkan. Tidak ada perubahan yang disimpan.'];
        set_flash($pesan_batal[0], $pesan_batal[1]);
        redirect('detail.php?id=' . $id);
    }

    set_flash('error', 'Permintaan lelang tidak valid.');
    redirect('detail.php?id=' . $id);
}

$judul_halaman = 'Detail Lelang';
$menu_aktif = 'lelang';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <?php if ($database_error !== ''): ?>
          <div class="alert alert-error" role="alert"><?= e($database_error) ?></div>
        <?php else: ?>
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

          <?php if ($hasil['deskripsi'] !== null && $hasil['deskripsi'] !== ''): ?>
            <p><?= nl2br(e($hasil['deskripsi'])) ?></p>
          <?php endif; ?>

          <div class="form-actions">
            <a class="btn btn-ghost" href="<?= BASE_URL ?>/petani/lelang/index.php">Kembali ke daftar</a>
            <?php if ($boleh_ubah): ?>
              <a class="btn btn-primary" href="<?= BASE_URL ?>/petani/lelang/form.php?id=<?= (int) $hasil['id'] ?>">Ubah lelang</a>
            <?php endif; ?>
            <?php if ($hasil['status'] === 'aktif'): ?>
              <form method="post" action=""
                    data-confirm="Batalkan lelang ini? Semua tawaran yang menunggu akan ditandai kalah.">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="batalkan">
                <button class="btn btn-danger" type="submit">Batalkan lelang</button>
              </form>
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
                    <input type="hidden" name="aksi" value="pilih_pemenang">
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
        <?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
