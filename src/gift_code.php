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

// ตรวจสอบและใช้รหัสของขวัญ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['gift_code'])) {
    $gift_code = $_POST['gift_code'];

    // ตรวจสอบว่าผู้ใช้เคยใช้รหัสนี้หรือยัง
    $check_sql = "SELECT * FROM user_gift_codes WHERE user_id = :user_id AND gift_code_id = (SELECT id FROM gift_codes WHERE code = :code)";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->bindParam(':code', $gift_code);
    $check_stmt->execute();
    $already_used = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($already_used) {
        // แสดงข้อความข้อผิดพลาด
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    text: 'คุณได้ใช้รหัสของขวัญนี้ไปแล้ว'
                });
            });
        </script>";
    } else {
        $code_sql = "SELECT * FROM gift_codes WHERE code = :code AND is_used < usage_limit";
        $code_stmt = $conn->prepare($code_sql);
        $code_stmt->bindParam(':code', $gift_code);
        $code_stmt->execute();
        $code = $code_stmt->fetch(PDO::FETCH_ASSOC);

        if ($code) {
            // อัปเดตยอดเงินคงเหลือของผู้ใช้
            $new_balance = $balance + $code['amount'];
            $update_balance_sql = "UPDATE users SET balance = :balance WHERE id = :user_id";
            $update_balance_stmt = $conn->prepare($update_balance_sql);
            $update_balance_stmt->bindParam(':balance', $new_balance);
            $update_balance_stmt->bindParam(':user_id', $user_id);
            $update_balance_stmt->execute();

            // อัปเดตสถานะรหัสของขวัญเป็นใช้งานแล้ว
            $update_code_sql = "UPDATE gift_codes SET is_used = is_used + 1 WHERE id = :code_id";
            $update_code_stmt = $conn->prepare($update_code_sql);
            $update_code_stmt->bindParam(':code_id', $code['id']);
            $update_code_stmt->execute();

            // บันทึกการใช้รหัสของขวัญ
            $insert_user_gift_code_sql = "INSERT INTO user_gift_codes (user_id, gift_code_id) VALUES (:user_id, :gift_code_id)";
            $insert_user_gift_code_stmt = $conn->prepare($insert_user_gift_code_sql);
            $insert_user_gift_code_stmt->bindParam(':user_id', $user_id);
            $insert_user_gift_code_stmt->bindParam(':gift_code_id', $code['id']);
            $insert_user_gift_code_stmt->execute();

            // อัปเดตยอดเงินคงเหลือในตัวแปร $balance
            $balance = $new_balance;

            // แสดงข้อความสำเร็จ
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: 'รหัสของขวัญถูกใช้สำเร็จแล้ว ยอดเงินคงเหลือของคุณได้รับการอัปเดต'
                    });
                });
            </script>";
        } else {
            // แสดงข้อความข้อผิดพลาด
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'ข้อผิดพลาด',
                        text: 'รหัสของขวัญไม่ถูกต้องหรือถูกใช้งานเต็มจำนวนแล้ว'
                    });
                });
            </script>";
        }
    }
}
?>

<!-- เพิ่มการนำเข้า SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="../assets/css/gift_code.css">

<section class="gift-code-section">
    <h2>ใช้รหัสของขวัญ</h2>
    <form action="gift_code.php" method="post" class="gift-code-form">
        <label for="gift_code">กรอกรหัสของขวัญ:</label>
        <input type="text" id="gift_code" name="gift_code" required>
        <button type="submit">ใช้รหัส</button>
    </form>
    <a href="dashboard.php" class="button back-to-dashboard">กลับแดชบอร์ด</a>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
require '../includes/footer.php';
?>
