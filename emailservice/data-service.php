<?php
/**
 * Data service for email service dashboard
 */
require_once 'database.php';

header('Content-Type: application/json');

// Get the action from request
$action = $_GET['action'] ?? '';

try {
    $pdo = getDatabaseConnection();
    $response = ['success' => true];
    
    switch ($action) {
        case 'dashboard':
            $response['data'] = getDashboardStats($pdo);
            break;
            
        case 'subscribers':
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 50);
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? 'all';
            $response['data'] = getSubscribers($pdo, $page, $limit, $search, $status);
            break;
            
        case 'campaigns':
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 50);
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? 'all';
            $response['data'] = getCampaigns($pdo, $page, $limit, $search, $status);
            break;
            
        case 'campaign':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $response['data'] = getCampaignDetails($pdo, $id);
            } else {
                throw new Exception('Campaign ID required');
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);

// Dashboard statistics
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total subscribers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM subscribers WHERE status = 'active'");
    $stats['total_subscribers'] = $stmt->fetch()['total'];
    
    // Total campaigns
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM campaigns");
    $stats['total_campaigns'] = $stmt->fetch()['total'];
    
    // Total emails sent
    $stmt = $pdo->query("SELECT SUM(total_sent) as total FROM campaigns WHERE status = 'sent'");
    $result = $stmt->fetch();
    $stats['total_sent'] = $result['total'] ?? 0;
    
    // Recent activity (last 5 campaigns)
    $stmt = $pdo->query("
        SELECT subject, status, created_at, sent_at 
        FROM campaigns 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stats['recent_campaigns'] = $stmt->fetchAll();
    
    // Stats by status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM subscribers 
        GROUP BY status
    ");
    $subscriber_stats = [];
    while ($row = $stmt->fetch()) {
        $subscriber_stats[$row['status']] = $row['count'];
    }
    $stats['subscriber_stats'] = $subscriber_stats;
    
    return $stats;
}

// Get subscribers with pagination and filtering
function getSubscribers($pdo, $page = 1, $limit = 50, $search = '', $status = 'all') {
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(email LIKE ? OR name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($status !== 'all') {
        $where[] = "status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM subscribers $whereClause";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalCount = $stmt->fetch()['total'];
    
    // Get subscribers
    $sql = "
        SELECT id, email, name, status, subscribed_at, updated_at
        FROM subscribers 
        $whereClause
        ORDER BY subscribed_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $subscribers = $stmt->fetchAll();
    
    return [
        'subscribers' => $subscribers,
        'total' => $totalCount,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($totalCount / $limit)
    ];
}

// Get campaigns with pagination and filtering
function getCampaigns($pdo, $page = 1, $limit = 50, $search = '', $status = 'all') {
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "subject LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($status !== 'all') {
        $where[] = "status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM campaigns $whereClause";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalCount = $stmt->fetch()['total'];
    
    // Get campaigns
    $sql = "
        SELECT id, subject, status, total_recipients, total_sent, 
               total_opened, total_clicked, created_at, sent_at
        FROM campaigns 
        $whereClause
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $campaigns = $stmt->fetchAll();
    
    return [
        'campaigns' => $campaigns,
        'total' => $totalCount,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($totalCount / $limit)
    ];
}

// Get campaign details
function getCampaignDetails($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT * FROM campaigns WHERE id = ?
    ");
    $stmt->execute([$id]);
    $campaign = $stmt->fetch();
    
    if (!$campaign) {
        throw new Exception('Campaign not found');
    }
    
    // Get send details
    $stmt = $pdo->prepare("
        SELECT cs.*, s.email, s.name 
        FROM campaign_sends cs
        JOIN subscribers s ON cs.subscriber_id = s.id
        WHERE cs.campaign_id = ?
        ORDER BY cs.sent_at DESC
        LIMIT 100
    ");
    $stmt->execute([$id]);
    $sends = $stmt->fetchAll();
    
    $campaign['sends'] = $sends;
    
    return $campaign;
}
?>