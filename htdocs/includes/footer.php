<?php
/**
 * Footer Template for File Share Hub
 * AdminLTE 3 Compatible Footer
 * 
 * @author File Share Hub
 * @version 1.0
 * @created 2025
 */

// ป้องกันการเรียกใช้โดยตรง
if (!defined('FILE_SHARE_HUB')) {
    die('Direct access not permitted');
}

// รับข้อมูลระบบ
$siteTitle = getSystemSetting('site_name', APP_NAME);
$currentYear = date('Y');
$buildDate = getSystemSetting('last_updated', date('Y-m-d H:i:s'));
$isLoggedIn = isLoggedIn();
?>

                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="row">
                <!-- Left side -->
                <div class="col-md-6">
                    <strong>
                        <?= t('copyright') ?> &copy; <?= $currentYear ?> 
                        <a href="<?= getFullUrl('/') ?>" class="text-decoration-none">
                            <?= htmlspecialchars($siteTitle) ?>
                        </a>
                    </strong>
                    <?= t('all_rights_reserved') ?>
                </div>
                
                <!-- Right side -->
                <div class="col-md-6 text-right">
                    <div class="d-inline-block">
                        <b><?= t('version') ?></b> <?= APP_VERSION ?>
                        <span class="text-muted ml-2">
                            <?= t('build') ?> <?= VERSION_BUILD ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Additional Footer Info -->
            <div class="row mt-2">
                <div class="col-md-6">
                    <small class="text-muted">
                        <?= t('powered_by') ?> 
                        <a href="https://adminlte.io" target="_blank" class="text-decoration-none">AdminLTE</a> &amp; 
                        <a href="https://getbootstrap.com" target="_blank" class="text-decoration-none">Bootstrap</a>
                    </small>
                </div>
                
                <div class="col-md-6 text-right">
                    <small class="text-muted">
                        <?= t('last_updated') ?>: <?= formatThaiDate($buildDate, 'd/m/Y H:i') ?>
                    </small>
                </div>
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
            <div class="p-3">
                <h5><?= t('settings') ?></h5>
                
                <!-- Theme Settings -->
                <div class="mb-4">
                    <h6><?= t('appearance') ?></h6>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="darkModeToggle">
                            <label class="custom-control-label" for="darkModeToggle">
                                <?= t('dark_mode') ?>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Language Settings -->
                <div class="mb-4">
                    <h6><?= t('language') ?></h6>
                    <div class="form-group">
                        <select class="form-control form-control-sm" id="languageSelect">
                            <option value="th" <?= getSession('language', 'th') === 'th' ? 'selected' : '' ?>>
                                🇹🇭 <?= t('thai') ?>
                            </option>
                            <option value="en" <?= getSession('language', 'th') === 'en' ? 'selected' : '' ?>>
                                🇺🇸 <?= t('english') ?>
                            </option>
                        </select>
                    </div>
                </div>
                
                <?php if ($isLoggedIn): ?>
                <!-- Quick Actions -->
                <div class="mb-4">
                    <h6><?= t('quick_actions') ?></h6>
                    <div class="d-grid gap-2">
                        <a href="<?= getFullUrl('/dashboard/upload.php') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-cloud-upload-alt"></i> <?= t('upload_files') ?>
                        </a>
                        <a href="<?= getFullUrl('/dashboard/folders.php') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-folder-plus"></i> <?= t('create_folder') ?>
                        </a>
                        <?php if (isAdmin()): ?>
                        <a href="<?= getFullUrl('/admin/') ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-cogs"></i> <?= t('admin_panel') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Storage Info -->
                <div class="mb-4">
                    <h6><?= t('storage_info') ?></h6>
                    <?php 
                    $currentUser = getUserById(getCurrentUserId());
                    $storagePercent = $currentUser['storage_limit'] > 0 ? 
                        round(($currentUser['storage_used'] / $currentUser['storage_limit']) * 100, 1) : 0;
                    $progressClass = $storagePercent > 90 ? 'bg-danger' : ($storagePercent > 70 ? 'bg-warning' : 'bg-success');
                    ?>
                    <div class="progress progress-sm mb-2">
                        <div class="progress-bar <?= $progressClass ?>" 
                             style="width: <?= min($storagePercent, 100) ?>%"></div>
                    </div>
                    <small class="text-muted">
                        <?= formatFileSize($currentUser['storage_used']) ?> / 
                        <?= formatFileSize($currentUser['storage_limit']) ?>
                        <br>
                        <?= t('files') ?>: <?= number_format($currentUser['file_count']) ?>/<?= number_format($currentUser['max_files']) ?>
                    </small>
                </div>
                <?php endif; ?>
                
                <!-- Help & Support -->
                <div class="mb-4">
                    <h6><?= t('help_support') ?></h6>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action py-2" data-toggle="modal" data-target="#helpModal">
                            <i class="fas fa-question-circle text-info"></i> <?= t('help') ?>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-2" data-toggle="modal" data-target="#aboutModal">
                            <i class="fas fa-info-circle text-primary"></i> <?= t('about_us') ?>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-2" onclick="reportBug()">
                            <i class="fas fa-bug text-danger"></i> <?= t('report_bug') ?>
                        </a>
                    </div>
                </div>
            </div>
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">
                        <i class="fas fa-question-circle"></i> <?= t('help') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-cloud-upload-alt text-primary"></i> <?= t('upload_files') ?></h6>
                            <p class="small text-muted">
                                <?= t('help_upload_desc') ?>
                            </p>
                            
                            <h6><i class="fas fa-share-alt text-success"></i> <?= t('share_files') ?></h6>
                            <p class="small text-muted">
                                <?= t('help_share_desc') ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-folder text-warning"></i> <?= t('organize_files') ?></h6>
                            <p class="small text-muted">
                                <?= t('help_organize_desc') ?>
                            </p>
                            
                            <h6><i class="fas fa-shield-alt text-info"></i> <?= t('security') ?></h6>
                            <p class="small text-muted">
                                <?= t('help_security_desc') ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Keyboard Shortcuts -->
                    <hr>
                    <h6><i class="fas fa-keyboard"></i> <?= t('keyboard_shortcuts') ?></h6>
                    <div class="row">
                        <div class="col-md-6">
                            <small>
                                <kbd>Ctrl</kbd> + <kbd>/</kbd> - <?= t('focus_search') ?><br>
                                <kbd>Esc</kbd> - <?= t('close_modals') ?><br>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small>
                                <kbd>Ctrl</kbd> + <kbd>U</kbd> - <?= t('quick_upload') ?><br>
                                <kbd>F11</kbd> - <?= t('fullscreen') ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <?= t('close') ?>
                    </button>
                    <a href="<?= getFullUrl('/help/') ?>" class="btn btn-primary">
                        <i class="fas fa-book"></i> <?= t('full_documentation') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- About Modal -->
    <div class="modal fade" id="aboutModal" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aboutModalLabel">
                        <i class="fas fa-info-circle"></i> <?= t('about') ?> <?= htmlspecialchars($siteTitle) ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img src="<?= getAssetUrl('logo.png', 'img') ?>" alt="<?= htmlspecialchars($siteTitle) ?>" 
                         class="img-fluid mb-3" style="max-width: 150px;">
                    
                    <h4><?= htmlspecialchars($siteTitle) ?></h4>
                    <p class="text-muted"><?= htmlspecialchars(APP_DESCRIPTION) ?></p>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <strong><?= t('version') ?></strong><br>
                            <span class="text-muted"><?= APP_VERSION ?></span>
                        </div>
                        <div class="col-4">
                            <strong><?= t('release_date') ?></strong><br>
                            <span class="text-muted"><?= RELEASE_DATE ?></span>
                        </div>
                        <div class="col-4">
                            <strong><?= t('codename') ?></strong><br>
                            <span class="text-muted"><?= RELEASE_CODENAME ?></span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <p class="small text-muted">
                        <?= t('developed_by') ?> <?= htmlspecialchars(APP_AUTHOR) ?><br>
                        <?= t('powered_by') ?> PHP, MySQL, AdminLTE, Bootstrap
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <?= t('close') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Footer JavaScript -->
    <script>
        // Dark mode toggle
        $('#darkModeToggle').on('change', function() {
            let isDark = $(this).is(':checked');
            $('body').toggleClass('dark-mode', isDark);
            
            // Save preference
            localStorage.setItem('darkMode', isDark);
            
            // Show toast
            window.fileShareHub.utils.showToast(
                isDark ? t('dark_mode_enabled') : t('dark_mode_disabled'), 
                'success'
            );
        });

        // Load dark mode preference
        $(document).ready(function() {
            let isDark = localStorage.getItem('darkMode') === 'true';
            $('#darkModeToggle').prop('checked', isDark);
            $('body').toggleClass('dark-mode', isDark);
        });

        // Language selector
        $('#languageSelect').on('change', function() {
            let lang = $(this).val();
            let url = new URL(window.location);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        });

        // Report bug function
        function reportBug() {
            let bugReport = {
                url: window.location.href,
                userAgent: navigator.userAgent,
                timestamp: new Date().toISOString(),
                userId: window.fileShareHub.userId,
                language: window.fileShareHub.language
            };
            
            let subject = encodeURIComponent(`Bug Report - ${window.location.pathname}`);
            let body = encodeURIComponent(`
Please describe the bug:

Technical Information:
- URL: ${bugReport.url}
- Browser: ${bugReport.userAgent}
- Time: ${bugReport.timestamp}
- User ID: ${bugReport.userId || 'Guest'}
- Language: ${bugReport.language}
            `);
            
            let contactEmail = '<?= getSystemSetting("contact_email", "admin@yourdomain.com") ?>';
            window.open(`mailto:${contactEmail}?subject=${subject}&body=${body}`);
        }

        // Additional keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl+U for quick upload
            if (e.ctrlKey && e.key === 'u' && window.fileShareHub.isLoggedIn) {
                e.preventDefault();
                window.location.href = window.fileShareHub.baseUrl + '/dashboard/upload.php';
            }
            
            // Ctrl+H for help
            if (e.ctrlKey && e.key === 'h') {
                e.preventDefault();
                $('#helpModal').modal('show');
            }
        });

        // Auto-save form data (for important forms)
        $('form[data-autosave]').each(function() {
            let form = $(this);
            let formId = form.attr('id') || 'form_' + Math.random().toString(36).substr(2, 9);
            
            // Load saved data
            let savedData = localStorage.getItem('autosave_' + formId);
            if (savedData) {
                try {
                    let data = JSON.parse(savedData);
                    Object.keys(data).forEach(function(key) {
                        form.find('[name="' + key + '"]').val(data[key]);
                    });
                } catch (e) {
                    console.error('Error loading autosaved data:', e);
                }
            }
            
            // Save data on change
            form.on('change input', function() {
                let formData = {};
                form.find('input, textarea, select').each(function() {
                    let input = $(this);
                    if (input.attr('name') && input.attr('type') !== 'password') {
                        formData[input.attr('name')] = input.val();
                    }
                });
                localStorage.setItem('autosave_' + formId, JSON.stringify(formData));
            });
            
            // Clear saved data on successful submit
            form.on('submit', function() {
                setTimeout(function() {
                    localStorage.removeItem('autosave_' + formId);
                }, 1000);
            });
        });

        // Performance monitoring
        $(window).on('load', function() {
            if (window.performance && window.performance.timing) {
                let loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
                if (loadTime > 5000) { // If page takes more than 5 seconds
                    console.warn('Slow page load detected:', loadTime + 'ms');
                }
            }
        });

        // Service worker registration (for future PWA support)
        if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
            navigator.serviceWorker.register('/sw.js').catch(function(error) {
                console.log('ServiceWorker registration failed:', error);
            });
        }

        // Add to home screen prompt (PWA)
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show install button or banner
            console.log('App can be installed');
        });

        // Cleanup on page unload
        $(window).on('beforeunload', function() {
            // Cancel any pending AJAX requests
            if (window.activeAjaxRequests) {
                window.activeAjaxRequests.forEach(function(xhr) {
                    if (xhr.readyState !== 4) {
                        xhr.abort();
                    }
                });
            }
        });

        // Final initialization
        console.log('🚀 File Share Hub initialized successfully');
        console.log('Version:', '<?= APP_VERSION ?>');
        console.log('Build:', '<?= VERSION_BUILD ?>');
        console.log('Environment:', '<?= ENVIRONMENT ?>');
        
        // Add some Thai translations for JavaScript-only messages
        <?php if (!isset($translations['dark_mode_enabled'])): ?>
        window.fileShareHub.translations.dark_mode_enabled = 'เปิดใช้งานโหมดมืดแล้ว';
        window.fileShareHub.translations.dark_mode_disabled = 'ปิดใช้งานโหมดมืดแล้ว';
        window.fileShareHub.translations.focus_search = 'โฟกัสการค้นหา';
        window.fileShareHub.translations.close_modals = 'ปิดหน้าต่าง';
        window.fileShareHub.translations.quick_upload = 'อัพโหลดด่วน';
        window.fileShareHub.translations.fullscreen = 'เต็มจอ';
        window.fileShareHub.translations.appearance = 'การแสดงผล';
        window.fileShareHub.translations.dark_mode = 'โหมดมืด';
        window.fileShareHub.translations.quick_actions = 'การดำเนินการด่วน';
        window.fileShareHub.translations.storage_info = 'ข้อมูลพื้นที่เก็บข้อมูล';
        window.fileShareHub.translations.help_support = 'ช่วยเหลือและสนับสนุน';
        window.fileShareHub.translations.keyboard_shortcuts = 'ทางลัดแป้นพิมพ์';
        window.fileShareHub.translations.full_documentation = 'เอกสารฉบับเต็ม';
        window.fileShareHub.translations.about = 'เกี่ยวกับ';
        window.fileShareHub.translations.codename = 'ชื่อรหัส';
        window.fileShareHub.translations.help_upload_desc = 'ลากและวางไฟล์หรือคลิกเพื่อเลือกไฟล์ที่ต้องการอัพโหลด';
        window.fileShareHub.translations.help_share_desc = 'สร้างลิงค์แชร์พร้อมตั้งรหัสผ่านและวันหมดอายุ';
        window.fileShareHub.translations.help_organize_desc = 'สร้างโฟลเดอร์และจัดระเบียบไฟล์ของคุณ';
        window.fileShareHub.translations.help_security_desc = 'ไฟล์ของคุณปลอดภัยด้วยการเข้ารหัสและการควบคุมการเข้าถึง';
        window.fileShareHub.translations.organize_files = 'จัดระเบียบไฟล์';
        <?php endif; ?>
    </script>

    <!-- Page-specific cleanup JavaScript -->
    <?php if (isset($cleanupJS)): ?>
        <script>
            <?= $cleanupJS ?>
        </script>
    <?php endif; ?>

</body>
</html>