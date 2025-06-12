<?php
/**
 * Login Page for File Share Hub
 * ระบบเข้าสู่ระบบ
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

// ตัวแปรสำหรับแสดงผล
$error = '';
$success = '';
$username = '';
$rememberChecked = '';

// ประมวลผลการล็อกอิน
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบ CSRF token
    if (!validateCSRFFromRequest()) {
        $error = t('csrf_token_invalid');
    } else {
        // ตรวจสอบ rate limiting
        if (!checkRateLimit(RATE_LIMIT_LOGIN, 5, 300)) {
            $error = t('rate_limit_exceeded');
        } else {
            // รับข้อมูลจากฟอร์ม
            $username = sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            // เก็บค่าสำหรับแสดงผลใหม่
            $rememberChecked = $remember ? 'checked' : '';
            
            // ตรวจสอบข้อมูลที่จำเป็น
            if (empty($username) || empty($password)) {
                $error = t('please_fill_required_fields');
            } else {
                // ตรวจสอบการเข้าสู่ระบบ
                $authResult = authenticateUser($username, $password);
                
                if ($authResult['success']) {
                    $user = $authResult['user'];
                    
                    // ตรวจสอบสถานะบัญชี
                    if (!$user['email_verified'] && getSystemSetting('require_email_verification', false)) {
                        $error = t('email_not_verified');
                    } else {
                        // เข้าสู่ระบบสำเร็จ
                        loginUser($user['user_id'], $user['username'], $user['user_role']);
                        
                        // ตั้งค่า remember me
                        if ($remember) {
                            $rememberToken = generateShareToken(64);
                            setcookie('remember_token', $rememberToken, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 วัน
                            
                            // บันทึก remember token ในฐานข้อมูล (optional)
                            // updateUser($user['user_id'], ['remember_token' => hash('sha256', $rememberToken)]);
                        }
                        
                        // กำหนดหน้าปลายทาง
                        $redirectUrl = $_GET['redirect'] ?? '/dashboard/';
                        $redirectUrl = filter_var($redirectUrl, FILTER_SANITIZE_URL);
                        
                        // ตรวจสอบว่า URL ปลอดภัย
                        if (!str_starts_with($redirectUrl, '/') || str_contains($redirectUrl, '//')) {
                            $redirectUrl = '/dashboard/';
                        }
                        
                        // แสดงข้อความสำเร็จ
                        setSession('flash_message', [
                            'type' => 'success',
                            'message' => t('login_successful')
                        ]);
                        
                        // เปลี่ยนเส้นทาง
                        header('Location: ' . getFullUrl($redirectUrl));
                        exit;
                    }
                } else {
                    $error = $authResult['message'];
                }
            }
        }
    }
}

// ตั้งค่าหน้า
$pageTitle = t('login');
$hideContentHeader = true;

// รวมไฟล์ header
include '../includes/header.php';
?>

<!-- Login Content -->
<div class="login-page">
    <div class="login-box">
        <!-- Logo และชื่อเว็บไซต์ -->
        <div class="login-logo">
            <a href="<?= getFullUrl('/') ?>">
                <img src="<?= getAssetUrl('logo.png', 'img') ?>" alt="<?= htmlspecialchars($siteTitle) ?>" 
                     style="height: 60px;" class="mb-2">
                <br>
                <b><?= htmlspecialchars($siteTitle) ?></b>
            </a>
        </div>

        <!-- Login Box -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h1 class="h4 mb-0">
                    <i class="fas fa-sign-in-alt"></i> <?= t('login') ?>
                </h1>
                <p class="text-muted mt-2"><?= t('please_login_to_continue') ?></p>
            </div>

            <div class="card-body">
                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-ban"></i>
                    <?= htmlspecialchars($error) ?>
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

                <!-- Login Form -->
                <form method="post" action="" id="loginForm" data-autosave>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                    <!-- Username Field -->
                    <div class="input-group mb-3">
                        <input type="text" 
                               class="form-control" 
                               name="username" 
                               id="username"
                               placeholder="<?= t('enter_username_email') ?>" 
                               value="<?= htmlspecialchars($username) ?>"
                               required 
                               autocomplete="username"
                               autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="input-group mb-3">
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               id="password"
                               placeholder="<?= t('enter_password') ?>" 
                               required 
                               autocomplete="current-password">
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

                    <!-- Remember Me & Forgot Password -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" name="remember" <?= $rememberChecked ?>>
                                <label for="remember">
                                    <?= t('remember_me') ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-6 text-right">
                            <a href="<?= getFullUrl('/auth/forgot-password.php') ?>" class="text-decoration-none">
                                <?= t('forgot_password') ?>
                            </a>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                                <i class="fas fa-sign-in-alt"></i> <?= t('login') ?>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Social Login (Optional) -->
                <?php if (getSystemSetting('enable_social_login', false)): ?>
                <div class="social-auth-links text-center mt-3">
                    <p class="text-muted">- <?= t('or') ?> -</p>
                    
                    <?php if (getSystemSetting('google_login_enabled', false)): ?>
                    <a href="<?= getFullUrl('/auth/google.php') ?>" class="btn btn-block btn-danger">
                        <i class="fab fa-google-plus mr-2"></i> <?= t('login_with_google') ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (getSystemSetting('facebook_login_enabled', false)): ?>
                    <a href="<?= getFullUrl('/auth/facebook.php') ?>" class="btn btn-block btn-primary">
                        <i class="fab fa-facebook mr-2"></i> <?= t('login_with_facebook') ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Registration Link -->
                <?php if (getSystemSetting('enable_registration', true)): ?>
                <hr>
                <p class="text-center">
                    <?= t('no_account') ?> 
                    <a href="<?= getFullUrl('/auth/register.php') ?>" class="text-center text-decoration-none">
                        <?= t('register_here') ?>
                    </a>
                </p>
                <?php endif; ?>

                <!-- Guest Access -->
                <hr>
                <p class="text-center">
                    <a href="<?= getFullUrl('/') ?>" class="text-muted text-decoration-none">
                        <i class="fas fa-arrow-left"></i> <?= t('back_to_home') ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Additional CSS for Login Page -->
<style>
.login-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-box {
    width: 400px;
    margin: 0 auto;
}

.login-logo a {
    color: #fff;
    text-decoration: none;
    font-size: 2rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.login-logo a:hover {
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

/* Responsive */
@media (max-width: 576px) {
    .login-box {
        width: 90%;
        margin: 20px auto;
    }
    
    .login-page {
        padding: 20px;
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
    // Password toggle
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
    
    // Form submission handling
    $('#loginForm').on('submit', function(e) {
        let btn = $('#loginBtn');
        let username = $('#username').val().trim();
        let password = $('#password').val();
        
        // Basic validation
        if (!username || !password) {
            e.preventDefault();
            window.fileShareHub.utils.showToast(t('please_fill_required_fields'), 'error');
            return false;
        }
        
        // Show loading state
        btn.addClass('btn-loading').prop('disabled', true);
        
        // Add timeout to prevent hanging
        setTimeout(function() {
            btn.removeClass('btn-loading').prop('disabled', false);
        }, 10000); // 10 seconds timeout
    });
    
    // Auto-focus username field
    $('#username').focus();
    
    // Enter key handling
    $('#password').on('keypress', function(e) {
        if (e.which === 13) {
            $('#loginForm').submit();
        }
    });
    
    // Remember me toggle effect
    $('#remember').on('change', function() {
        if ($(this).is(':checked')) {
            window.fileShareHub.utils.showToast(t('remember_me_enabled'), 'info', 3000);
        }
    });
    
    // Check for saved username
    let savedUsername = localStorage.getItem('saved_username');
    if (savedUsername && !$('#username').val()) {
        $('#username').val(savedUsername);
        $('#password').focus();
    }
    
    // Save username on successful login
    $('#loginForm').on('submit', function() {
        let username = $('#username').val().trim();
        if (username) {
            localStorage.setItem('saved_username', username);
        }
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Alt+U to focus username
        if (e.altKey && e.key === 'u') {
            e.preventDefault();
            $('#username').focus();
        }
        
        // Alt+P to focus password
        if (e.altKey && e.key === 'p') {
            e.preventDefault();
            $('#password').focus();
        }
    });
    
    // Security: Clear password on page unload
    $(window).on('beforeunload', function() {
        $('#password').val('');
    });
    
    // Detect caps lock
    $('#password, #username').on('keypress', function(e) {
        let capsLock = e.originalEvent.getModifierState('CapsLock');
        if (capsLock) {
            if (!$('.caps-lock-warning').length) {
                $(this).after('<small class="caps-lock-warning text-warning"><i class="fas fa-exclamation-triangle"></i> ' + t('caps_lock_on') + '</small>');
            }
        } else {
            $('.caps-lock-warning').remove();
        }
    });
    
    // Remove caps lock warning on blur
    $('#password, #username').on('blur', function() {
        $('.caps-lock-warning').remove();
    });
});

// Add Thai translations for JavaScript
window.fileShareHub.translations.please_fill_required_fields = 'กรุณากรอกข้อมูลที่จำเป็น';
window.fileShareHub.translations.remember_me_enabled = 'เปิดใช้งานการจดจำการเข้าสู่ระบบ';
window.fileShareHub.translations.caps_lock_on = 'Caps Lock เปิดอยู่';
window.fileShareHub.translations.enter_username_email = 'ป้อนชื่อผู้ใช้หรืออีเมล';
window.fileShareHub.translations.please_login_to_continue = 'กรุณาเข้าสู่ระบบเพื่อดำเนินการต่อ';
window.fileShareHub.translations.no_account = 'ยังไม่มีบัญชี?';
window.fileShareHub.translations.register_here = 'สมัครที่นี่';
window.fileShareHub.translations.back_to_home = 'กลับสู่หน้าแรก';
window.fileShareHub.translations.login_with_google = 'เข้าสู่ระบบด้วย Google';
window.fileShareHub.translations.login_with_facebook = 'เข้าสู่ระบบด้วย Facebook';
</script>

<?php
// รวมไฟล์ footer
include '../includes/footer.php';
?>