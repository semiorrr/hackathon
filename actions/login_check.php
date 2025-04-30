<?php
session_start();
require '../config/db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];  
    header('Location: ../public/dashboard.php');
    exit;
} else {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login Failed</title>
        <link rel="stylesheet" href="../public/style.css">
    </head>
    <body>
        <div class="modal">
            <div class="modal-content">
                <h2 style="color: red;">❌ Invalid username or password.</h2>
                <button onclick="window.location.href=\'../public/login.php\'">Try Again</button>
            </div>
        </div>
    </body>
    </html>';
}
?>
