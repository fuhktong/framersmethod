<?php
/**
 * Campaign Management API
 */
require_once 'database.php';
require_once '../contact/smtp_mailer.php';
require_once '../contact/env_loader.php';

// Load environment variables
$env_paths = [
    __DIR__ . '/../.env',
    __DIR__ . '/../../.env',
];

foreach ($env_paths as $env_path) {
    if (file_exists($env_path)) {
        loadEnv($env_path);
        break;
    }
}

header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDatabaseConnection();
    $response = ['success' => true];
    
    switch ($method) {
        case 'POST':
            $action = $_GET['action'] ?? 'create';
            
            if ($action === 'test') {
                // Send test email
                $response['data'] = sendTestEmail($pdo, $input);
            } elseif ($action === 'send') {
                // Send campaign to all subscribers
                $response['data'] = sendCampaign($pdo, $input);
            } else {
                // Create/save campaign
                $response['data'] = saveCampaign($pdo, $input);
            }
            break;
            
        case 'PUT':
            // Update campaign
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $response['data'] = updateCampaign($pdo, $id, $input);
            } else {
                throw new Exception('Campaign ID required for update');
            }
            break;
            
        case 'DELETE':
            // Delete campaign
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $response['data'] = deleteCampaign($pdo, $id);
            } else {
                throw new Exception('Campaign ID required for delete');
            }
            break;
            
        case 'GET':
            // Get campaign
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $response['data'] = getCampaign($pdo, $id);
            } else {
                throw new Exception('Campaign ID required');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    http_response_code(400);
}

echo json_encode($response);

// Save campaign (create or update draft)
function saveCampaign($pdo, $data) {
    $subject = trim($data['subject'] ?? '');
    $content = trim($data['content'] ?? '');
    $contentType = $data['content_type'] ?? 'html';
    $fromName = trim($data['from_name'] ?? 'The Framers Method');
    $status = $data['status'] ?? 'draft';
    $recipientType = $data['recipient_type'] ?? 'all';
    $scheduledDatetime = $data['scheduled_datetime'] ?? null;
    $timezone = $data['timezone'] ?? null;
    
    if (empty($subject)) {
        throw new Exception('Subject is required');
    }
    
    if (empty($content)) {
        throw new Exception('Content is required');
    }
    
    if (!in_array($contentType, ['html', 'plain'])) {
        throw new Exception('Invalid content type');
    }
    
    if (!in_array($status, ['draft', 'scheduled', 'sending', 'sent', 'failed', 'cancelled'])) {
        throw new Exception('Invalid status');
    }
    
    // Validate scheduling data
    if ($status === 'scheduled') {
        if (empty($scheduledDatetime)) {
            throw new Exception('Scheduled date and time is required for scheduled campaigns');
        }
        
        // Convert user timezone to UTC for storage
        $scheduledAt = convertToUtc($scheduledDatetime, $timezone);
        
        // Validate scheduled time is in the future
        if (strtotime($scheduledAt) <= time()) {
            throw new Exception('Scheduled time must be in the future');
        }
    } else {
        $scheduledAt = null;
    }
    
    // Calculate recipient count
    $totalRecipients = 0;
    if ($recipientType === 'all') {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM subscribers WHERE status = 'active'");
        $totalRecipients = $stmt->fetch()['count'];
    }
    
    // Insert new campaign
    $stmt = $pdo->prepare("
        INSERT INTO campaigns (subject, content, content_type, from_name, status, total_recipients, scheduled_at, timezone, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$subject, $content, $contentType, $fromName, $status, $totalRecipients, $scheduledAt, $timezone]);
    
    $id = $pdo->lastInsertId();
    
    // Return the new campaign
    return getCampaign($pdo, $id);
}

