<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - The Framers Method</title>
    <link rel="stylesheet" href="emailservice.css">
    <style>
        .unsubscribe-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .unsubscribe-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #45698c;
            margin-bottom: 20px;
        }
        
        .unsubscribe-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .unsubscribe-subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.5;
        }
        
        .subscriber-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .subscriber-info h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 18px;
        }
        
        .subscriber-info p {
            margin: 5px 0;
            color: #666;
        }
        
        .unsubscribe-options {
            text-align: left;
            margin-bottom: 30px;
        }
        
        .option-group {
            margin-bottom: 20px;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .option-group:hover {
            border-color: #45698c;
            background: #f8f9fa;
        }
        
        .option-group.selected {
            border-color: #45698c;
            background: #e3f2fd;
        }
        
        .option-group input[type="radio"] {
            margin-right: 10px;
        }
        
        .option-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .option-description {
            color: #666;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .option-details {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .option-details.show {
            display: block;
        }
        
        .pause-options {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .pause-option {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .pause-option:hover,
        .pause-option.selected {
            border-color: #45698c;
            background: #e3f2fd;
        }
        
        .frequency-options {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .frequency-option {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .frequency-option:hover,
        .frequency-option.selected {
            border-color: #45698c;
            background: #e3f2fd;
        }
        
        .reason-section {
            display: none;
            margin-top: 20px;
            text-align: left;
        }
        
        .reason-section.show {
            display: block;
        }
        
        .reason-section h4 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .reason-options {
            display: grid;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .reason-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .reason-option input[type="checkbox"] {
            margin: 0;
        }
        
        .custom-reason {
            margin-top: 15px;
        }
        
        .custom-reason textarea {
            width: 100%;
            min-height: 80px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .success-message {
            display: none;
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
        }
        
        .error-message {
            display: none;
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            margin-bottom: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #45698c;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .unsubscribe-card {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .pause-options,
            .frequency-options {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="unsubscribe-container">
        <div class="unsubscribe-card">
            <div class="logo">📧 The Framers Method</div>
            
            <div id="loading-state">
                <h2 class="unsubscribe-title">Loading...</h2>
                <p class="unsubscribe-subtitle">Please wait while we load your subscription details.</p>
            </div>
            
            <div id="unsubscribe-form" style="display: none;">
                <div class="success-message" id="success-message"></div>
                <div class="error-message" id="error-message"></div>
                
                <h2 class="unsubscribe-title">Manage Your Subscription</h2>
                <p class="unsubscribe-subtitle">
                    We're sorry to see you go! Please let us know how you'd like to proceed with your subscription.
                </p>
                
                <div class="subscriber-info" id="subscriber-info">
                    <!-- Subscriber details will be loaded here -->
                </div>
                
                <form id="unsubscribe-action-form">
                    <div class="unsubscribe-options">
                        <div class="option-group" onclick="selectOption('pause')">
                            <input type="radio" name="action" value="pause" id="pause-option">
                            <div>
                                <div class="option-title">📅 Pause for a while</div>
                                <div class="option-description">Take a break for 30, 60, or 90 days, then automatically resume your subscription.</div>
                                <div class="option-details" id="pause-details">
                                    <label>Pause Duration:</label>
                                    <div class="pause-options">
                                        <div class="pause-option" onclick="selectPauseDuration(30)" data-days="30">30 Days</div>
                                        <div class="pause-option selected" onclick="selectPauseDuration(60)" data-days="60">60 Days</div>
                                        <div class="pause-option" onclick="selectPauseDuration(90)" data-days="90">90 Days</div>
                                    </div>
                                    <input type="hidden" name="pause_days" id="pause_days" value="60">
                                </div>
                            </div>
                        </div>
                        
                        <div class="option-group" onclick="selectOption('reduce')">
                            <input type="radio" name="action" value="reduce" id="reduce-option">
                            <div>
                                <div class="option-title">📉 Reduce frequency</div>
                                <div class="option-description">Receive emails less frequently - perfect if you're getting too many emails.</div>
                                <div class="option-details" id="reduce-details">
                                    <label>New Frequency:</label>
                                    <div class="frequency-options">
                                        <div class="frequency-option selected" onclick="selectFrequency('weekly')" data-freq="weekly">Weekly</div>
                                        <div class="frequency-option" onclick="selectFrequency('biweekly')" data-freq="biweekly">Bi-weekly</div>
                                        <div class="frequency-option" onclick="selectFrequency('monthly')" data-freq="monthly">Monthly</div>
                                    </div>
                                    <input type="hidden" name="frequency" id="frequency" value="weekly">
                                </div>
                            </div>
                        </div>
                        
                        <div class="option-group" onclick="selectOption('unsubscribe')">
                            <input type="radio" name="action" value="unsubscribe" id="unsubscribe-option">
                            <div>
                                <div class="option-title">❌ Unsubscribe completely</div>
                                <div class="option-description">Remove your email from all our mailing lists. You won't receive any more emails from us.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="reason-section" id="reason-section">
                        <h4>Help us improve - why are you leaving? (Optional)</h4>
                        <div class="reason-options">
                            <div class="reason-option">
                                <input type="checkbox" id="reason-too-frequent" name="reasons[]" value="too_frequent">
                                <label for="reason-too-frequent">Emails are too frequent</label>
                            </div>
                            <div class="reason-option">
                                <input type="checkbox" id="reason-not-relevant" name="reasons[]" value="not_relevant">
                                <label for="reason-not-relevant">Content is not relevant to me</label>
                            </div>
                            <div class="reason-option">
                                <input type="checkbox" id="reason-never-signed" name="reasons[]" value="never_signed">
                                <label for="reason-never-signed">I never signed up for this</label>
                            </div>
                            <div class="reason-option">
                                <input type="checkbox" id="reason-poor-quality" name="reasons[]" value="poor_quality">
                                <label for="reason-poor-quality">Poor email quality</label>
                            </div>
                            <div class="reason-option">
                                <input type="checkbox" id="reason-other" name="reasons[]" value="other">
                                <label for="reason-other">Other reason</label>
                            </div>
                        </div>
                        
                        <div class="custom-reason">
                            <label for="custom-reason-text">Additional comments (optional):</label>
                            <textarea id="custom-reason-text" name="custom_reason" placeholder="Please tell us more about your decision..."></textarea>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary" onclick="goBack()">Keep Subscription</button>
                        <button type="submit" class="btn btn-primary" id="submit-button">Confirm</button>
                    </div>
                </form>
            </div>
            
            <div id="completed-state" style="display: none;">
                <h2 class="unsubscribe-title">✅ Done!</h2>
                <p class="unsubscribe-subtitle" id="completion-message"></p>
                <a href="/" class="back-link">← Return to The Framers Method</a>
            </div>
        </div>
    </div>

    <script>
        let subscriberData = null;
        let unsubscribeToken = null;
        
        // Get token from URL
        function getUnsubscribeToken() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('token');
        }
        
        // Load subscriber information
        async function loadSubscriberInfo() {
            unsubscribeToken = getUnsubscribeToken();
            
            if (!unsubscribeToken) {
                showError('Invalid unsubscribe link. Please use the link from your email.');
                return;
            }
            
            try {
                const response = await fetch(`unsubscribe-api.php?action=info&token=${encodeURIComponent(unsubscribeToken)}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message);
                }
                
                subscriberData = result.data;
                displaySubscriberInfo();
                showUnsubscribeForm();
                
            } catch (error) {
                console.error('Error loading subscriber info:', error);
                showError('Unable to load subscription information. The link may be invalid or expired.');
            }
        }
        
        function displaySubscriberInfo() {
            const container = document.getElementById('subscriber-info');
            container.innerHTML = `
                <h3>📋 Your Subscription Details</h3>
                <p><strong>Email:</strong> ${subscriberData.email}</p>
                ${subscriberData.name ? `<p><strong>Name:</strong> ${subscriberData.name}</p>` : ''}
                <p><strong>Subscribed:</strong> ${new Date(subscriberData.subscribed_at).toLocaleDateString()}</p>
                <p><strong>Status:</strong> <span class="status ${subscriberData.status}">${subscriberData.status}</span></p>
            `;
        }
        
        function showUnsubscribeForm() {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('unsubscribe-form').style.display = 'block';
        }
        
        function selectOption(action) {
            // Clear previous selections
            document.querySelectorAll('.option-group').forEach(group => {
                group.classList.remove('selected');
                const details = group.querySelector('.option-details');
                if (details) details.classList.remove('show');
            });
            
            // Select current option
            const selectedGroup = document.querySelector(`#${action}-option`).closest('.option-group');
            selectedGroup.classList.add('selected');
            document.getElementById(`${action}-option`).checked = true;
            
            // Show option details
            const details = selectedGroup.querySelector('.option-details');
            if (details) {
                details.classList.add('show');
            }
            
            // Show/hide reason section for unsubscribe
            const reasonSection = document.getElementById('reason-section');
            if (action === 'unsubscribe') {
                reasonSection.classList.add('show');
            } else {
                reasonSection.classList.remove('show');
            }
            
            // Update submit button text
            const submitButton = document.getElementById('submit-button');
            switch(action) {
                case 'pause':
                    submitButton.textContent = 'Pause Subscription';
                    break;
                case 'reduce':
                    submitButton.textContent = 'Reduce Frequency';
                    break;
                case 'unsubscribe':
                    submitButton.textContent = 'Unsubscribe';
                    break;
            }
        }
        
        function selectPauseDuration(days) {
            document.querySelectorAll('.pause-option').forEach(opt => opt.classList.remove('selected'));
            event.target.classList.add('selected');
            document.getElementById('pause_days').value = days;
            event.stopPropagation();
        }
        
        function selectFrequency(freq) {
            document.querySelectorAll('.frequency-option').forEach(opt => opt.classList.remove('selected'));
            event.target.classList.add('selected');
            document.getElementById('frequency').value = freq;
            event.stopPropagation();
        }
        
        function goBack() {
            window.history.back();
        }
        
        function showError(message) {
            const errorElement = document.getElementById('error-message');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            
            // Hide other states
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('unsubscribe-form').style.display = 'block';
        }
        
        function showSuccess(message) {
            const successElement = document.getElementById('success-message');
            successElement.textContent = message;
            successElement.style.display = 'block';
            
            // Hide error if visible
            document.getElementById('error-message').style.display = 'none';
        }
        
        function showCompletedState(message) {
            document.getElementById('unsubscribe-form').style.display = 'none';
            document.getElementById('completed-state').style.display = 'block';
            document.getElementById('completion-message').textContent = message;
        }
        
        // Form submission
        document.getElementById('unsubscribe-action-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = formData.get('action');
            
            if (!action) {
                showError('Please select an option above.');
                return;
            }
            
            // Disable submit button
            const submitButton = document.getElementById('submit-button');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Processing...';
            submitButton.disabled = true;
            
            try {
                // Collect form data
                let requestData = {
                    token: unsubscribeToken,
                    action: action
                };
                
                // Add action-specific data
                if (action === 'pause') {
                    requestData.pause_days = parseInt(formData.get('pause_days'));
                } else if (action === 'reduce') {
                    requestData.frequency = formData.get('frequency');
                }
                
                // Collect reasons if unsubscribing
                if (action === 'unsubscribe') {
                    const reasonCheckboxes = document.querySelectorAll('input[name="reasons[]"]:checked');
                    requestData.reasons = Array.from(reasonCheckboxes).map(cb => cb.value);
                    requestData.custom_reason = document.getElementById('custom-reason-text').value.trim();
                }
                
                const response = await fetch('unsubscribe-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestData)
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message);
                }
                
                // Show completion message
                let completionMessage = result.data.message;
                showCompletedState(completionMessage);
                
            } catch (error) {
                console.error('Error processing request:', error);
                showError('An error occurred while processing your request. Please try again.');
                
                // Re-enable submit button
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            }
        });
        
        // Load subscriber info when page loads
        document.addEventListener('DOMContentLoaded', loadSubscriberInfo);
    </script>
</body>
</html>