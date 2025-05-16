<?php
session_start();
require_once 'config.php';
require_once 'logger.php';

ensureLogsTableExists($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // buat perilaku ketika username tidak ditemukan
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Log failed login attempt
        logActivity($conn, 0, $username, 'login', 'failed', 'Username tidak ditemukan');
        
        $_SESSION['error'] = "Username tidak ditemukan!";
        header("Location: ../login.php");
        exit();
    }

    // buat perilaku ketika username ditemukan
    $user = $result->fetch_assoc();

    // buat perilaku ketika password salah
    if (md5($password) !== $user['password']) {
        // Log failed login attempt
        logActivity($conn, $user['id'], $user['username'], 'login', 'failed', 'Password salah');
        
        $_SESSION['error'] = "Password salah!";
        header("Location: ../login.php");
        exit();
    }

    // buat Perilaku ketika login berhasil
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    // Log successful login
    logActivity($conn, $user['id'], $user['username'], 'login', 'success', 'Login berhasil');
    
    header("Location: ../dashboard.php");
    exit();
} else {
    // Perilaku ketika request method tidak valid
    logActivity($conn, 0, 'unknown', 'login', 'failed', 'Metode request tidak valid');
    
    $_SESSION['error'] = "Permintaan tidak valid!";
    header("Location: ../login.php");
    exit();
}
?>
