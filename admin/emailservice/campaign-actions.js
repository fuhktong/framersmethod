/**
 * Shared campaign action handlers.
 * Used by the campaigns list (campaigns.php) and the campaign hub (campaign.php).
 *
 * Each page must define `afterCampaignAction(action)` to decide how to refresh
 * after a successful action (e.g. reload the list, or return to the list after
 * a delete). `action` is one of: 'send', 'delete', 'cancel'.
 */

async function sendCampaign(campaignId, btn) {
    if (!confirm('Send this campaign to all active subscribers? This cannot be undone.')) {
        return;
    }

    const originalText = btn ? btn.textContent : null;
    if (btn) {
        btn.textContent = 'Sending…';
        btn.disabled = true;
    }

    try {
        const response = await fetch('campaign-api.php?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ campaign_id: campaignId })
        });
        const result = await response.json();

        if (result.success) {
            const d = result.data;
            alert(`Campaign sending started!\n\nTotal Subscribers: ${d.total_subscribers}\nSent: ${d.sent_count}\nFailed: ${d.failed_count}\n\nStatus: ${d.status}`);
            afterCampaignAction('send');
        } else {
            alert('Error sending campaign: ' + result.message);
            if (btn) { btn.textContent = originalText; btn.disabled = false; }
        }
    } catch (error) {
        alert('Error sending campaign: ' + error.message);
        if (btn) { btn.textContent = originalText; btn.disabled = false; }
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
    if (!confirm('Delete this campaign? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`campaign-api.php?id=${campaignId}`, { method: 'DELETE' });
        const result = await response.json();

        if (result.success) {
            afterCampaignAction('delete');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error deleting campaign: ' + error.message);
    }
}

async function cancelScheduledCampaign(campaignId) {
    if (!confirm('Cancel this scheduled campaign? It will be changed back to draft status.')) {
        return;
    }

    try {
        const response = await fetch(`campaign-api.php?id=${campaignId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: 'draft', scheduled_at: null, timezone: null })
        });
        const result = await response.json();

        if (result.success) {
            afterCampaignAction('cancel');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error cancelling scheduled campaign: ' + error.message);
    }
}
