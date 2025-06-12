<?php
/**
 * API: Check Username/Email Availability
 * ตรวจสอบความพร้อมใช้งานของ Username และ Email
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

// ตั้งค่า Content-Type เป็น JSON
header('Content-Type: application/json; charset=utf-8');

// อนุญาตเฉพาะ POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', ERR_INVALID_REQUEST, HTTP_METHOD_NOT_ALLOWED);
}

// ตรวจสอบ CSRF token
if (!validateCSRFFromRequest()) {
    sendError('Invalid CSRF token', ERR_CSRF_TOKEN_INVALID, HTTP_FORBIDDEN);
}

// ตรวจสอบ rate limiting
if (!checkRateLimit('check_availability', 20, 60)) {
    sendError('Too many requests', ERR_RATE_LIMIT_EXCEEDED, HTTP_TOO_MANY_REQUESTS);
}

try {
    // รับข้อมูลจาก POST
    $type = sanitizeInput($_POST['type'] ?? '');
    $value = sanitizeInput($_POST['value'] ?? '');
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($type) || empty($value)) {
        sendError('Missing required parameters', ERR_INVALID_REQUEST, HTTP_BAD_REQUEST);
    }
    
    // ตรวจสอบประเภทที่รองรับ
    $allowedTypes = ['username', 'email'];
    if (!in_array($type, $allowedTypes)) {
        sendError('Invalid type parameter', ERR_INVALID_REQUEST, HTTP_BAD_REQUEST);
    }
    
    // เชื่อมต่อฐานข้อมูล
    $pdo = getDatabase();
    
    $isAvailable = false;
    $message = '';
    $suggestions = [];
    
    switch ($type) {
        case 'username':
            $isAvailable = checkUsernameAvailability($pdo, $value, $message, $suggestions);
            break;
            
        case 'email':
            $isAvailable = checkEmailAvailability($pdo, $value, $message, $suggestions);
            break;
    }
    
    // ส่งผลลัพธ์
    sendSuccess([
        'available' => $isAvailable,
        'message' => $message,
        'suggestions' => $suggestions,
        'type' => $type,
        'value' => $value
    ]);
    
} catch (Exception $e) {
    error_log("Check availability error: " . $e->getMessage());
    sendError('Internal server error', ERR_SYSTEM_ERROR, HTTP_INTERNAL_SERVER_ERROR);
}

/**
 * ตรวจสอบความพร้อมใช้งานของ Username
 * 
 * @param PDO $pdo
 * @param string $username
 * @param string &$message
 * @param array &$suggestions
 * @return bool
 */
function checkUsernameAvailability($pdo, $username, &$message, &$suggestions)
{
    // ตรวจสอบรูปแบบ username
    if (!preg_match(REGEX_USERNAME, $username)) {
        $message = 'ชื่อผู้ใช้ต้องเป็นตัวอักษร ตัวเลข _ หรือ - เท่านั้น (3-20 ตัวอักษร)';
        return false;
    }
    
    // ตรวจสอบความยาว
    if (strlen($username) < 3) {
        $message = 'ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร';
        return false;
    }
    
    if (strlen($username) > 20) {
        $message = 'ชื่อผู้ใช้ต้องไม่เกิน 20 ตัวอักษร';
        return false;
    }
    
    // ตรวจสอบคำที่สงวนไว้
    $reservedUsernames = [
        'admin', 'administrator', 'root', 'system', 'api', 'www',
        'mail', 'email', 'support', 'help', 'info', 'contact',
        'test', 'demo', 'guest', 'user', 'null', 'undefined',
        'true', 'false', 'ftp', 'http', 'https', 'www', 'blog',
        'forum', 'shop', 'store', 'news', 'media', 'static',
        'cdn', 'asset', 'upload', 'download', 'file', 'share'
    ];
    
    if (in_array(strtolower($username), $reservedUsernames)) {
        $message = 'ชื่อผู้ใช้นี้ถูกสงวนไว้ โปรดเลือกชื่ือื่น';
        $suggestions = generateUsernameSuggestions($username);
        return false;
    }
    
    // ตรวจสอบในฐานข้อมูล
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM " . TABLE_USERS . " 
        WHERE LOWER(username) = LOWER(?)
    ");
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $message = 'ชื่อผู้ใช้นี้มีการใช้งานแล้ว';
        $suggestions = generateUsernameSuggestions($username);
        return false;
    }
    
    // ตรวจสอบความคล้ายคลึงกับผู้ใช้ที่มีอยู่
    $similarCount = checkSimilarUsernames($pdo, $username);
    if ($similarCount >= 3) {
        $message = 'มีชื่อผู้ใช้ที่คล้ายกันมาก โปรดเลือกชื่ออื่น';
        $suggestions = generateUsernameSuggestions($username);
        return false;
    }
    
    $message = 'ชื่อผู้ใช้นี้ใช้ได้';
    return true;
}

