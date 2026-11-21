<?php
/* Landing page WiraTani (S08). Bagian dari rencana Agung, dibuat oleh Marvian
   agar alamat utama tidak lagi kosong. Mengikuti DESAIN.md: hero masalah + solusi,
   tiga kartu manfaat, tombol Masuk dan Daftar, serta kaitan dengan SDG 8. */
require_once __DIR__ . '/includes/auth_guard.php';

// Pengguna yang sudah masuk langsung diteruskan ke dashboard role-nya.
if (sudah_login()) {
    redirect(url_dashboard($_SESSION['role']));
}

$layout        = 'publik';
$judul_halaman = 'Panen dihargai adil, usaha tani tercatat rapi';
include __DIR__ . '/includes/header.php';
?>
    <div class="dash">
      <section class="hero">
        <h1>Panen dihargai adil, usaha tani tercatat rapi</h1>
        <p>
          Harga panen sering ditentukan sepihak dan modal tanam jarang tercatat.
          WiraTani mempertemukan petani, tengkulak, dan kios tani dalam satu platform,
          supaya harga terbentuk dari penawaran terbuka dan laba rugi tiap musim tanam terlihat jelas.
        </p>
        <div class="form-actions hero-actions">
          <a class="btn btn-primary" href="<?= BASE_URL ?>/auth/register.php">Daftar gratis</a>
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/auth/login.php">Masuk</a>
        </div>
      </section>

      <section class="feature-grid" aria-label="Manfaat WiraTani">
        <article class="card">
          <h2 class="card-title">Lelang panen yang adil</h2>
          <p>
            Petani membuka lelang hasil panen. Tengkulak mengirim penawaran, dan petani
            memilih penawaran terbaik, sehingga harga tidak lagi ditentukan sepihak.
          </p>
        </article>

        <article class="card">
          <h2 class="card-title">Catat keuangan tiap musim</h2>
          <p>
            Pendapatan dari lelang dan biaya pupuk, bibit, serta sewa alat dari kios masuk
            ke catatan musim tanam, lengkap dengan laba atau rugi.
          </p>
        </article>

        <article class="card">
          <h2 class="card-title">Bursa kerja buruh tani</h2>
          <p>
            Petani memasang lowongan kerja harian dengan upah yang jelas. Buruh tani bisa
            melihat dan menghubungi petani tanpa perlu daftar.
          </p>
          <a class="card-link" href="<?= BASE_URL ?>/publik/bursa.php">Lihat lowongan</a>
        </article>
      </section>

      <section class="cta" aria-label="Kaitan dengan SDG 8">
        <div class="cta-content">
          <h2>Mendukung SDG 8: pekerjaan layak dan pertumbuhan ekonomi</h2>
          <p>
            Harga yang lebih adil dan peluang kerja yang terbuka membantu pelaku usaha tani
            kecil tumbuh dengan penghasilan yang lebih layak.
          </p>
          <a class="btn" href="<?= BASE_URL ?>/auth/register.php">Mulai sekarang</a>
        </div>
        <span class="cta-decoration" aria-hidden="true"></span>
      </section>
    </div>
<?php include __DIR__ . '/includes/footer.php'; ?>
