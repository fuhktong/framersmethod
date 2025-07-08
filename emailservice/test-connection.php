<?php
/**
 * Test database connection and check tables
 */
require_once 'database.php';

header('Content-Type: application/json');

try {
    $result = testDatabaseConnection();
    
    if ($result['success']) {
        $pdo = getDatabaseConnection();
        
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
        
        // Check table structure
        if (in_array('subscribers', $result['existing_tables'])) {
            $stmt = $pdo->query("DESCRIBE subscribers");
            $result['subscriber_table_structure'] = $stmt->fetchAll();
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