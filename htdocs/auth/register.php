<?php
/**
 * Registration Page for File Share Hub
 * หน้าสมัครสมาชิก
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

// ตรวจสอบว่าล็อกอินแล้วหรือยัง
if (isLoggedIn()) {
    header('Location: ' . getFullUrl('/dashboard/'));
    exit;
}

// ตรวจสอบว่าเปิดให้สมัครสมาชิกหรือไม่
if (!getSystemSetting('enable_registration', true)) {
    setSession('flash_message', [
        'type' => 'error',
        'message' => 'ระบบปิดการสมัครสมาชิกชั่วคราว'
    ]);
    header('Location: ' . getFullUrl('/auth/login.php'));
    exit;
}

// ตัวแปรสำหรับแสดงผล
$error = '';
$success = '';
$formData = [
    'username' => '',
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'phone' => ''
];

// ประมวลผลการสมัครสมาชิก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบ CSRF token
    if (!validateCSRFFromRequest()) {
        $error = 'Token ความปลอดภัยไม่ถูกต้อง';
    } else {
        // ตรวจสอบ rate limiting
        if (!checkRateLimit('register', 3, 300)) {
            $error = 'คำขอสมัครสมาชิกเร็วเกินไป กรุณารอสักครู่';
        } else {
            // รับข้อมูลจากฟอร์ม
            $formData['username'] = sanitizeInput($_POST['username'] ?? '');
            $formData['email'] = sanitizeInput($_POST['email'] ?? '');
            $formData['password'] = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $formData['first_name'] = sanitizeInput($_POST['first_name'] ?? '');
            $formData['last_name'] = sanitizeInput($_POST['last_name'] ?? '');
            $formData['phone'] = sanitizeInput($_POST['phone'] ?? '');
            $acceptTerms = isset($_POST['accept_terms']);
            
            // ตรวจสอบข้อมูลที่จำเป็น
            $validationErrors = [];
            
            if (empty($formData['username'])) {
                $validationErrors[] = 'กรุณากรอกชื่อผู้ใช้';
            } elseif (!preg_match(REGEX_USERNAME, $formData['username'])) {
                $validationErrors[] = 'ชื่อผู้ใช้ต้องเป็นตัวอักษร ตัวเลข _ หรือ - เท่านั้น (3-20 ตัวอักษร)';
            }
            
            if (empty($formData['email'])) {
                $validationErrors[] = 'กรุณากรอกอีเมล';
            } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
                $validationErrors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
            }
            
            if (empty($formData['password'])) {
                $validationErrors[] = 'กรุณากรอกรหัสผ่าน';
            } elseif (strlen($formData['password']) < PASSWORD_MIN_LENGTH) {
                $validationErrors[] = 'รหัสผ่านต้องมีอย่างน้อย ' . PASSWORD_MIN_LENGTH . ' ตัวอักษร';
            }
            
            if ($formData['password'] !== $confirmPassword) {
                $validationErrors[] = 'รหัสผ่านไม่ตรงกัน';
            }
            
            if (empty($formData['first_name'])) {
                $validationErrors[] = 'กรุณากรอกชื่อ';
            }
            
            if (empty($formData['last_name'])) {
                $validationErrors[] = 'กรุณากรอกนามสกุล';
            }
            
            if (!empty($formData['phone']) && !preg_match('/^[0-9\-\+\(\)\s]{8,20}$/', $formData['phone'])) {
                $validationErrors[] = 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง';
            }
            
            if (!$acceptTerms) {
                $validationErrors[] = 'กรุณายอมรับข้อกำหนดการใช้งาน';
            }
            
            // แสดง validation errors
            if (!empty($validationErrors)) {
                $error = implode('<br>', $validationErrors);
            } else {
                // ตรวจสอบว่า username หรือ email ซ้ำหรือไม่
                try {
                    $pdo = getDatabase();
                    $stmt = $pdo->prepare("SELECT username, email FROM " . TABLE_USERS . " WHERE username = ? OR email = ?");
                    $stmt->execute([$formData['username'], $formData['email']]);
                    $existingUser = $stmt->fetch();
                    
                    if ($existingUser) {
                        if ($existingUser['username'] === $formData['username']) {
                            $error = 'ชื่อผู้ใช้นี้มีการใช้งานแล้ว';
                        } else {
                            $error = 'อีเมลนี้มีการใช้งานแล้ว';
                        }
                    } else {
                        // สร้างผู้ใช้ใหม่
                        $userData = [
                            'username' => $formData['username'],
                            'email' => $formData['email'],
                            'password' => $formData['password'],
                            'first_name' => $formData['first_name'],
                            'last_name' => $formData['last_name'],
                            'phone' => $formData['phone'] ?: null,
                            'language' => getSession('language', DEFAULT_LANGUAGE),
                            'timezone' => DEFAULT_TIMEZONE
                        ];
                        
                        $result = createUser($userData);
                        
                        if ($result['success']) {
                            // บันทึก activity log
                            logActivity($result['user_id'], ACTIVITY_REGISTER, 'ผู้ใช้สมัครสมาชิกใหม่', TARGET_USER, $result['user_id']);
                            
                            // ตั้งค่า success message
                            setSession('flash_message', [
                                'type' => 'success',
                                'message' => 'สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ'
                            ]);
                            
                            // ถ้าไม่ต้องยืนยันอีเมล ให้ล็อกอินอัตโนมัติ
                            if (!getSystemSetting('require_email_verification', false)) {
                                loginUser($result['user_id'], $formData['username'], ROLE_USER);
                                header('Location: ' . getFullUrl('/dashboard/'));
                                exit;
                            } else {
                                // ส่งอีเมลยืนยัน (ถ้าเปิดใช้งาน)
                                if (MAIL_ENABLED) {
                                    // TODO: ส่งอีเมลยืนยัน
                                }
                                header('Location: ' . getFullUrl('/auth/login.php?message=verify_email'));
                                exit;
                            }
                        } else {
                            $error = $result['message'];
                        }
                    }
                } catch (Exception $e) {
                    error_log("Registration error: " . $e->getMessage());
                    $error = 'เกิดข้อผิดพลาดในการสมัครสมาชิก กรุณาลองใหม่อีกครั้ง';
                }
            }
        }
    }
}

// ตั้งค่าหน้า
$pageTitle = 'สมัครสมาชิก';
$hideContentHeader = true;

// รวมไฟล์ header
include '../includes/header.php';
?>

<!-- Registration Content -->
<div class="register-page">
    <div class="register-box">
        <!-- Logo และชื่อเว็บไซต์ -->
        <div class="register-logo">
            <a href="<?= getFullUrl('/') ?>">
                <img src="<?= getAssetUrl('logo.png', 'img') ?>" alt="<?= htmlspecialchars($siteTitle) ?>" 
                     style="height: 60px;" class="mb-2">
                <br>
                <b><?= htmlspecialchars($siteTitle) ?></b>
            </a>
        </div>

        <!-- Registration Box -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h1 class="h4 mb-0">
                    <i class="fas fa-user-plus"></i> สมัครสมาชิก
                </h1>
                <p class="text-muted mt-2">สร้างบัญชีใหม่เพื่อเริ่มใช้งาน</p>
            </div>

            <div class="card-body">
                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-ban"></i>
                    <?= $error ?>
                </div>
                <?php endif; ?>

                <!-- Success Message -->
                <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-check"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form method="post" action="" id="registerForm" data-autosave>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                    <!-- Username Field -->
                    <div class="input-group mb-3">
                        <input type="text" 
                               class="form-control" 
                               name="username" 
                               id="username"
                               placeholder="ชื่อผู้ใช้ (3-20 ตัวอักษร)" 
                               value="<?= htmlspecialchars($formData['username']) ?>"
                               required 
                               pattern="[a-zA-Z0-9_-]{3,20}"
                               title="ชื่อผู้ใช้ต้องเป็นตัวอักษร ตัวเลข _ หรือ - เท่านั้น (3-20 ตัวอักษร)"
                               autocomplete="username"
                               autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="input-group mb-3">
                        <input type="email" 
                               class="form-control" 
                               name="email" 
                               id="email"
                               placeholder="อีเมล" 
                               value="<?= htmlspecialchars($formData['email']) ?>"
                               required 
                               autocomplete="email">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Name Fields Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <input type="text" 
                                       class="form-control" 
                                       name="first_name" 
                                       id="first_name"
                                       placeholder="ชื่อ" 
                                       value="<?= htmlspecialchars($formData['first_name']) ?>"
                                       required 
                                       autocomplete="given-name">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-id-card"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <input type="text" 
                                       class="form-control" 
                                       name="last_name" 
                                       id="last_name"
                                       placeholder="นามสกุล" 
                                       value="<?= htmlspecialchars($formData['last_name']) ?>"
                                       required 
                                       autocomplete="family-name">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-id-card"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phone Field (Optional) -->
                    <div class="input-group mb-3">
                        <input type="tel" 
                               class="form-control" 
                               name="phone" 
                               id="phone"
                               placeholder="เบอร์โทรศัพท์ (ไม่บังคับ)" 
                               value="<?= htmlspecialchars($formData['phone']) ?>"
                               pattern="[0-9\-\+\(\)\s]{8,20}"
                               title="รูปแบบเบอร์โทรไม่ถูกต้อง"
                               autocomplete="tel">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-phone"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="input-group mb-3">
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               id="password"
                               placeholder="รหัสผ่าน (อย่างน้อย <?= PASSWORD_MIN_LENGTH ?> ตัวอักษร)" 
                               required 
                               minlength="<?= PASSWORD_MIN_LENGTH ?>"
                               autocomplete="new-password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="input-group mb-3">
                        <input type="password" 
                               class="form-control" 
                               name="confirm_password" 
                               id="confirm_password"
                               placeholder="ยืนยันรหัสผ่าน" 
                               required 
                               minlength="<?= PASSWORD_MIN_LENGTH ?>"
                               autocomplete="new-password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="mb-3">
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted" id="passwordStrengthText">ความแข็งแกร่งของรหัสผ่าน</small>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="icheck-primary">
                                <input type="checkbox" id="acceptTerms" name="accept_terms" required>
                                <label for="acceptTerms">
                                    ฉันยอมรับ <a href="<?= getFullUrl('/terms.php') ?>" target="_blank">ข้อกำหนดการใช้งาน</a> 
                                    และ <a href="<?= getFullUrl('/privacy.php') ?>" target="_blank">นโยบายความเป็นส่วนตัว</a>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Register Button -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block" id="registerBtn">
                                <i class="fas fa-user-plus"></i> สมัครสมาชิก
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Login Link -->
                <hr>
                <p class="text-center">
                    มีบัญชีอยู่แล้ว? 
                    <a href="<?= getFullUrl('/auth/login.php') ?>" class="text-center text-decoration-none">
                        เข้าสู่ระบบที่นี่
                    </a>
                </p>

                <!-- Guest Access -->
                <hr>
                <p class="text-center">
                    <a href="<?= getFullUrl('/') ?>" class="text-muted text-decoration-none">
                        <i class="fas fa-arrow-left"></i> กลับสู่หน้าแรก
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Additional CSS for Registration Page -->
<style>
.register-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 0;
}

.register-box {
    width: 500px;
    margin: 0 auto;
}

.register-logo a {
    color: #fff;
    text-decoration: none;
    font-size: 2rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.register-logo a:hover {
    color: #fff;
    text-decoration: none;
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
}

.card-header {
    background: rgba(255,255,255,0.1);
    border-bottom: 1px solid rgba(255,255,255,0.2);
    border-radius: 15px 15px 0 0 !important;
    color: #333;
}

.form-control {
    border-radius: 10px;
    border: 1px solid #ddd;
    padding: 12px 15px;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    padding: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.input-group-text {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 0 10px 10px 0;
}

.alert {
    border-radius: 10px;
    border: none;
}

.icheck-primary input[type="checkbox"]:checked + label::before {
    background-color: #667eea;
    border-color: #667eea;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: all 0.3s ease;
}

/* Password strength colors */
.progress-bar.bg-danger { background-color: #dc3545 !important; }
.progress-bar.bg-warning { background-color: #ffc107 !important; }
.progress-bar.bg-info { background-color: #17a2b8 !important; }
.progress-bar.bg-success { background-color: #28a745 !important; }

/* Responsive */
@media (max-width: 576px) {
    .register-box {
        width: 90%;
        margin: 20px auto;
    }
    
    .register-page {
        padding: 20px;
    }
    
    .row .col-md-6 {
        margin-bottom: 0;
    }
}

/* Loading state */
.btn-loading {
    position: relative;
    color: transparent !important;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Dark mode adjustments */
.dark-mode .card {
    background-color: rgba(52, 58, 64, 0.9);
    color: #fff;
}

.dark-mode .card-header {
    background: rgba(255,255,255,0.05);
    color: #fff;
}

.dark-mode .form-control {
    background-color: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.2);
    color: #fff;
}

.dark-mode .form-control::placeholder {
    color: rgba(255,255,255,0.7);
}

.dark-mode .input-group-text {
    background-color: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.2);
    color: #fff;
}
</style>

<!-- Page-specific JavaScript -->
<script>
$(document).ready(function() {
    // Password toggle functionality
    $('#togglePassword').on('click', function() {
        let passwordField = $('#password');
        let icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#toggleConfirmPassword').on('click', function() {
        let passwordField = $('#confirm_password');
        let icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Password strength checker
    $('#password').on('input', function() {
        let password = $(this).val();
        let strength = calculatePasswordStrength(password);
        updatePasswordStrengthDisplay(strength);
    });

    function calculatePasswordStrength(password) {
        let score = 0;
        let feedback = '';
        
        if (password.length === 0) {
            return { score: 0, feedback: 'ความแข็งแกร่งของรหัสผ่าน' };
        }
        
        // Length check
        if (password.length >= 8) score += 25;
        if (password.length >= 12) score += 25;
        
        // Character variety
        if (/[a-z]/.test(password)) score += 10;
        if (/[A-Z]/.test(password)) score += 15;
        if (/[0-9]/.test(password)) score += 15;
        if (/[^A-Za-z0-9]/.test(password)) score += 10;
        
        // Determine feedback
        if (score < 30) {
            feedback = 'รหัสผ่านอ่อนแอ';
        } else if (score < 60) {
            feedback = 'รหัสผ่านปานกลาง';
        } else if (score < 80) {
            feedback = 'รหัสผ่านดี';
        } else {
            feedback = 'รหัสผ่านแข็งแกร่ง';
        }
        
        return { score: Math.min(score, 100), feedback: feedback };
    }

    function updatePasswordStrengthDisplay(strength) {
        let progressBar = $('#passwordStrength');
        let strengthText = $('#passwordStrengthText');
        
        progressBar.css('width', strength.score + '%');
        strengthText.text(strength.feedback);
        
        // Update color based on strength
        progressBar.removeClass('bg-danger bg-warning bg-info bg-success');
        if (strength.score < 30) {
            progressBar.addClass('bg-danger');
        } else if (strength.score < 60) {
            progressBar.addClass('bg-warning');
        } else if (strength.score < 80) {
            progressBar.addClass('bg-info');
        } else {
            progressBar.addClass('bg-success');
        }
    }

    // Real-time password confirmation check
    $('#confirm_password').on('input', function() {
        let password = $('#password').val();
        let confirmPassword = $(this).val();
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        } else {
            $(this).removeClass('is-valid is-invalid');
        }
    });

    // Username availability check (with debounce)
    let usernameTimeout;
    $('#username').on('input', function() {
        let username = $(this).val().trim();
        let field = $(this);
        
        clearTimeout(usernameTimeout);
        
        if (username.length >= 3) {
            usernameTimeout = setTimeout(function() {
                checkUsernameAvailability(username, field);
            }, 500);
        } else {
            field.removeClass('is-valid is-invalid');
        }
    });

    function checkUsernameAvailability(username, field) {
        $.post(window.fileShareHub.baseUrl + '/api/check-availability.php', {
            type: 'username',
            value: username,
            csrf_token: window.fileShareHub.csrfToken
        })
        .done(function(response) {
            if (response.success) {
                if (response.available) {
                    field.removeClass('is-invalid').addClass('is-valid');
                } else {
                    field.removeClass('is-valid').addClass('is-invalid');
                }
            }
        })
        .fail(function() {
            field.removeClass('is-valid is-invalid');
        });
    }

    // Email availability check
    let emailTimeout;
    $('#email').on('input', function() {
        let email = $(this).val().trim();
        let field = $(this);
        
        clearTimeout(emailTimeout);
        
        if (email.length > 0 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            emailTimeout = setTimeout(function() {
                checkEmailAvailability(email, field);
            }, 500);
        } else {
            field.removeClass('is-valid is-invalid');
        }
    });

    function checkEmailAvailability(email, field) {
        $.post(window.fileShareHub.baseUrl + '/api/check-availability.php', {
            type: 'email',
            value: email,
            csrf_token: window.fileShareHub.csrfToken
        })
        .done(function(response) {
            if (response.success) {
                if (response.available) {
                    field.removeClass('is-invalid').addClass('is-valid');
                } else {
                    field.removeClass('is-valid').addClass('is-invalid');
                }
            }
        })
        .fail(function() {
            field.removeClass('is-valid is-invalid');
        });
    }

    // Form submission handling
    $('#registerForm').on('submit', function(e) {
        let btn = $('#registerBtn');
        let isValid = true;
        
        // Client-side validation
        let username = $('#username').val().trim();
        let email = $('#email').val().trim();
        let password = $('#password').val();
        let confirmPassword = $('#confirm_password').val();
        let firstName = $('#first_name').val().trim();
        let lastName = $('#last_name').val().trim();
        let acceptTerms = $('#acceptTerms').is(':checked');
        
        // Reset previous validation states
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate required fields
        if (!username) {
            showFieldError('#username', 'กรุณากรอกชื่อผู้ใช้');
            isValid = false;
        } else if (!/^[a-zA-Z0-9_-]{3,20}$/.test(username)) {
            showFieldError('#username', 'ชื่อผู้ใช้ไม่ถูกต้อง');
            isValid = false;
        }
        
        if (!email) {
            showFieldError('#email', 'กรุณากรอกอีเมล');
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showFieldError('#email', 'รูปแบบอีเมลไม่ถูกต้อง');
            isValid = false;
        }
        
        if (!firstName) {
            showFieldError('#first_name', 'กรุณากรอกชื่อ');
            isValid = false;
        }
        
        if (!lastName) {
            showFieldError('#last_name', 'กรุณากรอกนามสกุล');
            isValid = false;
        }
        
        if (!password) {
            showFieldError('#password', 'กรุณากรอกรหัสผ่าน');
            isValid = false;
        } else if (password.length < <?= PASSWORD_MIN_LENGTH ?>) {
            showFieldError('#password', 'รหัสผ่านต้องมีอย่างน้อย <?= PASSWORD_MIN_LENGTH ?> ตัวอักษร');
            isValid = false;
        }
        
        if (password !== confirmPassword) {
            showFieldError('#confirm_password', 'รหัสผ่านไม่ตรงกัน');
            isValid = false;
        }
        
        if (!acceptTerms) {
            window.fileShareHub.utils.showToast('กรุณายอมรับข้อกำหนดการใช้งาน', 'error');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        btn.addClass('btn-loading').prop('disabled', true);
        
        // Add timeout to prevent hanging
        setTimeout(function() {
            btn.removeClass('btn-loading').prop('disabled', false);
        }, 15000); // 15 seconds timeout
    });

    function showFieldError(fieldSelector, message) {
        let field = $(fieldSelector);
        field.addClass('is-invalid');
        field.parent().append('<div class="invalid-feedback d-block">' + message + '</div>');
    }

    // Auto-focus first field
    $('#username').focus();

    // Enter key handling
    $('.form-control').on('keypress', function(e) {
        if (e.which === 13) {
            let inputs = $('.form-control:visible');
            let currentIndex = inputs.index(this);
            
            if (currentIndex < inputs.length - 1) {
                inputs.eq(currentIndex + 1).focus();
            } else {
                $('#registerForm').submit();
            }
        }
    });

    // Phone number formatting
    $('#phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if (value.length > 0) {
            // Format Thai phone number (0X-XXXX-XXXX)
            if (value.startsWith('0') && value.length <= 10) {
                if (value.length > 6) {
                    value = value.slice(0, 2) + '-' + value.slice(2, 6) + '-' + value.slice(6);
                } else if (value.length > 2) {
                    value = value.slice(0, 2) + '-' + value.slice(2);
                }
            }
        }
        $(this).val(value);
    });

    // Clear form on page reload (security measure)
    $(window).on('beforeunload', function() {
        $('#password, #confirm_password').val('');
    });

    // Caps lock detection
    $('#password, #confirm_password, #username').on('keypress', function(e) {
        let capsLock = e.originalEvent.getModifierState('CapsLock');
        if (capsLock) {
            if (!$('.caps-lock-warning').length) {
                $(this).parent().after('<small class="caps-lock-warning text-warning mt-1 d-block"><i class="fas fa-exclamation-triangle"></i> Caps Lock เปิดอยู่</small>');
            }
        } else {
            $('.caps-lock-warning').remove();
        }
    });

    // Remove caps lock warning on blur
    $('#password, #confirm_password, #username').on('blur', function() {
        $('.caps-lock-warning').remove();
    });

    // Terms modal handling
    $('a[href*="terms.php"], a[href*="privacy.php"]').on('click', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        let title = $(this).text();
        
        // Create modal for terms/privacy (optional)
        let modal = $(`
            <div class="modal fade" id="termsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <iframe src="${url}" width="100%" height="400" frameborder="0"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(modal);
        modal.modal('show');
        
        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    });

    // Auto-save form data (for better UX)
    let autoSaveFields = ['username', 'email', 'first_name', 'last_name', 'phone'];
    
    autoSaveFields.forEach(function(field) {
        let savedValue = localStorage.getItem('register_' + field);
        if (savedValue && !$('#' + field).val()) {
            $('#' + field).val(savedValue);
        }
        
        $('#' + field).on('input', function() {
            localStorage.setItem('register_' + field, $(this).val());
        });
    });

    // Clear saved data on successful registration
    $('#registerForm').on('submit', function() {
        setTimeout(function() {
            autoSaveFields.forEach(function(field) {
                localStorage.removeItem('register_' + field);
            });
        }, 1000);
    });

    // Accessibility improvements
    $('input[required]').attr('aria-required', 'true');
    
    // Screen reader announcements
    $('#username').on('blur', function() {
        if ($(this).hasClass('is-valid')) {
            announceToScreenReader('ชื่อผู้ใช้ใช้ได้');
        } else if ($(this).hasClass('is-invalid')) {
            announceToScreenReader('ชื่อผู้ใช้ไม่ถูกต้องหรือมีผู้ใช้แล้ว');
        }
    });

    $('#email').on('blur', function() {
        if ($(this).hasClass('is-valid')) {
            announceToScreenReader('อีเมลใช้ได้');
        } else if ($(this).hasClass('is-invalid')) {
            announceToScreenReader('อีเมลไม่ถูกต้องหรือมีผู้ใช้แล้ว');
        }
    });

    function announceToScreenReader(message) {
        let announcement = $('<div class="sr-only" aria-live="polite">' + message + '</div>');
        $('body').append(announcement);
        setTimeout(function() {
            announcement.remove();
        }, 1000);
    }
});

// Global utility functions
window.fileShareHub = window.fileShareHub || {};
window.fileShareHub.register = {
    
    // Validate form before submission
    validateForm: function() {
        let isValid = true;
        let errors = [];
        
        // Get form values
        let formData = {
            username: $('#username').val().trim(),
            email: $('#email').val().trim(),
            password: $('#password').val(),
            confirmPassword: $('#confirm_password').val(),
            firstName: $('#first_name').val().trim(),
            lastName: $('#last_name').val().trim(),
            acceptTerms: $('#acceptTerms').is(':checked')
        };
        
        // Validation rules
        if (!formData.username || !/^[a-zA-Z0-9_-]{3,20}$/.test(formData.username)) {
            errors.push('ชื่อผู้ใช้ไม่ถูกต้อง');
            isValid = false;
        }
        
        if (!formData.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            errors.push('อีเมลไม่ถูกต้อง');
            isValid = false;
        }
        
        if (!formData.password || formData.password.length < <?= PASSWORD_MIN_LENGTH ?>) {
            errors.push('รหัสผ่านต้องมีอย่างน้อย <?= PASSWORD_MIN_LENGTH ?> ตัวอักษร');
            isValid = false;
        }
        
        if (formData.password !== formData.confirmPassword) {
            errors.push('รหัสผ่านไม่ตรงกัน');
            isValid = false;
        }
        
        if (!formData.firstName) {
            errors.push('กรุณากรอกชื่อ');
            isValid = false;
        }
        
        if (!formData.lastName) {
            errors.push('กรุณากรอกนามสกุล');
            isValid = false;
        }
        
        if (!formData.acceptTerms) {
            errors.push('กรุณายอมรับข้อกำหนดการใช้งาน');
            isValid = false;
        }
        
        return { isValid: isValid, errors: errors };
    },
    
    // Clear form
    clearForm: function() {
        $('#registerForm')[0].reset();
        $('.form-control').removeClass('is-valid is-invalid');
        $('.invalid-feedback').remove();
        $('#passwordStrength').css('width', '0%').removeClass('bg-danger bg-warning bg-info bg-success');
        $('#passwordStrengthText').text('ความแข็งแกร่งของรหัสผ่าน');
    }
};
</script>

<?php
// รวมไฟล์ footer
include '../includes/footer.php';
?>