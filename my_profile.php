<?php
session_start();
require_once 'includes/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Ambil data user dari database
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-img {
            display: block;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 2px solid #ccc;
        }

        .profile-info {
            text-align: center;
            font-size: 16px;
            color: #333;
        }

        .profile-info p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-container">
        <h2>Profil Saya</h2>
        <img src="assets/img/user.jpg" alt="Foto Profil" class="profile-img">
        <div class="profile-info">
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>
        <button class="btn" onclick="window.location.href='dashboard.php'">Kembali ke Dashboard</button>
    </div>
</div>
</body>
</html>
