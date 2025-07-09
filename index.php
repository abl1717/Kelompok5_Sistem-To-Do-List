<?php
include 'session.php';
include 'db.php';

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$userRole = $_SESSION['role'] ?? 'user'; // Dapatkan peran pengguna dari sesi, default 'user'

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
            <span class="navbar-brand mb-0 h1">ToDo List</span>
            <div>
                <?php if ($userRole === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn btn-sm btn-outline-light me-2">Admin Dashboard</a>
                <?php endif; ?>
                <a href="fitur_database.php" class="btn btn-sm btn-outline-light me-2">Chart Tugas</a>
                <span class="text-white me-2">Halo, <?= htmlspecialchars($username) ?></span>
                <a href="logout.php" class="btn btn-sm btn-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
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
                        <p class="display-6"><?= number_format($data['rata'] ?? 0, 2) ?> Tugas / Hari</p>
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
                                <!-- Tombol Hapus dengan modal konfirmasi -->
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-id="<?= $row['id'] ?>">
                                    Hapus
                                </button>
                                <?php if ($row['status'] != 'completed'): ?>
                                    <!-- Tombol Selesai dengan modal konfirmasi -->
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#completeConfirmModal" data-id="<?= $row['id'] ?>">
                                        Selesai
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus tugas ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a id="confirmDeleteButton" href="#" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Selesai -->
    <div class="modal fade" id="completeConfirmModal" tabindex="-1" aria-labelledby="completeConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="completeConfirmModalLabel">Konfirmasi Selesai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah tugas ini sudah selesai?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="confirmCompleteForm" method="post" style="display:inline;">
                        <input type="hidden" name="mark_id" id="completeTaskId">
                        <button type="submit" class="btn btn-warning">Selesai</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script untuk menangani modal konfirmasi hapus
        var deleteConfirmModal = document.getElementById('deleteConfirmModal');
        deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Tombol yang memicu modal
            var todoId = button.getAttribute('data-id'); // Ambil ID tugas dari atribut data-id
            var confirmDeleteButton = deleteConfirmModal.querySelector('#confirmDeleteButton');
            confirmDeleteButton.href = 'delete.php?id=' + todoId; // Atur href tombol konfirmasi
        });

        // Script untuk menangani modal konfirmasi selesai
        var completeConfirmModal = document.getElementById('completeConfirmModal');
        completeConfirmModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Tombol yang memicu modal
            var todoId = button.getAttribute('data-id'); // Ambil ID tugas dari atribut data-id
            var completeTaskIdInput = completeConfirmModal.querySelector('#completeTaskId');
            completeTaskIdInput.value = todoId; // Atur nilai input hidden dengan ID tugas
        });
    </script>
</body>

</html>