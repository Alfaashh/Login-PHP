<?php
session_start();

// Hapus semua session variables, destroy the session, lalu redirect ke halaman login
session_unset();

session_destroy();

header("Location: ../tugas-login-register/login.php?success-logout=logout");
exit;
?> 