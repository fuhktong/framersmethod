<?php require_once __DIR__ . '/../auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Report - Email Service</title>
    <link rel="stylesheet" href="/admin/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-page">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="email-main">
        <a href="campaigns.php" class="back-button">
            ← Back to Campaigns
        </a>
        
        <div class="report-header" id="report-header">
            <div style="text-align: center; padding: 40px; color: #666;">
                Loading campaign report...
            </div>
        </div>
        
        <div class="metrics-grid" id="metrics-grid" style="display: none;">
            <!-- Metrics will be loaded here -->
        </div>
        
        <div class="charts-grid" id="charts-grid" style="display: none;">
            <div class="chart-card">
                <h3>📊 Hourly Performance</h3>
                <div class="chart-container">
                    <canvas id="hourly-chart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3>👥 Engagement Breakdown</h3>
                <div class="chart-container">
                    <canvas id="engagement-chart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="details-section" id="timeline-section" style="display: none;">
            <h3>📈 Performance Timeline</h3>
            <div class="chart-container">
                <canvas id="timeline-chart"></canvas>
            </div>
        </div>
        
        <div class="details-section" id="links-section" style="display: none;">
            <h3>🔗 Top Clicked Links</h3>
            <div id="links-content">
                <!-- Links table will be loaded here -->
            </div>
        </div>
    </main>

    <script>
        let campaignId = null;
        let hourlyChart = null;
        let engagementChart = null;
        let timelineChart = null;
        
        // Get campaign ID from URL
        function getCampaignId() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('id');
        }
        
        // Load campaign report data
        async function loadCampaignReport() {
            campaignId = getCampaignId();
            
            if (!campaignId) {
                document.getElementById('report-header').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <h3>Error</h3>
                        <p>No campaign ID provided. <a href="campaigns.php">Return to campaigns</a></p>
                    </div>
                `;
                return;
            }
            
            try {
                const response = await fetch(`analytics-api.php?action=campaign_report&campaign_id=${campaignId}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message);
                }
                
                displayCampaignReport(result.data);
                
            } catch (error) {
                console.error('Error loading campaign report:', error);
                document.getElementById('report-header').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <h3>Error Loading Report</h3>
                        <p>${error.message}</p>
                        <a href="campaigns.php" class="btn btn-secondary">Return to Campaigns</a>
                    </div>
                `;
            }
        }
        
        function displayCampaignReport(data) {
            const campaign = data.campaign;
            
            // Display header
            document.getElementById('report-header').innerHTML = `
                <h1 class="report-title">${campaign.subject}</h1>
                <div class="report-meta">
                    <span><strong>Status:</strong> <span class="status ${campaign.status}">${campaign.status}</span></span>
                    <span><strong>Created:</strong> ${new Date(campaign.created_at).toLocaleString()}</span>
                    ${campaign.sent_at ? `<span><strong>Sent:</strong> ${new Date(campaign.sent_at).toLocaleString()}</span>` : ''}
                    <span><strong>From:</strong> ${campaign.from_name}</span>
                    <span><strong>Type:</strong> ${campaign.content_type.toUpperCase()}</span>
                </div>
            `;
            
            // Display metrics
            displayMetrics(campaign);
            
            // Display charts
            displayHourlyChart(data.hourly_performance);
            displayEngagementChart(data.engagement_levels);
            displayTimelineChart(data.timeline);
            
            // Display links
            displayTopLinks(data.top_links);
            
            // Show sections
            document.getElementById('metrics-grid').style.display = 'grid';
            document.getElementById('charts-grid').style.display = 'grid';
            document.getElementById('timeline-section').style.display = 'block';
            document.getElementById('links-section').style.display = 'block';
        }
        
        function displayMetrics(campaign) {
            const deliveryRate = campaign.actual_recipients > 0 ? 
                ((campaign.successful_sends / campaign.actual_recipients) * 100).toFixed(2) : 0;
            
            document.getElementById('metrics-grid').innerHTML = `
                <div class="metric-card">
                    <div class="metric-value">${campaign.actual_recipients || 0}</div>
                    <div class="metric-label">Total Recipients</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value">${campaign.successful_sends || 0}</div>
                    <div class="metric-label">Successfully Delivered</div>
                    <div class="metric-change">${deliveryRate}% delivery rate</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value">${campaign.unique_opens || 0}</div>
                    <div class="metric-label">Unique Opens</div>
                    <div class="metric-change">${campaign.open_rate}% open rate</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value">${campaign.unique_clicks || 0}</div>
                    <div class="metric-label">Unique Clicks</div>
                    <div class="metric-change">${campaign.click_rate}% click rate</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value">${campaign.click_to_open_rate}%</div>
                    <div class="metric-label">Click-to-Open Rate</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value">${campaign.failed_sends || 0}</div>
                    <div class="metric-label">Failed Sends</div>
                </div>
            `;
        }
        
        function displayHourlyChart(data) {
            if (!data || data.length === 0) return;
            
            const ctx = document.getElementById('hourly-chart').getContext('2d');
            
            if (hourlyChart) {
                hourlyChart.destroy();
            }
            
            const labels = data.map(item => `${item.hour}:00`);
            const opens = data.map(item => item.opens);
            const clicks = data.map(item => item.clicks);
            
            hourlyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Opens',
                        data: opens,
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    }, {
                        label: 'Clicks',
                        data: clicks,
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
            if (!data || Object.keys(data).length === 0) return;
            
            const ctx = document.getElementById('engagement-chart').getContext('2d');
            
            if (engagementChart) {
                engagementChart.destroy();
            }
            
            const labels = Object.keys(data);
            const values = Object.values(data);
            
            engagementChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            '#28a745', // High
                            '#ffc107', // Medium  
                            '#6c757d', // Low
                            '#dc3545'  // Failed
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
        
        function displayTimelineChart(data) {
            if (!data || data.length === 0) return;
            
            const ctx = document.getElementById('timeline-chart').getContext('2d');
            
            if (timelineChart) {
                timelineChart.destroy();
            }
            
            const labels = data.map(item => new Date(item.date).toLocaleDateString());
            const opens = data.map(item => item.opens);
            const clicks = data.map(item => item.clicks);
            
            timelineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Opens',
                        data: opens,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Clicks', 
                        data: clicks,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4,
                        fill: true
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
        
        function displayTopLinks(data) {
            const container = document.getElementById('links-content');
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666;">No link clicks recorded</p>';
                return;
            }
            
            const tableHTML = `
                <table class="links-table">
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th>Total Clicks</th>
                            <th>Unique Clicks</th>
                            <th>First Click</th>
                            <th>Last Click</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(link => `
                            <tr>
                                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${link.url}">
                                    <a href="${link.url}" target="_blank" style="color: #0066cc;">${link.url}</a>
                                </td>
                                <td><strong>${link.total_clicks}</strong></td>
                                <td>${link.unique_clicks}</td>
                                <td>${new Date(link.first_click).toLocaleString()}</td>
                                <td>${new Date(link.last_click).toLocaleString()}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            container.innerHTML = tableHTML;
        }
        
        // Load report when page loads
        document.addEventListener('DOMContentLoaded', loadCampaignReport);
    </script>
</body>
</html>