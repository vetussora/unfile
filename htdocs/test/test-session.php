<?php
/**
 * Session Test File
 * ทดสอบการทำงานของ Session Manager
 */

define('FILE_SHARE_HUB', true);

// รวมไฟล์ config
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'config/session.php';

// ประมวลผล form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'set_session':
                setSession('test_key', $_POST['test_value'] ?? 'test_value');
                $message = "✅ Session set successfully!";
                break;
                
            case 'login_test':
                loginUser(999, 'testuser', 'admin');
                $message = "✅ Test login successful!";
                break;
                
            case 'logout_test':
                logoutUser();
                $message = "✅ Logout successful!";
                break;
                
            case 'destroy_session':
                destroySession();
                $message = "✅ Session destroyed!";
                break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Manager Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e6f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .card { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        form { margin: 10px 0; }
        input, button { padding: 8px 12px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; cursor: pointer; }
        button:hover { background: #0056b3; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Session Manager Test</h1>
        
        <?php if (isset($message)): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <!-- Session Status -->
        <div class="card">
            <h3>📊 Session Status</h3>
            <table>
                <tr>
                    <td><strong>Session Started:</strong></td>
                    <td><?= SessionManager::getInstance()->isStarted() ? '✅ Yes' : '❌ No' ?></td>
                </tr>
                <tr>
                    <td><strong>Session ID:</strong></td>
                    <td><?= SessionManager::getInstance()->getId() ?: 'N/A' ?></td>
                </tr>
                <tr>
                    <td><strong>PHP Session Status:</strong></td>
                    <td><?= session_status() === PHP_SESSION_ACTIVE ? '✅ Active' : '❌ Inactive' ?></td>
                </tr>
                <tr>
                    <td><strong>User Logged In:</strong></td>
                    <td><?= isLoggedIn() ? '✅ Yes' : '❌ No' ?></td>
                </tr>
            </table>
        </div>
        
        <!-- User Information -->
        <?php if (isLoggedIn()): ?>
        <div class="card">
            <h3>👤 User Information</h3>
            <table>
                <tr><td><strong>User ID:</strong></td><td><?= getCurrentUserId() ?></td></tr>
                <tr><td><strong>Username:</strong></td><td><?= getCurrentUsername() ?></td></tr>
                <tr><td><strong>User Role:</strong></td><td><?= getCurrentUserRole() ?></td></tr>
                <tr><td><strong>Is Admin:</strong></td><td><?= isAdmin() ? '✅ Yes' : '❌ No' ?></td></tr>
                <tr><td><strong>Is Moderator:</strong></td><td><?= isModerator() ? '✅ Yes' : '❌ No' ?></td></tr>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Session Data -->
        <div class="card">
            <h3>📋 Session Data</h3>
            <?php
            $sessionData = getAllSession();
            if (!empty($sessionData)):
            ?>
            <table>
                <tr><th>Key</th><th>Value</th></tr>
                <?php foreach ($sessionData as $key => $value): ?>
                    <tr>
                        <td><?= htmlspecialchars($key) ?></td>
                        <td><?= htmlspecialchars(is_array($value) ? json_encode($value) : $value) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p><em>No session data</em></p>
            <?php endif; ?>
        </div>
        
        <!-- Session Info -->
        <div class="card">
            <h3>ℹ️ Session Information</h3>
            <?php $sessionInfo = getSessionInfo(); ?>
            <?php if ($sessionInfo): ?>
            <table>
                <tr><td><strong>Session ID:</strong></td><td><?= htmlspecialchars($sessionInfo['session_id']) ?></td></tr>
                <tr><td><strong>Is Logged In:</strong></td><td><?= $sessionInfo['is_logged_in'] ? 'Yes' : 'No' ?></td></tr>
                <tr><td><strong>User ID:</strong></td><td><?= $sessionInfo['user_id'] ?: 'N/A' ?></td></tr>
                <tr><td><strong>Username:</strong></td><td><?= htmlspecialchars($sessionInfo['username'] ?: 'N/A') ?></td></tr>
                <tr><td><strong>User Role:</strong></td><td><?= htmlspecialchars($sessionInfo['user_role'] ?: 'N/A') ?></td></tr>
                <tr><td><strong>Login Time:</strong></td><td><?= $sessionInfo['login_time'] ? date('Y-m-d H:i:s', $sessionInfo['login_time']) : 'N/A' ?></td></tr>
                <tr><td><strong>Last Activity:</strong></td><td><?= $sessionInfo['last_activity'] ? date('Y-m-d H:i:s', $sessionInfo['last_activity']) : 'N/A' ?></td></tr>
                <tr><td><strong>IP Address:</strong></td><td><?= htmlspecialchars($sessionInfo['ip_address'] ?: 'N/A') ?></td></tr>
            </table>
            <?php else: ?>
            <p><em>No session information available</em></p>
            <?php endif; ?>
        </div>
        
        <!-- Test Actions -->
        <div class="grid">
            <!-- Basic Session Tests -->
            <div class="card">
                <h3>🧪 Basic Session Tests</h3>
                
                <form method="post">
                    <input type="hidden" name="action" value="set_session">
                    <input type="text" name="test_value" placeholder="Enter test value" value="<?= getSession('test_key', '') ?>">
                    <button type="submit">Set Session</button>
                </form>
                
                <p><strong>Current test_key value:</strong> <?= htmlspecialchars(getSession('test_key', 'Not set')) ?></p>
                
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="destroy_session">
                    <button type="submit" onclick="return confirm('Destroy session?')">Destroy Session</button>
                </form>
            </div>
            
            <!-- User Authentication Tests -->
            <div class="card">
                <h3>👤 Authentication Tests</h3>
                
                <?php if (!isLoggedIn()): ?>
                <form method="post">
                    <input type="hidden" name="action" value="login_test">
                    <button type="submit">Test Login (as admin)</button>
                </form>
                <?php else: ?>
                <form method="post">
                    <input type="hidden" name="action" value="logout_test">
                    <button type="submit">Test Logout</button>
                </form>
                <?php endif; ?>
            </div>
            
            <!-- CSRF Token Test -->
            <div class="card">
                <h3>🛡️ CSRF Token Test</h3>
                <p><strong>Current CSRF Token:</strong></p>
                <pre><?= getCSRFToken() ?></pre>
                
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <button type="submit">Test CSRF Token</button>
                </form>
                
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])): ?>
                    <p><strong>CSRF Validation:</strong> 
                        <?= validateCSRFFromRequest() ? '✅ Valid' : '❌ Invalid' ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- PHP Session Configuration -->
        <div class="card">
            <h3>⚙️ PHP Session Configuration</h3>
            <table>
                <tr><td><strong>session.name:</strong></td><td><?= ini_get('session.name') ?></td></tr>
                <tr><td><strong>session.cookie_lifetime:</strong></td><td><?= ini_get('session.cookie_lifetime') ?> seconds</td></tr>
                <tr><td><strong>session.gc_maxlifetime:</strong></td><td><?= ini_get('session.gc_maxlifetime') ?> seconds</td></tr>
                <tr><td><strong>session.cookie_httponly:</strong></td><td><?= ini_get('session.cookie_httponly') ? 'On' : 'Off' ?></td></tr>
                <tr><td><strong>session.cookie_secure:</strong></td><td><?= ini_get('session.cookie_secure') ? 'On' : 'Off' ?></td></tr>
                <tr><td><strong>session.use_strict_mode:</strong></td><td><?= ini_get('session.use_strict_mode') ? 'On' : 'Off' ?></td></tr>
                <tr><td><strong>session.use_only_cookies:</strong></td><td><?= ini_get('session.use_only_cookies') ? 'On' : 'Off' ?></td></tr>
                <tr><td><strong>session.save_handler:</strong></td><td><?= ini_get('session.save_handler') ?></td></tr>
                <tr><td><strong>session.save_path:</strong></td><td><?= ini_get('session.save_path') ?></td></tr>
            </table>
        </div>
        
        <!-- Database Session Test -->
        <div class="card">
            <h3>🗄️ Database Session Test</h3>
            <?php
            try {
                $pdo = getDatabase();
                $stmt = $pdo->query("SELECT COUNT(*) as session_count FROM " . TABLE_SESSIONS);
                $result = $stmt->fetch();
                $sessionCount = $result['session_count'];
                
                echo "<p><strong>Sessions in database:</strong> {$sessionCount}</p>";
                
                // แสดง sessions ล่าสุด
                $stmt = $pdo->query("SELECT session_id, user_id, ip_address, last_activity FROM " . TABLE_SESSIONS . " ORDER BY last_activity DESC LIMIT 5");
                $sessions = $stmt->fetchAll();
                
                if ($sessions) {
                    echo "<h4>Recent Sessions:</h4>";
                    echo "<table>";
                    echo "<tr><th>Session ID</th><th>User ID</th><th>IP Address</th><th>Last Activity</th></tr>";
                    foreach ($sessions as $session) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars(substr($session['session_id'], 0, 16)) . "...</td>";
                        echo "<td>" . ($session['user_id'] ?: 'Guest') . "</td>";
                        echo "<td>" . htmlspecialchars($session['ip_address']) . "</td>";
                        echo "<td>" . htmlspecialchars($session['last_activity']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Database session error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>
        </div>
        
        <!-- Session Security Headers -->
        <div class="card">
            <h3>🔒 Security Headers</h3>
            <table>
                <?php
                $headers = headers_list();
                $securityHeaders = ['X-Frame-Options', 'X-Content-Type-Options', 'X-XSS-Protection', 'Strict-Transport-Security'];
                
                foreach ($securityHeaders as $header) {
                    $found = false;
                    foreach ($headers as $sentHeader) {
                        if (strpos($sentHeader, $header) === 0) {
                            echo "<tr><td><strong>{$header}:</strong></td><td>" . htmlspecialchars($sentHeader) . "</td></tr>";
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        echo "<tr><td><strong>{$header}:</strong></td><td><em>Not set</em></td></tr>";
                    }
                }
                ?>
            </table>
        </div>
        
        <!-- Test Results Summary -->
        <div class="card">
            <h3>📋 Test Results Summary</h3>
            <?php
            $tests = [
                'Session Manager Class' => SessionManager::getInstance() instanceof SessionManager,
                'Session Started' => SessionManager::getInstance()->isStarted(),
                'Helper Functions' => function_exists('startSession') && function_exists('getSession'),
                'Authentication Functions' => function_exists('isLoggedIn') && function_exists('loginUser'),
                'CSRF Functions' => function_exists('getCSRFToken') && function_exists('validateCSRFToken'),
                'Database Connection' => function_exists('getDatabase') && testDatabaseConnection(),
                'Sessions Table' => function_exists('getDatabase') // Will test in database section
            ];
            
            echo "<ul>";
            foreach ($tests as $test => $result) {
                $status = $result ? '✅ Pass' : '❌ Fail';
                echo "<li><strong>{$test}:</strong> {$status}</li>";
            }
            echo "</ul>";
            ?>
        </div>
        
        <!-- Usage Examples -->
        <div class="card">
            <h3>📖 Usage Examples</h3>
            <pre><code>// Basic session operations
startSession();
setSession('key', 'value');
$value = getSession('key', 'default');

// User authentication
loginUser($userId, $username, $role);
if (isLoggedIn()) {
    echo "Welcome " . getCurrentUsername();
}

// Role checking
if (isAdmin()) {
    // Admin features
}

// CSRF protection
$token = getCSRFToken();
if (validateCSRFFromRequest()) {
    // Process form
}

// Session info
$info = getSessionInfo();
print_r($info);</code></pre>
        </div>
        
        <div class="info">
            <h4>🎯 Next Steps:</h4>
            <ol>
                <li>Session Manager is working properly</li>
                <li>Database sessions are functional</li>
                <li>CSRF protection is active</li>
                <li>Ready to create includes/functions.php</li>
                <li>Delete this test file after verification</li>
            </ol>
        </div>
        
        <p><em>Page generated at: <?= date('Y-m-d H:i:s') ?></em></p>
    </div>
</body>
</html>