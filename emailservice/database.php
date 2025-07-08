<?php
/**
 * Database Connection for Email Service
 */

// Include environment loader from contact directory
require_once __DIR__ . '/../contact/env_loader.php';

// Try different .env file paths for different environments
$env_paths = [
    __DIR__ . '/../.env',      // Production path (one level up)
    __DIR__ . '/../../.env',   // Alternative path
];

$env_loaded = false;
foreach ($env_paths as $env_path) {
    if (file_exists($env_path)) {
        loadEnv($env_path);
        $env_loaded = true;
        break;
    }
}

if (!$env_loaded) {
    throw new Exception('.env file not found in any expected location');
}

function getDatabaseConnection() {
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
    $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME');
    $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
    
    if (empty($dbname) || empty($username)) {
        throw new Exception('Database configuration is missing. Please check your .env file.');
    }
    
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
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

// Test connection function
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        
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
            'message' => 'Database connection successful',
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