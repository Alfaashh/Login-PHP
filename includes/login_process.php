<?php
session_start();
require_once 'config.php';

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
        $_SESSION['error'] = "Username tidak ditemukan!";
        header("Location: ../login.php");
        exit();
    }

    // buat perilaku ketika username ditemukan
    $user = $result->fetch_assoc();

    // buat perilaku ketika password salah
    if (md5($password) !== $user['password']) {
        $_SESSION['error'] = "Password salah!";
        header("Location: ../login.php");
        exit();
    }

    // buat Perilaku ketika login berhasil
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    header("Location: ../dashboard.php");
    exit();
} else {
    // Perilaku ketika request method tidak valid
    $_SESSION['error'] = "Permintaan tidak valid!";
    header("Location: ../login.php");
    exit();
}
?>