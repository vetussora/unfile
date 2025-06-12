/*
Usage Examples:

// การสร้างผู้ใช้ใหม่
$result = createUser([
    'username' => 'newuser',
    'email' => 'user@example.com',
    'password' => 'password123',
    'first_name' => 'John',
    'last_name' => 'Doe'
]);

// การตรวจสอบการเข้าสู่ระบบ
$auth = authenticateUser('username', 'password');
if ($auth['success']) {
    loginUser($auth['user']['user_id'], $auth['user']['username'], $auth['user']['user_role']);
}

// การตรวจสอบไฟล์
$validation = validateFile($_FILES['upload']);
if ($validation['valid']) {
    // Process upload
}<?php
/**
 * Core Functions for File Share Hub
 * ฟังก์ชันหลักสำหรับระบบแชร์ไฟล์
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
// DUAL TABLE MANAGEMENT FUNCTIONS
// ===================================================================

/**
 * สร้างผู้ใช้ใหม่ในทั้ง 2 ตาราง (users + pure_users)
 * 
 * @param array $userData ข้อมูลผู้ใช้
 * @return array ['success' => bool, 'user_id' => int, 'message' => string]
 */
function createUser($userData) {
    try {
        $pdo = getDatabase();
        $pdo->beginTransaction();
        
        // ตรวจสอบข้อมูลที่จำเป็น
        $required = ['username', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                return ['success' => false, 'message' => "ข้อมูล {$field} จำเป็นต้องระบุ"];
            }
        }
        
        // ตรวจสอบความซ้ำ
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$userData['username'], $userData['email']]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'ชื่อผู้ใช้หรืออีเมลนี้มีการใช้งานแล้ว'];
        }
        
        // เตรียมข้อมูล
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        $plainPassword = $userData['password'];
        
        $insertData = [
            'username' => $userData['username'],
            'email' => $userData['email'],
            'password_hash' => $hashedPassword,
            'password_plain' => $plainPassword,
            'first_name' => $userData['first_name'] ?? null,
            'last_name' => $userData['last_name'] ?? null,
            'phone' => $userData['phone'] ?? null,
            'storage_limit' => $userData['storage_limit'] ?? DEFAULT_USER_STORAGE_LIMIT,
            'max_files' => $userData['max_files'] ?? DEFAULT_USER_FILE_LIMIT,
            'user_role' => $userData['user_role'] ?? ROLE_USER,
            'status' => $userData['status'] ?? USER_STATUS_ACTIVE,
            'language' => $userData['language'] ?? DEFAULT_LANGUAGE,
            'timezone' => $userData['timezone'] ?? DEFAULT_TIMEZONE
        ];
        
        // สร้างใน users table
        $usersSql = "INSERT INTO " . TABLE_USERS . " 
                    (username, email, password_hash, first_name, last_name, phone, 
                     storage_limit, max_files, user_role, status, language, timezone) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($usersSql);
        $stmt->execute([
            $insertData['username'], $insertData['email'], $insertData['password_hash'],
            $insertData['first_name'], $insertData['last_name'], $insertData['phone'],
            $insertData['storage_limit'], $insertData['max_files'], 
            $insertData['user_role'], $insertData['status'],
            $insertData['language'], $insertData['timezone']
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // สร้างใน pure_users table
        $pureUsersSql = "INSERT INTO " . TABLE_PURE_USERS . " 
                        (user_id, username, email, password_plain, first_name, last_name, phone, 
                         storage_limit, max_files, user_role, status, language, timezone) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($pureUsersSql);
        $stmt->execute([
            $userId, $insertData['username'], $insertData['email'], $insertData['password_plain'],
            $insertData['first_name'], $insertData['last_name'], $insertData['phone'],
            $insertData['storage_limit'], $insertData['max_files'],
            $insertData['user_role'], $insertData['status'],
            $insertData['language'], $insertData['timezone']
        ]);
        
        // สร้างโฟลเดอร์เริ่มต้น
        createDefaultFolders($userId);
        
        // บันทึก activity log
        logActivity($userId, ACTIVITY_REGISTER, 'ผู้ใช้สมัครสมาชิกใหม่', TARGET_USER, $userId);
        
        $pdo->commit();
        
        return [
            'success' => true, 
            'user_id' => $userId,
            'message' => 'สร้างบัญชีผู้ใช้สำเร็จ'
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error creating user: " . $e->getMessage());
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการสร้างบัญชี'];
    }
}

/**
 * อัพเดทข้อมูลผู้ใช้ในทั้ง 2 ตาราง
 * 
 * @param int $userId
 * @param array $updateData
 * @return array
 */
function updateUser($userId, $updateData) {
    try {
        $pdo = getDatabase();
        $pdo->beginTransaction();
        
        // สร้าง SQL สำหรับ users table
        $userFields = [];
        $userValues = [];
        $allowedFields = ['first_name', 'last_name', 'phone', 'language', 'timezone', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($updateData[$field])) {
                $userFields[] = "{$field} = ?";
                $userValues[] = $updateData[$field];
            }
        }
        
        // จัดการรหัสผ่าน
        if (isset($updateData['password'])) {
            $userFields[] = "password_hash = ?";
            $userValues[] = password_hash($updateData['password'], PASSWORD_DEFAULT);
        }
        
        if (!empty($userFields)) {
            $userValues[] = $userId;
            $userSql = "UPDATE " . TABLE_USERS . " SET " . implode(', ', $userFields) . ", updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
            $stmt = $pdo->prepare($userSql);
            $stmt->execute($userValues);
        }
        
        // สร้าง SQL สำหรับ pure_users table
        $pureFields = [];
        $pureValues = [];
        
        foreach ($allowedFields as $field) {
            if (isset($updateData[$field])) {
                $pureFields[] = "{$field} = ?";
                $pureValues[] = $updateData[$field];
            }
        }
        
        // จัดการรหัสผ่าน plain text
        if (isset($updateData['password'])) {
            $pureFields[] = "password_plain = ?";
            $pureValues[] = $updateData['password'];
        }
        
        if (!empty($pureFields)) {
            $pureValues[] = $userId;
            $pureSql = "UPDATE " . TABLE_PURE_USERS . " SET " . implode(', ', $pureFields) . ", updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
            $stmt = $pdo->prepare($pureSql);
            $stmt->execute($pureValues);
        }
        
        // บันทึก activity log
        logActivity($userId, ACTIVITY_ADMIN, 'อัพเดทข้อมูลผู้ใช้', TARGET_USER, $userId);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'อัพเดทข้อมูลสำเร็จ'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating user: " . $e->getMessage());
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัพเดท'];
    }
}

