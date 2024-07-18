<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    $update_sql = "UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, role = :role WHERE id = :user_id";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindParam(':username', $username);
    $update_stmt->bindParam(':first_name', $first_name);
    $update_stmt->bindParam(':last_name', $last_name);
    $update_stmt->bindParam(':email', $email);
    $update_stmt->bindParam(':phone', $phone);
    $update_stmt->bindParam(':role', $role);
    $update_stmt->bindParam(':user_id', $user_id);
    $update_stmt->execute();

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: 'ข้อมูลผู้ใช้ได้รับการอัปเดตแล้ว'
            }).then(() => {
                window.location.href = 'user_management.php';
            });
        });
    </script>";
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    header("Location: user_management.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลผู้ใช้</title>
    <link rel="stylesheet" href="../assets/css/edit_user.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <section class="dashboard">
        <h2>แก้ไขข้อมูลผู้ใช้</h2>
        <form action="edit_user.php" method="post" class="edit-form">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <label for="username">ชื่อผู้ใช้:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            
            <label for="first_name">ชื่อจริง:</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            
            <label for="last_name">นามสกุล:</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            
            <label for="email">อีเมล:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label for="phone">เบอร์โทร:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            
            <label for="role">บทบาท:</label>
            <select id="role" name="role" required>
                <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>ผู้ใช้</option>
                <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>ผู้ดูแลระบบ</option>
            </select>
            
            <button type="submit" name="update_user">อัปเดตข้อมูล</button>
        </form>
        <a href="admin_dashboard.php" class="button">กลับแดชบอร์ดผู้ดูแลระบบ</a>
    </section>
</body>
</html>
