<?php
/**
 * Header Template for File Share Hub
 * AdminLTE 3 + Thai Language Support
 * 
 * @author File Share Hub
 * @version 1.0
 * @created 2025
 */

// ป้องกันการเรียกใช้โดยตรง
if (!defined('FILE_SHARE_HUB')) {
    die('Direct access not permitted');
}

// รวมไฟล์ที่จำเป็น
require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/session.php';
require_once INCLUDES_PATH . '/functions.php';

// เริ่ม session
startSession();

// ตรวจสอบ maintenance mode
if (getSystemSetting('maintenance_mode', false) && !isAdmin()) {
    include ROOT_PATH . '/maintenance.php';
    exit;
}

// รับข้อมูลผู้ใช้ปัจจุบัน
$currentUser = null;
$isLoggedIn = isLoggedIn();
if ($isLoggedIn) {
    $currentUser = getUserById(getCurrentUserId());
}

// ตั้งค่าภาษา
$currentLanguage = getSession('language', DEFAULT_LANGUAGE);
if ($currentUser && $currentUser['language']) {
    $currentLanguage = $currentUser['language'];
}

// โหลดไฟล์ภาษา
$langFile = LANG_PATH . "/{$currentLanguage}.php";
$translations = file_exists($langFile) ? include $langFile : [];

// ตั้งค่าหน้าปัจจุบัน
$currentPage = $_GET['page'] ?? basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $_GET['title'] ?? $currentPage;

// สถิติผู้ใช้
$userStats = [];
if ($isLoggedIn) {
    $userStats = [
        'storage_used' => $currentUser['storage_used'],
        'storage_limit' => $currentUser['storage_limit'],
        'file_count' => $currentUser['file_count'],
        'max_files' => $currentUser['max_files']
    ];
}

