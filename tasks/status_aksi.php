<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $user_id = $_SESSION['user_id'];
    $task_id = $_GET['id'];
    $status = $_GET['status'];

    // Validasi status untuk keamanan
    if ($status == 'completed' || $status == 'pending') {
        $stmt = $koneksi->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $task_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

header('Location: ../dashboard.php');
exit();
?>