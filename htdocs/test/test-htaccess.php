<?php
/**
 * .htaccess Test File
 * ทดสอบการทำงานของ .htaccess
 */

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>.htaccess Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e6f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .card { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .test-link { display: inline-block; margin: 5px; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .test-link:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 .htaccess Configuration Test</h1>
        
        <div class="success">✅ .htaccess is working! (This page loaded via rewrite rules)</div>
        
        <!-- Server Information -->
        <div class="card">
            <h3>🖥️ Server Information</h3>
            <table>
                <tr><td><strong>Server Software:</strong></td><td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td></tr>
                <tr><td><strong>Apache Version:</strong></td><td><?= apache_get_version() ?? 'Not available' ?></td></tr>
                <tr><td><strong>Request Method:</strong></td><td><?= $_SERVER['REQUEST_METHOD'] ?></td></tr>
                <tr><td><strong>Request URI:</strong></td><td><?= $_SERVER['REQUEST_URI'] ?></td></tr>
                <tr><td><strong>Query String:</strong></td><td><?= $_SERVER['QUERY_STRING'] ?: 'None' ?></td></tr>
                <tr><td><strong>HTTPS:</strong></td><td><?= isset($_SERVER['HTTPS']) ? '✅ Yes' : '❌ No' ?></td></tr>
            </table>
        </div>
        
        <!-- Security Headers Check -->
        <div class="card">
            <h3>🔒 Security Headers Test</h3>
            <table>
                <tr><th>Header</th><th>Status</th><th>Value</th></tr>
                <?php
                $securityHeaders = [
                    'X-Frame-Options' => 'DENY',
                    'X-Content-Type-Options' => 'nosniff',
                    'X-XSS-Protection' => '1; mode=block',
                    'Referrer-Policy' => 'strict-origin-when-cross-origin',
                    'Content-Security-Policy' => 'Set'
                ];
                
                foreach ($securityHeaders as $header => $expected) {
                    $headers = headers_list();
                    $found = false;
                    $value = '';
                    
                    foreach ($headers as $sentHeader) {
                        if (stripos($sentHeader, $header) === 0) {
                            $found = true;
                            $value = substr($sentHeader, strlen($header) + 2);
                            break;
                        }
                    }
                    
                    $status = $found ? '✅ Set' : '❌ Missing';
                    echo "<tr><td><strong>{$header}</strong></td><td>{$status}</td><td>" . htmlspecialchars($value) . "</td></tr>";
                }
                ?>
            </table>
        </div>
        
        <!-- URL Rewriting Test -->
        <div class="card">
            <h3>🔗 URL Rewriting Test</h3>
            <p>Test these clean URLs (should work without .php extension):</p>
            
            <div class="grid">
                <div>
                    <h4>Public Routes:</h4>
                    <a href="/home" class="test-link">Home</a><br>
                    <a href="/login" class="test-link">Login</a><br>
                    <a href="/register" class="test-link">Register</a><br>
                </div>
                
                <div>
                    <h4>Dashboard Routes:</h4>
                    <a href="/dashboard" class="test-link">Dashboard</a><br>
                    <a href="/dashboard/files" class="test-link">Files</a><br>
                    <a href="/dashboard/folders" class="test-link">Folders</a><br>
                </div>
                
                <div>
                    <h4>Admin Routes:</h4>
                    <a href="/admin" class="test-link">Admin</a><br>
                    <a href="/admin/users" class="test-link">Users</a><br>
                    <a href="/admin/system" class="test-link">System</a><br>
                </div>