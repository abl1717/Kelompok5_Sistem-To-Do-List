<?php
// Pengaturan koneksi database
$db_host = 'localhost';
$db_user = 'root'; // Sesuaikan dengan username database Anda
$db_pass = '';     // Sesuaikan dengan password database Anda
$db_name = 'db_todolist_uas';

// Membuat koneksi
$koneksi = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi ke database gagal: " . $koneksi->connect_error);
}

// Memulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>