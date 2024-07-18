<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

$users_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $users_per_page;

$search_username = isset($_GET['search_username']) ? $_GET['search_username'] : '';

// ดึงข้อมูลผู้ใช้ทั้งหมด
$sql = "SELECT id, username, first_name, last_name, email, phone, role, status 
        FROM users 
        WHERE username LIKE :username
        LIMIT :start, :users_per_page";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':username', '%' . $search_username . '%', PDO::PARAM_STR);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':users_per_page', $users_per_page, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลจำนวนผู้ใช้ทั้งหมด
$total_users_sql = "SELECT COUNT(*) FROM users WHERE username LIKE :username";
$total_users_stmt = $conn->prepare($total_users_sql);
$total_users_stmt->bindValue(':username', '%' . $search_username . '%', PDO::PARAM_STR);
$total_users_stmt->execute();
$total_users = $total_users_stmt->fetchColumn();
$total_pages = ceil($total_users / $users_per_page);

// เปลี่ยนสถานะการใช้งานผู้ใช้
if (isset($_GET['change_status']) && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $new_status = $_GET['change_status'] == 'active' ? 'active' : 'suspended';

    $update_status_sql = "UPDATE users SET status = :status WHERE id = :id";
    $update_status_stmt = $conn->prepare($update_status_sql);
    $update_status_stmt->bindParam(':status', $new_status);
    $update_status_stmt->bindParam(':id', $user_id);
    $update_status_stmt->execute();

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: 'เปลี่ยนสถานะผู้ใช้สำเร็จ',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                window.location.href = 'user_management.php';
            });
        });
    </script>";
    exit();
}
?>

<link rel="stylesheet" href="/PJ/assets/css/user_management.css">
<section class="dashboard">
    <h2>จัดการผู้ใช้</h2>
    <form class="search-form" method="GET" action="user_management.php">
        <input type="text" name="search_username" placeholder="ค้นหาชื่อผู้ใช้" value="<?php echo htmlspecialchars($search_username); ?>">
        <button type="submit">ค้นหา</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>ชื่อผู้ใช้</th>
                <th>ชื่อจริง</th>
                <th>นามสกุล</th>
                <th>อีเมล</th>
                <th>เบอร์โทร</th>
                <th>บทบาท</th>
                <th>สถานะ</th>
                <th>การกระทำ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo htmlspecialchars($user['status']); ?></td>
                    <td class="action-buttons">
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="button edit">แก้ไข</a>
                        <?php if ($user['status'] === 'active'): ?>
                            <a href="user_management.php?change_status=suspended&user_id=<?php echo $user['id']; ?>" class="button suspend">ระงับการใช้งาน</a>
                        <?php else: ?>
                            <a href="user_management.php?change_status=active&user_id=<?php echo $user['id']; ?>" class="button activate">เปิดใช้งาน</a>
                        <?php endif; ?>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="user_management.php?page=<?php echo $i; ?>&search_username=<?php echo htmlspecialchars($search_username); ?>" class="button"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <a href="admin_dashboard.php" class="button">กลับแดชบอร์ดผู้ดูแลระบบ</a>
</section>

<?php
require '../includes/footer.php';
?>

<!-- เพิ่มการนำเข้า SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
