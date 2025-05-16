<?php
require_once 'includes/config.php';
require_once 'includes/logger.php';
require_once 'auth_check.php';

// Ensure only admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ensure logs table exists
ensureLogsTableExists($conn);

// Pagination settings
$records_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filtering
$filter_username = isset($_GET['username']) ? $_GET['username'] : '';
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query with filters
$where_clauses = [];
$params = [];
$types = '';

if (!empty($filter_username)) {
    $where_clauses[] = "username LIKE ?";
    $params[] = "%$filter_username%";
    $types .= 's';
}

if (!empty($filter_action)) {
    $where_clauses[] = "action = ?";
    $params[] = $filter_action;
    $types .= 's';
}

if (!empty($filter_status)) {
    $where_clauses[] = "status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($filter_date_from)) {
    $where_clauses[] = "DATE(created_at) >= ?";
    $params[] = $filter_date_from;
    $types .= 's';
}

if (!empty($filter_date_to)) {
    $where_clauses[] = "DATE(created_at) <= ?";
    $params[] = $filter_date_to;
    $types .= 's';
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM logs $where_sql";
$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get logs with pagination
$sql = "SELECT * FROM logs $where_sql ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);

// Add pagination parameters
$params[] = $offset;
$params[] = $records_per_page;
$types .= 'ii';

$stmt->bind_param($types, ...$params);
$stmt->execute();
$logs = $stmt->get_result();

// Get unique actions and statuses for filters
$actions = $conn->query("SELECT DISTINCT action FROM logs ORDER BY action");
$statuses = $conn->query("SELECT DISTINCT status FROM logs ORDER BY status");

// Clear logs functionality
$success = $error = '';
if (isset($_POST['clear_logs']) && $_POST['clear_logs'] === 'confirm') {
    $clear_sql = "TRUNCATE TABLE logs";
    if ($conn->query($clear_sql)) {
        $success = "Log aktivitas berhasil dibersihkan!";
        // Log this action
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'clear_logs', 'success', 'Admin membersihkan semua log aktivitas');
        // Refresh the page to show empty logs
        header("Location: log_activity.php?cleared=1");
        exit;
    } else {
        $error = "Gagal membersihkan log aktivitas!";
    }
}

