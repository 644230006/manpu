<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require '../config/db.php';
require '../includes/header.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// ตรวจสอบว่าข้อมูลผู้ใช้ครบถ้วนหรือไม่
$missing_info = false;
foreach ($user as $key => $value) {
    if (empty($value) && $key != 'balance') {
        $missing_info = true;
        break;
    }
}

if ($missing_info) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'กรุณากรอกข้อมูลส่วนตัวให้ครบถ้วนก่อนสร้าง QR Code'
            }).then(function() {
                window.location.href = 'profile.php';
            });
        });
    </script>";
    exit();
}

// สร้าง QR Code
$radid = uniqid('', true); // สร้างคีย์เอง
$uid = md5($radid);
$amount = $_POST['amount'];

$api_url = 'https://api-sandbox.partners.scb/partners/sandbox/v1/oauth/token';
$post_data = array(
    "applicationKey" => 'l7ceccce9b7d54449baa008b7d4753f49b',
    "applicationSecret" => '878e8e77d1494e8586aad4f11d8a61c7'
);
$headers = array(
    'Content-Type: application/json',
    'resourceOwnerId: l7ceccce9b7d54449baa008b7d4753f49b',
    'accept-language: EN',
    'requestUId:' . $uid
);

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

curl_close($ch);

$response = json_decode($response);

if (!isset($response->data)) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'ไม่สามารถสร้าง QR Code ได้ กรุณาลองใหม่อีกครั้ง'
            }).then(function() {
                window.location.href = 'createqrcode.php';
            });
        });
    </script>";
    exit();
}

$data = $response->data;
$accessToken = $data->accessToken;

// สุ่มตัวเลข 8 ตัวสำหรับ ref1 และ ref2
$ref1 = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
$ref2 = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

$qruri = 'https://api-sandbox.partners.scb/partners/sandbox/v1/payment/qrcode/create';
$post_data = array(
    "qrType" => "PP",
    "ppType" => "BILLERID",
    "ppId" => $user['biller_id'],
    "amount" => $amount,
    "ref1" => $ref1,
    "ref2" => $ref2,
    "ref3" => "CIW"
);
$authen = "Bearer " . $accessToken;
$headers = array(
    "Content-Type: application/json",
    "authorization: $authen",
    "resourceOwnerId: l7ceccce9b7d54449baa008b7d4753f49b",
    "requestUId:" . $uid,
    "accept-language: EN"
);
$ch = curl_init($qruri);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

curl_close($ch);

$response = json_decode($response);

if (!isset($response->data)) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'ไม่สามารถสร้าง QR Code ได้ กรุณาลองใหม่อีกครั้ง'
            }).then(function() {
                window.location.href = 'createqrcode.php';
            });
        });
    </script>";
    exit();
}

$data = $response->data;
$qrimage = $data->qrImage;

$qr_codes_directory = '../assets/imgqr/';
if (!is_dir($qr_codes_directory)) {
    mkdir($qr_codes_directory, 0777, true);
}

$base64_image = $qrimage;
$image = base64_decode($base64_image);
$file_name = 'qr_' . time() . '.png';
file_put_contents($qr_codes_directory . $file_name, $image);

// เพิ่มข้อมูลรูปภาพลงในฐานข้อมูล
$insert_sql = "INSERT INTO qr_codes (user_id, image_path, amount, ref1, ref2, created_at) VALUES (:user_id, :image_path, :amount, :ref1, :ref2, NOW())";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bindParam(':user_id', $user_id);
$insert_stmt->bindParam(':image_path', $file_name);
$insert_stmt->bindParam(':amount', $amount);
$insert_stmt->bindParam(':ref1', $ref1);
$insert_stmt->bindParam(':ref2', $ref2);
$insert_stmt->execute();

echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: 'สร้าง QR Code สำเร็จแล้ว'
        }).then(function() {
            window.location.href = 'createqrcode.php';
        });
    });
</script>";
?>
