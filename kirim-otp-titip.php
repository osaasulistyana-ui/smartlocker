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

$no_hp = isset($data['no_hp']) ? trim($data['no_hp']) : '';

if (!$no_hp) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Nomor HP tidak boleh kosong'
    ]);
    exit;
}

$otp     = rand(100000, 999999);
$expired = 120;

echo json_encode([
    'status'  => 'success',
    'message' => 'Kode OTP berhasil dibuat',
    'otp'     => $otp,
    'expired' => $expired
]);
?>