<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns - Email Service</title>
    <link rel="stylesheet" href="emailservice.css">
</head>
<body>
    <header class="email-header">
        <h1>Campaign History</h1>
        <nav>
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="campaigns.php" class="nav-link active">Campaigns</a>
            <a href="subscribers.php" class="nav-link">Subscribers</a>
            <a href="create-campaign.php" class="nav-link">Create Campaign</a>
        </nav>
    </header>

    <main class="email-main">
        <div class="campaigns-header">
            <h2>All Campaigns (0)</h2>
            <div class="campaign-actions">
                <a href="create-campaign.php" class="btn btn-primary">Create New Campaign</a>
            </div>
        </div>

        <div class="campaigns-filters">
            <input type="text" id="search" placeholder="Search campaigns..." class="search-input">
            <select id="status-filter" class="filter-select">
                <option value="all">All Status</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="sending">Sending</option>
                <option value="failed">Failed</option>
            </select>
            <select id="date-filter" class="filter-select">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>

        <div class="campaigns-table-container">
            <table class="campaigns-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Recipients</th>
                        <th>Sent</th>
                        <th>Opens</th>
                        <th>Clicks</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="campaigns-tbody">
                    <tr>
                        <td colspan="8" class="no-data">No campaigns found. <a href="create-campaign.php">Create your first campaign</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Campaign Details Modal -->
    <div id="campaign-modal" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <span class="close" onclick="hideCampaignModal()">&times;</span>
            <div id="campaign-details">
                <!-- Campaign details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        let campaigns = [];

        function showCampaignModal(campaignId) {
            // Load campaign details
            document.getElementById('campaign-modal').style.display = 'block';
            loadCampaignDetails(campaignId);
        }

        function hideCampaignModal() {
            document.getElementById('campaign-modal').style.display = 'none';
        }

        function loadCampaignDetails(campaignId) {
            // This will be implemented with backend
            document.getElementById('campaign-details').innerHTML = `
                <h3>Campaign Details</h3>
                <p>Campaign ID: ${campaignId}</p>
                <p>Details will be loaded from database...</p>
            `;
        }

        function duplicateCampaign(campaignId) {
            if (confirm('Duplicate this campaign?')) {
                alert('Duplicate functionality coming soon');
            }
        }

        function deleteCampaign(campaignId) {
            if (confirm('Delete this campaign? This action cannot be undone.')) {
                alert('Delete functionality coming soon');
            }
        }

        function viewReport(campaignId) {
            alert('Campaign report functionality coming soon');
        }

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            // Search implementation will come with backend
        });

        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            // Filter implementation will come with backend
        });

        // Date filter
        document.getElementById('date-filter').addEventListener('change', function() {
            // Filter implementation will come with backend
        });

        // Sample data for demonstration (will be replaced with backend data)
        function loadSampleData() {
            const sampleCampaigns = [
                {
                    id: 1,
                    subject: "Welcome to The Framers Method",
                    status: "sent",
                    recipients: 1250,
                    sent: 1250,
                    opens: 312,
                    clicks: 45,
                    created: "2024-01-15",
                    sent_date: "2024-01-15"
                },
                {
                    id: 2,
                    subject: "New Article: Democracy vs Republic",
                    status: "sent",
                    recipients: 1250,
                    sent: 1250,
                    opens: 289,
                    clicks: 32,
                    created: "2024-01-10",
                    sent_date: "2024-01-10"
                }
            ];

            // This would normally come from the backend
            // updateCampaignsTable(sampleCampaigns);
        }

        function updateCampaignsTable(campaignData) {
            const tbody = document.getElementById('campaigns-tbody');
            if (campaignData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="no-data">No campaigns found. <a href="create-campaign.php">Create your first campaign</a></td></tr>';
                return;
            }

            tbody.innerHTML = campaignData.map(campaign => `
                <tr>
                    <td><strong>${campaign.subject}</strong></td>
                    <td><span class="status ${campaign.status}">${campaign.status}</span></td>
                    <td>${campaign.recipients}</td>
                    <td>${campaign.sent}</td>
                    <td>${campaign.opens} (${Math.round((campaign.opens/campaign.sent)*100)}%)</td>
                    <td>${campaign.clicks} (${Math.round((campaign.clicks/campaign.sent)*100)}%)</td>
                    <td>${campaign.created}</td>
                    <td>
                        <button onclick="showCampaignModal(${campaign.id})" class="btn-small">View</button>
                        <button onclick="duplicateCampaign(${campaign.id})" class="btn-small">Duplicate</button>
                        <button onclick="deleteCampaign(${campaign.id})" class="btn-small btn-danger">Delete</button>
                    </td>
                </tr>
            `).join('');
        }
    </script>
</body>
</html>