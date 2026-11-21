<?php
/* Form tambah/ubah lowongan petani (S15), belum menyimpan data. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../dummy_musim.php';

$id_input = input_teks($_GET, 'id');
$lowongan_edit = null;
if ($id_input !== '') {
    if (!ctype_digit($id_input)) {
        set_flash('error', 'Lowongan tidak ditemukan.');
        redirect('index.php');
    }
    foreach ($demo_lowongan as $lowongan) {
        if ($lowongan['id'] === (int) $id_input && $lowongan['user_id'] === $demo_user_id) {
            $lowongan_edit = $lowongan;
            break;
        }
    }
    if ($lowongan_edit === null) {
        set_flash('error', 'Lowongan tidak ditemukan.');
        redirect('index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('form.php' . ($lowongan_edit ? '?id=' . (int) $lowongan_edit['id'] : ''));
    set_flash('info', 'Mode UI: lowongan belum disimpan ke database.');
    redirect('index.php');
}

$judul_halaman = $lowongan_edit ? 'Ubah Lowongan' : 'Buat Lowongan';
$menu_aktif = 'lowongan';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <div>
              <h2 class="card-title">Informasi pekerjaan</h2>
              <p class="text-muted">Cantumkan detail agar calon buruh memahami pekerjaan dan upahnya.</p>
            </div>
            <a class="btn btn-ghost btn-sm" href="index.php">Kembali</a>
          </div>

          <form method="post" action="">
            <?= csrf_field() ?>
            <div class="form-grid">
              <div class="form-group">
                <label for="judul">Judul lowongan</label>
                <input class="input" type="text" id="judul" name="judul" maxlength="150"
                       value="<?= e($lowongan_edit['judul'] ?? '') ?>" placeholder="Contoh: Buruh tanam padi" required>
              </div>
              <div class="form-group">
                <label for="jenis_pekerjaan">Jenis pekerjaan</label>
                <select class="select" id="jenis_pekerjaan" name="jenis_pekerjaan" required>
                  <option value="">Pilih jenis pekerjaan</option>
                  <option value="tanam" <?= ($lowongan_edit['jenis_pekerjaan'] ?? '') === 'tanam' ? 'selected' : '' ?>>Tanam</option>
                  <option value="panen" <?= ($lowongan_edit['jenis_pekerjaan'] ?? '') === 'panen' ? 'selected' : '' ?>>Panen</option>
                  <option value="lainnya" <?= ($lowongan_edit['jenis_pekerjaan'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
              </div>
              <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input class="input" type="text" id="lokasi" name="lokasi" maxlength="150"
                       value="<?= e($lowongan_edit['lokasi'] ?? '') ?>" placeholder="Nama desa/kecamatan" required>
              </div>
              <div class="form-group">
                <label for="jumlah_buruh">Jumlah buruh dibutuhkan</label>
                <input class="input" type="number" id="jumlah_buruh" name="jumlah_buruh" min="1" step="1"
                       value="<?= e($lowongan_edit['jumlah_buruh'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label for="upah_harian">Upah harian (Rp)</label>
                <input class="input" type="number" id="upah_harian" name="upah_harian" min="1" step="0.01"
                       value="<?= e($lowongan_edit['upah_harian'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label for="tanggal_mulai">Tanggal mulai</label>
                <input class="input" type="date" id="tanggal_mulai" name="tanggal_mulai"
                       value="<?= e($lowongan_edit['tanggal_mulai'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label for="durasi_hari">Durasi (hari)</label>
                <input class="input" type="number" id="durasi_hari" name="durasi_hari" min="1" step="1"
                       value="<?= e($lowongan_edit['durasi_hari'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label for="status">Status</label>
                <select class="select" id="status" name="status" required>
                  <option value="buka" <?= ($lowongan_edit['status'] ?? 'buka') === 'buka' ? 'selected' : '' ?>>Buka</option>
                  <option value="ditutup" <?= ($lowongan_edit['status'] ?? '') === 'ditutup' ? 'selected' : '' ?>>Ditutup</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi pekerjaan</label>
              <textarea class="textarea" id="deskripsi" name="deskripsi"
                        placeholder="Jelaskan tugas, kebutuhan, atau informasi tambahan"><?= e($lowongan_edit['deskripsi'] ?? '') ?></textarea>
            </div>
            <div class="form-actions">
              <button class="btn btn-primary" type="submit"><?= $lowongan_edit ? 'Simpan perubahan' : 'Terbitkan lowongan' ?></button>
              <a class="btn btn-ghost" href="index.php">Batal</a>
            </div>
          </form>
        </section>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
