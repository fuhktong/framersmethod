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
    <title>Email Templates - Email Service</title>
    <link rel="stylesheet" href="emailservice.css">
    <style>
        .templates-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .templates-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #666;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            border-color: #45698c;
            background: #45698c;
            color: white;
        }
        
        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .template-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .template-preview {
            height: 200px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .template-preview iframe {
            width: 100%;
            height: 400px;
            transform: scale(0.5);
            transform-origin: top left;
            border: none;
        }
        
        .template-preview-placeholder {
            color: #666;
            font-size: 48px;
        }
        
        .template-info {
            padding: 20px;
        }
        
        .template-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        
        .template-description {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .template-category {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .template-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .template-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .template-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            max-height: 80%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .template-modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .template-modal-body {
            flex: 1;
            overflow: auto;
            padding: 20px;
        }
        
        .template-editor {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            height: 500px;
        }
        
        .template-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .template-preview-area {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: auto;
        }
        
        .variable-inputs {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .variable-input {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            font-family: 'Courier New', monospace;
        }
        
        .variable-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .variable-row input {
            flex: 1;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        @media (max-width: 768px) {
            .templates-grid {
                grid-template-columns: 1fr;
            }
            
            .template-editor {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .template-modal-content {
                width: 95%;
                max-height: 90%;
            }
            
            .variable-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="email-header">
        <h1>Email Templates</h1>
        <nav>
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="campaigns.php" class="nav-link">Campaigns</a>
            <a href="subscribers.php" class="nav-link">Subscribers</a>
            <a href="analytics.php" class="nav-link">Analytics</a>
            <a href="templates.php" class="nav-link active">Templates</a>
            <a href="bounce-management.php" class="nav-link">Bounces</a>
            <a href="create-campaign.php" class="nav-link">Create Campaign</a>
            <div class="nav-user">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                <a href="../login/logout.php" class="nav-link logout">Logout</a>
            </div>
        </nav>
    </header>

    <main class="email-main">
        <div class="templates-header">
            <h2 id="templates-title">All Templates</h2>
            <button class="btn btn-primary" onclick="showCreateTemplateModal()">Create Template</button>
        </div>

        <div class="templates-filters">
            <button class="filter-btn active" onclick="filterTemplates('all')">All</button>
            <button class="filter-btn" onclick="filterTemplates('newsletter')">Newsletter</button>
            <button class="filter-btn" onclick="filterTemplates('welcome')">Welcome</button>
            <button class="filter-btn" onclick="filterTemplates('announcement')">Announcement</button>
            <button class="filter-btn" onclick="filterTemplates('marketing')">Marketing</button>
            <button class="filter-btn" onclick="filterTemplates('custom')">Custom</button>
        </div>

        <div class="templates-grid" id="templates-grid">
            <div style="text-align: center; padding: 40px; color: #666; grid-column: 1 / -1;">
                Loading templates...
            </div>
        </div>
    </main>

    <!-- Template Modal -->
    <div id="template-modal" class="template-modal">
        <div class="template-modal-content">
            <div class="template-modal-header">
                <h3 id="modal-title">Template Preview</h3>
                <button class="close-modal" onclick="hideTemplateModal()">&times;</button>
            </div>
            <div class="template-modal-body">
                <div class="template-editor">
                    <div class="template-form">
                        <h4>Customize Variables</h4>
                        <div id="variable-inputs" class="variable-inputs">
                            <!-- Variable inputs will be generated here -->
                        </div>
                        <div style="margin-top: 20px;">
                            <button class="btn btn-primary" onclick="useTemplate()" id="use-template-btn">Use This Template</button>
                            <button class="btn btn-secondary" onclick="previewTemplate()">Refresh Preview</button>
                        </div>
                    </div>
                    <div class="template-preview-area">
                        <iframe id="template-preview-frame" style="width: 100%; height: 100%; border: none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Template Modal -->
    <div id="create-template-modal" class="template-modal">
        <div class="template-modal-content">
            <div class="template-modal-header">
                <h3>Create New Template</h3>
                <button class="close-modal" onclick="hideCreateTemplateModal()">&times;</button>
            </div>
            <div class="template-modal-body">
                <form id="create-template-form" class="template-form">
                    <div class="form-group">
                        <label for="template-name-input">Template Name</label>
                        <input type="text" id="template-name-input" required placeholder="Enter template name">
                    </div>
                    
                    <div class="form-group">
                        <label for="template-description-input">Description</label>
                        <textarea id="template-description-input" rows="2" placeholder="Brief description of the template"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="template-category-input">Category</label>
                        <select id="template-category-input" required>
                            <option value="newsletter">Newsletter</option>
                            <option value="welcome">Welcome</option>
                            <option value="announcement">Announcement</option>
                            <option value="marketing">Marketing</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="template-html-input">HTML Content</label>
                        <textarea id="template-html-input" rows="15" required placeholder="Enter your HTML template here. Use {{variable_name}} for variables."></textarea>
                        <small style="color: #666;">Use {{variable_name}} syntax for dynamic content. For example: {{subject}}, {{content}}, {{footer_text}}</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Template Variables</label>
                        <div id="variables-container">
                            <div class="variable-row">
                                <input type="text" placeholder="Variable name (e.g., subject)" class="variable-name">
                                <input type="text" placeholder="Default value" class="variable-value">
                                <button type="button" onclick="removeVariable(this)" class="btn-small btn-danger">Remove</button>
                            </div>
                        </div>
                        <button type="button" onclick="addVariable()" class="btn-small btn-secondary">Add Variable</button>
                    </div>
                    
                    <div class="form-actions" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary">Create Template</button>
                        <button type="button" class="btn btn-secondary" onclick="hideCreateTemplateModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let templates = [];
        let currentCategory = 'all';
        let selectedTemplate = null;

        // Load templates
        async function loadTemplates(category = 'all') {
            try {
                const response = await fetch(`templates-api.php?action=list&category=${category}`);
                const result = await response.json();
                
                if (result.success) {
                    templates = result.data;
                    displayTemplates();
                    updateTitle(category);
                } else {
                    console.error('Failed to load templates:', result.message);
                }
            } catch (error) {
                console.error('Error loading templates:', error);
            }
        }

        // Display templates
        function displayTemplates() {
            const grid = document.getElementById('templates-grid');
            
            if (templates.length === 0) {
                grid.innerHTML = '<div style="text-align: center; padding: 40px; color: #666; grid-column: 1 / -1;">No templates found for this category.</div>';
                return;
            }

            grid.innerHTML = templates.map(template => `
                <div class="template-card">
                    <div class="template-preview">
                        <div class="template-preview-placeholder">📧</div>
                    </div>
                    <div class="template-info">
                        <div class="template-name">${template.name}</div>
                        <div class="template-description">${template.description || 'No description'}</div>
                        <div class="template-category">${template.category}</div>
                        <div class="template-actions">
                            <button class="btn-small btn-primary" onclick="previewTemplateModal(${template.id})">Preview</button>
                            <button class="btn-small btn-success" onclick="useTemplateDirectly(${template.id})">Use Template</button>
                            <button class="btn-small btn-secondary" onclick="editTemplate(${template.id})">Edit</button>
                            <button class="btn-small btn-danger" onclick="deleteTemplate(${template.id})">Delete</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Filter templates
        function filterTemplates(category) {
            currentCategory = category;
            
            // Update active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            loadTemplates(category);
        }

        // Update page title
        function updateTitle(category) {
            const titles = {
                'all': 'All Templates',
                'newsletter': 'Newsletter Templates',
                'welcome': 'Welcome Templates',
                'announcement': 'Announcement Templates',
                'marketing': 'Marketing Templates',
                'custom': 'Custom Templates'
            };
            
            document.getElementById('templates-title').textContent = titles[category] || 'Templates';
        }

        // Preview template in modal
        async function previewTemplateModal(templateId) {
            try {
                const response = await fetch(`templates-api.php?action=get&id=${templateId}`);
                const result = await response.json();
                
                if (result.success) {
                    selectedTemplate = result.data;
                    showTemplateModal();
                    generateVariableInputs();
                    previewTemplate();
                } else {
                    alert('Error loading template: ' + result.message);
                }
            } catch (error) {
                alert('Error loading template: ' + error.message);
            }
        }

        // Show template modal
        function showTemplateModal() {
            document.getElementById('template-modal').style.display = 'block';
            document.getElementById('modal-title').textContent = selectedTemplate.name;
        }

        // Hide template modal
        function hideTemplateModal() {
            document.getElementById('template-modal').style.display = 'none';
            selectedTemplate = null;
        }

        // Generate variable inputs
        function generateVariableInputs() {
            const container = document.getElementById('variable-inputs');
            const variables = selectedTemplate.template_variables || {};
            
            container.innerHTML = Object.keys(variables).map(key => `
                <div class="variable-input">
                    <label for="var-${key}">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</label>
                    <input type="text" id="var-${key}" value="${variables[key]}" placeholder="Enter ${key}">
                </div>
            `).join('');
        }

        // Preview template with current variables
        async function previewTemplate() {
            if (!selectedTemplate) return;
            
            const variables = {};
            const inputs = document.querySelectorAll('[id^="var-"]');
            inputs.forEach(input => {
                const key = input.id.replace('var-', '');
                variables[key] = input.value;
            });
            
            try {
                const response = await fetch(`templates-api.php?action=preview&id=${selectedTemplate.id}&variables=${encodeURIComponent(JSON.stringify(variables))}`);
                const result = await response.json();
                
                if (result.success) {
                    const iframe = document.getElementById('template-preview-frame');
                    iframe.srcdoc = result.data.html;
                }
            } catch (error) {
                console.error('Error previewing template:', error);
            }
        }

        // Use template (redirect to create campaign with template)
        function useTemplate() {
            if (!selectedTemplate) return;
            
            const variables = {};
            const inputs = document.querySelectorAll('[id^="var-"]');
            inputs.forEach(input => {
                const key = input.id.replace('var-', '');
                variables[key] = input.value;
            });
            
            // Store template data in sessionStorage
            sessionStorage.setItem('selectedTemplate', JSON.stringify({
                id: selectedTemplate.id,
                variables: variables
            }));
            
            // Redirect to create campaign
            window.location.href = 'create-campaign.php?template=1';
        }

        // Use template directly (quick action)
        function useTemplateDirectly(templateId) {
            sessionStorage.setItem('selectedTemplate', JSON.stringify({
                id: templateId,
                variables: {}
            }));
            
            window.location.href = 'create-campaign.php?template=1';
        }

        // Edit template
        function editTemplate(templateId) {
            alert('Template editing functionality coming soon!');
        }

        // Delete template
        async function deleteTemplate(templateId) {
            if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
                try {
                    const response = await fetch(`templates-api.php?id=${templateId}`, {
                        method: 'DELETE'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Template deleted successfully!');
                        loadTemplates(currentCategory);
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error deleting template: ' + error.message);
                }
            }
        }

        // Show create template modal
        function showCreateTemplateModal() {
            document.getElementById('create-template-modal').style.display = 'block';
            resetCreateForm();
        }
        
        // Hide create template modal
        function hideCreateTemplateModal() {
            document.getElementById('create-template-modal').style.display = 'none';
        }
        
        // Reset create form
        function resetCreateForm() {
            document.getElementById('create-template-form').reset();
            // Reset variables to one empty row
            const container = document.getElementById('variables-container');
            container.innerHTML = `
                <div class="variable-row">
                    <input type="text" placeholder="Variable name (e.g., subject)" class="variable-name">
                    <input type="text" placeholder="Default value" class="variable-value">
                    <button type="button" onclick="removeVariable(this)" class="btn-small btn-danger">Remove</button>
                </div>
            `;
        }
        
        // Add variable row
        function addVariable() {
            const container = document.getElementById('variables-container');
            const newRow = document.createElement('div');
            newRow.className = 'variable-row';
            newRow.innerHTML = `
                <input type="text" placeholder="Variable name (e.g., subject)" class="variable-name">
                <input type="text" placeholder="Default value" class="variable-value">
                <button type="button" onclick="removeVariable(this)" class="btn-small btn-danger">Remove</button>
            `;
            container.appendChild(newRow);
        }
        
        // Remove variable row
        function removeVariable(button) {
            const container = document.getElementById('variables-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
            } else {
                alert('At least one variable is required.');
            }
        }
        
        // Handle create template form submission
        function setupCreateTemplateForm() {
            document.getElementById('create-template-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = {
                    name: document.getElementById('template-name-input').value,
                    description: document.getElementById('template-description-input').value,
                    category: document.getElementById('template-category-input').value,
                    template_html: document.getElementById('template-html-input').value,
                    template_variables: {}
                };
                
                // Collect variables
                const variableRows = document.querySelectorAll('.variable-row');
                variableRows.forEach(row => {
                    const nameInput = row.querySelector('.variable-name');
                    const valueInput = row.querySelector('.variable-value');
                    
                    if (nameInput.value.trim() && valueInput.value.trim()) {
                        formData.template_variables[nameInput.value.trim()] = valueInput.value.trim();
                    }
                });
                
                try {
                    const response = await fetch('templates-api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Template created successfully!');
                        hideCreateTemplateModal();
                        loadTemplates(currentCategory); // Refresh the templates list
                    } else {
                        alert('Error creating template: ' + result.message);
                    }
                } catch (error) {
                    alert('Error creating template: ' + error.message);
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const templateModal = document.getElementById('template-modal');
            const createModal = document.getElementById('create-template-modal');
            
            if (event.target === templateModal) {
                hideTemplateModal();
            } else if (event.target === createModal) {
                hideCreateTemplateModal();
            }
        }

        // Load templates when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadTemplates();
            setupCreateTemplateForm();
        });
    </script>
</body>
</html>