<?php
// =============================================================
// Database Connection Test
// File: test-db.php
// =============================================================

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Database configuration
$db_config = [
    'host' => $_ENV['DB_HOST'] ?? '145.79.10.205',
    'port' => $_ENV['DB_PORT'] ?? '5432',
    'dbname' => $_ENV['DB_NAME'] ?? 'basdatbackup',
    'user' => $_ENV['DB_USER'] ?? 'dbPro',
    'password' => $_ENV['DB_PASSWORD'] ?? 'Ky7zboJPHIV7fcJIv71CKp7kkLgtDGJTH8chwAeWr6uJfuuv2efL2wIfwbl2twLg'
];

echo "<h2>🔗 Database Connection Test</h2>\n";
echo "<pre>\n";

echo "Configuration:\n";
echo "- Host: {$db_config['host']}\n";
echo "- Port: {$db_config['port']}\n";
echo "- Database: {$db_config['dbname']}\n";
echo "- User: {$db_config['user']}\n";
echo "- Password: " . str_repeat('*', strlen($db_config['password'])) . "\n\n";

try {
    $dsn = "pgsql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "✅ Database connection successful!\n\n";
    
    // Test basic query
    $stmt = $pdo->query("SELECT version() as version");
    $result = $stmt->fetch();
    echo "📋 PostgreSQL Version:\n";
    echo $result['version'] . "\n\n";
    
    // Test table existence
    $tables = ['users', 'products', 'flash_sale_products', 'orders', 'transactions', 'backup_data', 'system_status'];
    echo "📊 Table Status:\n";
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "✅ $table: {$result['count']} records\n";
        } catch (Exception $e) {
            echo "❌ $table: Error - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎮 Ready for simulation!\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "🔧 Troubleshooting:\n";
    echo "1. Check if database server is running\n";
    echo "2. Verify network connectivity\n";
    echo "3. Check credentials in .env file\n";
    echo "4. Ensure PostgreSQL allows external connections\n";
}

echo "</pre>\n";
?>