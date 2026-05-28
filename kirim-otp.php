<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
session_start();
require_once 'config.php';

$data        = json_decode(file_get_contents('php://input'), true);
$no_hp       = $conn->real_escape_string($data['no_hp']);
$nomor_loker = (int)$data['nomor_loker'];

// Cek no hp terdaftar di loker ini
$cek = $conn->query("SELECT * FROM pengguna 
                     WHERE no_hp='$no_hp' 
                     AND nomor_loker=$nomor_loker
                     AND status_barang='dititip' 
                     ORDER BY created_at DESC LIMIT 1");

if ($cek->num_rows == 0) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Nomor HP tidak terdaftar di loker ini'
    ]);
    exit;
}

// Buat kode OTP 6 digit
$otp     = rand(100000, 999999);
$expired = time() + 40;

// Simpan OTP di session
$_SESSION['otp']         = $otp;
$_SESSION['otp_expired'] = $expired;
$_SESSION['otp_no_hp']   = $no_hp;
$_SESSION['otp_loker']   = $nomor_loker;

echo json_encode([
    'status'  => 'success',
    'message' => 'Kode OTP berhasil dibuat',
    'expired' => 40
]);
?>