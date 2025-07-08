<?php
/**
 * Subscriber Management API
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
            // Add new subscriber
            $response['data'] = addSubscriber($pdo, $input);
            break;
            
        case 'PUT':
            // Update subscriber
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $response['data'] = updateSubscriber($pdo, $id, $input);
            } else {
                throw new Exception('Subscriber ID required for update');
            }
            break;
            
        case 'DELETE':
            $action = $_GET['action'] ?? 'single';
            
            if ($action === 'bulk') {
                // Bulk operations
                $ids = $input['ids'] ?? [];
                $operation = $input['operation'] ?? '';
                $response['data'] = bulkOperations($pdo, $ids, $operation);
            } else {
                // Single delete
                $id = (int)($_GET['id'] ?? 0);
                if ($id > 0) {
                    $response['data'] = deleteSubscriber($pdo, $id);
                } else {
                    throw new Exception('Subscriber ID required for delete');
                }
            }
            break;
            
        case 'GET':
            // Get single subscriber
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $response['data'] = getSubscriber($pdo, $id);
            } else {
                throw new Exception('Subscriber ID required');
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

// Add new subscriber
function addSubscriber($pdo, $data) {
    $email = trim($data['email'] ?? '');
    $name = trim($data['name'] ?? '');
    
    if (empty($email)) {
        throw new Exception('Email is required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already exists');
    }
    
    // Generate unsubscribe token
    $unsubscribe_token = hash('sha256', $email . time() . rand());
    
    // Insert new subscriber
    $stmt = $pdo->prepare("
        INSERT INTO subscribers (email, name, status, unsubscribe_token, subscribed_at) 
        VALUES (?, ?, 'active', ?, NOW())
    ");
    $stmt->execute([$email, $name ?: null, $unsubscribe_token]);
    
    $id = $pdo->lastInsertId();
    
    // Return the new subscriber
    return getSubscriber($pdo, $id);
}

// Update subscriber
function updateSubscriber($pdo, $id, $data) {
    $email = trim($data['email'] ?? '');
    $name = trim($data['name'] ?? '');
    $status = $data['status'] ?? 'active';
    
    if (empty($email)) {
        throw new Exception('Email is required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    if (!in_array($status, ['active', 'unsubscribed'])) {
        throw new Exception('Invalid status');
    }
    
    // Check if subscriber exists
    $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Subscriber not found');
    }
    
    // Check if email is already used by another subscriber
    $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already exists');
    }
    
    // Update subscriber
    $stmt = $pdo->prepare("
        UPDATE subscribers 
        SET email = ?, name = ?, status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$email, $name ?: null, $status, $id]);
    
    // If status changed to unsubscribed, log it
    if ($status === 'unsubscribed') {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO unsubscribes (email, reason, unsubscribed_at) 
            VALUES (?, 'Manual unsubscribe', NOW())
        ");
        $stmt->execute([$email]);
    }
    
    // Return updated subscriber
    return getSubscriber($pdo, $id);
}

// Delete subscriber
function deleteSubscriber($pdo, $id) {
    // Check if subscriber exists
    $stmt = $pdo->prepare("SELECT email FROM subscribers WHERE id = ?");
    $stmt->execute([$id]);
    $subscriber = $stmt->fetch();
    
    if (!$subscriber) {
        throw new Exception('Subscriber not found');
    }
    
    // Log the unsubscribe
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO unsubscribes (email, reason, unsubscribed_at) 
        VALUES (?, 'Deleted by admin', NOW())
    ");
    $stmt->execute([$subscriber['email']]);
    
    // Delete subscriber
    $stmt = $pdo->prepare("DELETE FROM subscribers WHERE id = ?");
    $stmt->execute([$id]);
    
    return ['message' => 'Subscriber deleted successfully'];
}

// Get single subscriber
function getSubscriber($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT id, email, name, status, subscribed_at, updated_at 
        FROM subscribers 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $subscriber = $stmt->fetch();
    
    if (!$subscriber) {
        throw new Exception('Subscriber not found');
    }
    
    return $subscriber;
}

// Bulk operations
function bulkOperations($pdo, $ids, $operation) {
    if (empty($ids) || !is_array($ids)) {
        throw new Exception('No subscribers selected');
    }
    
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    switch ($operation) {
        case 'delete':
            // Get emails for logging
            $stmt = $pdo->prepare("SELECT email FROM subscribers WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Log unsubscribes
            foreach ($emails as $email) {
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO unsubscribes (email, reason, unsubscribed_at) 
                    VALUES (?, 'Bulk deleted by admin', NOW())
                ");
                $stmt->execute([$email]);
            }
            
            // Delete subscribers
            $stmt = $pdo->prepare("DELETE FROM subscribers WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            
            $affected = $stmt->rowCount();
            return ['message' => "$affected subscribers deleted successfully"];
            
        case 'unsubscribe':
            // Get emails for logging
            $stmt = $pdo->prepare("SELECT email FROM subscribers WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Log unsubscribes
            foreach ($emails as $email) {
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO unsubscribes (email, reason, unsubscribed_at) 
                    VALUES (?, 'Bulk unsubscribed by admin', NOW())
                ");
                $stmt->execute([$email]);
            }
            
            // Update status
            $stmt = $pdo->prepare("
                UPDATE subscribers 
                SET status = 'unsubscribed', updated_at = NOW() 
                WHERE id IN ($placeholders)
            ");
            $stmt->execute($ids);
            
            $affected = $stmt->rowCount();
            return ['message' => "$affected subscribers unsubscribed successfully"];
            
        default:
            throw new Exception('Invalid bulk operation');
    }
}
?>