// Update campaign
function updateCampaign($pdo, $id, $data) {
    $subject = trim($data['subject'] ?? '');
    $content = trim($data['content'] ?? '');
    $contentType = $data['content_type'] ?? 'html';
    $fromName = trim($data['from_name'] ?? 'The Framers Method');
    $status = $data['status'] ?? 'draft';
    $scheduledAt = $data['scheduled_at'] ?? null;
    $timezone = $data['timezone'] ?? null;
    
    // Check if campaign exists and is editable
    $stmt = $pdo->prepare("SELECT status FROM campaigns WHERE id = ?");
    $stmt->execute([$id]);
    $campaign = $stmt->fetch();
    
    if (!$campaign) {
        throw new Exception('Campaign not found');
    }
    
    if ($campaign['status'] === 'sent') {
        throw new Exception('Cannot edit sent campaigns');
    }
    
    // Handle cancelling scheduled campaigns (status update only)
    if (isset($data['status']) && count($data) <= 4) { // Only status, scheduled_at, timezone, and maybe ID
        $stmt = $pdo->prepare("
            UPDATE campaigns 
            SET status = ?, scheduled_at = ?, timezone = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $scheduledAt, $timezone, $id]);
        
        return getCampaign($pdo, $id);
    }
    
    // Full campaign update
    if (empty($subject)) {
        throw new Exception('Subject is required');
    }
    
    if (empty($content)) {
        throw new Exception('Content is required');
    }
    
    // Update campaign
    $stmt = $pdo->prepare("
        UPDATE campaigns 
        SET subject = ?, content = ?, content_type = ?, from_name = ?, status = ?, scheduled_at = ?, timezone = ?
        WHERE id = ?
    ");
    $stmt->execute([$subject, $content, $contentType, $fromName, $status, $scheduledAt, $timezone, $id]);
    
    // Return updated campaign
    return getCampaign($pdo, $id);
}

// Delete campaign
function deleteCampaign($pdo, $id) {
    // Check if campaign exists
    $stmt = $pdo->prepare("SELECT status FROM campaigns WHERE id = ?");
    $stmt->execute([$id]);
    $campaign = $stmt->fetch();
    
    if (!$campaign) {
        throw new Exception('Campaign not found');
    }
    
    if ($campaign['status'] === 'sent') {
        throw new Exception('Cannot delete sent campaigns');
    }
    
    // Delete campaign (this will cascade delete campaign_sends)
    $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ?");
    $stmt->execute([$id]);
    
    return ['message' => 'Campaign deleted successfully'];
}

// Get campaign
function getCampaign($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT * FROM campaigns WHERE id = ?
    ");
    $stmt->execute([$id]);
    $campaign = $stmt->fetch();
    
    if (!$campaign) {
        throw new Exception('Campaign not found');
    }
    
    return $campaign;
}

// Send test email
function sendTestEmail($pdo, $data) {
    $subject = trim($data['subject'] ?? '');
    $content = trim($data['content'] ?? '');
    $contentType = $data['content_type'] ?? 'html';
    $fromName = trim($data['from_name'] ?? 'The Framers Method');
    $testEmail = trim($data['test_email'] ?? '');
    
    if (empty($subject)) {
        throw new Exception('Subject is required');
    }
    
    if (empty($content)) {
        throw new Exception('Content is required');
    }
    
    if (empty($testEmail)) {
        throw new Exception('Test email address is required');
    }
    
    if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid test email address');
    }
    
    // SMTP Configuration
    $smtp_config = [
        'host' => $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?? 'smtp.hostinger.com',
        'port' => (int)($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587),
        'username' => $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME'),
        'password' => $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD'),
        'use_tls' => ($_ENV['SMTP_USE_TLS'] ?? getenv('SMTP_USE_TLS') ?? 'true') === 'true'
    ];
    
    if (empty($smtp_config['username']) || empty($smtp_config['password'])) {
        throw new Exception('SMTP configuration is missing');
    }
    
    // Add test email header
    $testSubject = "[TEST] " . $subject;
    $testContent = $content;
    
    if ($contentType === 'html') {
        $testContent = '<div style="background: #fffacd; padding: 10px; margin-bottom: 20px; border: 1px solid #ffd700; border-radius: 4px;"><strong>🧪 This is a test email</strong><br>This email was sent as a test from the Email Service dashboard.</div>' . $content;
    } else {
        $testContent = "🧪 This is a test email\nThis email was sent as a test from the Email Service dashboard.\n\n" . $content;
    }
    
    // Create SMTP mailer instance
    $mailer = new SimpleSmtpMailer(
        $smtp_config['host'],
        $smtp_config['port'],
        $smtp_config['username'],
        $smtp_config['password'],
        $smtp_config['use_tls']
    );
    
    // Send test email
    $result = $mailer->sendMail(
        $testEmail,                    // To
        $testSubject,                  // Subject
        $testContent,                  // Message
        $smtp_config['username'],      // From email
        $fromName,                     // From name
        $smtp_config['username'],      // Reply-to
        $contentType === 'html'        // Is HTML
    );
    
    if ($result === true) {
        return ['message' => 'Test email sent successfully to ' . $testEmail];
    } else {
        throw new Exception('Failed to send test email: ' . $result);
    }
}