// ตั้งค่าหน้า
$siteTitle = getSystemSetting('site_name', APP_NAME);
$siteDescription = getSystemSetting('site_description', APP_DESCRIPTION);
$allowRegistration = getSystemSetting('enable_registration', true);
?>
<!DOCTYPE html>
<html lang="<?= $currentLanguage ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($siteDescription) ?>">
    <meta name="author" content="<?= htmlspecialchars(APP_AUTHOR) ?>">
    <meta name="csrf-token" content="<?= getCSRFToken() ?>">
    
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($siteTitle) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= getAssetUrl('favicon.ico', 'img') ?>">
    
    <!-- Google Font: Sarabun (Thai) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE Theme style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    
    <!-- Custom CSS for Thai Language -->
    <link rel="stylesheet" href="<?= getAssetUrl('thai-style.css', 'css') ?>">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= getAssetUrl('custom.css', 'css') ?>">
    
    <style>
        /* Thai Font Support */
        body, .sidebar, .navbar, .main-footer, .content-wrapper {
            font-family: 'Sarabun', 'Noto Sans Thai', sans-serif !important;
        }
        
        /* Custom Variables */
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        /* Header Customizations */
        .main-header .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 3px solid rgba(255,255,255,0.1);
        }
        
        .main-header .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
        }
        
        .main-header .navbar-nav .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        
        /* Sidebar Customizations */
        .main-sidebar .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        }
        
        .sidebar .nav-sidebar .nav-item .nav-link {
            color: rgba(255,255,255,0.8);
        }
        
        .sidebar .nav-sidebar .nav-item .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .sidebar .nav-sidebar .nav-item.menu-open > .nav-link,
        .sidebar .nav-sidebar .nav-item .nav-link.active {
            background-color: var(--primary-color);
            color: #fff;
        }
        
        /* User Panel */
        .user-panel {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 10px;
        }
        
        .user-panel .info a {
            color: #fff;
            text-decoration: none;
        }
        
        /* Storage Progress */
        .storage-info {
            padding: 10px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .storage-progress {
            margin-top: 5px;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .loading-spinner {
            color: #fff;
            font-size: 2rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-header h1 {
                font-size: 1.5rem;
            }
            
            .main-sidebar {
                margin-left: -250px;
            }
            
            .sidebar-open .main-sidebar {
                margin-left: 0;
            }
        }
    </style>
    
    <!-- Additional Page-specific CSS -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <div class="mt-2"><?= t('loading') ?></div>
        </div>
    </div>
    
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?= getFullUrl('/') ?>" class="nav-link">
                        <i class="fas fa-home"></i> <?= t('home') ?>
                    </a>
                </li>
                <?php if ($isLoggedIn): ?>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?= getFullUrl('/dashboard/') ?>" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i> <?= t('dashboard') ?>
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Search Form -->
            <form class="form-inline ml-3 d-none d-md-block">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-navbar" type="search" 
                           placeholder="<?= t('search_placeholder') ?>" aria-label="<?= t('search') ?>" id="globalSearch">
                    <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Language Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" title="<?= t('language') ?>">
                        <i class="fas fa-globe"></i>
                        <span class="d-none d-md-inline ml-1">
                            <?= $currentLanguage === 'th' ? 'ไทย' : 'EN' ?>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="?lang=th" class="dropdown-item <?= $currentLanguage === 'th' ? 'active' : '' ?>">
                            🇹🇭 <?= t('thai') ?>
                        </a>
                        <a href="?lang=en" class="dropdown-item <?= $currentLanguage === 'en' ? 'active' : '' ?>">
                            🇺🇸 <?= t('english') ?>
                        </a>
                    </div>
                </li>

                <?php if ($isLoggedIn): ?>
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" title="<?= t('notifications') ?>">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge" id="notificationCount">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-header"><?= t('notifications') ?></span>
                        <div class="dropdown-divider"></div>
                        <div id="notificationList">
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-info mr-2"></i> <?= t('no_notifications') ?>
                            </a>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer"><?= t('see_all') ?></a>
                    </div>
                </li>

                <!-- Upload Quick Button -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= getFullUrl('/dashboard/upload.php') ?>" title="<?= t('upload_files') ?>">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span class="d-none d-md-inline ml-1"><?= t('upload') ?></span>
                    </a>
                </li>

                <!-- User Account Dropdown -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $currentUser['profile_picture'] ? htmlspecialchars($currentUser['profile_picture']) : getAssetUrl('default-avatar.png', 'img') ?>" 
                             class="user-image img-circle elevation-2" alt="<?= htmlspecialchars($currentUser['username']) ?>">
                        <span class="d-none d-md-inline"><?= htmlspecialchars($currentUser['username']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header bg-primary">
                            <img src="<?= $currentUser['profile_picture'] ? htmlspecialchars($currentUser['profile_picture']) : getAssetUrl('default-avatar.png', 'img') ?>" 
                                 class="img-circle elevation-2" alt="<?= htmlspecialchars($currentUser['username']) ?>">
                            <p>
                                <?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>
                                <small><?= t($currentUser['user_role']) ?></small>
                                <small><?= t('member_since') ?> <?= formatThaiDate($currentUser['created_at'], 'd/m/Y') ?></small>
                            </p>
                        </li>
                        <!-- Menu Body -->
                        <li class="user-body">
                            <div class="row">
                                <div class="col-4 text-center">
                                    <a href="<?= getFullUrl('/dashboard/files.php') ?>" class="text-decoration-none">
                                        <span class="d-block font-weight-bold"><?= number_format($userStats['file_count']) ?></span>
                                        <small><?= t('files') ?></small>
                                    </a>
                                </div>
                                <div class="col-4 text-center">
                                    <a href="<?= getFullUrl('/dashboard/shared.php') ?>" class="text-decoration-none">
                                        <span class="d-block font-weight-bold" id="shareCount">0</span>
                                        <small><?= t('shares') ?></small>
                                    </a>
                                </div>
                                <div class="col-4 text-center">
                                    <span class="d-block font-weight-bold"><?= formatFileSize($userStats['storage_used']) ?></span>
                                    <small><?= t('storage_used') ?></small>
                                </div>
                            </div>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="<?= getFullUrl('/dashboard/profile.php') ?>" class="btn btn-default btn-flat">
                                <i class="fas fa-user"></i> <?= t('profile') ?>
                            </a>
                            <a href="<?= getFullUrl('/auth/logout.php') ?>" class="btn btn-default btn-flat float-right"
                               onclick="return confirm('<?= t('confirm_logout') ?>')">
                                <i class="fas fa-sign-out-alt"></i> <?= t('logout') ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                <!-- Guest User Links -->
                <li class="nav-item">
                    <a href="<?= getFullUrl('/auth/login.php') ?>" class="nav-link">
                        <i class="fas fa-sign-in-alt"></i> <?= t('login') ?>
                    </a>
                </li>
                <?php if ($allowRegistration): ?>
                <li class="nav-item">
                    <a href="<?= getFullUrl('/auth/register.php') ?>" class="nav-link">
                        <i class="fas fa-user-plus"></i> <?= t('register') ?>
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>

                <!-- Fullscreen Button -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="<?= t('fullscreen') ?>">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <?php if ($isLoggedIn): ?>
        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="<?= getFullUrl('/dashboard/') ?>" class="brand-link">
                <img src="<?= getAssetUrl('logo.png', 'img') ?>" alt="<?= htmlspecialchars($siteTitle) ?>" 
                     class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light"><?= htmlspecialchars($siteTitle) ?></span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="<?= $currentUser['profile_picture'] ? htmlspecialchars($currentUser['profile_picture']) : getAssetUrl('default-avatar.png', 'img') ?>" 
                             class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="<?= getFullUrl('/dashboard/profile.php') ?>" class="d-block">
                            <?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>
                        </a>
                        <small class="text-muted"><?= t($currentUser['user_role']) ?></small>
                    </div>
                </div>

                <!-- Storage Info -->
                <div class="storage-info">
                    <small class="text-light"><?= t('storage_usage') ?></small>
                    <div class="storage-progress">
                        <?php 
                        $storagePercent = $userStats['storage_limit'] > 0 ? 
                            round(($userStats['storage_used'] / $userStats['storage_limit']) * 100, 1) : 0;
                        $progressClass = $storagePercent > 90 ? 'bg-danger' : ($storagePercent > 70 ? 'bg-warning' : 'bg-success');
                        ?>
                        <div class="progress progress-sm">
                            <div class="progress-bar <?= $progressClass ?>" 
                                 style="width: <?= min($storagePercent, 100) ?>%"></div>
                        </div>
                        <small class="text-light">
                            <?= formatFileSize($userStats['storage_used']) ?> / 
                            <?= formatFileSize($userStats['storage_limit']) ?> 
                            (<?= $storagePercent ?>%)
                        </small>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/dashboard/') ?>" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p><?= t('dashboard') ?></p>
                            </a>
                        </li>
                        
                        <!-- Files -->
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/dashboard/files.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-file"></i>
                                <p><?= t('my_files') ?></p>
                            </a>
                        </li>
                        
                        <!-- Folders -->
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/dashboard/folders.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-folder"></i>
                                <p><?= t('my_folders') ?></p>
                            </a>
                        </li>
                        
                        <!-- Shared Links -->
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/dashboard/shared.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-share-alt"></i>
                                <p><?= t('shared_links') ?></p>
                            </a>
                        </li>
                        
                        <!-- Upload -->
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/dashboard/upload.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-cloud-upload-alt"></i>
                                <p><?= t('upload_files') ?></p>
                            </a>
                        </li>

                        <!-- Admin Menu Section -->
                        <?php if (isAdmin()): ?>
                        <li class="nav-header"><?= t('admin_panel') ?></li>
                        
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/admin/') ?>" class="nav-link">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p><?= t('admin_dashboard') ?></p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/admin/users.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p><?= t('manage_users') ?></p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/admin/files.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-folder-open"></i>
                                <p><?= t('manage_files') ?></p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/admin/deleted-files.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-trash-alt"></i>
                                <p><?= t('deleted_files') ?></p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/admin/settings.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-wrench"></i>
                                <p><?= t('system_settings') ?></p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/admin/logs.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-list-alt"></i>
                                <p><?= t('activity_logs') ?></p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= getFullUrl('/admin/system-info.php') ?>" class="nav-link">
                                <i class="nav-icon fas fa-info-circle"></i>
                                <p><?= t('system_info') ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>
        <?php endif; ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper <?= !$isLoggedIn ? 'ml-0' : '' ?>">
            <!-- Content Header (Page header) -->
            <?php if (!isset($hideContentHeader) || !$hideContentHeader): ?>
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?= htmlspecialchars($pageTitle) ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                                    <?= generateBreadcrumb($breadcrumbs) ?>
                                <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?= getFullUrl('/') ?>"><?= t('home') ?></a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle) ?></li>
                                <?php endif; ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Flash Messages -->
                    <?php if (hasSession('flash_message')): ?>
                        <?php $flash = getSession('flash_message'); removeSession('flash_message'); ?>
                        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="icon fas fa-<?= $flash['type'] === 'success' ? 'check' : ($flash['type'] === 'error' ? 'ban' : 'info') ?>"></i> 
                                <?= t($flash['type']) ?>
                            </h5>
                            <?= htmlspecialchars($flash['message']) ?>
                        </div>
                    <?php endif; ?>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

    <script>
        // Global JavaScript configurations
        window.fileShareHub = {
            baseUrl: '<?= SITE_URL ?>',
            language: '<?= $currentLanguage ?>',
            csrfToken: '<?= getCSRFToken() ?>',
            isLoggedIn: <?= $isLoggedIn ? 'true' : 'false' ?>,
            userId: <?= $isLoggedIn ? getCurrentUserId() : 'null' ?>,
            userRole: '<?= $isLoggedIn ? getCurrentUserRole() : '' ?>',
            translations: <?= json_encode($translations, JSON_UNESCAPED_UNICODE) ?>
        };

        // Translation function
        function t(key, params = {}) {
            let text = window.fileShareHub.translations[key] || key;
            for (let param in params) {
                text = text.replace('{' + param + '}', params[param]);
            }
            return text;
        }

        // Show loading overlay
        function showLoading() {
            $('#loadingOverlay').css('display', 'flex');
        }

        // Hide loading overlay
        function hideLoading() {
            $('#loadingOverlay').hide();
        }

        // AJAX setup with CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': window.fileShareHub.csrfToken
            },
            beforeSend: function() {
                showLoading();
            },
            complete: function() {
                hideLoading();
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                if (xhr.status === 401) {
                    window.location.href = window.fileShareHub.baseUrl + '/auth/login.php';
                }
            }
        });

        // Global search functionality
        $('#globalSearch').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                let query = $(this).val().trim();
                if (query) {
                    window.location.href = window.fileShareHub.baseUrl + '/dashboard/search.php?q=' + encodeURIComponent(query);
                }
            }
        });

        // Language switcher
        $('a[href*="?lang="]').on('click', function(e) {
            e.preventDefault();
            let lang = $(this).attr('href').split('=')[1];
            let url = new URL(window.location);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        });

        // Auto-refresh notifications
        function loadNotifications() {
            if (!window.fileShareHub.isLoggedIn) return;
            
            $.get(window.fileShareHub.baseUrl + '/api/notifications.php')
                .done(function(response) {
                    if (response.success) {
                        updateNotificationBadge(response.data.unread_count);
                        updateNotificationList(response.data.notifications);
                    }
                })
                .fail(function() {
                    console.log('Failed to load notifications');
                });
        }

        function updateNotificationBadge(count) {
            let badge = $('#notificationCount');
            if (count > 0) {
                badge.text(count > 99 ? '99+' : count).show();
            } else {
                badge.hide();
            }
        }

        function updateNotificationList(notifications) {
            let list = $('#notificationList');
            list.empty();
            
            if (notifications.length === 0) {
                list.append('<a href="#" class="dropdown-item"><i class="fas fa-info mr-2"></i> ' + t('no_notifications') + '</a>');
            } else {
                notifications.forEach(function(notification) {
                    let icon = getNotificationIcon(notification.type);
                    let timeAgo = formatTimeAgo(notification.created_at);
                    list.append(`
                        <a href="#" class="dropdown-item ${notification.read ? '' : 'font-weight-bold'}">
                            <i class="${icon} mr-2"></i> ${notification.message}
                            <span class="float-right text-muted text-sm">${timeAgo}</span>
                        </a>
                    `);
                });
            }
        }

        function getNotificationIcon(type) {
            const icons = {
                'upload': 'fas fa-cloud-upload-alt text-info',
                'share': 'fas fa-share-alt text-primary',
                'download': 'fas fa-download text-success',
                'delete': 'fas fa-trash text-danger',
                'admin': 'fas fa-cog text-warning',
                'system': 'fas fa-info-circle text-info'
            };
            return icons[type] || 'fas fa-bell text-secondary';
        }

        function formatTimeAgo(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return t('just_now');
            if (diffInSeconds < 3600) return t('minutes_ago', {count: Math.floor(diffInSeconds / 60)});
            if (diffInSeconds < 86400) return t('hours_ago', {count: Math.floor(diffInSeconds / 3600)});
            if (diffInSeconds < 2592000) return t('days_ago', {count: Math.floor(diffInSeconds / 86400)});
            if (diffInSeconds < 31536000) return t('months_ago', {count: Math.floor(diffInSeconds / 2592000)});
            return t('years_ago', {count: Math.floor(diffInSeconds / 31536000)});
        }

        // Load user statistics
        function loadUserStats() {
            if (!window.fileShareHub.isLoggedIn) return;
            
            $.get(window.fileShareHub.baseUrl + '/api/user-stats.php')
                .done(function(response) {
                    if (response.success) {
                        $('#shareCount').text(response.data.share_count);
                        updateStorageInfo(response.data.storage);
                    }
                });
        }

        function updateStorageInfo(storage) {
            let percent = storage.limit > 0 ? Math.round((storage.used / storage.limit) * 100) : 0;
            let progressClass = percent > 90 ? 'bg-danger' : (percent > 70 ? 'bg-warning' : 'bg-success');
            
            $('.storage-progress .progress-bar')
                .removeClass('bg-success bg-warning bg-danger')
                .addClass(progressClass)
                .css('width', Math.min(percent, 100) + '%');
                
            $('.storage-progress small').html(
                formatFileSize(storage.used) + ' / ' + 
                formatFileSize(storage.limit) + ' (' + percent + '%)'
            );
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Flash message auto-hide
        $('.alert').each(function() {
            let alert = $(this);
            if (alert.hasClass('alert-success') || alert.hasClass('alert-info')) {
                setTimeout(function() {
                    alert.fadeOut();
                }, 5000);
            }
        });

        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl+/ for search focus
            if (e.ctrlKey && e.key === '/') {
                e.preventDefault();
                $('#globalSearch').focus();
            }
            
            // Escape to close modals
            if (e.key === 'Escape') {
                $('.modal').modal('hide');
                $('.dropdown-menu').dropdown('hide');
            }
        });

        // Initialize on document ready
        $(document).ready(function() {
            // Load initial data
            loadNotifications();
            loadUserStats();
            
            // Set up periodic refresh
            if (window.fileShareHub.isLoggedIn) {
                setInterval(loadNotifications, 30000); // Every 30 seconds
                setInterval(loadUserStats, 60000); // Every minute
            }
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Initialize popovers
            $('[data-toggle="popover"]').popover();
            
            // Auto-focus first input in modals
            $('.modal').on('shown.bs.modal', function() {
                $(this).find('input:text:visible:first').focus();
            });
            
            // Prevent double form submission
            $('form').on('submit', function() {
                $(this).find('button[type="submit"]').prop('disabled', true);
                showLoading();
            });
            
            // Handle file drag and drop on body
            $(document).on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('body').addClass('drag-active');
            });
            
            $(document).on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('body').removeClass('drag-active');
            });
            
            $(document).on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('body').removeClass('drag-active');
                
                // Redirect to upload page if files dropped
                if (e.originalEvent.dataTransfer.files.length > 0) {
                    window.location.href = window.fileShareHub.baseUrl + '/dashboard/upload.php';
                }
            });
        });
        
        // Global utility functions
        window.fileShareHub.utils = {
            showLoading: showLoading,
            hideLoading: hideLoading,
            t: t,
            formatFileSize: formatFileSize,
            formatTimeAgo: formatTimeAgo,
            
            // Show toast notification
            showToast: function(message, type = 'info', duration = 5000) {
                let toastClass = 'bg-' + type;
                let iconClass = type === 'success' ? 'fa-check' : 
                               type === 'error' || type === 'danger' ? 'fa-times' : 
                               type === 'warning' ? 'fa-exclamation' : 'fa-info';
                
                let toast = $(`
                    <div class="toast ${toastClass}" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                        <div class="toast-header">
                            <i class="fas ${iconClass} mr-2"></i>
                            <strong class="mr-auto">${t(type)}</strong>
                            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="toast-body text-white">
                            ${message}
                        </div>
                    </div>
                `);
                
                $('body').append(toast);
                toast.toast({delay: duration}).toast('show');
                
                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            },
            
            // Confirm dialog
            confirm: function(message, callback) {
                if (confirm(message)) {
                    callback();
                }
            },
            
            // Copy to clipboard
            copyToClipboard: function(text) {
                navigator.clipboard.writeText(text).then(function() {
                    window.fileShareHub.utils.showToast(t('link_copied'), 'success');
                }).catch(function() {
                    // Fallback for older browsers
                    let textArea = document.createElement('textarea');
                    textArea.value = text;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    window.fileShareHub.utils.showToast(t('link_copied'), 'success');
                });
            }
        };
    </script>

    <!-- Additional Page-specific JavaScript -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline JavaScript -->
    <?php if (isset($inlineJS)): ?>
        <script>
            <?= $inlineJS ?>
        </script>
    <?php endif; ?>