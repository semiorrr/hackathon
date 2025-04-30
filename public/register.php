<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – eSeal Alert</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>eSeal Alert</h1>
    <nav>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<div class="container">
    <h2>Create an Account</h2>
    <form method="POST" action="../actions/register_action.php">
        <input type="text" name="username" placeholder="New Username" required><br><br>
        <input type="password" name="password" placeholder="New Password" required><br><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>
