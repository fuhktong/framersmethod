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
    <title>Campaign - Email Service</title>
    <link rel="stylesheet" href="/admin/admin.css">
</head>
<body class="admin-page">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="email-main">
        <a href="campaigns.php" class="back-button">← Back to Campaigns</a>
        <div id="campaign-detail" class="campaign-detail">
            <div class="report-header"><p>Loading campaign…</p></div>
        </div>
    </main>

    <script src="campaign-actions.js"></script>
    <script>
        const campaignId = new URLSearchParams(window.location.search).get('id');

        // Refresh behaviour for the shared campaign-actions.js handlers
        function afterCampaignAction(action) {
            if (action === 'delete') {
                window.location.href = 'campaigns.php';
            } else {
                window.location.reload();
            }
        }

        function esc(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : value;
            return div.innerHTML;
        }

        function actionBar(c) {
            const buttons = [];

            if (c.status === 'draft') {
                buttons.push(`<button class="btn btn-primary" onclick="editCampaign(${campaignId})">Edit</button>`);
                buttons.push(`<button class="btn btn-success" onclick="sendCampaign(${campaignId}, this)">Send</button>`);
            } else if (c.status === 'scheduled') {
                buttons.push(`<button class="btn btn-success" onclick="sendCampaign(${campaignId}, this)">Send now</button>`);
                buttons.push(`<button class="btn btn-warning" onclick="cancelScheduledCampaign(${campaignId})">Cancel schedule</button>`);
            }

            if (c.status === 'sent' || c.status === 'sending' || c.status === 'partial') {
                buttons.push(`<a class="btn btn-secondary" href="campaign-report.php?id=${campaignId}">View full report</a>`);
            }

            buttons.push(`<button class="btn btn-secondary" onclick="duplicateCampaign(${campaignId})">Duplicate</button>`);

            if (c.status !== 'sent' && c.status !== 'sending') {
                buttons.push(`<button class="btn btn-danger" onclick="deleteCampaign(${campaignId})">Delete</button>`);
            }

            return `<div class="detail-actions">${buttons.join('')}</div>`;
        }

        function render(c) {
            const openRate = c.total_sent > 0 ? Math.round((c.total_opened / c.total_sent) * 100) : 0;
            const clickRate = c.total_sent > 0 ? Math.round((c.total_clicked / c.total_sent) * 100) : 0;

            document.getElementById('campaign-detail').innerHTML = `
                <div class="report-header">
                    <h1 class="report-title">${esc(c.subject)}</h1>
                    <div class="report-meta">
                        <span><strong>Status:</strong> <span class="status ${esc(c.status)}">${esc(c.status)}</span></span>
                        <span><strong>From:</strong> ${esc(c.from_name)}</span>
                        <span><strong>Type:</strong> ${esc((c.content_type || '').toUpperCase())}</span>
                        <span><strong>Created:</strong> ${new Date(c.created_at).toLocaleString()}</span>
                        ${c.sent_at ? `<span><strong>Sent:</strong> ${new Date(c.sent_at).toLocaleString()}</span>` : ''}
                    </div>
                    ${actionBar(c)}
                </div>

                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-value">${c.total_recipients || 0}</div>
                        <div class="metric-label">Recipients</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">${c.total_sent || 0}</div>
                        <div class="metric-label">Sent</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">${c.total_opened || 0}</div>
                        <div class="metric-label">Opens</div>
                        <div class="metric-change">${openRate}% open rate</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">${c.total_clicked || 0}</div>
                        <div class="metric-label">Clicks</div>
                        <div class="metric-change">${clickRate}% click rate</div>
                    </div>
                </div>

                <div class="details-section">
                    <h3>Email content</h3>
                    <div class="content-preview">
                        ${c.content_type === 'html' ? c.content : `<pre>${esc(c.content)}</pre>`}
                    </div>
                </div>
            `;
        }

        async function loadCampaign() {
            const target = document.getElementById('campaign-detail');

            if (!campaignId) {
                target.innerHTML = `<div class="report-header"><p>No campaign specified. <a href="campaigns.php">Back to campaigns</a></p></div>`;
                return;
            }

            try {
                const response = await fetch(`data-service.php?action=campaign&id=${campaignId}`);
                const result = await response.json();

                if (result.success) {
                    render(result.data);
                } else {
                    target.innerHTML = `<div class="report-header"><p>Could not load campaign: ${esc(result.message)}</p></div>`;
                }
            } catch (error) {
                target.innerHTML = `<div class="report-header"><p>Error loading campaign: ${esc(error.message)}</p></div>`;
            }
        }

        document.addEventListener('DOMContentLoaded', loadCampaign);
    </script>
</body>
</html>
