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
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<?php if ($layout === 'publik'): ?>
  <header class="public-nav">
    <a class="brand" href="<?= BASE_URL ?>/index.php"><span aria-hidden="true">🌾</span> WiraTani</a>
    <nav class="public-nav-links" aria-label="Menu utama">
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
