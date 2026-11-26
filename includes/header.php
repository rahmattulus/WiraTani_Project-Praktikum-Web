<?php
/*
 * header.php: <head>, viewport, tautan CSS, pembuka layout.
 * CATATAN: versi SEMENTARA agar halaman Marvian bisa jalan. PIC asli: Agung (S05).
 *
 * Variabel yang dibaca dari halaman pemanggil:
 *   $judul_halaman  judul (h1 di topbar / <title>)
 *   $layout         'app' (default, dengan sidebar) atau 'publik' (tanpa sidebar)
 */
$layout        = $layout ?? 'app';
$judul_halaman = $judul_halaman ?? 'WiraTani';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul_halaman) ?> | WiraTani</title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/wiratani-icon.svg">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
<?php if ($layout === 'publik'): ?>
  <header class="public-nav">
    <a class="brand" href="<?= BASE_URL ?>/index.php"><span aria-hidden="true">
      <img src="<?= BASE_URL ?>/assets/img/wiratani-icon.svg" alt="WiraTani" height="40">
    </span> WiraTani</a>
    <button class="public-nav-toggle" type="button" aria-label="Buka menu" aria-controls="public-nav-links" aria-expanded="false">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
        <path d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
    <nav class="public-nav-links" id="public-nav-links" aria-label="Menu utama">
      <a href="<?= BASE_URL ?>/index.php">Beranda</a>
      <a href="<?= BASE_URL ?>/publik/bursa.php">Bursa Kerja</a>
      <a href="<?= BASE_URL ?>/auth/login.php">Masuk</a>
    </nav>
  </header>
  <main class="content">
    <?php tampil_flash(); ?>
<?php else: ?>
<div class="app">
<?php endif; ?>
