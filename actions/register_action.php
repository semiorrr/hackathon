<?php
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? 'staff';

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");

    try {
        $stmt->execute([$username, $password, $role]);

        // ✅ Success Modal
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Registration Successful</title>
            <link rel="stylesheet" href="../public/style.css">
        </head>
        <body>
            <div class="modal">
                <div class="modal-content">
                    <h2 style="color: green;">✅ Registration successful!</h2>
                    <button onclick="window.location.href=\'../public/login.php\'">Login Now</button>
                </div>
            </div>
        </body>
        </html>';

    } catch (PDOException $e) {
        $message = ($e->getCode() == 23000)
            ? "❌ Username already exists."
            : "❌ Registration failed: " . htmlspecialchars($e->getMessage());

        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Registration Error</title>
            <link rel="stylesheet" href="../public/style.css">
        </head>
        <body>
            <div class="modal">
                <div class="modal-content">
                    <h2 style="color: red;">' . $message . '</h2>
                    <button onclick="window.location.href=\'../public/register.php\'">Try Again</button>
                </div>
            </div>
        </body>
        </html>';
    }
}
?>
