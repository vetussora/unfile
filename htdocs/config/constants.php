<?php
/**
 * System Constants for File Share Hub
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
// Application Constants
// ===================================================================

// App Identification
define('APP_ID', 'file_share_hub');
define('APP_NAMESPACE', 'FileShareHub');
define('APP_SLUG', 'file-share-hub');

// Version Information
define('VERSION_MAJOR', 1);
define('VERSION_MINOR', 0);
define('VERSION_PATCH', 0);
define('VERSION_BUILD', date('Ymd'));

// Release Information
define('RELEASE_DATE', '2025-01-01');
define('RELEASE_CODENAME', 'Phoenix');
define('MIN_PHP_VERSION', '7.4.0');

// ===================================================================
// Database Constants
// ===================================================================

// Table Names
define('TABLE_USERS', 'users');
define('TABLE_PURE_USERS', 'pure_users');
define('TABLE_FILES', 'files');
define('TABLE_FOLDERS', 'folders');
define('TABLE_SHARED_LINKS', 'shared_links');
define('TABLE_SYSTEM_SETTINGS', 'system_settings');
define('TABLE_ACTIVITY_LOGS', 'activity_logs');
define('TABLE_CLEANUP_QUEUE', 'cleanup_queue');
define('TABLE_SESSIONS', 'sessions');
define('TABLE_SHARE_ACCESS_LOGS', 'share_access_logs');

// Database Status Constants
define('DB_STATUS_CONNECTED', 'connected');
define('DB_STATUS_DISCONNECTED', 'disconnected');
define('DB_STATUS_ERROR', 'error');

// ===================================================================
// User Management Constants
// ===================================================================

// User Roles
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');
define('ROLE_MODERATOR', 'moderator');

// User Status
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_SUSPENDED', 'suspended');
define('USER_STATUS_DELETED', 'deleted');

// User Permissions
define('PERM_READ', 'read');
define('PERM_WRITE', 'write');
define('PERM_DELETE', 'delete');
define('PERM_SHARE', 'share');
define('PERM_ADMIN', 'admin');

// Default User Limits (InfinityFree Optimized)
define('DEFAULT_USER_STORAGE_LIMIT', 104857600); // 100MB
define('DEFAULT_USER_FILE_LIMIT', 50);
define('DEFAULT_ADMIN_STORAGE_LIMIT', 1073741824); // 1GB
define('DEFAULT_ADMIN_FILE_LIMIT', 1000);

// ===================================================================
// File Management Constants
// ===================================================================

// File Status
define('FILE_STATUS_ACTIVE', 'active');
define('FILE_STATUS_DELETED', 'deleted');
define('FILE_STATUS_PROCESSING', 'processing');
define('FILE_STATUS_ERROR', 'error');

// File Types Categories
define('FILE_TYPE_IMAGE', 'image');
define('FILE_TYPE_DOCUMENT', 'document');
define('FILE_TYPE_ARCHIVE', 'archive');
define('FILE_TYPE_VIDEO', 'video');
define('FILE_TYPE_AUDIO', 'audio');
define('FILE_TYPE_OTHER', 'other');

// File Size Limits (bytes)
define('FILE_SIZE_1KB', 1024);
define('FILE_SIZE_1MB', 1048576);
define('FILE_SIZE_10MB', 10485760);
define('FILE_SIZE_100MB', 104857600);
define('FILE_SIZE_1GB', 1073741824);

// Upload Status
define('UPLOAD_STATUS_PENDING', 'pending');
define('UPLOAD_STATUS_UPLOADING', 'uploading');
define('UPLOAD_STATUS_SUCCESS', 'success');
define('UPLOAD_STATUS_FAILED', 'failed');
define('UPLOAD_STATUS_CANCELLED', 'cancelled');

// File Hash Algorithm
define('FILE_HASH_ALGORITHM', 'sha256');

// ===================================================================
// Folder Management Constants
// ===================================================================

// Folder Status
define('FOLDER_STATUS_ACTIVE', 'active');
define('FOLDER_STATUS_DELETED', 'deleted');

// Default Folder Names
define('FOLDER_DOCUMENTS', 'Documents');
define('FOLDER_IMAGES', 'Images');
define('FOLDER_VIDEOS', 'Videos');
define('FOLDER_OTHERS', 'Others');

// Folder Colors (Hex)
define('FOLDER_COLOR_BLUE', '#007bff');
define('FOLDER_COLOR_GREEN', '#28a745');
define('FOLDER_COLOR_YELLOW', '#ffc107');
define('FOLDER_COLOR_RED', '#dc3545');
define('FOLDER_COLOR_PURPLE', '#6f42c1');
define('FOLDER_COLOR_ORANGE', '#fd7e14');

// Folder Icons
define('FOLDER_ICON_DEFAULT', 'folder');
define('FOLDER_ICON_DOCUMENT', 'folder-open');
define('FOLDER_ICON_IMAGE', 'images');
define('FOLDER_ICON_VIDEO', 'video');
define('FOLDER_ICON_ARCHIVE', 'archive');

// ===================================================================
// Sharing System Constants
// ===================================================================

// Share Status
define('SHARE_STATUS_ACTIVE', 'active');
define('SHARE_STATUS_INACTIVE', 'inactive');
define('SHARE_STATUS_EXPIRED', 'expired');
define('SHARE_STATUS_DISABLED', 'disabled');

// Share Types
define('SHARE_TYPE_FILE', 'file');
define('SHARE_TYPE_FOLDER', 'folder');

// Share Access Types
define('SHARE_ACCESS_VIEW', 'view');
define('SHARE_ACCESS_DOWNLOAD', 'download');

// Share Expiry Options (days)
define('SHARE_EXPIRY_1_DAY', 1);
define('SHARE_EXPIRY_3_DAYS', 3);
define('SHARE_EXPIRY_7_DAYS', 7);
define('SHARE_EXPIRY_14_DAYS', 14);
define('SHARE_EXPIRY_30_DAYS', 30);
define('SHARE_EXPIRY_NEVER', null);

// Share Token Types
define('TOKEN_TYPE_SHARE', 'share');
define('TOKEN_TYPE_DOWNLOAD', 'download');
define('TOKEN_TYPE_PREVIEW', 'preview');

// ===================================================================
// Security Constants
// ===================================================================

// Authentication Status
define('AUTH_SUCCESS', 'success');
define('AUTH_FAILED', 'failed');
define('AUTH_LOCKED', 'locked');
define('AUTH_EXPIRED', 'expired');
define('AUTH_INVALID', 'invalid');

// CSRF Token Status
define('CSRF_VALID', 'valid');
define('CSRF_INVALID', 'invalid');
define('CSRF_EXPIRED', 'expired');
define('CSRF_MISSING', 'missing');

// Rate Limiting
define('RATE_LIMIT_LOGIN', 'login');
define('RATE_LIMIT_UPLOAD', 'upload');
define('RATE_LIMIT_SHARE', 'share');
define('RATE_LIMIT_DOWNLOAD', 'download');
define('RATE_LIMIT_API', 'api');

// IP Restriction Types
define('IP_ALLOW', 'allow');
define('IP_DENY', 'deny');
define('IP_WHITELIST', 'whitelist');
define('IP_BLACKLIST', 'blacklist');

// Encryption Methods
define('ENCRYPT_METHOD_AES256', 'AES-256-CBC');
define('ENCRYPT_METHOD_AES128', 'AES-128-CBC');

// ===================================================================
// Session Management Constants
// ===================================================================

// Session Status
define('SESSION_ACTIVE', 'active');
define('SESSION_EXPIRED', 'expired');
define('SESSION_INVALID', 'invalid');
define('SESSION_DESTROYED', 'destroyed');

// Session Variables
define('SESS_USER_ID', 'user_id');
define('SESS_USERNAME', 'username');
define('SESS_USER_ROLE', 'user_role');
define('SESS_LOGIN_TIME', 'login_time');
define('SESS_LAST_ACTIVITY', 'last_activity');
define('SESS_IP_ADDRESS', 'ip_address');
define('SESS_USER_AGENT', 'user_agent');
define('SESS_CSRF_TOKEN', 'csrf_token');
define('SESS_LANGUAGE', 'language');

// ===================================================================
// HTTP Status Codes
// ===================================================================

// Success Codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_ACCEPTED', 202);
define('HTTP_NO_CONTENT', 204);

// Redirection Codes
define('HTTP_MOVED_PERMANENTLY', 301);
define('HTTP_FOUND', 302);
define('HTTP_NOT_MODIFIED', 304);

// Client Error Codes
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_REQUEST_TIMEOUT', 408);
define('HTTP_CONFLICT', 409);
define('HTTP_GONE', 410);
define('HTTP_LENGTH_REQUIRED', 411);
define('HTTP_PAYLOAD_TOO_LARGE', 413);
define('HTTP_UNSUPPORTED_MEDIA_TYPE', 415);
define('HTTP_TOO_MANY_REQUESTS', 429);

// Server Error Codes
define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('HTTP_NOT_IMPLEMENTED', 501);
define('HTTP_BAD_GATEWAY', 502);
define('HTTP_SERVICE_UNAVAILABLE', 503);
define('HTTP_GATEWAY_TIMEOUT', 504);

// ===================================================================
// Activity Log Constants
// ===================================================================

// Activity Types
define('ACTIVITY_LOGIN', 'login');
define('ACTIVITY_LOGOUT', 'logout');
define('ACTIVITY_REGISTER', 'register');
define('ACTIVITY_UPLOAD', 'upload');
define('ACTIVITY_DOWNLOAD', 'download');
define('ACTIVITY_DELETE', 'delete');
define('ACTIVITY_SHARE', 'share');
define('ACTIVITY_UNSHARE', 'unshare');
define('ACTIVITY_CREATE_FOLDER', 'create_folder');
define('ACTIVITY_DELETE_FOLDER', 'delete_folder');
define('ACTIVITY_ADMIN', 'admin');
define('ACTIVITY_ERROR', 'error');
define('ACTIVITY_SYSTEM', 'system');

// Target Types
define('TARGET_FILE', 'file');
define('TARGET_FOLDER', 'folder');
define('TARGET_USER', 'user');
define('TARGET_SHARE', 'share');
define('TARGET_SYSTEM', 'system');

// Log Levels
define('LOG_LEVEL_DEBUG', 'debug');
define('LOG_LEVEL_INFO', 'info');
define('LOG_LEVEL_WARNING', 'warning');
define('LOG_LEVEL_ERROR', 'error');
define('LOG_LEVEL_CRITICAL', 'critical');

// ===================================================================
// API Response Constants
// ===================================================================

// Response Status
define('API_SUCCESS', 'success');
define('API_ERROR', 'error');
define('API_WARNING', 'warning');
define('API_INFO', 'info');

// Response Messages
define('MSG_SUCCESS', 'Operation completed successfully');
define('MSG_ERROR', 'An error occurred');
define('MSG_INVALID_REQUEST', 'Invalid request');
define('MSG_UNAUTHORIZED', 'Unauthorized access');
define('MSG_FORBIDDEN', 'Access forbidden');
define('MSG_NOT_FOUND', 'Resource not found');
define('MSG_VALIDATION_FAILED', 'Validation failed');
define('MSG_SERVER_ERROR', 'Internal server error');

// ===================================================================
// Validation Constants
// ===================================================================

// Validation Rules
define('VALIDATE_REQUIRED', 'required');
define('VALIDATE_EMAIL', 'email');
define('VALIDATE_URL', 'url');
define('VALIDATE_NUMERIC', 'numeric');
define('VALIDATE_ALPHA', 'alpha');
define('VALIDATE_ALPHANUMERIC', 'alphanumeric');
define('VALIDATE_MIN_LENGTH', 'min_length');
define('VALIDATE_MAX_LENGTH', 'max_length');
define('VALIDATE_EXACT_LENGTH', 'exact_length');
define('VALIDATE_MATCHES', 'matches');
define('VALIDATE_DIFFERS', 'differs');
define('VALIDATE_IS_UNIQUE', 'is_unique');

// Input Sanitization
define('SANITIZE_STRING', 'string');
define('SANITIZE_EMAIL', 'email');
define('SANITIZE_URL', 'url');
define('SANITIZE_INT', 'int');
define('SANITIZE_FLOAT', 'float');
define('SANITIZE_BOOLEAN', 'boolean');

// ===================================================================
// File MIME Types
// ===================================================================

// Image MIME Types
define('MIME_JPEG', 'image/jpeg');
define('MIME_PNG', 'image/png');
define('MIME_GIF', 'image/gif');
define('MIME_WEBP', 'image/webp');
define('MIME_SVG', 'image/svg+xml');

// Document MIME Types
define('MIME_PDF', 'application/pdf');
define('MIME_DOC', 'application/msword');
define('MIME_DOCX', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
define('MIME_TXT', 'text/plain');
define('MIME_RTF', 'application/rtf');

// Archive MIME Types
define('MIME_ZIP', 'application/zip');
define('MIME_RAR', 'application/x-rar-compressed');
define('MIME_7Z', 'application/x-7z-compressed');

// Video MIME Types
define('MIME_MP4', 'video/mp4');
define('MIME_AVI', 'video/x-msvideo');
define('MIME_MOV', 'video/quicktime');

// Audio MIME Types
define('MIME_MP3', 'audio/mpeg');
define('MIME_WAV', 'audio/wav');
define('MIME_OGG', 'audio/ogg');

// ===================================================================
// Date & Time Constants
// ===================================================================

// Time Units (seconds)
define('TIME_MINUTE', 60);
define('TIME_HOUR', 3600);
define('TIME_DAY', 86400);
define('TIME_WEEK', 604800);
define('TIME_MONTH', 2592000); // 30 days
define('TIME_YEAR', 31536000); // 365 days

// Date Formats
define('DATE_FORMAT_MYSQL', 'Y-m-d H:i:s');
define('DATE_FORMAT_ISO', 'c');
define('DATE_FORMAT_HUMAN', 'd/m/Y H:i');
define('DATE_FORMAT_SHORT', 'd/m/Y');
define('DATE_FORMAT_LONG', 'l, d F Y H:i');

// ===================================================================
// Cache Constants
// ===================================================================

// Cache Keys
define('CACHE_USER_PREFIX', 'user_');
define('CACHE_FILE_PREFIX', 'file_');
define('CACHE_SHARE_PREFIX', 'share_');
define('CACHE_SYSTEM_PREFIX', 'system_');
define('CACHE_SESSION_PREFIX', 'session_');

// Cache Expiry Times
define('CACHE_EXPIRY_SHORT', 300); // 5 minutes
define('CACHE_EXPIRY_MEDIUM', 1800); // 30 minutes
define('CACHE_EXPIRY_LONG', 3600); // 1 hour
define('CACHE_EXPIRY_VERY_LONG', 86400); // 24 hours

// ===================================================================
// Regular Expressions
// ===================================================================

// Validation Patterns
define('REGEX_USERNAME', '/^[a-zA-Z0-9_-]{3,20}$/');
define('REGEX_PASSWORD', '/^.{6,50}$/');
define('REGEX_EMAIL', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
define('REGEX_FILENAME', '/^[a-zA-Z0-9._-]+$/');
define('REGEX_FOLDER_NAME', '/^[a-zA-Z0-9._\-\s]+$/');
define('REGEX_SHARE_TOKEN', '/^[a-zA-Z0-9]{32}$/');
define('REGEX_HEX_COLOR', '/^#[0-9A-Fa-f]{6}$/');
define('REGEX_IP_ADDRESS', '/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/');

// ===================================================================
// Language Constants
// ===================================================================

// Language Codes
define('LANG_THAI', 'th');
define('LANG_ENGLISH', 'en');

// Language Direction
define('LANG_DIR_LTR', 'ltr');
define('LANG_DIR_RTL', 'rtl');

// ===================================================================
// Error Codes
// ===================================================================

// System Error Codes
define('ERR_SYSTEM_ERROR', 1000);
define('ERR_DATABASE_ERROR', 1001);
define('ERR_FILE_ERROR', 1002);
define('ERR_PERMISSION_DENIED', 1003);
define('ERR_CONFIGURATION_ERROR', 1004);

// User Error Codes
define('ERR_USER_NOT_FOUND', 2001);
define('ERR_USER_INACTIVE', 2002);
define('ERR_USER_SUSPENDED', 2003);
define('ERR_USER_EXISTS', 2004);
define('ERR_INVALID_CREDENTIALS', 2005);
define('ERR_PASSWORD_MISMATCH', 2006);

// File Error Codes
define('ERR_FILE_NOT_FOUND', 3001);
define('ERR_FILE_TOO_LARGE', 3002);
define('ERR_FILE_TYPE_NOT_ALLOWED', 3003);
define('ERR_UPLOAD_FAILED', 3004);
define('ERR_STORAGE_LIMIT_EXCEEDED', 3005);
define('ERR_FILE_LIMIT_EXCEEDED', 3006);

// Share Error Codes
define('ERR_SHARE_NOT_FOUND', 4001);
define('ERR_SHARE_EXPIRED', 4002);
define('ERR_SHARE_PASSWORD_REQUIRED', 4003);
define('ERR_SHARE_PASSWORD_INCORRECT', 4004);
define('ERR_SHARE_LIMIT_EXCEEDED', 4005);

// Security Error Codes
define('ERR_CSRF_TOKEN_INVALID', 5001);
define('ERR_SESSION_EXPIRED', 5002);
define('ERR_RATE_LIMIT_EXCEEDED', 5003);
define('ERR_IP_BLOCKED', 5004);
define('ERR_SUSPICIOUS_ACTIVITY', 5005);

// ===================================================================
// Helper Functions for Constants
// ===================================================================

/**
 * รับค่าคงที่จาก prefix
 */
