<?php
include 'db.php';
include 'session.php';

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil data tugas
$stmt = $conn->prepare("SELECT * FROM todos WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_assoc();

if (!$tugas) {
    echo "Tugas tidak ditemukan atau bukan milik Anda.";
    exit;
}

// Simpan perubahan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];

    $stmt = $conn->prepare("UPDATE todos SET title = ?, description = ?, priority = ?, deadline = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssdsii", $title, $desc, $priority, $deadline, $id, $user_id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5>Edit Tugas</h5>
            </div>
            <form method="post" class="p-4">
                <div class="mb-3">
                    <label>Judul</label>
                    <input name="title" class="form-control" value="<?= $tugas['title'] ?>" required>
                </div>
                <div class="mb-3">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control"><?= $tugas['description'] ?></textarea>
                </div>
                <div class="mb-3">
                    <label>Prioritas (1-5)</label>
                    <input type="number" name="priority" min="1" max="5" class="form-control"
                        value="<?= $tugas['priority'] ?>">
                </div>
                <div class="mb-3">
                    <label>Deadline</label>
                    <input type="date" name="deadline" class="form-control" value="<?= $tugas['deadline'] ?>">
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>