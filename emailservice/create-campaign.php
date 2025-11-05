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
            <a href="campaigns.php" class="nav-link">Campaigns</a>
            <a href="subscribers.php" class="nav-link">Subscribers</a>
            <a href="create-campaign.php" class="nav-link active">Create Campaign</a>
            <div class="nav-user">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                <a href="../login/logout.php" class="nav-link logout">Logout</a>
            </div>
        </nav>
    </header>

    <main class="email-main">
        <div class="campaign-form-container">
            <form id="campaign-form" class="campaign-form">
                <div class="form-group">
                    <label for="subject">Subject Line *</label>
                    <input type="text" id="subject" name="subject" required placeholder="Enter email subject...">
                </div>

                <div class="form-group">
                    <label for="from_name">From Name</label>
                    <input type="text" id="from_name" name="from_name" value="The Framers Method" placeholder="Your name or organization">
                </div>

                <div class="form-group">
                    <label for="content_type">Content Type</label>
                    <select id="content_type" name="content_type">
                        <option value="html">HTML</option>
                        <option value="plain">Plain Text</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Email Content *</label>
                    <textarea id="content" name="content" required rows="15" placeholder="Enter your email content here...

You can use these placeholders:
{subscriber_name} - Will be replaced with the subscriber's name
{subscriber_email} - Will be replaced with the subscriber's email"></textarea>
                </div>

                <div class="form-group">
                    <label for="send_to">Send To</label>
                    <select id="send_to" name="send_to">
                        <option value="all">All Subscribers</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="send_timing">Send Timing</label>
                    <select id="send_timing" name="send_timing">
                        <option value="immediately">Send Immediately</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" id="save-draft" class="btn btn-secondary">Save Draft</button>
                    <button type="button" id="send-test" class="btn btn-warning">Send Test</button>
                    <button type="button" id="send-campaign" class="btn btn-primary">Send Campaign</button>
                </div>
            </form>

            <!-- Test Email Modal -->
            <div id="test-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="hideTestModal()">&times;</span>
                    <h3>Send Test Email</h3>
                    <div class="form-group">
                        <label for="test_email">Test Email Address</label>
                        <input type="email" id="test_email" placeholder="Enter email to send test to...">
                    </div>
                    <div class="modal-actions">
                        <button type="button" onclick="hideTestModal()" class="btn btn-secondary">Cancel</button>
                        <button type="button" onclick="sendTestEmail()" class="btn btn-primary">Send Test</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="campaign-preview">
            <h3>Preview</h3>
            <div id="preview-content" class="preview-box">
                <p><em>Your email preview will appear here...</em></p>
            </div>
        </div>
    </main>

    <script>
        // Update preview as user types
        document.getElementById('content').addEventListener('input', updatePreview);
        document.getElementById('content_type').addEventListener('change', updatePreview);

        function updatePreview() {
            const content = document.getElementById('content').value;
            const contentType = document.getElementById('content_type').value;
            const previewBox = document.getElementById('preview-content');
            
            if (content.trim() === '') {
                previewBox.innerHTML = '<p><em>Your email preview will appear here...</em></p>';
                return;
            }
            
            // Replace placeholders for preview
            let previewContent = content
                .replace(/{subscriber_name}/g, 'Valued Subscriber')
                .replace(/{subscriber_email}/g, 'subscriber@example.com');
            
            if (contentType === 'html') {
                previewBox.innerHTML = previewContent;
            } else {
                previewBox.innerHTML = `<pre>${previewContent}</pre>`;
            }
        }

        // Save draft
        document.getElementById('save-draft').addEventListener('click', async function() {
            const formData = getFormData();
            formData.status = 'draft';
            
            try {
                const response = await fetch('campaign-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Campaign saved as draft!');
                    window.location.href = 'campaigns.php';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error saving campaign: ' + error.message);
            }
        });

        // Send test email
        document.getElementById('send-test').addEventListener('click', function() {
            const subject = document.getElementById('subject').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (!subject || !content) {
                alert('Please fill in subject and content before sending test.');
                return;
            }
            
            document.getElementById('test-modal').style.display = 'block';
        });

        function hideTestModal() {
            document.getElementById('test-modal').style.display = 'none';
        }

        async function sendTestEmail() {
            const testEmail = document.getElementById('test_email').value.trim();
            
            if (!testEmail) {
                alert('Please enter a test email address.');
                return;
            }
            
            const formData = getFormData();
            formData.test_email = testEmail;
            
            try {
                const response = await fetch('campaign-api.php?action=test', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Test email sent successfully to ' + testEmail);
                    hideTestModal();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error sending test: ' + error.message);
            }
        }

        // Send campaign
        document.getElementById('send-campaign').addEventListener('click', async function() {
            if (!confirm('Send this campaign to all subscribers? This action cannot be undone.')) {
                return;
            }
            
            const formData = getFormData();
            formData.status = 'draft';
            
            try {
                // First save the campaign
                const saveResponse = await fetch('campaign-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const saveResult = await saveResponse.json();
                
                if (!saveResult.success) {
                    alert('Error saving campaign: ' + saveResult.message);
                    return;
                }
                
                // Then send it
                const sendResponse = await fetch('campaign-api.php?action=send', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ campaign_id: saveResult.data.id })
                });
                
                const sendResult = await sendResponse.json();
                
                if (sendResult.success) {
                    alert(`Campaign sent successfully!\n\nSent: ${sendResult.data.sent_count}\nFailed: ${sendResult.data.failed_count}`);
                    window.location.href = 'campaigns.php';
                } else {
                    alert('Error sending campaign: ' + sendResult.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        function getFormData() {
            return {
                subject: document.getElementById('subject').value.trim(),
                content: document.getElementById('content').value.trim(),
                content_type: document.getElementById('content_type').value,
                from_name: document.getElementById('from_name').value.trim(),
                recipient_type: document.getElementById('send_to').value,
                send_timing: document.getElementById('send_timing').value
            };
        }

        // Initialize preview
        updatePreview();
    </script>
</body>
</html>