<?php
/**
 * Main System Configuration for File Share Hub
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
// System Information
// ===================================================================

define('APP_NAME', 'File Share Hub');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'File Share Hub Team');
define('APP_DESCRIPTION', 'ระบบแชร์ไฟล์ออนไลน์ที่ปลอดภัยและใช้งานง่าย');

// ===================================================================
// Environment Configuration
// ===================================================================

// Environment: development, staging, production
define('ENVIRONMENT', 'production'); // เปลี่ยนเป็น development เมื่อพัฒนา

// Debug Mode (เปิดเฉพาะตอนพัฒนา)
define('DEBUG_MODE', ENVIRONMENT === 'development');
define('ERROR_REPORTING', DEBUG_MODE);

// แสดง PHP errors (เฉพาะ development)
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// ===================================================================
// URL Configuration
// ===================================================================

// Base URL (แก้ไขให้ตรงกับโดเมนจริง)
define('BASE_URL', 'https://unfile.wuaze.com/');
define('SITE_URL', rtrim(BASE_URL, '/'));

// Asset URLs
define('ASSETS_URL', SITE_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMG_URL', ASSETS_URL . '/images');

// Upload URLs
define('UPLOAD_URL', SITE_URL . '/uploads');

// ===================================================================
// File System Paths
// ===================================================================

// Root path
define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

// Directory paths
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('LANG_PATH', ROOT_PATH . '/lang');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');

// User upload paths
define('USER_UPLOAD_PATH', UPLOAD_PATH . '/users');

// ===================================================================
// Security Configuration
// ===================================================================

// Session Configuration
define('SESSION_NAME', 'FILESHAREHUB_SESSION');
define('SESSION_LIFETIME', 1800); // 30 minutes (เหมาะกับ free hosting)
define('SESSION_REGENERATE_TIME', 300); // 5 minutes

// CSRF Protection
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Password Configuration
define('PASSWORD_MIN_LENGTH', 6); // เหมาะกับ free hosting
define('PASSWORD_REQUIRE_SPECIAL', false);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);

// Security Headers
define('SECURITY_HEADERS', [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin'
]);

// ===================================================================
// File Upload Configuration (InfinityFree Limits)
// ===================================================================

// File size limits (bytes)
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB (InfinityFree limit)
define('MAX_TOTAL_SIZE', 100 * 1024 * 1024); // 100MB per user
define('MAX_FILES_PER_USER', 50); // จำกัดจำนวนไฟล์

// Allowed file types
define('ALLOWED_FILE_TYPES', [
    'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'documents' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
    'archives' => ['zip', 'rar'],
    'others' => []
]);

// MIME types mapping
define('ALLOWED_MIME_TYPES', [
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain', 'application/rtf',
    'application/zip', 'application/x-rar-compressed'
]);

// Upload settings
define('UPLOAD_TEMP_DIR', sys_get_temp_dir());
define('UPLOAD_MAX_EXECUTION_TIME', 30); // seconds
define('UPLOAD_MEMORY_LIMIT', '128M');

// ===================================================================
// Sharing Configuration
// ===================================================================

// Share link settings
define('SHARE_TOKEN_LENGTH', 32);
define('SHARE_DEFAULT_EXPIRY', 7); // days
define('SHARE_MAX_EXPIRY', 30); // days
define('SHARE_MAX_DOWNLOADS', 100);
define('SHARE_MAX_VIEWS', 1000);

// Share password settings
define('SHARE_PASSWORD_MIN_LENGTH', 4);
define('SHARE_PASSWORD_MAX_LENGTH', 50);

// ===================================================================
// Database Configuration Overrides
// ===================================================================

// Connection settings for InfinityFree
define('DB_CONNECTION_TIMEOUT', 30);
define('DB_QUERY_TIMEOUT', 30);
define('DB_MAX_CONNECTIONS', 5);

// Cleanup settings (manual for free hosting)
define('CLEANUP_ENABLED', true);
define('CLEANUP_METHOD', 'manual'); // 'auto' หรือ 'manual'
define('CLEANUP_DELETED_FILES_AFTER', 30); // days
define('CLEANUP_EXPIRED_SHARES_AFTER', 7); // days
define('CLEANUP_OLD_LOGS_AFTER', 30); // days

// ===================================================================
// Localization Configuration
// ===================================================================

// Default language
define('DEFAULT_LANGUAGE', 'th');
define('SUPPORTED_LANGUAGES', ['th', 'en']);

// Timezone
define('DEFAULT_TIMEZONE', 'Asia/Bangkok');
date_default_timezone_set(DEFAULT_TIMEZONE);

// Date/Time formats
define('DATE_FORMAT', 'd/m/Y');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');

// ===================================================================
// Email Configuration (External SMTP required for InfinityFree)
// ===================================================================

// Email settings (ต้องใช้ external SMTP service)
define('MAIL_ENABLED', false); // เปลี่ยนเป็น true เมื่อตั้งค่า SMTP
define('MAIL_FROM_EMAIL', 'noreply@yourdomain.infinityfreeapp.com');
define('MAIL_FROM_NAME', APP_NAME);

// SMTP Configuration (ใส่ข้อมูลจริงเมื่อใช้งาน)
define('SMTP_HOST', 'smtp.gmail.com'); // หรือ SMTP service อื่น
define('SMTP_PORT', 587);
define('SMTP_USERNAME', ''); // ใส่อีเมลจริง
define('SMTP_PASSWORD', ''); // ใส่รหัสผ่านหรือ app password
define('SMTP_ENCRYPTION', 'tls'); // 'tls' หรือ 'ssl'

// ===================================================================
// Logging Configuration
// ===================================================================

// Log settings
define('LOG_ENABLED', true);
define('LOG_LEVEL', DEBUG_MODE ? 'debug' : 'error');
define('LOG_MAX_FILES', 10);
define('LOG_MAX_SIZE', 1024 * 1024); // 1MB

// Log file paths
define('ERROR_LOG_FILE', LOGS_PATH . '/error.log');
define('ACCESS_LOG_FILE', LOGS_PATH . '/access.log');
define('UPLOAD_LOG_FILE', LOGS_PATH . '/upload.log');

// ===================================================================
// Cache Configuration
// ===================================================================

// Cache settings (simple file cache for free hosting)
define('CACHE_ENABLED', true);
define('CACHE_METHOD', 'file'); // เฉพาะ 'file' สำหรับ free hosting
define('CACHE_LIFETIME', 3600); // 1 hour
define('CACHE_PATH', ROOT_PATH . '/cache');

// ===================================================================
// Rate Limiting Configuration
// ===================================================================

// Rate limiting (ป้องกันการใช้งานมากเกินไป)
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 60); // requests per window
define('RATE_LIMIT_WINDOW', 300); // 5 minutes

// Specific limits
define('LOGIN_RATE_LIMIT', 5); // attempts per window
define('UPLOAD_RATE_LIMIT', 10); // uploads per window
define('SHARE_RATE_LIMIT', 20); // shares per window

// ===================================================================
// Feature Flags
// ===================================================================

// Enable/disable features
define('FEATURE_REGISTRATION', true);
define('FEATURE_EMAIL_VERIFICATION', false); // ปิดเพราะไม่มี SMTP
define('FEATURE_PASSWORD_RESET', false); // ปิดเพราะไม่มี SMTP
define('FEATURE_THUMBNAILS', false); // ปิดเพื่อประหยัดพื้นที่
define('FEATURE_FILE_VERSIONING', false); // ปิดเพื่อประหยัดพื้นที่
define('FEATURE_ACTIVITY_LOGS', true);
define('FEATURE_SHARE_ANALYTICS', true);

// Admin features
define('FEATURE_ADMIN_PANEL', true);
define('FEATURE_SYSTEM_INFO', true);
define('FEATURE_MANUAL_CLEANUP', true);

// ===================================================================
// Performance Configuration
// ===================================================================

// PHP settings for InfinityFree
ini_set('max_execution_time', 30);
ini_set('memory_limit', '128M');
ini_set('post_max_size', '12M');
ini_set('upload_max_filesize', '10M');
ini_set('max_file_uploads', 10);

// Output compression
if (!DEBUG_MODE && extension_loaded('zlib')) {
    ini_set('zlib.output_compression', 1);
    ini_set('zlib.output_compression_level', 6);
}

// ===================================================================
// Helper Functions
// ===================================================================

/**
 * รับ URL แบบเต็ม
 */
