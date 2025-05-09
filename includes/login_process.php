<?php

    // buat perilaku ketika salah username atau password
    // buat perilaku ketika username tidak ditemukan
    // buat perilaku ketika password salah
    // buat perilaku ketika login berhasil
    // buat perilaku ketika login gagal
    // buat perilaku ketika login berhasil 
    
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];


    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (md5($password) === $user['password']) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];


        header("Location: ../dashboard.php");
        exit;
        } else {

            $error = "Password salah";
        }
    } else {
        $error = "Username tidak ditemukan";
    }

    header("Location: ../login.php?error=" . urlencode($error));
    exit;
}
?> 