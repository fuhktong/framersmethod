<?php
/**
 * Bounce Handler API
 * Processes email bounces and manages subscriber status
 */
require_once 'database.php';

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
            $action = $_GET['action'] ?? 'process';
            
            if ($action === 'process') {
                $response['data'] = processBounce($pdo, $input);
            } elseif ($action === 'webhook') {
                $response['data'] = handleBounceWebhook($pdo, $input);
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        case 'GET':
            $action = $_GET['action'] ?? 'list';
            
            if ($action === 'list') {
                $response['data'] = getBounces($pdo);
            } elseif ($action === 'stats') {
                $response['data'] = getBounceStats($pdo);
            } elseif ($action === 'subscriber') {
                $subscriberId = (int)($_GET['subscriber_id'] ?? 0);
                if ($subscriberId > 0) {
                    $response['data'] = getSubscriberBounces($pdo, $subscriberId);
                } else {
                    throw new Exception('Subscriber ID required');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        case 'PUT':
            $action = $_GET['action'] ?? 'reactivate';
            
            if ($action === 'reactivate') {
                $subscriberId = (int)($_GET['subscriber_id'] ?? 0);
                if ($subscriberId > 0) {
                    $response['data'] = reactivateSubscriber($pdo, $subscriberId);
                } else {
                    throw new Exception('Subscriber ID required');
                }
            } else {
                throw new Exception('Invalid action');
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

/**
 * Process a bounce event
 */
function processBounce($pdo, $data) {
    $email = trim($data['email'] ?? '');
    $bounceType = $data['bounce_type'] ?? 'soft';
    $bounceReason = $data['bounce_reason'] ?? '';
    $bounceCode = $data['bounce_code'] ?? '';
    $bounceMessage = $data['bounce_message'] ?? '';
    $campaignId = (int)($data['campaign_id'] ?? 0);
    
    if (empty($email)) {
        throw new Exception('Email address is required');
    }
    
    if (!in_array($bounceType, ['hard', 'soft', 'complaint'])) {
        throw new Exception('Invalid bounce type');
    }
    
    // Find subscriber
    $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $subscriber = $stmt->fetch();
    
    if (!$subscriber) {
        throw new Exception('Subscriber not found');
    }
    
    $subscriberId = $subscriber['id'];
    
    // Insert bounce record
    $stmt = $pdo->prepare("
        INSERT INTO email_bounces (campaign_id, subscriber_id, email, bounce_type, bounce_reason, bounce_code, bounce_message) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$campaignId, $subscriberId, $email, $bounceType, $bounceReason, $bounceCode, $bounceMessage]);
    
    // Update subscriber bounce status
    updateSubscriberBounceStatus($pdo, $subscriberId, $bounceType);
    
    // Update campaign_sends record if exists
    if ($campaignId > 0) {
        $stmt = $pdo->prepare("
            UPDATE campaign_sends 
            SET bounce_type = ?, bounce_reason = ?, bounced_at = NOW() 
            WHERE campaign_id = ? AND subscriber_id = ?
        ");
        $stmt->execute([$bounceType, $bounceReason, $campaignId, $subscriberId]);
        
        // Update campaign bounce counts
        updateCampaignBounceStats($pdo, $campaignId);
    }
    
    return [
        'message' => 'Bounce processed successfully',
        'subscriber_id' => $subscriberId,
        'bounce_type' => $bounceType,
        'action_taken' => getBounceAction($bounceType)
    ];
}

/**
 * Handle bounce webhook from email service providers
 */
function handleBounceWebhook($pdo, $data) {
    // This would be customized based on your email provider's webhook format
    // Examples: SendGrid, Mailgun, Amazon SES, etc.
    
    $bounces = [];
    
    // Example for a generic webhook format
    if (isset($data['events'])) {
        foreach ($data['events'] as $event) {
            if (in_array($event['event'], ['bounce', 'dropped', 'spamreport'])) {
                $bounceData = [
                    'email' => $event['email'],
                    'bounce_type' => determineBounceType($event),
                    'bounce_reason' => $event['reason'] ?? '',
                    'bounce_code' => $event['status'] ?? '',
                    'bounce_message' => $event['message'] ?? '',
                    'campaign_id' => extractCampaignId($event)
                ];
                
                $bounces[] = processBounce($pdo, $bounceData);
            }
        }
    }
    
    return [
        'processed_bounces' => count($bounces),
        'bounces' => $bounces
    ];
}

/**
 * Get bounce list with filtering
 */
function getBounces($pdo, $limit = 50, $offset = 0) {
    $bounceType = $_GET['bounce_type'] ?? 'all';
    $email = $_GET['email'] ?? '';
    
    $where = [];
    $params = [];
    
    if ($bounceType !== 'all') {
        $where[] = 'eb.bounce_type = ?';
        $params[] = $bounceType;
    }
    
    if (!empty($email)) {
        $where[] = 'eb.email LIKE ?';
        $params[] = '%' . $email . '%';
    }
    
    $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
    
    $stmt = $pdo->prepare("
        SELECT 
            eb.*,
            s.name as subscriber_name,
            c.subject as campaign_subject
        FROM email_bounces eb
        LEFT JOIN subscribers s ON eb.subscriber_id = s.id
        LEFT JOIN campaigns c ON eb.campaign_id = c.id
        $whereClause
        ORDER BY eb.bounced_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

/**
 * Get bounce statistics
 */
function getBounceStats($pdo) {
    // Overall bounce stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_bounces,
            SUM(CASE WHEN bounce_type = 'hard' THEN 1 ELSE 0 END) as hard_bounces,
            SUM(CASE WHEN bounce_type = 'soft' THEN 1 ELSE 0 END) as soft_bounces,
            SUM(CASE WHEN bounce_type = 'complaint' THEN 1 ELSE 0 END) as complaints,
            COUNT(DISTINCT subscriber_id) as affected_subscribers
        FROM email_bounces
        WHERE bounced_at >= DATE_SUB(NOW(), INTERVAL 30 DAYS)
    ");
    $stmt->execute();
    $overallStats = $stmt->fetch();
    
    // Bounce rate by campaign
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.subject,
            c.total_sent,
            COUNT(eb.id) as bounces,
            ROUND((COUNT(eb.id) / c.total_sent) * 100, 2) as bounce_rate
        FROM campaigns c
        LEFT JOIN email_bounces eb ON c.id = eb.campaign_id
        WHERE c.total_sent > 0
        GROUP BY c.id
        ORDER BY bounce_rate DESC
        LIMIT 10
    ");
    $stmt->execute();
    $campaignStats = $stmt->fetchAll();
    
    // Subscribers by bounce status
    $stmt = $pdo->prepare("
        SELECT 
            bounce_status,
            COUNT(*) as count
        FROM subscribers
        GROUP BY bounce_status
    ");
    $stmt->execute();
    $subscriberStats = $stmt->fetchAll();
    
    return [
        'overall' => $overallStats,
        'by_campaign' => $campaignStats,
        'by_subscriber_status' => $subscriberStats
    ];
}

/**
 * Get bounces for specific subscriber
 */
function getSubscriberBounces($pdo, $subscriberId) {
    $stmt = $pdo->prepare("
        SELECT 
            eb.*,
            c.subject as campaign_subject
        FROM email_bounces eb
        LEFT JOIN campaigns c ON eb.campaign_id = c.id
        WHERE eb.subscriber_id = ?
        ORDER BY eb.bounced_at DESC
    ");
    $stmt->execute([$subscriberId]);
    
    return $stmt->fetchAll();
}

/**
 * Reactivate a bounced subscriber
 */
function reactivateSubscriber($pdo, $subscriberId) {
    $stmt = $pdo->prepare("
        UPDATE subscribers 
        SET bounce_status = 'active', bounce_count = 0, last_bounce_at = NULL 
        WHERE id = ?
    ");
    $stmt->execute([$subscriberId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Subscriber not found');
    }
    
    return ['message' => 'Subscriber reactivated successfully'];
}

/**
 * Update subscriber bounce status based on bounce type
 */
function updateSubscriberBounceStatus($pdo, $subscriberId, $bounceType) {
    // Get current bounce count
    $stmt = $pdo->prepare("SELECT bounce_count FROM subscribers WHERE id = ?");
    $stmt->execute([$subscriberId]);
    $subscriber = $stmt->fetch();
    
    $newBounceCount = ($subscriber['bounce_count'] ?? 0) + 1;
    $newStatus = 'active';
    
    // Determine new status based on bounce type and count
    switch ($bounceType) {
        case 'hard':
            $newStatus = 'hard_bounce';
            break;
        case 'complaint':
            $newStatus = 'complaint';
            break;
        case 'soft':
            // After 3 soft bounces, mark as soft_bounce
            if ($newBounceCount >= 3) {
                $newStatus = 'soft_bounce';
            }
            break;
    }
    
    // Update subscriber
    $stmt = $pdo->prepare("
        UPDATE subscribers 
        SET bounce_count = ?, last_bounce_at = NOW(), bounce_status = ?
        WHERE id = ?
    ");
    $stmt->execute([$newBounceCount, $newStatus, $subscriberId]);
    
    // If hard bounce or complaint, also update main status
    if (in_array($newStatus, ['hard_bounce', 'complaint'])) {
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

/**
 * Determine bounce type from webhook event
 */
function determineBounceType($event) {
    $eventType = strtolower($event['event'] ?? '');
    $reason = strtolower($event['reason'] ?? '');
    
    // Hard bounces
    if (in_array($eventType, ['bounce', 'dropped']) && 
        (strpos($reason, 'invalid') !== false || 
         strpos($reason, 'not exist') !== false ||
         strpos($reason, 'unknown user') !== false)) {
        return 'hard';
    }
    
    // Complaints
    if ($eventType === 'spamreport') {
        return 'complaint';
    }
    
    // Default to soft bounce
    return 'soft';
}

/**
 * Extract campaign ID from webhook event
 */
function extractCampaignId($event) {
    // Look for campaign ID in custom headers or metadata
    if (isset($event['unique_args']['campaign_id'])) {
        return (int)$event['unique_args']['campaign_id'];
    }
    
    if (isset($event['metadata']['campaign_id'])) {
        return (int)$event['metadata']['campaign_id'];
    }
    
    return 0;
}

/**
 * Get action description for bounce type
 */
function getBounceAction($bounceType) {
    switch ($bounceType) {
        case 'hard':
            return 'Subscriber marked as hard bounce and unsubscribed';
        case 'complaint':
            return 'Subscriber marked as complaint and unsubscribed';
        case 'soft':
            return 'Soft bounce recorded, subscriber remains active';
        default:
            return 'Bounce recorded';
    }
}
?>