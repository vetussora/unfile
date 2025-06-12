<?php
/**
 * Landing Page for File Share Hub
 * หน้าแรกของเว็บไซต์
 * 
 * @author File Share Hub
 * @version 1.0
 * @created 2025
 */

define('FILE_SHARE_HUB', true);

// รวมไฟล์ที่จำเป็น
require_once 'config/constants.php';
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

// เริ่ม session
startSession();

// ตรวจสอบ maintenance mode
if (getSystemSetting('maintenance_mode', false) && !isAdmin()) {
    include 'maintenance.php';
    exit;
}

// ถ้าล็อกอินแล้วให้ redirect ไป dashboard
if (isLoggedIn()) {
    header('Location: ' . getFullUrl('/dashboard/'));
    exit;
}

// รับข้อมูลระบบ
$siteTitle = getSystemSetting('site_name', APP_NAME);
$siteDescription = getSystemSetting('site_description', APP_DESCRIPTION);
$allowRegistration = getSystemSetting('enable_registration', true);
$maxFileSize = getSystemSetting('max_file_size', MAX_FILE_SIZE);
$supportedTypes = getSystemSetting('allowed_file_types', 'jpg,png,pdf,doc,zip');

// รับสถิติระบบ (ไม่ระบุตัวตน)
$systemStats = getPublicSystemStats();

// ตั้งค่าหน้า
$pageTitle = $siteTitle;
$hideContentHeader = true;

// ตัวแปร CSS และ JS เพิ่มเติม
$additionalCSS = [];
$additionalJS = [];

// รวมไฟล์ header
include 'includes/header.php';
?>

