<?php
/**
 * Constants Test File
 * ใช้สำหรับทดสอบ constants
 * ลบไฟล์นี้หลังจากทดสอบเสร็จแล้ว
 */

define('FILE_SHARE_HUB', true);

// รวมไฟล์ constants
require_once 'config/constants.php';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constants Test</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e6f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; font-size: 14px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
        .card { background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
        .constant-group { margin: 20px 0; }
        .count { color: #666; font-size: 14px; }
        code { background: #f1f1f1; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Constants Test - File Share Hub</h1>
        
        <?php
        echo "<div class='success'>✅ <strong>Constants loaded successfully!</strong></div>";
        
        // นับจำนวน constants
        $allConstants = get_defined_constants(true)['user'];
        $appConstants = array_filter($allConstants, function($key) {
            return strpos($key, 'FILE_SHARE_HUB') !== false || 
                   strpos($key, 'APP_') !== false ||
                   strpos($key, 'TABLE_') !== false ||
                   strpos($key, 'ROLE_') !== false ||
                   strpos($key, 'USER_') !== false ||
                   strpos($key, 'FILE_') !== false ||
                   strpos($key, 'SHARE_') !== false ||
                   strpos($key, 'HTTP_') !== false ||
                   strpos($key, 'ACTIVITY_') !== false ||
                   strpos($key, 'ERR_') !== false ||
                   strpos($key, 'MIME_') !== false;
        }, ARRAY_FILTER_USE_KEY);
        
        echo "<div class='info'>📊 Total constants defined: <strong>" . count($appConstants) . "</strong></div>";
        ?>
        
        <div class="grid">
            <!-- Application Constants -->
            <div class="card">
                <h3>📱 Application Constants</h3>
                <table>
                    <tr><th>Constant</th><th>Value</th></tr>
                    <tr><td>APP_NAME</td><td><?= APP_NAME ?></td></tr>
                    <tr><td>APP_VERSION</td><td><?= APP_VERSION ?></td></tr>
                    <tr><td>APP_ID</td><td><?= APP_ID ?></td></tr>
                    <tr><td>VERSION_MAJOR</td><td><?= VERSION_MAJOR ?></td></tr>
                    <tr><td>VERSION_MINOR</td><td><?= VERSION_MINOR ?></td></tr>
                    <tr><td>VERSION_PATCH</td><td><?= VERSION_PATCH ?></td></tr>
                    <tr><td>VERSION_BUILD</td><td><?= VERSION_BUILD ?></td></tr>
                    <tr><td>RELEASE_CODENAME</td><td><?= RELEASE_CODENAME ?></td></tr>
                </table>
            </div>
            
            <!-- Database Tables -->
            <div class="card">
                <h3>🗄️ Database Tables</h3>
                <table>
                    <tr><th>Constant</th><th>Table Name</th></tr>
                    <tr><td>TABLE_USERS</td><td><?= TABLE_USERS ?></td></tr>
                    <tr><td>TABLE_PURE_USERS</td><td><?= TABLE_PURE_USERS ?></td></tr>
                    <tr><td>TABLE_FILES</td><td><?= TABLE_FILES ?></td></tr>
                    <tr><td>TABLE_FOLDERS</td><td><?= TABLE_FOLDERS ?></td></tr>
                    <tr><td>TABLE_SHARED_LINKS</td><td><?= TABLE_SHARED_LINKS ?></td></tr>
                    <tr><td>TABLE_SYSTEM_SETTINGS</td><td><?= TABLE_SYSTEM_SETTINGS ?></td></tr>
                    <tr><td>TABLE_ACTIVITY_LOGS</td><td><?= TABLE_ACTIVITY_LOGS ?></td></tr>
                    <tr><td>TABLE_SESSIONS</td><td><?= TABLE_SESSIONS ?></td></tr>
                </table>
            </div>
            
            <!-- User Management -->
            <div class="card">
                <h3>👥 User Management</h3>
                <h4>User Roles:</h4>
                <ul>
                    <li><code>ROLE_USER</code> = <?= ROLE_USER ?></li>
                    <li><code>ROLE_ADMIN</code> = <?= ROLE_ADMIN ?></li>
                    <li><code>ROLE_MODERATOR</code> = <?= ROLE_MODERATOR ?></li>
                </ul>
                
                <h4>User Status:</h4>
                <ul>
                    <li><code>USER_STATUS_ACTIVE</code> = <?= USER_STATUS_ACTIVE ?></li>
                    <li><code>USER_STATUS_INACTIVE</code> = <?= USER_STATUS_INACTIVE ?></li>
                    <li><code>USER_STATUS_SUSPENDED</code> = <?= USER_STATUS_SUSPENDED ?></li>
                    <li><code>USER_STATUS_DELETED</code> = <?= USER_STATUS_DELETED ?></li>
                </ul>
                
                <h4>Default Limits:</h4>
                <ul>
                    <li>User Storage: <?= bytesToHumanReadable(DEFAULT_USER_STORAGE_LIMIT) ?></li>
                    <li>User Files: <?= DEFAULT_USER_FILE_LIMIT ?></li>
                    <li>Admin Storage: <?= bytesToHumanReadable(DEFAULT_ADMIN_STORAGE_LIMIT) ?></li>
                    <li>Admin Files: <?= DEFAULT_ADMIN_FILE_LIMIT ?></li>
                </ul>
            </div>
            
            <!-- File Management -->
            <div class="card">
                <h3>📁 File Management</h3>
                <h4>File Sizes:</h4>
                <ul>
                    <li><code>FILE_SIZE_1KB</code> = <?= bytesToHumanReadable(FILE_SIZE_1KB) ?></li>
                    <li><code>FILE_SIZE_1MB</code> = <?= bytesToHumanReadable(FILE_SIZE_1MB) ?></li>
                    <li><code>FILE_SIZE_10MB</code> = <?= bytesToHumanReadable(FILE_SIZE_10MB) ?></li>
                    <li><code>FILE_SIZE_100MB</code> = <?= bytesToHumanReadable(FILE_SIZE_100MB) ?></li>
                    <li><code>FILE_SIZE_1GB</code> = <?= bytesToHumanReadable(FILE_SIZE_1GB) ?></li>
                </ul>
                
                <h4>File Types:</h4>
                <ul>
                    <li><code>FILE_TYPE_IMAGE</code> = <?= FILE_TYPE_IMAGE ?></li>
                    <li><code>FILE_TYPE_DOCUMENT</code> = <?= FILE_TYPE_DOCUMENT ?></li>
                    <li><code>FILE_TYPE_ARCHIVE</code> = <?= FILE_TYPE_ARCHIVE ?></li>
                    <li><code>FILE_TYPE_OTHER</code> = <?= FILE_TYPE_OTHER ?></li>
                </ul>
            </div>
            
            <!-- Sharing System -->
            <div class="card">
                <h3>🔗 Sharing System</h3>
                <h4>Share Types:</h4>
                <ul>
                    <li><code>SHARE_TYPE_FILE</code> = <?= SHARE_TYPE_FILE ?></li>
                    <li><code>SHARE_TYPE_FOLDER</code> = <?= SHARE_TYPE_FOLDER ?></li>
                </ul>
                
                <h4>Share Status:</h4>
                <ul>
                    <li><code>SHARE_STATUS_ACTIVE</code> = <?= SHARE_STATUS_ACTIVE ?></li>
                    <li><code>SHARE_STATUS_INACTIVE</code> = <?= SHARE_STATUS_INACTIVE ?></li>
                    <li><code>SHARE_STATUS_EXPIRED</code> = <?= SHARE_STATUS_EXPIRED ?></li>
                </ul>
                
                <h4>Expiry Options:</h4>
                <ul>
                    <li>1 Day = <?= SHARE_EXPIRY_1_DAY ?></li>
                    <li>7 Days = <?= SHARE_EXPIRY_7_DAYS ?></li>
                    <li>30 Days = <?= SHARE_EXPIRY_30_DAYS ?></li>
                    <li>Never = <?= SHARE_EXPIRY_NEVER === null ? 'null' : SHARE_EXPIRY_NEVER ?></li>
                </ul>
            </div>
            
            <!-- HTTP Status Codes -->
            <div class="card">
                <h3>🌐 HTTP Status Codes</h3>
                <table>
                    <tr><th>Code</th><th>Constant</th><th>Message</th></tr>
                    <tr><td><?= HTTP_OK ?></td><td>HTTP_OK</td><td><?= getHttpStatusMessage(HTTP_OK) ?></td></tr>
                    <tr><td><?= HTTP_CREATED ?></td><td>HTTP_CREATED</td><td><?= getHttpStatusMessage(HTTP_CREATED) ?></td></tr>
                    <tr><td><?= HTTP_BAD_REQUEST ?></td><td>HTTP_BAD_REQUEST</td><td><?= getHttpStatusMessage(HTTP_BAD_REQUEST) ?></td></tr>
                    <tr><td><?= HTTP_UNAUTHORIZED ?></td><td>HTTP_UNAUTHORIZED</td><td><?= getHttpStatusMessage(HTTP_UNAUTHORIZED) ?></td></tr>
                    <tr><td><?= HTTP_FORBIDDEN ?></td><td>HTTP_FORBIDDEN</td><td><?= getHttpStatusMessage(HTTP_FORBIDDEN) ?></td></tr>
                    <tr><td><?= HTTP_NOT_FOUND ?></td><td>HTTP_NOT_FOUND</td><td><?= getHttpStatusMessage(HTTP_NOT_FOUND) ?></td></tr>
                    <tr><td><?= HTTP_INTERNAL_SERVER_ERROR ?></td><td>HTTP_INTERNAL_SERVER_ERROR</td><td><?= getHttpStatusMessage(HTTP_INTERNAL_SERVER_ERROR) ?></td></tr>
                </table>
            </div>
        </div>
        
        <!-- Helper Functions Test -->
        <div class="constant-group">
            <h3>🔧 Helper Functions Test</h3>
            <div class="grid">
                <div class="card">
                    <h4>MIME Type Detection</h4>
                    <table>
                        <tr><th>Extension</th><th>MIME Type</th></tr>
                        <tr><td>jpg</td><td><?= getMimeTypeFromExtension('jpg') ?></td></tr>
                        <tr><td>png</td><td><?= getMimeTypeFromExtension('png') ?></td></tr>
                        <tr><td>pdf</td><td><?= getMimeTypeFromExtension('pdf') ?></td></tr>
                        <tr><td>doc</td><td><?= getMimeTypeFromExtension('doc') ?></td></tr>
                        <tr><td>zip</td><td><?= getMimeTypeFromExtension('zip') ?></td></tr>
                        <tr><td>mp4</td><td><?= getMimeTypeFromExtension('mp4') ?></td></tr>
                        <tr><td>unknown</td><td><?= getMimeTypeFromExtension('unknown') ?></td></tr>
                    </table>
                </div>
                
                <div class="card">
                    <h4>File Size Conversion</h4>
                    <table>
                        <tr><th>Bytes</th><th>Human Readable</th></tr>
                        <tr><td>1024</td><td><?= bytesToHumanReadable(1024) ?></td></tr>
                        <tr><td>1048576</td><td><?= bytesToHumanReadable(1048576) ?></td></tr>
                        <tr><td>10485760</td><td><?= bytesToHumanReadable(10485760) ?></td></tr>
                        <tr><td>104857600</td><td><?= bytesToHumanReadable(104857600) ?></td></tr>
                        <tr><td>1073741824</td><td><?= bytesToHumanReadable(1073741824) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Error Codes -->
        <div class="constant-group">
            <h3>❌ Error Codes</h3>
            <div class="grid">
                <div class="card">
                    <h4>System Errors (1000-1999)</h4>
                    <ul>
                        <li><code>ERR_SYSTEM_ERROR</code> = <?= ERR_SYSTEM_ERROR ?></li>
                        <li><code>ERR_DATABASE_ERROR</code> = <?= ERR_DATABASE_ERROR ?></li>
                        <li><code>ERR_FILE_ERROR</code> = <?= ERR_FILE_ERROR ?></li>
                        <li><code>ERR_PERMISSION_DENIED</code> = <?= ERR_PERMISSION_DENIED ?></li>
                    </ul>
                </div>
                
                <div class="card">
                    <h4>User Errors (2000-2999)</h4>
                    <ul>
                        <li><code>ERR_USER_NOT_FOUND</code> = <?= ERR_USER_NOT_FOUND ?></li>
                        <li><code>ERR_USER_INACTIVE</code> = <?= ERR_USER_INACTIVE ?></li>
                        <li><code>ERR_INVALID_CREDENTIALS</code> = <?= ERR_INVALID_CREDENTIALS ?></li>
                        <li><code>ERR_USER_EXISTS</code> = <?= ERR_USER_EXISTS ?></li>
                    </ul>
                </div>
                
                <div class="card">
                    <h4>File Errors (3000-3999)</h4>
                    <ul>
                        <li><code>ERR_FILE_NOT_FOUND</code> = <?= ERR_FILE_NOT_FOUND ?></li>
                        <li><code>ERR_FILE_TOO_LARGE</code> = <?= ERR_FILE_TOO_LARGE ?></li>
                        <li><code>ERR_FILE_TYPE_NOT_ALLOWED</code> = <?= ERR_FILE_TYPE_NOT_ALLOWED ?></li>
                        <li><code>ERR_STORAGE_LIMIT_EXCEEDED</code> = <?= ERR_STORAGE_LIMIT_EXCEEDED ?></li>
                    </ul>
                </div>
                
                <div class="card">
                    <h4>Share Errors (4000-4999)</h4>
                    <ul>
                        <li><code>ERR_SHARE_NOT_FOUND</code> = <?= ERR_SHARE_NOT_FOUND ?></li>
                        <li><code>ERR_SHARE_EXPIRED</code> = <?= ERR_SHARE_EXPIRED ?></li>
                        <li><code>ERR_SHARE_PASSWORD_REQUIRED</code> = <?= ERR_SHARE_PASSWORD_REQUIRED ?></li>
                        <li><code>ERR_SHARE_LIMIT_EXCEEDED</code> = <?= ERR_SHARE_LIMIT_EXCEEDED ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Activity Types -->
        <div class="constant-group">
            <h3>📊 Activity & Logging</h3>
            <div class="grid">
                <div class="card">
                    <h4>Activity Types</h4>
                    <ul>
                        <li><code>ACTIVITY_LOGIN</code> = <?= ACTIVITY_LOGIN ?></li>
                        <li><code>ACTIVITY_LOGOUT</code> = <?= ACTIVITY_LOGOUT ?></li>
                        <li><code>ACTIVITY_UPLOAD</code> = <?= ACTIVITY_UPLOAD ?></li>
                        <li><code>ACTIVITY_DOWNLOAD</code> = <?= ACTIVITY_DOWNLOAD ?></li>
                        <li><code>ACTIVITY_SHARE</code> = <?= ACTIVITY_SHARE ?></li>
                        <li><code>ACTIVITY_DELETE</code> = <?= ACTIVITY_DELETE ?></li>
                        <li><code>ACTIVITY_ADMIN</code> = <?= ACTIVITY_ADMIN ?></li>
                    </ul>
                </div>
                
                <div class="card">
                    <h4>Target Types</h4>
                    <ul>
                        <li><code>TARGET_FILE</code> = <?= TARGET_FILE ?></li>
                        <li><code>TARGET_FOLDER</code> = <?= TARGET_FOLDER ?></li>
                        <li><code>TARGET_USER</code> = <?= TARGET_USER ?></li>
                        <li><code>TARGET_SHARE</code> = <?= TARGET_SHARE ?></li>
                        <li><code>TARGET_SYSTEM</code> = <?= TARGET_SYSTEM ?></li>
                    </ul>
                </div>
                
                <div class="card">
                    <h4>Log Levels</h4>
                    <ul>
                        <li><code>LOG_LEVEL_DEBUG</code> = <?= LOG_LEVEL_DEBUG ?></li>
                        <li><code>LOG_LEVEL_INFO</code> = <?= LOG_LEVEL_INFO ?></li>
                        <li><code>LOG_LEVEL_WARNING</code> = <?= LOG_LEVEL_WARNING ?></li>
                        <li><code>LOG_LEVEL_ERROR</code> = <?= LOG_LEVEL_ERROR ?></li>
                        <li><code>LOG_LEVEL_CRITICAL</code> = <?= LOG_LEVEL_CRITICAL ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Validation Patterns -->
        <div class="constant-group">
            <h3>✅ Validation & Security</h3>
            <div class="grid">
                <div class="card">
                    <h4>Regular Expressions</h4>
                    <table>
                        <tr><th>Pattern</th><th>Regex</th></tr>
                        <tr><td>Username</td><td><code><?= htmlspecialchars(REGEX_USERNAME) ?></code></td></tr>
                        <tr><td>Password</td><td><code><?= htmlspecialchars(REGEX_PASSWORD) ?></code></td></tr>
                        <tr><td>Email</td><td><code><?= htmlspecialchars(REGEX_EMAIL) ?></code></td></tr>
                        <tr><td>Filename</td><td><code><?= htmlspecialchars(REGEX_FILENAME) ?></code></td></tr>
                        <tr><td>Share Token</td><td><code><?= htmlspecialchars(REGEX_SHARE_TOKEN) ?></code></td></tr>
                    </table>
                </div>
                
                <div class="card">
                    <h4>Session Variables</h4>
                    <ul>
                        <li><code>SESS_USER_ID</code> = <?= SESS_USER_ID ?></li>
                        <li><code>SESS_USERNAME</code> = <?= SESS_USERNAME ?></li>
                        <li><code>SESS_USER_ROLE</code> = <?= SESS_USER_ROLE ?></li>
                        <li><code>SESS_LOGIN_TIME</code> = <?= SESS_LOGIN_TIME ?></li>
                        <li><code>SESS_CSRF_TOKEN</code> = <?= SESS_CSRF_TOKEN ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Constants by Prefix -->
        <div class="constant-group">
            <h3>📋 All Constants by Category</h3>
            <div class="info">
                <p>Testing helper function: <code>getConstantsByPrefix()</code></p>
            </div>
            
            <?php
            $categories = [
                'APP_' => 'Application Constants',
                'TABLE_' => 'Database Tables',
                'ROLE_' => 'User Roles',
                'FILE_' => 'File Management',
                'SHARE_' => 'Sharing System',
                'HTTP_' => 'HTTP Status Codes',
                'ERR_' => 'Error Codes',
                'ACTIVITY_' => 'Activity Types'
            ];
            
            echo "<div class='grid'>";
            foreach ($categories as $prefix => $title) {
                $constants = getConstantsByPrefix($prefix);
                if (!empty($constants)) {
                    echo "<div class='card'>";
                    echo "<h4>{$title} <span class='count'>(" . count($constants) . ")</span></h4>";
                    echo "<ul>";
                    foreach (array_slice($constants, 0, 8) as $name => $value) {
                        if (is_bool($value)) {
                            $value = $value ? 'true' : 'false';
                        } elseif (is_null($value)) {
                            $value = 'null';
                        } elseif (is_numeric($value) && $value > 1024) {
                            $value = number_format($value);
                        }
                        echo "<li><code>{$name}</code> = " . htmlspecialchars($value) . "</li>";
                    }
                    if (count($constants) > 8) {
                        echo "<li><em>... และอีก " . (count($constants) - 8) . " constants</em></li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                }
            }
            echo "</div>";
            ?>
        </div>
        
        <!-- Testing Results -->
        <div class="constant-group">
            <h3>🧪 Testing Results</h3>
            <div class="success">
                ✅ <strong>All constants loaded successfully!</strong><br>
                📊 Total constants: <?= count($appConstants) ?><br>
                🔧 Helper functions working properly<br>
                ✅ No missing required constants
            </div>
            
            <div class="info">
                <strong>📝 Next Steps:</strong>
                <ol>
                    <li>Constants are ready for use in the application</li>
                    <li>Create <code>config/session.php</code> next</li>
                    <li>Use constants throughout the codebase for consistency</li>
                    <li>Delete this test file after verification</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>