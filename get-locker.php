<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'config.php';

$result = $conn->query("SELECT nomor_loker, status FROM loker ORDER BY nomor_loker ASC");

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Query gagal']);
    exit;
}

$lokers = [];
while ($row = $result->fetch_assoc()) {
    $lokers[] = [
        'nomor_loker' => $row['nomor_loker'],
        'status'      => $row['status']
    ];
}

echo json_encode(['status' => 'success', 'data' => $lokers]);
?>