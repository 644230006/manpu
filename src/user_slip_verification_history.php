<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

$user_id = $_SESSION['user_id'];
$records_per_page = 15;

// Determine the current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $records_per_page;

// Determine the filter type
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

switch ($filter) {
    case 'week':
        $sql = "SELECT * FROM slip_verification WHERE user_id = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK) ORDER BY created_at DESC LIMIT :start, :records_per_page";
        break;
    case 'month':
        $sql = "SELECT * FROM slip_verification WHERE user_id = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY created_at DESC LIMIT :start, :records_per_page";
        break;
    default:
        $sql = "SELECT * FROM slip_verification WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :start, :records_per_page";
        break;
}

$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$slip_verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total number of records
$total_records_sql = "SELECT COUNT(*) FROM slip_verification WHERE user_id = :user_id";
$total_stmt = $conn->prepare($total_records_sql);
$total_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$total_stmt->execute();
$total_records = $total_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);
?>

<link rel="stylesheet" href="../assets/css/user_slip_verification_history.css">
<section class="dashboard">
    <h2>ประวัติการตรวจสอบสลิป</h2>

    <div class="filter-buttons">
        <a href="?filter=all" class="button <?php echo $filter == 'all' ? 'active' : ''; ?>">ทั้งหมด</a>
        <a href="?filter=week" class="button <?php echo $filter == 'week' ? 'active' : ''; ?>">รายสัปดาห์</a>
        <a href="?filter=month" class="button <?php echo $filter == 'month' ? 'active' : ''; ?>">รายเดือน</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>วันที่และเวลา</th>
                <th>จำนวนเงิน</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($slip_verifications as $verification): ?>
                <tr>
                    <td><?php echo htmlspecialchars($verification['created_at']); ?></td>
                    <td><?php echo number_format($verification['amount'], 2); ?> บาท</td>
                    <td>
                        <?php if ($verification['status'] == 'ถูกต้อง'): ?>
                            <i class="fas fa-check-circle" style="color: green;"></i> ถูกต้อง
                        <?php elseif ($verification['status'] == 'สลิปถูกต้อง'): ?>
                            <i class="fas fa-check-circle" style="color: green;"></i> ถูกต้อง
                        <?php elseif ($verification['status'] == 'ไม่ถูกต้องเนื่องจากข้อมูลไม่ตรงกัน'): ?>
                            <i class="fas fa-times-circle" style="color: red;"></i> ไม่ถูกต้องเนื่องจากข้อมูลไม่ตรงกัน
                        <?php elseif ($verification['status'] == 'ไม่ถูกต้องเนื่องจากใช้สลิปเก่าที่ได้รับการตรวจสอบไปแล้ว'): ?>
                            <i class="fas fa-exclamation-circle" style="color: orange;"></i> สลิปซ้ำ
                        <?php elseif ($verification['status'] == 'ไม่สามารถถอดรหัส QR code ได้'): ?>
                            <i class="fas fa-times-circle" style="color: red;"></i> ไม่สามารถถอดรหัส QR code ได้
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>" class="button <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <a href="dashboard.php" class="button">กลับแดชบอร์ด</a>
</section>

<?php
require '../includes/footer.php';
?>