function getFullUrl($path = '')
{
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * รับ Asset URL
 */
function getAssetUrl($file, $type = 'css')
{
    switch ($type) {
        case 'css':
            return CSS_URL . '/' . ltrim($file, '/');
        case 'js':
            return JS_URL . '/' . ltrim($file, '/');
        case 'img':
            return IMG_URL . '/' . ltrim($file, '/');
        default:
            return ASSETS_URL . '/' . ltrim($file, '/');
    }
}

/**
 * ตรวจสอบว่าอยู่ใน debug mode หรือไม่
 */
function isDebugMode()
{
    return DEBUG_MODE;
}

/**
 * ตรวจสอบว่า feature เปิดใช้งานหรือไม่
 */
function isFeatureEnabled($feature)
{
    $constant_name = 'FEATURE_' . strtoupper($feature);
    return defined($constant_name) && constant($constant_name) === true;
}

/**
 * รับการตั้งค่าแบบ array
 */
function getConfig($key = null)
{
    static $config = null;
    
    if ($config === null) {
        $config = [
            'app' => [
                'name' => APP_NAME,
                'version' => APP_VERSION,
                'description' => APP_DESCRIPTION,
                'environment' => ENVIRONMENT
            ],
            'urls' => [
                'base' => BASE_URL,
                'site' => SITE_URL,
                'assets' => ASSETS_URL,
                'upload' => UPLOAD_URL
            ],
            'paths' => [
                'root' => ROOT_PATH,
                'upload' => UPLOAD_PATH,
                'logs' => LOGS_PATH,
                'cache' => CACHE_PATH
            ],
            'upload' => [
                'max_file_size' => MAX_FILE_SIZE,
                'max_total_size' => MAX_TOTAL_SIZE,
                'max_files' => MAX_FILES_PER_USER,
                'allowed_types' => ALLOWED_FILE_TYPES
            ],
            'security' => [
                'csrf_token_name' => CSRF_TOKEN_NAME,
                'session_name' => SESSION_NAME,
                'session_lifetime' => SESSION_LIFETIME
            ],
            'features' => [
                'registration' => FEATURE_REGISTRATION,
                'email_verification' => FEATURE_EMAIL_VERIFICATION,
                'thumbnails' => FEATURE_THUMBNAILS,
                'admin_panel' => FEATURE_ADMIN_PANEL
            ]
        ];
    }
    
    if ($key === null) {
        return $config;
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return null;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * ฟอร์แมตขนาดไฟล์
 */
function formatFileSize($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * ตรวจสอบว่าเป็น AJAX request หรือไม่
 */
function isAjaxRequest()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * ตั้งค่า Security Headers
 */
function setSecurityHeaders()
{
    foreach (SECURITY_HEADERS as $header => $value) {
        header($header . ': ' . $value);
    }
    
    // Content Security Policy (basic)
    if (!DEBUG_MODE) {
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
    }
}

// ===================================================================
// Auto-load Configuration
// ===================================================================

// ตั้งค่า error handler
if (!DEBUG_MODE) {
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error = sprintf(
            "[%s] %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $message,
            $file,
            $line
        );
        
        error_log($error, 3, ERROR_LOG_FILE);
        return true;
    });
}

// ตั้งค่า exception handler
set_exception_handler(function($exception) {
    $error = sprintf(
        "[%s] Uncaught exception: %s in %s on line %d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    error_log($error, 3, ERROR_LOG_FILE);
    
    if (DEBUG_MODE) {
        echo "<pre>" . htmlspecialchars($error) . "</pre>";
    } else {
        echo "เกิดข้อผิดพลาดของระบบ กรุณาลองใหม่อีกครั้ง";
    }
});

// ตั้งค่า Security Headers อัตโนมัติ
setSecurityHeaders();

// สร้างโฟลเดอร์ที่จำเป็น
$required_dirs = [UPLOAD_PATH, LOGS_PATH, CACHE_PATH, USER_UPLOAD_PATH];
foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ===================================================================
// Configuration Complete
// ===================================================================

if (DEBUG_MODE) {
    error_log("Configuration loaded successfully at " . date('Y-m-d H:i:s'));
}

/*
Usage Examples:

// รับ URL
$homeUrl = getFullUrl('/');
$cssUrl = getAssetUrl('custom.css', 'css');

// ตรวจสอบ feature
if (isFeatureEnabled('registration')) {
    // แสดงปุ่มสมัครสมาชิก
}

// รับการตั้งค่า
$appName = getConfig('app.name');
$maxFileSize = getConfig('upload.max_file_size');

// ฟอร์แมตขนาดไฟล์
echo formatFileSize(1048576); // 1.00 MB

// ตรวจสอบ AJAX
if (isAjaxRequest()) {
    // ส่งข้อมูล JSON
}
*/
?>