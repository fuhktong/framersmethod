<?php
/**
 * Debug database connection with detailed error reporting
 */

echo "<h2>Database Connection Debug</h2>";

// Include environment loader from contact directory
require_once __DIR__ . '/../contact/env_loader.php';

// Try different .env file paths for different environments
$env_paths = [
    __DIR__ . '/../.env',      // Production path (one level up)
    __DIR__ . '/../../.env',   // Alternative path
];

echo "<h3>Environment Loading:</h3>";
$env_loaded = false;
foreach ($env_paths as $env_path) {
    echo "<p>Trying: $env_path - ";
    if (file_exists($env_path)) {
        echo "EXISTS - ";
        try {
            loadEnv($env_path);
            echo "LOADED SUCCESSFULLY</p>";
            $env_loaded = true;
            break;
        } catch (Exception $e) {
            echo "LOAD FAILED: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "NOT FOUND</p>";
    }
}

if (!$env_loaded) {
    echo "<p><strong>ERROR: .env file not found in any expected location</strong></p>";
    exit;
}

// Check environment variables
echo "<h3>Environment Variables:</h3>";
$db_vars = ['DB_HOST', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD'];
foreach ($db_vars as $var) {
    $env_value = $_ENV[$var] ?? null;
    $getenv_value = getenv($var);
    echo "<p><strong>$var:</strong> ";
    echo "ENV: " . ($env_value ? "'" . $env_value . "'" : 'NOT SET') . " | ";
    echo "getenv: " . ($getenv_value ? "'" . $getenv_value . "'" : 'NOT SET');
    echo "</p>";
}

// Try database connection
echo "<h3>Database Connection Test:</h3>";
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
$username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME');
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');

echo "<p><strong>Connection String:</strong> mysql:host=$host;dbname=$dbname;charset=utf8mb4</p>";
echo "<p><strong>Username:</strong> $username</p>";
echo "<p><strong>Password:</strong> " . (empty($password) ? 'EMPTY' : 'SET (length: ' . strlen($password) . ')') . "</p>";

if (empty($dbname) || empty($username)) {
    echo "<p><strong>ERROR: Database configuration is missing!</strong></p>";
    exit;
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
    
    echo "<p><strong>SUCCESS: Database connection established!</strong></p>";
    
    // Test query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "<p><strong>MySQL Version:</strong> " . $result['version'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p><strong>ERROR: Database connection failed!</strong></p>";
    echo "<p><strong>Error Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
}
?>