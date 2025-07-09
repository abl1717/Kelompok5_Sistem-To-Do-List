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
$user_data = null;

// Pastikan ID pengguna disediakan di URL
if (isset($_GET['id'])) {
    $user_id_to_edit = (int)$_GET['id'];

    // Ambil data pengguna yang akan diedit
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id_to_edit);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user_data) {
        $error = "Pengguna tidak ditemukan.";
    }
} else {
    $error = "ID pengguna tidak disediakan.";
}

// Tangani pengiriman formulir (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_data) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = $_POST['password']; // Password bisa kosong jika tidak diubah
    $new_role = $_POST['role'];
    $user_id_to_edit = (int)$_POST['user_id']; // Ambil ID dari hidden input form

    // Validasi input
    if (empty($new_username) || empty($new_email) || empty($new_role)) {
        $error = "Username, Email, dan Peran harus diisi.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Periksa duplikasi username atau email (kecuali untuk pengguna yang sedang diedit)
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $checkStmt->bind_param("ssi", $new_username, $new_email, $user_id_to_edit);
        $checkStmt->execute();
        $existingUser = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($existingUser) {
            $error = "Username atau email sudah terdaftar untuk pengguna lain.";
        } else {
            // Bangun query UPDATE secara dinamis
            $update_query = "UPDATE users SET username = ?, email = ?, role = ?";
            $params = "sss";
            $bind_values = [&$new_username, &$new_email, &$new_role];

            // Tambahkan password jika disediakan
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $update_query .= ", password = ?";
                $params .= "s";
                $bind_values[] = &$hashed_password;
            }

            $update_query .= " WHERE id = ?";
            $params .= "i";
            $bind_values[] = &$user_id_to_edit;

            $stmt = $conn->prepare($update_query);

            // Bind parameter secara dinamis
            call_user_func_array([$stmt, 'bind_param'], array_merge([$params], $bind_values));

            if ($stmt->execute()) {
                $success = "Data pengguna '$new_username' berhasil diperbarui!";
                // Perbarui data yang ditampilkan di form setelah update berhasil
                $user_data['username'] = $new_username;
                $user_data['email'] = $new_email;
                $user_data['role'] = $new_role;
            } else {
                $error = "Gagal memperbarui pengguna: " . $conn->error;
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
    <title>Edit Pengguna</title>
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
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Edit Pengguna</h5>
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

                <?php if ($user_data): ?>
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_data['id']) ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user_data['username']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Isi hanya jika Anda ingin mengubah password pengguna ini.</small>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Peran</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="user" <?= ($user_data['role'] == 'user') ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= ($user_data['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="admin_dashboard.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

</body>

</html>