<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Service Dashboard</title>
    <link rel="stylesheet" href="emailservice.css">
</head>
<body>
    <header class="email-header">
        <h1>Email Service Dashboard</h1>
        <nav>
            <a href="index.php" class="nav-link active">Dashboard</a>
            <a href="campaigns.php" class="nav-link">Campaigns</a>
            <a href="subscribers.php" class="nav-link">Subscribers</a>
            <a href="create-campaign.php" class="nav-link">Create Campaign</a>
        </nav>
    </header>

    <main class="email-main">
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3>Total Subscribers</h3>
                <p class="stat-number">0</p>
            </div>
            
            <div class="stat-card">
                <h3>Total Campaigns</h3>
                <p class="stat-number">0</p>
            </div>
            
            <div class="stat-card">
                <h3>Emails Sent</h3>
                <p class="stat-number">0</p>
            </div>
            
            <div class="stat-card">
                <h3>Recent Activity</h3>
                <p class="stat-text">No recent activity</p>
            </div>
        </div>

        <div class="action-buttons">
            <a href="create-campaign.php" class="btn btn-primary">Create New Campaign</a>
            <a href="subscribers.php" class="btn btn-secondary">Manage Subscribers</a>
            <a href="campaigns.php" class="btn btn-secondary">View Campaigns</a>
        </div>

        <div class="recent-campaigns">
            <h2>Recent Campaigns</h2>
            <div class="campaigns-list">
                <p class="no-data">No campaigns yet. <a href="create-campaign.php">Create your first campaign</a></p>
            </div>
        </div>
    </main>
</body>
</html>