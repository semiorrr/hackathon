<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – LogiLock</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login.css"> <!-- Reuse the same login.css for styling -->
</head>
<body>

<header>
    <h1>LogiLock</h1>
    <nav>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<div class="login-container">
    <h2>Create an Account</h2>
    <form method="POST" action="../actions/register_action.php">
        <input type="text" name="username" placeholder="New Username" required><br>
        <input type="password" name="password" placeholder="New Password" required><br>
        <label for="role">Select Role:</label><br>
        <select name="role" id="role" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px;">
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
            <option value="security">Security</option>
        </select><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>
