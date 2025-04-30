<?php
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");

    try {
        $stmt->execute([$username, $password]);
        echo "Registration successful! <a href='../public/login.php'>Login now</a>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "Username already exists.";
        } else {
            echo "Registration failed: " . $e->getMessage();
        }
    }
}
?>
