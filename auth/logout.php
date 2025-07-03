<?php
require_once '../config/koneksi.php';

// Hancurkan semua session
session_unset();
session_destroy();

header('Location: ../index.php');
exit();
?>