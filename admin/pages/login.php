<?php
require_once __DIR__ . '/../../login/auth_check.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password && loginUser($username, $password)) {
        header('Location: /admin');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Login — The Framers' Method</title>
    <link rel="stylesheet" href="/admin/admin.css" />
</head>
<body class="admin-login-page">
    <div class="login-card">
        <h1>The Framers' Method</h1>
        <p class="login-subtitle">Admin</p>

        <?php if ($error): ?>
            <p class="login-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="/admin/login">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autocomplete="username" required />

            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required />

            <button type="submit">Sign in</button>
        </form>
    </div>
</body>
</html>
