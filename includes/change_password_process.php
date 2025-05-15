<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_id = $_SESSION['user_id'];

    // Validasi password baru
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Password baru dan konfirmasi password tidak cocok!";
        header("Location: ../change_password.php");
        exit();
    }

    // Cek password saat ini
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (md5($current_password) !== $user['password']) {
        $_SESSION['error'] = "Password saat ini tidak valid!";
        header("Location: ../change_password.php");
        exit();
    }

    // Update password
    $new_password_hash = md5($new_password);
    $update_query = "UPDATE users SET password = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $new_password_hash, $user_id);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Password berhasil diubah!";
    } else {
        $_SESSION['error'] = "Gagal mengubah password!";
    }

    header("Location: ../change_password.php");
    exit();
} else {
    $_SESSION['error'] = "Permintaan tidak valid!";
    header("Location: ../change_password.php");
    exit();
}
?>