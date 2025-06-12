<?php
/**
 * Logout Handler for File Share Hub
 * หน้าออกจากระบบ
 * 
 * @author File Share Hub
 * @version 1.0
 * @created 2025
 */

define('FILE_SHARE_HUB', true);

// รวมไฟล์ที่จำเป็น
require_once '../config/constants.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// เริ่ม session
startSession();

// ตรวจสอบว่าล็อกอินอยู่หรือไม่
if (!isLoggedIn()) {
    // ถ้าไม่ได้ล็อกอินอยู่แล้ว ให้ redirect ไปหน้าแรก
    header('Location: ' . getFullUrl('/'));
    exit;
}

// ตัวแปรสำหรับเก็บข้อมูลผู้ใช้ก่อนออกจากระบบ
$currentUserId = getCurrentUserId();
$currentUsername = getCurrentUsername();
$logoutMethod = 'manual'; // manual, timeout, forced
$logoutReason = '';

// ตรวจสอบ method การออกจากระบบ
if (isset($_GET['reason'])) {
    switch ($_GET['reason']) {
        case 'timeout':
            $logoutMethod = 'timeout';
            $logoutReason = 'Session หมดอายุ';
            break;
        case 'forced':
            $logoutMethod = 'forced';
            $logoutReason = 'ถูกบังคับให้ออกจากระบบโดยผู้ดูแล';
            break;
        case 'security':
            $logoutMethod = 'security';
            $logoutReason = 'ออกจากระบบเพื่อความปลอดภัย';
            break;
        default:
            $logoutMethod = 'manual';
            $logoutReason = 'ผู้ใช้ออกจากระบบ';
    }
} else {
    $logoutReason = 'ผู้ใช้ออกจากระบบ';
}

// ตรวจสอบ CSRF token (เฉพาะกรณี manual logout)
if ($logoutMethod === 'manual' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFFromRequest()) {
        setSession('flash_message', [
            'type' => 'error',
            'message' => 'Token ความปลอดภัยไม่ถูกต้อง'
        ]);
        header('Location: ' . getFullUrl('/dashboard/'));
        exit;
    }
}

