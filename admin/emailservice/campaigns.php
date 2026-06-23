<?php
// Protect this page with authentication
require_once __DIR__ . '/../auth.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns - Email Service</title>
    <link rel="stylesheet" href="/admin/admin.css">
</head>
<body class="admin-page">
    <?php include __DIR__ . '/../partials/header.php'; ?>

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
                        <th class="col-action"></th>
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

    <script src="campaign-actions.js"></script>
    <script>
        let campaigns = [];
        let currentPage = 1;
        let totalPages = 1;

        // Refresh behaviour for the shared campaign-actions.js handlers
        function afterCampaignAction(action) {
            loadCampaigns();
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

                    document.getElementById('campaigns-count').textContent = `All Campaigns (${data.total})`;
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

        function updateCampaignsTable(campaignData) {
            const tbody = document.getElementById('campaigns-tbody');
            if (campaignData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="no-data">No campaigns found. <a href="create-campaign.php">Create your first campaign</a></td></tr>';
                return;
            }

            tbody.innerHTML = campaignData.map(campaign => {
                const opens = campaign.total_sent > 0 ? Math.round((campaign.total_opened / campaign.total_sent) * 100) : 0;
                const clicks = campaign.total_sent > 0 ? Math.round((campaign.total_clicked / campaign.total_sent) * 100) : 0;

                // Status-aware single inline action; everything else lives on the campaign page
                let rowAction = '';
                if (campaign.status === 'draft') {
                    rowAction = `<button class="row-action row-action-send" onclick="sendCampaign(${campaign.id}, this)">Send</button>`;
                } else if (campaign.status === 'scheduled') {
                    rowAction = `<button class="row-action row-action-send" onclick="sendCampaign(${campaign.id}, this)">Send now</button>`;
                } else if (campaign.status === 'sent' || campaign.status === 'partial') {
                    rowAction = `<a class="row-action" href="campaign-report.php?id=${campaign.id}">Report</a>`;
                }

                return `
                <tr>
                    <td><a class="campaign-link" href="campaign.php?id=${campaign.id}">${campaign.subject}</a></td>
                    <td><span class="status ${campaign.status}">${campaign.status}</span></td>
                    <td>${campaign.total_recipients || 0}</td>
                    <td>${campaign.total_sent || 0}</td>
                    <td>${campaign.total_opened || 0} (${opens}%)</td>
                    <td>${campaign.total_clicked || 0} (${clicks}%)</td>
                    <td>${new Date(campaign.created_at).toLocaleDateString()}</td>
                    <td class="col-action">${rowAction}</td>
                </tr>`;
            }).join('');
        }

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            currentPage = 1;
            loadCampaigns();
        });

        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            currentPage = 1;
            loadCampaigns();
        });

        // Date filter
        document.getElementById('date-filter').addEventListener('change', function() {
            currentPage = 1;
            loadCampaigns();
        });

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', loadCampaigns);
    </script>
</body>
</html>
