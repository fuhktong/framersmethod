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
    <title>Create Campaign - Email Service</title>
    <link rel="stylesheet" href="emailservice.css">
</head>
<body>
    <header class="email-header">
        <h1>Create Email Campaign</h1>
        <nav>
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="campaigns.php" class="nav-link">Campaigns</a>
            <a href="subscribers.php" class="nav-link">Subscribers</a>
            <a href="analytics.php" class="nav-link">Analytics</a>
            <a href="templates.php" class="nav-link">Templates</a>
            <a href="bounce-management.php" class="nav-link">Bounces</a>
            <a href="create-campaign.php" class="nav-link active">Create Campaign</a>
            <div class="nav-user">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                <a href="../login/logout.php" class="nav-link logout">Logout</a>
            </div>
        </nav>
    </header>

    <main class="email-main">
        <div class="form-container">
            <!-- Template Selection Section -->
            <div class="template-selection" id="template-selection" style="margin-bottom: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h3>📧 Start with a Template</h3>
                <p>Choose a template to make campaign creation faster and more professional:</p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                    <button type="button" class="btn btn-primary" onclick="chooseTemplate()">Browse Templates</button>
                    <button type="button" class="btn btn-secondary" onclick="startFromScratch()">Start from Scratch</button>
                </div>
                <div id="selected-template-info" style="display: none; background: white; padding: 15px; border-radius: 4px; border-left: 4px solid #28a745;">
                    <strong>Selected Template:</strong> <span id="template-name"></span>
                    <button type="button" class="btn-small btn-secondary" onclick="clearTemplate()" style="margin-left: 10px;">Change Template</button>
                </div>
            </div>
            
            <form id="campaignForm" class="campaign-form">
                <div class="form-group">
                    <label for="subject">Email Subject</label>
                    <input type="text" id="subject" name="subject" required placeholder="Enter email subject">
                </div>

                <div class="form-group">
                    <label for="from_name">From Name</label>
                    <input type="text" id="from_name" name="from_name" value="The Framers Method" placeholder="Sender name">
                </div>

                <div class="form-group">
                    <label for="content_type">Content Type</label>
                    <select id="content_type" name="content_type">
                        <option value="html">HTML</option>
                        <option value="plain">Plain Text</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email_content">Email Content</label>
                    <textarea id="email_content" name="email_content" rows="15" required placeholder="Enter your email content here..."></textarea>
                </div>

                <div class="form-group">
                    <label for="recipient_type">Send To</label>
                    <select id="recipient_type" name="recipient_type">
                        <option value="all">All Subscribers</option>
                        <option value="test">Test Email (to yourself)</option>
                    </select>
                </div>

                <div class="form-group" id="test_email_group" style="display: none;">
                    <label for="test_email">Test Email Address</label>
                    <input type="email" id="test_email" name="test_email" placeholder="Enter test email address">
                </div>

                <div class="form-group">
                    <label for="send_timing">Send Timing</label>
                    <select id="send_timing" name="send_timing">
                        <option value="now">Send Immediately</option>
                        <option value="scheduled">Schedule for Later</option>
                    </select>
                </div>

                <div class="form-group" id="schedule_group" style="display: none;">
                    <label for="scheduled_datetime">Schedule Date & Time</label>
                    <input type="datetime-local" id="scheduled_datetime" name="scheduled_datetime" class="form-control">
                    <small style="color: #666; margin-top: 5px; display: block;">
                        Time will be based on your current timezone: <span id="user-timezone">Loading...</span>
                    </small>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="saveDraft()">Save Draft</button>
                    <button type="button" class="btn btn-warning" onclick="sendTest()">Send Test</button>
                    <button type="submit" class="btn btn-primary">Send Campaign</button>
                </div>
            </form>
        </div>

        <div class="campaign-preview">
            <h3>Preview</h3>
            <div id="preview-content" class="preview-box">
                <p class="preview-placeholder">Email preview will appear here...</p>
            </div>
        </div>
    </main>

    <script>
        // Show/hide test email field
        document.getElementById('recipient_type').addEventListener('change', function() {
            const testEmailGroup = document.getElementById('test_email_group');
            if (this.value === 'test') {
                testEmailGroup.style.display = 'block';
                document.getElementById('test_email').required = true;
            } else {
                testEmailGroup.style.display = 'none';
                document.getElementById('test_email').required = false;
            }
        });

        // Show/hide schedule field
        document.getElementById('send_timing').addEventListener('change', function() {
            const scheduleGroup = document.getElementById('schedule_group');
            const submitButton = document.querySelector('button[type="submit"]');
            
            if (this.value === 'scheduled') {
                scheduleGroup.style.display = 'block';
                document.getElementById('scheduled_datetime').required = true;
                submitButton.textContent = 'Schedule Campaign';
                
                // Set minimum date to current time
                const now = new Date();
                now.setMinutes(now.getMinutes() + 5); // Minimum 5 minutes from now
                const minDateTime = now.toISOString().slice(0, 16);
                document.getElementById('scheduled_datetime').min = minDateTime;
            } else {
                scheduleGroup.style.display = 'none';
                document.getElementById('scheduled_datetime').required = false;
                submitButton.textContent = 'Send Campaign';
            }
        });

        // Display user's timezone
        function displayUserTimezone() {
            try {
                const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                document.getElementById('user-timezone').textContent = timezone;
            } catch (error) {
                document.getElementById('user-timezone').textContent = 'Unable to detect';
            }
        }

        // Update preview
        document.getElementById('email_content').addEventListener('input', updatePreview);

        async function saveDraft() {
            const form = document.getElementById('campaignForm');
            const formData = getFormData();
            formData.status = 'draft';
            
            const editId = form.dataset.editId;
            
            try {
                let response;
                
                if (editId) {
                    // Update existing campaign
                    response = await fetch(`campaign-api.php?id=${editId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    });
                } else {
                    // Create new campaign
                    response = await fetch('campaign-api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    });
                }
                
                const result = await response.json();
                
                if (result.success) {
                    if (editId) {
                        alert('Campaign updated successfully!');
                    } else {
                        alert('Draft saved successfully!');
                        // Set the edit ID for future saves
                        form.dataset.editId = result.data.id;
                        document.querySelector('h1').textContent = 'Edit Email Campaign';
                        document.querySelector('button[type="submit"]').textContent = 'Update Campaign';
                    }
                } else {
                    alert('Error saving draft: ' + result.message);
                }
            } catch (error) {
                alert('Error saving draft: ' + error.message);
            }
        }

        async function sendTest() {
            const formData = getFormData();
            
            if (formData.recipient_type !== 'test') {
                alert('Please select "Test Email" in the recipient type to send a test.');
                return;
            }
            
            if (!formData.test_email) {
                alert('Please enter a test email address.');
                return;
            }
            
            try {
                const response = await fetch('campaign-api.php?action=test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.data.message);
                } else {
                    alert('Error sending test: ' + result.message);
                }
            } catch (error) {
                alert('Error sending test: ' + error.message);
            }
        }

        function getFormData() {
            const data = {
                subject: document.getElementById('subject').value,
                content: document.getElementById('email_content').value,
                content_type: document.getElementById('content_type').value,
                from_name: document.getElementById('from_name').value,
                recipient_type: document.getElementById('recipient_type').value,
                test_email: document.getElementById('test_email').value,
                send_timing: document.getElementById('send_timing').value
            };
            
            if (data.send_timing === 'scheduled') {
                data.scheduled_datetime = document.getElementById('scheduled_datetime').value;
                data.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            }
            
            return data;
        }

        document.getElementById('campaignForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = getFormData();
            
            if (formData.recipient_type === 'test') {
                alert('This will send a test email. Use the "Send Test" button for test emails.');
                return;
            }
            
            const form = e.target;
            const editId = form.dataset.editId;
            
            if (editId) {
                // Update existing campaign
                formData.status = 'draft'; // Keep as draft when editing
                
                try {
                    const response = await fetch(`campaign-api.php?id=${editId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Campaign updated successfully!');
                        window.location.href = 'campaigns.php';
                    } else {
                        alert('Error updating campaign: ' + result.message);
                    }
                } catch (error) {
                    alert('Error updating campaign: ' + error.message);
                }
            } else {
                // Create new campaign (send immediately or schedule)
                const isScheduled = formData.send_timing === 'scheduled';
                const confirmMessage = isScheduled 
                    ? `Are you sure you want to schedule this campaign? It will be sent on ${new Date(formData.scheduled_datetime).toLocaleString()}.`
                    : 'Are you sure you want to send this campaign to all subscribers? This action cannot be undone.';
                
                if (confirm(confirmMessage)) {
                    formData.status = isScheduled ? 'scheduled' : 'draft';
                    
                    try {
                        // Create the campaign
                        const createResponse = await fetch('campaign-api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(formData)
                        });
                        
                        const createResult = await createResponse.json();
                        
                        if (createResult.success) {
                            const campaignId = createResult.data.id;
                            
                            if (isScheduled) {
                                // Just scheduled, don't send now
                                alert(`Campaign scheduled successfully!\n\nScheduled for: ${new Date(formData.scheduled_datetime).toLocaleString()}\nTimezone: ${formData.timezone}`);
                                window.location.href = 'campaigns.php';
                            } else {
                                // Send immediately
                                const sendResponse = await fetch('campaign-api.php?action=send', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        campaign_id: campaignId
                                    })
                                });
                                
                                const sendResult = await sendResponse.json();
                                
                                if (sendResult.success) {
                                    alert(`Campaign sent successfully!\n\nTotal Subscribers: ${sendResult.data.total_subscribers}\nSent: ${sendResult.data.sent_count}\nFailed: ${sendResult.data.failed_count}`);
                                    window.location.href = 'campaigns.php';
                                } else {
                                    alert('Campaign created but failed to send: ' + sendResult.message);
                                    window.location.href = 'campaigns.php';
                                }
                            }
                        } else {
                            alert('Error creating campaign: ' + createResult.message);
                        }
                    } catch (error) {
                        alert('Error creating/sending campaign: ' + error.message);
                    }
                }
            }
        });

        // Load campaign data if duplicating or editing
        async function loadCampaignData() {
            const urlParams = new URLSearchParams(window.location.search);
            const duplicateId = urlParams.get('duplicate');
            const editId = urlParams.get('edit');
            
            if (duplicateId || editId) {
                try {
                    const response = await fetch(`campaign-api.php?id=${duplicateId || editId}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        const campaign = result.data;
                        
                        // Fill form with campaign data
                        document.getElementById('subject').value = duplicateId ? `Copy of ${campaign.subject}` : campaign.subject;
                        document.getElementById('from_name').value = campaign.from_name;
                        document.getElementById('content_type').value = campaign.content_type;
                        document.getElementById('email_content').value = campaign.content;
                        
                        // Update preview
                        const preview = document.getElementById('preview-content');
                        if (campaign.content_type === 'html') {
                            preview.innerHTML = campaign.content;
                        } else {
                            preview.textContent = campaign.content;
                        }
                        
                        if (editId) {
                            // Update form for editing
                            document.querySelector('h1').textContent = 'Edit Email Campaign';
                            document.querySelector('button[type="submit"]').textContent = 'Update Campaign';
                            
                            // Store edit ID for form submission
                            const form = document.getElementById('campaignForm');
                            form.dataset.editId = editId;
                        }
                        
                    } else {
                        alert('Error loading campaign: ' + result.message);
                    }
                } catch (error) {
                    alert('Error loading campaign: ' + error.message);
                }
            }
        }

        // Template integration functions
        function chooseTemplate() {
            window.location.href = 'templates.php';
        }
        
        function startFromScratch() {
            clearTemplate();
            document.getElementById('template-selection').style.display = 'none';
        }
        
        function clearTemplate() {
            sessionStorage.removeItem('selectedTemplate');
            document.getElementById('selected-template-info').style.display = 'none';
            document.getElementById('template-selection').style.display = 'block';
            
            // Clear form fields
            document.getElementById('subject').value = '';
            document.getElementById('email_content').value = '';
            document.getElementById('content_type').value = 'html';
            updatePreview();
        }
        
        async function loadSelectedTemplate() {
            const templateData = sessionStorage.getItem('selectedTemplate');
            if (templateData) {
                try {
                    const template = JSON.parse(templateData);
                    
                    // Get full template data
                    const response = await fetch(`templates-api.php?action=get&id=${template.id}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        const templateInfo = result.data;
                        
                        // Show selected template info
                        document.getElementById('template-name').textContent = templateInfo.name;
                        document.getElementById('selected-template-info').style.display = 'block';
                        document.getElementById('template-selection').style.display = 'none';
                        
                        // Apply template with variables
                        const variables = template.variables || {};
                        let content = templateInfo.template_html;
                        
                        // Replace variables in template
                        Object.keys(variables).forEach(key => {
                            const value = variables[key] || '';
                            content = content.replace(new RegExp(`{{${key}}}`, 'g'), value);
                        });
                        
                        // Fill form
                        document.getElementById('email_content').value = content;
                        document.getElementById('content_type').value = 'html';
                        
                        // Update preview
                        updatePreview();
                        
                        // Clear session storage
                        sessionStorage.removeItem('selectedTemplate');
                    }
                } catch (error) {
                    console.error('Error loading template:', error);
                }
            }
        }
        
        function updatePreview() {
            const preview = document.getElementById('preview-content');
            const content = document.getElementById('email_content').value;
            const contentType = document.getElementById('content_type').value;
            
            if (content.trim()) {
                if (contentType === 'html') {
                    preview.innerHTML = content;
                } else {
                    preview.textContent = content;
                }
            } else {
                preview.innerHTML = '<p class="preview-placeholder">Email preview will appear here...</p>';
            }
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            displayUserTimezone();
            loadCampaignData();
            loadSelectedTemplate();
        });
    </script>
</body>
</html>