<?php
require_once 'config/koneksi.php';

// Proteksi halaman, hanya user yang sudah login bisa akses
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ambil data untuk Edit (jika ada)
$is_editing = false;
$edit_task = null;
if (isset($_GET['edit_id'])) {
    $is_editing = true;
    $edit_id = $_GET['edit_id'];
    $stmt = $koneksi->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_task = $result->fetch_assoc();
    $stmt->close();
}

// ---- OPERASI AGREGAT ----
// Hitung total tugas, tugas selesai, dan pending
$stmt_agg = $koneksi->prepare("
    SELECT
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
    FROM tasks
    WHERE user_id = ?
");
$stmt_agg->bind_param("i", $user_id);
$stmt_agg->execute();
$result_agg = $stmt_agg->get_result()->fetch_assoc();
$total_tasks = $result_agg['total_tasks'] ?? 0;
$completed_tasks = $result_agg['completed_tasks'] ?? 0;
$pending_tasks = $total_tasks - $completed_tasks;
$stmt_agg->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Todo List</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h1>
            <a href="auth/logout.php">Logout</a>
        </header>

        <main>
            <section class="stats">
                <div class="stat-item">
                    <h3>Total Tugas</h3>
                    <p><?php echo $total_tasks; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Selesai</h3>
                    <p><?php echo $completed_tasks; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Pending</h3>
                    <p><?php echo $pending_tasks; ?></p>
                </div>
            </section>

            <section class="task-form">
                <h3><?php echo $is_editing ? 'Edit Tugas' : 'Tambah Tugas Baru'; ?></h3>
                <form action="<?php echo $is_editing ? 'tasks/edit_aksi.php' : 'tasks/tambah_aksi.php'; ?>" method="POST">
                    <?php if ($is_editing): ?>
                        <input type="hidden" name="task_id" value="<?php echo $edit_task['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="task_name">Nama Tugas</label>
                        <input type="text" id="task_name" name="task_name" value="<?php echo $is_editing ? htmlspecialchars($edit_task['task_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="task_description">Deskripsi (Opsional)</label>
                        <textarea id="task_description" name="task_description" rows="3"><?php echo $is_editing ? htmlspecialchars($edit_task['task_description']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Tanggal Tenggat (Opsional)</label>
                        <input type="date" id="due_date" name="due_date" value="<?php echo $is_editing ? $edit_task['due_date'] : ''; ?>">
                    </div>
                    <button type="submit" class="btn"><?php echo $is_editing ? 'Update Tugas' : 'Tambah Tugas'; ?></button>
                    <?php if ($is_editing): ?>
                        <a href="dashboard.php" class="btn btn-secondary">Batal Edit</a>
                    <?php endif; ?>
                </form>
            </section>

            <hr style="margin: 2rem 0;">

            <section class="task-list-container">
                <h3>Daftar Tugas Anda</h3>
                <ul class="task-list">
                    <?php
                    // Ambil semua tugas milik user yang login
                    $stmt_tasks = $koneksi->prepare("SELECT id, task_name, task_description, status, due_date FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
                    $stmt_tasks->bind_param("i", $user_id);
                    $stmt_tasks->execute();
                    $result_tasks = $stmt_tasks->get_result();

                    if ($result_tasks->num_rows > 0):
                        while ($task = $result_tasks->fetch_assoc()):
                    ?>
                        <li class="task-item <?php echo $task['status']; ?>">
                            <div class="task-details">
                                <p><?php echo htmlspecialchars($task['task_name']); ?></p>
                                <small>
                                    Status: <?php echo ucfirst($task['status']); ?>
                                    <?php if($task['due_date']): ?>
                                        | Tenggat: <?php echo date('d M Y', strtotime($task['due_date'])); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="task-actions">
                                <?php if ($task['status'] == 'pending'): ?>
                                    <a href="tasks/status_aksi.php?id=<?php echo $task['id']; ?>&status=completed" class="action-status">Selesai</a>
                                <?php else: ?>
                                    <a href="tasks/status_aksi.php?id=<?php echo $task['id']; ?>&status=pending" class="action-status pending">Batal</a>
                                <?php endif; ?>
                                <a href="dashboard.php?edit_id=<?php echo $task['id']; ?>" class="action-edit">Edit</a>
                                <a href="tasks/hapus_aksi.php?id=<?php echo $task['id']; ?>" class="action-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');">Hapus</a>
                            </div>
                        </li>
                    <?php
                        endwhile;
                    else:
                        echo "<p>Anda belum memiliki tugas. Silakan tambahkan tugas baru!</p>";
                    endif;
                    $stmt_tasks->close();
                    ?>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>
<?php
$koneksi->close();
?>