<!-- Landing Page Content -->
<div class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-background">
            <div class="hero-overlay"></div>
        </div>
        
        <div class="container-fluid">
            <div class="row min-vh-100 align-items-center">
                <div class="col-lg-6 offset-lg-1">
                    <div class="hero-content">
                        <!-- Logo -->
                        <div class="hero-logo mb-4">
                            <img src="<?= getAssetUrl('logo.png', 'img') ?>" 
                                 alt="<?= htmlspecialchars($siteTitle) ?>" 
                                 class="img-fluid"
                                 style="max-height: 80px;">
                        </div>
                        
                        <!-- Main Heading -->
                        <h1 class="hero-title">
                            <?= htmlspecialchars($siteTitle) ?>
                        </h1>
                        
                        <!-- Subtitle -->
                        <p class="hero-subtitle">
                            <?= htmlspecialchars($siteDescription) ?>
                        </p>
                        
                        <!-- Features List -->
                        <div class="hero-features">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="feature-item">
                                        <i class="fas fa-cloud-upload-alt text-primary"></i>
                                        <span>อัพโหลดไฟล์ง่าย รวดเร็ว</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="feature-item">
                                        <i class="fas fa-share-alt text-success"></i>
                                        <span>แชร์ไฟล์ปลอดภัย</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="feature-item">
                                        <i class="fas fa-lock text-warning"></i>
                                        <span>ป้องกันด้วยรหัสผ่าน</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="feature-item">
                                        <i class="fas fa-mobile-alt text-info"></i>
                                        <span>รองรับทุกอุปกรณ์</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CTA Buttons -->
                        <div class="hero-cta">
                            <?php if ($allowRegistration): ?>
                            <a href="<?= getFullUrl('/auth/register.php') ?>" 
                               class="btn btn-primary btn-lg mr-3">
                                <i class="fas fa-user-plus"></i> สมัครสมาชิก
                            </a>
                            <?php endif; ?>
                            
                            <a href="<?= getFullUrl('/auth/login.php') ?>" 
                               class="btn btn-outline-light btn-lg">
                                <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
                            </a>
                        </div>
                        
                        <!-- Demo Link -->
                        <div class="hero-demo mt-4">
                            <a href="#demo-section" class="text-light">
                                <i class="fas fa-play-circle"></i> ดูการใช้งาน
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Visual -->
                <div class="col-lg-4 d-none d-lg-block">
                    <div class="hero-visual">
                        <div class="hero-image">
                            <img src="<?= getAssetUrl('hero-illustration.svg', 'img') ?>" 
                                 alt="File Sharing Illustration" 
                                 class="img-fluid">
                        </div>
                        
                        <!-- Floating Cards Animation -->
                        <div class="floating-cards">
                            <div class="floating-card card-1">
                                <i class="fas fa-file-pdf"></i>
                                <span>PDF</span>
                            </div>
                            <div class="floating-card card-2">
                                <i class="fas fa-file-image"></i>
                                <span>JPG</span>
                            </div>
                            <div class="floating-card card-3">
                                <i class="fas fa-file-archive"></i>
                                <span>ZIP</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
            <a href="#features-section">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features-section" class="features-section py-5">
        <div class="container">
            <!-- Section Header -->
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="section-title">ทำไมต้องเลือกเรา?</h2>
                    <p class="section-subtitle">
                        ระบบแชร์ไฟล์ที่ออกแบบมาเพื่อความง่าย รวดเร็ว และปลอดภัย
                    </p>
                </div>
            </div>
            
            <!-- Features Grid -->
            <div class="row">
                <!-- Feature 1 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h4>รวดเร็วทันใจ</h4>
                        <p>อัพโหลดและแชร์ไฟล์ได้ภายในไม่กี่วินาที พร้อม progress bar แสดงความคืบหน้า</p>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>ปลอดภัยสูง</h4>
                        <p>ป้องกันไฟล์ด้วยรหัสผ่าน กำหนดวันหมดอายุ และควบคุมสิทธิ์การเข้าถึง</p>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>ใช้งานง่าย</h4>
                        <p>ไม่ต้องสมัครสมาชิกก็ดาวน์โหลดได้ อินเทอร์เฟซเป็นภาษาไทยเข้าใจง่าย</p>
                    </div>
                </div>
                
                <!-- Feature 4 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h4>จัดระเบียบดี</h4>
                        <p>สร้างโฟลเดอร์ จัดกลุ่มไฟล์ และแชร์ทั้งโฟลเดอร์ได้ในคลิกเดียว</p>
                    </div>
                </div>
                
                <!-- Feature 5 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>ใช้ได้ทุกที่</h4>
                        <p>รองรับทุกอุปกรณ์ ไม่ว่าจะเป็นมือถือ แท็บเล็ต หรือคอมพิวเตอร์</p>
                    </div>
                </div>
                
                <!-- Feature 6 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>ติดตามสถิติ</h4>
                        <p>ดูจำนวนการดาวน์โหลด สถิติการใช้งาน และจัดการลิงค์แชร์ได้ง่าย</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo-section" class="demo-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="section-title">วิธีใช้งาน</h2>
                    <p class="section-subtitle">เพียง 3 ขั้นตอนง่ายๆ ก็แชร์ไฟล์ได้แล้ว</p>
                </div>
            </div>
            
            <div class="row">
                <!-- Step 1 -->
                <div class="col-lg-4 mb-4">
                    <div class="demo-step text-center">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h4>อัพโหลดไฟล์</h4>
                        <p>ลากและวางไฟล์ หรือคลิกเพื่อเลือกไฟล์ที่ต้องการแชร์</p>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="col-lg-4 mb-4">
                    <div class="demo-step text-center">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h4>ตั้งค่าการแชร์</h4>
                        <p>เลือกตั้งรหัสผ่าน กำหนดวันหมดอายุ หรือจำกัดจำนวนดาวน์โหลด</p>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="col-lg-4 mb-4">
                    <div class="demo-step text-center">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <h4>แชร์ลิงค์</h4>
                        <p>คัดลอกลิงค์และส่งให้เพื่อน เขาสามารถดาวน์โหลดได้ทันที</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number" data-count="<?= $systemStats['total_users'] ?>">0</div>
                        <div class="stat-label">ผู้ใช้งาน</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number" data-count="<?= $systemStats['total_files'] ?>">0</div>
                        <div class="stat-label">ไฟล์ที่แชร์</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number" data-count="<?= $systemStats['total_downloads'] ?>">0</div>
                        <div class="stat-label">ดาวน์โหลดทั้งหมด</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number"><?= formatFileSize($maxFileSize) ?></div>
                        <div class="stat-label">ขนาดไฟล์สูงสุด</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing/Plans Section (Optional) -->
    <section class="plans-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="section-title">แผนการใช้งาน</h2>
                    <p class="section-subtitle">เลือกแผนที่เหมาะกับคุณ</p>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <!-- Free Plan -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="plan-card">
                        <div class="plan-header">
                            <h3>ฟรี</h3>
                            <div class="plan-price">
                                <span class="currency">฿</span>
                                <span class="amount">0</span>
                                <span class="period">/เดือน</span>
                            </div>
                        </div>
                        <div class="plan-features">
                            <ul>
                                <li><i class="fas fa-check"></i> พื้นที่ <?= formatFileSize(DEFAULT_USER_STORAGE_LIMIT) ?></li>
                                <li><i class="fas fa-check"></i> ไฟล์สูงสุด <?= DEFAULT_USER_FILE_LIMIT ?> ไฟล์</li>
                                <li><i class="fas fa-check"></i> ขนาดไฟล์ <?= formatFileSize($maxFileSize) ?></li>
                                <li><i class="fas fa-check"></i> แชร์ด้วยรหัสผ่าน</li>
                                <li><i class="fas fa-check"></i> สถิติการใช้งาน</li>
                            </ul>
                        </div>
                        <div class="plan-action">
                            <?php if ($allowRegistration): ?>
                            <a href="<?= getFullUrl('/auth/register.php') ?>" class="btn btn-outline-primary btn-block">
                                เริ่มใช้งานฟรี
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline-secondary btn-block" disabled>
                                ปิดรับสมัครชั่วคราว
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Pro Plan (Future) -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="plan-card featured">
                        <div class="plan-badge">แนะนำ</div>
                        <div class="plan-header">
                            <h3>โปร</h3>
                            <div class="plan-price">
                                <span class="currency">฿</span>
                                <span class="amount">99</span>
                                <span class="period">/เดือน</span>
                            </div>
                        </div>
                        <div class="plan-features">
                            <ul>
                                <li><i class="fas fa-check"></i> พื้นที่ 10 GB</li>
                                <li><i class="fas fa-check"></i> ไฟล์ไม่จำกัด</li>
                                <li><i class="fas fa-check"></i> ขนาดไฟล์ 100 MB</li>
                                <li><i class="fas fa-check"></i> แชร์แบบกลุ่ม</li>
                                <li><i class="fas fa-check"></i> รายงานขั้นสูง</li>
                                <li><i class="fas fa-check"></i> ลบโฆษณา</li>
                            </ul>
                        </div>
                        <div class="plan-action">
                            <button class="btn btn-primary btn-block" disabled>
                                เร็วๆ นี้
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact/Support Section -->
    <section class="contact-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">ต้องการความช่วยเหลือ?</h2>
                    <p class="section-subtitle mb-4">
                        ทีมงานของเราพร้อมให้ความช่วยเหลือตลอด 24 ชั่วโมง
                    </p>
                    
                    <div class="contact-options">
                        <a href="mailto:<?= getSystemSetting('contact_email', 'support@example.com') ?>" 
                           class="btn btn-outline-primary mr-3 mb-2">
                            <i class="fas fa-envelope"></i> ส่งอีเมล
                        </a>
                        
                        <a href="<?= getFullUrl('/help/') ?>" 
                           class="btn btn-outline-secondary mr-3 mb-2">
                            <i class="fas fa-question-circle"></i> คำถามที่พบบ่อย
                        </a>
                        
                        <a href="<?= getFullUrl('/contact/') ?>" 
                           class="btn btn-outline-info mb-2">
                            <i class="fas fa-comments"></i> ติดต่อเรา
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Additional CSS -->
<style>
/* Landing Page Styles */
.landing-page {
    font-family: 'Sarabun', sans-serif;
}

