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
            <a href="create-campaign.php" class="nav-link active">Create Campaign</a>
        </nav>
    </header>

    <main class="email-main">
        <div class="form-container">
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

        // Update preview
        document.getElementById('email_content').addEventListener('input', function() {
            const preview = document.getElementById('preview-content');
            const content = this.value;
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
        });

        function saveDraft() {
            alert('Draft saved! (Feature coming soon)');
        }

        function sendTest() {
            const form = document.getElementById('campaignForm');
            const formData = new FormData(form);
            formData.append('action', 'test');
            
            alert('Test email sent! (Feature coming soon)');
        }

        document.getElementById('campaignForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to send this campaign?')) {
                const formData = new FormData(this);
                formData.append('action', 'send');
                
                alert('Campaign sent! (Feature coming soon)');
            }
        });
    </script>
</body>
</html>