/**
 * ตรวจสอบความสอดคล้องระหว่าง 2 ตาราง
 * 
 * @return array
 */
function validateDualTableSync() {
    try {
        $pdo = getDatabase();
        
        $stmt = $pdo->query("
            SELECT u.user_id, u.username, u.email,
                   p.user_id as pure_user_id, p.username as pure_username, p.email as pure_email
            FROM " . TABLE_USERS . " u
            LEFT JOIN " . TABLE_PURE_USERS . " p ON u.user_id = p.user_id
            WHERE p.user_id IS NULL OR u.username != p.username OR u.email != p.email
        ");
        
        $mismatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'is_synced' => empty($mismatches),
            'mismatches' => $mismatches,
            'count' => count($mismatches)
        ];
        
    } catch (Exception $e) {
        error_log("Error validating dual table sync: " . $e->getMessage());
        return ['is_synced' => false, 'error' => $e->getMessage()];
    }
}

// ===================================================================
// USER AUTHENTICATION FUNCTIONS
// ===================================================================

/**
 * ตรวจสอบการเข้าสู่ระบบ
 * 
 * @param string $username
 * @param string $password
 * @return array
 */
function authenticateUser($username, $password) {
    try {
        $pdo = getDatabase();
        
        // หาผู้ใช้
        $stmt = $pdo->prepare("
            SELECT user_id, username, email, password_hash, user_role, status, 
                   login_attempts, locked_until, email_verified
            FROM " . TABLE_USERS . " 
            WHERE (username = ? OR email = ?) AND status = ?
        ");
        $stmt->execute([$username, $username, USER_STATUS_ACTIVE]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'ไม่พบผู้ใช้หรือบัญชีถูกระงับ'];
        }
        
        // ตรวจสอบบัญชีถูกล็อค
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return ['success' => false, 'message' => 'บัญชีถูกล็อคชั่วคราว กรุณาลองใหม่ภายหลัง'];
        }
        
        // ตรวจสอบรหัสผ่าน
        if (!password_verify($password, $user['password_hash'])) {
            // เพิ่มจำนวนครั้งที่พยายามเข้าสู่ระบบ
            $attempts = $user['login_attempts'] + 1;
            $lockUntil = null;
            
            if ($attempts >= getSystemSetting('max_login_attempts', 3)) {
                $lockoutDuration = getSystemSetting('lockout_duration', 900); // 15 minutes
                $lockUntil = date('Y-m-d H:i:s', time() + $lockoutDuration);
            }
            
            $stmt = $pdo->prepare("
                UPDATE " . TABLE_USERS . " 
                SET login_attempts = ?, locked_until = ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$attempts, $lockUntil, $user['user_id']]);
            
            return ['success' => false, 'message' => 'รหัสผ่านไม่ถูกต้อง'];
        }
        
        // รีเซ็ตจำนวนครั้งที่พยายาม
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_USERS . " 
            SET login_attempts = 0, locked_until = NULL, last_login = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        $stmt->execute([$user['user_id']]);
        
        // sync กับ pure_users
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_PURE_USERS . " 
            SET last_login = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        $stmt->execute([$user['user_id']]);
        
        // บันทึก activity log
        logActivity($user['user_id'], ACTIVITY_LOGIN, 'ผู้ใช้เข้าสู่ระบบ', TARGET_USER, $user['user_id']);
        
        return [
            'success' => true,
            'user' => [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'user_role' => $user['user_role'],
                'email_verified' => $user['email_verified']
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error authenticating user: " . $e->getMessage());
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ'];
    }
}

/**
 * รับข้อมูลผู้ใช้โดย ID
 * 
 * @param int $userId
 * @param bool $includeSensitive รวมข้อมูลที่อ่อนไหว
 * @return array|null
 */
function getUserById($userId, $includeSensitive = false) {
    try {
        $pdo = getDatabase();
        
        $fields = "user_id, username, email, first_name, last_name, profile_picture, phone, 
                   storage_used, storage_limit, file_count, max_files, email_verified, 
                   last_login, user_role, status, language, timezone, created_at, updated_at";
        
        if ($includeSensitive) {
            $fields .= ", login_attempts, locked_until, reset_password_token, reset_password_expires";
        }
        
        $stmt = $pdo->prepare("SELECT {$fields} FROM " . TABLE_USERS . " WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error getting user by ID: " . $e->getMessage());
        return null;
    }
}

// ===================================================================
// FILE MANAGEMENT FUNCTIONS
// ===================================================================

/**
 * สร้างโฟลเดอร์เริ่มต้นสำหรับผู้ใช้ใหม่
 * 
 * @param int $userId
 * @return bool
 */
function createDefaultFolders($userId) {
    try {
        $pdo = getDatabase();
        
        $userUploadPath = USER_UPLOAD_PATH . "/user_{$userId}";
        
        // สร้างโฟลเดอร์ในระบบไฟล์
        if (!is_dir($userUploadPath)) {
            mkdir($userUploadPath, 0755, true);
        }
        
        $defaultFolders = [
            [
                'name' => FOLDER_DOCUMENTS,
                'path' => $userUploadPath . '/documents/',
                'description' => 'โฟลเดอร์สำหรับเอกสาร',
                'color' => FOLDER_COLOR_BLUE,
                'icon' => FOLDER_ICON_DOCUMENT
            ],
            [
                'name' => FOLDER_IMAGES,
                'path' => $userUploadPath . '/images/',
                'description' => 'โฟลเดอร์สำหรับรูปภาพ',
                'color' => FOLDER_COLOR_GREEN,
                'icon' => FOLDER_ICON_IMAGE
            ],
            [
                'name' => FOLDER_OTHERS,
                'path' => $userUploadPath . '/others/',
                'description' => 'โฟลเดอร์สำหรับไฟล์อื่นๆ',
                'color' => FOLDER_COLOR_YELLOW,
                'icon' => FOLDER_ICON_DEFAULT
            ]
        ];
        
        foreach ($defaultFolders as $folder) {
            // สร้างโฟลเดอร์ในระบบไฟล์
            if (!is_dir($folder['path'])) {
                mkdir($folder['path'], 0755, true);
            }
            
            // สร้างในฐานข้อมูล
            $stmt = $pdo->prepare("
                INSERT INTO " . TABLE_FOLDERS . " 
                (user_id, folder_name, folder_path, folder_description, folder_color, folder_icon) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId, $folder['name'], $folder['path'],
                $folder['description'], $folder['color'], $folder['icon']
            ]);
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error creating default folders: " . $e->getMessage());
        return false;
    }
}

