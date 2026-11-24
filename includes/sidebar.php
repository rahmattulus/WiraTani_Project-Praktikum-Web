<?php
/*
 * sidebar.php: sidebar per role + topbar + pembuka area konten.
 * CATATAN: versi SEMENTARA agar halaman Marvian bisa jalan. PIC asli: Agung (S05).
 * Ikon menu menggunakan aset SVG dari assets/img/.
 *
 * Variabel dari halaman pemanggil: $judul_halaman, $menu_aktif (kunci menu).
 */
$role_sekarang = $_SESSION['role'];
$menu_aktif    = $menu_aktif ?? '';

$semua_menu = [
    'petani' => [
        ['dashboard', 'Dashboard',    'index.php',  'dashboard.svg'],
        ['lelang',    'Lelang Panen', 'lelang/',    'lelang.svg'],
        ['erp',       'ERP Keuangan', 'erp/',       'erp.svg'],
        ['musim',     'Musim Tanam',  'musim/',     'musim.svg'],
        ['saprotan',  'Saprotan',     'saprotan/',  'saprotan.svg'],
        ['lowongan',  'Lowongan',     'lowongan/',  'lowongan.svg'],
    ],
    'tengkulak' => [
        ['dashboard', 'Dashboard',    'index.php', 'dashboard.svg'],
        ['pasar',     'Pasar Lelang', 'pasar/',   'pasar.svg'],
    ],
    'kios' => [
        ['dashboard', 'Dashboard',     'index.php',  'dashboard.svg'],
        ['produk',    'Produk',        'produk.php', 'produk.svg'],
        ['pesanan',   'Pesanan Masuk', 'pesanan.php','pesanan.svg'],
    ],
];
?>
  <aside class="sidebar" id="sidebar">
    <div class="brand"><span aria-hidden="true">
      <img src="<?= BASE_URL ?>/assets/img/wiratani-icon.svg" alt="WiraTani" height="40">
    </span> WiraTani</div>
    <nav aria-label="Menu <?= e($role_sekarang) ?>">
      <ul class="nav-list">
<?php foreach ($semua_menu[$role_sekarang] as [$kunci, $label, $berkas, $ikon]): ?>
        <li>
          <a class="nav-item<?= $kunci === $menu_aktif ? ' active' : '' ?>"
             href="<?= BASE_URL . '/' . e($role_sekarang) . '/' . $berkas ?>"
             <?= $kunci === $menu_aktif ? 'aria-current="page"' : '' ?>>
            <span class="icon" aria-hidden="true">
              <img src="<?= BASE_URL ?>/assets/img/<?= e($ikon) ?>?v=2" alt="" width="20" height="20">
            </span><?= e($label) ?>
          </a>
        </li>
<?php endforeach; ?>
        <li class="nav-divider" role="separator"></li>
        <li>
          <a class="nav-item" href="<?= BASE_URL ?>/auth/logout.php">
            <span class="icon" aria-hidden="true">
              <img src="<?= BASE_URL ?>/assets/img/logout.svg?v=2" alt="" width="20" height="20">
            </span>Keluar
          </a>
        </li>
      </ul>
    </nav>
  </aside>
  <div class="overlay"></div>

  <div class="main">
    <header class="topbar">
      <div class="topbar-start">
        <button class="menu-btn" type="button" aria-label="Buka menu" aria-controls="sidebar">☰</button>
        <div>
          <p class="topbar-greeting">Selamat datang kembali, <?= e($_SESSION['nama']) ?></p>
          <h1><?= e($judul_halaman) ?></h1>
        </div>
      </div>
      <div class="topbar-actions">
        <div class="user-summary">
          <span class="avatar" aria-hidden="true"><?= e(inisial($_SESSION['nama'])) ?></span>
          <span class="topbar-user-name"><?= e($_SESSION['nama']) ?></span>
        </div>
      </div>
    </header>
    <main class="content">
      <?php tampil_flash(); ?>
