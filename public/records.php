<?php
session_start();
if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require '../config/db.php';

// Fetch logs from container_logs table
$logs = $pdo->query("SELECT * FROM container_logs ORDER BY action_time DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Container Interaction Records – LogiLock</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>LogiLock – Admin</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="tamper.php">Simulate Tamper</a>
        <a href="records.php">Records</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Container Interaction Logs</h2>

    <table>
        <thead>
            <tr>
                <th>Container ID</th>
                <th>Action</th>
                <th>Details</th>
                <th>Action Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['container_id']) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                        <td><?= $log['action_time'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No logs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
