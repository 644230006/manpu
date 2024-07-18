<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

// ตั้งค่าการแบ่งหน้า
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ตั้งค่าการกรองตามช่วงเวลา
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = '';
if ($filter === 'weekly') {
    $where = 'AND used_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
} elseif ($filter === 'monthly') {
    $where = 'AND used_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
}

// ดึงข้อมูลประวัติการหักยอดเงินของผู้ใช้
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM point_usage_history WHERE user_id = :user_id $where ORDER BY used_at DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$point_usage_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// นับจำนวนรายการทั้งหมด
$count_sql = "SELECT COUNT(*) FROM point_usage_history WHERE user_id = :user_id $where";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$count_stmt->execute();
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);
?>

<link rel="stylesheet" href="../assets/css/user_wallet_history.css">

<section class="dashboard">
    <h2>ประวัติการใช้งานกระเป๋าเงิน</h2>
    <div class="filter-options">
        <a href="?filter=all" class="button">ทั้งหมด</a>
        <a href="?filter=weekly" class="button">รายสัปดาห์</a>
        <a href="?filter=monthly" class="button">รายเดือน</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>วันที่และเวลา</th>
                <th>จำนวนเงินที่หัก</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($point_usage_history as $history): ?>
                <tr>
                    <td><?php echo htmlspecialchars($history['used_at']); ?></td>
                    <td><?php echo number_format($history['points_used'], 2); ?> บาท</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>" class="button">หน้าก่อนหน้า</a>
        <?php endif; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>" class="button">หน้าถัดไป</a>
        <?php endif; ?>
    </div>
    <a href="dashboard.php" class="button">กลับแดชบอร์ด</a>
</section>

<?php
require '../includes/footer.php';
?>
