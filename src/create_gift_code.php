<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

// ฟังก์ชันสำหรับสุ่มรหัสของขวัญ
function generateGiftCode($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// ตรวจสอบและสร้างรหัสของขวัญ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount']) && isset($_POST['usage_limit'])) {
    $amount = $_POST['amount'];
    $usage_limit = $_POST['usage_limit'];

    if ($amount > 500) {
        $amount += $amount * 0.15;
    }

    $gift_code = generateGiftCode();

    $insert_sql = "INSERT INTO gift_codes (code, amount, usage_limit, is_used) VALUES (:code, :amount, :usage_limit, 0)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bindParam(':code', $gift_code);
    $insert_stmt->bindParam(':amount', $amount);
    $insert_stmt->bindParam(':usage_limit', $usage_limit);
    $insert_stmt->execute();

    // แสดงข้อความสำเร็จ
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: 'สร้างรหัสของขวัญสำเร็จ: $gift_code'
            }).then(() => {
                window.location.href = 'admin_dashboard.php';
            });
        });
    </script>";
}
?>

<!-- เพิ่มการนำเข้า SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="../assets/css/create_gift_code.css">

<section class="create-gift-code-section">
    <h2>สร้างรหัสของขวัญ</h2>
    <form action="create_gift_code.php" method="post" class="create-gift-code-form">
        <label for="amount">จำนวนเงิน:</label>
        <input type="number" id="amount" name="amount" required>
        
        <label for="usage_limit">จำนวนครั้งที่ใช้ได้:</label>
        <input type="number" id="usage_limit" name="usage_limit" required>
        
        <button type="submit">สร้างรหัสของขวัญ</button>
    </form>
    <a href="admin_dashboard.php" class="button back-to-dashboard">กลับแดชบอร์ดผู้ดูแลระบบ</a>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
require '../includes/footer.php';
?>
