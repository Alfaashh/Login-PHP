<?php
require_once 'includes/config.php';
require_once 'auth_check.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil data user selain admin utama (id = 1)
$query = "SELECT id, username, email, role, created_at FROM users WHERE id != 1 ORDER BY created_at ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengguna</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .table-wrapper {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2196f3;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn-secondary {
            text-align: center;
            margin-top: 20px;
        }

        .btn-secondary a {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-secondary a:hover {
            background-color: #45a049;
        }

        .btn-action {
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 5px;
        }

        .btn-edit {
            background-color: #ff9800;
            color: white;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
        }

        .btn-edit:hover {
            background-color: #e68900;
        }

        .btn-delete:hover {
            background-color: #d32f2f;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .form-container {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1000px; /* Lebih lebar dari sebelumnya */
        }


    </style>
</head>
<body>
<div class="container">
    <div class="form-container">
        <h2>Kelola Pengguna</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['role']) ?></td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                                <td>
                                    <a href="includes/edit_role.php?id=<?= $row['id'] ?>" class="btn-action btn-edit">Edit Role</a>
                                    <a href="includes/delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="btn-action btn-delete">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Tidak ada data pengguna ditemukan.</p>
        <?php endif; ?>
        <button class="btn" onclick="window.location.href='dashboard.php'">Kembali ke Dashboard</button>
    </div>
</div>
</body>
</html>
