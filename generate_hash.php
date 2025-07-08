    <?php
    // Password yang ingin Anda gunakan untuk admin (misalnya 'adminpassword')
    $admin_password_plain = 'admin123'; // Ganti dengan password yang Anda inginkan

    // Menghasilkan hash dari password
    $hashed_password = password_hash($admin_password_plain, PASSWORD_BCRYPT);

    echo "Password Asli: " . $admin_password_plain . "<br>";
    echo "Password Hash: " . $hashed_password . "<br>";
    ?>