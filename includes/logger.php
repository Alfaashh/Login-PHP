<?php

function logActivity($conn, $user_id, $username, $action, $status, $details = '') {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, status, ip_address, details) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $username, $action, $status, $ip_address, $details);
    $stmt->execute();
}

function ensureLogsTableExists($conn) {
    $check_table = $conn->query("SHOW TABLES LIKE 'logs'");
    if ($check_table->num_rows == 0) {
        $create_table = "CREATE TABLE logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            username VARCHAR(50),
            action VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL,    
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($create_table);
    }
}
