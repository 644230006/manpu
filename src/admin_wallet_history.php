<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    $where = 'AND p.used_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
} elseif ($filter === 'monthly') {
    $where = 'AND p.used_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
}

// ตั้งค่าการค้นหาผู้ใช้
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_query = '';
if ($search) {
    $search_query = 'AND u.username LIKE :search';
}

// ดึงข้อมูลประวัติการหักยอดเงินของผู้ใช้
$sql = "SELECT p.*, u.username FROM point_usage_history p 
        JOIN users u ON p.user_id = u.id 
        WHERE 1=1 $where $search_query
        ORDER BY p.used_at DESC 
        LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
if ($search) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$point_usage_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// นับจำนวนรายการทั้งหมด
$count_sql = "SELECT COUNT(*) FROM point_usage_history p 
              JOIN users u ON p.user_id = u.id 
              WHERE 1=1 $where $search_query";
$count_stmt = $conn->prepare($count_sql);
if ($search) {
    $count_stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
}
$count_stmt->execute();
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);
?>

<link rel="stylesheet" href="../assets/css/admin_wallet_history.css">

<section class="dashboard">
    <h2>ประวัติการใช้งานกระเป๋าเงินของผู้ใช้</h2>
    <div class="filter-options">
        <a href="?filter=all" class="button">ทั้งหมด</a>
        <a href="?filter=weekly" class="button">รายสัปดาห์</a>
        <a href="?filter=monthly" class="button">รายเดือน</a>
    </div>
    <form class="search-form" method="get">
        <input type="text" name="search" placeholder="ค้นหาผู้ใช้" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="button">ค้นหา</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>ชื่อผู้ใช้</th>
                <th>วันที่และเวลา</th>
                <th>จำนวนเงินที่หัก</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($point_usage_history as $history): ?>
                <tr>
                    <td><?php echo htmlspecialchars($history['username']); ?></td>
                    <td><?php echo htmlspecialchars($history['used_at']); ?></td>
                    <td><?php echo number_format($history['points_used'], 2); ?> บาท</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo htmlspecialchars($search); ?>" class="button">หน้าก่อนหน้า</a>
        <?php endif; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo htmlspecialchars($search); ?>" class="button">หน้าถัดไป</a>
        <?php endif; ?>
    </div>
    <a href="admin_dashboard.php" class="button">กลับแดชบอร์ดผู้ดูแลระบบ</a>
</section>

<?php
require '../includes/footer.php';
?>
