<?php
session_start();
if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'security') {
    header('Location: login.php');
    exit;
}

require '../config/db.php';
$containers = $pdo->query("SELECT * FROM containers")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Dashboard – LogiLock</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>LogiLock – Security Dashboard</h1>
    <nav>
        <a href="Securitydashboard.php">Dashboard</a>
        <a href="tamper.php">Simulate Tamper</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (Security)</h2>

    <h3>Container Access Log</h3>
    <table>
        <thead>
            <tr>
                <th>Container ID</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($containers as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['container_id']) ?></td>
                <td><?= $c['status'] === 'Tampered' ? '❌ Tampered' : '✅ Sealed' ?></td>
                <td><?= $c['updated_at'] ?></td>
                <td><?= htmlspecialchars($c['location']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <p><strong>Note:</strong> Security staff can view container statuses and validate tamper logs. No modification privileges.</p>
</div>

</body>
</html>
