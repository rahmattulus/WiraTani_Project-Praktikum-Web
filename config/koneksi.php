<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_pertanian';

$koneksi = new mysqli($host, $username, $password, $database);
$koneksi->set_charset('utf8mb4');
