<?php
/* Form tambah/ubah lowongan yang hanya dapat dikelola pemiliknya. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../config/koneksi.php';

$user_id = user_id_login();
$id_input = input_teks($_GET, 'id');
$lowongan_edit = null;
if ($id_input !== '') {
    if (!ctype_digit($id_input) || (int) $id_input < 1) {
        set_flash('error', 'Lowongan tidak ditemukan.');
        redirect('index.php');
    }

    $id = (int) $id_input;
    $stmt = $koneksi->prepare(
        'SELECT id, judul, jenis_pekerjaan, lokasi, jumlah_buruh, upah_harian,
                tanggal_mulai, durasi_hari, deskripsi, status
         FROM lowongan_kerja WHERE id = ? AND user_id = ?'
    );
    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    $lowongan_edit = $stmt->get_result()->fetch_assoc();
    if (!$lowongan_edit) {
        set_flash('error', 'Lowongan tidak ditemukan atau bukan milik Anda.');
        redirect('index.php');
    }
}

$nilai = $lowongan_edit ?: [
    'judul' => '',
    'jenis_pekerjaan' => '',
    'lokasi' => '',
    'jumlah_buruh' => '',
    'upah_harian' => '',
    'tanggal_mulai' => '',
    'durasi_hari' => '',
    'deskripsi' => '',
    'status' => 'buka',
];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    wajib_csrf('form.php' . ($lowongan_edit ? '?id=' . (int) $lowongan_edit['id'] : ''));
    $nilai = [
        'judul' => input_teks($_POST, 'judul'),
        'jenis_pekerjaan' => input_teks($_POST, 'jenis_pekerjaan'),
        'lokasi' => input_teks($_POST, 'lokasi'),
        'jumlah_buruh' => input_teks($_POST, 'jumlah_buruh'),
        'upah_harian' => input_teks($_POST, 'upah_harian'),
        'tanggal_mulai' => input_teks($_POST, 'tanggal_mulai'),
        'durasi_hari' => input_teks($_POST, 'durasi_hari'),
        'deskripsi' => input_teks($_POST, 'deskripsi'),
        'status' => input_teks($_POST, 'status'),
    ];

    if (preg_match('/\A.{1,150}\z/us', $nilai['judul']) !== 1) {
        $errors['judul'] = 'Judul wajib diisi (maksimal 150 karakter).';
    }
    if (!in_array($nilai['jenis_pekerjaan'], ['tanam', 'panen', 'lainnya'], true)) {
        $errors['jenis_pekerjaan'] = 'Jenis pekerjaan tidak valid.';
    }
    if (preg_match('/\A.{1,150}\z/us', $nilai['lokasi']) !== 1) {
        $errors['lokasi'] = 'Lokasi wajib diisi (maksimal 150 karakter).';
    }
    if (!ctype_digit($nilai['jumlah_buruh']) || (int) $nilai['jumlah_buruh'] < 1 || (float) $nilai['jumlah_buruh'] > 4294967295) {
        $errors['jumlah_buruh'] = 'Jumlah buruh harus berupa bilangan bulat positif.';
    }
    if (!preg_match('/\A\d{1,13}(?:\.\d{1,2})?\z/', $nilai['upah_harian']) || (float) $nilai['upah_harian'] <= 0) {
        $errors['upah_harian'] = 'Upah harian harus lebih besar dari 0 dan maksimal dua angka desimal.';
    }
    $tanggal_mulai = DateTime::createFromFormat('!Y-m-d', $nilai['tanggal_mulai']);
    if (!$tanggal_mulai || $tanggal_mulai->format('Y-m-d') !== $nilai['tanggal_mulai']) {
        $errors['tanggal_mulai'] = 'Tanggal mulai tidak valid.';
    }
    if (!ctype_digit($nilai['durasi_hari']) || (int) $nilai['durasi_hari'] < 1 || (float) $nilai['durasi_hari'] > 4294967295) {
        $errors['durasi_hari'] = 'Durasi harus berupa bilangan bulat positif.';
    }
    if (!in_array($nilai['status'], ['buka', 'ditutup'], true)) {
        $errors['status'] = 'Status lowongan tidak valid.';
    }

    if (!$errors) {
        $jumlah_buruh = (int) $nilai['jumlah_buruh'];
        $upah_harian = $nilai['upah_harian'];
        $tanggal_mulai = $nilai['tanggal_mulai'];
        $durasi_hari = (int) $nilai['durasi_hari'];
        $deskripsi = $nilai['deskripsi'] === '' ? null : $nilai['deskripsi'];
        try {
            if ($lowongan_edit) {
                $stmt = $koneksi->prepare(
                    'UPDATE lowongan_kerja
                     SET judul = ?, jenis_pekerjaan = ?, lokasi = ?, jumlah_buruh = ?,
                         upah_harian = ?, tanggal_mulai = ?, durasi_hari = ?, deskripsi = ?, status = ?
                     WHERE id = ? AND user_id = ?'
                );
                $id = (int) $lowongan_edit['id'];
                $stmt->bind_param(
                    'sssississii',
                    $nilai['judul'],
                    $nilai['jenis_pekerjaan'],
                    $nilai['lokasi'],
                    $jumlah_buruh,
                    $upah_harian,
                    $tanggal_mulai,
                    $durasi_hari,
                    $deskripsi,
                    $nilai['status'],
                    $id,
                    $user_id
                );
                $stmt->execute();
                set_flash('success', 'Lowongan berhasil diperbarui.');
            } else {
                $stmt = $koneksi->prepare(
                    'INSERT INTO lowongan_kerja
                     (user_id, judul, jenis_pekerjaan, lokasi, jumlah_buruh, upah_harian,
                      tanggal_mulai, durasi_hari, deskripsi, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param(
                    'isssississ',
                    $user_id,
                    $nilai['judul'],
                    $nilai['jenis_pekerjaan'],
                    $nilai['lokasi'],
                    $jumlah_buruh,
                    $upah_harian,
                    $tanggal_mulai,
                    $durasi_hari,
                    $deskripsi,
                    $nilai['status']
                );
                $stmt->execute();
                set_flash('success', 'Lowongan berhasil dibuat.');
            }
            redirect('index.php');
        } catch (mysqli_sql_exception $e) {
            error_log('Gagal menyimpan lowongan_kerja: ' . $e->getMessage());
            $errors['_global'] = 'Lowongan gagal disimpan. Periksa data dan coba lagi.';
        }
    }
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

          <?php if (isset($errors['_global'])): ?>
            <div class="alert alert-error" role="alert"><?= e($errors['_global']) ?></div>
          <?php endif; ?>

          <form method="post" action="">
            <?= csrf_field() ?>
            <div class="form-grid">
              <div class="form-group">
                <label for="judul">Judul lowongan</label>
                <input class="input" type="text" id="judul" name="judul" maxlength="150"
                       value="<?= e($nilai['judul']) ?>" required>
                <?php tampil_error($errors, 'judul'); ?>
              </div>
              <div class="form-group">
                <label for="jenis_pekerjaan">Jenis pekerjaan</label>
                <select class="select" id="jenis_pekerjaan" name="jenis_pekerjaan" required>
                  <option value="">Pilih jenis pekerjaan</option>
                  <option value="tanam" <?= $nilai['jenis_pekerjaan'] === 'tanam' ? 'selected' : '' ?>>Tanam</option>
                  <option value="panen" <?= $nilai['jenis_pekerjaan'] === 'panen' ? 'selected' : '' ?>>Panen</option>
                  <option value="lainnya" <?= $nilai['jenis_pekerjaan'] === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
                <?php tampil_error($errors, 'jenis_pekerjaan'); ?>
              </div>
              <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input class="input" type="text" id="lokasi" name="lokasi" maxlength="150"
                       value="<?= e($nilai['lokasi']) ?>" required>
                <?php tampil_error($errors, 'lokasi'); ?>
              </div>
              <div class="form-group">
                <label for="jumlah_buruh">Jumlah buruh dibutuhkan</label>
                <input class="input" type="number" id="jumlah_buruh" name="jumlah_buruh" min="1" step="1"
                       value="<?= e($nilai['jumlah_buruh']) ?>" required>
                <?php tampil_error($errors, 'jumlah_buruh'); ?>
              </div>
              <div class="form-group">
                <label for="upah_harian">Upah harian (Rp)</label>
                <input class="input" type="number" id="upah_harian" name="upah_harian" min="0.01" step="0.01"
                       value="<?= e($nilai['upah_harian']) ?>" required>
                <?php tampil_error($errors, 'upah_harian'); ?>
              </div>
              <div class="form-group">
                <label for="tanggal_mulai">Tanggal mulai</label>
                <input class="input" type="date" id="tanggal_mulai" name="tanggal_mulai"
                       value="<?= e($nilai['tanggal_mulai']) ?>" required>
                <?php tampil_error($errors, 'tanggal_mulai'); ?>
              </div>
              <div class="form-group">
                <label for="durasi_hari">Durasi (hari)</label>
                <input class="input" type="number" id="durasi_hari" name="durasi_hari" min="1" step="1"
                       value="<?= e($nilai['durasi_hari']) ?>" required>
                <?php tampil_error($errors, 'durasi_hari'); ?>
              </div>
              <div class="form-group">
                <label for="status">Status</label>
                <select class="select" id="status" name="status" required>
                  <option value="buka" <?= $nilai['status'] === 'buka' ? 'selected' : '' ?>>Buka</option>
                  <option value="ditutup" <?= $nilai['status'] === 'ditutup' ? 'selected' : '' ?>>Ditutup</option>
                </select>
                <?php tampil_error($errors, 'status'); ?>
              </div>
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi pekerjaan</label>
              <textarea class="textarea" id="deskripsi" name="deskripsi"
                        placeholder="Jelaskan tugas, kebutuhan, atau informasi tambahan"><?= e($nilai['deskripsi']) ?></textarea>
            </div>
            <div class="form-actions">
              <button class="btn btn-primary" type="submit"><?= $lowongan_edit ? 'Simpan perubahan' : 'Terbitkan lowongan' ?></button>
              <a class="btn btn-ghost" href="index.php">Batal</a>
            </div>
          </form>
        </section>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
