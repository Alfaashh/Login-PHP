<?php
session_start();
require_once 'config.php';
require_once 'logger.php';

ensureLogsTableExists($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = 'user';

    // buat perilaku ketika username sudah ada
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Log failed registration
        logActivity($conn, 0, $username, 'register', 'failed', 'Username sudah digunakan');
        
        $_SESSION['error'] = "Username sudah digunakan!";
        header("Location: ../register.php");
        exit();
    }

    // buat perilaku ketika password tidak sama
    if ($password !== $confirm_password) {
        // Log failed registration
        logActivity($conn, 0, $username, 'register', 'failed', 'Password tidak cocok');
        
        $_SESSION['error'] = "Password tidak cocok!";
        header("Location: ../register.php");
        exit();
    }

    // Check password length against settings
    $min_length = 6; // Default minimum length
    $length_query = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'min_password_length' LIMIT 1");
    if ($length_query && $length_query->num_rows > 0) {
        $length_row = $length_query->fetch_assoc();
        $min_length = intval($length_row['setting_value']);
    }

    if (strlen($password) < $min_length) {
        // Log failed registration
        logActivity($conn, 0, $username, 'register', 'failed', "Password kurang dari $min_length karakter");
        
        $_SESSION['error'] = "Password harus minimal $min_length karakter!";
        header("Location: ../register.php");
        exit();
    }

    $hashed_password = md5($password);

    // buat perilaku ketika register berhasil
    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $new_user_id = $conn->insert_id;
        
        // Log successful registration
        logActivity($conn, $new_user_id, $username, 'register', 'success', 'Registrasi pengguna baru');
        
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        header("Location: ../login.php");
        exit();
    } else {
        // Log failed registration
        logActivity($conn, 0, $username, 'register', 'failed', 'Error database: ' . $conn->error);
        
        $_SESSION['error'] = "Terjadi kesalahan saat registrasi. Silakan coba lagi.";
        header("Location: ../register.php");
        exit();
    }
}
?>