// Send campaign to all subscribers
function sendCampaign($pdo, $data) {
    $campaign_id = (int)($data['campaign_id'] ?? 0);
    
    if ($campaign_id <= 0) {
        throw new Exception('Campaign ID is required');
    }
    
    // Verify campaign exists and is ready to send
    $stmt = $pdo->prepare("SELECT id, subject, status, scheduled_at FROM campaigns WHERE id = ?");
    $stmt->execute([$campaign_id]);
    $campaign = $stmt->fetch();
    
    if (!$campaign) {
        throw new Exception('Campaign not found');
    }
    
    if (!in_array($campaign['status'], ['draft', 'scheduled'])) {
        throw new Exception('Campaign cannot be sent. Current status: ' . $campaign['status']);
    }
    
    // For scheduled campaigns, verify it's time to send
    if ($campaign['status'] === 'scheduled' && !empty($campaign['scheduled_at'])) {
        if (strtotime($campaign['scheduled_at']) > time()) {
            throw new Exception('Campaign is scheduled for later. Scheduled time: ' . $campaign['scheduled_at']);
        }
    }
    
    // Call the bulk sender directly (include the file)
    require_once 'bulk-sender.php';
    
    // Simulate the POST request data
    $_POST = [];
    $GLOBALS['HTTP_RAW_POST_DATA'] = json_encode([
        'campaign_id' => $campaign_id,
        'action' => 'send'
    ]);
    
    // Capture output buffer to get the JSON response
    ob_start();
    
    // Mock the input stream for bulk-sender.php
    $original_input = file_get_contents('php://input');
    
    // Call startCampaignSend directly
    try {
        $result = startCampaignSend($pdo, $campaign_id);
        return $result;
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    } finally {
        ob_end_clean();
    }
}

/**
 * Convert user timezone datetime to UTC for storage
 */
function convertToUtc($datetime, $timezone) {
    try {
        $userTimezone = new DateTimeZone($timezone);
        $utcTimezone = new DateTimeZone('UTC');
        
        $dt = new DateTime($datetime, $userTimezone);
        $dt->setTimezone($utcTimezone);
        
        return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        // Fallback to treating as UTC if timezone conversion fails
        return date('Y-m-d H:i:s', strtotime($datetime));
    }
}

/**
 * Convert UTC datetime to user timezone for display
 */
function convertFromUtc($datetime, $timezone) {
    try {
        $utcTimezone = new DateTimeZone('UTC');
        $userTimezone = new DateTimeZone($timezone);
        
        $dt = new DateTime($datetime, $utcTimezone);
        $dt->setTimezone($userTimezone);
        
        return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        // Fallback to UTC if timezone conversion fails
        return $datetime;
    }
}
?>