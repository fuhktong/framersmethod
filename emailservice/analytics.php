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
    <title>Analytics - Email Service</title>
    <link rel="stylesheet" href="emailservice.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .analytics-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .analytics-card h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 18px;
        }
        
        .metric-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .metric-row:last-child {
            border-bottom: none;
        }
        
        .metric-label {
            font-weight: 500;
            color: #666;
        }
        
        .metric-value {
            font-weight: bold;
            color: #333;
        }
        
        .metric-value.positive {
            color: #28a745;
        }
        
        .metric-value.negative {
            color: #dc3545;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 15px;
        }
        
        .chart-container canvas {
            max-height: 300px;
        }
        
        .analytics-filters {
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
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }
        
        .campaign-reports {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .reports-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .reports-table th,
        .reports-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .reports-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .reports-table tr:hover {
            background: #f8f9fa;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #28a745, #20c997);
            transition: width 0.3s ease;
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
            color: #0066cc;
            border-bottom-color: #0066cc;
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
        
        .url-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .url-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        .url-item:last-child {
            border-bottom: none;
        }
        
        .url-text {
            flex: 1;
            margin-right: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .url-clicks {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <header class="email-header">
        <h1>Email Analytics</h1>
        <nav>
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="campaigns.php" class="nav-link">Campaigns</a>
            <a href="subscribers.php" class="nav-link">Subscribers</a>
            <a href="analytics.php" class="nav-link active">Analytics</a>
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
        <div class="analytics-filters">
            <div class="filter-group">
                <label>Time Period</label>
                <select id="time-period" class="filter-select">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Campaign</label>
                <select id="campaign-filter" class="filter-select">
                    <option value="all">All Campaigns</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="refreshAnalytics()">Refresh Data</button>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="showTab('overview')">Overview</button>
            <button class="tab" onclick="showTab('campaigns')">Campaign Performance</button>
            <button class="tab" onclick="showTab('engagement')">Subscriber Engagement</button>
            <button class="tab" onclick="showTab('clicks')">Click Analytics</button>
        </div>

        <!-- Overview Tab -->
        <div id="overview-tab" class="tab-content active">
            <div class="analytics-grid">
                <div class="analytics-card">
                    <h3>📊 Key Metrics</h3>
                    <div id="overview-stats" class="loading">Loading...</div>
                </div>
                
                <div class="analytics-card">
                    <h3>📈 Performance Trends</h3>
                    <div class="chart-container">
                        <canvas id="trends-chart"></canvas>
                    </div>
                </div>
                
                <div class="analytics-card">
                    <h3>🎯 Engagement Rates</h3>
                    <div class="chart-container">
                        <canvas id="engagement-chart"></canvas>
                    </div>
                </div>
                
                <div class="analytics-card">
                    <h3>📅 Recent Activity</h3>
                    <div id="recent-activity" class="loading">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Campaign Performance Tab -->
        <div id="campaigns-tab" class="tab-content">
            <div class="campaign-reports">
                <h3>📋 Top Performing Campaigns</h3>
                <div id="top-campaigns" class="loading">Loading...</div>
            </div>
        </div>

        <!-- Subscriber Engagement Tab -->
        <div id="engagement-tab" class="tab-content">
            <div class="campaign-reports">
                <h3>👥 Subscriber Engagement</h3>
                <div id="subscriber-engagement" class="loading">Loading...</div>
            </div>
        </div>

        <!-- Click Analytics Tab -->
        <div id="clicks-tab" class="tab-content">
            <div class="analytics-grid">
                <div class="analytics-card">
                    <h3>🔗 Most Clicked Links</h3>
                    <div id="click-analytics" class="loading">Loading...</div>
                </div>
                
                <div class="analytics-card">
                    <h3>📊 Click Distribution</h3>
                    <div class="chart-container">
                        <canvas id="clicks-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let trendsChart = null;
        let engagementChart = null;
        let clicksChart = null;
        
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
                    loadOverviewData();
                    break;
                case 'campaigns':
                    loadTopCampaigns();
                    break;
                case 'engagement':
                    loadSubscriberEngagement();
                    break;
                case 'clicks':
                    loadClickAnalytics();
                    break;
            }
        }
        
        async function loadOverviewData() {
            try {
                const [overviewResponse, trendsResponse] = await Promise.all([
                    fetch('analytics-api.php?action=overview'),
                    fetch(`analytics-api.php?action=performance_trends&days=${document.getElementById('time-period').value}`)
                ]);
                
                const overviewResult = await overviewResponse.json();
                const trendsResult = await trendsResponse.json();
                
                if (overviewResult.success) {
                    displayOverviewStats(overviewResult.data);
                }
                
                if (trendsResult.success) {
                    displayTrendsChart(trendsResult.data);
                    displayEngagementChart(trendsResult.data);
                    displayRecentActivity(overviewResult.data.recent_activity);
                }
                
            } catch (error) {
                console.error('Error loading overview data:', error);
            }
        }
        
        function displayOverviewStats(data) {
            const container = document.getElementById('overview-stats');
            container.innerHTML = `
                <div class="metric-row">
                    <span class="metric-label">Total Campaigns</span>
                    <span class="metric-value">${data.total_campaigns}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Active Subscribers</span>
                    <span class="metric-value">${data.total_subscribers}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Emails Sent</span>
                    <span class="metric-value">${data.total_emails_sent}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Average Open Rate</span>
                    <span class="metric-value positive">${data.avg_open_rate}%</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Average Click Rate</span>
                    <span class="metric-value positive">${data.avg_click_rate}%</span>
                </div>
            `;
        }
        
        function displayTrendsChart(data) {
            const ctx = document.getElementById('trends-chart').getContext('2d');
            
            if (trendsChart) {
                trendsChart.destroy();
            }
            
            const labels = data.map(item => new Date(item.date).toLocaleDateString());
            const emailsSent = data.map(item => item.emails_sent);
            const opens = data.map(item => item.opens);
            const clicks = data.map(item => item.clicks);
            
            trendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.reverse(),
                    datasets: [{
                        label: 'Emails Sent',
                        data: emailsSent.reverse(),
                        borderColor: '#0066cc',
                        backgroundColor: 'rgba(0, 102, 204, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Opens',
                        data: opens.reverse(),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Clicks',
                        data: clicks.reverse(),
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }
        
        function displayEngagementChart(data) {
            const ctx = document.getElementById('engagement-chart').getContext('2d');
            
            if (engagementChart) {
                engagementChart.destroy();
            }
            
            const labels = data.map(item => new Date(item.date).toLocaleDateString());
            const openRates = data.map(item => item.open_rate);
            const clickRates = data.map(item => item.click_rate);
            
            engagementChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.reverse(),
                    datasets: [{
                        label: 'Open Rate %',
                        data: openRates.reverse(),
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    }, {
                        label: 'Click Rate %',
                        data: clickRates.reverse(),
                        backgroundColor: 'rgba(255, 193, 7, 0.8)',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }
        
        function displayRecentActivity(data) {
            const container = document.getElementById('recent-activity');
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666;">No recent activity</p>';
                return;
            }
            
            const html = data.slice(0, 10).map(item => `
                <div class="metric-row">
                    <span class="metric-label">${new Date(item.date).toLocaleDateString()}</span>
                    <span class="metric-value">${item.emails_sent} sent, ${item.opens} opens</span>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }
        
        async function loadTopCampaigns() {
            try {
                const response = await fetch('analytics-api.php?action=top_campaigns&limit=20');
                const result = await response.json();
                
                if (result.success) {
                    displayTopCampaigns(result.data);
                }
            } catch (error) {
                console.error('Error loading top campaigns:', error);
            }
        }
        
        function displayTopCampaigns(data) {
            const container = document.getElementById('top-campaigns');
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666;">No campaigns found</p>';
                return;
            }
            
            const tableHTML = `
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Sent Date</th>
                            <th>Recipients</th>
                            <th>Opens</th>
                            <th>Clicks</th>
                            <th>Open Rate</th>
                            <th>Click Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(campaign => `
                            <tr>
                                <td><strong>${campaign.subject}</strong></td>
                                <td>${new Date(campaign.sent_at).toLocaleDateString()}</td>
                                <td>${campaign.total_sent}</td>
                                <td>${campaign.total_opened}</td>
                                <td>${campaign.total_clicked}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="progress-bar" style="width: 60px;">
                                            <div class="progress-fill" style="width: ${campaign.open_rate}%"></div>
                                        </div>
                                        ${campaign.open_rate}%
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="progress-bar" style="width: 60px;">
                                            <div class="progress-fill" style="width: ${campaign.click_rate}%"></div>
                                        </div>
                                        ${campaign.click_rate}%
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            container.innerHTML = tableHTML;
        }
        
        async function loadSubscriberEngagement() {
            try {
                const response = await fetch('analytics-api.php?action=subscriber_engagement');
                const result = await response.json();
                
                if (result.success) {
                    displaySubscriberEngagement(result.data);
                }
            } catch (error) {
                console.error('Error loading subscriber engagement:', error);
            }
        }
        
        function displaySubscriberEngagement(data) {
            const container = document.getElementById('subscriber-engagement');
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666;">No engagement data found</p>';
                return;
            }
            
            const tableHTML = `
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Subscriber</th>
                            <th>Name</th>
                            <th>Emails Received</th>
                            <th>Emails Opened</th>
                            <th>Emails Clicked</th>
                            <th>Open Rate</th>
                            <th>Click Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.slice(0, 50).map(subscriber => `
                            <tr>
                                <td>${subscriber.email}</td>
                                <td>${subscriber.name || '-'}</td>
                                <td>${subscriber.emails_received}</td>
                                <td>${subscriber.emails_opened}</td>
                                <td>${subscriber.emails_clicked}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="progress-bar" style="width: 60px;">
                                            <div class="progress-fill" style="width: ${subscriber.open_rate}%"></div>
                                        </div>
                                        ${subscriber.open_rate}%
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="progress-bar" style="width: 60px;">
                                            <div class="progress-fill" style="width: ${subscriber.click_rate}%"></div>
                                        </div>
                                        ${subscriber.click_rate}%
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            container.innerHTML = tableHTML;
        }
        
        async function loadClickAnalytics() {
            try {
                const response = await fetch('analytics-api.php?action=click_analytics');
                const result = await response.json();
                
                if (result.success) {
                    displayClickAnalytics(result.data);
                    displayClicksChart(result.data);
                }
            } catch (error) {
                console.error('Error loading click analytics:', error);
            }
        }
        
        function displayClickAnalytics(data) {
            const container = document.getElementById('click-analytics');
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666;">No click data found</p>';
                return;
            }
            
            const html = `
                <div class="url-list">
                    ${data.slice(0, 20).map(item => `
                        <div class="url-item">
                            <div class="url-text" title="${item.url}">${item.url}</div>
                            <div class="url-clicks">${item.total_clicks} clicks</div>
                        </div>
                    `).join('')}
                </div>
            `;
            
            container.innerHTML = html;
        }
        
        function displayClicksChart(data) {
            const ctx = document.getElementById('clicks-chart').getContext('2d');
            
            if (clicksChart) {
                clicksChart.destroy();
            }
            
            const top10 = data.slice(0, 10);
            const labels = top10.map(item => {
                const url = new URL(item.url);
                return url.hostname;
            });
            const clicks = top10.map(item => item.total_clicks);
            
            clicksChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: clicks,
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                            '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                            '#4BC0C0', '#FF6384'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        function refreshAnalytics() {
            const activeTab = document.querySelector('.tab.active').textContent.toLowerCase();
            loadTabData(activeTab);
        }
        
        // Time period change handler
        document.getElementById('time-period').addEventListener('change', function() {
            refreshAnalytics();
        });
        
        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadOverviewData();
        });
    </script>
</body>
</html>