if (isset($_GET['cleared']) && $_GET['cleared'] == 1) {
    $success = "Log aktivitas berhasil dibersihkan!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Aktivitas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        .filter-form .form-group {
            margin-bottom: 0;
            flex: 1 1 200px;
        }
        
        .filter-form .btn {
            margin-top: 24px;
            height: 42px;
        }
        
        .filter-form label {
            font-size: 14px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #4a90e2;
        }
        
        .pagination a:hover {
            background-color: #f5f5f5;
        }
        
        .pagination .active {
            background-color: #4a90e2;
            color: white;
            border-color: #4a90e2;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-failed {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .status-warning {
            background-color: #fff8e1;
            color: #f57f17;
        }
        
        .clear-logs {
            margin-top: 20px;
            text-align: right;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 14px;
        }
        
        .btn-danger {
            background-color: #f44336;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .log-details {
            font-size: 13px;
            color: #666;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .log-details:hover {
            white-space: normal;
            word-break: break-word;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-container" style="max-width: 1000px;">
        <h2>Log Aktivitas Pengguna</h2>
        
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Filter Form -->
        <form method="get" action="" class="filter-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($filter_username) ?>">
            </div>
            
            <div class="form-group">
                <label for="action">Aksi:</label>
                <select id="action" name="action">
                    <option value="">Semua</option>
                    <?php while ($action = $actions->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($action['action']) ?>" <?= $filter_action === $action['action'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($action['action']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="">Semua</option>
                    <?php while ($status = $statuses->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($status['status']) ?>" <?= $filter_status === $status['status'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($status['status']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_from">Dari Tanggal:</label>
                <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($filter_date_from) ?>">
            </div>
            
            <div class="form-group">
                <label for="date_to">Sampai Tanggal:</label>
                <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($filter_date_to) ?>">
            </div>
            
            <button type="submit" class="btn">Filter</button>
            <button type="button" class="btn" style="background-color: #6c757d;" onclick="window.location.href='log_activity.php'">Reset</button>
        </form>
        
        <!-- Logs Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Username</th>
                        <th>Aksi</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs->num_rows > 0): ?>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                                <td><?= htmlspecialchars($log['username'] ?: 'Guest') ?></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $log['status'] === 'success' ? 'success' : ($log['status'] === 'warning' ? 'warning' : 'failed') ?>">
                                        <?= htmlspecialchars($log['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                <td class="log-details"><?= htmlspecialchars($log['details'] ?: '-') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Tidak ada data log aktivitas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($filter_username) ? '&username='.urlencode($filter_username) : '' ?><?= !empty($filter_action) ? '&action='.urlencode($filter_action) : '' ?><?= !empty($filter_status) ? '&status='.urlencode($filter_status) : '' ?><?= !empty($filter_date_from) ? '&date_from='.urlencode($filter_date_from) : '' ?><?= !empty($filter_date_to) ? '&date_to='.urlencode($filter_date_to) : '' ?>">«</a>
                    <a href="?page=<?= $page-1 ?><?= !empty($filter_username) ? '&username='.urlencode($filter_username) : '' ?><?= !empty($filter_action) ? '&action='.urlencode($filter_action) : '' ?><?= !empty($filter_status) ? '&status='.urlencode($filter_status) : '' ?><?= !empty($filter_date_from) ? '&date_from='.urlencode($filter_date_from) : '' ?><?= !empty($filter_date_to) ? '&date_to='.urlencode($filter_date_to) : '' ?>">‹</a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= !empty($filter_username) ? '&username='.urlencode($filter_username) : '' ?><?= !empty($filter_action) ? '&action='.urlencode($filter_action) : '' ?><?= !empty($filter_status) ? '&status='.urlencode($filter_status) : '' ?><?= !empty($filter_date_from) ? '&date_from='.urlencode($filter_date_from) : '' ?><?= !empty($filter_date_to) ? '&date_to='.urlencode($filter_date_to) : '' ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?><?= !empty($filter_username) ? '&username='.urlencode($filter_username) : '' ?><?= !empty($filter_action) ? '&action='.urlencode($filter_action) : '' ?><?= !empty($filter_status) ? '&status='.urlencode($filter_status) : '' ?><?= !empty($filter_date_from) ? '&date_from='.urlencode($filter_date_from) : '' ?><?= !empty($filter_date_to) ? '&date_to='.urlencode($filter_date_to) : '' ?>">›</a>
                    <a href="?page=<?= $total_pages ?><?= !empty($filter_username) ? '&username='.urlencode($filter_username) : '' ?><?= !empty($filter_action) ? '&action='.urlencode($filter_action) : '' ?><?= !empty($filter_status) ? '&status='.urlencode($filter_status) : '' ?><?= !empty($filter_date_from) ? '&date_from='.urlencode($filter_date_from) : '' ?><?= !empty($filter_date_to) ? '&date_to='.urlencode($filter_date_to) : '' ?>">»</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Clear Logs Button -->
        <div class="clear-logs">
            <button type="button" class="btn btn-danger" onclick="openClearLogsModal()">Bersihkan Log</button>
        </div>
        
        <!-- Back Button -->
        <button class="btn" style="background-color: #6c757d; margin-top: 20px;" onclick="window.location.href='dashboard.php'">Kembali ke Dashboard</button>
        
        <!-- Clear Logs Confirmation Modal -->
        <div id="clearLogsModal" class="modal">
            <div class="modal-content">
                <h3>Konfirmasi</h3>
                <p>Apakah Anda yakin ingin menghapus semua log aktivitas? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="modal-actions">
                    <form method="post" action="">
                        <input type="hidden" name="clear_logs" value="confirm">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeClearLogsModal()">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm">Hapus Semua</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openClearLogsModal() {
        document.getElementById('clearLogsModal').style.display = 'block';
    }
    
    function closeClearLogsModal() {
        document.getElementById('clearLogsModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('clearLogsModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>
</body>
</html>
