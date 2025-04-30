<?php
session_start();
if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require '../config/db.php';

$error = '';
$success = '';

// Handle new container submission with PIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['container_id'], $_POST['location'], $_POST['pin'])) {
    $cid = $_POST['container_id'];
    $loc = $_POST['location'];
    $pin = $_POST['pin'];

    if (!preg_match('/^\d{4}$/', $pin)) {
        $error = '❌ PIN must be a 4-digit number.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO containers (container_id, location, pin) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$cid, $loc, $pin]);
            $success = '✅ Container added successfully!';
        } catch (PDOException $e) {
            $error = '❌ Failed to add container: ' . htmlspecialchars($e->getMessage());
        }
    }
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
    <style>
        .modal {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            z-index: 1000;
            text-align: center;
        }
        .modal-content {
            position: relative;
        }
        .modal-content .close-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 14px;
            width: 24px;
            height: 24px;
            cursor: pointer;
        }
    </style>
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

    <!-- Status Messages -->
    <?php if ($error || $success): ?>
        <div class="modal" id="statusModal">
            <div class="modal-content">
                <button class="close-btn" onclick="document.getElementById('statusModal').style.display='none'">×</button>
                <h2 style="color:<?= $error ? 'red' : 'green' ?>;"><?= $error ?: $success ?></h2>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const modal = document.getElementById('statusModal');
                if (modal) modal.style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>

    <!-- Add Container Form -->
    <h3>Add New Container</h3>
    <form method="POST">
        <input type="text" name="container_id" placeholder="Container ID" required>
        <input type="text" name="location" placeholder="Location" required>
        <input type="password" name="pin" placeholder="4-digit PIN" maxlength="4" required>
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