/* Hero Section */
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('<?= getAssetUrl("hero-bg.jpg", "img") ?>') center/cover;
    z-index: 1;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
    z-index: 2;
}

.hero-content {
    position: relative;
    z-index: 3;
    animation: fadeInUp 1s ease-out;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    line-height: 1.6;
}

.hero-features {
    margin-bottom: 2.5rem;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.feature-item i {
    font-size: 1.5rem;
    margin-right: 1rem;
    width: 2rem;
}

.hero-cta {
    margin-bottom: 2rem;
}

.hero-demo a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.hero-demo a:hover {
    color: white;
    text-decoration: none;
}

/* Hero Visual */
.hero-visual {
    position: relative;
    z-index: 3;
}

.hero-image {
    text-align: center;
    margin-bottom: 2rem;
}

.floating-cards {
    position: relative;
}

.floating-card {
    position: absolute;
    background: white;
    color: #333;
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    font-weight: 600;
    animation: float 3s ease-in-out infinite;
}

.floating-card i {
    font-size: 1.5rem;
    margin-right: 0.5rem;
}

.floating-card.card-1 {
    top: -50px;
    left: 20px;
    animation-delay: 0s;
}

.floating-card.card-2 {
    top: 20px;
    right: 30px;
    animation-delay: 1s;
}

.floating-card.card-3 {
    top: 100px;
    left: 50px;
    animation-delay: 2s;
}

/* Scroll Indicator */
.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    animation: bounce 2s infinite;
}

