<?php
/* Logout (S06, Marvian): hancurkan session lalu kembali ke login. */
require_once __DIR__ . '/../includes/auth_guard.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Session baru hanya untuk membawa pesan flash.
session_start();
set_flash('success', 'Anda sudah keluar.');
redirect(BASE_URL . '/auth/login.php');
