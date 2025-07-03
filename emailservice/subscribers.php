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
        </nav>
    </header>

    <main class="email-main">
        <div class="subscribers-header">
            <h2>Subscribers (0)</h2>
            <div class="subscriber-actions">
                <button class="btn btn-primary" onclick="showUploadModal()">Upload Subscribers</button>
                <button class="btn btn-secondary" onclick="exportSubscribers()">Export List</button>
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
                <input type="file" id="csv-file" accept=".csv" class="file-input">
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

        function showUploadModal() {
            document.getElementById('upload-modal').style.display = 'block';
        }

        function hideUploadModal() {
            document.getElementById('upload-modal').style.display = 'none';
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

        function processCsvUpload() {
            const fileInput = document.getElementById('csv-file');
            if (!fileInput.files[0]) {
                alert('Please select a CSV file');
                return;
            }

            alert('CSV upload will be implemented with backend');
        }

        function exportSubscribers() {
            alert('Export functionality coming soon');
        }

        function bulkDelete() {
            if (selectedSubscribers.size === 0) return;
            if (confirm(`Delete ${selectedSubscribers.size} subscribers?`)) {
                alert('Bulk delete functionality coming soon');
            }
        }

        function bulkUnsubscribe() {
            if (selectedSubscribers.size === 0) return;
            if (confirm(`Unsubscribe ${selectedSubscribers.size} subscribers?`)) {
                alert('Bulk unsubscribe functionality coming soon');
            }
        }

        // Select all checkbox
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.subscriber-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) {
                    selectedSubscribers.add(checkbox.value);
                } else {
                    selectedSubscribers.delete(checkbox.value);
                }
            });
            updateBulkActions();
        });

        function updateBulkActions() {
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');
            
            if (selectedSubscribers.size > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = `${selectedSubscribers.size} selected`;
            } else {
                bulkActions.style.display = 'none';
            }
        }

        // Manual form submission
        document.getElementById('manual-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('manual-email').value;
            const name = document.getElementById('manual-name').value;
            
            alert('Manual subscriber addition will be implemented with backend');
            this.reset();
        });

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            // Search implementation will come with backend
        });

        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            // Filter implementation will come with backend
        });
    </script>
</body>
</html>