/**
 * ตรวจสอบความพร้อมใช้งานของ Email
 * 
 * @param PDO $pdo
 * @param string $email
 * @param string &$message
 * @param array &$suggestions
 * @return bool
 */
function checkEmailAvailability($pdo, $email, &$message, &$suggestions)
{
    // ตรวจสอบรูปแบบ email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'รูปแบบอีเมลไม่ถูกต้อง';
        return false;
    }
    
    // ตรวจสอบความยาว
    if (strlen($email) > 100) {
        $message = 'อีเมลต้องไม่เกิน 100 ตัวอักษร';
        return false;
    }
    
    // แยกส่วน local และ domain
    $emailParts = explode('@', $email);
    if (count($emailParts) !== 2) {
        $message = 'รูปแบบอีเมลไม่ถูกต้อง';
        return false;
    }
    
    $localPart = $emailParts[0];
    $domain = strtolower($emailParts[1]);
    
    // ตรวจสอบความยาวของ local part
    if (strlen($localPart) < 1 || strlen($localPart) > 64) {
        $message = 'ส่วนก่อน @ ของอีเมลไม่ถูกต้อง';
        return false;
    }
    
    // ตรวจสอบ domain ที่ถูกบล็อค (ถ้ามี)
    $blockedDomains = [
        '10minutemail.com',
        'tempmail.org',
        'guerrillamail.com',
        'mailinator.com',
        'throwaway.email'
    ];
    
    if (in_array($domain, $blockedDomains)) {
        $message = 'ไม่อนุญาตให้ใช้อีเมลชั่วคราว';
        return false;
    }
    
    // ตรวจสอบ domain ที่แนะนำ
    $suggestedDomains = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
        'live.com', 'icloud.com', 'protonmail.com'
    ];
    
    // แนะนำการแก้ไข typo ใน domain
    $domainSuggestion = suggestEmailDomain($domain, $suggestedDomains);
    if ($domainSuggestion && $domainSuggestion !== $domain) {
        $suggestions[] = $localPart . '@' . $domainSuggestion;
    }
    
    // ตรวจสอบในฐานข้อมูล
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM " . TABLE_USERS . " 
        WHERE LOWER(email) = LOWER(?)
    ");
    $stmt->execute([$email]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $message = 'อีเมลนี้มีการใช้งานแล้ว';
        
        // แนะนำการใช้ alias หรือ subdomain
        if (strpos($domain, 'gmail.com') !== false) {
            $suggestions[] = str_replace('@gmail.com', '+alias@gmail.com', $email);
        }
        
        return false;
    }
    
    $message = 'อีเมลนี้ใช้ได้';
    return true;
}

/**
 * ตรวจสอบ username ที่คล้ายคลึงกัน
 * 
 * @param PDO $pdo
 * @param string $username
 * @return int
 */
function checkSimilarUsernames($pdo, $username)
{
    // ตรวจสอบ username ที่เหมือนกันยกเว้นตัวเลขท้าย
    $baseUsername = preg_replace('/\d+$/', '', $username);
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM " . TABLE_USERS . " 
        WHERE username REGEXP ?
    ");
    $pattern = '^' . preg_quote($baseUsername, '/') . '[0-9]*$';
    $stmt->execute([$pattern]);
    
    return $stmt->fetchColumn();
}

/**
 * สร้างคำแนะนำสำหรับ username
 * 
 * @param string $username
 * @return array
 */
function generateUsernameSuggestions($username)
{
    $suggestions = [];
    $baseUsername = preg_replace('/\d+$/', '', $username);
    
    // เพิ่มตัวเลขท้าย
    for ($i = 1; $i <= 5; $i++) {
        $suggestions[] = $baseUsername . rand(10, 99);
        $suggestions[] = $baseUsername . date('y') . $i;
    }
    
    // เพิ่ม prefix/suffix
    $prefixes = ['user', 'member', 'new'];
    $suffixes = ['th', 'user', date('y')];
    
    foreach ($prefixes as $prefix) {
        if (strlen($prefix . $username) <= 20) {
            $suggestions[] = $prefix . $username;
        }
    }
    
    foreach ($suffixes as $suffix) {
        if (strlen($username . $suffix) <= 20) {
            $suggestions[] = $username . $suffix;
        }
    }
    
    // เอาเฉพาะที่ไม่ซ้ำและจำกัดจำนวน
    $suggestions = array_unique($suggestions);
    return array_slice($suggestions, 0, 5);
}

