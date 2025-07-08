/**
 * Team Modal Enhancement
 * Interactive features for team page
 */

(function() {
    'use strict';
    
    let isModalVisible = false;
    let teamModal = null;
    
    // Initialize team enhancements on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Only activate on team page
        if (window.location.pathname.includes('/team') || 
            document.querySelector('.team-template')) {
            initializeTeamFeatures();
        }
    });
    
    function initializeTeamFeatures() {
        // Add invisible double-click listeners to paragraphs containing "New Mexico"
        const teamText = document.querySelectorAll('.team-template-text p');
        
        teamText.forEach(paragraph => {
            if (paragraph.textContent.includes('New Mexico')) {
                // Add double-click listener directly to paragraph
                paragraph.addEventListener('dblclick', function(e) {
                    // Check if the actual clicked text is "New Mexico"
                    if (isNewMexicoClicked(e)) {
                        e.preventDefault();
                        showTeamModal();
                    }
                });
            }
        });
        
        // Create team modal (hidden initially)
        createTeamModal();
    }
    
    function isNewMexicoClicked(event) {
        // Get the clicked position
        const x = event.clientX;
        const y = event.clientY;
        
        // Get the element at the clicked position
        const elementAtPoint = document.elementFromPoint(x, y);
        if (!elementAtPoint) return false;
        
        // Get text content around the click
        const range = document.caretRangeFromPoint(x, y);
        if (!range) return false;
        
        // Expand range to get word(s) around click position
        const textNode = range.startContainer;
        if (textNode.nodeType !== Node.TEXT_NODE) return false;
        
        const text = textNode.textContent;
        const clickPosition = range.startOffset;
        
        // Find word boundaries around click position
        let start = clickPosition;
        let end = clickPosition;
        
        // Expand backwards to find start of "New Mexico"
        while (start > 0 && text[start - 1] !== ' ' && text[start - 1] !== ',') {
            start--;
        }
        
        // Expand forwards to find end of "New Mexico" 
        while (end < text.length && text[end] !== ' ' && text[end] !== ',' && text[end] !== '.') {
            end++;
        }
        
        // Check if we need to include the next word for "New Mexico"
        if (text.substring(start, end) === 'New' && end < text.length - 1) {
            // Skip any spaces
            while (end < text.length && text[end] === ' ') {
                end++;
            }
            // Include "Mexico"
            while (end < text.length && text[end] !== ' ' && text[end] !== ',' && text[end] !== '.') {
                end++;
            }
        }
        
        // Get the selected text
        const selectedText = text.substring(start, end).trim();
        
        // Check if clicked text contains "New Mexico"
        return selectedText.includes('New Mexico');
    }
    
    function createTeamModal() {
        // Create overlay container
        teamModal = document.createElement('div');
        teamModal.id = 'team-modal-overlay';
        teamModal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        `;
        
        // Create modal form container
        const modalContainer = document.createElement('div');
        modalContainer.style.cssText = `
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            position: relative;
            animation: slideIn 0.4s ease;
        `;
        
        // Create the modal form HTML
        modalContainer.innerHTML = `
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="font-size: 18px; color: #667eea; font-weight: 600; margin-bottom: 5px;">📧 The Framers Method</div>
                <h2 style="margin: 0; color: #333; font-size: 24px;">Admin Access</h2>
                <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">Secure login portal</p>
            </div>
            
            <div id="team-error-message" style="background: #fee; color: #c33; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c33; font-size: 14px; display: none;"></div>
            
            <form id="team-modal-form">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">Username</label>
                    <input type="text" id="team-username" required autocomplete="username" style="width: 100%; padding: 12px 16px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 30px;">
                    <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">Password</label>
                    <input type="password" id="team-password" required autocomplete="current-password" style="width: 100%; padding: 12px 16px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
                </div>
                
                <button type="submit" id="team-submit-btn" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 14px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-bottom: 15px;">
                    Sign In
                </button>
                
                <button type="button" onclick="hideTeamModal()" style="width: 100%; background: #f8f9fa; color: #666; border: 1px solid #dee2e6; padding: 12px; border-radius: 8px; font-size: 14px; cursor: pointer;">
                    Cancel
                </button>
            </form>
            
            <div id="team-loading" style="display: none; text-align: center; color: #666; margin-top: 15px; font-size: 14px;">
                Authenticating...
            </div>
        `;
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 28px;
            color: #999;
            cursor: pointer;
            line-height: 1;
        `;
        closeBtn.onclick = hideTeamModal;
        modalContainer.appendChild(closeBtn);
        
        teamModal.appendChild(modalContainer);
        document.body.appendChild(teamModal);
        
        // Add form submission handler
        document.getElementById('team-modal-form').addEventListener('submit', handleModalSubmit);
        
        // Add ESC key handler
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isModalVisible) {
                hideTeamModal();
            }
        });
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes slideIn {
                from { transform: translateY(-50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    function showTeamModal() {
        if (teamModal && !isModalVisible) {
            teamModal.style.display = 'flex';
            isModalVisible = true;
            
            // Focus username field
            setTimeout(() => {
                const usernameField = document.getElementById('team-username');
                console.log('Username field found:', !!usernameField);
                if (usernameField) {
                    usernameField.focus();
                }
            }, 100);
        }
    }
    
    // Make hideTeamModal globally accessible
    window.hideTeamModal = function() {
        if (teamModal && isModalVisible) {
            teamModal.style.display = 'none';
            isModalVisible = false;
            
            // Clear form
            document.getElementById('team-modal-form').reset();
            document.getElementById('team-error-message').style.display = 'none';
        }
    };
    
    async function handleModalSubmit(e) {
        e.preventDefault();
        
        const username = document.getElementById('team-username').value;
        const password = document.getElementById('team-password').value;
        const errorDiv = document.getElementById('team-error-message');
        const loadingDiv = document.getElementById('team-loading');
        const submitBtn = document.getElementById('team-submit-btn');
        
        // Clear previous errors
        errorDiv.style.display = 'none';
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing In...';
        loadingDiv.style.display = 'block';
        
        try {
            const response = await fetch('/login/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Success - redirect to email service
                window.location.href = result.redirect;
            } else {
                // Show error
                errorDiv.textContent = result.message;
                errorDiv.style.display = 'block';
                
                // Clear password field
                document.getElementById('team-password').value = '';
                document.getElementById('team-password').focus();
            }
            
        } catch (error) {
            errorDiv.textContent = 'Login failed. Please try again.';
            errorDiv.style.display = 'block';
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.textContent = 'Sign In';
            loadingDiv.style.display = 'none';
        }
    }
    
})();