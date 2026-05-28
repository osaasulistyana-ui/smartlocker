<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

$nomor_loker = (int)$_GET['loker'];

$result = $conn->query("SELECT nama, alamat FROM pengguna 
                        WHERE nomor_loker=$nomor_loker 
                        AND status_barang='dititip' 
                        ORDER BY created_at DESC LIMIT 1");

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    exit;
}

$row = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'nama'   => $row['nama'],
    'alamat' => $row['alamat']
]);
?>