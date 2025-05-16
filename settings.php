<?php
require_once 'auth_check.php';

// Cek role admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$message = '';

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $timeout = filter_input(INPUT_POST, 'session_timeout', FILTER_VALIDATE_INT);
    
    if ($timeout && $timeout >= 1) {
        // Update timeout di auth_check.php
        $config_content = file_get_contents('auth_check.php');
        $new_content = preg_replace(
            '/\$timeout_duration = \d+;/', 
            '$timeout_duration = ' . ($timeout * 60) . ';',
            $config_content
        );
        
        if (file_put_contents('auth_check.php', $new_content)) {
            $message = '<div class="success-message">Pengaturan timeout berhasil diperbarui!</div>';
        } else {
            $message = '<div class="error-message">Gagal memperbarui pengaturan!</div>';
        }
    } else {
        $message = '<div class="error-message">Nilai timeout tidak valid!</div>';
    }
}

// Ambil nilai timeout saat ini
$config_content = file_get_contents('auth_check.php');
preg_match('/\$timeout_duration = (\d+);/', $config_content, $matches);
$current_timeout = isset($matches[1]) ? intval($matches[1]) / 60 : 30; // Konversi detik ke menit
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Pengaturan Sistem</h2>
            
            <?php echo $message; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Session Timeout (menit)</label>
                    <input type="number" 
                           name="session_timeout" 
                           value="<?php echo htmlspecialchars($current_timeout); ?>" 
                           min="1">
                    <small>Timeout saat ini: <?php echo htmlspecialchars($current_timeout); ?> menit</small>
                </div>
                
                <button type="submit" class="btn">Simpan Pengaturan</button>
            </form>
            
            <div class="btn-secondary">
                <a href="dashboard.php">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>