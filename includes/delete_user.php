<?php
require_once 'config.php';
require_once 'logger.php';
require_once '../auth_check.php';

ensureLogsTableExists($conn);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
if ($id && $id != 1) {
    // Get username before deletion for logging
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $username = $user ? $user['username'] : 'unknown';
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // Log user deletion
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'delete_user', 'success', "Menghapus pengguna: $username (ID: $id)");
    } else {
        // Log failed deletion
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'delete_user', 'failed', "Gagal menghapus pengguna: $username (ID: $id). Error: " . $conn->error);
    }
}
header("Location: ../manage_user.php");
exit;
