<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
require '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['container_id'], $_POST['pin'])) {
    $id = $_POST['container_id'];
    $pin = $_POST['pin'];

    // Simulated biometric PIN verification (default: 1234)
    if ($pin === '1234') {
        $stmt = $pdo->prepare("UPDATE containers SET status = 'Tampered' WHERE container_id = ?");
        $stmt->execute([$id]);
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "❌ Invalid biometric PIN.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simulate Tamper</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>eSeal Alert</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Simulate Seal Break</h2>
    <?php if ($error): ?>
        <div class="modal">
            <div class="modal-content">
                <h2 style="color: red;"><?= $error ?></h2>
                <button onclick="window.location.href='tamper.php'">Try Again</button>
            </div>
        </div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="container_id" placeholder="Enter Container ID" required><br><br>
        <input type="password" name="pin" placeholder="Enter Biometric PIN" required><br><br>
        <button type="submit">Break Seal</button>
    </form>
</div>

</body>
</html>
