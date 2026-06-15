<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Preferences - The Framers Method</title>
    <link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
    <div class="preferences-container">
        <div class="preferences-card">
            <div class="logo">📧 The Framers Method</div>
            
            <div id="loading-state">
                <h2 class="preferences-title">Loading...</h2>
                <p class="preferences-subtitle">Please wait while we load your preferences.</p>
            </div>
            
            <div id="preferences-form" style="display: none;">
                <div class="success-message" id="success-message"></div>
                <div class="error-message" id="error-message"></div>
                
                <h2 class="preferences-title">Email Preferences</h2>
                <p class="preferences-subtitle">
                    Customize how often you receive emails from us. You can change these settings anytime.
                </p>
                
                <div class="subscriber-info" id="subscriber-info">
                    <!-- Subscriber details will be loaded here -->
                </div>
                
                <form id="preferences-form-element">
                    <div class="preference-section">
                        <h4>📬 Email Frequency</h4>
                        <div class="frequency-options">
                            <div class="frequency-option" onclick="selectFrequency('daily')">
                                <input type="radio" name="frequency" value="daily" id="daily-option">
                                <div>
                                    <div class="option-title">Daily</div>
                                    <div class="option-description">Receive emails as they're sent (recommended for active followers)</div>
                                </div>
                            </div>
                            
                            <div class="frequency-option" onclick="selectFrequency('weekly')">
                                <input type="radio" name="frequency" value="weekly" id="weekly-option">
                                <div>
                                    <div class="option-title">Weekly</div>
                                    <div class="option-description">Receive emails no more than once per week</div>
                                </div>
                            </div>
                            
                            <div class="frequency-option" onclick="selectFrequency('biweekly')">
                                <input type="radio" name="frequency" value="biweekly" id="biweekly-option">
                                <div>
                                    <div class="option-title">Bi-weekly</div>
                                    <div class="option-description">Receive emails no more than once every two weeks</div>
                                </div>
                            </div>
                            
                            <div class="frequency-option" onclick="selectFrequency('monthly')">
                                <input type="radio" name="frequency" value="monthly" id="monthly-option">
                                <div>
                                    <div class="option-title">Monthly</div>
                                    <div class="option-description">Receive emails no more than once per month</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" id="save-button">Save Preferences</button>
                        <a href="/" class="back-link">← Back to The Framers Method</a>
                    </div>
                </form>
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
                showError('Invalid preferences link. Please use the link from your email.');
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
                setCurrentFrequency();
                showPreferencesForm();
                
            } catch (error) {
                console.error('Error loading subscriber info:', error);
                showError('Unable to load preference information. The link may be invalid or expired.');
            }
        }
        
        function displaySubscriberInfo() {
            const container = document.getElementById('subscriber-info');
            container.innerHTML = `
                <h3>📋 Your Account</h3>
                <p><strong>Email:</strong> ${subscriberData.email}</p>
                ${subscriberData.name ? `<p><strong>Name:</strong> ${subscriberData.name}</p>` : ''}
                <p><strong>Current Status:</strong> <span class="status ${subscriberData.status}">${subscriberData.status}</span></p>
                <p><strong>Member Since:</strong> ${new Date(subscriberData.subscribed_at).toLocaleDateString()}</p>
            `;
        }
        
        function setCurrentFrequency() {
            const currentFreq = subscriberData.email_frequency || 'daily';
            selectFrequency(currentFreq);
        }
        
        function showPreferencesForm() {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('preferences-form').style.display = 'block';
        }
        
        function selectFrequency(frequency) {
            // Clear previous selections
            document.querySelectorAll('.frequency-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Select current option
            const selectedOption = document.querySelector(`#${frequency}-option`).closest('.frequency-option');
            selectedOption.classList.add('selected');
            document.getElementById(`${frequency}-option`).checked = true;
        }
        
        function showError(message) {
            const errorElement = document.getElementById('error-message');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('preferences-form').style.display = 'block';
            
            // Hide success if visible
            document.getElementById('success-message').style.display = 'none';
        }
        
        function showSuccess(message) {
            const successElement = document.getElementById('success-message');
            successElement.textContent = message;
            successElement.style.display = 'block';
            
            // Hide error if visible
            document.getElementById('error-message').style.display = 'none';
        }
        
        // Form submission
        document.getElementById('preferences-form-element').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const frequency = formData.get('frequency');
            
            if (!frequency) {
                showError('Please select an email frequency.');
                return;
            }
            
            // Disable save button
            const saveButton = document.getElementById('save-button');
            const originalText = saveButton.textContent;
            saveButton.textContent = 'Saving...';
            saveButton.disabled = true;
            
            try {
                const requestData = {
                    token: unsubscribeToken,
                    action: 'reduce',
                    frequency: frequency
                };
                
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
                
                showSuccess('Your email preferences have been updated successfully!');
                
                // Update displayed frequency
                subscriberData.email_frequency = frequency;
                
            } catch (error) {
                console.error('Error saving preferences:', error);
                showError('An error occurred while saving your preferences. Please try again.');
            } finally {
                // Re-enable save button
                saveButton.textContent = originalText;
                saveButton.disabled = false;
            }
        });
        
        // Load subscriber info when page loads
        document.addEventListener('DOMContentLoaded', loadSubscriberInfo);
    </script>
</body>
</html>