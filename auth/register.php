<?php
/* Register (S06, Marvian): buat akun petani / tengkulak / kios. */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/auth_ui.php';   // komponen tampilan (ikon, panel kiri)

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

/* ---------- Data tampilan ---------- */
$kelas_error = fn(string $k): string => isset($error[$k]) ? ' has-error' : '';
$info_role = [
    'petani'    => ['sprout', 'Melelang panen, mencatat keuangan musim tanam, memesan saprotan, dan membuka lowongan.'],
    'tengkulak' => ['gavel',  'Menjelajahi lelang panen dan mengajukan harga tawar per kilogram.'],
    'kios'      => ['box',    'Mengelola katalog, stok, dan pesanan pupuk, bibit, serta sewa alat.'],
];

$layout        = 'publik';
$judul_halaman = 'Daftar';
include __DIR__ . '/../includes/header.php';
?>
    <!-- EDIT: kalau header.php punya tempat khusus CSS tambahan, pindahkan baris ini ke sana -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth_redesain.css">

    <div class="au-wrap au-wrap--wide">
      <div class="au-shell au-shell--wide">
        <?php au_panel('Bergabung dengan WiraTani', 'Pilih peranmu, lengkapi data diri, dan mulai bertransaksi secara terbuka dan tercatat.'); ?>

        <section class="au-side">
          <h1>Buat akun WiraTani</h1>
          <p class="au-callout"><?= au_ikon('info') ?> <span>Satu akun untuk satu peran. Peran tidak bisa diubah setelah daftar.</span></p>

          <form method="post" action="">
            <?= csrf_field() ?>

            <!-- LANGKAH 1: PERAN -->
            <fieldset class="au-roles-wrap">
              <legend class="au-section">1. Daftar sebagai</legend>
              <div class="au-roles">
                <?php foreach ($label_role as $nilai => $label): ?>
                  <label class="au-role">
                    <input type="radio" name="role" value="<?= e($nilai) ?>"<?= $input['role'] === $nilai ? ' checked' : '' ?> required>
                    <span class="au-role-body">
                      <span class="au-role-ico"><?= au_ikon($info_role[$nilai][0]) ?></span>
                      <strong><?= e($label) ?></strong>
                      <small><?= e($info_role[$nilai][1]) ?></small>
                      <span class="au-role-check"><?= au_ikon('check') ?></span>
                    </span>
                  </label>
                <?php endforeach; ?>
              </div>
              <?php tampil_error($error, 'role'); ?>
            </fieldset>

            <!-- LANGKAH 2: DATA DIRI -->
            <p class="au-section">2. Data diri</p>
            <div class="au-grid">
              <div class="au-field<?= $kelas_error('nama') ?>">
                <label for="nama">Nama lengkap</label>
                <div class="au-input">
                  <?= au_ikon('user') ?>
                  <input type="text" id="nama" name="nama" maxlength="100"
                         value="<?= e($input['nama']) ?>" autocomplete="name" required>
                </div>
                <?php tampil_error($error, 'nama'); ?>
              </div>

              <div class="au-field<?= $kelas_error('email') ?>">
                <label for="email">Email</label>
                <div class="au-input">
                  <?= au_ikon('mail') ?>
                  <input type="email" id="email" name="email" maxlength="100"
                         value="<?= e($input['email']) ?>" autocomplete="email" placeholder="nama@email.com" required>
                </div>
                <?php tampil_error($error, 'email'); ?>
              </div>

              <div class="au-field<?= $kelas_error('no_telepon') ?>">
                <label for="no_telepon">Nomor telepon</label>
                <div class="au-input">
                  <?= au_ikon('phone') ?>
                  <input type="tel" id="no_telepon" name="no_telepon" maxlength="20"
                         value="<?= e($input['no_telepon']) ?>" placeholder="081234567890" autocomplete="tel" required>
                </div>
                <?php tampil_error($error, 'no_telepon'); ?>
              </div>

              <div class="au-field<?= $kelas_error('alamat') ?>">
                <label for="alamat">Alamat atau desa</label>
                <div class="au-input">
                  <?= au_ikon('pin') ?>
                  <input type="text" id="alamat" name="alamat" maxlength="255"
                         value="<?= e($input['alamat']) ?>" placeholder="Desa Jatinom, Klaten" required>
                </div>
                <?php tampil_error($error, 'alamat'); ?>
              </div>
            </div>

            <!-- LANGKAH 3: KEAMANAN -->
            <p class="au-section">3. Keamanan akun</p>
            <div class="au-grid">
              <div class="au-field<?= $kelas_error('password') ?>">
                <label for="password">Kata sandi</label>
                <div class="au-input">
                  <?= au_ikon('lock') ?>
                  <input type="password" id="password" name="password" minlength="8"
                         autocomplete="new-password" placeholder="Minimal 8 karakter" required>
                  <?= au_toggle_pw('password') ?>
                </div>
                <?php tampil_error($error, 'password'); ?>
              </div>

              <div class="au-field<?= $kelas_error('konfirmasi_password') ?>">
                <label for="konfirmasi_password">Ulangi kata sandi</label>
                <div class="au-input">
                  <?= au_ikon('lock') ?>
                  <input type="password" id="konfirmasi_password" name="konfirmasi_password"
                         minlength="8" autocomplete="new-password" placeholder="Ketik ulang kata sandi" required>
                  <?= au_toggle_pw('konfirmasi_password') ?>
                </div>
                <?php tampil_error($error, 'konfirmasi_password'); ?>
              </div>
            </div>

            <button class="au-btn" type="submit">Buat akun <?= au_ikon('arrow') ?></button>
          </form>

          <br>
          <p class="au-alt">Sudah punya akun? <a href="<?= BASE_URL ?>/auth/login.php">Masuk</a></p>
        </section>
      </div>
    </div>

    <script>
      document.querySelectorAll('[data-toggle-pw]').forEach(function (b) {
        b.addEventListener('click', function () {
          var i = document.getElementById(b.dataset.togglePw);
          var tampil = i.type === 'password';
          i.type = tampil ? 'text' : 'password';
          b.classList.toggle('is-on', tampil);
          b.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
      });
    </script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
