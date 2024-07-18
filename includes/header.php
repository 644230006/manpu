<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MANPU STRONG HERE</title>
    <link rel="stylesheet" href="/PJ/assets/css/reset.css">
    <link rel="stylesheet" href="/PJ/assets/css/header.css">
    <link rel="stylesheet" href="/PJ/assets/css/main.css">
    <link rel="stylesheet" href="/PJ/assets/css/aboutme.css">
    <link rel="stylesheet" href="/PJ/assets/css/register.css">
    <link rel="stylesheet" href="/PJ/assets/css/login.css">
    <link rel="stylesheet" href="/PJ/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PJ/assets/css/gift_code.css">
    <link rel="stylesheet" href="/PJ/assets/css/gift_code_history.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> <!-- Font Awesome -->
    <link rel="stylesheet" href="/PJ/assets/css/change_password.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
function isActive($page) {
    $current_file = basename($_SERVER['PHP_SELF']);
    return $current_file == $page ? 'active' : '';
}
?>
<header>
    <div class="logo">
        <a href="/PJ/index.php"><h2>MANPU STRONG HERE</h2></a>
    </div>
    <nav class="main-nav">
        <ul>
            <li><a href="/PJ/index.php" class="<?php echo isActive('index.php'); ?>"><i class="fas fa-home"></i> หน้าแรก</a></li>
            <li><a href="/PJ/src/aboutme.php" class="<?php echo isActive('aboutme.php'); ?>"><i class="fas fa-info-circle"></i> เกี่ยวกับเรา</a></li>
            <li><a href="/PJ/src/contactme.php" class="<?php echo isActive('contactme.php'); ?>"><i class="fas fa-envelope"></i> ติดต่อเรา</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="/PJ/src/dashboard.php" class="<?php echo isActive('dashboard.php'); ?>"><i class="fas fa-tachometer-alt"></i> แดชบอร์ด</a></li>
                <li><a href="/PJ/src/createqrcode.php" class="<?php echo isActive('createqrcode.php'); ?>"><i class="fas fa-qrcode"></i> สร้างคิวอาร์โค้ด</a></li>
                <li><a href="/PJ/src/slip_verification.php" class="<?php echo isActive('slip_verification.php'); ?>"><i class="fas fa-file-invoice"></i>ตรวจสอบสลิป</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="/PJ/src/admin_dashboard.php" class="<?php echo isActive('admin_dashboard.php'); ?>"><i class="fas fa-user-shield"></i> แดชบอร์ดผู้ดูแลระบบ</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="auth-buttons">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="/PJ/src/logout.php" class="button logout-button"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        <?php else: ?>
            <a href="/PJ/src/register.php" class="button register-button <?php echo isActive('register.php'); ?>"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
            <a href="/PJ/src/login.php" class="button login-button <?php echo isActive('login.php'); ?>"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
        <?php endif; ?>
    </div>
</header>
<main>