function getConstantsByPrefix($prefix)
{
    $constants = get_defined_constants(true)['user'];
    $result = [];
    
    foreach ($constants as $name => $value) {
        if (strpos($name, $prefix) === 0) {
            $result[$name] = $value;
        }
    }
    
    return $result;
}

/**
 * ตรวจสอบว่ามีค่าคงที่หรือไม่
 */
function hasConstant($name)
{
    return defined($name);
}

/**
 * รับค่าคงที่พร้อม default value
 */
function getConstant($name, $default = null)
{
    return defined($name) ? constant($name) : $default;
}

/**
 * รับ HTTP Status Code message
 */
function getHttpStatusMessage($code)
{
    $messages = [
        HTTP_OK => 'OK',
        HTTP_CREATED => 'Created',
        HTTP_ACCEPTED => 'Accepted',
        HTTP_NO_CONTENT => 'No Content',
        HTTP_MOVED_PERMANENTLY => 'Moved Permanently',
        HTTP_FOUND => 'Found',
        HTTP_NOT_MODIFIED => 'Not Modified',
        HTTP_BAD_REQUEST => 'Bad Request',
        HTTP_UNAUTHORIZED => 'Unauthorized',
        HTTP_FORBIDDEN => 'Forbidden',
        HTTP_NOT_FOUND => 'Not Found',
        HTTP_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        HTTP_REQUEST_TIMEOUT => 'Request Timeout',
        HTTP_CONFLICT => 'Conflict',
        HTTP_GONE => 'Gone',
        HTTP_PAYLOAD_TOO_LARGE => 'Payload Too Large',
        HTTP_TOO_MANY_REQUESTS => 'Too Many Requests',
        HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        HTTP_NOT_IMPLEMENTED => 'Not Implemented',
        HTTP_BAD_GATEWAY => 'Bad Gateway',
        HTTP_SERVICE_UNAVAILABLE => 'Service Unavailable',
        HTTP_GATEWAY_TIMEOUT => 'Gateway Timeout'
    ];
    
    return isset($messages[$code]) ? $messages[$code] : 'Unknown Status';
}

