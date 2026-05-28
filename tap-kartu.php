<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

$uid = $conn->real_escape_string($_GET['uid'] ?? '');

if (empty($uid)) {
    echo json_encode(['status' => 'error', 'message' => 'UID tidak dikirim']);
    exit;
}

// Cari pengguna berdasarkan UID kartu, dan barang masih dititip
$cek = $conn->query("SELECT * FROM pengguna 
                     WHERE uid_kartu='$uid' 
                     AND status_barang='dititip' 
                     ORDER BY created_at DESC LIMIT 1");

if ($cek->num_rows == 0) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Kartu tidak dikenali atau tidak ada barang yang dititip'
    ]);
    exit;
}

$pengguna    = $cek->fetch_assoc();
$nomor_loker = $pengguna['nomor_loker'];
$id_pengguna = $pengguna['id'];

// Update status barang menjadi diambil
$conn->query("UPDATE pengguna 
              SET status_barang='diambil' 
              WHERE id=$id_pengguna");

// Update status loker menjadi kosong
$conn->query("UPDATE loker 
              SET status='kosong' 
              WHERE nomor_loker=$nomor_loker");

// Kirim perintah BUKA ke ESP32 via tabel perintah_loker
$conn->query("INSERT INTO perintah_loker (nomor_loker, perintah, status)
              VALUES ($nomor_loker, 'BUKA', 'menunggu')");

echo json_encode([
    'status'      => 'success',
    'message'     => 'Kartu dikenali, loker sedang dibuka',
    'nama'        => $pengguna['nama'],
    'nomor_loker' => $nomor_loker
]);
?>