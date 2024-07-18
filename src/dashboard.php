<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

// เรียกข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = $_SESSION['user_id'];
$sql = "SELECT balance FROM users WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$balance = $user['balance'];
?>

<link rel="stylesheet" href="/PJ/assets/css/dashboard.css">
<section class="dashboard">
    <h2>แดชบอร์ด</h2>
    <div class="card-container">
        <a href="#" class="card balance-card">
            <i class="icon fa fa-wallet"></i>
            <p>ยอดเงินคงเหลือ</p>
            <p><?php echo number_format($balance, 2); ?> บาท</p>
        </a>
        <a href="gift_code.php" class="card gift-code-card">
            <i class="icon fa fa-gift"></i>
            <p>ใช้รหัสของขวัญ</p>
        </a>
        <a href="user_gift_code_history.php" class="card gift-code-history-card">
            <i class="icon fa fa-history"></i>
            <p>ประวัติการใช้รหัสของขวัญ</p>
        </a>
        <a href="profile.php" class="card profile-card">
            <i class="icon fa fa-user"></i>
            <p>จัดการข้อมูลส่วนตัว</p>
        </a>
        <a href="change_password.php" class="card password-card">
            <i class="icon fa fa-key"></i>
            <p>จัดการรหัสผ่าน</p>
        </a>
        <a href="user_qr_code_history.php" class="card qr-code-history-card">
            <i class="icon fa fa-qrcode"></i>
            <p>ประวัติการสร้าง QR code</p>
        </a>
        <a href="user_slip_verification_history.php" class="card transaction-history-card">
            <i class="icon fa fa-file-invoice"></i>
            <p>ประวัติการทำธุรกรรม</p>
        </a>
        <a href="user_wallet_history.php" class="card wallet-history-card">
            <i class="icon fas fa-history"></i>
            <p>ประวัติการใช้เครดิต</p>
        </a>
    </div>
</section>

<?php
require '../includes/footer.php';
?>

<!-- เพิ่มการนำเข้า SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
