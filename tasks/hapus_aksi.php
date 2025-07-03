<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $task_id = $_GET['id'];

    $stmt = $koneksi->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: ../dashboard.php');
exit();
?>