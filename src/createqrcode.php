<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require '../config/db.php';
require '../includes/header.php';

// เรียกข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, first_name, last_name, phone, biller_id FROM users WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบว่าข้อมูลผู้ใช้ครบถ้วนหรือไม่
$missing_info = false;
foreach ($user as $key => $value) {
    if (empty($value)) {
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
                text: 'กรุณากรอกข้อมูลส่วนตัวให้ครบถ้วนก่อนสร้าง QR Code'
            }).then(function() {
                window.location.href = 'profile.php';
            });
        });
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้าง QR Code</title>
    <link rel="stylesheet" href="../assets/css/createqrcode.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <section class="create-qrcode-section">
        <h2>สร้าง QR Code</h2>
        <form action="createqrcode_process.php" method="post" class="create-qrcode-form">
            <label for="amount">จำนวนเงิน:</label>
            <input type="text" id="amount" name="amount" required>
            <button type="submit">สร้าง QR Code</button>
        </form>
        <a href="user_qr_code_history.php" class="button">ดูประวัติการสร้าง QR Code</a>
    </section>

<?php
require '../includes/footer.php';
?>

</body>
</html>