.scroll-indicator a {
    color: white;
    font-size: 1.5rem;
    text-decoration: none;
}

/* Sections */
.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #333;
}

.section-subtitle {
    font-size: 1.2rem;
    color: #666;
    line-height: 1.6;
}

/* Features Section */
.features-section {
    padding: 5rem 0;
}

.feature-card {
    padding: 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.feature-icon {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 1.5rem;
}

.feature-card h4 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #333;
}

.feature-card p {
    color: #666;
    line-height: 1.6;
}

/* Demo Section */
.demo-section {
    background: #f8f9fa;
}

.demo-step {
    position: relative;
    padding: 2rem;
}

.step-number {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 40px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.step-icon {
    font-size: 3rem;
    color: #667eea;
    margin: 2rem 0 1.5rem;
}

.demo-step h4 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #333;
}

.demo-step p {
    color: #666;
    line-height: 1.6;
}

/* Stats Section */
.stats-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-item {
    padding: 2rem 1rem;
}

.stat-number {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: block;
}

.stat-label {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* Plans Section */
.plan-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    padding: 2rem;
    position: relative;
    transition: all 0.3s ease;
    height: 100%;
}

.plan-card.featured {
    border: 3px solid #667eea;
    transform: scale(1.05);
}

.plan-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.plan-card.featured:hover {
    transform: scale(1.05) translateY(-10px);
}

.plan-badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: #667eea;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.plan-header {
    text-align: center;
    margin-bottom: 2rem;
}

.plan-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #333;
}

.plan-price {
    margin-bottom: 1rem;
}

.plan-price .currency {
    font-size: 1.2rem;
    color: #666;
}

.plan-price .amount {
    font-size: 3rem;
    font-weight: 700;
    color: #667eea;
}

.plan-price .period {
    font-size: 1rem;
    color: #666;
}

.plan-features ul {
    list-style: none;
    padding: 0;
    margin-bottom: 2rem;
}

.plan-features li {
    padding: 0.5rem 0;
    display: flex;
    align-items: center;
}

.plan-features li i {
    color: #28a745;
    margin-right: 1rem;
    width: 1rem;
}

/* Contact Section */
.contact-section {
    background: #f8f9fa;
}

.contact-options {
    margin-top: 2rem;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translate(-50%, 0);
    }
    40% {
        transform: translate(-50%, -10px);
    }
    60% {
        transform: translate(-50%, -5px);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .feature-item {
        font-size: 1rem;
    }
    
    .hero-cta .btn {
        display: block;
        margin-bottom: 1rem;
        margin-right: 0 !important;
    }
    
    .plan-card.featured {
        transform: none;
        margin-bottom: 2rem;
    }
    
    .floating-cards {
        display: none;
    }
}

