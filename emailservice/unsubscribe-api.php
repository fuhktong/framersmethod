<?php
/**
 * Unsubscribe API
 * Handles unsubscribe requests and preference management
 */
require_once 'database.php';

header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDatabaseConnection();
    $response = ['success' => true];
    
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? 'info';
            $token = $_GET['token'] ?? '';
            
            if ($action === 'info') {
                $response['data'] = getSubscriberInfo($pdo, $token);
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $response['data'] = processUnsubscribeAction($pdo, $input);
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

/**
 * Get subscriber information by token
 */
function getSubscriberInfo($pdo, $token) {
    if (empty($token)) {
        throw new Exception('Unsubscribe token is required');
    }
    
    $stmt = $pdo->prepare("
        SELECT id, email, name, status, subscribed_at, updated_at
        FROM subscribers 
        WHERE unsubscribe_token = ?
    ");
    $stmt->execute([$token]);
    $subscriber = $stmt->fetch();
    
    if (!$subscriber) {
        throw new Exception('Invalid unsubscribe link. The link may be expired or invalid.');
    }
    
    return $subscriber;
}

/**
 * Process unsubscribe action
 */
function processUnsubscribeAction($pdo, $data) {
    $token = $data['token'] ?? '';
    $action = $data['action'] ?? '';
    $reasons = $data['reasons'] ?? [];
    $customReason = $data['custom_reason'] ?? '';
    
    if (empty($token)) {
        throw new Exception('Unsubscribe token is required');
    }
    
    if (empty($action)) {
        throw new Exception('Action is required');
    }
    
    // Get subscriber
    $stmt = $pdo->prepare("SELECT id, email, name, status FROM subscribers WHERE unsubscribe_token = ?");
    $stmt->execute([$token]);
    $subscriber = $stmt->fetch();
    
    if (!$subscriber) {
        throw new Exception('Invalid unsubscribe link');
    }
    
    if ($subscriber['status'] === 'unsubscribed' && $action === 'unsubscribe') {
        return ['message' => 'You are already unsubscribed from our mailing list.'];
    }
    
    switch ($action) {
        case 'pause':
            return pauseSubscription($pdo, $subscriber, $data);
            
        case 'reduce':
            return reduceFrequency($pdo, $subscriber, $data);
            
        case 'unsubscribe':
            return unsubscribeCompletely($pdo, $subscriber, $reasons, $customReason);
            
        default:
            throw new Exception('Invalid action');
    }
}

/**
 * Pause subscription for a period
 */
function pauseSubscription($pdo, $subscriber, $data) {
    $pauseDays = (int)($data['pause_days'] ?? 30);
    
    // Valid pause periods
    if (!in_array($pauseDays, [30, 60, 90])) {
        $pauseDays = 30;
    }
    
    $resumeDate = date('Y-m-d H:i:s', strtotime("+{$pauseDays} days"));
    
    // Update subscriber with pause info
    $stmt = $pdo->prepare("
        UPDATE subscribers 
        SET status = 'paused', 
            updated_at = NOW(),
            resume_date = ?
        WHERE id = ?
    ");
    $stmt->execute([$resumeDate, $subscriber['id']]);
    
    // Add resume_date column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE subscribers ADD COLUMN resume_date DATETIME NULL AFTER updated_at");
    } catch (PDOException $e) {
        // Column probably already exists
    }
    
    // Record the action
    $stmt = $pdo->prepare("
        INSERT INTO unsubscribes (email, reason, campaign_id, unsubscribed_at) 
        VALUES (?, ?, NULL, NOW())
    ");
    $stmt->execute([$subscriber['email'], "Paused for {$pauseDays} days"]);
    
    return [
        'message' => "Subscription paused for {$pauseDays} days",
        'resume_date' => $resumeDate,
        'pause_days' => $pauseDays
    ];
}

/**
 * Reduce email frequency
 */
function reduceFrequency($pdo, $subscriber, $data) {
    $frequency = $data['frequency'] ?? 'weekly';
    
    // Valid frequencies
    $validFrequencies = ['weekly', 'biweekly', 'monthly'];
    if (!in_array($frequency, $validFrequencies)) {
        $frequency = 'weekly';
    }
    
    // Add email_frequency column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE subscribers ADD COLUMN email_frequency VARCHAR(20) DEFAULT 'daily' AFTER status");
    } catch (PDOException $e) {
        // Column probably already exists
    }
    
    // Update subscriber frequency
    $stmt = $pdo->prepare("
        UPDATE subscribers 
        SET email_frequency = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$frequency, $subscriber['id']]);
    
    // Record the action
    $stmt = $pdo->prepare("
        INSERT INTO unsubscribes (email, reason, campaign_id, unsubscribed_at) 
        VALUES (?, ?, NULL, NOW())
    ");
    $stmt->execute([$subscriber['email'], "Reduced frequency to {$frequency}"]);
    
    return [
        'message' => "Email frequency reduced to {$frequency}",
        'frequency' => $frequency
    ];
}

/**
 * Unsubscribe completely
 */
function unsubscribeCompletely($pdo, $subscriber, $reasons, $customReason) {
    // Update subscriber status
    $stmt = $pdo->prepare("
        UPDATE subscribers 
        SET status = 'unsubscribed', 
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$subscriber['id']]);
    
    // Prepare reason text
    $reasonText = '';
    if (!empty($reasons)) {
        $reasonText = implode(', ', $reasons);
    }
    
    if (!empty($customReason)) {
        $reasonText .= (!empty($reasonText) ? '; ' : '') . $customReason;
    }
    
    if (empty($reasonText)) {
        $reasonText = 'No reason provided';
    }
    
    // Record unsubscribe
    $stmt = $pdo->prepare("
        INSERT INTO unsubscribes (email, reason, campaign_id, unsubscribed_at) 
        VALUES (?, ?, NULL, NOW())
    ");
    $stmt->execute([$subscriber['email'], $reasonText]);
    
    return [
        'message' => 'You have been successfully unsubscribed from our mailing list',
        'email' => $subscriber['email']
    ];
}

/**
 * Check if subscriber should receive emails based on frequency
 */
function shouldReceiveEmail($subscriber) {
    if ($subscriber['status'] !== 'active') {
        return false;
    }
    
    // Check if paused and should resume
    if ($subscriber['status'] === 'paused') {
        if (!empty($subscriber['resume_date']) && strtotime($subscriber['resume_date']) <= time()) {
            // Resume subscription
            $pdo = getDatabaseConnection();
            $stmt = $pdo->prepare("UPDATE subscribers SET status = 'active', resume_date = NULL WHERE id = ?");
            $stmt->execute([$subscriber['id']]);
            return true;
        }
        return false;
    }
    
    // Check frequency limits
    $frequency = $subscriber['email_frequency'] ?? 'daily';
    
    if ($frequency === 'daily') {
        return true;
    }
    
    // Get last email sent to this subscriber
    $pdo = getDatabaseConnection();
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
?>