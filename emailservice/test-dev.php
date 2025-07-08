<?php
/**
 * Test development database connection
 */
require_once 'database-dev.php';

header('Content-Type: application/json');

try {
    $result = testDatabaseConnectionDev();
    
    if ($result['success']) {
        $pdo = getDatabaseConnectionDev();
        
        // Check if subscribers table exists and has data
        if (in_array('subscribers', $result['existing_tables'])) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM subscribers");
            $count = $stmt->fetch()['count'];
            $result['subscriber_count'] = $count;
            
            // Get sample data
            $stmt = $pdo->query("SELECT * FROM subscribers LIMIT 5");
            $result['sample_subscribers'] = $stmt->fetchAll();
        }
        
        // Check campaigns table
        if (in_array('campaigns', $result['existing_tables'])) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM campaigns");
            $count = $stmt->fetch()['count'];
            $result['campaign_count'] = $count;
        }
    }
    
} catch (Exception $e) {
    $result = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>