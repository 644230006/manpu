<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';
?>

<link rel="stylesheet" href="/PJ/assets/css/admin_dashboard.css">

<section class="dashboard">
    <h2>แดชบอร์ดผู้ดูแลระบบ</h2>
    <div class="card-container">
        <a href="report.php" class="card green">
            <i class="fas fa-chart-line"></i>
            <p>รายงานผล</p>
        </a>
        <a href="gift_code_history.php" class="card black">
            <i class="fas fa-gift"></i>
            <p>ประวัติการใช้รหัสของขวัญทั้งหมด</p>
        </a>
        <a href="admin_wallet_history.php" class="card red">
            <i class="fas fa-history"></i>
            <p>ประวัติการใช้เครดิตทั้งหมด</p>
        </a>
        <a href="status_gift_code.php" class="card blue">
            <i class="fas fa-info-circle"></i>
            <p>สถานะการใช้งานรหัสของขวัญ</p>
        </a>
        <a href="user_management.php" class="card orange">
            <i class="fas fa-users-cog"></i>
            <p>จัดการผู้ใช้</p>
        </a>
        <a href="create_gift_code.php" class="card purple">
            <i class="fas fa-plus-circle"></i>
            <p>สร้างรหัสของขวัญ</p>
        </a>
        <a href="admin_slip_verification_history.php" class="card pink">
            <i class="fas fa-file-invoice"></i>
            <p>ประวัติการทำธุรกรรมทั้งหมด</p>
        </a>
    </div>
</section>

<?php
require '../includes/footer.php';
?>

<!-- เพิ่มการนำเข้า SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
