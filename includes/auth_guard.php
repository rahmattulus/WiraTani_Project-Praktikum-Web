<?php
/*
 * auth_guard.php (S07, PIC Marvian): pembatasan akses per role.
 *
 * Kontrak dengan anggota lain:
 *   wajib_login('petani');   // atau 'tengkulak', 'kios'
 *   - Belum login atau role salah  -> diarahkan ke halaman login + flash message.
 *   - Session menyimpan $_SESSION['user_id'], ['nama'], ['role'].
 *
 * Pakai di baris paling atas halaman terproteksi, SEBELUM ada output apa pun:
 *   require_once __DIR__ . '/../includes/auth_guard.php';
 *   wajib_login('petani');
 */

// Cookie session: tidak bisa dibaca JavaScript dan tidak ikut dikirim dari situs lain.
// Harus diatur SEBELUM session_start() (yang dipanggil di helper.php).
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

require_once __DIR__ . '/helper.php';

const ROLE_VALID = ['petani', 'tengkulak', 'kios'];

function sudah_login(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['role']);
}

function user_id_login(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function url_dashboard(string $role): string
{
    return BASE_URL . '/' . $role . '/index.php';
}

function wajib_login(?string $role = null): void
{
    if ($role !== null && !in_array($role, ROLE_VALID, true)) {
        throw new InvalidArgumentException("Role tidak dikenal: $role");
    }

    if (!sudah_login()) {
        set_flash('error', 'Silakan masuk terlebih dahulu.');
        redirect(BASE_URL . '/auth/login.php');
    }

    if ($role !== null && $_SESSION['role'] !== $role) {
        // Halaman login akan meneruskan pengguna yang sudah masuk ke dashboard
        // miliknya sendiri, dan flash ini tampil di sana.
        set_flash('error', 'Anda tidak punya akses ke halaman tersebut.');
        redirect(BASE_URL . '/auth/login.php');
    }
}

/* ---------- Input aman ----------
   $_GET / $_POST bisa berisi array (?x[]=1). Fungsi ini selalu mengembalikan string,
   sehingga tidak ada TypeError atau warning "Array to string conversion". */
function input_teks(array $sumber, string $kunci, bool $pangkas = true): string
{
    $nilai = $sumber[$kunci] ?? '';
    if (!is_string($nilai)) {
        return '';
    }
    return $pangkas ? trim($nilai) : $nilai;
}

/* ---------- Perlindungan CSRF ----------
   Form POST: taruh <?= csrf_field() ?> di dalam <form>, lalu di penanganan POST
   panggil wajib_csrf('halaman.php') sebelum memproses apa pun. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $kirim = $_POST['csrf'] ?? '';
    return is_string($kirim) && $kirim !== '' && hash_equals($_SESSION['csrf'] ?? '', $kirim);
}

function wajib_csrf(string $tujuan): void
{
    if (!csrf_valid()) {
        set_flash('error', 'Formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.');
        redirect($tujuan);
    }
}
