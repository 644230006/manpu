<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
require '../includes/header.php';

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, first_name, last_name, phone, biller_id, email FROM users WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// อัปเดตข้อมูลส่วนตัว
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $biller_id = $_POST['biller_id'];
    $email = $_POST['email'];

    $update_sql = "UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, phone = :phone, biller_id = :biller_id, email = :email WHERE id = :user_id";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindParam(':username', $username);
    $update_stmt->bindParam(':first_name', $first_name);
    $update_stmt->bindParam(':last_name', $last_name);
    $update_stmt->bindParam(':phone', $phone);
    $update_stmt->bindParam(':biller_id', $biller_id);
    $update_stmt->bindParam(':email', $email);
    $update_stmt->bindParam(':user_id', $user_id);
    $update_stmt->execute();

    // อัปเดตข้อมูลในตัวแปร $user
    $user['username'] = $username;
    $user['first_name'] = $first_name;
    $user['last_name'] = $last_name;
    $user['phone'] = $phone;
    $user['biller_id'] = $biller_id;
    $user['email'] = $email;

    // แสดงข้อความสำเร็จ
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: 'ข้อมูลส่วนตัวของคุณได้รับการอัปเดต'
            });
        });
    </script>";
}
?>

<link rel="stylesheet" href="../assets/css/profile.css">
<section class="dashboard">
    <h2>จัดการข้อมูลส่วนตัว</h2>
    <form action="profile.php" method="post" class="profile-form">
        <label for="username">ชื่อผู้ใช้:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        
        <label for="first_name">ชื่อจริง:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
        
        <label for="last_name">นามสกุล:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
        
        <label for="phone">เบอร์โทร:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        
        <label for="biller_id">Biller ID:</label>
        <input type="text" id="biller_id" name="biller_id" value="<?php echo htmlspecialchars($user['biller_id']); ?>" required>
        
        <label for="email">อีเมล:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        
        <button type="submit" name="update_profile">อัปเดตข้อมูล</button>
    </form>
    <a href="dashboard.php" class="button">กลับแดชบอร์ด</a>
</section>

<?php
require '../includes/footer.php';
?>

<!-- เพิ่มการนำเข้า SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
