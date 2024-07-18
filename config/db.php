<?php
$servername = "localhost:3306";
$username = "manpu_strong_here";
$password = "fR$5s76m5";
$dbname = "manpu_strong_here"; // แก้ไขเป็นชื่อฐานข้อมูลของคุณ

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>