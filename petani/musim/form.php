<?php
/* Form tambah/ubah musim tanam dengan penyimpanan database. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../config/koneksi.php';

$user_id = user_id_login();
$id_input = input_teks($_GET, 'id');
$musim_edit = null;
if ($id_input !== '') {
    if (!ctype_digit($id_input) || (int) $id_input < 1) {
        set_flash('error', 'Musim tanam tidak ditemukan.');
        redirect('index.php');
    }

    $id = (int) $id_input;
    $stmt = $koneksi->prepare(
        'SELECT id, nama_musim, komoditas, tanggal_mulai, tanggal_selesai, status
         FROM musim_tanam WHERE id = ? AND user_id = ?'
    );
    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    $musim_edit = $stmt->get_result()->fetch_assoc();
    if (!$musim_edit) {
        set_flash('error', 'Musim tanam tidak ditemukan atau bukan milik Anda.');
        redirect('index.php');
    }
}

$nilai = $musim_edit ?: [
    'nama_musim' => '',
    'komoditas' => '',
    'tanggal_mulai' => '',
    'tanggal_selesai' => '',
    'status' => 'berjalan',
];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    wajib_csrf('form.php' . ($musim_edit ? '?id=' . (int) $musim_edit['id'] : ''));
    $nilai = [
        'nama_musim' => input_teks($_POST, 'nama_musim'),
        'komoditas' => input_teks($_POST, 'komoditas'),
        'tanggal_mulai' => input_teks($_POST, 'tanggal_mulai'),
        'tanggal_selesai' => input_teks($_POST, 'tanggal_selesai'),
        'status' => input_teks($_POST, 'status'),
    ];

    $nama_valid = preg_match('/\A.{1,100}\z/us', $nilai['nama_musim']) === 1;
    $komoditas_valid = preg_match('/\A.{1,100}\z/us', $nilai['komoditas']) === 1;
    $mulai = DateTime::createFromFormat('!Y-m-d', $nilai['tanggal_mulai']);
    $mulai_valid = $mulai && $mulai->format('Y-m-d') === $nilai['tanggal_mulai'];
    $selesai_valid = true;
    if ($nilai['tanggal_selesai'] !== '') {
        $selesai = DateTime::createFromFormat('!Y-m-d', $nilai['tanggal_selesai']);
        $selesai_valid = $selesai
            && $selesai->format('Y-m-d') === $nilai['tanggal_selesai']
            && $mulai_valid
            && $nilai['tanggal_selesai'] >= $nilai['tanggal_mulai'];
    }

    if (!$nama_valid) {
        $errors['nama_musim'] = 'Nama musim wajib diisi (maksimal 100 karakter).';
    }
    if (!$komoditas_valid) {
        $errors['komoditas'] = 'Komoditas wajib diisi (maksimal 100 karakter).';
    }
    if (!$mulai_valid) {
        $errors['tanggal_mulai'] = 'Tanggal mulai tidak valid.';
    }
    if (!$selesai_valid) {
        $errors['tanggal_selesai'] = 'Tanggal selesai harus valid dan tidak lebih awal dari tanggal mulai.';
    }
    if (!in_array($nilai['status'], ['berjalan', 'selesai'], true)) {
        $errors['status'] = 'Status musim tidak valid.';
    }

    if (!$errors) {
        $tanggal_selesai = $nilai['tanggal_selesai'] === '' ? null : $nilai['tanggal_selesai'];
        try {
            if ($musim_edit) {
                $stmt = $koneksi->prepare(
                    'UPDATE musim_tanam
                     SET nama_musim = ?, komoditas = ?, tanggal_mulai = ?, tanggal_selesai = ?, status = ?
                     WHERE id = ? AND user_id = ?'
                );
                $id = (int) $musim_edit['id'];
                $stmt->bind_param(
                    'sssssii',
                    $nilai['nama_musim'],
                    $nilai['komoditas'],
                    $nilai['tanggal_mulai'],
                    $tanggal_selesai,
                    $nilai['status'],
                    $id,
                    $user_id
                );
                $stmt->execute();
                set_flash('success', 'Musim tanam berhasil diperbarui.');
            } else {
                $stmt = $koneksi->prepare(
                    'INSERT INTO musim_tanam
                     (user_id, nama_musim, komoditas, tanggal_mulai, tanggal_selesai, status)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param(
                    'isssss',
                    $user_id,
                    $nilai['nama_musim'],
                    $nilai['komoditas'],
                    $nilai['tanggal_mulai'],
                    $tanggal_selesai,
                    $nilai['status']
                );
                $stmt->execute();
                set_flash('success', 'Musim tanam berhasil dibuat.');
            }
            redirect('index.php');
        } catch (mysqli_sql_exception $e) {
            error_log('Gagal menyimpan musim_tanam: ' . $e->getMessage());
            $errors['_global'] = 'Musim tanam gagal disimpan. Periksa data dan coba lagi.';
        }
    }
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

          <?php if (isset($errors['_global'])): ?>
            <div class="alert alert-error" role="alert"><?= e($errors['_global']) ?></div>
          <?php endif; ?>

          <form method="post" action="">
            <?= csrf_field() ?>
            <div class="form-grid">
              <div class="form-group">
                <label for="nama_musim">Nama musim</label>
                <input class="input" type="text" id="nama_musim" name="nama_musim" maxlength="100"
                       value="<?= e($nilai['nama_musim']) ?>" placeholder="Contoh: Musim Tanam 1 2026" required>
                <?php tampil_error($errors, 'nama_musim'); ?>
              </div>
              <div class="form-group">
                <label for="komoditas">Komoditas</label>
                <input class="input" type="text" id="komoditas" name="komoditas" maxlength="100"
                       value="<?= e($nilai['komoditas']) ?>" placeholder="Padi, jagung, atau komoditas lain" required>
                <?php tampil_error($errors, 'komoditas'); ?>
              </div>
              <div class="form-group">
                <label for="tanggal_mulai">Tanggal mulai</label>
                <input class="input" type="date" id="tanggal_mulai" name="tanggal_mulai"
                       value="<?= e($nilai['tanggal_mulai']) ?>" required>
                <?php tampil_error($errors, 'tanggal_mulai'); ?>
              </div>
              <div class="form-group">
                <label for="tanggal_selesai">Tanggal selesai (opsional)</label>
                <input class="input" type="date" id="tanggal_selesai" name="tanggal_selesai"
                       value="<?= e($nilai['tanggal_selesai']) ?>">
                <?php tampil_error($errors, 'tanggal_selesai'); ?>
              </div>
              <div class="form-group">
                <label for="status">Status</label>
                <select class="select" id="status" name="status" required>
                  <option value="berjalan" <?= $nilai['status'] === 'berjalan' ? 'selected' : '' ?>>Berjalan</option>
                  <option value="selesai" <?= $nilai['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
                <?php tampil_error($errors, 'status'); ?>
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
