<?php
include 'session.php'; // Memulai sesi dan memeriksa login
include 'db.php';     // Koneksi database

// Periksa apakah pengguna adalah admin, jika tidak, arahkan ke index.php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Pastikan ID pengguna disediakan di URL
if (isset($_GET['id'])) {
    $user_id_to_delete = (int)$_GET['id'];

    // Pencegahan agar admin tidak menghapus dirinya sendiri
    if ($user_id_to_delete === $_SESSION['user_id']) {
        // Redirect kembali ke dashboard admin dengan pesan error
        header("Location: admin_dashboard.php?error=cannot_delete_self");
        exit;
    }

    // Mulai transaksi untuk memastikan integritas data
    $conn->begin_transaction();

    try {
        // Hapus semua tugas yang terkait dengan pengguna ini terlebih dahulu
        $stmt_todos = $conn->prepare("DELETE FROM todos WHERE user_id = ?");
        $stmt_todos->bind_param("i", $user_id_to_delete);
        $stmt_todos->execute();
        $stmt_todos->close();

        // Kemudian, hapus pengguna itu sendiri
        $stmt_user = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $user_id_to_delete);
        $stmt_user->execute();
        $stmt_user->close();

        // Commit transaksi jika semuanya berhasil
        $conn->commit();
        header("Location: admin_dashboard.php?success=user_deleted");
        exit;

    } catch (mysqli_sql_exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $conn->rollback();
        // Redirect kembali ke dashboard admin dengan pesan error
        header("Location: admin_dashboard.php?error=" . urlencode("Gagal menghapus pengguna: " . $e->getMessage()));
        exit;
    }

} else {
    // Jika ID pengguna tidak disediakan, arahkan kembali ke dashboard admin
    header("Location: admin_dashboard.php?error=no_user_id");
    exit;
}
?>