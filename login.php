<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Login</h2>
            <?php
            if (isset($_GET['success'])) {
                echo '<div class="success-message">Registrasi berhasil! Silakan login.</div>';
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="error-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            <form action="includes/login_process.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <p class="register-link">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</body>
</html> 