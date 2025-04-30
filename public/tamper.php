<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['container_id'])) {
    $id = $_POST['container_id'];
    $stmt = $pdo->prepare("UPDATE containers SET status = 'Tampered' WHERE container_id = ?");
    $stmt->execute([$id]);
    echo "Seal broken on container $id!";
}
?>

<form method="POST">
    <input type="text" name="container_id" placeholder="Enter Container ID">
    <button type="submit">Break Seal</button>
</form>
