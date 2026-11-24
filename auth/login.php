<?php
/* Login (S06, Marvian): email + kata sandi, verifikasi dengan password_verify(). */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/auth_ui.php';   // komponen tampilan (ikon, panel kiri)

// Sudah masuk: teruskan ke dashboard role-nya (flash dari wajib_login() tampil di sana).
if (sudah_login()) {
    redirect(url_dashboard($_SESSION['role']));
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf('login.php');
    $email    = strtolower(input_teks($_POST, 'email'));
    $password = input_teks($_POST, 'password', false);

    if ($email === '' || $password === '') {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = $koneksi->prepare('SELECT id, nama, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        // Pesan sengaja sama untuk email tak dikenal dan sandi salah.
        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Email atau kata sandi salah.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];

            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $baru = password_hash($password, PASSWORD_DEFAULT);
                $upd  = $koneksi->prepare('UPDATE users SET password = ? WHERE id = ?');
                $upd->bind_param('si', $baru, $user['id']);
                $upd->execute();
            }

            set_flash('success', 'Selamat datang, ' . $user['nama'] . '.');
            redirect(url_dashboard($user['role']));
        }
    }
}

$layout        = 'publik';
$judul_halaman = 'Masuk';
include __DIR__ . '/../includes/header.php';
?>
    <!-- EDIT: kalau header.php punya tempat khusus CSS tambahan, pindahkan baris ini ke sana -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth_redesain.css">

    <div class="au-wrap">
      <div class="au-shell">
        <?php au_panel('Selamat datang kembali', 'Masuk untuk mengelola lelang, catatan keuangan, pesanan, atau lowongan kerjamu.'); ?>

        <section class="au-side">
          <h1>Masuk ke WiraTani</h1>
          <p class="au-sub">Gunakan email dan kata sandi akunmu.</p>

          <?php if ($error !== ''): ?>
            <div class="au-alert" role="alert"><?= au_ikon('info') ?> <span><?= e($error) ?></span></div>
          <?php endif; ?>

          <form method="post" action="">
            <?= csrf_field() ?>

            <div class="au-field">
              <label for="email">Email</label>
              <div class="au-input">
                <?= au_ikon('mail') ?>
                <input type="email" id="email" name="email" maxlength="100"
                       value="<?= e($email) ?>" autocomplete="email" placeholder="nama@email.com" required autofocus>
              </div>
            </div>

            <div class="au-field">
              <label for="password">Kata sandi</label>
              <div class="au-input">
                <?= au_ikon('lock') ?>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" placeholder="Masukkan kata sandi" required>
                <?= au_toggle_pw('password') ?>
              </div>
            </div>

            <button class="au-btn" type="submit">Masuk <?= au_ikon('arrow') ?></button>
          </form>

          <br>
          <p class="au-alt">Belum punya akun? <a href="<?= BASE_URL ?>/auth/register.php">Buat akun baru</a></p>
          <p class="au-alt au-alt--soft">Hanya ingin mencari pekerjaan? <a href="<?= BASE_URL ?>/publik/bursa.php">Lihat bursa kerja tanpa akun</a></p>
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