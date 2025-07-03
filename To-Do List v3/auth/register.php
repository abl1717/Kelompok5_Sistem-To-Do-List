<?php
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi sederhana
    if (empty($username) || empty($email) || empty($password)) {
        header('Location: ../register.php?error=Semua field harus diisi');
        exit();
    }

    // Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Gunakan prepared statement untuk mencegah SQL Injection
    $stmt = $koneksi->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        header('Location: ../index.php?success=Registrasi berhasil! Silakan login.');
    } else {
        // Cek jika username atau email sudah ada
        if ($koneksi->errno == 1062) {
            header('Location: ../register.php?error=Username atau email sudah terdaftar.');
        } else {
            header('Location: ../register.php?error=Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    $stmt->close();
    $koneksi->close();
}
?>