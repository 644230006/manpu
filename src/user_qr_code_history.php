<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

$user_id = $_SESSION['user_id'];
$time_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 15;
$offset = ($page - 1) * $records_per_page;

switch ($time_filter) {
    case 'weekly':
        $sql = "SELECT * FROM qr_codes WHERE user_id = :user_id AND created_at >= NOW() - INTERVAL 1 WEEK ORDER BY created_at DESC LIMIT :offset, :records_per_page";
        $count_sql = "SELECT COUNT(*) FROM qr_codes WHERE user_id = :user_id AND created_at >= NOW() - INTERVAL 1 WEEK";
        break;
    case 'monthly':
        $sql = "SELECT * FROM qr_codes WHERE user_id = :user_id AND created_at >= NOW() - INTERVAL 1 MONTH ORDER BY created_at DESC LIMIT :offset, :records_per_page";
        $count_sql = "SELECT COUNT(*) FROM qr_codes WHERE user_id = :user_id AND created_at >= NOW() - INTERVAL 1 MONTH";
        break;
    default:
        $sql = "SELECT * FROM qr_codes WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :offset, :records_per_page";
        $count_sql = "SELECT COUNT(*) FROM qr_codes WHERE user_id = :user_id";
        break;
}

$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$qr_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

?>

<link rel="stylesheet" href="../assets/css/user_qr_code_history.css">

<section class="dashboard">
    <h2>ประวัติการสร้าง QR Code</h2>
    <div class="filter-buttons">
        <a href="user_qr_code_history.php?filter=all" class="button">ทั้งหมด</a>
        <a href="user_qr_code_history.php?filter=weekly" class="button">รายสัปดาห์</a>
        <a href="user_qr_code_history.php?filter=monthly" class="button">รายเดือน</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>จำนวนเงิน</th>
                <th>วันที่สร้าง</th>
                <th>ดู QR Code</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($qr_codes) > 0): ?>
                <?php foreach ($qr_codes as $qr): ?>
                    <tr>
                        <td><?php echo number_format($qr['amount'], 2); ?> บาท</td>
                        <td><?php echo $qr['created_at']; ?></td>
                        <td>
                            <button onclick="showQRCode('<?php echo '../assets/imgqr/' . htmlspecialchars($qr['image_path']); ?>')">ดู QR Code</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">ไม่มีประวัติการสร้าง QR Code</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="user_qr_code_history.php?filter=<?php echo $time_filter; ?>&page=<?php echo $page - 1; ?>" class="button">&laquo; ก่อนหน้า</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="user_qr_code_history.php?filter=<?php echo $time_filter; ?>&page=<?php echo $i; ?>" class="button"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="user_qr_code_history.php?filter=<?php echo $time_filter; ?>&page=<?php echo $page + 1; ?>" class="button">ถัดไป &raquo;</a>
        <?php endif; ?>
    </div>
    <a href="dashboard.php" class="button back-to-dashboard">กลับแดชบอร์ด</a>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showQRCode(imagePath) {
    Swal.fire({
        title: 'QR Code',
        text: 'QR Code ของคุณ',
        imageUrl: imagePath,
        imageWidth: 400,
        imageHeight: 400,
        imageAlt: 'QR Code'
    });
}
</script>

<?php
require '../includes/footer.php';
?>
