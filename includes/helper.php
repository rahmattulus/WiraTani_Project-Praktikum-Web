<?php
/*
 * helper.php: fungsi bantu bersama.
 *
 * CATATAN: ini versi SEMENTARA supaya halaman buatan Marvian bisa dijalankan.
 * PIC aslinya Rahmat (S03). Fungsi yang disepakati: flash message, redirect,
 * format rupiah. Bila versi Rahmat memakai nama lain, ganti berkas ini lalu
 * sesuaikan pemanggilannya (cari: set_flash, tampil_flash, redirect, format_rupiah, e).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* BASE_URL: path proyek terhadap document root, mis. "" atau "/WiraTani".
   Dipakai agar tautan dan redirect benar baik di root maupun subfolder htdocs. */
if (!defined('BASE_URL')) {
    $docroot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $docroot = rtrim(str_replace('\\', '/', $docroot), '/');
    $proyek  = str_replace('\\', '/', dirname(__DIR__));
    $path = ($docroot !== '' && stripos($proyek, $docroot) === 0)
        ? substr($proyek, strlen($docroot))
        : '';
    // Encode per segmen agar folder bernama "Project Web" tidak merusak href / header Location.
    define('BASE_URL', implode('/', array_map('rawurlencode', explode('/', $path))));
}

/* Escape output ke HTML. Wajib untuk semua data dari input pengguna. */
function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* Flash message: tipe = success | error | info. Tampil sekali lalu hilang. */
function set_flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function tampil_flash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $kelas = [
        'success' => 'alert-success',
        'error'   => 'alert-error',
        'info'    => 'alert-info',
    ][$flash['tipe']] ?? 'alert-info';
    echo '<div class="alert ' . $kelas . '" role="status">' . e($flash['pesan']) . '</div>';
}

/* Pesan galat di bawah input form: tampil_error($error, 'email'). */
function tampil_error(array $error, string $field): void
{
    if (isset($error[$field])) {
        echo '<p class="form-error" id="error-' . e($field) . '" role="alert">'
            . e($error[$field]) . '</p>';
    }
}

function format_rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function format_tanggal(?string $tanggal): string
{
    if (!$tanggal) {
        return '-';
    }
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $waktu = strtotime($tanggal);
    return date('j', $waktu) . ' ' . $bulan[(int) date('n', $waktu)] . ' ' . date('Y', $waktu);
}

/* Inisial untuk avatar, mis. "Budi Santoso" -> "BS". */
function inisial(string $nama): string
{
    $kata = preg_split('/\s+/', trim($nama), -1, PREG_SPLIT_NO_EMPTY);
    $hasil = '';
    foreach (array_slice($kata, 0, 2) as $k) {
        $hasil .= strtoupper(substr($k, 0, 1));
    }
    return $hasil !== '' ? $hasil : '?';
}

/*
 * Mencatat satu transaksi ERP tanpa memulai transaksi database.
 * Pemanggil wajib mengelola begin_transaction() / commit() / rollback().
 */
function catat_transaksi(
    mysqli $koneksi,
    int $musim_tanam_id,
    string $jenis,
    string $kategori,
    float $nominal,
    string $tanggal,
    ?string $keterangan = null,
    ?int $hasil_panen_id = null,
    ?int $pesanan_saprotan_id = null
): bool {
    if (!in_array($jenis, ['pendapatan', 'pengeluaran'], true)
        || !in_array($kategori, ['hasil_lelang', 'bibit', 'pupuk', 'pestisida', 'alat', 'tenaga_kerja', 'lainnya'], true)
        || $nominal <= 0
        || $musim_tanam_id <= 0
    ) {
        return false;
    }

    if ($hasil_panen_id !== null && $pesanan_saprotan_id === null) {
        $stmt = $koneksi->prepare(
            'INSERT INTO transaksi_keuangan
             (musim_tanam_id, jenis, kategori, nominal, tanggal, keterangan, hasil_panen_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('issdssi', $musim_tanam_id, $jenis, $kategori, $nominal, $tanggal, $keterangan, $hasil_panen_id);
    } elseif ($hasil_panen_id === null && $pesanan_saprotan_id !== null) {
        $stmt = $koneksi->prepare(
            'INSERT INTO transaksi_keuangan
             (musim_tanam_id, jenis, kategori, nominal, tanggal, keterangan, pesanan_saprotan_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('issdssi', $musim_tanam_id, $jenis, $kategori, $nominal, $tanggal, $keterangan, $pesanan_saprotan_id);
    } elseif ($hasil_panen_id === null && $pesanan_saprotan_id === null) {
        $stmt = $koneksi->prepare(
            'INSERT INTO transaksi_keuangan
             (musim_tanam_id, jenis, kategori, nominal, tanggal, keterangan)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('issdss', $musim_tanam_id, $jenis, $kategori, $nominal, $tanggal, $keterangan);
    } else {
        $stmt = $koneksi->prepare(
            'INSERT INTO transaksi_keuangan
             (musim_tanam_id, jenis, kategori, nominal, tanggal, keterangan, hasil_panen_id, pesanan_saprotan_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('issdssii', $musim_tanam_id, $jenis, $kategori, $nominal, $tanggal, $keterangan, $hasil_panen_id, $pesanan_saprotan_id);
    }

    try {
        $stmt->execute();
        return true;
    } catch (mysqli_sql_exception $ex) {
        if ((int) $ex->getCode() === 1062) {
            return false;
        }
        throw $ex;
    }
}
