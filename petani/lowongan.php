<?php
/* URL kompatibilitas untuk tautan lama menuju daftar lowongan. */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('petani');
redirect('lowongan/index.php');
