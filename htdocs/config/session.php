<?php
/**
 * Session Management for File Share Hub
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
// Session Configuration Class
// ===================================================================

class SessionManager 
{
    private static $instance = null;
    private $sessionName;
    private $sessionLifetime;
    private $sessionPath;
    private $isStarted = false;
    private $database = null;

    /**
     * Private constructor (Singleton)
     */
    private function __construct()
    {
        $this->sessionName = defined('SESSION_NAME') ? SESSION_NAME : 'FILESHAREHUB_SESSION';
        $this->sessionLifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 1800; // 30 minutes
        $this->sessionPath = defined('ROOT_PATH') ? ROOT_PATH . '/tmp' : sys_get_temp_dir();
        
        $this->configureSession();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Configure session settings สำหรับ InfinityFree
     */
    private function configureSession()
    {
        // Basic session configuration
        ini_set('session.name', $this->sessionName);
        ini_set('session.cookie_lifetime', $this->sessionLifetime);
        ini_set('session.gc_maxlifetime', $this->sessionLifetime);
        
        // Security settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        
        // Session ID settings
        ini_set('session.entropy_length', 32);
        ini_set('session.hash_function', 'sha256');
        ini_set('session.hash_bits_per_character', 5);
        
        // การเก็บ session - ใช้ database สำหรับ InfinityFree
        if (defined('USE_DATABASE_SESSIONS') && USE_DATABASE_SESSIONS === true) {
            session_set_save_handler(
                [$this, 'sessionOpen'],
                [$this, 'sessionClose'],
                [$this, 'sessionRead'],
                [$this, 'sessionWrite'],
                [$this, 'sessionDestroy'],
                [$this, 'sessionGC']
            );
        } else {
            // ใช้ file sessions
            if (!is_dir($this->sessionPath)) {
                @mkdir($this->sessionPath, 0755, true);
            }
            ini_set('session.save_path', $this->sessionPath);
        }
    }

    /**
     * Start session
     */
    public function start()
    {
        if ($this->isStarted) {
            return true;
        }

        // ตรวจสอบว่า headers ยังไม่ส่งหรือไม่
        if (headers_sent()) {
            error_log("Cannot start session: headers already sent");
            return false;
        }

        try {
            if (session_start()) {
                $this->isStarted = true;
                
                // Regenerate session ID เป็นระยะ
                $this->regenerateIfNeeded();
                
                // ตรวจสอบ session timeout
                if ($this->isExpired()) {
                    $this->destroy();
                    return $this->start(); // เริ่มใหม่
                }
                
                // อัพเดทเวลา activity
                $this->updateActivity();
                
                // ตรวจสอบ IP address (optional)
                if (defined('CHECK_SESSION_IP') && CHECK_SESSION_IP === true) {
                    if (!$this->validateIP()) {
                        $this->destroy();
                        return false;
                    }
                }
                
                return true;
            }
        } catch (Exception $e) {
            error_log("Session start error: " . $e->getMessage());
            return false;
        }
        
        return false;
    }

    /**
     * Regenerate session ID if needed
     */
    private function regenerateIfNeeded()
    {
        $regenerateTime = defined('SESSION_REGENERATE_TIME') ? SESSION_REGENERATE_TIME : 300; // 5 minutes
        
        if (!isset($_SESSION['__last_regenerate'])) {
            $_SESSION['__last_regenerate'] = time();
            session_regenerate_id(true);
        } elseif (time() - $_SESSION['__last_regenerate'] > $regenerateTime) {
            $_SESSION['__last_regenerate'] = time();
            session_regenerate_id(true);
        }
    }

    /**
     * Check if session is expired
     */
    private function isExpired()
    {
        if (!isset($_SESSION['__last_activity'])) {
            return false;
        }
        
        return (time() - $_SESSION['__last_activity']) > $this->sessionLifetime;
    }

    /**
     * Update last activity time
     */
    private function updateActivity()
    {
        $_SESSION['__last_activity'] = time();
        $_SESSION['__ip_address'] = $this->getClientIP();
        $_SESSION['__user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Validate IP address
     */
    private function validateIP()
    {
        if (!isset($_SESSION['__ip_address'])) {
            return true; // ครั้งแรก
        }
        
        return $_SESSION['__ip_address'] === $this->getClientIP();
    }

    /**
     * Get client IP address
     */
    private function getClientIP()
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Destroy session
     */
    public function destroy()
    {
        if ($this->isStarted) {
            session_unset();
            session_destroy();
            
            // ลบ cookie
            if (isset($_COOKIE[$this->sessionName])) {
                setcookie($this->sessionName, '', time() - 3600, '/');
            }
            
            $this->isStarted = false;
        }
    }

    /**
     * Check if session is started
     */
    public function isStarted()
    {
        return $this->isStarted;
    }

    /**
     * Get session ID
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Set session data
     */
    public function set($key, $value)
    {
        if (!$this->isStarted) {
            $this->start();
        }
        $_SESSION[$key] = $value;
    }

    /**
     * Get session data
     */
    public function get($key, $default = null)
    {
        if (!$this->isStarted) {
            $this->start();
        }
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Check if session key exists
     */
    public function has($key)
    {
        if (!$this->isStarted) {
            $this->start();
        }
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session data
     */
    public function remove($key)
    {
        if (!$this->isStarted) {
            $this->start();
        }
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public function all()
    {
        if (!$this->isStarted) {
            $this->start();
        }
        return $_SESSION;
    }

    // ===================================================================
    // Database Session Handlers (สำหรับ InfinityFree)
    // ===================================================================

    /**
     * Session open handler
     */
    public function sessionOpen($savePath, $sessionName)
    {
        try {
            if (function_exists('getDatabase')) {
                $this->database = getDatabase();
                return true;
            }
        } catch (Exception $e) {
            error_log("Session open error: " . $e->getMessage());
        }
        return false;
    }

    /**
     * Session close handler
     */
    public function sessionClose()
    {
        return true;
    }

    /**
     * Session read handler
     */
    public function sessionRead($sessionId)
    {
        try {
            if (!$this->database) {
                return '';
            }

            $stmt = $this->database->prepare("
                SELECT session_data 
                FROM " . (defined('TABLE_SESSIONS') ? TABLE_SESSIONS : 'sessions') . " 
                WHERE session_id = ? AND last_activity > ?
            ");
            
            $expiry = time() - $this->sessionLifetime;
            $stmt->execute([$sessionId, date('Y-m-d H:i:s', $expiry)]);
            
            $result = $stmt->fetch();
            return $result ? $result['session_data'] : '';
            
        } catch (Exception $e) {
            error_log("Session read error: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Session write handler
     */
    public function sessionWrite($sessionId, $sessionData)
    {
        try {
            if (!$this->database) {
                return false;
            }

            $userId = $this->get(defined('SESS_USER_ID') ? SESS_USER_ID : 'user_id');
            $ipAddress = $this->getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $this->database->prepare("
                INSERT INTO " . (defined('TABLE_SESSIONS') ? TABLE_SESSIONS : 'sessions') . " 
                (session_id, user_id, session_data, ip_address, user_agent, last_activity, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                user_id = VALUES(user_id),
                session_data = VALUES(session_data),
                ip_address = VALUES(ip_address),
                user_agent = VALUES(user_agent),
                last_activity = VALUES(last_activity)
            ");
            
            $now = date('Y-m-d H:i:s');
            return $stmt->execute([$sessionId, $userId, $sessionData, $ipAddress, $userAgent, $now, $now]);
            
        } catch (Exception $e) {
            error_log("Session write error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Session destroy handler
     */
    public function sessionDestroy($sessionId)
    {
        try {
            if (!$this->database) {
                return false;
            }

            $stmt = $this->database->prepare("
                DELETE FROM " . (defined('TABLE_SESSIONS') ? TABLE_SESSIONS : 'sessions') . " 
                WHERE session_id = ?
            ");
            
            return $stmt->execute([$sessionId]);
            
        } catch (Exception $e) {
            error_log("Session destroy error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Session garbage collection handler
     */
    public function sessionGC($maxLifetime)
    {
        try {
            if (!$this->database) {
                return false;
            }

            $expiry = date('Y-m-d H:i:s', time() - $maxLifetime);
            
            $stmt = $this->database->prepare("
                DELETE FROM " . (defined('TABLE_SESSIONS') ? TABLE_SESSIONS : 'sessions') . " 
                WHERE last_activity < ?
            ");
            
            return $stmt->execute([$expiry]);
            
        } catch (Exception $e) {
            error_log("Session GC error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() 
    {
        throw new Exception("Cannot unserialize singleton");
    }
}

// ===================================================================
// Session Helper Functions
// ===================================================================

/**
 * Start session
 */
function startSession()
{
    return SessionManager::getInstance()->start();
}

/**
 * Destroy session
 */
function destroySession()
{
    SessionManager::getInstance()->destroy();
}

/**
 * Set session data
 */
function setSession($key, $value)
{
    SessionManager::getInstance()->set($key, $value);
}

/**
 * Get session data
 */
function getSession($key, $default = null)
{
    return SessionManager::getInstance()->get($key, $default);
}

/**
 * Check if session key exists
 */
function hasSession($key)
{
    return SessionManager::getInstance()->has($key);
}

/**
 * Remove session data
 */
function removeSession($key)
{
    SessionManager::getInstance()->remove($key);
}

/**
 * Get all session data
 */
function getAllSession()
{
    return SessionManager::getInstance()->all();
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    $userIdKey = defined('SESS_USER_ID') ? SESS_USER_ID : 'user_id';
    return hasSession($userIdKey) && getSession($userIdKey) > 0;
}

/**
 * Get current user ID
 */
function getCurrentUserId()
{
    $userIdKey = defined('SESS_USER_ID') ? SESS_USER_ID : 'user_id';
    return isLoggedIn() ? getSession($userIdKey) : null;
}

/**
 * Get current username
 */
function getCurrentUsername()
{
    $usernameKey = defined('SESS_USERNAME') ? SESS_USERNAME : 'username';
    return isLoggedIn() ? getSession($usernameKey) : null;
}

/**
 * Get current user role
 */
function getCurrentUserRole()
{
    $roleKey = defined('SESS_USER_ROLE') ? SESS_USER_ROLE : 'user_role';
    return isLoggedIn() ? getSession($roleKey) : null;
}

/**
 * Check if current user is admin
 */
function isAdmin()
{
    $adminRole = defined('ROLE_ADMIN') ? ROLE_ADMIN : 'admin';
    return getCurrentUserRole() === $adminRole;
}

/**
 * Check if current user is moderator
 */
function isModerator()
{
    $modRole = defined('ROLE_MODERATOR') ? ROLE_MODERATOR : 'moderator';
    return getCurrentUserRole() === $modRole || isAdmin();
}

/**
 * Login user
 */
function loginUser($userId, $username, $userRole = null)
{
    $userIdKey = defined('SESS_USER_ID') ? SESS_USER_ID : 'user_id';
    $usernameKey = defined('SESS_USERNAME') ? SESS_USERNAME : 'username';
    $roleKey = defined('SESS_USER_ROLE') ? SESS_USER_ROLE : 'user_role';
    $loginTimeKey = defined('SESS_LOGIN_TIME') ? SESS_LOGIN_TIME : 'login_time';
    
    setSession($userIdKey, $userId);
    setSession($usernameKey, $username);
    
    if ($userRole) {
        setSession($roleKey, $userRole);
    }
    
    setSession($loginTimeKey, time());
    
    // Regenerate session ID เพื่อความปลอดภัย
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Logout user
 */
function logoutUser()
{
    destroySession();
}

/**
 * Get session info
 */
function getSessionInfo()
{
    if (!SessionManager::getInstance()->isStarted()) {
        return null;
    }
    
    return [
        'session_id' => SessionManager::getInstance()->getId(),
        'is_logged_in' => isLoggedIn(),
        'user_id' => getCurrentUserId(),
        'username' => getCurrentUsername(),
        'user_role' => getCurrentUserRole(),
        'login_time' => getSession(defined('SESS_LOGIN_TIME') ? SESS_LOGIN_TIME : 'login_time'),
        'last_activity' => getSession('__last_activity'),
        'ip_address' => getSession('__ip_address'),
        'user_agent' => getSession('__user_agent')
    ];
}

// ===================================================================
// CSRF Protection Functions
// ===================================================================

/**
 * Generate CSRF token
 */
function generateCSRFToken()
{
    if (!hasSession('__csrf_tokens')) {
        setSession('__csrf_tokens', []);
    }
    
    $token = bin2hex(random_bytes(32));
    $tokens = getSession('__csrf_tokens', []);
    
    // เก็บ token สูงสุด 10 ตัว
    if (count($tokens) >= 10) {
        array_shift($tokens);
    }
    
    $tokens[$token] = time();
    setSession('__csrf_tokens', $tokens);
    
    return $token;
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token)
{
    if (!$token || !hasSession('__csrf_tokens')) {
        return false;
    }
    
    $tokens = getSession('__csrf_tokens', []);
    
    if (!isset($tokens[$token])) {
        return false;
    }
    
    // ตรวจสอบอายุของ token
    $tokenLifetime = defined('CSRF_TOKEN_LIFETIME') ? CSRF_TOKEN_LIFETIME : 3600; // 1 hour
    
    if (time() - $tokens[$token] > $tokenLifetime) {
        unset($tokens[$token]);
        setSession('__csrf_tokens', $tokens);
        return false;
    }
    
    return true;
}

/**
 * Get CSRF token for forms
 */
function getCSRFToken()
{
    $tokenName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';
    
    if (!hasSession($tokenName)) {
        setSession($tokenName, generateCSRFToken());
    }
    
    return getSession($tokenName);
}

/**
 * Validate CSRF token from request
 */
function validateCSRFFromRequest()
{
    $tokenName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';
    $token = $_POST[$tokenName] ?? $_GET[$tokenName] ?? null;
    
    return validateCSRFToken($token);
}

// ===================================================================
// Auto-initialize Session
// ===================================================================

// Enable database sessions สำหรับ InfinityFree
if (!defined('USE_DATABASE_SESSIONS')) {
    define('USE_DATABASE_SESSIONS', true);
}

// Auto-start session ถ้าไม่ได้ระบุให้ปิด
if (!defined('AUTO_START_SESSION') || AUTO_START_SESSION === true) {
    if (PHP_SAPI !== 'cli') { // ไม่ start session ใน CLI mode
        startSession();
    }
}

// ===================================================================
// Session Security Headers
// ===================================================================

if (session_status() === PHP_SESSION_ACTIVE) {
    // ตั้งค่า security headers
    if (!headers_sent()) {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        
        if (isset($_SERVER['HTTPS'])) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

// ===================================================================
// End of Session Configuration
// ===================================================================

if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    error_log("Session manager initialized successfully");
}

/*
Usage Examples:

// Basic usage
startSession();
setSession('username', 'john_doe');
$username = getSession('username');

// User authentication
if (isLoggedIn()) {
    echo "Welcome " . getCurrentUsername();
}

// Admin check
if (isAdmin()) {
    // Show admin features
}

// Login user
loginUser(123, 'john_doe', 'admin');

// Logout
logoutUser();

// CSRF protection
$token = getCSRFToken();
echo '<input type="hidden" name="csrf_token" value="' . $token . '">';

if (validateCSRFFromRequest()) {
    // Process form
}

// Session info
$info = getSessionInfo();
print_r($info);
*/
?>