/**
 * ตรวจสอบความถูกต้องของไฟล์
 * 
 * @param array $file $_FILES array
 * @return array
 */
function validateFile($file) {
    $errors = [];
    
    // ตรวจสอบข้อผิดพลาดการอัพโหลด
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'ไฟล์ขนาดใหญ่เกินกว่าที่กำหนด';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'ไฟล์อัพโหลดไม่สมบูรณ์';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'ไม่มีไฟล์ถูกอัพโหลด';
                break;
            default:
                $errors[] = 'เกิดข้อผิดพลาดในการอัพโหลด';
        }
        return ['valid' => false, 'errors' => $errors];
    }
    
    // ตรวจสอบขนาดไฟล์
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'ไฟล์ขนาดใหญ่เกิน ' . formatFileSize(MAX_FILE_SIZE);
    }
    
    // ตรวจสอบชนิดไฟล์
    $allowedTypes = [];
    foreach (ALLOWED_FILE_TYPES as $category => $types) {
        $allowedTypes = array_merge($allowedTypes, $types);
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        $errors[] = 'ชนิดไฟล์ไม่ได้รับอนุญาต: ' . $extension;
    }
    
    // ตรวจสอบ MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($detectedMime, ALLOWED_MIME_TYPES)) {
        $errors[] = 'ชนิดไฟล์ไม่ถูกต้อง';
    }
    
    // ตรวจสอบชื่อไฟล์
    if (!preg_match('/^[a-zA-Z0-9._\-\s\u0E00-\u0E7F]+$/u', $file['name'])) {
        $errors[] = 'ชื่อไฟล์มีอักขระที่ไม่ได้รับอนุญาต';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'size' => $file['size'],
        'extension' => $extension,
        'mime_type' => $detectedMime
    ];
}

/**
 * สร้างชื่อไฟล์ที่ไม่ซ้ำ
 * 
 * @param string $originalName
 * @param string $directory
 * @return string
 */
function generateUniqueFilename($originalName, $directory) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    
    // ทำความสะอาดชื่อไฟล์
    $basename = preg_replace('/[^a-zA-Z0-9._\-\u0E00-\u0E7F]/u', '_', $basename);
    
    $timestamp = time();
    $random = substr(md5(uniqid()), 0, 8);
    
    $newName = $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    
    // ตรวจสอบว่าไม่ซ้ำ
    $counter = 1;
    while (file_exists($directory . '/' . $newName)) {
        $newName = $basename . '_' . $timestamp . '_' . $random . '_' . $counter . '.' . $extension;
        $counter++;
    }
    
    return $newName;
}

/**
 * คำนวณ hash ของไฟล์
 * 
 * @param string $filePath
 * @return string
 */
function calculateFileHash($filePath) {
    return hash_file(FILE_HASH_ALGORITHM, $filePath);
}

/**
 * อัพเดทการใช้พื้นที่เก็บข้อมูลของผู้ใช้
 * 
 * @param int $userId
 * @return bool
 */
function updateUserStorageUsed($userId) {
    try {
        $pdo = getDatabase();
        
        // คำนวณการใช้พื้นที่และจำนวนไฟล์
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(file_size), 0) as total_size,
                COUNT(*) as file_count
            FROM " . TABLE_FILES . " 
            WHERE user_id = ? AND is_deleted = 0
        ");
        $stmt->execute([$userId]);
        $usage = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // อัพเดทในทั้ง 2 ตาราง
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_USERS . " 
            SET storage_used = ?, file_count = ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$usage['total_size'], $usage['file_count'], $userId]);
        
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_PURE_USERS . " 
            SET storage_used = ?, file_count = ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$usage['total_size'], $usage['file_count'], $userId]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error updating user storage: " . $e->getMessage());
        return false;
    }
}

// ===================================================================
// SHARING SYSTEM FUNCTIONS
// ===================================================================

/**
 * สร้าง token สำหรับแชร์
 * 
 * @param int $length
 * @return string
 */
function generateShareToken($length = SHARE_TOKEN_LENGTH) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $token = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[random_int(0, $max)];
    }
    
    return $token;
}

/**
 * ตรวจสอบว่า token ซ้ำหรือไม่
 * 
 * @param string $token
 * @return bool
 */