/**
 * แนะนำ domain ที่ถูกต้องสำหรับ email
 * 
 * @param string $domain
 * @param array $suggestedDomains
 * @return string|null
 */
function suggestEmailDomain($domain, $suggestedDomains)
{
    $minDistance = PHP_INT_MAX;
    $suggestion = null;
    
    foreach ($suggestedDomains as $suggestedDomain) {
        $distance = levenshtein($domain, $suggestedDomain);
        
        // ถ้าระยะห่างน้อยและไม่เหมือนกันทุกประการ
        if ($distance < $minDistance && $distance > 0 && $distance <= 2) {
            $minDistance = $distance;
            $suggestion = $suggestedDomain;
        }
    }
    
    return $suggestion;
}

/**
 * บันทึก activity log สำหรับการตรวจสอบ
 * 
 * @param string $type
 * @param string $value
 * @param bool $available
 */
function logAvailabilityCheck($type, $value, $available)
{
    try {
        $description = sprintf(
            'ตรวจสอบ %s: %s - %s',
            $type,
            $value,
            $available ? 'ใช้ได้' : 'ไม่ใช้ได้'
        );
        
        logActivity(
            null, // ไม่มี user_id เพราะยังไม่ได้ล็อกอิน
            ACTIVITY_SYSTEM,
            $description,
            TARGET_SYSTEM,
            null,
            [
                'type' => $type,
                'value' => $value,
                'available' => $available,
                'ip_address' => getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]
        );
    } catch (Exception $e) {
        // ไม่ต้องหยุดการทำงานถ้า log ไม่สำเร็จ
        error_log("Failed to log availability check: " . $e->getMessage());
    }
}

/**
 * ตรวจสอบรูปแบบรหัสผ่าน (ใช้ในอนาคต)
 * 
 * @param string $password
 * @return array
 */
function validatePasswordStrength($password)
{
    $result = [
        'valid' => true,
        'score' => 0,
        'feedback' => [],
        'suggestions' => []
    ];
    
    // ตรวจสอบความยาว
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $result['valid'] = false;
        $result['feedback'][] = 'รหัสผ่านต้องมีอย่างน้อย ' . PASSWORD_MIN_LENGTH . ' ตัวอักษร';
    } else {
        $result['score'] += 20;
    }
    
    // ตรวจสอบตัวอักษรเล็ก
    if (preg_match('/[a-z]/', $password)) {
        $result['score'] += 10;
    } else {
        $result['suggestions'][] = 'ควรมีตัวอักษรเล็ก';
    }
    
    // ตรวจสอบตัวอักษรใหญ่
    if (preg_match('/[A-Z]/', $password)) {
        $result['score'] += 15;
    } else {
        $result['suggestions'][] = 'ควรมีตัวอักษรใหญ่';
    }
    
    // ตรวจสอบตัวเลข
    if (preg_match('/[0-9]/', $password)) {
        $result['score'] += 15;
    } else {
        $result['suggestions'][] = 'ควรมีตัวเลข';
    }
    
    // ตรวจสอบอักขระพิเศษ
    if (preg_match('/[^A-Za-z0-9]/', $password)) {
        $result['score'] += 20;
    } else {
        $result['suggestions'][] = 'ควรมีอักขระพิเศษ';
    }
    
    // ตรวจสอบความยาวเพิ่มเติม
    if (strlen($password) >= 12) {
        $result['score'] += 10;
    }
    
    if (strlen($password) >= 16) {
        $result['score'] += 10;
    }
    
    // ตรวจสอบรูปแบบที่ไม่ปลอดภัย
    $weakPatterns = [
        '/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9]+$/', // เฉพาะตัวอักษรและตัวเลข
        '/(.)\1{2,}/', // ตัวอักษรซ้ำ 3 ตัวขึ้นไป
        '/123|abc|qwe|asd|zxc/i', // รูปแบบที่เดาง่าย
    ];
    
    foreach ($weakPatterns as $pattern) {
        if (preg_match($pattern, $password)) {
            $result['score'] -= 10;
            $result['feedback'][] = 'รหัสผ่านมีรูปแบบที่ไม่ปลอดภัย';
            break;
        }
    }
    
    // คำนวณคะแนนสุดท้าย
    $result['score'] = max(0, min(100, $result['score']));
    
    return $result;
}

// บันทึก log การเข้าใช้งาน API
if (defined('LOG_API_USAGE') && LOG_API_USAGE) {
    logAvailabilityCheck(
        $_POST['type'] ?? 'unknown',
        $_POST['value'] ?? 'unknown',
        isset($isAvailable) ? $isAvailable : false
    );
}
?>