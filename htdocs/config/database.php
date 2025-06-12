<?php
/**
 * Database Configuration for File Share Hub
 * InfinityFree Hosting Compatible
 * 
 * @author File Share Hub
 * @version 1.0
 * @created 2025
 */

// ป้องกันการเรียกใช้โดยตรง
if (!defined('FILE_SHARE_HUB')) {
    die('Direct access not permitted');
}

// ===================================================================
// Database Configuration for InfinityFree
// ===================================================================

// InfinityFree Database Credentials
define('DB_HOST', 'sql103.infinityfree.com');
define('DB_PORT', '3306');
define('DB_NAME', 'if0_39204489_file_share_hub'); // แก้ไขตามฐานข้อมูลที่สร้างจริง
define('DB_USER', 'if0_39204489');
define('DB_PASS', 'opY6aQ8XGRw6i');
define('DB_CHARSET', 'utf8mb4');

// ===================================================================
// PDO Database Connection Class
// ===================================================================

class Database 
{
    private static $instance = null;
    private $connection;
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;

    /**
     * Private constructor เพื่อใช้ Singleton pattern
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * สร้าง instance เดียวของ Database (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * เชื่อมต่อฐานข้อมูล
     */
    private function connect()
    {
        try {
            // DSN สำหรับ MySQL
            $dsn = "mysql:host={$this->host};port=" . DB_PORT . ";dbname={$this->db_name};charset={$this->charset}";
            
            // PDO Options สำหรับ InfinityFree
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false, // ปิด persistent connection สำหรับ free hosting
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_TIMEOUT            => 30, // timeout 30 วินาที
            ];

            // สร้างการเชื่อมต่อ PDO
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            // ตั้งค่า timezone
            $this->connection->exec("SET time_zone = '+07:00'");
            
            // Log การเชื่อมต่อสำเร็จ (ใช้เฉพาะตอน debug)
            if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
                error_log("Database connection successful at " . date('Y-m-d H:i:s'));
            }
            
        } catch (PDOException $e) {
            // Log error
            error_log("Database Connection Error: " . $e->getMessage());
            
            // แสดง error แบบ user-friendly
            if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
                die("Database Connection Failed: " . $e->getMessage());
            } else {
                die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาลองใหม่อีกครั้ง");
            }
        }
    }

    /**
     * รับ PDO connection object
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * ทดสอบการเชื่อมต่อฐานข้อมูล
     */
    public function testConnection()
    {
        try {
            $stmt = $this->connection->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            error_log("Database Test Connection Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * รับข้อมูลเซิร์ฟเวอร์ฐานข้อมูล
     */
    public function getServerInfo()
    {
        try {
            $version = $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
            $info = $this->connection->getAttribute(PDO::ATTR_SERVER_INFO);
            
            return [
                'version' => $version,
                'info' => $info,
                'host' => $this->host,
                'database' => $this->db_name,
                'charset' => $this->charset
            ];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * ป้องกันการ clone object
     */
    private function __clone() {}

    /**
     * ป้องกันการ unserialize
     */
    public function __wakeup() 
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * ปิดการเชื่อมต่อเมื่อ script จบ
     */
    public function __destruct()
    {
        $this->connection = null;
    }
}

// ===================================================================
// Helper Functions
// ===================================================================

/**
 * รับ PDO connection (shorthand function)
 * 
 * @return PDO
 */
function getDatabase()
{
    return Database::getInstance()->getConnection();
}

/**
 * ทดสอบการเชื่อมต่อฐานข้อมูล
 * 
 * @return bool
 */
function testDatabaseConnection()
{
    return Database::getInstance()->testConnection();
}

/**
 * รับข้อมูลเซิร์ฟเวอร์ฐานข้อมูล
 * 
 * @return array
 */
function getDatabaseInfo()
{
    return Database::getInstance()->getServerInfo();
}

// ===================================================================
// Initialize Database Connection
// ===================================================================

try {
    // สร้าง database instance
    $database = Database::getInstance();
    $pdo = $database->getConnection();
    
    // Test connection
    if (!$database->testConnection()) {
        throw new Exception("Database connection test failed");
    }
    
} catch (Exception $e) {
    error_log("Database Initialization Error: " . $e->getMessage());
    
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        die("Database initialization failed: " . $e->getMessage());
    } else {
        die("ระบบฐานข้อมูลไม่พร้อมใช้งาน กรุณาลองใหม่อีกครั้ง");
    }
}

// ===================================================================
// Database Status Check (เฉพาะใน debug mode)
// ===================================================================

if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    // แสดงสถานะการเชื่อมต่อ
    $db_info = getDatabaseInfo();
    
    echo "<!-- Database Connection Info:\n";
    echo "Host: " . $db_info['host'] . "\n";
    echo "Database: " . $db_info['database'] . "\n";
    echo "MySQL Version: " . $db_info['version'] . "\n";
    echo "Charset: " . $db_info['charset'] . "\n";
    echo "Connection: Success\n";
    echo "-->\n";
}

// ===================================================================
// Common Database Queries (สำหรับ InfinityFree)
// ===================================================================

/**
 * รับการตั้งค่าระบบ
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getSystemSetting($key, $default = null)
{
    try {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("SELECT setting_value, setting_type FROM system_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        if ($result) {
            $value = $result['setting_value'];
            $type = $result['setting_type'];
            
            // แปลงประเภทข้อมูล
            switch ($type) {
                case 'boolean':
                    return (bool) $value;
                case 'number':
                    return is_numeric($value) ? (strpos($value, '.') !== false ? (float) $value : (int) $value) : $default;
                case 'json':
                    return json_decode($value, true) ?: $default;
                default:
                    return $value;
            }
        }
        
        return $default;
    } catch (Exception $e) {
        error_log("Error getting system setting '{$key}': " . $e->getMessage());
        return $default;
    }
}

/**
 * อัพเดทการตั้งค่าระบบ
 * 
 * @param string $key
 * @param mixed $value
 * @param string $type
 * @return bool
 */
function updateSystemSetting($key, $value, $type = 'string')
{
    try {
        $pdo = getDatabase();
        
        // แปลงค่าตามประเภท
        switch ($type) {
            case 'boolean':
                $value = $value ? '1' : '0';
                break;
            case 'json':
                $value = json_encode($value);
                break;
            default:
                $value = (string) $value;
        }
        
        $stmt = $pdo->prepare("
            UPDATE system_settings 
            SET setting_value = ?, setting_type = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE setting_key = ?
        ");
        
        return $stmt->execute([$value, $type, $key]);
    } catch (Exception $e) {
        error_log("Error updating system setting '{$key}': " . $e->getMessage());
        return false;
    }
}

/**
 * ตรวจสอบสถานะการเชื่อมต่อและฐานข้อมูล
 * 
 * @return array
 */
function checkDatabaseHealth()
{
    $health = [
        'connection' => false,
        'tables' => [],
        'settings' => false,
        'users' => 0,
        'files' => 0,
        'shares' => 0
    ];
    
    try {
        $pdo = getDatabase();
        
        // ทดสอบการเชื่อมต่อ
        $health['connection'] = testDatabaseConnection();
        
        if ($health['connection']) {
            // ตรวจสอบตาราง
            $stmt = $pdo->query("SHOW TABLES");
            $health['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // ตรวจสอบการตั้งค่า
            $stmt = $pdo->query("SELECT COUNT(*) FROM system_settings");
            $health['settings'] = $stmt->fetchColumn() > 0;
            
            // นับข้อมูล
            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
            $health['users'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM files WHERE is_deleted = 0");
            $health['files'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM shared_links WHERE is_active = 1");
            $health['shares'] = $stmt->fetchColumn();
        }
        
    } catch (Exception $e) {
        error_log("Database health check error: " . $e->getMessage());
    }
    
    return $health;
}

// ===================================================================
// End of Database Configuration
// ===================================================================

/*
Usage Examples:

// รับ PDO connection
$pdo = getDatabase();

// ทดสอบการเชื่อมต่อ
if (testDatabaseConnection()) {
    echo "Database connected successfully!";
}

// รับข้อมูลเซิร์ฟเวอร์
$info = getDatabaseInfo();
echo "MySQL Version: " . $info['version'];

// รับการตั้งค่าระบบ
$siteName = getSystemSetting('site_name', 'File Share Hub');
$maxFileSize = getSystemSetting('max_file_size', 10485760);

// อัพเดทการตั้งค่า
updateSystemSetting('maintenance_mode', true, 'boolean');

// ตรวจสอบสุขภาพฐานข้อมูล
$health = checkDatabaseHealth();
if ($health['connection']) {
    echo "Database is healthy!";
}
*/
?>