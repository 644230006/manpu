<?php
require '../includes/header.php';
?>

<section class="login-form">
    <h2>เข้าสู่ระบบ</h2>
    <form id="loginForm" action="login_process.php" method="post">
        <label for="email">อีเมล:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">รหัสผ่าน:</label>
        <div class="password-container">
            <input type="password" id="password" name="password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility('password')">&#128065;</span>
        </div>

        <button type="submit">เข้าสู่ระบบ</button>
    </form>
    <div id="errorMessages" style="color: red;"></div>
</section>

<!-- เพิ่มการนำเข้า SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        const errorType = urlParams.get('error');
        let errorMessage = '';
        switch(errorType) {
            case 'invalid_credentials':
                errorMessage = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
                break;
            case 'db_error':
                errorMessage = 'เกิดข้อผิดพลาดในฐานข้อมูล';
                break;
            case 'suspended':
                errorMessage = 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ';
                break;
        }
        Swal.fire({
            icon: 'error',
            title: 'ข้อผิดพลาด',
            text: errorMessage
        });
    }

    if (urlParams.has('success')) {
        const successType = urlParams.get('success');
        let successMessage = '';
        switch(successType) {
            case 'registered':
                successMessage = 'คุณได้สมัครสมาชิกเรียบร้อยแล้ว';
                break;
            case 'logged_out':
                successMessage = 'คุณได้ออกจากระบบเรียบร้อยแล้ว';
                break;
        }
        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: successMessage
            });
        }
    }
});

function togglePasswordVisibility(fieldId) {
    var field = document.getElementById(fieldId);
    var toggleIcon = field.nextElementSibling;
    if (field.type === "password") {
        field.type = "text";
        toggleIcon.innerHTML = "&#128064;"; // เปลี่ยนไอคอนเป็นไอคอนเปิดตา
    } else {
        field.type = "password";
        toggleIcon.innerHTML = "&#128065;"; // เปลี่ยนไอคอนเป็นไอคอนปิดตา
    }
}
</script>

<?php
require '../includes/footer.php';
?>
