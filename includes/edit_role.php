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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newRole = $_POST['role'];
    $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
    $stmt->bind_param("si", $newRole, $id);
    
    if ($stmt->execute()) {
        // Get username for logging
        $user_stmt = $conn->prepare("SELECT username FROM users WHERE id=?");
        $user_stmt->bind_param("i", $id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        $username = $user ? $user['username'] : 'unknown';

        // Log role change
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'change_role', 'success', "Mengubah role pengguna $username (ID: $id) menjadi $newRole");
    } else {
        // Log failed role change
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'change_role', 'failed', "Gagal mengubah role pengguna (ID: $id). Error: " . $conn->error);
    }
    
    header("Location: ../manage_user.php");
    exit;
}

$stmt = $conn->prepare("SELECT username, role FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Role</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="form-container">
        <h2>Edit Role - <?= htmlspecialchars($user['username']) ?></h2>
        <form method="post">
            <div class="form-group">
                <label>Role Baru</label>
                <select name="role" required>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <button type="submit" class="btn">Simpan</button>
        </form>
        <button class="btn" onclick="window.location.href='../manage_user.php'">Batal</button>
    </div>
</div>
</body>
</html>
