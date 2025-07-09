<?php
include 'session.php'; // Memulai sesi dan memeriksa login
include 'db.php';     // Koneksi database

// Periksa apakah pengguna adalah admin, jika tidak, arahkan ke index.php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password
    $role = $_POST['role'];

    // Validasi input
    if (empty($username) || empty($email) || empty($_POST['password']) || empty($role)) {
        $error = "Semua kolom harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Periksa apakah username atau email sudah ada
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $existingUser = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($existingUser) {
            $error = "Username atau email sudah terdaftar.";
        } else {
            // Masukkan pengguna baru ke database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password, $role);

            if ($stmt->execute()) {
                $success = "Pengguna '$username' berhasil ditambahkan!";
                // Kosongkan input setelah berhasil
                $_POST = array();
            } else {
                $error = "Gagal menambahkan pengguna: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Pengguna Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .card {
            border-radius: 15px;
        }
        .btn {
            border-radius: 8px;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <span class="navbar-brand mb-0 h1">Admin Panel</span>
            <a href="admin_dashboard.php" class="btn btn-sm btn-outline-light">← Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Tambah Pengguna Baru</h5>
            </div>
            <form method="post" class="p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Peran</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="user" <?= (($_POST['role'] ?? '') == 'user') ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= (($_POST['role'] ?? '') == 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">Tambah Pengguna</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>