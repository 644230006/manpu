<?php
require '../vendor/autoload.php';

use Zxing\QrReader;

function sendErrorResponse($message) {
    echo $message;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['qrImageUrl']) && !empty($_POST['qrImageUrl'])) {
        $imageUrl = $_POST['qrImageUrl'];

        // ตรวจสอบว่า URL ใช้ได้หรือไม่
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            sendErrorResponse('ลิงก์รูปภาพไม่ถูกต้อง');
        }

        // ตั้งค่าหัวข้อ HTTP
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' => "User-Agent: PHP\r\n" .
                            "Authorization: Bearer VWg+h2U0kbzkiFpXYzPFID1piDS3vSt8XdBU75BNMEoFv3bXCrCvMi/e9PKJgbTcWWOffd8rt3eg93dMyrjfoyD/VOQRiIWDUWtXq7ZiaCHG8AX5rTPx9TLqBVTHMTLQhLEAETreVktbhcB3xJLm+gdB04t89/1O/w1cDnyilFU="
            )
        );

        $context = stream_context_create($opts);

        // ดาวน์โหลดรูปภาพ
        $imageContent = @file_get_contents($imageUrl, false, $context);
        if ($imageContent === FALSE) {
            sendErrorResponse('ไม่สามารถดาวน์โหลดรูปภาพจากลิงก์ที่ระบุได้');
        }

        // สร้างชื่อไฟล์และบันทึกลงในเครื่อง
        $uploadFileDir = '../assets/slips/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }
        $fileName = basename($imageUrl);
        $dest_path = $uploadFileDir . $fileName;

        if (!file_put_contents($dest_path, $imageContent)) {
            sendErrorResponse('เกิดข้อผิดพลาดในการบันทึกรูปภาพ');
        }

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
                    sendErrorResponse('เกิดข้อผิดพลาดจากการเรียก cURL: ' . curl_error($ch));
                }

                curl_close($ch);
                $response = json_decode($response);

                if (!$response || !isset($response->data->accessToken)) {
                    sendErrorResponse('ไม่สามารถดึงข้อมูล access token ได้');
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
                    sendErrorResponse('เกิดข้อผิดพลาดจากการเรียก cURL: ' . curl_error($ch));
                }

                if ($response === false) {
                    sendErrorResponse('การเรียก cURL ล้มเหลว');
                }

                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode != 200) {
                    sendErrorResponse("รหัส HTTP: $httpCode - การตอบกลับ: $response");
                }

                $response_data = json_decode($response, true);

                // ดึงข้อมูลที่ต้องการ
                $ref1 = $response_data['data']['ref1'] ?? null;
                $ref2 = $response_data['data']['ref2'] ?? null;
                $amount = $response_data['data']['amount'] ?? null;

                // สถานะ
                $status = 'การตรวจสอบสลิปสำเร็จ: ref1=' . $ref1 . ', ref2=' . $ref2 . ', amount=' . $amount;
                echo $status;

            } else {
                sendErrorResponse('ไม่สามารถถอดรหัส QR code ได้');
            }
        } catch (Exception $e) {
            sendErrorResponse('เกิดข้อผิดพลาดในการถอดรหัส QR code: ' . $e->getMessage());
        }
    } else {
        sendErrorResponse('ลิงก์รูปภาพไม่ถูกต้อง');
    }
} else {
    sendErrorResponse('วิธีการร้องขอไม่ถูกต้อง');
}
?>
