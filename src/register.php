<?php
require '../includes/header.php';
require '../config/db.php';
?>

<section class="register-form">
    <h2>สมัครสมาชิก</h2>
    <form id="registerForm" action="register_process.php" method="post" onsubmit="return validateForm()">
        <label for="username">ชื่อผู้ใช้:</label>
        <input type="text" id="username" name="username" required>

        <label for="email">อีเมล:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">รหัสผ่าน:</label>
        <div class="password-container">
            <input type="password" id="password" name="password" required oninput="checkPasswordStrength()">
            <span class="toggle-password" onclick="togglePasswordVisibility('password')">&#128065;</span>
        </div>
        <div id="password-strength-status"></div>

        <label for="confirm_password">ยืนยันรหัสผ่าน:</label>
        <div class="password-container">
            <input type="password" id="confirm_password" name="confirm_password" required oninput="checkPasswordMatch()">
            <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">&#128065;</span>
        </div>
        <div id="password-match-status"></div>

        <button type="submit">สมัครสมาชิก</button>
    </form>
    <div id="errorMessages" style="color: red;"></div>
</section>

<!-- เพิ่มการนำเข้า SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/PJ/assets/js/register.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        const errorType = urlParams.get('error');
        let errorMessage = '';
        switch(errorType) {
            case 'password_mismatch':
                errorMessage = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
                break;
            case 'weak_password':
                errorMessage = 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวอักษรพิมพ์ใหญ่ พิมพ์เล็ก ตัวเลข และอักขระพิเศษอย่างน้อยหนึ่งตัว';
                break;
            case 'email_exists':
                errorMessage = 'อีเมลนี้มีการใช้งานอยู่แล้ว';
                break;
            case 'registration_failed':
                errorMessage = 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
                break;
            case 'db_error':
                errorMessage = 'เกิดข้อผิดพลาดในฐานข้อมูล';
                break;
        }
        Swal.fire({
            icon: 'error',
            title: 'ข้อผิดพลาด',
            text: errorMessage
        });
    }
});
</script>

<?php
require '../includes/footer.php';
?>
