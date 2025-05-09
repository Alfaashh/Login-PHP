<?php
session_start();

// Cek apakah user sudah login


// Cek role user

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-container {
            text-align: center;
            padding: 20px;
        }
        .welcome-message {
            margin-bottom: 20px;
        }
        .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
        }
        .admin-panel {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .admin-panel h3 {
            color: #1976d2;
            margin-bottom: 15px;
        }
        .admin-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #2196f3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
        }
        .admin-btn:hover {
            background-color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container dashboard-container">
            <h2>Selamat Datang</h2>
            <div class="welcome-message">
                <p>Halo, ?????</p>
                <p>Role: ?????</p>
                <p>Anda telah berhasil login ke sistem.</p>
            </div>

            <?php if ($isAdmin): ?>
            <div class="admin-panel">
                <h3>Admin Panel</h3>
                <a href="#" class="admin-btn">Kelola User</a>
                <a href="#" class="admin-btn">Lihat Log</a>
                <a href="#" class="admin-btn">Pengaturan</a>
            </div>
            <?php else: ?>
            <div class="user-panel">
                <p>Selamat datang di dashboard user.</p>
                <a href="#" class="admin-btn">Profil Saya</a>
                <a href="#" class="admin-btn">Ubah Password</a>
            </div>
            <?php endif; ?>

            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</body>
</html> 