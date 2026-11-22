<?php
/* Form biaya modal manual; hanya untuk musim milik petani yang login. */
require_once __DIR__ . '/../../includes/auth_guard.php';
wajib_login('petani');
require_once __DIR__ . '/../../config/koneksi.php';

$user_id = user_id_login();
$stmt = $koneksi->prepare(
    'SELECT id, nama_musim, komoditas
     FROM musim_tanam WHERE user_id = ? ORDER BY tanggal_mulai DESC, id DESC'
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$musim_tanam = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$nilai = [
    'musim_tanam_id' => '',
    'kategori' => '',
    'nominal' => '',
    'tanggal' => date('Y-m-d'),
    'keterangan' => '',
];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    wajib_csrf('transaksi_form.php');
    $nilai = [
        'musim_tanam_id' => input_teks($_POST, 'musim_tanam_id'),
        'kategori' => input_teks($_POST, 'kategori'),
        'nominal' => input_teks($_POST, 'nominal'),
        'tanggal' => input_teks($_POST, 'tanggal'),
        'keterangan' => input_teks($_POST, 'keterangan'),
    ];

    $musim_id_valid = ctype_digit($nilai['musim_tanam_id']) && (int) $nilai['musim_tanam_id'] > 0;
    if (!$musim_id_valid) {
        $errors['musim_tanam_id'] = 'Pilih musim tanam yang valid.';
    } else {
        $musim_id = (int) $nilai['musim_tanam_id'];
        $cek_musim = $koneksi->prepare('SELECT id FROM musim_tanam WHERE id = ? AND user_id = ?');
        $cek_musim->bind_param('ii', $musim_id, $user_id);
        $cek_musim->execute();
        if (!$cek_musim->get_result()->fetch_assoc()) {
            $errors['musim_tanam_id'] = 'Musim tanam tidak ditemukan atau bukan milik Anda.';
        }
    }

    $kategori_valid = ['bibit', 'pupuk', 'pestisida', 'alat', 'tenaga_kerja', 'lainnya'];
    if (!in_array($nilai['kategori'], $kategori_valid, true)) {
        $errors['kategori'] = 'Kategori biaya tidak valid.';
    }
    if (!preg_match('/\A\d{1,13}(?:\.\d{1,2})?\z/', $nilai['nominal']) || (float) $nilai['nominal'] <= 0) {
        $errors['nominal'] = 'Nominal harus lebih besar dari 0 dan maksimal dua angka desimal.';
    }
    $tanggal = DateTime::createFromFormat('!Y-m-d', $nilai['tanggal']);
    if (!$tanggal || $tanggal->format('Y-m-d') !== $nilai['tanggal']) {
        $errors['tanggal'] = 'Tanggal transaksi tidak valid.';
    }
    if (preg_match('/\A.{0,255}\z/us', $nilai['keterangan']) !== 1) {
        $errors['keterangan'] = 'Keterangan maksimal 255 karakter.';
    }

    if (!$errors) {
        $musim_id = (int) $nilai['musim_tanam_id'];
        $nominal = $nilai['nominal'];
        $keterangan = $nilai['keterangan'] === '' ? null : $nilai['keterangan'];
        try {
            $stmt = $koneksi->prepare(
                "INSERT INTO transaksi_keuangan
                 (musim_tanam_id, jenis, kategori, nominal, tanggal, keterangan)
                 VALUES (?, 'pengeluaran', ?, ?, ?, ?)"
            );
            $stmt->bind_param('issss', $musim_id, $nilai['kategori'], $nominal, $nilai['tanggal'], $keterangan);
            $stmt->execute();
            set_flash('success', 'Biaya berhasil dicatat.');
            redirect('index.php?musim_tanam_id=' . $musim_id);
        } catch (mysqli_sql_exception $e) {
            error_log('Gagal mencatat transaksi manual: ' . $e->getMessage());
            $errors['_global'] = 'Biaya gagal dicatat. Periksa data dan coba lagi.';
        }
    }
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

          <?php if (isset($errors['_global'])): ?>
            <div class="alert alert-error" role="alert"><?= e($errors['_global']) ?></div>
          <?php endif; ?>

          <?php if (!$musim_tanam): ?>
            <div class="empty-state">
              <p>Buat musim tanam terlebih dahulu sebelum mencatat biaya.</p>
              <a class="btn btn-primary" href="../musim/form.php">Buat musim tanam</a>
            </div>
          <?php else: ?>
            <form method="post" action="">
              <?= csrf_field() ?>
              <div class="form-grid">
                <div class="form-group">
                  <label for="musim_tanam_id">Musim tanam</label>
                  <select class="select" id="musim_tanam_id" name="musim_tanam_id" required>
                    <option value="">Pilih musim tanam</option>
                    <?php foreach ($musim_tanam as $musim): ?>
                      <option value="<?= (int) $musim['id'] ?>"
                        <?= $nilai['musim_tanam_id'] === (string) $musim['id'] ? 'selected' : '' ?>>
                        <?= e($musim['nama_musim']) ?> — <?= e($musim['komoditas']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <?php tampil_error($errors, 'musim_tanam_id'); ?>
                </div>
                <div class="form-group">
                  <label for="kategori">Kategori biaya</label>
                  <select class="select" id="kategori" name="kategori" required>
                    <option value="">Pilih kategori</option>
                    <option value="bibit" <?= $nilai['kategori'] === 'bibit' ? 'selected' : '' ?>>Bibit</option>
                    <option value="pupuk" <?= $nilai['kategori'] === 'pupuk' ? 'selected' : '' ?>>Pupuk</option>
                    <option value="pestisida" <?= $nilai['kategori'] === 'pestisida' ? 'selected' : '' ?>>Pestisida</option>
                    <option value="alat" <?= $nilai['kategori'] === 'alat' ? 'selected' : '' ?>>Alat</option>
                    <option value="tenaga_kerja" <?= $nilai['kategori'] === 'tenaga_kerja' ? 'selected' : '' ?>>Tenaga kerja</option>
                    <option value="lainnya" <?= $nilai['kategori'] === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                  </select>
                  <?php tampil_error($errors, 'kategori'); ?>
                </div>
                <div class="form-group">
                  <label for="nominal">Nominal (Rp)</label>
                  <input class="input" type="number" id="nominal" name="nominal" min="0.01" step="0.01"
                         value="<?= e($nilai['nominal']) ?>" placeholder="Contoh: 250000" required>
                  <?php tampil_error($errors, 'nominal'); ?>
                </div>
                <div class="form-group">
                  <label for="tanggal">Tanggal transaksi</label>
                  <input class="input" type="date" id="tanggal" name="tanggal"
                         value="<?= e($nilai['tanggal']) ?>" required>
                  <?php tampil_error($errors, 'tanggal'); ?>
                </div>
              </div>
              <div class="form-group">
                <label for="keterangan">Keterangan (opsional)</label>
                <textarea class="textarea" id="keterangan" name="keterangan" maxlength="255"
                          placeholder="Contoh: upah pengolahan lahan"><?= e($nilai['keterangan']) ?></textarea>
                <?php tampil_error($errors, 'keterangan'); ?>
              </div>
              <div class="form-actions">
                <button class="btn btn-primary" type="submit">Simpan biaya</button>
                <a class="btn btn-ghost" href="index.php">Batal</a>
              </div>
            </form>
          <?php endif; ?>
        </section>
      </div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
