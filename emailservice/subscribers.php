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
    <title>Subscribers - Email Service</title>
    <link rel="stylesheet" href="emailservice.css">
</head>
<body>
    <header class="email-header">
        <h1>Subscriber Management</h1>
        <nav>
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="campaigns.php" class="nav-link">Campaigns</a>
            <a href="subscribers.php" class="nav-link active">Subscribers</a>
            <a href="create-campaign.php" class="nav-link">Create Campaign</a>
            <div class="nav-user">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                <a href="../login/logout.php" class="nav-link logout">Logout</a>
            </div>
        </nav>
    </header>

    <main class="email-main">
        <div class="subscribers-header">
            <h2 id="subscribers-count">Subscribers (Loading...)</h2>
            <div class="subscriber-actions">
                <button class="btn btn-primary" onclick="showUploadModal()">Upload Subscribers</button>
                <button class="btn btn-secondary" onclick="exportSubscribers()">Export All</button>
                <button class="btn btn-secondary" onclick="exportSelected()" id="export-selected-btn" style="display: none;">Export Selected</button>
            </div>
        </div>

        <div class="subscribers-filters">
            <input type="text" id="search" placeholder="Search subscribers..." class="search-input">
            <select id="status-filter" class="filter-select">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="unsubscribed">Unsubscribed</option>
            </select>
        </div>

        <div class="subscribers-table-container">
            <table class="subscribers-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Subscribed Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="subscribers-tbody">
                    <tr>
                        <td colspan="6" class="no-data">No subscribers found. <a href="#" onclick="showUploadModal()">Upload your first subscribers</a></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bulk-actions" id="bulk-actions" style="display: none;">
            <span id="selected-count">0 selected</span>
            <button class="btn btn-danger" onclick="bulkDelete()">Delete Selected</button>
            <button class="btn btn-warning" onclick="bulkUnsubscribe()">Unsubscribe Selected</button>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="hideEditModal()">&times;</span>
            <h3>Edit Subscriber</h3>
            
            <form id="edit-form">
                <input type="hidden" id="edit-id">
                <div class="form-group">
                    <label for="edit-email">Email</label>
                    <input type="email" id="edit-email" required>
                </div>
                <div class="form-group">
                    <label for="edit-name">Name (optional)</label>
                    <input type="text" id="edit-name">
                </div>
                <div class="form-group">
                    <label for="edit-status">Status</label>
                    <select id="edit-status">
                        <option value="active">Active</option>
                        <option value="unsubscribed">Unsubscribed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Subscriber</button>
                <button type="button" class="btn btn-secondary" onclick="hideEditModal()">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="hideUploadModal()">&times;</span>
            <h3>Upload Subscribers</h3>
            
            <div class="upload-tabs">
                <button class="tab-btn active" onclick="showTab('csv')">CSV Upload</button>
                <button class="tab-btn" onclick="showTab('manual')">Manual Entry</button>
            </div>

            <div id="csv-tab" class="tab-content active">
                <p>Upload a CSV file with columns: email, name (optional)</p>
                <p><strong>Supported formats:</strong></p>
                <ul>
                    <li>With headers: email, name</li>
                    <li>Without headers: first column = email, second column = name</li>
                    <li>Alternative headers: "email address", "e-mail", "full name", "first name"</li>
                </ul>
                <input type="file" id="csv-file" accept=".csv" class="file-input" onchange="previewCsv()">
                <div class="upload-preview" id="upload-preview" style="display: none;">
                    <h4>Preview:</h4>
                    <div id="preview-content"></div>
                </div>
                <button class="btn btn-primary" onclick="processCsvUpload()">Upload CSV</button>
            </div>

            <div id="manual-tab" class="tab-content">
                <form id="manual-form">
                    <div class="form-group">
                        <label for="manual-email">Email</label>
                        <input type="email" id="manual-email" required>
                    </div>
                    <div class="form-group">
                        <label for="manual-name">Name (optional)</label>
                        <input type="text" id="manual-name">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Subscriber</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let subscribers = [];
        let selectedSubscribers = new Set();
        let currentPage = 1;
        let totalPages = 1;

        function showUploadModal() {
            document.getElementById('upload-modal').style.display = 'block';
            // Clear any previous preview
            clearCsvPreview();
        }

        function hideUploadModal() {
            document.getElementById('upload-modal').style.display = 'none';
            // Clear preview and file input when closing
            clearCsvPreview();
            document.getElementById('csv-file').value = '';
        }

        function clearCsvPreview() {
            const previewDiv = document.getElementById('upload-preview');
            const previewContent = document.getElementById('preview-content');
            previewDiv.style.display = 'none';
            previewContent.innerHTML = '';
        }

        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        async function processCsvUpload() {
            const fileInput = document.getElementById('csv-file');
            if (!fileInput.files[0]) {
                alert('Please select a CSV file');
                return;
            }
            
            const file = fileInput.files[0];
            
            // Validate file type
            if (!file.name.toLowerCase().endsWith('.csv')) {
                alert('Please select a CSV file');
                return;
            }
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File is too large. Maximum size is 5MB');
                return;
            }
            
            const formData = new FormData();
            formData.append('csv_file', file);
            
            // Show progress
            const uploadBtn = document.querySelector('button[onclick="processCsvUpload()"]');
            const originalText = uploadBtn.textContent;
            uploadBtn.textContent = 'Uploading...';
            uploadBtn.disabled = true;
            
            try {
                const response = await fetch('csv-upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    hideUploadModal();
                    loadSubscribers(); // Refresh the list
                } else {
                    alert('Error uploading CSV: ' + result.message);
                }
            } catch (error) {
                alert('Error uploading CSV: ' + error.message);
            } finally {
                uploadBtn.textContent = originalText;
                uploadBtn.disabled = false;
            }
        }

        function previewCsv() {
            const fileInput = document.getElementById('csv-file');
            const previewDiv = document.getElementById('upload-preview');
            const previewContent = document.getElementById('preview-content');
            
            if (!fileInput.files[0]) {
                previewDiv.style.display = 'none';
                return;
            }
            
            const file = fileInput.files[0];
            
            if (!file.name.toLowerCase().endsWith('.csv')) {
                previewContent.innerHTML = '<p style="color: red;">Please select a CSV file</p>';
                previewDiv.style.display = 'block';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const text = e.target.result;
                const lines = text.split('\n').slice(0, 6); // Show first 6 lines
                
                let preview = '<table style="width: 100%; border-collapse: collapse;">';
                lines.forEach((line, index) => {
                    if (line.trim()) {
                        const cells = line.split(',');
                        preview += '<tr>';
                        cells.forEach(cell => {
                            const tag = index === 0 ? 'th' : 'td';
                            preview += `<${tag} style="border: 1px solid #ddd; padding: 8px; text-align: left;">${cell.trim()}</${tag}>`;
                        });
                        preview += '</tr>';
                    }
                });
                preview += '</table>';
                
                if (text.split('\n').length > 6) {
                    preview += '<p><em>... and ' + (text.split('\n').length - 6) + ' more rows</em></p>';
                }
                
                previewContent.innerHTML = preview;
                previewDiv.style.display = 'block';
            };
            
            reader.readAsText(file);
        }

        function exportSubscribers() {
            // Get current filter values
            const search = document.getElementById('search').value;
            const status = document.getElementById('status-filter').value;
            
            // Build export URL with current filters
            let exportUrl = 'export-subscribers.php?format=csv';
            
            if (search) {
                exportUrl += '&search=' + encodeURIComponent(search);
            }
            
            if (status && status !== 'all') {
                exportUrl += '&status=' + encodeURIComponent(status);
            }
            
            // Trigger download
            triggerDownload(exportUrl);
        }

        function exportSelected() {
            if (selectedSubscribers.size === 0) {
                alert('Please select subscribers to export');
                return;
            }
            
            // Build export URL with selected IDs
            const ids = Array.from(selectedSubscribers).join(',');
            const exportUrl = `export-subscribers.php?format=csv&ids=${ids}`;
            
            // Trigger download
            triggerDownload(exportUrl);
        }

        function triggerDownload(url) {
            const link = document.createElement('a');
            link.href = url;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        async function bulkDelete() {
            if (selectedSubscribers.size === 0) return;
            if (confirm(`Delete ${selectedSubscribers.size} subscribers? This action cannot be undone.`)) {
                try {
                    const response = await fetch('subscriber-api.php?action=bulk', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            ids: Array.from(selectedSubscribers),
                            operation: 'delete'
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(result.data.message);
                        selectedSubscribers.clear();
                        updateBulkActions();
                        loadSubscribers(); // Refresh the list
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error deleting subscribers: ' + error.message);
                }
            }
        }

        async function bulkUnsubscribe() {
            if (selectedSubscribers.size === 0) return;
            if (confirm(`Unsubscribe ${selectedSubscribers.size} subscribers?`)) {
                try {
                    const response = await fetch('subscriber-api.php?action=bulk', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            ids: Array.from(selectedSubscribers),
                            operation: 'unsubscribe'
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(result.data.message);
                        selectedSubscribers.clear();
                        updateBulkActions();
                        loadSubscribers(); // Refresh the list
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error unsubscribing subscribers: ' + error.message);
                }
            }
        }

        // Select all checkbox
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.subscriber-checkbox');
            
            if (this.checked) {
                // Select all
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                    selectedSubscribers.add(parseInt(checkbox.value));
                });
            } else {
                // Unselect all
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    selectedSubscribers.delete(parseInt(checkbox.value));
                });
            }
            updateBulkActions();
        });

        function updateBulkActions() {
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');
            const exportSelectedBtn = document.getElementById('export-selected-btn');
            
            if (selectedSubscribers.size > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = `${selectedSubscribers.size} selected`;
                exportSelectedBtn.style.display = 'inline-block';
            } else {
                bulkActions.style.display = 'none';
                exportSelectedBtn.style.display = 'none';
            }
        }

        // Manual form submission
        document.getElementById('manual-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('manual-email').value;
            const name = document.getElementById('manual-name').value;
            
            try {
                const response = await fetch('subscriber-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        name: name
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Subscriber added successfully!');
                    this.reset();
                    hideUploadModal();
                    loadSubscribers(); // Refresh the list
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error adding subscriber: ' + error.message);
            }
        });

        // Load subscribers data
        async function loadSubscribers() {
            try {
                const search = document.getElementById('search').value;
                const status = document.getElementById('status-filter').value;
                
                const response = await fetch(`data-service.php?action=subscribers&page=${currentPage}&search=${encodeURIComponent(search)}&status=${status}`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    subscribers = data.subscribers;
                    totalPages = data.total_pages;
                    
                    // Update subscribers count
                    document.getElementById('subscribers-count').textContent = `Subscribers (${data.total})`;
                    
                    // Update table
                    updateSubscribersTable(data.subscribers);
                    
                    // Update pagination if needed
                    // updatePagination();
                    
                } else {
                    console.error('Failed to load subscribers:', result.message);
                    document.getElementById('subscribers-tbody').innerHTML = 
                        '<tr><td colspan="6" class="no-data">Error loading subscribers</td></tr>';
                }
            } catch (error) {
                console.error('Error loading subscribers:', error);
                document.getElementById('subscribers-tbody').innerHTML = 
                    '<tr><td colspan="6" class="no-data">Error loading subscribers</td></tr>';
            }
        }

        function updateSubscribersTable(subscribersData) {
            const tbody = document.getElementById('subscribers-tbody');
            
            // Clear selection when table updates
            selectedSubscribers.clear();
            updateBulkActions();
            
            if (subscribersData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="no-data">No subscribers found. <a href="#" onclick="showUploadModal()">Upload your first subscribers</a></td></tr>';
                document.getElementById('select-all').checked = false;
                document.getElementById('select-all').indeterminate = false;
                return;
            }
            
            tbody.innerHTML = subscribersData.map(subscriber => `
                <tr>
                    <td><input type="checkbox" class="subscriber-checkbox" value="${subscriber.id}" onchange="toggleSubscriber(${subscriber.id})"></td>
                    <td><strong>${subscriber.email}</strong></td>
                    <td>${subscriber.name || '-'}</td>
                    <td><span class="status ${subscriber.status}">${subscriber.status}</span></td>
                    <td>${new Date(subscriber.subscribed_at).toLocaleDateString()}</td>
                    <td>
                        <button onclick="editSubscriber(${subscriber.id})" class="btn-small">Edit</button>
                        <button onclick="deleteSubscriber(${subscriber.id})" class="btn-small btn-danger">Delete</button>
                    </td>
                </tr>
            `).join('');
            
            // Reset select all checkbox
            document.getElementById('select-all').checked = false;
            document.getElementById('select-all').indeterminate = false;
        }

        function toggleSubscriber(id) {
            const checkbox = document.querySelector(`input[value="${id}"]`);
            const numericId = parseInt(id);
            
            if (checkbox.checked) {
                selectedSubscribers.add(numericId);
            } else {
                selectedSubscribers.delete(numericId);
            }
            
            // Update "select all" checkbox state
            updateSelectAllCheckbox();
            updateBulkActions();
        }

        function updateSelectAllCheckbox() {
            const checkboxes = document.querySelectorAll('.subscriber-checkbox');
            const selectAllCheckbox = document.getElementById('select-all');
            
            if (checkboxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
                return;
            }
            
            const checkedCount = selectedSubscribers.size;
            
            if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === checkboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true; // Shows partial selection
            }
        }

        async function editSubscriber(id) {
            try {
                // Get subscriber data
                const response = await fetch(`subscriber-api.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const subscriber = result.data;
                    
                    // Fill edit form
                    document.getElementById('edit-id').value = subscriber.id;
                    document.getElementById('edit-email').value = subscriber.email;
                    document.getElementById('edit-name').value = subscriber.name || '';
                    document.getElementById('edit-status').value = subscriber.status;
                    
                    // Show modal
                    document.getElementById('edit-modal').style.display = 'block';
                } else {
                    alert('Error loading subscriber: ' + result.message);
                }
            } catch (error) {
                alert('Error loading subscriber: ' + error.message);
            }
        }

        async function deleteSubscriber(id) {
            if (confirm('Delete this subscriber? This action cannot be undone.')) {
                try {
                    const response = await fetch(`subscriber-api.php?id=${id}`, {
                        method: 'DELETE'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Subscriber deleted successfully!');
                        loadSubscribers(); // Refresh the list
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error deleting subscriber: ' + error.message);
                }
            }
        }

        function showEditModal() {
            document.getElementById('edit-modal').style.display = 'block';
        }

        function hideEditModal() {
            document.getElementById('edit-modal').style.display = 'none';
        }

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            currentPage = 1; // Reset to first page
            loadSubscribers();
        });

        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            currentPage = 1; // Reset to first page
            loadSubscribers();
        });

        // Edit form submission
        document.getElementById('edit-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('edit-id').value;
            const email = document.getElementById('edit-email').value;
            const name = document.getElementById('edit-name').value;
            const status = document.getElementById('edit-status').value;
            
            try {
                const response = await fetch(`subscriber-api.php?id=${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        name: name,
                        status: status
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Subscriber updated successfully!');
                    hideEditModal();
                    loadSubscribers(); // Refresh the list
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error updating subscriber: ' + error.message);
            }
        });

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', loadSubscribers);
    </script>
</body>
</html>