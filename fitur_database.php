<?php
include 'session.php';
include 'db.php';

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Eksekusi PROCEDURE jika tombol ditekan
if (isset($_POST['mark_example'])) {
    $todoId = (int) $_POST['mark_example']; // Ambil ID dari form, bukan hardcoded
    $stmt = $conn->prepare("CALL mark_done(?)");
    $stmt->bind_param("i", $todoId);
    $stmt->execute();
    header("Location: fitur_database.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Fitur Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <span class="navbar-brand mb-0 h1">Fitur Database</span>
            <a href="index.php" class="btn btn-sm btn-light">← Kembali</a>
        </div>
    </nav>

    <div class="container">

        <h4 class="mb-3">📊 Agregat Todo Milik: <strong><?= $username ?></strong></h4>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Total Tugas yang pernah dibuat</h6>
                        <?php
                        $res = $conn->query("SELECT COUNT(*) AS total FROM todos");
                        $data = $res->fetch_assoc();
                        ?>
                        <p class="display-6"><?= $data['total'] ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Prioritas Tertinggi</h6>
                        <?php
                        $max = $conn->query("SELECT MAX(priority) AS maxp FROM todos WHERE user_id = $userId")->fetch_assoc();
                        ?>
                        <p class="display-6"><?= $max['maxp'] ?? '-' ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Deadline Terdekat</h6>
                        <?php
                        $min = $conn->query("SELECT MIN(deadline) AS mind FROM todos WHERE user_id = $userId AND status = 'pending'")->fetch_assoc();
                        ?>
                        <p class="display-6"><?= $min['mind'] ? date('d M Y', strtotime($min['mind'])) : '-' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- ✅ VIEW -->
        <h4 class="mb-3">📄Tugas yang Sudah Diselesaikan</h4>
        <div class="table-responsive bg-white shadow-sm rounded p-3 mb-4">
            <table class="table table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>Judul</th>
                        <th>Username</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $view = $conn->query("SELECT * FROM completed_todos WHERE username = '$username'");
                    if ($view->num_rows > 0):
                        while ($row = $view->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><span class="badge bg-success"><?= $row['status'] ?></span></td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="3">Belum ada tugas yang diselesaikan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>