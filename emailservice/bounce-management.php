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
    <title>Bounce Management - Email Service</title>
    <link rel="stylesheet" href="emailservice.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .bounce-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .bounce-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .bounce-card h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
        }
        
        .bounce-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .bounce-stat:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .stat-value {
            font-weight: bold;
            color: #333;
        }
        
        .stat-value.danger {
            color: #dc3545;
        }
        
        .stat-value.warning {
            color: #ffc107;
        }
        
        .stat-value.success {
            color: #28a745;
        }
        
        .bounce-filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .bounce-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .bounce-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .bounce-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: grid;
            grid-template-columns: 1fr 120px 100px 150px 100px;
            gap: 15px;
            align-items: center;
        }
        
        .bounce-item:last-child {
            border-bottom: none;
        }
        
        .bounce-item:hover {
            background: #f8f9fa;
        }
        
        .bounce-email {
            font-weight: 500;
            color: #333;
        }
        
        .bounce-type {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
        }
        
        .bounce-type.hard {
            background: #f8d7da;
            color: #721c24;
        }
        
        .bounce-type.soft {
            background: #fff3cd;
            color: #856404;
        }
        
        .bounce-type.complaint {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .bounce-date {
            color: #666;
            font-size: 14px;
        }
        
        .bounce-reason {
            color: #666;
            font-size: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .chart-container {
            height: 300px;
            margin-top: 15px;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 12px 24px;
            cursor: pointer;
            border: none;
            background: none;
            color: #666;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            color: #dc3545;
            border-bottom-color: #dc3545;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .manual-bounce-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        @media (max-width: 768px) {
            .bounce-item {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .bounce-filters {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <header class="email-header">
        <h1>Bounce Management</h1>
        <nav>
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="campaigns.php" class="nav-link">Campaigns</a>
            <a href="subscribers.php" class="nav-link">Subscribers</a>
            <a href="analytics.php" class="nav-link">Analytics</a>
            <a href="templates.php" class="nav-link">Templates</a>
            <a href="bounce-management.php" class="nav-link active">Bounce Management</a>
            <a href="create-campaign.php" class="nav-link">Create Campaign</a>
            <div class="nav-user">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                <a href="../login/logout.php" class="nav-link logout">Logout</a>
            </div>
        </nav>
    </header>

    <main class="email-main">
        <div class="tabs">
            <button class="tab active" onclick="showTab('overview')">Overview</button>
            <button class="tab" onclick="showTab('bounces')">Bounce List</button>
            <button class="tab" onclick="showTab('manual')">Manual Processing</button>
        </div>

        <!-- Overview Tab -->
        <div id="overview-tab" class="tab-content active">
            <div class="bounce-grid">
                <div class="bounce-card">
                    <h3>📊 Bounce Statistics (Last 30 Days)</h3>
                    <div id="bounce-stats" class="loading">Loading...</div>
                </div>
                
                <div class="bounce-card">
                    <h3>📈 Bounce Trends</h3>
                    <div class="chart-container">
                        <canvas id="bounce-trends-chart"></canvas>
                    </div>
                </div>
                
                <div class="bounce-card">
                    <h3>👥 Subscriber Status</h3>
                    <div id="subscriber-status" class="loading">Loading...</div>
                </div>
                
                <div class="bounce-card">
                    <h3>📋 Worst Performing Campaigns</h3>
                    <div id="campaign-bounce-rates" class="loading">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Bounce List Tab -->
        <div id="bounces-tab" class="tab-content">
            <div class="bounce-filters">
                <div class="filter-group">
                    <label>Bounce Type</label>
                    <select id="bounce-type-filter" class="filter-select">
                        <option value="all">All Types</option>
                        <option value="hard">Hard Bounces</option>
                        <option value="soft">Soft Bounces</option>
                        <option value="complaint">Complaints</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Email</label>
                    <input type="text" id="email-filter" placeholder="Search by email..." class="search-input">
                </div>
                <button class="btn btn-primary" onclick="loadBounces()">Filter</button>
                <button class="btn btn-secondary" onclick="exportBounces()">Export</button>
            </div>

            <div class="bounce-table">
                <div class="table-header">
                    <h3>Recent Bounces</h3>
                    <span id="bounce-count">0 bounces</span>
                </div>
                <div class="bounce-list" id="bounce-list">
                    <div class="loading">Loading bounces...</div>
                </div>
            </div>
        </div>

        <!-- Manual Processing Tab -->
        <div id="manual-tab" class="tab-content">
            <div class="manual-bounce-form">
                <h3>📝 Process Bounce Manually</h3>
                <p>Use this form to manually record bounces that weren't automatically detected.</p>
                
                <form id="manual-bounce-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bounce-email">Email Address</label>
                            <input type="email" id="bounce-email" required placeholder="subscriber@example.com">
                        </div>
                        <div class="form-group">
                            <label for="bounce-type-input">Bounce Type</label>
                            <select id="bounce-type-input" required>
                                <option value="soft">Soft Bounce</option>
                                <option value="hard">Hard Bounce</option>
                                <option value="complaint">Complaint</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bounce-reason-input">Reason</label>
                            <input type="text" id="bounce-reason-input" placeholder="Bounce reason (optional)">
                        </div>
                        <button type="submit" class="btn btn-primary">Process Bounce</button>
                    </div>
                </form>
            </div>
            
            <div class="bounce-table">
                <div class="table-header">
                    <h3>🔄 Bounced Subscribers Management</h3>
                    <p>Manage subscribers who have bounced emails</p>
                </div>
                <div id="bounced-subscribers" class="loading">Loading bounced subscribers...</div>
            </div>
        </div>
    </main>

    <script>
        let bounceStatsChart = null;
        
        // Tab management
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
            
            // Load tab-specific data
            loadTabData(tabName);
        }
        
        function loadTabData(tabName) {
            switch(tabName) {
                case 'overview':
                    loadBounceStats();
                    break;
                case 'bounces':
                    loadBounces();
                    break;
                case 'manual':
                    loadBouncedSubscribers();
                    break;
            }
        }
        
        // Load bounce statistics
        async function loadBounceStats() {
            try {
                const response = await fetch('bounce-handler.php?action=stats');
                const result = await response.json();
                
                if (result.success) {
                    displayBounceStats(result.data);
                }
            } catch (error) {
                console.error('Error loading bounce stats:', error);
            }
        }
        
        function displayBounceStats(data) {
            // Overall stats
            const statsContainer = document.getElementById('bounce-stats');
            const overall = data.overall;
            
            statsContainer.innerHTML = `
                <div class="bounce-stat">
                    <span class="stat-label">Total Bounces</span>
                    <span class="stat-value danger">${overall.total_bounces || 0}</span>
                </div>
                <div class="bounce-stat">
                    <span class="stat-label">Hard Bounces</span>
                    <span class="stat-value danger">${overall.hard_bounces || 0}</span>
                </div>
                <div class="bounce-stat">
                    <span class="stat-label">Soft Bounces</span>
                    <span class="stat-value warning">${overall.soft_bounces || 0}</span>
                </div>
                <div class="bounce-stat">
                    <span class="stat-label">Complaints</span>
                    <span class="stat-value danger">${overall.complaints || 0}</span>
                </div>
                <div class="bounce-stat">
                    <span class="stat-label">Affected Subscribers</span>
                    <span class="stat-value">${overall.affected_subscribers || 0}</span>
                </div>
            `;
            
            // Subscriber status
            const statusContainer = document.getElementById('subscriber-status');
            const statusData = data.by_subscriber_status || [];
            
            statusContainer.innerHTML = statusData.map(status => `
                <div class="bounce-stat">
                    <span class="stat-label">${status.bounce_status.replace('_', ' ').toUpperCase()}</span>
                    <span class="stat-value ${getStatusClass(status.bounce_status)}">${status.count}</span>
                </div>
            `).join('');
            
            // Campaign bounce rates
            const campaignContainer = document.getElementById('campaign-bounce-rates');
            const campaignData = data.by_campaign || [];
            
            campaignContainer.innerHTML = campaignData.slice(0, 5).map(campaign => `
                <div class="bounce-stat">
                    <span class="stat-label" title="${campaign.subject}">${campaign.subject.substring(0, 25)}...</span>
                    <span class="stat-value danger">${campaign.bounce_rate}%</span>
                </div>
            `).join('');
        }
        
        function getStatusClass(status) {
            switch(status) {
                case 'active': return 'success';
                case 'hard_bounce': return 'danger';
                case 'complaint': return 'danger';
                case 'soft_bounce': return 'warning';
                default: return '';
            }
        }
        
        // Load bounce list
        async function loadBounces() {
            try {
                const bounceType = document.getElementById('bounce-type-filter')?.value || 'all';
                const email = document.getElementById('email-filter')?.value || '';
                
                const params = new URLSearchParams({
                    action: 'list',
                    bounce_type: bounceType,
                    email: email
                });
                
                const response = await fetch(`bounce-handler.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    displayBounces(result.data);
                }
            } catch (error) {
                console.error('Error loading bounces:', error);
            }
        }
        
        function displayBounces(bounces) {
            const container = document.getElementById('bounce-list');
            const countElement = document.getElementById('bounce-count');
            
            countElement.textContent = `${bounces.length} bounces`;
            
            if (bounces.length === 0) {
                container.innerHTML = '<div class="loading">No bounces found</div>';
                return;
            }
            
            container.innerHTML = bounces.map(bounce => `
                <div class="bounce-item">
                    <div>
                        <div class="bounce-email">${bounce.email}</div>
                        <div class="bounce-reason">${bounce.bounce_reason || 'No reason provided'}</div>
                    </div>
                    <div class="bounce-type ${bounce.bounce_type}">${bounce.bounce_type.toUpperCase()}</div>
                    <div>${bounce.bounce_code || '-'}</div>
                    <div class="bounce-date">${new Date(bounce.bounced_at).toLocaleDateString()}</div>
                    <div>
                        ${bounce.campaign_subject ? `<small>${bounce.campaign_subject}</small>` : '<small>No campaign</small>'}
                    </div>
                </div>
            `).join('');
        }
        
        // Load bounced subscribers
        async function loadBouncedSubscribers() {
            try {
                // This would typically call a separate endpoint for bounced subscribers
                // For now, we'll show a placeholder
                document.getElementById('bounced-subscribers').innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #666;">
                        <p>Bounced subscribers management will show here.</p>
                        <p>This would list subscribers with bounce status and allow reactivation.</p>
                    </div>
                `;
            } catch (error) {
                console.error('Error loading bounced subscribers:', error);
            }
        }
        
        // Handle manual bounce form
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('manual-bounce-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = {
                    email: document.getElementById('bounce-email').value,
                    bounce_type: document.getElementById('bounce-type-input').value,
                    bounce_reason: document.getElementById('bounce-reason-input').value,
                    campaign_id: 0 // Manual bounces don't have campaign ID
                };
                
                try {
                    const response = await fetch('bounce-handler.php?action=process', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Bounce processed successfully!\n\n' + result.data.action_taken);
                        this.reset();
                        loadBounces(); // Refresh bounce list if we're on that tab
                    } else {
                        alert('Error processing bounce: ' + result.message);
                    }
                } catch (error) {
                    alert('Error processing bounce: ' + error.message);
                }
            });
        });
        
        // Export bounces
        function exportBounces() {
            const bounceType = document.getElementById('bounce-type-filter')?.value || 'all';
            const email = document.getElementById('email-filter')?.value || '';
            
            const params = new URLSearchParams({
                action: 'export',
                bounce_type: bounceType,
                email: email
            });
            
            // This would trigger a CSV download
            // window.location.href = `bounce-export.php?${params}`;
            alert('Export functionality would download a CSV file with bounce data.');
        }
        
        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadBounceStats();
        });
    </script>
</body>
</html>