try {
    // บันทึก activity log ก่อนออกจากระบบ
    logActivity(
        $currentUserId, 
        ACTIVITY_LOGOUT, 
        $logoutReason,
        TARGET_USER, 
        $currentUserId,
        [
            'logout_method' => $logoutMethod,
            'session_duration' => getSessionDuration(),
            'ip_address' => getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]
    );
    
    // อัพเดทเวลา last logout ในฐานข้อมูล
    updateUserLastLogout($currentUserId);
    
    // ลบ remember me token (ถ้ามี)
    clearRememberMeToken($currentUserId);
    
    // ลบ session ที่เกี่ยวข้องในฐานข้อมูล (ถ้าใช้ database sessions)
    cleanupUserSessions($currentUserId);
    
    // ทำความสะอาด session data
    cleanupSessionData();
    
    // ออกจากระบบ
    logoutUser();
    
    // ตั้งค่าข้อความแจ้งเตือน
    $message = '';
    $messageType = 'success';
    
    switch ($logoutMethod) {
        case 'timeout':
            $message = 'หมดเวลาการใช้งาน กรุณาเข้าสู่ระบบใหม่';
            $messageType = 'warning';
            break;
        case 'forced':
            $message = 'คุณถูกบังคับให้ออกจากระบบโดยผู้ดูแล';
            $messageType = 'error';
            break;
        case 'security':
            $message = 'ออกจากระบบเพื่อความปลอดภัย กรุณาเข้าสู่ระบบใหม่';
            $messageType = 'warning';
            break;
        default:
            $message = 'ออกจากระบบสำเร็จ ขอบคุณที่ใช้บริการ';
            $messageType = 'success';
    }
    
    // ตั้งค่าข้อความใน session ใหม่
    startSession();
    setSession('flash_message', [
        'type' => $messageType,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    
    // ถ้าเกิดข้อผิดพลาด ยังคงต้องออกจากระบบ
    logoutUser();
    
    startSession();
    setSession('flash_message', [
        'type' => 'warning',
        'message' => 'ออกจากระบบสำเร็จ แต่มีข้อผิดพลาดบางประการ'
    ]);
}

// กำหนดหน้าปลายทาง
$redirectUrl = '/auth/login.php';

// ตรวจสอบ redirect parameter
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $redirect = filter_var($_GET['redirect'], FILTER_SANITIZE_URL);
    
    // ตรวจสอบว่า URL ปลอดภัย (ภายในเว็บไซต์เท่านั้น)
    if (str_starts_with($redirect, '/') && !str_contains($redirect, '//')) {
        $redirectUrl = $redirect;
    }
}

// Redirect ไปยังหน้าที่ต้องการ
header('Location: ' . getFullUrl($redirectUrl));
exit;

/**
 * อัพเดทเวลา last logout ในฐานข้อมูล
 * 
 * @param int $userId
 */
function updateUserLastLogout($userId)
{
    try {
        $pdo = getDatabase();
        
        // อัพเดทในตาราง users
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_USERS . " 
            SET last_login = NULL, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        
        // อัพเดทในตาราง pure_users
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_PURE_USERS . " 
            SET last_login = NULL, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        
    } catch (Exception $e) {
        error_log("Error updating last logout: " . $e->getMessage());
    }
}

/**
 * ลบ remember me token
 * 
 * @param int $userId
 */
function clearRememberMeToken($userId)
{
    try {
        // ลบ cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
        
        // ลบ token ในฐานข้อมูล (ถ้ามีการเก็บ)
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_USERS . " 
            SET remember_token = NULL, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        
    } catch (Exception $e) {
        error_log("Error clearing remember me token: " . $e->getMessage());
    }
}

/**
 * ลบ sessions ของผู้ใช้ในฐานข้อมูล
 * 
 * @param int $userId
 */
function cleanupUserSessions($userId)
{
    try {
        if (defined('USE_DATABASE_SESSIONS') && USE_DATABASE_SESSIONS) {
            $pdo = getDatabase();
            
            // ลบ sessions ทั้งหมดของผู้ใช้
            $stmt = $pdo->prepare("
                DELETE FROM " . TABLE_SESSIONS . " 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
        }
    } catch (Exception $e) {
        error_log("Error cleaning up user sessions: " . $e->getMessage());
    }
}

/**
 * ทำความสะอาด session data ก่อนออกจากระบบ
 */
function cleanupSessionData()
{
    try {
        // เก็บข้อมูลสำคัญไว้ก่อน
        $language = getSession('language', DEFAULT_LANGUAGE);
        $csrfTokens = getSession('__csrf_tokens', []);
        
        // ลบข้อมูลที่อ่อนไหว
        $sensitiveKeys = [
            SESS_USER_ID,
            SESS_USERNAME,
            SESS_USER_ROLE,
            SESS_LOGIN_TIME,
            'user_permissions',
            'admin_access',
            'temp_data',
            'upload_progress'
        ];
        
        foreach ($sensitiveKeys as $key) {
            removeSession($key);
        }
        
        // คืนค่าข้อมูลที่จำเป็น
        setSession('language', $language);
        setSession('__csrf_tokens', $csrfTokens);
        
    } catch (Exception $e) {
        error_log("Error cleaning up session data: " . $e->getMessage());
    }
}

/**
 * คำนวณระยะเวลาที่ใช้ session
 * 
 * @return int ระยะเวลาเป็นวินาที
 */
function getSessionDuration()
{
    $loginTime = getSession(SESS_LOGIN_TIME);
    if ($loginTime) {
        return time() - $loginTime;
    }
    return 0;
}

/**
 * บังคับออกจากระบบสำหรับผู้ใช้คนอื่น (ใช้โดย Admin)
 * 
 * @param int $targetUserId
 * @param int $adminUserId
 * @param string $reason
 * @return bool
 */
function forceLogoutUser($targetUserId, $adminUserId, $reason = 'ถูกบังคับออกจากระบบ')
{
    try {
        // ตรวจสอบสิทธิ์ Admin
        if (!isAdmin()) {
            return false;
        }
        
        $pdo = getDatabase();
        
        // ลบ sessions ทั้งหมดของผู้ใช้เป้าหมาย
        if (defined('USE_DATABASE_SESSIONS') && USE_DATABASE_SESSIONS) {
            $stmt = $pdo->prepare("
                DELETE FROM " . TABLE_SESSIONS . " 
                WHERE user_id = ?
            ");
            $stmt->execute([$targetUserId]);
        }
        
        // ลบ remember me tokens
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_USERS . " 
            SET remember_token = NULL, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        $stmt->execute([$targetUserId]);
        
        // บันทึก activity log
        logActivity(
            $adminUserId,
            ACTIVITY_ADMIN,
            "Admin บังคับให้ผู้ใช้ ID {$targetUserId} ออกจากระบบ: {$reason}",
            TARGET_USER,
            $targetUserId,
            [
                'action' => 'force_logout',
                'reason' => $reason,
                'admin_id' => $adminUserId
            ]
        );
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error forcing logout: " . $e->getMessage());
        return false;
    }
}

/**
 * ออกจากระบบทุกอุปกรณ์ (Logout from all devices)
 * 
 * @param int $userId
 * @return bool
 */
function logoutFromAllDevices($userId)
{
    try {
        $pdo = getDatabase();
        
        // ลบ sessions ทั้งหมด
        if (defined('USE_DATABASE_SESSIONS') && USE_DATABASE_SESSIONS) {
            $stmt = $pdo->prepare("
                DELETE FROM " . TABLE_SESSIONS . " 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
        }
        
        // ลบ remember me tokens
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_USERS . " 
            SET remember_token = NULL, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        
        // บันทึก activity log
        logActivity(
            $userId,
            ACTIVITY_LOGOUT,
            'ออกจากระบบทุกอุปกรณ์',
            TARGET_USER,
            $userId,
            ['action' => 'logout_all_devices']
        );
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error logging out from all devices: " . $e->getMessage());
        return false;
    }
}

/**
 * ตรวจสอบและจัดการ session หมดอายุ
 */
function handleSessionExpiry()
{
    if (isLoggedIn()) {
        $lastActivity = getSession('__last_activity');
        $sessionLifetime = SESSION_LIFETIME;
        
        if ($lastActivity && (time() - $lastActivity) > $sessionLifetime) {
            // Session หมดอายุ
            $userId = getCurrentUserId();
            
            // บันทึก log
            logActivity(
                $userId,
                ACTIVITY_LOGOUT,
                'Session หมดอายุอัตโนมัติ',
                TARGET_USER,
                $userId,
                ['session_expired' => true]
            );
            
            // ออกจากระบบและ redirect
            logoutUser();
            
            startSession();
            setSession('flash_message', [
                'type' => 'warning',
                'message' => 'หมดเวลาการใช้งาน กรุณาเข้าสู่ระบบใหม่'
            ]);
            
            header('Location: ' . getFullUrl('/auth/login.php?reason=timeout'));
            exit;
        }
    }
}

/**
 * สร้าง logout URL พร้อม CSRF token
 * 
 * @param string $redirect
 * @param string $reason
 * @return string
 */
function generateLogoutUrl($redirect = '', $reason = '')
{
    $params = [];
    
    if (!empty($redirect)) {
        $params['redirect'] = urlencode($redirect);
    }
    
    if (!empty($reason)) {
        $params['reason'] = $reason;
    }
    
    $queryString = !empty($params) ? '?' . http_build_query($params) : '';
    
    return getFullUrl('/auth/logout.php' . $queryString);
}

// ตรวจสอบ session หมดอายุ (เรียกใช้ทุกครั้งที่มีการโหลดหน้า logout)
if (isLoggedIn()) {
    handleSessionExpiry();
}

/**
 * JavaScript สำหรับ logout confirmation
 */
function getLogoutConfirmationScript()
{
    return "
    <script>
    // Logout confirmation function
    window.confirmLogout = function(url, message) {
        if (typeof message === 'undefined') {
            message = 'คุณต้องการออกจากระบบหรือไม่?';
        }
        
        if (confirm(message)) {
            // Create form for POST logout
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url || '" . getFullUrl('/auth/logout.php') . "';
            
            // Add CSRF token
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = window.fileShareHub ? window.fileShareHub.csrfToken : '';
            form.appendChild(csrfInput);
            
            // Submit form
            document.body.appendChild(form);
            form.submit();
        }
        return false;
    };
    
    // Auto logout on page unload (security measure)
    window.addEventListener('beforeunload', function() {
        // Cancel any pending logout timers
        if (window.logoutTimer) {
            clearTimeout(window.logoutTimer);
        }
    });
    
    // Logout timer for idle users
    window.startLogoutTimer = function(minutes) {
        if (window.logoutTimer) {
            clearTimeout(window.logoutTimer);
        }
        
        window.logoutTimer = setTimeout(function() {
            if (confirm('คุณไม่ได้ใช้งานเป็นเวลานาน ต้องการออกจากระบบหรือไม่?')) {
                window.location.href = '" . generateLogoutUrl('', 'timeout') . "';
            } else {
                // Restart timer
                window.startLogoutTimer(minutes);
            }
        }, minutes * 60 * 1000);
    };
    </script>
    ";
}

// ถ้าเป็นการเรียกใช้แบบ AJAX
if (isAjaxRequest()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => $message ?? 'ออกจากระบบสำเร็จ',
        'redirect' => getFullUrl($redirectUrl)
    ]);
    exit;
}

/* 
Usage Examples:

1. Simple logout link:
<a href="<?= getFullUrl('/auth/logout.php') ?>" onclick="return confirmLogout()">ออกจากระบบ</a>

2. Logout with redirect:
<a href="<?= generateLogoutUrl('/') ?>">ออกจากระบบและกลับหน้าแรก</a>

3. Force logout (Admin only):
<?php if (isAdmin()): ?>
<button onclick="forceLogout(<?= $targetUserId ?>)">บังคับออกจากระบบ</button>
<?php endif; ?>

4. Logout from all devices:
<button onclick="logoutAllDevices()">ออกจากระบบทุกอุปกรณ์</button>

5. Add logout timer:
<script>
// Start 30-minute idle timer
window.startLogoutTimer(30);
</script>
*/
?>