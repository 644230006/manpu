<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

// เรียกข้อมูลประวัติการใช้รหัสของขวัญของผู้ใช้
$user_id = $_SESSION['user_id'];
$sql = "SELECT gc.code, gc.amount, gc.is_used, ugc.used_at 
        FROM user_gift_codes ugc 
        JOIN gift_codes gc ON ugc.gift_code_id = gc.id 
        WHERE ugc.user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$gift_code_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <a href="admin_dashboard.php" class="button">กลับแดชบอร์ด</a>
</section>

<?php
require '../includes/footer.php';
?>
