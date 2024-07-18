<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

// Fetch data for charts
$gift_code_usage_sql = "SELECT DATE(used_at) as date, COUNT(*) as count FROM user_gift_codes WHERE used_at IS NOT NULL GROUP BY DATE(used_at)";
$gift_code_usage_stmt = $conn->prepare($gift_code_usage_sql);
$gift_code_usage_stmt->execute();
$gift_code_usage_data = $gift_code_usage_stmt->fetchAll(PDO::FETCH_ASSOC);

$qr_code_creation_sql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM qr_codes GROUP BY DATE(created_at)";
$qr_code_creation_stmt = $conn->prepare($qr_code_creation_sql);
$qr_code_creation_stmt->execute();
$qr_code_creation_data = $qr_code_creation_stmt->fetchAll(PDO::FETCH_ASSOC);

$slip_verification_sql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM slip_verification GROUP BY DATE(created_at)";
$slip_verification_stmt = $conn->prepare($slip_verification_sql);
$slip_verification_stmt->execute();
$slip_verification_data = $slip_verification_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../assets/css/report.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<section class="dashboard">
    <h2>รายงานผลทั้งหมด</h2>
    <br>
    <div class="card-container">
        <div class="card">
            <h3>การใช้รหัสของขวัญ</h3>
            <canvas id="giftCodeUsageChart"></canvas>
        </div>
        <br>
        <div class="card">
            <h3>การสร้าง QR Code</h3>
            <canvas id="qrCodeCreationChart"></canvas>
        </div>
        <br>
        <div class="card">
            <h3>การตรวจสอบสลิป</h3>
            <canvas id="slipVerificationChart"></canvas>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const giftCodeUsageCtx = document.getElementById('giftCodeUsageChart').getContext('2d');
    const qrCodeCreationCtx = document.getElementById('qrCodeCreationChart').getContext('2d');
    const slipVerificationCtx = document.getElementById('slipVerificationChart').getContext('2d');

    const giftCodeUsageData = <?php echo json_encode($gift_code_usage_data); ?>;
    const qrCodeCreationData = <?php echo json_encode($qr_code_creation_data); ?>;
    const slipVerificationData = <?php echo json_encode($slip_verification_data); ?>;

    new Chart(giftCodeUsageCtx, {
        type: 'line',
        data: {
            labels: giftCodeUsageData.map(item => item.date),
            datasets: [{
                label: 'จำนวนการใช้รหัสของขวัญ',
                data: giftCodeUsageData.map(item => item.count),
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2
            }]
        }
    });

    new Chart(qrCodeCreationCtx, {
        type: 'line',
        data: {
            labels: qrCodeCreationData.map(item => item.date),
            datasets: [{
                label: 'จำนวนการสร้าง QR Code',
                data: qrCodeCreationData.map(item => item.count),
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 2
            }]
        }
    });

    new Chart(slipVerificationCtx, {
        type: 'line',
        data: {
            labels: slipVerificationData.map(item => item.date),
            datasets: [{
                label: 'จำนวนการตรวจสอบสลิป',
                data: slipVerificationData.map(item => item.count),
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 2
            }]
        }
    });
});
</script>

<?php
require '../includes/footer.php';
?>
