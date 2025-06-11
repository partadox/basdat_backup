<?php
// =============================================================
// PostgreSQL Recovery Simulator - Backend Controller
// File: controller.php
// =============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Load environment variables if .env file exists
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
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'dbname' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD']
];

// Security: Allowed SQL operations
$allowed_operations = ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN', 'WITH'];
$blocked_operations = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER', 'TRUNCATE', 'GRANT', 'REVOKE'];

class DatabaseController
{
    private $pdo;
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    private function connect()
    {
        try {
            $dsn = "pgsql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['dbname']}";
            $this->pdo = new PDO($dsn, $this->config['user'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 10
            ]);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function isQueryAllowed($query)
    {
        $query_upper = strtoupper(trim($query));

        // Check for blocked operations (except UPDATE for system_status)
        foreach ($GLOBALS['blocked_operations'] as $blocked) {
            if (strpos($query_upper, $blocked) === 0) {
                return false;
            }
        }

        // Allow SELECT and other read operations
        foreach ($GLOBALS['allowed_operations'] as $allowed) {
            if (strpos($query_upper, $allowed) === 0) {
                return true;
            }
        }

        // Special simulation cases
        if (strpos($query_upper, 'RESTORE') === 0) {
            return 'SIMULATED';
        }
        
        // Allow UPDATE only for system_status table
        if (strpos($query_upper, 'UPDATE') === 0 && strpos($query_upper, 'SYSTEM_STATUS') !== false) {
            return 'SIMULATED';
        }

        return false;
    }

    public function executeQuery($query)
    {
        $security_check = $this->isQueryAllowed($query);

        if ($security_check === false) {
            return [
                'success' => false,
                'error' => 'Query not allowed for security reasons',
                'data' => [],
                'message' => 'Only SELECT queries and special simulation commands are allowed'
            ];
        }

        // Handle simulated operations
        if ($security_check === 'SIMULATED') {
            return $this->handleSimulatedQuery($query);
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $result = $stmt->fetchAll();

            return [
                'success' => true,
                'data' => $result,
                'row_count' => count($result),
                'message' => 'Query executed successfully',
                'query' => $query
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
                'message' => 'Database error occurred'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
                'message' => 'An error occurred while executing the query'
            ];
        }
    }

    private function handleSimulatedQuery($query)
    {
        $query_lower = strtolower($query);

        if (strpos($query_lower, 'restore') !== false && strpos($query_lower, 'flash_sale_products') !== false) {
            return $this->simulateFlashSaleRestore();
        }

        if (strpos($query_lower, 'update') !== false && 
            strpos($query_lower, 'system_status') !== false && 
            strpos($query_lower, 'operational') !== false) {
            return $this->simulateSystemUpdate();
        }

        return [
            'success' => false,
            'error' => 'Unknown simulated operation',
            'data' => [],
            'message' => 'Simulation command not recognized. Use RESTORE flash_sale_products or UPDATE system_status SET status = \'OPERATIONAL\';'
        ];
    }

    private function simulateFlashSaleRestore()
    {
        try {
            // Get backup data
            $stmt = $this->pdo->prepare("SELECT data_json FROM backup_data WHERE table_name = 'flash_sale_products_backup'");
            $stmt->execute();
            $backup = $stmt->fetch();

            if (!$backup) {
                return [
                    'success' => false,
                    'error' => 'No backup data found',
                    'data' => [],
                    'message' => 'Flash sale products backup not available'
                ];
            }

            $backup_data = json_decode($backup['data_json'], true);

            if (!$backup_data) {
                return [
                    'success' => false,
                    'error' => 'Invalid backup data format',
                    'data' => [],
                    'message' => 'Backup data is corrupted'
                ];
            }

            // Clear existing data
            $this->pdo->exec("DELETE FROM flash_sale_products");

            // Insert backup data
            $insert_sql = "INSERT INTO flash_sale_products (flash_sale_id, product_id, original_price, flash_sale_price, discount_percentage, start_time, end_time, stock_limit, sold_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($insert_sql);

            $restored_count = 0;
            foreach ($backup_data as $item) {
                $stmt->execute([
                    $item['flash_sale_id'],
                    $item['product_id'],
                    $item['original_price'],
                    $item['flash_sale_price'],
                    $item['discount_percentage'],
                    $item['start_time'],
                    $item['end_time'],
                    $item['stock_limit'],
                    $item['sold_quantity']
                ]);
                $restored_count++;
            }

            return [
                'success' => true,
                'data' => [['message' => 'Flash sale products restored successfully', 'count' => $restored_count]],
                'message' => "✅ RESTORE COMPLETED!\n\n{$restored_count} flash sale products restored from backup.\nHarbolnas deals are back online!",
                'restored_count' => $restored_count
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Restore operation failed: ' . $e->getMessage(),
                'data' => [],
                'message' => 'Failed to restore flash sale products'
            ];
        }
    }

    private function simulateSystemUpdate()
    {
        try {
            $this->pdo->exec("UPDATE system_status SET status = 'OPERATIONAL', last_updated = CURRENT_TIMESTAMP");

            return [
                'success' => true,
                'data' => [['status' => 'OPERATIONAL', 'updated_at' => date('Y-m-d H:i:s')]],
                'message' => "🎉 SYSTEM RECOVERY COMPLETED!\n\nDatabase status: OPERATIONAL\nWebsite back online\nHarbolnas can continue!"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'System update failed: ' . $e->getMessage(),
                'data' => [],
                'message' => 'Failed to update system status'
            ];
        }
    }

    public function getSystemStatus()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM system_status ORDER BY status_id DESC LIMIT 1");
            $status = $stmt->fetch();

            return [
                'success' => true,
                'data' => $status ? [$status] : [],
                'message' => 'System status retrieved'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
                'message' => 'Failed to retrieve system status'
            ];
        }
    }

    public function getDamageAssessment()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM damage_assessment");
            $damage = $stmt->fetchAll();

            return [
                'success' => true,
                'data' => $damage,
                'message' => 'Damage assessment retrieved'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
                'message' => 'Failed to retrieve damage assessment'
            ];
        }
    }
}

// Main API router
try {
    $controller = new DatabaseController($db_config);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new Exception('Invalid JSON input');
        }

        $action = $input['action'] ?? 'query';

        switch ($action) {
            case 'query':
                if (!isset($input['query'])) {
                    throw new Exception('No query provided');
                }

                $query = trim($input['query']);

                if (empty($query)) {
                    throw new Exception('Empty query');
                }

                $result = $controller->executeQuery($query);
                break;

            case 'status':
                $result = $controller->getSystemStatus();
                break;

            case 'damage':
                $result = $controller->getDamageAssessment();
                break;

            default:
                throw new Exception('Unknown action: ' . $action);
        }

        echo json_encode($result);
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Test endpoint
        echo json_encode([
            'success' => true,
            'message' => 'PostgreSQL Recovery Simulator API is running',
            'version' => '2.0',
            'database' => $db_config['dbname'],
            'allowed_operations' => $allowed_operations,
            'php_version' => PHP_VERSION,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed',
            'message' => 'Only GET and POST methods are supported'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Server error occurred',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
