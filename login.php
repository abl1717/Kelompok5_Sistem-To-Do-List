<?php
include 'db.php';
session_start();

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login'; // default login
$error = '';

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST" && $mode === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Simpan peran pengguna

        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php"); // untuk arahkan admin ke dashboard admin
        } else {
            header("Location: index.php"); // untuk arahkan pengguna biasa ke halaman utama
        }
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}

// Handle register
if ($_SERVER["REQUEST_METHOD"] === "POST" && $mode === 'register') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Periksa apakah username atau email sudah ada
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $existingUser = $checkStmt->get_result()->fetch_assoc();

    if ($existingUser) {
        $error = "Username atau email sudah terdaftar!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')"); // Default role adalah 'user'
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            // Langsung login setelah registrasi berhasil
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            header("Location: index.php");
            exit;
        } else {
            $error = "Registrasi gagal. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= ($mode === 'login') ? 'Login' : 'Daftar'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0"><?= ($mode === 'login') ? 'Login' : 'Daftar'; ?></h4>
                    </div>
                    <form method="post" class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input name="username" id="username" class="form-control" required>
                        </div>

                        <?php if ($mode === 'register'): ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input name="email" id="email" type="email" class="form-control" required>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input name="password" id="password" type="password" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <?php if ($mode === 'login'): ?>
                                <a href="login.php?mode=register">Belum punya akun?</a>
                                <button type="submit" class="btn btn-primary">Login</button>
                            <?php else: ?>
                                <a href="login.php?mode=login">Sudah punya akun?</a>
                                <button type="submit" class="btn btn-success">Daftar</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>