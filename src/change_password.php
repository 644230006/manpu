<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

// เปลี่ยนรหัสผ่าน
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // ตรวจสอบรหัสผ่านเก่า
    $sql = "SELECT password FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($old_password, $user['password'])) {
        // อัปเดตรหัสผ่านใหม่
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password_sql = "UPDATE users SET password = :new_password WHERE id = :user_id";
        $update_password_stmt = $conn->prepare($update_password_sql);
        $update_password_stmt->bindParam(':new_password', $hashed_password);
        $update_password_stmt->bindParam(':user_id', $user_id);
        $update_password_stmt->execute();

        // แสดงข้อความสำเร็จ
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: 'รหัสผ่านของคุณได้รับการอัปเดต'
                }).then(() => {
                    window.location.href = 'dashboard.php';
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
                    text: 'รหัสผ่านเก่าไม่ถูกต้อง'
                });
            });
        </script>";
    }
}
?>

<link rel="stylesheet" href="assets/css/change_password.css">
<section class="dashboard">
    <h2>จัดการรหัสผ่าน</h2>
    <form action="change_password.php" method="post" class="password-form">
        <label for="old_password">รหัสผ่านเก่า:</label>
        <div class="input-container">
            <input type="password" id="old_password" name="old_password" required>
            <i class="show-password" onclick="togglePasswordVisibility('old_password')">👁️</i>
        </div>
        
        <label for="new_password">รหัสผ่านใหม่:</label>
        <div class="input-container">
            <input type="password" id="new_password" name="new_password" required>
            <i class="show-password" onclick="togglePasswordVisibility('new_password')">👁️</i>
        </div>
        
        <button type="submit" name="change_password">เปลี่ยนรหัสผ่าน</button>
    </form>
    <a href="dashboard.php" class="button">กลับแดชบอร์ด</a>
</section>

<?php
require '../includes/footer.php';
?>

<!-- เพิ่มการนำเข้า SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function togglePasswordVisibility(id) {
    var input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>
