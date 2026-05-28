<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://smartlocker-ta.infinityfree.me');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'config.php';

$data  = json_decode(file_get_contents('php://input'), true);
$no_hp = $conn->real_escape_string($data['no_hp'] ?? '');

if (!$no_hp) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor HP tidak boleh kosong']);
    exit;
}

// Cek di tabel data_titip dengan status Aktif
$cek = $conn->query("SELECT * FROM data_titip 
                     WHERE no_hp='$no_hp' 
                     AND status='Aktif' 
                     ORDER BY created_at DESC LIMIT 1");

if ($cek->num_rows == 0) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Nomor HP tidak ditemukan atau barang sudah diambil'
    ]);
    exit;
}

$titip        = $cek->fetch_assoc();
$nomor_loker  = $titip['nomor_loker'];
$id           = $titip['id'];

// Update status data_titip menjadi Diambil
$conn->query("UPDATE data_titip SET status='Diambil' WHERE id=$id");

// Update status loker menjadi kosong
$conn->query("UPDATE loker SET status='kosong' WHERE nomor_loker=$nomor_loker");

echo json_encode([
    'status'      => 'success',
    'message'     => 'Barang berhasil diambil',
    'nomor_loker' => $nomor_loker
]);
?>