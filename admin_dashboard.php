<?php
include 'session.php'; // Memulai sesi dan memeriksa login
include 'db.php';     // Koneksi database

// Periksa apakah pengguna adalah admin, jika tidak, arahkan ke index.php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil semua pengguna dari database
$users_query = "SELECT u.id, u.username, u.email, u.role, COUNT(t.id) AS total_tasks
                FROM users u
                LEFT JOIN todos t ON u.id = t.user_id
                GROUP BY u.id, u.username, u.email, u.role
                ORDER BY u.id DESC"; // Urutkan berdasarkan ID terbaru
$users_result = $conn->query($users_query);

// Ambil statistik total pengguna dan total tugas
$total_users_query = "SELECT COUNT(*) AS total_users FROM users";
$total_users_result = $conn->query($total_users_query)->fetch_assoc();
$total_users = $total_users_result['total_users'];

$total_todos_query = "SELECT COUNT(*) AS total_todos FROM todos";
$total_todos_result = $conn->query($total_todos_query)->fetch_assoc();
$total_todos = $total_todos_result['total_todos'];

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <span class="navbar-brand mb-0 h1">Admin Dashboard</span>
            <div>
                <span class="text-white me-3">Halo, Admin <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-white bg-info mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Pengguna</h5>
                        <p class="card-text fs-2"><?= $total_users ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-success mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Tugas</h5>
                        <p class="card-text fs-2"><?= $total_todos ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Manajemen Pengguna</h4>
            <a href="admin_add_user.php" class="btn btn-success">Tambah Pengguna Baru</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Jumlah Tugas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users_result->num_rows > 0): ?>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['id']) ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge <?= ($user['role'] === 'admin') ? 'bg-danger' : 'bg-primary'; ?>">
                                                <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($user['total_tasks']) ?></td>
                                        <td>
                                            <a href="admin_edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning me-2">Edit</a>
                                            <a href="admin_delete_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Yakin ingin menghapus pengguna ini dan semua tugasnya? Tindakan ini tidak dapat dibatalkan!')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada pengguna yang terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>