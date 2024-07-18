<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

// ดึงข้อมูลสถานะการใช้งานรหัสของขวัญ
$sql = "SELECT code,amount, usage_limit	, is_used,created_at FROM gift_codes";
$stmt = $conn->prepare($sql);
$stmt->execute();
$gift_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../assets/css/status_gift_code.css">

<section class="dashboard">
    <h2>สถานะการใช้งานรหัสของขวัญ</h2>
    <table>
        <thead>
            <tr>
                <th>รหัสของขวัญ</th>
                <th>จำนวนเงิน</th>
                <th>จำนวนครั้งที่ใช้ได้</th>
                <th>จำนวนครั้งที่ใช้แล้ว</th>
                <th>ถูกสร้างเมื่อ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gift_codes as $code): ?>
                <tr>
                    <td><?php echo htmlspecialchars($code['code']); ?></td>
                    <td><?php echo htmlspecialchars($code['amount']); ?></td>
                    <td><?php echo htmlspecialchars($code['usage_limit']); ?></td>
                    <td><?php echo htmlspecialchars($code['is_used']); ?></td>
                    <td><?php echo htmlspecialchars($code['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="admin_dashboard.php" class="button">กลับแดชบอร์ด</a>
</section>

<?php
require '../includes/footer.php';
?>