@media (max-width: 576px) {
    .hero-section {
        padding: 2rem 0;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .feature-card {
        padding: 1.5rem;
    }
    
    .demo-step {
        padding: 1.5rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .feature-card,
    .plan-card {
        background: #2d3748;
        color: #e2e8f0;
    }
    
    .section-title,
    .feature-card h4,
    .demo-step h4,
    .plan-header h3 {
        color: #e2e8f0;
    }
    
    .section-subtitle,
    .feature-card p,
    .demo-step p {
        color: #a0aec0;
    }
}

/* Scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Loading states */
.btn-loading {
    position: relative;
    color: transparent !important;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<!-- Page-specific JavaScript -->
<script>
$(document).ready(function() {
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        
        let target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 1000);
        }
    });
    
    // Counter animation for stats
    function animateCounters() {
        $('.stat-number[data-count]').each(function() {
            let $this = $(this);
            let countTo = parseInt($this.attr('data-count'));
            
            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 2000,
                easing: 'linear',
                step: function() {
                    $this.text(Math.floor(this.countNum).toLocaleString());
                },
                complete: function() {
                    $this.text(countTo.toLocaleString());
                }
            });
        });
    }
    
    // Intersection Observer for animations
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            threshold: 0.3,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    
                    // Trigger counter animation for stats section
                    if (entry.target.classList.contains('stats-section')) {
                        animateCounters();
                    }
                }
            });
        }, observerOptions);
        
        // Observe sections
        document.querySelectorAll('.features-section, .demo-section, .stats-section, .plans-section').forEach(function(section) {
            observer.observe(section);
        });
    } else {
        // Fallback for older browsers
        setTimeout(animateCounters, 1000);
    }
    
    // Parallax effect for hero section
    $(window).on('scroll', function() {
        let scrolled = $(this).scrollTop();
        let rate = scrolled * -0.5;
        
        $('.hero-background').css('transform', 'translateY(' + rate + 'px)');
    });
    
    // Floating cards animation
    function animateFloatingCards() {
        $('.floating-card').each(function(index) {
            let delay = index * 500;
            setTimeout(() => {
                $(this).addClass('animate-float');
            }, delay);
        });
    }
    
    setTimeout(animateFloatingCards, 1000);
    
    // Feature cards hover effect
    $('.feature-card').on('mouseenter', function() {
        $(this).find('.feature-icon').addClass('bounce');
    }).on('mouseleave', function() {
        $(this).find('.feature-icon').removeClass('bounce');
    });
    
    // Plan card interactions
    $('.plan-card').on('click', function() {
        if (!$(this).hasClass('featured')) {
            $('.plan-card').removeClass('featured');
            $(this).addClass('featured');
        }
    });
    
    // Newsletter subscription (if needed)
    $('#newsletter-form').on('submit', function(e) {
        e.preventDefault();
        
        let email = $(this).find('input[type="email"]').val();
        let btn = $(this).find('button[type="submit"]');
        
        if (!email) {
            window.fileShareHub.utils.showToast('กรุณาใส่อีเมล', 'error');
            return;
        }
        
        btn.addClass('btn-loading').prop('disabled', true);
        
        // TODO: Implement newsletter subscription
        setTimeout(function() {
            btn.removeClass('btn-loading').prop('disabled', false);
            window.fileShareHub.utils.showToast('สมัครรับข่าวสารสำเร็จ!', 'success');
        }, 2000);
    });
    
    // Contact form handling
    $('.contact-options a[href^="mailto:"]').on('click', function() {
        // Track email clicks
        if (window.gtag) {
            gtag('event', 'contact', {
                'event_category': 'engagement',
                'event_label': 'email_click'
            });
        }
    });
    
    // CTA button tracking
    $('.hero-cta a, .plan-action a').on('click', function() {
        let action = $(this).text().trim();
        
        // Track CTA clicks
        if (window.gtag) {
            gtag('event', 'cta_click', {
                'event_category': 'conversion',
                'event_label': action
            });
        }
    });
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(function(img) {
            imageObserver.observe(img);
        });
    }
    
    // Auto-hide flash messages
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+L for login
        if (e.ctrlKey && e.key === 'l') {
            e.preventDefault();
            window.location.href = '<?= getFullUrl("/auth/login.php") ?>';
        }
        
        // Ctrl+R for register
        if (e.ctrlKey && e.key === 'r' && <?= $allowRegistration ? 'true' : 'false' ?>) {
            e.preventDefault();
            window.location.href = '<?= getFullUrl("/auth/register.php") ?>';
        }
    });
    
    // PWA Install prompt
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        
        // Show install button (if you have one)
        $('#install-app-btn').show();
    });
    
    $('#install-app-btn').on('click', function() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                }
                deferredPrompt = null;
            });
        }
    });
    
    // Initialize tooltips and popovers
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
    
    // Add CSS classes for animations
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .animate-in {
                animation: slideInUp 0.8s ease-out forwards;
            }
            
            .animate-float {
                animation: float 3s ease-in-out infinite;
            }
            
            .bounce {
                animation: bounceIcon 0.6s ease-in-out;
            }
            
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(50px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes bounceIcon {
                0%, 20%, 50%, 80%, 100% {
                    transform: translateY(0);
                }
                40% {
                    transform: translateY(-10px);
                }
                60% {
                    transform: translateY(-5px);
                }
            }
            
            .lazy {
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .lazy.loaded {
                opacity: 1;
            }
        `)
        .appendTo('head');
});

// Global functions for landing page
window.landingPage = {
    // Scroll to section
    scrollTo: function(selector) {
        let target = $(selector);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 1000);
        }
    },
    
    // Show feature demo
    showDemo: function(feature) {
        // TODO: Implement feature demos
        console.log('Showing demo for:', feature);
    },
    
    // Track user interactions
    track: function(event, data) {
        if (window.gtag) {
            gtag('event', event, data);
        }
        
        // Also log to console in development
        if (window.fileShareHub && window.fileShareHub.debug) {
            console.log('Track:', event, data);
        }
    }
};

// Add structured data for SEO
const structuredData = {
    "@context": "https://schema.org",
    "@type": "WebApplication",
    "name": "<?= htmlspecialchars($siteTitle) ?>",
    "description": "<?= htmlspecialchars($siteDescription) ?>",
    "url": "<?= SITE_URL ?>",
    "applicationCategory": "UtilitiesApplication",
    "operatingSystem": "Any",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "THB"
    },
    "featureList": [
        "อัพโหลดไฟล์ง่าย รวดเร็ว",
        "แชร์ไฟล์ปลอดภัย",
        "ป้องกันด้วยรหัสผ่าน",
        "รองรับทุกอุปกรณ์"
    ]
};

$('head').append('<script type="application/ld+json">' + JSON.stringify(structuredData) + '</script>');
</script>

<?php
// ฟังก์ชันรับสถิติระบบสำหรับแสดงผล
function getPublicSystemStats()
{
    try {
        $pdo = getDatabase();
        
        // นับผู้ใช้ที่ active
        $stmt = $pdo->query("SELECT COUNT(*) FROM " . TABLE_USERS . " WHERE status = '" . USER_STATUS_ACTIVE . "'");
        $totalUsers = $stmt->fetchColumn();
        
        // นับไฟล์ที่ไม่ถูกลบ
        $stmt = $pdo->query("SELECT COUNT(*) FROM " . TABLE_FILES . " WHERE is_deleted = 0");
        $totalFiles = $stmt->fetchColumn();
        
        // นับจำนวนดาวน์โหลดทั้งหมด
        $stmt = $pdo->query("SELECT SUM(download_count) FROM " . TABLE_FILES . " WHERE is_deleted = 0");
        $totalDownloads = $stmt->fetchColumn() ?: 0;
        
        // นับ shared links ที่ active
        $stmt = $pdo->query("SELECT COUNT(*) FROM " . TABLE_SHARED_LINKS . " WHERE is_active = 1");
        $totalShares = $stmt->fetchColumn();
        
        return [
            'total_users' => (int) $totalUsers,
            'total_files' => (int) $totalFiles,
            'total_downloads' => (int) $totalDownloads,
            'total_shares' => (int) $totalShares
        ];
        
    } catch (Exception $e) {
        error_log("Error getting system stats: " . $e->getMessage());
        
        // Return default values
        return [
            'total_users' => 0,
            'total_files' => 0,
            'total_downloads' => 0,
            'total_shares' => 0
        ];
    }
}

// เพิ่มข้อมูล meta tags สำหรับ SEO
$metaData = [
    'description' => $siteDescription,
    'keywords' => 'แชร์ไฟล์, อัพโหลด, ดาวน์โหลด, ไฟล์ออนไลน์, แชร์ปลอดภัย',
    'author' => APP_AUTHOR,
    'robots' => 'index, follow',
    'og:title' => $siteTitle,
    'og:description' => $siteDescription,
    'og:type' => 'website',
    'og:url' => SITE_URL,
    'og:image' => getAssetUrl('og-image.jpg', 'img'),
    'twitter:card' => 'summary_large_image',
    'twitter:title' => $siteTitle,
    'twitter:description' => $siteDescription,
    'twitter:image' => getAssetUrl('og-image.jpg', 'img')
];

// เพิ่ม meta tags ใน head
foreach ($metaData as $name => $content) {
    if (str_starts_with($name, 'og:') || str_starts_with($name, 'twitter:')) {
        echo '<meta property="' . htmlspecialchars($name) . '" content="' . htmlspecialchars($content) . '">' . "\n";
    } else {
        echo '<meta name="' . htmlspecialchars($name) . '" content="' . htmlspecialchars($content) . '">' . "\n";
    }
}

// รวมไฟล์ footer
include 'includes/footer.php';
?>