<?php
session_start();
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลผู้ใช้ในฐานข้อมูล
    try {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'suspended') {
                header("Location: login.php?error=suspended");
                exit();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // ตรวจสอบบทบาทของผู้ใช้และเปลี่ยนเส้นทางไปยังแดชบอร์ดที่เหมาะสม
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php?success=login");
            } else {
                header("Location: dashboard.php?success=login");
            }
            exit();
        } else {
            header("Location: login.php?error=invalid_credentials");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: login.php?error=db_error");
        exit();
    }
}
?>
