<?php
/* URL kompatibilitas untuk tautan lama menuju daftar musim tanam. */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('petani');
redirect('musim/index.php');
