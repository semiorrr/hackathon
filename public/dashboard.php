<?php
session_start();
if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require '../config/db.php';

// Handle new container submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['container_id'], $_POST['location'])) {
    $cid = $_POST['container_id'];
    $loc = $_POST['location'];
    $stmt = $pdo->prepare("INSERT INTO containers (container_id, location) VALUES (?, ?)");
    $stmt->execute([$cid, $loc]);
}

// Fetch containers and users
$containers = $pdo->query("SELECT * FROM containers")->fetchAll();
$users = $pdo->query("SELECT id, username, role, created_at FROM users")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – LogiLock</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>LogiLock – Admin Dashboard</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="tamper.php">Simulate Tamper</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (Admin)</h2>

    <!-- Add Container Form -->
    <h3>Add New Container</h3>
    <form method="POST">
        <input type="text" name="container_id" placeholder="Container ID" required>
        <input type="text" name="location" placeholder="Location" required>
        <button type="submit">Add Container</button>
    </form>

    <!-- Container Table -->
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

    <!-- User List -->
    <h3>Registered Users</h3>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= ucfirst($u['role']) ?></td>
                <td><?= $u['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>
