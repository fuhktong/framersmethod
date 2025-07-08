<?php
// Protect this page with authentication
require_once '../login/auth_check.php';
$currentUser = getCurrentUser();
?>
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
            <a href="analytics.php" class="nav-link">Analytics</a>
            <a href="templates.php" class="nav-link">Templates</a>
            <a href="bounce-management.php" class="nav-link">Bounces</a>
            <a href="create-campaign.php" class="nav-link">Create Campaign</a>
            <div class="nav-user">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                <a href="../login/logout.php" class="nav-link logout">Logout</a>
            </div>
        </nav>
    </header>

    <main class="email-main">
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3>Total Subscribers</h3>
                <p class="stat-number" id="total-subscribers">Loading...</p>
            </div>
            
            <div class="stat-card">
                <h3>Total Campaigns</h3>
                <p class="stat-number" id="total-campaigns">Loading...</p>
            </div>
            
            <div class="stat-card">
                <h3>Emails Sent</h3>
                <p class="stat-number" id="total-sent">Loading...</p>
            </div>
            
            <div class="stat-card">
                <h3>Recent Activity</h3>
                <p class="stat-text" id="recent-activity">Loading...</p>
            </div>
        </div>

        <div class="action-buttons">
            <a href="create-campaign.php" class="btn btn-primary">Create New Campaign</a>
            <a href="subscribers.php" class="btn btn-secondary">Manage Subscribers</a>
            <a href="campaigns.php" class="btn btn-secondary">View Campaigns</a>
        </div>

        <div class="recent-campaigns">
            <h2>Recent Campaigns</h2>
            <div class="campaigns-list" id="recent-campaigns-list">
                <p class="no-data">Loading recent campaigns...</p>
            </div>
        </div>
    </main>

    <script>
        // Load dashboard data
        async function loadDashboardData() {
            try {
                const response = await fetch('data-service.php?action=dashboard');
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    
                    // Update stats
                    document.getElementById('total-subscribers').textContent = data.total_subscribers;
                    document.getElementById('total-campaigns').textContent = data.total_campaigns;
                    document.getElementById('total-sent').textContent = data.total_sent;
                    
                    // Update recent activity
                    const recentActivity = document.getElementById('recent-activity');
                    if (data.recent_campaigns.length > 0) {
                        const latest = data.recent_campaigns[0];
                        recentActivity.textContent = `Latest: "${latest.subject}" (${latest.status})`;
                    } else {
                        recentActivity.textContent = 'No recent activity';
                    }
                    
                    // Update recent campaigns list
                    const campaignsList = document.getElementById('recent-campaigns-list');
                    if (data.recent_campaigns.length > 0) {
                        campaignsList.innerHTML = data.recent_campaigns.map(campaign => `
                            <div class="campaign-item">
                                <h4>${campaign.subject}</h4>
                                <p>Status: <span class="status ${campaign.status}">${campaign.status}</span></p>
                                <p>Created: ${new Date(campaign.created_at).toLocaleDateString()}</p>
                                ${campaign.sent_at ? `<p>Sent: ${new Date(campaign.sent_at).toLocaleDateString()}</p>` : ''}
                            </div>
                        `).join('');
                    } else {
                        campaignsList.innerHTML = '<p class="no-data">No campaigns yet. <a href="create-campaign.php">Create your first campaign</a></p>';
                    }
                    
                } else {
                    console.error('Failed to load dashboard data:', result.message);
                    document.getElementById('total-subscribers').textContent = 'Error';
                    document.getElementById('total-campaigns').textContent = 'Error';
                    document.getElementById('total-sent').textContent = 'Error';
                    document.getElementById('recent-activity').textContent = 'Error loading data';
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                document.getElementById('total-subscribers').textContent = 'Error';
                document.getElementById('total-campaigns').textContent = 'Error';
                document.getElementById('total-sent').textContent = 'Error';
                document.getElementById('recent-activity').textContent = 'Error loading data';
            }
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', loadDashboardData);
    </script>
</body>
</html>