<?php
require_once 'includes/config.php';
require_once 'includes/logger.php';
require_once 'auth_check.php';

ensureLogsTableExists($conn);

$userId = $_SESSION['user_id'];
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $oldPass = md5($_POST['old_password']);
    $newPass = md5($_POST['new_password']);

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['password'] === $oldPass) {
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $newPass, $userId);
        if ($update->execute()) {
            // Log password change
            logActivity($conn, $userId, $_SESSION['username'], 'change_password', 'success', 'Password berhasil diubah');
            
            // Redirect langsung ke dashboard setelah berhasil ubah password
            header("Location: dashboard.php");
            exit();
        } else {
            // Log failed password change
            logActivity($conn, $userId, $_SESSION['username'], 'change_password', 'failed', 'Gagal mengubah password: ' . $conn->error);
            
            $error = "Gagal mengubah password!";
        }
    } else {
        // Log failed password change
        logActivity($conn, $userId, $_SESSION['username'], 'change_password', 'failed', 'Password lama tidak sesuai');
        
        $error = "Password lama tidak sesuai!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="form-container">
        <h2>Ubah Password</h2>

        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" name="old_password" required>
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="new_password" required>
            </div>
            <button type="submit" class="btn">Simpan</button>
        </form>
    </div>
</div>
</body>
</html>
