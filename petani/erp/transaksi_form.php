<?php
/* Form biaya manual ERP (S14), belum menyimpan data. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../dummy_musim.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('transaksi_form.php');
    set_flash('info', 'Mode UI: biaya belum disimpan ke database.');
    redirect('index.php');
}

$judul_halaman = 'Catat Biaya';
$menu_aktif = 'erp';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <div>
              <h2 class="card-title">Biaya modal manual</h2>
              <p class="text-muted">Catat pengeluaran dan hubungkan dengan musim tanam.</p>
            </div>
            <a class="btn btn-ghost btn-sm" href="index.php">Kembali ke ERP</a>
          </div>

          <form method="post" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="jenis" value="pengeluaran">
            <div class="form-grid">
              <div class="form-group">
                <label for="musim_tanam_id">Musim tanam</label>
                <select class="select" id="musim_tanam_id" name="musim_tanam_id" required>
                  <option value="">Pilih musim tanam</option>
                  <?php foreach ($demo_musim_tanam as $musim): ?>
                    <option value="<?= (int) $musim['id'] ?>">
                      <?= e($musim['nama_musim']) ?> — <?= e($musim['komoditas']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="kategori">Kategori biaya</label>
                <select class="select" id="kategori" name="kategori" required>
                  <option value="">Pilih kategori</option>
                  <option value="bibit">Bibit</option>
                  <option value="pupuk">Pupuk</option>
                  <option value="pestisida">Pestisida</option>
                  <option value="alat">Alat</option>
                  <option value="tenaga_kerja">Tenaga kerja</option>
                  <option value="lainnya">Lainnya</option>
                </select>
              </div>
              <div class="form-group">
                <label for="nominal">Nominal (Rp)</label>
                <input class="input" type="number" id="nominal" name="nominal" min="1" step="0.01"
                       placeholder="Contoh: 250000" required>
              </div>
              <div class="form-group">
                <label for="tanggal">Tanggal transaksi</label>
                <input class="input" type="date" id="tanggal" name="tanggal" value="<?= e(date('Y-m-d')) ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label for="keterangan">Keterangan (opsional)</label>
              <textarea class="textarea" id="keterangan" name="keterangan" maxlength="255"
                        placeholder="Contoh: upah pengolahan lahan"></textarea>
            </div>
            <div class="form-actions">
              <button class="btn btn-primary" type="submit">Simpan biaya</button>
              <a class="btn btn-ghost" href="index.php">Batal</a>
            </div>
          </form>
        </section>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
