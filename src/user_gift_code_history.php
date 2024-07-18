<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

// กำหนดจำนวนรายการต่อหน้า
$items_per_page = 15;

// กำหนดตัวกรองและหน้า
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// กำหนดเงื่อนไขสำหรับตัวกรอง
$where_clause = "";
if ($filter == 'week') {
    $where_clause = "AND ugc.used_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
} elseif ($filter == 'month') {
    $where_clause = "AND ugc.used_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
}

// เรียกข้อมูลประวัติการใช้รหัสของขวัญของผู้ใช้
$user_id = $_SESSION['user_id'];
$sql = "SELECT gc.code, gc.amount, gc.is_used, ugc.used_at 
        FROM user_gift_codes ugc 
        JOIN gift_codes gc ON ugc.gift_code_id = gc.id 
        WHERE ugc.user_id = :user_id $where_clause
        ORDER BY ugc.used_at DESC
        LIMIT :offset, :items_per_page";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':items_per_page', $items_per_page, PDO::PARAM_INT);
$stmt->execute();
$gift_code_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// นับจำนวนรายการทั้งหมดสำหรับการแบ่งหน้า
$count_sql = "SELECT COUNT(*) FROM user_gift_codes ugc 
              JOIN gift_codes gc ON ugc.gift_code_id = gc.id 
              WHERE ugc.user_id = :user_id $where_clause";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bindParam(':user_id', $user_id);
$count_stmt->execute();
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);
?>

<!-- เพิ่มการนำเข้า CSS -->
<link rel="stylesheet" href="../assets/css/gift_code_history.css">

<section class="dashboard">
    <h2>ประวัติการใช้รหัสของขวัญ</h2>
    <div class="filters">
        <a href="?filter=all" class="button <?php echo $filter == 'all' ? 'active' : ''; ?>">ทั้งหมด</a>
        <a href="?filter=week" class="button <?php echo $filter == 'week' ? 'active' : ''; ?>">รายสัปดาห์</a>
        <a href="?filter=month" class="button <?php echo $filter == 'month' ? 'active' : ''; ?>">รายเดือน</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>รหัสของขวัญ</th>
                <th>จำนวนเงิน</th>
                <th>สถานะ</th>
                <th>วันที่ใช้</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($gift_code_history) > 0): ?>
                <?php foreach ($gift_code_history as $history): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($history['code']); ?></td>
                        <td><?php echo number_format($history['amount'], 2); ?> บาท</td>
                        <td>
                            <?php if ($history['is_used']): ?>
                                <i class="fas fa-check-circle" style="color: green;"></i> ใช้แล้ว
                            <?php else: ?>
                                ยังไม่ได้ใช้
                            <?php endif; ?>
                        </td>
                        <td><?php echo $history['used_at'] ? $history['used_at'] : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">ไม่มีประวัติการใช้รหัสของขวัญ</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>" class="button">&laquo; ก่อนหน้า</a>
        <?php endif; ?>
        <span>หน้า <?php echo $page; ?> จาก <?php echo $total_pages; ?></span>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>" class="button">ถัดไป &raquo;</a>
        <?php endif; ?>
    </div>
    <a href="dashboard.php" class="button">กลับแดชบอร์ด</a>
</section>

<?php
require '../includes/footer.php';
?>
