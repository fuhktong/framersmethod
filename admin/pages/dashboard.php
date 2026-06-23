<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard — The Framers' Method</title>
    <link rel="stylesheet" href="/admin/admin.css" />
</head>
<body class="admin-page">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="admin-main">
        <h2>Dashboard</h2>
        <p class="admin-welcome">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'admin'); ?>.</p>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Posts</h3>
                <p>Write, edit, and publish articles to the site newsfeed.</p>
                <a href="/admin/posts" class="admin-btn">Manage Posts</a>
            </div>
            <div class="dashboard-card">
                <h3>Email Campaigns</h3>
                <p>Create and send email campaigns to subscribers.</p>
                <a href="/admin/emailservice/campaigns.php" class="admin-btn">Manage Campaigns</a>
            </div>
            <div class="dashboard-card">
                <h3>Subscribers</h3>
                <p>View and manage your email subscriber list.</p>
                <a href="/admin/emailservice/subscribers.php" class="admin-btn">Manage Subscribers</a>
            </div>
        </div>
    </main>
</body>
</html>
