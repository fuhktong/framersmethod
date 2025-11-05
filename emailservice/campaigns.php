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
    <title>Campaigns - Email Service</title>
    <link rel="stylesheet" href="emailservice.css">
</head>
<body>
    <header class="email-header">
        <h1>Framers' Method Campaign Email History</h1>
        <nav>
            <a href="campaigns.php" class="nav-link active">Campaigns</a>
            <a href="subscribers.php" class="nav-link">Subscribers</a>
            <a href="create-campaign.php" class="nav-link">Create Campaign</a>
            <div class="nav-user">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                <a href="../login/logout.php" class="nav-link logout">Logout</a>
            </div>
        </nav>
    </header>

    <main class="email-main">
        <div class="campaigns-header">
            <h2 id="campaigns-count">All Campaigns (Loading...)</h2>
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
                <option value="partial">Partially Sent</option>
                <option value="failed">Failed</option>
                <option value="cancelled">Cancelled</option>
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
        let currentPage = 1;
        let totalPages = 1;

        function showCampaignModal(campaignId) {
            // Load campaign details
            document.getElementById('campaign-modal').style.display = 'block';
            loadCampaignDetails(campaignId);
        }

        function hideCampaignModal() {
            document.getElementById('campaign-modal').style.display = 'none';
        }

        async function loadCampaignDetails(campaignId) {
            try {
                const response = await fetch(`data-service.php?action=campaign&id=${campaignId}`);
                const result = await response.json();
                
                if (result.success) {
                    const campaign = result.data;
                    
                    document.getElementById('campaign-details').innerHTML = `
                        <h3>${campaign.subject}</h3>
                        <div class="campaign-info">
                            <div class="campaign-meta">
                                <p><strong>Status:</strong> <span class="status ${campaign.status}">${campaign.status}</span></p>
                                <p><strong>From:</strong> ${campaign.from_name}</p>
                                <p><strong>Content Type:</strong> ${campaign.content_type}</p>
                                <p><strong>Created:</strong> ${new Date(campaign.created_at).toLocaleString()}</p>
                                ${campaign.sent_at ? `<p><strong>Sent:</strong> ${new Date(campaign.sent_at).toLocaleString()}</p>` : ''}
                                <p><strong>Recipients:</strong> ${campaign.total_recipients || 0}</p>
                                <p><strong>Sent:</strong> ${campaign.total_sent || 0}</p>
                                <p><strong>Opened:</strong> ${campaign.total_opened || 0}</p>
                                <p><strong>Clicked:</strong> ${campaign.total_clicked || 0}</p>
                            </div>
                            
                            <div class="campaign-content">
                                <h4>Email Content:</h4>
                                <div class="content-preview">
                                    ${campaign.content_type === 'html' ? campaign.content : `<pre>${campaign.content}</pre>`}
                                </div>
                            </div>
                            
                            ${campaign.sends && campaign.sends.length > 0 ? `
                                <div class="campaign-sends">
                                    <h4>Recent Sends (${campaign.sends.length}):</h4>
                                    <div class="sends-list">
                                        ${campaign.sends.slice(0, 10).map(send => `
                                            <div class="send-item">
                                                <span>${send.email}</span>
                                                <span class="status ${send.status}">${send.status}</span>
                                                <span>${new Date(send.sent_at).toLocaleString()}</span>
                                            </div>
                                        `).join('')}
                                        ${campaign.sends.length > 10 ? `<p>... and ${campaign.sends.length - 10} more</p>` : ''}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                } else {
                    document.getElementById('campaign-details').innerHTML = `
                        <h3>Error</h3>
                        <p>Failed to load campaign details: ${result.message}</p>
                    `;
                }
            } catch (error) {
                document.getElementById('campaign-details').innerHTML = `
                    <h3>Error</h3>
                    <p>Error loading campaign details: ${error.message}</p>
                `;
            }
        }

        function editCampaign(campaignId) {
            window.location.href = `create-campaign.php?edit=${campaignId}`;
        }

        function duplicateCampaign(campaignId) {
            if (confirm('Duplicate this campaign?')) {
                window.location.href = `create-campaign.php?duplicate=${campaignId}`;
            }
        }

        async function deleteCampaign(campaignId) {
            if (confirm('Delete this campaign? This action cannot be undone.')) {
                try {
                    const response = await fetch(`campaign-api.php?id=${campaignId}`, {
                        method: 'DELETE'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Campaign deleted successfully!');
                        loadCampaigns(); // Refresh the list
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error deleting campaign: ' + error.message);
                }
            }
        }

        async function sendCampaign(campaignId) {
            if (confirm('Send this campaign to all active subscribers? This action cannot be undone.')) {
                try {
                    // Disable the send button to prevent double-clicking
                    const sendButton = event.target;
                    const originalText = sendButton.textContent;
                    sendButton.textContent = 'Sending...';
                    sendButton.disabled = true;
                    
                    const response = await fetch('campaign-api.php?action=send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            campaign_id: campaignId
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(`Campaign sending started!\n\nTotal Subscribers: ${result.data.total_subscribers}\nSent: ${result.data.sent_count}\nFailed: ${result.data.failed_count}\n\nStatus: ${result.data.status}`);
                        loadCampaigns(); // Refresh the list to show updated status
                    } else {
                        alert('Error sending campaign: ' + result.message);
                        // Re-enable button on error
                        sendButton.textContent = originalText;
                        sendButton.disabled = false;
                    }
                } catch (error) {
                    alert('Error sending campaign: ' + error.message);
                    // Re-enable button on error
                    const sendButton = event.target;
                    sendButton.textContent = 'Send';
                    sendButton.disabled = false;
                }
            }
        }


        async function cancelScheduledCampaign(campaignId) {
            if (confirm('Are you sure you want to cancel this scheduled campaign? It will be changed back to draft status.')) {
                try {
                    const response = await fetch(`campaign-api.php?id=${campaignId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            status: 'draft',
                            scheduled_at: null,
                            timezone: null
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Scheduled campaign cancelled successfully!');
                        loadCampaigns(); // Refresh the list
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error cancelling scheduled campaign: ' + error.message);
                }
            }
        }

        // Load campaigns data
        async function loadCampaigns() {
            try {
                const search = document.getElementById('search').value;
                const status = document.getElementById('status-filter').value;
                
                const response = await fetch(`data-service.php?action=campaigns&page=${currentPage}&search=${encodeURIComponent(search)}&status=${status}`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    campaigns = data.campaigns;
                    totalPages = data.total_pages;
                    
                    // Update campaigns count
                    document.getElementById('campaigns-count').textContent = `All Campaigns (${data.total})`;
                    
                    // Update table
                    updateCampaignsTable(data.campaigns);
                    
                } else {
                    console.error('Failed to load campaigns:', result.message);
                    document.getElementById('campaigns-tbody').innerHTML = 
                        '<tr><td colspan="8" class="no-data">Error loading campaigns</td></tr>';
                }
            } catch (error) {
                console.error('Error loading campaigns:', error);
                document.getElementById('campaigns-tbody').innerHTML = 
                    '<tr><td colspan="8" class="no-data">Error loading campaigns</td></tr>';
            }
        }

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            currentPage = 1; // Reset to first page
            loadCampaigns();
        });

        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            currentPage = 1; // Reset to first page
            loadCampaigns();
        });

        // Date filter
        document.getElementById('date-filter').addEventListener('change', function() {
            currentPage = 1; // Reset to first page
            loadCampaigns();
        });

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
                    <td>${campaign.total_recipients || 0}</td>
                    <td>${campaign.total_sent || 0}</td>
                    <td>${campaign.total_opened || 0} (${campaign.total_sent > 0 ? Math.round((campaign.total_opened/campaign.total_sent)*100) : 0}%)</td>
                    <td>${campaign.total_clicked || 0} (${campaign.total_sent > 0 ? Math.round((campaign.total_clicked/campaign.total_sent)*100) : 0}%)</td>
                    <td>${new Date(campaign.created_at).toLocaleDateString()}</td>
                    <td>
                        <button onclick="showCampaignModal(${campaign.id})" class="btn-small">View</button>
                        ${campaign.status === 'draft' ? `<button onclick="editCampaign(${campaign.id})" class="btn-small">Edit</button>` : ''}
                        ${campaign.status === 'draft' ? `<button onclick="sendCampaign(${campaign.id})" class="btn-small btn-primary">Send</button>` : ''}
                        ${campaign.status === 'scheduled' ? `<button onclick="sendCampaign(${campaign.id})" class="btn-small btn-primary">Send Now</button>` : ''}
                        ${campaign.status === 'scheduled' ? `<button onclick="cancelScheduledCampaign(${campaign.id})" class="btn-small btn-warning">Cancel Schedule</button>` : ''}
                        <button onclick="duplicateCampaign(${campaign.id})" class="btn-small">Duplicate</button>
                        ${campaign.status !== 'sent' && campaign.status !== 'sending' ? `<button onclick="deleteCampaign(${campaign.id})" class="btn-small btn-danger">Delete</button>` : ''}
                    </td>
                </tr>
            `).join('');
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', loadCampaigns);
    </script>
</body>
</html>