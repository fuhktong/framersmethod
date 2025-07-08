<?php
/**
 * Debug environment variable loading
 */

echo "<h2>Environment Debug</h2>";

// Check if .env file exists
$env_path = __DIR__ . '/../.env';
echo "<p><strong>.env file path:</strong> " . $env_path . "</p>";
echo "<p><strong>.env file exists:</strong> " . (file_exists($env_path) ? 'YES' : 'NO') . "</p>";

if (file_exists($env_path)) {
    echo "<p><strong>.env file contents:</strong></p>";
    echo "<pre>" . htmlspecialchars(file_get_contents($env_path)) . "</pre>";
}

// Try to load environment
try {
    require_once __DIR__ . '/../contact/env_loader.php';
    loadEnv($env_path);
    echo "<p><strong>Environment loading:</strong> SUCCESS</p>";
} catch (Exception $e) {
    echo "<p><strong>Environment loading error:</strong> " . $e->getMessage() . "</p>";
}

// Check environment variables
$db_vars = ['DB_HOST', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD'];
echo "<h3>Database Environment Variables:</h3>";
foreach ($db_vars as $var) {
    $env_value = $_ENV[$var] ?? null;
    $getenv_value = getenv($var);
    echo "<p><strong>$var:</strong> ";
    echo "ENV: " . ($env_value ? 'SET' : 'NOT SET') . " | ";
    echo "getenv: " . ($getenv_value ? 'SET' : 'NOT SET');
    echo "</p>";
}

// Show all environment variables (be careful in production!)
echo "<h3>All Environment Variables:</h3>";
echo "<pre>" . print_r($_ENV, true) . "</pre>";
?>