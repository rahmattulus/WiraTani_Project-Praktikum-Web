<?php
/* Login (S06, Marvian): email + kata sandi, verifikasi dengan password_verify(). */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth_guard.php';

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
    <div class="auth-wrap">
      <section class="card">
        <h1>Masuk ke WiraTani</h1>

        <?php if ($error !== ''): ?>
          <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="email">Email</label>
            <input class="input" type="email" id="email" name="email" maxlength="100"
                   value="<?= e($email) ?>" autocomplete="email" required autofocus>
          </div>

          <div class="form-group">
            <label for="password">Kata sandi</label>
            <input class="input" type="password" id="password" name="password"
                   autocomplete="current-password" required>
          </div>

          <div class="form-actions">
            <button class="btn btn-primary" type="submit">Masuk</button>
            <a class="btn btn-ghost" href="<?= BASE_URL ?>/auth/register.php">Buat akun baru</a>
          </div>
        </form>
      </section>
    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
