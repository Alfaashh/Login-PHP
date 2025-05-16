<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/logger.php';

ensureLogsTableExists($conn);

// Log logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'logout', 'success', 'User logged out');
}

// Hapus semua session variables, destroy the session, lalu redirect ke halaman login
session_unset();
session_destroy();

header("Location: login.php?success-logout=logout");
exit;
?>
