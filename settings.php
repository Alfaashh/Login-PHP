<?php

require_once 'auth_check.php';

// Check admin role
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $timeout = filter_input(INPUT_POST, 'session_timeout', FILTER_VALIDATE_INT);
    
    if ($timeout && $timeout >= 1) {
        // Update timeout in auth_check.php
        $config_content = file_get_contents('auth_check.php');
        $new_content = preg_replace(
            '/\$timeout_duration = \d+;/', 
            '$timeout_duration = ' . ($timeout * 60) . ';',
            $config_content
        );
        
        if (file_put_contents('auth_check.php', $new_content)) {
            $message = '<div class="success-message">Session timeout has been updated successfully!</div>';
        } else {
            $message = '<div class="error-message">Failed to update settings!</div>';
        }
    } else {
        $message = '<div class="error-message">Invalid timeout value!</div>';
    }
}

// Get current timeout value
$config_content = file_get_contents('auth_check.php');
preg_match('/\$timeout_duration = (\d+);/', $config_content, $matches);
$current_timeout = isset($matches[1]) ? intval($matches[1]) / 60 : 30; // Convert seconds to minutes
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>System Settings</h2>
            
            <?php echo $message; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Session Timeout (minutes)</label>
                    <input type="number" 
                           name="session_timeout" 
                           value="<?php echo htmlspecialchars($current_timeout); ?>" 
                           min="1"
                           class="form-control">
                    <small>Current timeout: <?php echo htmlspecialchars($current_timeout); ?> minutes</small>
                </div>
                
                <button type="submit" class="btn">Save Settings</button>
            </form>
            
            <div class="btn-secondary">
                <a href="dashboard.php">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>