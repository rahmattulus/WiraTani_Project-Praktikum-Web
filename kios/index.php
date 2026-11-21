<?php
/* Dashboard kios: KERANGKA SEMENTARA agar login punya tujuan. Isi dibuat Agung (S08). */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('kios');

$judul_halaman = 'Dashboard Kios';
$menu_aktif    = 'dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
      <section class="card">
        <div class="empty-state">
          <p>Dashboard kios belum dibuat.</p>
          <p>Gunakan menu di samping untuk membuka halaman yang sudah tersedia.</p>
        </div>
      </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
