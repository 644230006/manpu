้<head>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<?php
require '../vendor/autoload.php';

use Zxing\QrReader;

function sendErrorResponse($message, $status = 'error') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: '$message'
            }).then(function() {
                window.location.href = 'slip_verification.php';
            });
        });
    </script>";
    exit;
}

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

require '../config/db.php';

// ตรวจสอบยอดเงินคงเหลือของผู้ใช้
$sql = "SELECT balance FROM users WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    sendErrorResponse('ไม่พบผู้ใช้');
}

$balance = $user['balance'];

if ($balance < 10) {
    sendErrorResponse('ยอดเงินคงเหลือไม่เพียงพอ');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['qrImage']) && $_FILES['qrImage']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['qrImage']['tmp_name'];
        $fileName = $_FILES['qrImage']['name'];
        $fileSize = $_FILES['qrImage']['size'];
        $fileType = $_FILES['qrImage']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $uploadFileDir = '../assets/slips/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            $dest_path = $uploadFileDir . $fileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // หักยอดเงิน 10 บาท
                $new_balance = $balance - 10;
                $update_balance_sql = "UPDATE users SET balance = :balance WHERE id = :user_id";
                $update_balance_stmt = $conn->prepare($update_balance_sql);
                $update_balance_stmt->bindParam(':balance', $new_balance);
                $update_balance_stmt->bindParam(':user_id', $user_id);
                $update_balance_stmt->execute();

                // บันทึกประวัติการใช้พ้อย
                $insert_point_usage_sql = "INSERT INTO point_usage_history (user_id, points_used, used_at) VALUES (:user_id, 10, NOW())";
                $insert_point_usage_stmt = $conn->prepare($insert_point_usage_sql);
                $insert_point_usage_stmt->bindParam(':user_id', $user_id);
                $insert_point_usage_stmt->execute();

                // บันทึกสลิปลงฐานข้อมูล
                $insert_slip_sql = "INSERT INTO slips (user_id, image_path, created_at) VALUES (:user_id, :image_path, NOW())";
                $insert_slip_stmt = $conn->prepare($insert_slip_sql);
                $insert_slip_stmt->bindParam(':user_id', $user_id);
                $insert_slip_stmt->bindParam(':image_path', $fileName);
                $insert_slip_stmt->execute();

                // ใช้ QR code reader เพื่อถอดรหัส QR code
                try {
                    $qrcode = new QrReader($dest_path);
                    $text = $qrcode->text();

                    if ($text !== false) {
                        // ตัดอักขระแรก 8 ตัวและอักขระสุดท้าย 14 ตัว
                        $cutText = substr($text, 8, -14);

                        // คำขอ SCB API
                        $radid = uniqid('', true); // สร้างคีย์เอง
                        $uid = md5($radid);
                        $api_url = 'https://api-sandbox.partners.scb/partners/sandbox/v1/oauth/token';

                        $post_data = array(
                            "applicationKey" => 'l7ceccce9b7d54449baa008b7d4753f49b',
                            "applicationSecret" => '878e8e77d1494e8586aad4f11d8a61c7'
                        );

                        $headers = array(
                            'Content-Type: application/json',
                            'resourceOwnerId: l7ceccce9b7d54449baa008b7d4753f49b',
                            'accept-language: EN',
                            'requestUId: ' . $uid
                        );

                        $ch = curl_init($api_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $response = curl_exec($ch);

                        if (curl_errno($ch)) {
                            sendErrorResponse('Curl error: ' . curl_error($ch));
                        }

                        curl_close($ch);
                        $response = json_decode($response);

                        if (!$response || !isset($response->data->accessToken)) {
                            sendErrorResponse('Failed to retrieve access token');
                        }

                        $accessToken = $response->data->accessToken;

                        $radid = uniqid('', true); // สร้างคีย์เอง
                        $uid = md5($radid);
                        $qruri = 'https://api-sandbox.partners.scb/partners/sandbox/v1/payment/billpayment/transactions/' . $cutText . '?sendingBank=014';
                        $authen = "Bearer " . $accessToken;
                        $headers = array(
                            "authorization: $authen",
                            "requestUId: " . $uid,
                            "resourceOwnerId: l7ceccce9b7d54449baa008b7d4753f49b",
                            "accept-language: EN"
                        );

                        $ch = curl_init($qruri);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $response = curl_exec($ch);

                        if (curl_errno($ch)) {
                            sendErrorResponse('Curl error: ' . curl_error($ch));
                        }

                        if ($response === false) {
                            sendErrorResponse('cURL exec failed');
                        }

                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($httpCode != 200) {
                            sendErrorResponse("HTTP Code: $httpCode - Response: $response");
                        }

                        $response_data = json_decode($response, true);

                        // ดึงข้อมูลที่ต้องการ
                        $ref1 = $response_data['data']['ref1'] ?? null;
                        $ref2 = $response_data['data']['ref2'] ?? null;
                        $amount = $response_data['data']['amount'] ?? null;

                        // ตรวจสอบกับตาราง qr_codes
                        $check_qr_sql = "SELECT * FROM qr_codes WHERE ref1 = :ref1 AND ref2 = :ref2 AND amount = :amount";
                        $check_qr_stmt = $conn->prepare($check_qr_sql);
                        $check_qr_stmt->bindParam(':ref1', $ref1);
                        $check_qr_stmt->bindParam(':ref2', $ref2);
                        $check_qr_stmt->bindParam(':amount', $amount);
                        $check_qr_stmt->execute();
                        $qr_code = $check_qr_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($qr_code) {
                            $status = 'สลิปถูกต้อง';
                        } else {
                            $status = 'ไม่ถูกต้องเนื่องจากข้อมูลไม่ตรงกัน';
                        }

                        // ตรวจสอบการใช้สลิปซ้ำ
                        $check_slip_sql = "SELECT * FROM slip_verification WHERE ref1 = :ref1 AND ref2 = :ref2 AND amount = :amount";
                        $check_slip_stmt = $conn->prepare($check_slip_sql);
                        $check_slip_stmt->bindParam(':ref1', $ref1);
                        $check_slip_stmt->bindParam(':ref2', $ref2);
                        $check_slip_stmt->bindParam(':amount', $amount);
                        $check_slip_stmt->execute();
                        $slip = $check_slip_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($slip) {
                            $status = 'ไม่ถูกต้องเนื่องจากใช้สลิปเก่าที่ได้รับการตรวจสอบไปแล้ว';
                        }

                        // บันทึกการตรวจสอบ
                        $insert_verification_sql = "INSERT INTO slip_verification (user_id, ref1, ref2, amount, status, created_at) VALUES (:user_id, :ref1, :ref2, :amount, :status, NOW())";
                        $insert_verification_stmt = $conn->prepare($insert_verification_sql);
                        $insert_verification_stmt->bindParam(':user_id', $user_id);
                        $insert_verification_stmt->bindParam(':ref1', $ref1);
                        $insert_verification_stmt->bindParam(':ref2', $ref2);
                        $insert_verification_stmt->bindParam(':amount', $amount);
                        $insert_verification_stmt->bindParam(':status', $status);
                        $insert_verification_stmt->execute();

                        echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'สำเร็จ',
                                    text: 'การตรวจสอบสลิปสำเร็จ: $status'
                                }).then(function() {
                                    window.location.href = 'slip_verification.php';
                                });
                            });
                        </script>";
                    } else {
                        // บันทึกการตรวจสอบที่ล้มเหลว
                        $status = 'ไม่สามารถถอดรหัส QR code ได้';
                        $insert_verification_sql = "INSERT INTO slip_verification (user_id, ref1, ref2, amount, status, created_at) VALUES (:user_id, NULL, NULL, NULL, :status, NOW())";
                        $insert_verification_stmt = $conn->prepare($insert_verification_sql);
                        $insert_verification_stmt->bindParam(':user_id', $user_id);
                        $insert_verification_stmt->bindParam(':status', $status);
                        $insert_verification_stmt->execute();

                        sendErrorResponse($status);
                    }
                } catch (Exception $e) {
                    // บันทึกการตรวจสอบที่ล้มเหลว
                    $status = 'Error decoding QR code: ' . $e->getMessage();
                    $insert_verification_sql = "INSERT INTO slip_verification (user_id, ref1, ref2, amount, status, created_at) VALUES (:user_id, NULL, NULL, NULL, :status, NOW())";
                    $insert_verification_stmt = $conn->prepare($insert_verification_sql);
                    $insert_verification_stmt->bindParam(':user_id', $user_id);
                    $insert_verification_stmt->bindParam(':status', $status);
                    $insert_verification_stmt->execute();

                    sendErrorResponse($status);
                }
            } else {
                sendErrorResponse('Error moving the uploaded file.');
            }
        } else {
            sendErrorResponse('Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions));
        }
    } else {
        sendErrorResponse('No file uploaded or there was an upload error.');
    }
} else {
    sendErrorResponse('Invalid request method.');
}
?>
