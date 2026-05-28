<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://smartlocker-ta.infinityfree.me');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'config.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { $data = $_POST; }

$nama          = isset($data['nama'])          ? trim($data['nama'])          : '';
$no_hp         = isset($data['no_hp'])         ? trim($data['no_hp'])         : '';
$tanggal_pakai = isset($data['tanggal_pakai']) ? trim($data['tanggal_pakai']) : '';
$alamat        = isset($data['alamat'])        ? trim($data['alamat'])        : '';
$nomor_loker   = isset($data['nomor_loker'])   ? trim($data['nomor_loker'])   : '';

if (!$nama || !$no_hp || !$tanggal_pakai || !$alamat || !$nomor_loker) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

// Ambil angka dari nomor loker, misal "Loker 1" → 1
$nomor_loker_int = (int) filter_var($nomor_loker, FILTER_SANITIZE_NUMBER_INT);

if ($nomor_loker_int <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor loker tidak valid']);
    exit;
}

// Cek koneksi
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi DB gagal: ' . $conn->connect_error]);
    exit;
}

// Insert data dengan status Aktif
$stmt = $conn->prepare("INSERT INTO data_titip (nama, no_hp, tanggal_pakai, alamat, nomor_loker, status, created_at) VALUES (?, ?, ?, ?, ?, 'Aktif', NOW())");

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare gagal: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssssi", $nama, $no_hp, $tanggal_pakai, $alamat, $nomor_loker_int);

if ($stmt->execute()) {
    // Update status loker menjadi terisi
    $conn->query("UPDATE loker SET status='terisi' WHERE nomor_loker=$nomor_loker_int");

    echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal simpan: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>