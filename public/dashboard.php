<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

require '../config/db.php';
$stmt = $pdo->query("SELECT * FROM containers");
$containers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>eSeal Alert Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>eSeal Alert</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="tamper.php">Simulate Tamper</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?? 'user' ?>)</h2>
    <h3>Container Status Monitoring</h3>

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
                <td><?= $c['status'] == 'Tampered' ? '❌ Tampered' : '✅ Sealed' ?></td>
                <td><?= $c['updated_at'] ?></td>
                <td><?= htmlspecialchars($c['location']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