function isTokenUnique($token) {
    try {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . TABLE_SHARED_LINKS . " WHERE share_token = ?");
        $stmt->execute([$token]);
        return $stmt->fetchColumn() == 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * สร้าง token ที่ไม่ซ้ำ
 * 
 * @return string
 */
function generateUniqueShareToken() {
    do {
        $token = generateShareToken();
    } while (!isTokenUnique($token));
    
    return $token;
}

// ===================================================================
// ACTIVITY LOGGING FUNCTIONS
// ===================================================================

/**
 * บันทึก activity log
 * 
 * @param int|null $userId
 * @param string $activityType
 * @param string $description
 * @param string|null $targetType
 * @param int|null $targetId
 * @param array|null $additionalData
 * @return bool
 */
function logActivity($userId, $activityType, $description, $targetType = null, $targetId = null, $additionalData = null) {
    try {
        $pdo = getDatabase();
        
        $ipAddress = getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $additionalJson = $additionalData ? json_encode($additionalData) : null;
        
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_ACTIVITY_LOGS . " 
            (user_id, activity_type, activity_description, target_type, target_id, 
             ip_address, user_agent, additional_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $userId, $activityType, $description, $targetType, $targetId,
            $ipAddress, $userAgent, $additionalJson
        ]);
        
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

/**
 * รับ Client IP Address
 * 
 * @return string
 */
function getClientIP() {
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

// ===================================================================
// UTILITY FUNCTIONS
// ===================================================================

/**
 * ฟอร์แมตขนาดไฟล์
 * 
 * @param int $bytes
 * @param int $precision
 * @return string
 */
function formatFileSize($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * ฟอร์แมตวันที่แบบไทย
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function formatThaiDate($date, $format = 'd/m/Y H:i') {
    $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    
    $timestamp = strtotime($date);
    if (!$timestamp) return $date;
    
    $day = date('j', $timestamp);
    $month = $thaiMonths[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543; // Buddhist Era
    $time = date('H:i', $timestamp);
    
    if (strpos($format, 'H:i') !== false) {
        return "{$day} {$month} {$year} {$time}";
    } else {
        return "{$day} {$month} {$year}";
    }
}

/**
 * สร้าง breadcrumb navigation
 * 
 * @param array $items [['title' => 'หน้าแรก', 'url' => '/'], ...]
 * @return string HTML
 */
function generateBreadcrumb($items) {
    if (empty($items)) return '';
    
    $html = '<ol class="breadcrumb">';
    $total = count($items);
    
    foreach ($items as $index => $item) {
        if ($index === $total - 1) {
            // Last item - active
            $html .= '<li class="breadcrumb-item active">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            // Link item
            $html .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }
    
    $html .= '</ol>';
    return $html;
}

/**
 * ทำความสะอาด input
 * 
 * @param mixed $input
 * @return mixed
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * ตรวจสอบ AJAX request
 * 
 * @return bool
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * ส่ง JSON response
 * 
 * @param array $data
 * @param int $httpCode
 */
function sendJsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * ส่ง error response
 * 
 * @param string $message
 * @param int $code
 * @param int $httpCode
 */
function sendError($message, $code = 0, $httpCode = 400) {
    sendJsonResponse([
        'success' => false,
        'error' => $message,
        'code' => $code
    ], $httpCode);
}

/**
 * ส่ง success response
 * 
 * @param mixed $data
 * @param string $message
 */
function sendSuccess($data = null, $message = 'สำเร็จ') {
    $response = [
        'success' => true,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendJsonResponse($response);
}

// ===================================================================
// SECURITY FUNCTIONS
// ===================================================================

/**
 * ตรวจสอบสิทธิ์การเข้าถึง
 * 
 * @param string $requiredRole
 * @param int|null $resourceUserId ถ้าต้องการตรวจสอบเจ้าของ resource
 * @return bool
 */
function checkAccess($requiredRole = ROLE_USER, $resourceUserId = null) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $currentUserId = getCurrentUserId();
    $currentRole = getCurrentUserRole();
    
    // ตรวจสอบ role
    $roleHierarchy = [
        ROLE_USER => 1,
        ROLE_MODERATOR => 2,
        ROLE_ADMIN => 3
    ];
    
    $currentLevel = $roleHierarchy[$currentRole] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
    
    if ($currentLevel < $requiredLevel) {
        return false;
    }
    
    // ตรวจสอบเจ้าของ resource (ถ้าระบุ)
    if ($resourceUserId !== null) {
        return $currentUserId == $resourceUserId || $currentRole === ROLE_ADMIN;
    }
    
    return true;
}

/**
 * บังคับให้เข้าสู่ระบบ
 * 
 * @param string $redirectUrl
 */
function requireLogin($redirectUrl = '/auth/login.php') {
    if (!isLoggedIn()) {
        if (isAjaxRequest()) {
            sendError('กรุณาเข้าสู่ระบบ', ERR_SESSION_EXPIRED, HTTP_UNAUTHORIZED);
        } else {
            header('Location: ' . getFullUrl($redirectUrl));
            exit;
        }
    }
}

/**
 * บังคับให้มีสิทธิ์เฉพาะ
 * 
 * @param string $requiredRole
 * @param int|null $resourceUserId
 */
function requireAccess($requiredRole, $resourceUserId = null) {
    requireLogin();
    
    if (!checkAccess($requiredRole, $resourceUserId)) {
        if (isAjaxRequest()) {
            sendError('ไม่มีสิทธิ์เข้าถึง', ERR_PERMISSION_DENIED, HTTP_FORBIDDEN);
        } else {
            header('Location: ' . getFullUrl('/403.php'));
            exit;
        }
    }
}

/**
 * ตรวจสอบ rate limiting
 * 
 * @param string $action
 * @param int $limit
 * @param int $window seconds
 * @param string|null $identifier
 * @return bool
 */
function checkRateLimit($action, $limit, $window, $identifier = null) {
    if (!$identifier) {
        $identifier = getClientIP();
    }
    
    $key = "rate_limit_{$action}_{$identifier}";
    $cacheFile = CACHE_PATH . '/rate_limits/' . md5($key) . '.tmp';
    
    // สร้างโฟลเดอร์ถ้าไม่มี
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $now = time();
    $requests = [];
    
    // อ่านข้อมูลเก่า
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data && isset($data['requests'])) {
            $requests = $data['requests'];
        }
    }
    
    // ลบข้อมูลที่หมดอายุ
    $requests = array_filter($requests, function($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });
    
    // ตรวจสอบ limit
    if (count($requests) >= $limit) {
        return false;
    }
    
    // เพิ่ม request ใหม่
    $requests[] = $now;
    
    // บันทึกข้อมูล
    file_put_contents($cacheFile, json_encode(['requests' => $requests]));
    
    return true;
}

// ===================================================================
// FILE SYSTEM FUNCTIONS
// ===================================================================

/**
 * ลบไฟล์แบบ Soft Delete (ย้ายไปโฟลเดอร์ลับสำหรับ Admin)
 * 
 * @param int $fileId
 * @param int $userId (ผู้ที่ขอลบ)
 * @return array
 */
function softDeleteFile($fileId, $userId) {
    try {
        $pdo = getDatabase();
        $pdo->beginTransaction();
        
        // รับข้อมูลไฟล์
        $stmt = $pdo->prepare("
            SELECT f.*, u.username 
            FROM " . TABLE_FILES . " f
            LEFT JOIN " . TABLE_USERS . " u ON f.user_id = u.user_id
            WHERE f.file_id = ? AND f.is_deleted = 0
        ");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file) {
            return ['success' => false, 'message' => 'ไม่พบไฟล์หรือไฟล์ถูกลบแล้ว'];
        }
        
        // ตรวจสอบสิทธิ์ (เจ้าของไฟล์หรือ Admin)
        if (!checkAccess(ROLE_USER, $file['user_id'])) {
            return ['success' => false, 'message' => 'ไม่มีสิทธิ์ลบไฟล์นี้'];
        }
        
        // สร้างโฟลเดอร์ลับสำหรับเก็บไฟล์ที่ถูกลบ
        $deletedDir = UPLOAD_PATH . '/deleted/user_' . $file['user_id'];
        if (!is_dir($deletedDir)) {
            mkdir($deletedDir, 0755, true);
        }
        
        // สร้างชื่อไฟล์ใหม่ในโฟลเดอร์ลับ (เพิ่ม timestamp เพื่อไม่ให้ซ้ำ)
        $extension = pathinfo($file['stored_filename'], PATHINFO_EXTENSION);
        $basename = pathinfo($file['stored_filename'], PATHINFO_FILENAME);
        $deletedFilename = $basename . '_deleted_' . time() . '.' . $extension;
        $newPath = $deletedDir . '/' . $deletedFilename;
        
        // ย้ายไฟล์จากโฟลเดอร์ปกติไปยังโฟลเดอร์ลับ
        if (file_exists($file['file_path'])) {
            if (!rename($file['file_path'], $newPath)) {
                return ['success' => false, 'message' => 'ไม่สามารถย้ายไฟล์ได้'];
            }
        }
        
        // อัพเดทสถานะในฐานข้อมูล
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_FILES . " 
            SET is_deleted = 1, 
                deleted_at = CURRENT_TIMESTAMP,
                file_path = ?
            WHERE file_id = ?
        ");
        $stmt->execute([$newPath, $fileId]);
        
        // อัพเดทการใช้พื้นที่ของผู้ใช้
        updateUserStorageUsed($file['user_id']);
        
        // ลบ shared links ที่เกี่ยวข้อง (ปิดการแชร์)
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_SHARED_LINKS . " 
            SET is_active = 0 
            WHERE file_id = ?
        ");
        $stmt->execute([$fileId]);
        
        // บันทึก activity log
        $isOwner = ($userId == $file['user_id']);
        $activityDesc = $isOwner 
            ? "ผู้ใช้ลบไฟล์: {$file['original_filename']}" 
            : "Admin ลบไฟล์: {$file['original_filename']} ของผู้ใช้ {$file['username']}";
            
        logActivity(
            $userId, 
            ACTIVITY_DELETE, 
            $activityDesc, 
            TARGET_FILE, 
            $fileId,
            [
                'original_filename' => $file['original_filename'],
                'file_owner' => $file['username'],
                'deleted_by_owner' => $isOwner,
                'moved_to_quarantine' => $newPath
            ]
        );
        
        $pdo->commit();
        
        return [
            'success' => true, 
            'message' => 'ลบไฟล์สำเร็จ ไฟล์ถูกย้ายไปยังพื้นที่รอการตรวจสอบ'
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error soft deleting file: " . $e->getMessage());
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบไฟล์'];
    }
}

/**
 * กู้คืนไฟล์จากโฟลเดอร์ลับ (เฉพาะ Admin)
 * 
 * @param int $fileId
 * @param int $adminId
 * @return array
 */
function restoreDeletedFile($fileId, $adminId) {
    try {
        // ตรวจสอบสิทธิ์ Admin
        requireAccess(ROLE_ADMIN);
        
        $pdo = getDatabase();
        $pdo->beginTransaction();
        
        // รับข้อมูลไฟล์ที่ถูกลบ
        $stmt = $pdo->prepare("
            SELECT f.*, u.username 
            FROM " . TABLE_FILES . " f
            LEFT JOIN " . TABLE_USERS . " u ON f.user_id = u.user_id
            WHERE f.file_id = ? AND f.is_deleted = 1
        ");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file) {
            return ['success' => false, 'message' => 'ไม่พบไฟล์ที่ถูกลบ'];
        }
        
        // สร้างเส้นทางกลับคืน
        $userDir = USER_UPLOAD_PATH . '/user_' . $file['user_id'];
        $folderPath = $userDir . '/others'; // ย้ายไปโฟลเดอร์ Others
        
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        
        // สร้างชื่อไฟล์ใหม่ (ลบ _deleted_ ออก)
        $originalStored = str_replace('_deleted_' . substr($file['stored_filename'], -14, 10), '', basename($file['file_path']));
        $restoredPath = $folderPath . '/' . $originalStored;
        
        // ตรวจสอบว่าชื่อไฟล์ซ้ำหรือไม่
        if (file_exists($restoredPath)) {
            $extension = pathinfo($originalStored, PATHINFO_EXTENSION);
            $basename = pathinfo($originalStored, PATHINFO_FILENAME);
            $originalStored = $basename . '_restored_' . time() . '.' . $extension;
            $restoredPath = $folderPath . '/' . $originalStored;
        }
        
        // ย้ายไฟล์กลับ
        if (file_exists($file['file_path'])) {
            if (!rename($file['file_path'], $restoredPath)) {
                return ['success' => false, 'message' => 'ไม่สามารถย้ายไฟล์กลับได้'];
            }
        }
        
        // หาโฟลเดอร์ Others ของผู้ใช้
        $stmt = $pdo->prepare("
            SELECT folder_id 
            FROM " . TABLE_FOLDERS . " 
            WHERE user_id = ? AND folder_name = ? AND is_deleted = 0 
            LIMIT 1
        ");
        $stmt->execute([$file['user_id'], 'Others']);
        $folder = $stmt->fetch();
        $folderId = $folder ? $folder['folder_id'] : null;
        
        // อัพเดทสถานะในฐานข้อมูล
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_FILES . " 
            SET is_deleted = 0, 
                deleted_at = NULL,
                file_path = ?,
                folder_id = ?,
                stored_filename = ?
            WHERE file_id = ?
        ");
        $stmt->execute([$restoredPath, $folderId, $originalStored, $fileId]);
        
        // อัพเดทการใช้พื้นที่ของผู้ใช้
        updateUserStorageUsed($file['user_id']);
        
        // บันทึก activity log
        logActivity(
            $adminId, 
            ACTIVITY_ADMIN, 
            "Admin กู้คืนไฟล์: {$file['original_filename']} ของผู้ใช้ {$file['username']}", 
            TARGET_FILE, 
            $fileId,
            [
                'original_filename' => $file['original_filename'],
                'file_owner' => $file['username'],
                'restored_to' => $restoredPath
            ]
        );
        
        $pdo->commit();
        
        return [
            'success' => true, 
            'message' => 'กู้คืนไฟล์สำเร็จ ไฟล์ถูกย้ายกลับไปยังโฟลเดอร์ Others'
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error restoring file: " . $e->getMessage());
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการกู้คืนไฟล์'];
    }
}

/**
 * ลบไฟล์จริงๆ ออกจากระบบ (เฉพาะ Admin)
 * 
 * @param int $fileId
 * @param int $adminId
 * @return array
 */
function permanentDeleteFile($fileId, $adminId) {
    try {
        // ตรวจสอบสิทธิ์ Admin
        requireAccess(ROLE_ADMIN);
        
        $pdo = getDatabase();
        $pdo->beginTransaction();
        
        // รับข้อมูลไฟล์
        $stmt = $pdo->prepare("
            SELECT f.*, u.username 
            FROM " . TABLE_FILES . " f
            LEFT JOIN " . TABLE_USERS . " u ON f.user_id = u.user_id
            WHERE f.file_id = ? AND f.is_deleted = 1
        ");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file) {
            return ['success' => false, 'message' => 'ไม่พบไฟล์ที่ถูกลบ'];
        }
        
        // ลบไฟล์จริงจากระบบไฟล์
        if (file_exists($file['file_path'])) {
            if (!safeDeleteFileFromDisk($file['file_path'])) {
                return ['success' => false, 'message' => 'ไม่สามารถลบไฟล์จากระบบได้'];
            }
        }
        
        // ลบ shared links ที่เกี่ยวข้อง
        $stmt = $pdo->prepare("DELETE FROM " . TABLE_SHARED_LINKS . " WHERE file_id = ?");
        $stmt->execute([$fileId]);
        
        // ลบ share access logs ที่เกี่ยวข้อง
        $stmt = $pdo->prepare("
            DELETE FROM " . TABLE_SHARE_ACCESS_LOGS . " 
            WHERE share_id IN (
                SELECT share_id FROM " . TABLE_SHARED_LINKS . " WHERE file_id = ?
            )
        ");
        $stmt->execute([$fileId]);
        
        // ลบ activity logs ที่เกี่ยวข้อง (เก็บไว้บางส่วนเป็นประวัติ)
        // ไม่ลบ activity logs เพื่อเก็บ audit trail
        
        // ลบข้อมูลไฟล์จากฐานข้อมูล
        $stmt = $pdo->prepare("DELETE FROM " . TABLE_FILES . " WHERE file_id = ?");
        $stmt->execute([$fileId]);
        
        // อัพเดทการใช้พื้นที่ของผู้ใช้
        updateUserStorageUsed($file['user_id']);
        
        // บันทึก activity log
        logActivity(
            $adminId, 
            ACTIVITY_ADMIN, 
            "Admin ลบไฟล์ถาวร: {$file['original_filename']} ของผู้ใช้ {$file['username']}", 
            TARGET_FILE, 
            $fileId,
            [
                'original_filename' => $file['original_filename'],
                'file_owner' => $file['username'],
                'permanent_delete' => true,
                'file_size' => $file['file_size']
            ]
        );
        
        $pdo->commit();
        
        return [
            'success' => true, 
            'message' => 'ลบไฟล์ถาวรสำเร็จ ไฟล์ถูกลบออกจากระบบแล้ว'
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error permanently deleting file: " . $e->getMessage());
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบไฟล์ถาวร'];
    }
}

/**
 * รับรายการไฟล์ที่ถูกลบ (เฉพาะ Admin)
 * 
 * @param int $page
 * @param int $limit
 * @param int|null $userId กรองตามผู้ใช้
 * @return array
 */
function getDeletedFiles($page = 1, $limit = 50, $userId = null) {
    try {
        requireAccess(ROLE_ADMIN);
        
        $pdo = getDatabase();
        $offset = ($page - 1) * $limit;
        
        $where = "f.is_deleted = 1";
        $params = [];
        
        if ($userId) {
            $where .= " AND f.user_id = ?";
            $params[] = $userId;
        }
        
        // รับรายการไฟล์ที่ถูกลบ
        $stmt = $pdo->prepare("
            SELECT f.*, u.username, u.email,
                   DATEDIFF(CURRENT_DATE, DATE(f.deleted_at)) as days_deleted
            FROM " . TABLE_FILES . " f
            LEFT JOIN " . TABLE_USERS . " u ON f.user_id = u.user_id
            WHERE {$where}
            ORDER BY f.deleted_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // นับจำนวนทั้งหมด
        $countParams = array_slice($params, 0, -2); // ลบ limit และ offset
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM " . TABLE_FILES . " f
            LEFT JOIN " . TABLE_USERS . " u ON f.user_id = u.user_id
            WHERE {$where}
        ");
        $stmt->execute($countParams);
        $total = $stmt->fetchColumn();
        
        return [
            'success' => true,
            'files' => $files,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_files' => $total,
                'per_page' => $limit
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error getting deleted files: " . $e->getMessage());
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล'];
    }
}

/**
 * ลบไฟล์จากดิสก์อย่างปลอดภัย
 * 
 * @param string $filePath
 * @return bool
 */
function safeDeleteFileFromDisk($filePath) {
    if (!file_exists($filePath)) {
        return true;
    }
    
    // ตรวจสอบว่าไฟล์อยู่ในโฟลเดอร์ที่อนุญาต
    $allowedPaths = [UPLOAD_PATH, CACHE_PATH, LOGS_PATH];
    $realPath = realpath($filePath);
    $isAllowed = false;
    
    foreach ($allowedPaths as $allowedPath) {
        if (strpos($realPath, realpath($allowedPath)) === 0) {
            $isAllowed = true;
            break;
        }
    }
    
    if (!$isAllowed) {
        error_log("Attempted to delete file outside allowed directories: " . $filePath);
        return false;
    }
    
    return unlink($filePath);
}

/**
 * ทำความสะอาดไฟล์ที่ถูกลบเก่าๆ อัตโนมัติ
 * 
 * @param int $daysOld ลบไฟล์ที่ถูกลบมานานกว่า X วัน
 * @return array
 */
function cleanupOldDeletedFiles($daysOld = 30) {
    try {
        requireAccess(ROLE_ADMIN);
        
        $pdo = getDatabase();
        
        // หาไฟล์ที่ถูกลบมานานแล้ว
        $stmt = $pdo->prepare("
            SELECT file_id, file_path, original_filename 
            FROM " . TABLE_FILES . " 
            WHERE is_deleted = 1 
            AND deleted_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$daysOld]);
        $oldFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $deletedCount = 0;
        $errors = [];
        
        foreach ($oldFiles as $file) {
            $result = permanentDeleteFile($file['file_id'], getCurrentUserId());
            if ($result['success']) {
                $deletedCount++;
            } else {
                $errors[] = $file['original_filename'] . ': ' . $result['message'];
            }
        }
        
        // บันทึก activity log
        if ($deletedCount > 0) {
            logActivity(
                getCurrentUserId(),
                ACTIVITY_ADMIN,
                "ทำความสะอาดไฟล์เก่าอัตโนมัติ: ลบ {$deletedCount} ไฟล์",
                TARGET_SYSTEM,
                null,
                ['deleted_files_count' => $deletedCount, 'errors' => $errors]
            );
        }
        
        return [
            'success' => true,
            'deleted_count' => $deletedCount,
            'errors' => $errors,
            'message' => "ทำความสะอาดไฟล์เสร็จสิ้น ลบ {$deletedCount} ไฟล์"
        ];
        
    } catch (Exception $e) {
        error_log("Error cleaning up old deleted files: " . $e->getMessage());
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการทำความสะอาด'];
    }
}

/**
 * ทำความสะอาดไฟล์ชั่วคราว
 * 
 * @param int $olderThanMinutes
 * @return int จำนวนไฟล์ที่ลบ
 */
function cleanupTempFiles($olderThanMinutes = 60) {
    $tempDir = UPLOAD_PATH . '/temp';
    if (!is_dir($tempDir)) {
        return 0;
    }
    
    $cutoffTime = time() - ($olderThanMinutes * 60);
    $deletedCount = 0;
    
    $files = glob($tempDir . '/*');
    foreach ($files as $file) {
        if (is_file($file) && filemtime($file) < $cutoffTime) {
            if (safeDeleteFileFromDisk($file)) {
                $deletedCount++;
            }
        }
    }
    
    return $deletedCount;
}

/**
 * รับข้อมูลการใช้พื้นที่ดิสก์
 * 
 * @return array
 */
function getDiskUsage() {
    $uploadPath = UPLOAD_PATH;
    $totalSize = 0;
    $fileCount = 0;
    
    if (is_dir($uploadPath)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
                $fileCount++;
            }
        }
    }
    
    $freeSpace = disk_free_space($uploadPath);
    $totalSpace = disk_total_space($uploadPath);
    
    return [
        'used_space' => $totalSize,
        'file_count' => $fileCount,
        'free_space' => $freeSpace,
        'total_space' => $totalSpace,
        'usage_percent' => $totalSpace > 0 ? round(($totalSize / $totalSpace) * 100, 2) : 0
    ];
}

// ===================================================================
// LANGUAGE FUNCTIONS
// ===================================================================

/**
 * รับข้อความภาษาไทย
 * 
 * @param string $key
 * @param array $params
 * @return string
 */
function t($key, $params = []) {
    static $translations = null;
    
    if ($translations === null) {
        $langFile = LANG_PATH . '/' . (getSession('language', DEFAULT_LANGUAGE)) . '.php';
        if (file_exists($langFile)) {
            $translations = include $langFile;
        } else {
            $translations = [];
        }
    }
    
    $text = isset($translations[$key]) ? $translations[$key] : $key;
    
    // แทนที่ parameters
    foreach ($params as $param => $value) {
        $text = str_replace('{' . $param . '}', $value, $text);
    }
    
    return $text;
}

/**
 * ตั้งค่าภาษา
 * 
 * @param string $language
 */
function setLanguage($language) {
    if (in_array($language, SUPPORTED_LANGUAGES)) {
        setSession('language', $language);
        
        // อัพเดทในฐานข้อมูลถ้าล็อกอินแล้ว
        if (isLoggedIn()) {
            updateUser(getCurrentUserId(), ['language' => $language]);
        }
    }
}

// ===================================================================
// EMAIL FUNCTIONS (For future use)
// ===================================================================

/**
 * ส่งอีเมล (สำหรับใช้ในอนาคต)
 * 
 * @param string $to
 * @param string $subject
 * @param string $body
 * @param bool $isHtml
 * @return bool
 */
function sendEmail($to, $subject, $body, $isHtml = true) {
    if (!MAIL_ENABLED) {
        error_log("Email not sent - mail disabled: $to - $subject");
        return false;
    }
    
    // TODO: Implement SMTP email sending
    // For now, just log the email
    error_log("Email would be sent to: $to, Subject: $subject");
    return true;
}

// ===================================================================
// CACHE FUNCTIONS
// ===================================================================

/**
 * รับข้อมูลจาก cache
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getCache($key, $default = null) {
    if (!CACHE_ENABLED) {
        return $default;
    }
    
    $cacheFile = CACHE_PATH . '/' . md5($key) . '.cache';
    
    if (!file_exists($cacheFile)) {
        return $default;
    }
    
    $data = json_decode(file_get_contents($cacheFile), true);
    
    if (!$data || !isset($data['expires']) || $data['expires'] < time()) {
        safeDeleteFile($cacheFile);
        return $default;
    }
    
    return $data['value'];
}

/**
 * บันทึกข้อมูลลง cache
 * 
 * @param string $key
 * @param mixed $value
 * @param int $ttl seconds
 * @return bool
 */
function setCache($key, $value, $ttl = CACHE_LIFETIME) {
    if (!CACHE_ENABLED) {
        return false;
    }
    
    $cacheFile = CACHE_PATH . '/' . md5($key) . '.cache';
    
    // สร้างโฟลเดอร์ถ้าไม่มี
    if (!is_dir(CACHE_PATH)) {
        mkdir(CACHE_PATH, 0755, true);
    }
    
    $data = [
        'value' => $value,
        'expires' => time() + $ttl
    ];
    
    return file_put_contents($cacheFile, json_encode($data)) !== false;
}

/**
 * ลบ cache
 * 
 * @param string $key
 * @return bool
 */
function deleteCache($key) {
    $cacheFile = CACHE_PATH . '/' . md5($key) . '.cache';
    return safeDeleteFileFromDisk($cacheFile);
}

/**
 * ทำความสะอาด cache ที่หมดอายุ
 * 
 * @return int จำนวนไฟล์ที่ลบ
 */
function cleanupExpiredCache() {
    if (!is_dir(CACHE_PATH)) {
        return 0;
    }
    
    $deletedCount = 0;
    $files = glob(CACHE_PATH . '/*.cache');
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if (!$data || !isset($data['expires']) || $data['expires'] < time()) {
            if (safeDeleteFileFromDisk($file)) {
                $deletedCount++;
            }
        }
    }
    
    return $deletedCount;
}

// ===================================================================
// DEBUG FUNCTIONS
// ===================================================================

/**
 * Debug dump (เฉพาะ debug mode)
 * 
 * @param mixed $data
 * @param string $label
 */
function dd($data, $label = '') {
    if (!DEBUG_MODE) {
        return;
    }
    
    echo '<pre style="background:#f5f5f5;padding:10px;border:1px solid #ccc;margin:10px 0;">';
    if ($label) {
        echo '<strong>' . htmlspecialchars($label) . ':</strong><br>';
    }
    print_r($data);
    echo '</pre>';
}

/**
 * Debug log
 * 
 * @param mixed $data
 * @param string $label
 */
function debugLog($data, $label = '') {
    if (!DEBUG_MODE) {
        return;
    }
    
    $message = $label ? $label . ': ' : '';
    $message .= is_string($data) ? $data : print_r($data, true);
    
    error_log("[DEBUG] " . $message);
}

// ===================================================================
// INITIALIZATION
// ===================================================================

/**
 * ตรวจสอบและสร้างโฟลเดอร์ที่จำเป็น
 */
function ensureRequiredDirectories() {
    $directories = [
        UPLOAD_PATH,
        USER_UPLOAD_PATH,
        UPLOAD_PATH . '/deleted', // โฟลเดอร์สำหรับไฟล์ที่ถูกลบ
        LOGS_PATH,
        CACHE_PATH,
        CACHE_PATH . '/rate_limits'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            
            // สร้าง .htaccess เพื่อป้องกัน
            if (in_array($dir, [UPLOAD_PATH, UPLOAD_PATH . '/deleted', LOGS_PATH, CACHE_PATH])) {
                $htaccess = $dir . '/.htaccess';
                if (!file_exists($htaccess)) {
                    file_put_contents($htaccess, "deny from all\n");
                }
            }
        }
    }
}

/**
 * ทำความสะอาดระบบอัตโนมัติ
 */
function performSystemCleanup() {
    if (CLEANUP_METHOD === 'auto' && CLEANUP_ENABLED) {
        // ทำความสะอาดไฟล์ชั่วคราว
        cleanupTempFiles();
        
        // ทำความสะอาด cache ที่หมดอายุ
        cleanupExpiredCache();
        
        // ทำความสะอาด session เก่า (ถ้าใช้ database sessions)
        if (defined('USE_DATABASE_SESSIONS') && USE_DATABASE_SESSIONS) {
            try {
                $pdo = getDatabase();
                $expiry = date('Y-m-d H:i:s', time() - SESSION_LIFETIME);
                $stmt = $pdo->prepare("DELETE FROM " . TABLE_SESSIONS . " WHERE last_activity < ?");
                $stmt->execute([$expiry]);
            } catch (Exception $e) {
                error_log("Error cleaning up sessions: " . $e->getMessage());
            }
        }
    }
}

// เรียกใช้งาน initialization
ensureRequiredDirectories();

// ทำความสะอาดระบบ (10% chance เพื่อไม่ให้ทำทุกครั้ง)
if (rand(1, 100) <= 10) {
    performSystemCleanup();
}

// ===================================================================
// END OF FUNCTIONS
// ===================================================================

if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    error_log("Functions.php loaded successfully at " . date('Y-m-d H:i:s'));
}

/*
Usage Examples:

// การสร้างผู้ใช้ใหม่
$result = createUser([
    'username' => 'newuser',
    'email' => 'user@example.com',
    'password' => 'password123',
    'first_name' => 'John',
    'last_name' => 'Doe'
]);

// การตรวจสอบการเข้าสู่ระบบ
$auth = authenticateUser('username', 'password');
if ($auth['success']) {
    loginUser($auth['user']['user_id'], $auth['user']['username'], $auth['user']['user_role']);
}

// การตรวจสอบไฟล์
$validation = validateFile($_FILES['upload']);
if ($validation['valid']) {
    // Process upload
}

// การบันทึก activity
logActivity(getCurrentUserId(), ACTIVITY_UPLOAD, 'อัพโหลดไฟล์ใหม่', TARGET_FILE, $fileId);

// การตรวจสอบสิทธิ์
requireAccess(ROLE_ADMIN); // เฉพาะ admin
requireAccess(ROLE_USER, $fileUserId); // เจ้าของไฟล์หรือ admin

// การใช้ cache
setCache('user_' . $userId, $userData, 3600);
$userData = getCache('user_' . $userId);

// การส่ง JSON response
sendSuccess(['file_id' => 123], 'อัพโหลดสำเร็จ');
sendError('ไฟล์ขนาดใหญ่เกินไป', ERR_FILE_TOO_LARGE);

// การแปลภาษา
echo t('welcome_message', ['name' => $username]);

// การตรวจสอบ rate limiting
if (!checkRateLimit('upload', 10, 300)) {
    sendError('อัพโหลดเร็วเกินไป กรุณารอสักครู่', ERR_RATE_LIMIT_EXCEEDED);
}
*/
?>