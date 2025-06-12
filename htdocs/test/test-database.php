<?php
/**
 * Database Connection Test File
 * ใช้สำหรับทดสอบการเชื่อมต่อฐานข้อมูล
 * ลบไฟล์นี้หลังจากทดสอบเสร็จแล้ว
 */

// เปิด debug mode
define('DEBUG_MODE', true);
define('FILE_SHARE_HUB', true);

// รวมไฟล์ database config
require_once 'config/database.php';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; background: #e8f5e8; padding: 10px; border-radius: 5px; }
        .error { color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; }
        .info { color: blue; background: #e6f3ff; padding: 10px; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔗 Database Connection Test</h1>
    
    <?php
    try {
        echo "<div class='info'><strong>Testing Database Connection...</strong></div><br>";
        
        // ทดสอบการเชื่อมต่อ
        if (testDatabaseConnection()) {
            echo "<div class='success'>✅ <strong>Database Connected Successfully!</strong></div><br>";
            
            // แสดงข้อมูลเซิร์ฟเวอร์
            $dbInfo = getDatabaseInfo();
            echo "<h3>📊 Database Information:</h3>";
            echo "<pre>";
            echo "Host: " . htmlspecialchars($dbInfo['host']) . "\n";
            echo "Database: " . htmlspecialchars($dbInfo['database']) . "\n";
            echo "MySQL Version: " . htmlspecialchars($dbInfo['version']) . "\n";
            echo "Charset: " . htmlspecialchars($dbInfo['charset']) . "\n";
            echo "</pre>";
            
            // ตรวจสอบตาราง
            echo "<h3>📋 Database Tables:</h3>";
            $pdo = getDatabase();
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) > 0) {
                echo "<div class='success'>Found " . count($tables) . " tables:</div>";
                echo "<ul>";
                foreach ($tables as $table) {
                    echo "<li>" . htmlspecialchars($table) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<div class='error'>No tables found! Please import the database schema.</div>";
            }
            
            // ตรวจสอบข้อมูลพื้นฐาน
            echo "<h3>👥 Basic Data Check:</h3>";
            $health = checkDatabaseHealth();
            
            echo "<ul>";
            echo "<li>Active Users: " . $health['users'] . "</li>";
            echo "<li>Files: " . $health['files'] . "</li>";
            echo "<li>Active Shares: " . $health['shares'] . "</li>";
            echo "<li>System Settings: " . ($health['settings'] ? 'OK' : 'Missing') . "</li>";
            echo "</ul>";
            
            // ทดสอบการตั้งค่าระบบ
            echo "<h3>⚙️ System Settings Test:</h3>";
            $siteName = getSystemSetting('site_name', 'Default');
            $maxFileSize = getSystemSetting('max_file_size', 0);
            
            echo "<ul>";
            echo "<li>Site Name: " . htmlspecialchars($siteName) . "</li>";
            echo "<li>Max File Size: " . number_format($maxFileSize / 1024 / 1024, 1) . " MB</li>";
            echo "</ul>";
            
            // ทดสอบการ query
            echo "<h3>🔍 Sample Query Test:</h3>";
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
            $result = $stmt->fetch();
            echo "<div class='success'>Total users in database: " . $result['total'] . "</div>";
            
        } else {
            echo "<div class='error'>❌ <strong>Database Connection Failed!</strong></div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>
    
    <hr>
    <p><strong>Note:</strong> ลบไฟล์นี้หลังจากทดสอบเสร็จแล้ว</p>
    
    <script>
    // Auto-refresh every 30 seconds for testing
    // setTimeout(() => location.reload(), 30000);
    </script>
</body>
</html>