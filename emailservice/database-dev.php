<?php
/**
 * Development Database Connection (Hardcoded for local testing)
 */

function getDatabaseConnectionDev() {
    // Development database settings - update these for your local setup
    $host = 'localhost';
    $dbname = 'framersmethod';  // Your local database name
    $username = 'root';         // Your local username
    $password = 'root';         // MAMP default password is usually 'root'
    
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Development database connection failed: ' . $e->getMessage());
    }
}

// Test connection function for dev
function testDatabaseConnectionDev() {
    try {
        $pdo = getDatabaseConnectionDev();
        
        // Test if our tables exist
        $tables = ['subscribers', 'campaigns', 'campaign_sends', 'unsubscribes'];
        $existing_tables = [];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $existing_tables[] = $table;
            }
        }
        
        return [
            'success' => true,
            'message' => 'Development database connection successful',
            'existing_tables' => $existing_tables,
            'missing_tables' => array_diff($tables, $existing_tables)
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>