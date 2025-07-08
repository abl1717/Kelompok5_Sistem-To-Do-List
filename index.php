<?php
include 'session.php';
include 'db.php';

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Jika user klik tombol "Selesaikan (DB)", panggil PROCEDURE
if (isset($_POST['mark_id'])) {
    $todoId = (int) $_POST['mark_id'];
    $stmt = $conn->prepare("CALL mark_done(?)");
    $stmt->bind_param("i", $todoId);
    $stmt->execute();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>ToDo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <span class="navbar-brand mb-0 h1">ToDo List</span>
            <div>
                <a href="fitur_database.php" class="btn btn-sm btn-outline-light me-2">Chart Tugas</a>
                <span class="text-white me-2">Halo, <?= $username ?></span>
                <a href="logout.php" class="btn btn-sm btn-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Total Tugas Kamu</h6>
                        <?php
                        $res = $conn->query("SELECT total_todo($userId) AS total");
                        $data = $res->fetch_assoc();
                        ?>
                        <p class="display-6"><?= $data['total'] ?? 0 ?></p>
                    </div>
                </div>
            </div>


            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Rata-Rata Tugas selesai</h6>
                        <?php
                        $res = $conn->query("SELECT avg_complete($userId) AS rata");
                        $data = $res->fetch_assoc();
                        ?>
                        <p class="display-6"><?= $data['rata'] ?? 0 ?> Tugas / Hari</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Total Prioritas poin</h6>
                        <?php
                        $stmt = $conn->prepare("SELECT SUM(priority) AS total_priority_points FROM todos WHERE user_id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        $totalPriorityPoints = $row['total_priority_points'] ?? 0; // Default 0 jika tidak ada
                        $stmt->close();
                        ?>
                        <p class="display-6"><?= $totalPriorityPoints ?? 0 ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Daftar semua tugas -->
        <div class="d-flex justify-content-between mb-3">
            <h4>Daftar Tugas</h4>
            <a href="add.php" class="btn btn-success">+ Tambah Tugas</a>
        </div>

        <div class="table-responsive bg-white shadow rounded p-3 mb-4">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Judul</th>
                        <th>Prioritas</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM todos WHERE user_id = $userId ORDER BY deadline ASC");
                    while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><span class="badge bg-warning text-dark"><?= $row['priority'] ?></span></td>
                            <td><?= date('d M Y', strtotime($row['deadline'])) ?></td>
                            <td>
                                <?php if ($row['status'] == 'completed'): ?>
                                    <span class="badge bg-success">Selesai</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                                <?php if ($row['status'] != 'completed'): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="mark_id" value="<?= $row['id'] ?>">
                                        <button class="btn btn-sm btn-warning"
                                            onclick="return confirm('Apakah tugas sudah selesai?')">Selesai</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>