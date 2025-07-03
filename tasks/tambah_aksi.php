<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $task_name = $_POST['task_name'];
    $task_description = $_POST['task_description'] ?? null;
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    $stmt = $koneksi->prepare("INSERT INTO tasks (user_id, task_name, task_description, due_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $task_name, $task_description, $due_date);
    $stmt->execute();
    $stmt->close();
}

header('Location: ../dashboard.php');
exit();
?>