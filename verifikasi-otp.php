<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    $data = $_POST;
}

$otp_input   = isset($data['otp'])         ? trim($data['otp'])          : '';
$otp_benar   = isset($data['otp_benar'])   ? trim($data['otp_benar'])    : '';
$otp_expired = isset($data['otp_expired']) ? intval($data['otp_expired']) : 0;

if (!$otp_input || !$otp_benar) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

if (time() > $otp_expired) {
    echo json_encode([
        'status'  => 'expired',
        'message' => 'Kode OTP sudah kadaluarsa'
    ]);
    exit;
}

if ($otp_input == $otp_benar) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Verifikasi OTP berhasil'
    ]);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Kode OTP salah, coba lagi'
    ]);
}
?>