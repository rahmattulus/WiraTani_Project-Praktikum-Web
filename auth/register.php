<?php
/* Register (S06, Marvian): buat akun petani / tengkulak / kios. */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth_guard.php';

if (sudah_login()) {
    redirect(url_dashboard($_SESSION['role']));
}

$label_role = ['petani' => 'Petani', 'tengkulak' => 'Tengkulak', 'kios' => 'Kios tani'];

// Nama kunci = nama kolom tabel users.
$input = ['nama' => '', 'email' => '', 'no_telepon' => '', 'alamat' => '', 'role' => ''];
$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('register.php');
    foreach ($input as $kolom => $_) {
        $input[$kolom] = input_teks($_POST, $kolom);
    }
    $input['email']      = strtolower($input['email']);
    $input['no_telepon'] = preg_replace('/[\s-]/', '', $input['no_telepon']);
    $password            = input_teks($_POST, 'password', false);
    $konfirmasi          = input_teks($_POST, 'konfirmasi_password', false);

    if ($input['nama'] === '') {
        $error['nama'] = 'Nama wajib diisi.';
    } elseif (strlen($input['nama']) > 100) {
        $error['nama'] = 'Nama maksimal 100 karakter.';
    }

    if ($input['email'] === '') {
        $error['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL) || strlen($input['email']) > 100) {
        $error['email'] = 'Format email tidak valid.';
    }

    if (strlen($password) < 8) {
        $error['password'] = 'Kata sandi minimal 8 karakter.';
    } elseif (strlen($password) > 72) {
        $error['password'] = 'Kata sandi maksimal 72 karakter.';
    }
    if (!isset($error['password']) && $password !== $konfirmasi) {
        $error['konfirmasi_password'] = 'Konfirmasi kata sandi tidak sama.';
    }

    if (!preg_match('/^\+?[0-9]{8,15}$/', $input['no_telepon'])) {
        $error['no_telepon'] = 'Nomor telepon harus 8 sampai 15 angka.';
    }

    if ($input['alamat'] === '') {
        $error['alamat'] = 'Alamat atau desa wajib diisi.';
    } elseif (strlen($input['alamat']) > 255) {
        $error['alamat'] = 'Alamat maksimal 255 karakter.';
    }

    if (!isset($label_role[$input['role']])) {
        $error['role'] = 'Pilih salah satu peran.';
    }

    if (!$error) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare(
                'INSERT INTO users (nama, email, password, no_telepon, alamat, role)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'ssssss',
                $input['nama'], $input['email'], $hash,
                $input['no_telepon'], $input['alamat'], $input['role']
            );
            $stmt->execute();

            set_flash('success', 'Akun berhasil dibuat. Silakan masuk.');
            redirect(BASE_URL . '/auth/login.php');
        } catch (mysqli_sql_exception $ex) {
            if ((int) $ex->getCode() === 1062) {      // duplikat email (UNIQUE)
                $error['email'] = 'Email sudah terdaftar. Gunakan email lain atau masuk.';
            } else {
                throw $ex;
            }
        }
    }
}

$layout        = 'publik';
$judul_halaman = 'Daftar';
include __DIR__ . '/../includes/header.php';
?>
    <div class="auth-wrap">
      <section class="card">
        <h1>Buat akun WiraTani</h1>
        <p>Satu akun untuk satu peran. Peran tidak bisa diubah setelah daftar.</p>

        <form method="post" action="">
          <?= csrf_field() ?>
          <div class="form-grid">
            <div class="form-group">
              <label for="nama">Nama lengkap</label>
              <input class="input" type="text" id="nama" name="nama" maxlength="100"
                     value="<?= e($input['nama']) ?>" autocomplete="name" required>
              <?php tampil_error($error, 'nama'); ?>
            </div>

            <div class="form-group">
              <label for="role">Daftar sebagai</label>
              <select class="select" id="role" name="role" required>
                <option value="">Pilih peran</option>
                <?php foreach ($label_role as $nilai => $label): ?>
                  <option value="<?= e($nilai) ?>"<?= $input['role'] === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
              <?php tampil_error($error, 'role'); ?>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input class="input" type="email" id="email" name="email" maxlength="100"
                     value="<?= e($input['email']) ?>" autocomplete="email" required>
              <?php tampil_error($error, 'email'); ?>
            </div>

            <div class="form-group">
              <label for="no_telepon">Nomor telepon</label>
              <input class="input" type="tel" id="no_telepon" name="no_telepon" maxlength="20"
                     value="<?= e($input['no_telepon']) ?>" placeholder="081234567890" autocomplete="tel" required>
              <?php tampil_error($error, 'no_telepon'); ?>
            </div>

            <div class="form-group">
              <label for="password">Kata sandi</label>
              <input class="input" type="password" id="password" name="password" minlength="8"
                     autocomplete="new-password" required>
              <?php tampil_error($error, 'password'); ?>
            </div>

            <div class="form-group">
              <label for="konfirmasi_password">Ulangi kata sandi</label>
              <input class="input" type="password" id="konfirmasi_password" name="konfirmasi_password"
                     minlength="8" autocomplete="new-password" required>
              <?php tampil_error($error, 'konfirmasi_password'); ?>
            </div>
          </div>

          <div class="form-group">
            <label for="alamat">Alamat atau desa</label>
            <input class="input" type="text" id="alamat" name="alamat" maxlength="255"
                   value="<?= e($input['alamat']) ?>" placeholder="Desa Jatinom, Klaten" required>
            <?php tampil_error($error, 'alamat'); ?>
          </div>

          <div class="form-actions">
            <button class="btn btn-primary" type="submit">Buat akun</button>
            <a class="btn btn-ghost" href="<?= BASE_URL ?>/auth/login.php">Sudah punya akun</a>
          </div>
        </form>
      </section>
    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
