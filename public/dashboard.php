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
<html>
<head>
    <title>eSeal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> | <a href="logout.php">Logout</a></h2>
    <h3>eSeal Alert Dashboard</h3>
    <table>
        <tr>
            <th>Container ID</th>
            <th>Status</th>
            <th>Last Updated</th>
            <th>Location</th>
        </tr>
        <?php foreach ($containers as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['container_id']) ?></td>
            <td><?= $c['status'] == 'Tampered' ? '❌ Tampered' : '✅ Sealed' ?></td>
            <td><?= $c['updated_at'] ?></td>
            <td><?= htmlspecialchars($c['location']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
