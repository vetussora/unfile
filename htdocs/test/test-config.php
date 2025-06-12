<?php
/**
 * Configuration Test File
 * ใช้สำหรับทดสอบการตั้งค่าระบบ
 * ลบไฟล์นี้หลังจากทดสอบเสร็จแล้ว
 */

// เปิด debug mode
define('FILE_SHARE_HUB', true);

// รวมไฟล์ config
require_once 'config/config.php';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Test</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e6f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff3e0; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card { background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ File Share Hub - Configuration Test</h1>
        
        <?php
        echo "<div class='success'>✅ <strong>Configuration loaded successfully!</strong></div>";
        ?>
        
        <div class="grid">
            <!-- System Information -->
            <div class="card">
                <h3>📋 System Information</h3>
                <table>
                    <tr><td><strong>App Name:</strong></td><td><?= APP_NAME ?></td></tr>
                    <tr><td><strong>Version:</strong></td><td><?= APP_VERSION ?></td></tr>
                    <tr><td><strong>Environment:</strong></td><td><?= ENVIRONMENT ?></td></tr>
                    <tr><td><strong>Debug Mode:</strong></td><td><?= DEBUG_MODE ? 'ON' : 'OFF' ?></td></tr>
                    <tr><td><strong>PHP Version:</strong></td><td><?= PHP_VERSION ?></td></tr>
                    <tr><td><strong>Timezone:</strong></td><td><?= DEFAULT_TIMEZONE ?></td></tr>
                </table>
            </div>
            
            <!-- URL Configuration -->
            <div class="card">
                <h3>🌐 URL Configuration</h3>
                <table>
                    <tr><td><strong>Base URL:</strong></td><td><?= BASE_URL ?></td></tr>
                    <tr><td><strong>Site URL:</strong></td><td><?= SITE_URL ?></td></tr>
                    <tr><td><strong>Assets URL:</strong></td><td><?= ASSETS_URL ?></td></tr>
                    <tr><td><strong>Upload URL:</strong></td><td><?= UPLOAD_URL ?></td></tr>
                </table>
            </div>
            
            <!-- File Upload Settings -->
            <div class="card">
                <h3>📁 Upload Configuration</h3>
                <table>
                    <tr><td><strong>Max File Size:</strong></td><td><?= formatFileSize(MAX_FILE_SIZE) ?></td></tr>
                    <tr><td><strong>Max Total Size:</strong></td><td><?= formatFileSize(MAX_TOTAL_SIZE) ?></td></tr>
                    <tr><td><strong>Max Files per User:</strong></td><td><?= MAX_FILES_PER_USER ?></td></tr>
                    <tr><td><strong>Upload Path:</strong></td><td><?= UPLOAD_PATH ?></td></tr>
                </table>
            </div>
            
            <!-- Security Settings -->
            <div class="card">
                <h3>🔒 Security Configuration</h3>
                <table>
                    <tr><td><strong>Session Name:</strong></td><td><?= SESSION_NAME ?></td></tr>
                    <tr><td><strong>Session Lifetime:</strong></td><td><?= SESSION_LIFETIME ?> seconds</td></tr>
                    <tr><td><strong>CSRF Token:</strong></td><td><?= CSRF_TOKEN_NAME ?></td></tr>
                    <tr><td><strong>Password Min Length:</strong></td><td><?= PASSWORD_MIN_LENGTH ?></td></tr>
                </table>
            </div>
        </div>
        
        <!-- Features Status -->
        <h3>🎛️ Features Status</h3>
        <div class="grid">
            <div class="card">
                <h4>Core Features</h4>
                <ul>
                    <li>Registration: <?= FEATURE_REGISTRATION ? '✅ Enabled' : '❌ Disabled' ?></li>
                    <li>Email Verification: <?= FEATURE_EMAIL_VERIFICATION ? '✅ Enabled' : '❌ Disabled' ?></li>
                    <li>Password Reset: <?= FEATURE_PASSWORD_RESET ? '✅ Enabled' : '❌ Disabled' ?></li>
                    <li>Activity Logs: <?= FEATURE_ACTIVITY_LOGS ? '✅ Enabled' : '❌ Disabled' ?></li>
                </ul>
            </div>
            
            <div class="card">
                <h4>Advanced Features</h4>
                <ul>
                    <li>Thumbnails: <?= FEATURE_THUMBNAILS ? '✅ Enabled' : '❌ Disabled' ?></li>
                    <li>File Versioning: <?= FEATURE_FILE_VERSIONING ? '✅ Enabled' : '❌ Disabled' ?></li>
                    <li>Admin Panel: <?= FEATURE_ADMIN_PANEL ? '✅ Enabled' : '❌ Disabled' ?></li>
                    <li>Share Analytics: <?= FEATURE_SHARE_ANALYTICS ? '✅ Enabled' : '❌ Disabled' ?></li>
                </ul>
            </div>
        </div>
        
        <!-- Directory Check -->
        <h3>📂 Directory Status</h3>
        <?php
        $directories = [
            'Root Path' => ROOT_PATH,
            'Upload Path' => UPLOAD_PATH,
            'Logs Path' => LOGS_PATH,
            'Cache Path' => CACHE_PATH,
            'User Upload Path' => USER_UPLOAD_PATH
        ];
        
        echo "<table>";
        echo "<tr><th>Directory</th><th>Path</th><th>Status</th><th>Writable</th></tr>";
        
        foreach ($directories as $name => $path) {
            $exists = is_dir($path);
            $writable = $exists ? is_writable($path) : false;
            
            echo "<tr>";
            echo "<td><strong>{$name}</strong></td>";
            echo "<td>" . htmlspecialchars($path) . "</td>";
            echo "<td>" . ($exists ? '✅ Exists' : '❌ Missing') . "</td>";
            echo "<td>" . ($writable ? '✅ Writable' : '❌ Not Writable') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        ?>
        
        <!-- Allowed File Types -->
        <h3>📄 Allowed File Types</h3>
        <div class="grid">
            <?php foreach (ALLOWED_FILE_TYPES as $category => $types): ?>
            <div class="card">
                <h4><?= ucfirst($category) ?></h4>
                <?php if (!empty($types)): ?>
                    <p><?= implode(', ', $types) ?></p>
                <?php else: ?>
                    <p><em>No specific types defined</em></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Helper Functions Test -->
        <h3>🔧 Helper Functions Test</h3>
        <div class="card">
            <table>
                <tr>
                    <td><strong>getFullUrl('/'):</strong></td>
                    <td><?= getFullUrl('/') ?></td>
                </tr>
                <tr>
                    <td><strong>getAssetUrl('style.css', 'css'):</strong></td>
                    <td><?= getAssetUrl('style.css', 'css') ?></td>
                </tr>
                <tr>
                    <td><strong>getAssetUrl('app.js', 'js'):</strong></td>
                    <td><?= getAssetUrl('app.js', 'js') ?></td>
                </tr>
                <tr>
                    <td><strong>formatFileSize(1048576):</strong></td>
                    <td><?= formatFileSize(1048576) ?></td>
                </tr>
                <tr>
                    <td><strong>formatFileSize(MAX_FILE_SIZE):</strong></td>
                    <td><?= formatFileSize(MAX_FILE_SIZE) ?></td>
                </tr>
                <tr>
                    <td><strong>isDebugMode():</strong></td>
                    <td><?= isDebugMode() ? 'true' : 'false' ?></td>
                </tr>
                <tr>
                    <td><strong>isAjaxRequest():</strong></td>
                    <td><?= isAjaxRequest() ? 'true' : 'false' ?></td>
                </tr>
                <tr>
                    <td><strong>isFeatureEnabled('registration'):</strong></td>
                    <td><?= isFeatureEnabled('registration') ? 'true' : 'false' ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Configuration Array Test -->
        <h3>📊 Configuration Array Test</h3>
        <div class="card">
            <table>
                <tr>
                    <td><strong>getConfig('app.name'):</strong></td>
                    <td><?= getConfig('app.name') ?></td>
                </tr>
                <tr>
                    <td><strong>getConfig('app.version'):</strong></td>
                    <td><?= getConfig('app.version') ?></td>
                </tr>
                <tr>
                    <td><strong>getConfig('urls.base'):</strong></td>
                    <td><?= getConfig('urls.base') ?></td>
                </tr>
                <tr>
                    <td><strong>getConfig('upload.max_file_size'):</strong></td>
                    <td><?= formatFileSize(getConfig('upload.max_file_size')) ?></td>
                </tr>
                <tr>
                    <td><strong>getConfig('features.registration'):</strong></td>
                    <td><?= getConfig('features.registration') ? 'true' : 'false' ?></td>
                </tr>
            </table>
        </div>
        
        <!-- PHP Settings Check -->
        <h3>⚙️ PHP Settings Check</h3>
        <div class="card">
            <table>
                <tr>
                    <td><strong>max_execution_time:</strong></td>
                    <td><?= ini_get('max_execution_time') ?> seconds</td>
                </tr>
                <tr>
                    <td><strong>memory_limit:</strong></td>
                    <td><?= ini_get('memory_limit') ?></td>
                </tr>
                <tr>
                    <td><strong>post_max_size:</strong></td>
                    <td><?= ini_get('post_max_size') ?></td>
                </tr>
                <tr>
                    <td><strong>upload_max_filesize:</strong></td>
                    <td><?= ini_get('upload_max_filesize') ?></td>
                </tr>
                <tr>
                    <td><strong>max_file_uploads:</strong></td>
                    <td><?= ini_get('max_file_uploads') ?></td>
                </tr>
                <tr>
                    <td><strong>error_reporting:</strong></td>
                    <td><?= ini_get('display_errors') ? 'ON' : 'OFF' ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Security Headers Test -->
        <h3>🛡️ Security Headers</h3>
        <div class="card">
            <p><strong>Security headers have been set:</strong></p>
            <ul>
                <?php foreach (SECURITY_HEADERS as $header => $value): ?>
                    <li><code><?= htmlspecialchars($header) ?>: <?= htmlspecialchars($value) ?></code></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <!-- Warnings and Recommendations -->
        <h3>⚠️ Warnings & Recommendations</h3>
        
        <?php
        $warnings = [];
        $recommendations = [];
        
        // Check BASE_URL
        if (strpos(BASE_URL, 'yourdomain.infinityfreeapp.com') !== false) {
            $warnings[] = "❌ BASE_URL ยังใช้ 'yourdomain.infinityfreeapp.com' กรุณาแก้ไขเป็นโดเมนจริง";
        }
        
        // Check if in production mode
        if (ENVIRONMENT === 'development') {
            $warnings[] = "⚠️ ระบบอยู่ใน Development mode กรุณาเปลี่ยนเป็น Production เมื่อใช้งานจริง";
        }
        
        // Check mail configuration
        if (!MAIL_ENABLED) {
            $recommendations[] = "📧 SMTP ยังไม่ได้ตั้งค่า หากต้องการใช้ email verification และ password reset";
        }
        
        // Check upload directory
        if (!is_writable(UPLOAD_PATH)) {
            $warnings[] = "❌ โฟลเดอร์ upload ไม่สามารถเขียนไฟล์ได้";
        }
        
        // Check logs directory
        if (!is_writable(LOGS_PATH)) {
            $warnings[] = "❌ โฟลเดอร์ logs ไม่สามารถเขียนไฟล์ได้";
        }
        
        // Display warnings
        if (!empty($warnings)) {
            foreach ($warnings as $warning) {
                echo "<div class='warning'>{$warning}</div>";
            }
        }
        
        // Display recommendations
        if (!empty($recommendations)) {
            foreach ($recommendations as $recommendation) {
                echo "<div class='info'>{$recommendation}</div>";
            }
        }
        
        // If no warnings
        if (empty($warnings)) {
            echo "<div class='success'>✅ ไม่พบปัญหาที่สำคัญ ระบบพร้อมใช้งาน!</div>";
        }
        ?>
        
        <!-- Sample URLs -->
        <h3>🔗 Sample URLs</h3>
        <div class="card">
            <table>
                <tr>
                    <td><strong>Home Page:</strong></td>
                    <td><a href="<?= getFullUrl('/') ?>" target="_blank"><?= getFullUrl('/') ?></a></td>
                </tr>
                <tr>
                    <td><strong>Login Page:</strong></td>
                    <td><a href="<?= getFullUrl('/auth/login.php') ?>" target="_blank"><?= getFullUrl('/auth/login.php') ?></a></td>
                </tr>
                <tr>
                    <td><strong>Dashboard:</strong></td>
                    <td><a href="<?= getFullUrl('/dashboard/') ?>" target="_blank"><?= getFullUrl('/dashboard/') ?></a></td>
                </tr>
                <tr>
                    <td><strong>Admin Panel:</strong></td>
                    <td><a href="<?= getFullUrl('/admin/') ?>" target="_blank"><?= getFullUrl('/admin/') ?></a></td>
                </tr>
            </table>
        </div>
        
        <!-- Debug Information -->
        <?php if (DEBUG_MODE): ?>
        <h3>🐛 Debug Information</h3>
        <div class="card">
            <h4>All Configuration:</h4>
            <pre><?= htmlspecialchars(print_r(getConfig(), true)) ?></pre>
            
            <h4>Defined Constants:</h4>
            <pre><?php
            $constants = get_defined_constants(true)['user'];
            ksort($constants);
            foreach ($constants as $name => $value) {
                if (strpos($name, 'FILE_SHARE_HUB') !== false || 
                    strpos($name, 'APP_') !== false || 
                    strpos($name, 'BASE_') !== false ||
                    strpos($name, 'MAX_') !== false ||
                    strpos($name, 'FEATURE_') !== false) {
                    printf("%-30s = %s\n", $name, is_bool($value) ? ($value ? 'true' : 'false') : $value);
                }
            }
            ?></pre>
        </div>
        <?php endif; ?>
        
        <hr>
        <div class="info">
            <strong>📝 Next Steps:</strong>
            <ol>
                <li>แก้ไข BASE_URL ให้ตรงกับโดเมนจริง</li>
                <li>ตั้งค่า SMTP หากต้องการใช้ email features</li>
                <li>ตรวจสอบว่าโฟลเดอร์ต่างๆ สามารถเขียนไฟล์ได้</li>
                <li>เปลี่ยน ENVIRONMENT เป็น 'production' เมื่อใช้งานจริง</li>
                <li>ลบไฟล์ test นี้หลังจากทดสอบเสร็จ</li>
            </ol>
        </div>
    </div>
</body>
</html>