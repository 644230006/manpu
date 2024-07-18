function validateForm() {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm_password').value;
    var errorMessages = document.getElementById('errorMessages');
    errorMessages.innerHTML = '';

    var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!passwordRegex.test(password)) {
        Swal.fire({
            icon: 'error',
            title: 'ข้อผิดพลาด',
            text: 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวอักษรพิมพ์ใหญ่ พิมพ์เล็ก ตัวเลข และอักขระพิเศษอย่างน้อยหนึ่งตัว'
        });
        return false;
    }

    if (password !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'ข้อผิดพลาด',
            text: 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน'
        });
        return false;
    }

    return true;
}

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

function checkPasswordStrength() {
    var password = document.getElementById('password').value;
    var status = document.getElementById('password-strength-status');
    var strength = {
        length: false,
        lowercase: false,
        uppercase: false,
        digit: false,
        special: false
    };

    if (password.length >= 8) {
        strength.length = true;
    }
    if (/[a-z]/.test(password)) {
        strength.lowercase = true;
    }
    if (/[A-Z]/.test(password)) {
        strength.uppercase = true;
    }
    if (/\d/.test(password)) {
        strength.digit = true;
    }
    if (/[@$!%*?&]/.test(password)) {
        strength.special = true;
    }

    var message = "รหัสผ่านต้องประกอบด้วย:<ul>";
    message += strength.length ? "<li style='color:green'>ความยาวอย่างน้อย 8 ตัวอักษร</li>" : "<li style='color:red'>ความยาวอย่างน้อย 8 ตัวอักษร</li>";
    message += strength.lowercase ? "<li style='color:green'>ตัวอักษรพิมพ์เล็ก</li>" : "<li style='color:red'>ตัวอักษรพิมพ์เล็ก</li>";
    message += strength.uppercase ? "<li style='color:green'>ตัวอักษรพิมพ์ใหญ่</li>" : "<li style='color:red'>ตัวอักษรพิมพ์ใหญ่</li>";
    message += strength.digit ? "<li style='color:green'>ตัวเลข</li>" : "<li style='color:red'>ตัวเลข</li>";
    message += strength.special ? "<li style='color:green'>อักขระพิเศษ</li>" : "<li style='color:red'>อักขระพิเศษ</li>";
    message += "</ul>";

    status.innerHTML = message;
}

function checkPasswordMatch() {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm_password').value;
    var status = document.getElementById('password-match-status');

    if (password === confirmPassword) {
        status.innerHTML = "<p style='color:green'>รหัสผ่านและยืนยันรหัสผ่านตรงกัน</p>";
    } else {
        status.innerHTML = "<p style='color:red'>รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน</p>";
    }
}
