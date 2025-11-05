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
    
    // Calculate recipient count based on selected list
    $listId = (int)$recipientType; // recipient_type now contains the list ID
    $totalRecipients = 0;
    
    if ($listId > 0) {
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT s.id) as count 
            FROM subscribers s
            JOIN subscriber_list_memberships m ON s.id = m.subscriber_id
            WHERE s.status = 'active' AND m.list_id = ?
        ");
        $stmt->execute([$listId]);
        $totalRecipients = $stmt->fetch()['count'];
    }
    
    // Insert new campaign
    $stmt = $pdo->prepare("
        INSERT INTO campaigns (subject, content, content_type, from_name, status, total_recipients, list_id, scheduled_at, timezone, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$subject, $content, $contentType, $fromName, $status, $totalRecipients, $listId, $scheduledAt, $timezone]);
    
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
    
    // Include bulk sender functions but don't execute the main logic
    $bulk_sender_path = __DIR__ . '/bulk-sender.php';
    
    // Extract just the functions from bulk-sender.php
    $bulk_sender_content = file_get_contents($bulk_sender_path);
    
    // Find the startCampaignSend function and other needed functions
    // We'll call it directly instead of going through HTTP
    
    // Direct function call approach
    require_once '../contact/smtp_mailer.php';
    require_once 'tracking.php';
    
    // Call startCampaignSend function directly
    try {
        // Get campaign details
        $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ? AND status IN ('draft', 'scheduled')");
        $stmt->execute([$campaign_id]);
        $campaign = $stmt->fetch();
        
        if (!$campaign) {
            throw new Exception('Campaign not found or already sent');
        }
        
        // Get subscribers from the campaign's target list
        if ($campaign['list_id']) {
            $stmt = $pdo->prepare("
                SELECT s.* FROM subscribers s
                JOIN subscriber_list_memberships m ON s.id = m.subscriber_id
                WHERE s.status = 'active'
                AND (s.bounce_status IS NULL OR s.bounce_status NOT IN ('hard_bounce', 'complaint'))
                AND m.list_id = ?
                ORDER BY s.id
            ");
            $stmt->execute([$campaign['list_id']]);
        } else {
            // Fallback to all subscribers if no list specified
            $stmt = $pdo->prepare("
                SELECT * FROM subscribers 
                WHERE status = 'active'
                AND (bounce_status IS NULL OR bounce_status NOT IN ('hard_bounce', 'complaint'))
                ORDER BY id
            ");
            $stmt->execute();
        }
        $subscribers = $stmt->fetchAll();
        
        if (empty($subscribers)) {
            throw new Exception('No active subscribers found');
        }
        
        // Update campaign status to sending
        $stmt = $pdo->prepare("UPDATE campaigns SET status = 'sending', sent_at = NOW() WHERE id = ?");
        $stmt->execute([$campaign_id]);
        
        // Initialize SMTP mailer
        $from_email = $_ENV['SMTP_FROM_EMAIL'] ?? '';
        if (empty($from_email)) {
            throw new Exception('SMTP configuration missing');
        }
        
        $mailer = new SimpleSmtpMailer(
            $_ENV['SMTP_HOST'],
            (int)$_ENV['SMTP_PORT'],
            $_ENV['SMTP_USERNAME'],
            $_ENV['SMTP_PASSWORD'],
            $_ENV['SMTP_USE_TLS'] === 'true'
        );
        
        // Send emails to all subscribers
        $sent_count = 0;
        $failed_count = 0;
        $errors = [];
        
        foreach ($subscribers as $subscriber) {
            try {
                // Check if already sent to this subscriber
                $stmt = $pdo->prepare("SELECT id FROM campaign_sends WHERE campaign_id = ? AND subscriber_id = ?");
                $stmt->execute([$campaign_id, $subscriber['id']]);
                
                if ($stmt->rowCount() > 0) {
                    continue; // Skip if already processed
                }
                
                // Prepare email content
                $email_content = $campaign['content'];
                $email_subject = $campaign['subject'];
                
                // Replace placeholders
                $email_content = str_replace('{subscriber_name}', $subscriber['name'] ?: 'Valued Subscriber', $email_content);
                $email_content = str_replace('{subscriber_email}', $subscriber['email'], $email_content);
                
                // Send email
                $result = $mailer->sendMail(
                    $subscriber['email'],
                    $email_subject,
                    $email_content,
                    $from_email,
                    $campaign['from_name'],
                    $from_email,
                    $campaign['content_type'] === 'html'
                );
                
                // Record send attempt
                if ($result === true) {
                    $stmt = $pdo->prepare("
                        INSERT INTO campaign_sends (campaign_id, subscriber_id, status, sent_at) 
                        VALUES (?, ?, 'sent', NOW())
                    ");
                    $stmt->execute([$campaign_id, $subscriber['id']]);
                    $sent_count++;
                } else {
                    $error_message = is_string($result) ? $result : 'Unknown error';
                    $stmt = $pdo->prepare("
                        INSERT INTO campaign_sends (campaign_id, subscriber_id, status, error_message, sent_at) 
                        VALUES (?, ?, 'failed', ?, NOW())
                    ");
                    $stmt->execute([$campaign_id, $subscriber['id'], $error_message]);
                    $failed_count++;
                    $errors[] = "Failed to send to {$subscriber['email']}: $error_message";
                }
                
            } catch (Exception $e) {
                $error_message = $e->getMessage();
                $stmt = $pdo->prepare("
                    INSERT INTO campaign_sends (campaign_id, subscriber_id, status, error_message, sent_at) 
                    VALUES (?, ?, 'failed', ?, NOW())
                ");
                $stmt->execute([$campaign_id, $subscriber['id'], $error_message]);
                $failed_count++;
                $errors[] = "Failed to send to {$subscriber['email']}: $error_message";
            }
        }
        
        // Update campaign status
        $final_status = ($failed_count === 0) ? 'sent' : 'partial';
        $stmt = $pdo->prepare("UPDATE campaigns SET status = ?, total_sent = ? WHERE id = ?");
        $stmt->execute([$final_status, $sent_count, $campaign_id]);
        
        return [
            'campaign_id' => $campaign_id,
            'total_subscribers' => count($subscribers),
            'sent_count' => $sent_count,
            'failed_count' => $failed_count,
            'status' => $final_status,
            'errors' => array_slice($errors, 0, 5),
            'message' => "Campaign completed! Sent: $sent_count, Failed: $failed_count"
        ];
        
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
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