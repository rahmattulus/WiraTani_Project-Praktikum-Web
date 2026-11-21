<?php
/* Form tambah/ubah musim tanam (S14), belum menyimpan data. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../dummy_musim.php';

$id_input = input_teks($_GET, 'id');
$musim_edit = null;
if ($id_input !== '') {
    if (!ctype_digit($id_input)) {
        set_flash('error', 'Musim tanam tidak ditemukan.');
        redirect('index.php');
    }
    foreach ($demo_musim_tanam as $musim) {
        if ($musim['id'] === (int) $id_input && $musim['user_id'] === $demo_user_id) {
            $musim_edit = $musim;
            break;
        }
    }
    if ($musim_edit === null) {
        set_flash('error', 'Musim tanam tidak ditemukan.');
        redirect('index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('form.php' . ($musim_edit ? '?id=' . (int) $musim_edit['id'] : ''));
    set_flash('info', 'Mode UI: data musim tanam belum disimpan ke database.');
    redirect('index.php');
}

$judul_halaman = $musim_edit ? 'Ubah Musim Tanam' : 'Tambah Musim Tanam';
$menu_aktif = 'musim';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
      <div class="dash">
        <section class="card">
          <div class="card-head">
            <h2 class="card-title">Informasi musim tanam</h2>
            <a class="btn btn-ghost btn-sm" href="index.php">Kembali</a>
          </div>

          <form method="post" action="">
            <?= csrf_field() ?>
            <div class="form-grid">
              <div class="form-group">
                <label for="nama_musim">Nama musim</label>
                <input class="input" type="text" id="nama_musim" name="nama_musim" maxlength="100"
                       value="<?= e($musim_edit['nama_musim'] ?? '') ?>" placeholder="Contoh: Musim Tanam 1 2026" required>
              </div>
              <div class="form-group">
                <label for="komoditas">Komoditas</label>
                <input class="input" type="text" id="komoditas" name="komoditas" maxlength="100"
                       value="<?= e($musim_edit['komoditas'] ?? '') ?>" placeholder="Padi, jagung, atau komoditas lain" required>
              </div>
              <div class="form-group">
                <label for="tanggal_mulai">Tanggal mulai</label>
                <input class="input" type="date" id="tanggal_mulai" name="tanggal_mulai"
                       value="<?= e($musim_edit['tanggal_mulai'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label for="tanggal_selesai">Tanggal selesai (opsional)</label>
                <input class="input" type="date" id="tanggal_selesai" name="tanggal_selesai"
                       value="<?= e($musim_edit['tanggal_selesai'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="status">Status</label>
                <select class="select" id="status" name="status" required>
                  <option value="berjalan" <?= ($musim_edit['status'] ?? 'berjalan') === 'berjalan' ? 'selected' : '' ?>>Berjalan</option>
                  <option value="selesai" <?= ($musim_edit['status'] ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
              </div>
            </div>
            <div class="form-actions">
              <button class="btn btn-primary" type="submit"><?= $musim_edit ? 'Simpan perubahan' : 'Buat musim tanam' ?></button>
              <a class="btn btn-ghost" href="index.php">Batal</a>
            </div>
          </form>
        </section>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
