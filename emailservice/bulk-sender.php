<?php
/**
 * Bulk Email Sending Engine
 * Handles sending campaigns to all active subscribers
 */
require_once 'database.php';
require_once '../contact/smtp_mailer.php';
require_once '../contact/env_loader.php';
require_once 'tracking.php';

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$campaign_id = (int)($input['campaign_id'] ?? 0);
$action = $input['action'] ?? 'send';

try {
    $pdo = getDatabaseConnection();
    
    switch ($action) {
        case 'send':
            $result = startCampaignSend($pdo, $campaign_id);
            break;
        case 'status':
            $result = getCampaignSendStatus($pdo, $campaign_id);
            break;
        case 'stop':
            $result = stopCampaignSend($pdo, $campaign_id);
            break;
        default:
            throw new Exception('Invalid action');
    }
    
    echo json_encode(['success' => true, 'data' => $result]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    http_response_code(400);
}

/**
 * Start sending campaign to all active subscribers
 */
function startCampaignSend($pdo, $campaign_id) {
    // Get campaign details
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ? AND status IN ('draft', 'scheduled')");
    $stmt->execute([$campaign_id]);
    $campaign = $stmt->fetch();
    
    if (!$campaign) {
        throw new Exception('Campaign not found or already sent');
    }
    
    // Get all active subscribers (excluding bounced ones)
    $stmt = $pdo->prepare("
        SELECT * FROM subscribers 
        WHERE (status = 'active' OR (status = 'paused' AND resume_date IS NOT NULL AND resume_date <= NOW()))
        AND bounce_status NOT IN ('hard_bounce', 'complaint')
        ORDER BY id
    ");
    $stmt->execute();
    $allSubscribers = $stmt->fetchAll();
    
    // Auto-resume paused subscribers
    foreach ($allSubscribers as $subscriber) {
        if ($subscriber['status'] === 'paused' && 
            !empty($subscriber['resume_date']) && 
            strtotime($subscriber['resume_date']) <= time()) {
            
            $resumeStmt = $pdo->prepare("UPDATE subscribers SET status = 'active', resume_date = NULL WHERE id = ?");
            $resumeStmt->execute([$subscriber['id']]);
            $subscriber['status'] = 'active'; // Update for current processing
        }
    }
    
    // Filter subscribers based on frequency preferences
    $subscribers = [];
    foreach ($allSubscribers as $subscriber) {
        if ($subscriber['status'] === 'active' && shouldReceiveEmailNow($pdo, $subscriber)) {
            $subscribers[] = $subscriber;
        }
    }
    
    if (empty($subscribers)) {
        throw new Exception('No active subscribers found');
    }
    
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
    
    // Update campaign status to sending
    $stmt = $pdo->prepare("UPDATE campaigns SET status = 'sending', sent_at = NOW() WHERE id = ?");
    $stmt->execute([$campaign_id]);
    
    // Initialize send tracking
    $total_subscribers = count($subscribers);
    $sent_count = 0;
    $failed_count = 0;
    $errors = [];
    
    // Process subscribers in batches to avoid memory issues
    // Optimized for Hostinger SMTP (500 emails/hour limit)
    $batch_size = 25;
    $batches = array_chunk($subscribers, $batch_size);
    
    foreach ($batches as $batch_index => $batch) {
        foreach ($batch as $subscriber) {
            try {
                // Check if already sent to this subscriber
                $stmt = $pdo->prepare("SELECT id FROM campaign_sends WHERE campaign_id = ? AND subscriber_id = ?");
                $stmt->execute([$campaign_id, $subscriber['id']]);
                
                if ($stmt->rowCount() > 0) {
                    continue; // Skip if already processed
                }
                
                // Prepare email content with personalization
                $email_content = prepareEmailContent($campaign, $subscriber);
                $email_subject = $campaign['subject'];
                
                // Add tracking functionality
                if ($campaign['content_type'] === 'html') {
                    // Add tracking pixel for open tracking
                    $email_content = addTrackingPixel($email_content, $campaign['id'], $subscriber['id']);
                }
                
                // Add click tracking to links
                $email_content = wrapLinksForTracking($email_content, $campaign['id'], $subscriber['id'], $campaign['content_type'] === 'html');
                
                // Add unsubscribe link
                $unsubscribe_url = getUnsubscribeUrl($subscriber['unsubscribe_token']);
                $email_content = addUnsubscribeLink($email_content, $unsubscribe_url, $campaign['content_type']);
                
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
                    // Success
                    $stmt = $pdo->prepare("
                        INSERT INTO campaign_sends (campaign_id, subscriber_id, status, sent_at) 
                        VALUES (?, ?, 'sent', NOW())
                    ");
                    $stmt->execute([$campaign_id, $subscriber['id']]);
                    $sent_count++;
                } else {
                    // Failed - analyze error for bounce detection
                    $error_message = is_string($result) ? $result : 'Unknown error';
                    
                    // Detect bounce type from error message
                    $bounceType = detectBounceFromError($error_message);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO campaign_sends (campaign_id, subscriber_id, status, error_message, sent_at, bounce_type, bounce_reason) 
                        VALUES (?, ?, 'failed', ?, NOW(), ?, ?)
                    ");
                    $stmt->execute([$campaign_id, $subscriber['id'], $error_message, $bounceType, $error_message]);
                    
                    // Process bounce if detected
                    if ($bounceType) {
                        processBounceDetection($pdo, $subscriber, $campaign_id, $bounceType, $error_message);
                    }
                    
                    $failed_count++;
                    $errors[] = "Failed to send to {$subscriber['email']}: $error_message";
                }
                
            } catch (Exception $e) {
                // Log error and continue
                $error_message = $e->getMessage();
                $stmt = $pdo->prepare("
                    INSERT INTO campaign_sends (campaign_id, subscriber_id, status, error_message, sent_at) 
                    VALUES (?, ?, 'failed', ?, NOW())
                ");
                $stmt->execute([$campaign_id, $subscriber['id'], $error_message]);
                $failed_count++;
                $errors[] = "Failed to send to {$subscriber['email']}: $error_message";
            }
            
            // Delay to respect Hostinger's 500 emails/hour limit
            usleep(7200000); // 7.2 seconds delay (exactly 500 emails/hour)
        }
        
        // No additional batch delay needed with per-email delay
        // The 7.2 second per-email delay already ensures we stay under 500/hour
    }
    
    // Update campaign status
    $final_status = ($failed_count === 0) ? 'sent' : 'partial';
    $stmt = $pdo->prepare("UPDATE campaigns SET status = ? WHERE id = ?");
    $stmt->execute([$final_status, $campaign_id]);
    
    return [
        'campaign_id' => $campaign_id,
        'total_subscribers' => $total_subscribers,
        'sent_count' => $sent_count,
        'failed_count' => $failed_count,
        'status' => $final_status,
        'errors' => array_slice($errors, 0, 10), // Return first 10 errors
        'message' => "Campaign sent successfully! Sent: $sent_count, Failed: $failed_count"
    ];
}

/**
 * Get campaign sending status and progress
 */
function getCampaignSendStatus($pdo, $campaign_id) {
    // Get campaign info
    $stmt = $pdo->prepare("SELECT id, subject, status, sent_at FROM campaigns WHERE id = ?");
    $stmt->execute([$campaign_id]);
    $campaign = $stmt->fetch();
    
    if (!$campaign) {
        throw new Exception('Campaign not found');
    }
    
    // Get send statistics
    $stmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as count
        FROM campaign_sends 
        WHERE campaign_id = ? 
        GROUP BY status
    ");
    $stmt->execute([$campaign_id]);
    $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get total active subscribers
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE status = 'active'");
    $stmt->execute();
    $total_subscribers = $stmt->fetchColumn();
    
    $sent_count = (int)($stats['sent'] ?? 0);
    $failed_count = (int)($stats['failed'] ?? 0);
    $processed_count = $sent_count + $failed_count;
    
    return [
        'campaign' => $campaign,
        'total_subscribers' => $total_subscribers,
        'sent_count' => $sent_count,
        'failed_count' => $failed_count,
        'processed_count' => $processed_count,
        'progress_percentage' => $total_subscribers > 0 ? round(($processed_count / $total_subscribers) * 100, 1) : 0,
        'is_complete' => $processed_count >= $total_subscribers,
        'stats' => $stats
    ];
}

/**
 * Stop campaign sending (mark as cancelled)
 */
function stopCampaignSend($pdo, $campaign_id) {
    $stmt = $pdo->prepare("UPDATE campaigns SET status = 'cancelled' WHERE id = ? AND status = 'sending'");
    $stmt->execute([$campaign_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Campaign not found or not currently sending');
    }
    
    return ['message' => 'Campaign sending stopped'];
}

/**
 * Prepare email content with personalization
 */
function prepareEmailContent($campaign, $subscriber) {
    $content = $campaign['content'];
    
    // Replace placeholders
    $replacements = [
        '{subscriber_name}' => $subscriber['name'] ?: 'Valued Subscriber',
        '{subscriber_email}' => $subscriber['email'],
        '{campaign_subject}' => $campaign['subject']
    ];
    
    foreach ($replacements as $placeholder => $value) {
        $content = str_replace($placeholder, $value, $content);
    }
    
    return $content;
}

/**
 * Generate unsubscribe URL
 */
function getUnsubscribeUrl($token) {
    $base_url = $_ENV['BASE_URL'] ?? 'https://localhost';
    return rtrim($base_url, '/') . "/emailservice/unsubscribe.php?token=" . urlencode($token);
}

/**
 * Add unsubscribe link to email content
 */
function addUnsubscribeLink($content, $unsubscribe_url, $content_type) {
    // Generate preferences URL
    $base_url = $_ENV['BASE_URL'] ?? 'https://localhost';
    $token = basename(parse_url($unsubscribe_url, PHP_URL_QUERY));
    $token = str_replace('token=', '', $token);
    $preferences_url = rtrim($base_url, '/') . "/emailservice/preferences.php?token=" . urlencode($token);
    
    if ($content_type === 'html') {
        // Add HTML unsubscribe link
        $unsubscribe_html = '<div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
            <p>You received this email because you are subscribed to The Framers\' Method newsletter.</p>
            <p><a href="' . htmlspecialchars($preferences_url) . '" style="color: #666;">Manage Preferences</a> | 
            <a href="' . htmlspecialchars($unsubscribe_url) . '" style="color: #666;">Unsubscribe</a></p>
        </div>';
        
        // Try to add before closing body tag, otherwise append
        if (stripos($content, '</body>') !== false) {
            $content = str_ireplace('</body>', $unsubscribe_html . '</body>', $content);
        } else {
            $content .= $unsubscribe_html;
        }
    } else {
        // Add plain text unsubscribe link
        $unsubscribe_text = "\n\n---\nYou received this email because you are subscribed to The Framers' Method newsletter.\nManage Preferences: " . $preferences_url . "\nUnsubscribe: " . $unsubscribe_url;
        $content .= $unsubscribe_text;
    }
    
    return $content;
}

/**
 * Check if subscriber should receive emails now based on frequency
 */
function shouldReceiveEmailNow($pdo, $subscriber) {
    // Always send if no frequency preference set
    $frequency = $subscriber['email_frequency'] ?? 'daily';
    
    if ($frequency === 'daily') {
        return true;
    }
    
    // Get last email sent to this subscriber
    $stmt = $pdo->prepare("
        SELECT MAX(sent_at) as last_sent 
        FROM campaign_sends 
        WHERE subscriber_id = ? AND status = 'sent'
    ");
    $stmt->execute([$subscriber['id']]);
    $lastSent = $stmt->fetchColumn();
    
    if (!$lastSent) {
        return true; // No emails sent yet
    }
    
    $daysSinceLastEmail = (time() - strtotime($lastSent)) / 86400;
    
    switch ($frequency) {
        case 'weekly':
            return $daysSinceLastEmail >= 7;
        case 'biweekly':
            return $daysSinceLastEmail >= 14;
        case 'monthly':
            return $daysSinceLastEmail >= 30;
        default:
            return true;
    }
}

/**
 * Detect bounce type from SMTP error message
 */
function detectBounceFromError($errorMessage) {
    $errorLower = strtolower($errorMessage);
    
    // Hard bounce indicators
    $hardBounceKeywords = [
        'user unknown',
        'no such user',
        'invalid recipient',
        'does not exist',
        'unknown user',
        'mailbox unavailable',
        'recipient address rejected',
        '550',
        '551',
        '553',
        'permanent failure'
    ];
    
    // Soft bounce indicators
    $softBounceKeywords = [
        'mailbox full',
        'quota exceeded',
        'temporary failure',
        'try again later',
        'server busy',
        'connection timeout',
        '422',
        '450',
        '451',
        '452'
    ];
    
    // Check for hard bounces first
    foreach ($hardBounceKeywords as $keyword) {
        if (strpos($errorLower, $keyword) !== false) {
            return 'hard';
        }
    }
    
    // Check for soft bounces
    foreach ($softBounceKeywords as $keyword) {
        if (strpos($errorLower, $keyword) !== false) {
            return 'soft';
        }
    }
    
    // If error contains 5xx codes (except those already categorized), assume hard bounce
    if (preg_match('/\b5\d{2}\b/', $errorMessage)) {
        return 'hard';
    }
    
    // If error contains 4xx codes (except those already categorized), assume soft bounce
    if (preg_match('/\b4\d{2}\b/', $errorMessage)) {
        return 'soft';
    }
    
    return null; // No bounce detected
}

/**
 * Process detected bounce
 */
function processBounceDetection($pdo, $subscriber, $campaignId, $bounceType, $errorMessage) {
    try {
        // Insert bounce record
        $stmt = $pdo->prepare("
            INSERT INTO email_bounces (campaign_id, subscriber_id, email, bounce_type, bounce_reason, bounce_message) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $campaignId,
            $subscriber['id'],
            $subscriber['email'],
            $bounceType,
            'SMTP Error',
            $errorMessage
        ]);
        
        // Update subscriber bounce status
        updateSubscriberBounceStatus($pdo, $subscriber['id'], $bounceType);
        
        // Update campaign bounce stats
        updateCampaignBounceStats($pdo, $campaignId);
        
    } catch (Exception $e) {
        // Log error but don't stop processing
        error_log("Bounce processing error: " . $e->getMessage());
    }
}

/**
 * Update subscriber bounce status
 */
function updateSubscriberBounceStatus($pdo, $subscriberId, $bounceType) {
    // Get current bounce count
    $stmt = $pdo->prepare("SELECT bounce_count, bounce_status FROM subscribers WHERE id = ?");
    $stmt->execute([$subscriberId]);
    $subscriber = $stmt->fetch();
    
    $newBounceCount = ($subscriber['bounce_count'] ?? 0) + 1;
    $newBounceStatus = 'active';
    
    // Determine new status based on bounce type and count
    switch ($bounceType) {
        case 'hard':
            $newBounceStatus = 'hard_bounce';
            break;
        case 'complaint':
            $newBounceStatus = 'complaint';
            break;
        case 'soft':
            // After 3 soft bounces, mark as soft_bounce
            if ($newBounceCount >= 3) {
                $newBounceStatus = 'soft_bounce';
            }
            break;
    }
    
    // Update subscriber bounce information
    $stmt = $pdo->prepare("
        UPDATE subscribers 
        SET bounce_count = ?, last_bounce_at = NOW(), bounce_status = ?
        WHERE id = ?
    ");
    $stmt->execute([$newBounceCount, $newBounceStatus, $subscriberId]);
    
    // If hard bounce or complaint, also update main status to unsubscribed
    if (in_array($newBounceStatus, ['hard_bounce', 'complaint'])) {
        $stmt = $pdo->prepare("UPDATE subscribers SET status = 'unsubscribed' WHERE id = ?");
        $stmt->execute([$subscriberId]);
    }
}

/**
 * Update campaign bounce statistics
 */
function updateCampaignBounceStats($pdo, $campaignId) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_bounces,
            SUM(CASE WHEN bounce_type = 'complaint' THEN 1 ELSE 0 END) as complaints
        FROM email_bounces 
        WHERE campaign_id = ?
    ");
    $stmt->execute([$campaignId]);
    $stats = $stmt->fetch();
    
    $stmt = $pdo->prepare("
        UPDATE campaigns 
        SET total_bounced = ?, total_complaints = ?
        WHERE id = ?
    ");
    $stmt->execute([$stats['total_bounces'], $stats['complaints'], $campaignId]);
}
?>