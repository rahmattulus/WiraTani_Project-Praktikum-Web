<?php
/* URL kompatibilitas untuk tautan lama menuju ERP. */
require_once __DIR__ . '/../includes/auth_guard.php';
wajib_login('petani');
redirect('erp/index.php');
