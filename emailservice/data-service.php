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
            
        case 'lists':
            $response['data'] = getSubscriberLists($pdo);
            break;
            
        case 'create_list':
            $input = json_decode(file_get_contents('php://input'), true);
            $response['data'] = createSubscriberList($pdo, $input);
            break;
            
        case 'delete_list':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $response['data'] = deleteSubscriberList($pdo, $id);
            } else {
                throw new Exception('List ID required');
            }
            break;
            
        case 'subscriber_lists':
            $subscriber_id = (int)($_GET['subscriber_id'] ?? 0);
            if ($subscriber_id > 0) {
                $response['data'] = getSubscriberListMemberships($pdo, $subscriber_id);
            } else {
                throw new Exception('Subscriber ID required');
            }
            break;
            
        case 'update_subscriber_lists':
            $input = json_decode(file_get_contents('php://input'), true);
            $response['data'] = updateSubscriberListMemberships($pdo, $input);
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
    
    // Get list memberships for each subscriber
    if (!empty($subscribers)) {
        $subscriberIds = array_column($subscribers, 'id');
        $placeholders = str_repeat('?,', count($subscriberIds) - 1) . '?';
        
        $listSql = "
            SELECT m.subscriber_id, l.name
            FROM subscriber_list_memberships m
            JOIN subscriber_lists l ON m.list_id = l.id
            WHERE m.subscriber_id IN ($placeholders)
            ORDER BY l.name
        ";
        
        $stmt = $pdo->prepare($listSql);
        $stmt->execute($subscriberIds);
        $memberships = $stmt->fetchAll();
        
        // Group lists by subscriber
        $listsBySubscriber = [];
        foreach ($memberships as $membership) {
            $listsBySubscriber[$membership['subscriber_id']][] = $membership['name'];
        }
        
        // Add lists to subscriber data
        foreach ($subscribers as &$subscriber) {
            $subscriber['lists'] = $listsBySubscriber[$subscriber['id']] ?? [];
        }
    }
    
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

/**
 * Get all subscriber lists
 */
function getSubscriberLists($pdo) {
    $stmt = $pdo->prepare("
        SELECT l.*, 
               COUNT(m.subscriber_id) as subscriber_count
        FROM subscriber_lists l
        LEFT JOIN subscriber_list_memberships m ON l.id = m.list_id
        GROUP BY l.id
        ORDER BY l.name
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Create a new subscriber list
 */
function createSubscriberList($pdo, $data) {
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    
    if (empty($name)) {
        throw new Exception('List name is required');
    }
    
    // Check if list name already exists
    $stmt = $pdo->prepare("SELECT id FROM subscriber_lists WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('A list with this name already exists');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO subscriber_lists (name, description) 
        VALUES (?, ?)
    ");
    $stmt->execute([$name, $description]);
    
    $id = $pdo->lastInsertId();
    
    // Return the new list
    $stmt = $pdo->prepare("SELECT * FROM subscriber_lists WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Delete a subscriber list
 */
function deleteSubscriberList($pdo, $id) {
    // Prevent deletion of "All Subscribers" list
    $stmt = $pdo->prepare("SELECT name FROM subscriber_lists WHERE id = ?");
    $stmt->execute([$id]);
    $list = $stmt->fetch();
    
    if (!$list) {
        throw new Exception('List not found');
    }
    
    if ($list['name'] === 'All Subscribers') {
        throw new Exception('Cannot delete the "All Subscribers" list');
    }
    
    // Delete the list (memberships will be deleted by foreign key cascade)
    $stmt = $pdo->prepare("DELETE FROM subscriber_lists WHERE id = ?");
    $stmt->execute([$id]);
    
    return ['message' => 'List deleted successfully'];
}

/**
 * Get list memberships for a subscriber
 */
function getSubscriberListMemberships($pdo, $subscriber_id) {
    $stmt = $pdo->prepare("
        SELECT list_id 
        FROM subscriber_list_memberships 
        WHERE subscriber_id = ?
    ");
    $stmt->execute([$subscriber_id]);
    
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/**
 * Update subscriber list memberships
 */
function updateSubscriberListMemberships($pdo, $data) {
    $subscriber_id = (int)($data['subscriber_id'] ?? 0);
    $list_ids = $data['list_ids'] ?? [];
    
    if ($subscriber_id <= 0) {
        throw new Exception('Invalid subscriber ID');
    }
    
    // Ensure "All Subscribers" list is always included
    $stmt = $pdo->prepare("SELECT id FROM subscriber_lists WHERE name = 'All Subscribers'");
    $stmt->execute();
    $all_subscribers_id = $stmt->fetchColumn();
    
    if ($all_subscribers_id && !in_array($all_subscribers_id, $list_ids)) {
        $list_ids[] = $all_subscribers_id;
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Remove all current memberships
        $stmt = $pdo->prepare("DELETE FROM subscriber_list_memberships WHERE subscriber_id = ?");
        $stmt->execute([$subscriber_id]);
        
        // Add new memberships
        if (!empty($list_ids)) {
            $stmt = $pdo->prepare("
                INSERT INTO subscriber_list_memberships (subscriber_id, list_id) 
                VALUES (?, ?)
            ");
            
            foreach ($list_ids as $list_id) {
                $stmt->execute([$subscriber_id, (int)$list_id]);
            }
        }
        
        $pdo->commit();
        return ['message' => 'List memberships updated successfully'];
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}
?>