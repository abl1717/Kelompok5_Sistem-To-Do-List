<?php
include 'db.php';
session_start();

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login'; // default login
$error = '';

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST" && $mode === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
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

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username sudah digunakan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= $mode === 'login' ? 'Login' : 'Register' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-center"><?= $mode === 'login' ? 'Login Akun' : 'Registrasi Pengguna' ?>
                        </h5>
                    </div>
                    <form method="post" class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label>Username</label>
                            <input name="username" class="form-control" required>
                        </div>

                        <?php if ($mode === 'register'): ?>
                            <div class="mb-3">
                                <label>Email</label>
                                <input name="email" type="email" class="form-control" required>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label>Password</label>
                            <input name="password" type="password" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <?php if ($mode === 'login'): ?>
                                <a href="login.php?mode=register">Belum punya akun?</a>
                                <button class="btn btn-primary">Login</button>
                            <?php else: ?>
                                <a href="login.php?mode=login">Sudah punya akun?</a>
                                <button class="btn btn-success">Daftar</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>