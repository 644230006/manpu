<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require '../config/db.php';
require '../includes/header.php';

// ตรวจสอบว่าข้อมูลผู้ใช้ครบถ้วนหรือไม่
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$missing_info = false;
foreach ($user as $key => $value) {
    if (empty($value) && $key != 'balance') {
        $missing_info = true;
        break;
    }
}

if ($missing_info) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'กรุณากรอกข้อมูลส่วนตัวให้ครบถ้วนก่อนอัปโหลดสลิป'
            }).then(function() {
                window.location.href = 'profile.php';
            });
        });
    </script>";
    exit();
}
?>

<link rel="stylesheet" href="../assets/css/slip_verification.css">
<section class="slip-verification-section">
    <h2>ตรวจสอบสลิป เสีย 10 เครดิต</h2>
    <form action="slip_verification_process.php" method="post" enctype="multipart/form-data">
        <label for="qrImage">อัปโหลดรูปภาพสลิป:</label>
        <input type="file" id="qrImage" name="qrImage" accept="image/*" required>
        <button type="submit">อัปโหลดและตรวจสอบ</button>
    </form>
    <a href="user_slip_verification_history.php" class="button back-to-verification">ประวัติการตรวจสอบสลิป</a>
    <a href="dashboard.php" class="button back-to-dashboard">กลับแดชบอร์ด</a>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
require '../includes/footer.php';
?>