/**
 * รับ MIME type จาก file extension
 */
function getMimeTypeFromExtension($extension)
{
    $mimeTypes = [
        'jpg' => MIME_JPEG,
        'jpeg' => MIME_JPEG,
        'png' => MIME_PNG,
        'gif' => MIME_GIF,
        'webp' => MIME_WEBP,
        'pdf' => MIME_PDF,
        'doc' => MIME_DOC,
        'docx' => MIME_DOCX,
        'txt' => MIME_TXT,
        'rtf' => MIME_RTF,
        'zip' => MIME_ZIP,
        'rar' => MIME_RAR,
        'mp4' => MIME_MP4,
        'mp3' => MIME_MP3
    ];
    
    $ext = strtolower($extension);
    return isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';
}

/**
 * ตรวจสอบว่าเป็น error code หรือไม่
 */
function isErrorCode($code)
{
    return is_int($code) && $code >= 1000;
}

/**
 * แปลง bytes เป็นหน่วยที่อ่านง่าย
 */
function bytesToHumanReadable($bytes)
{
    if ($bytes >= FILE_SIZE_1GB) {
        return round($bytes / FILE_SIZE_1GB, 2) . ' GB';
    } elseif ($bytes >= FILE_SIZE_100MB) {
        return round($bytes / FILE_SIZE_1MB, 2) . ' MB';
    } elseif ($bytes >= FILE_SIZE_1MB) {
        return round($bytes / FILE_SIZE_1MB, 2) . ' MB';
    } elseif ($bytes >= FILE_SIZE_1KB) {
        return round($bytes / FILE_SIZE_1KB, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

// ===================================================================
// Constants Validation
// ===================================================================

// ตรวจสอบว่า constants สำคัญถูกกำหนดหรือไม่
$required_constants = [
    'APP_NAME', 'APP_VERSION', 'TABLE_USERS', 'ROLE_USER', 'ROLE_ADMIN',
    'FILE_SIZE_10MB', 'SHARE_TYPE_FILE', 'HTTP_OK', 'ACTIVITY_LOGIN'
];

foreach ($required_constants as $constant) {
    if (!defined($constant)) {
        error_log("Required constant '{$constant}' is not defined");
    }
}

// ===================================================================
// End of Constants Definition
// ===================================================================

if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    error_log("Constants loaded successfully. Total constants: " . count(get_defined_constants(true)['user']));
}

/*
Usage Examples:

// ใช้ table names
$users = $pdo->query("SELECT * FROM " . TABLE_USERS);

// ใช้ user roles
if ($_SESSION[SESS_USER_ROLE] === ROLE_ADMIN) {
    // Admin functionality
}

// ใช้ file size constants
if ($fileSize > FILE_SIZE_10MB) {
    // File too large
}

// ใช้ HTTP status codes
http_response_code(HTTP_NOT_FOUND);

// ใช้ helper functions
$mimeType = getMimeTypeFromExtension('jpg');
$sizeText = bytesToHumanReadable(1048576);
$statusMessage = getHttpStatusMessage(404);
*/
?>