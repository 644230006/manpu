<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require '../config/db.php';
require '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/slip_verification.css">
<section class="slip-verification-section">
    <h2>ตรวจสอบสลิป</h2>
    <form id="slipVerificationForm" action="slip_verification_process2.php" method="post">
        <label for="qrImageUrl">ใส่ลิงก์รูปภาพสลิป:</label>
        <input type="text" id="qrImageUrl" name="qrImageUrl" placeholder="https://example.com/your-image.jpg" required>
        <button type="submit">ตรวจสอบ</button>
    </form>
    <a href="user_slip_verification_history.php" class="button back-to-verification">ประวัติการตรวจสอบสลิป</a>
    <a href="dashboard.php" class="button back-to-dashboard">กลับแดชบอร์ด</a>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('qrImageUrl').addEventListener('input', function() {
    if (this.value.trim() !== '') {
        document.getElementById('slipVerificationForm').submit();
    }
});
</script>

<?php
require '../includes/footer.php';
?>
