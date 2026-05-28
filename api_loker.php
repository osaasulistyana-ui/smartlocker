<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================
// DATABASE
// ==========================
$host = "sql113.byetcluster.com";
$db   = "if0_41742559_smartlocker";
$user = "if0_41742559";
$pass = "BvUOkeszaj7bpB";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "msg" => "db_error"]);
    exit;
}

$conn->set_charset("utf8");

// ==========================
// API KEY
// ==========================
$action = $_GET['action'] ?? '';
$key    = $_GET['key']    ?? '';

if ($key !== 'SECRET_LOCKER_KEY_01') {
    http_response_code(403);
    echo json_encode(["status" => "error", "msg" => "forbidden"]);
    exit;
}

// ======================================================
// 1. VALIDASI OTP
// ======================================================
if ($action == "validasi_otp") {

    $otp = $_GET['otp'] ?? '';

    if (empty($otp)) {
        echo json_encode(["status" => "error", "msg" => "otp_kosong"]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT id, nomor_loker, otp_expired
        FROM perintah_loker
        WHERE otp=?
        AND status='menunggu'
        LIMIT 1
    ");
    $stmt->bind_param("s", $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "msg" => "otp_salah"]);
        exit;
    }

    $row = $result->fetch_assoc();

    if (time() > intval($row['otp_expired'])) {
        echo json_encode(["status" => "error", "msg" => "otp_kadaluarsa"]);
        exit;
    }

    echo json_encode([
        "status"      => "ok",
        "nomor_loker" => intval($row['nomor_loker'])
    ]);
    exit;
}

// ======================================================
// 2. CEK RFID
// ======================================================
if ($action == "cek_rfid") {

    $uid = $_GET['rfid'] ?? '';

    $stmt = $conn->prepare("
        SELECT nomor_loker
        FROM perintah_loker
        WHERE uid_rfid=?
        AND status_loker='AKTIF'
        LIMIT 1
    ");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "ada_pin"     => true,
            "nomor_loker" => intval($row['nomor_loker'])
        ]);
    } else {
        echo json_encode(["ada_pin" => false]);
    }
    exit;
}

// ======================================================
// 3. SIMPAN PIN + RFID
// ======================================================
if ($action == "simpan_pin") {

    $uid   = $_GET['rfid']        ?? '';
    $pin   = $_GET['pin']         ?? '';
    $loker = intval($_GET['nomor_loker'] ?? 0);

    if (!$uid || !$pin || $loker <= 0) {
        echo json_encode(["status" => "error"]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE perintah_loker
        SET uid_rfid=?, pin_user=?, status_loker='AKTIF', status='aktif'
        WHERE nomor_loker=?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->bind_param("ssi", $uid, $pin, $loker);
    $stmt->execute();

    echo json_encode(["status" => "ok"]);
    exit;
}

// ======================================================
// 4. VERIFIKASI PIN
// ======================================================
if ($action == "verifikasi_pin") {

    $uid = $_GET['rfid'] ?? '';
    $pin = $_GET['pin']  ?? '';

    $stmt = $conn->prepare("
        SELECT nomor_loker
        FROM perintah_loker
        WHERE uid_rfid=?
        AND pin_user=?
        AND status_loker='AKTIF'
        LIMIT 1
    ");
    $stmt->bind_param("ss", $uid, $pin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "msg" => "pin_salah"]);
        exit;
    }

    $row = $result->fetch_assoc();
    echo json_encode([
        "status"      => "ok",
        "nomor_loker" => intval($row['nomor_loker'])
    ]);
    exit;
}

// ======================================================
// 5. HAPUS DATA (DONE)
// ======================================================
if ($action == "hapus_pin") {

    $uid = $_GET['rfid'] ?? '';

    $stmt = $conn->prepare("
        UPDATE perintah_loker
        SET uid_rfid=NULL, pin_user=NULL, otp=NULL,
            status='selesai', status_loker='KOSONG', selesai_at=NOW()
        WHERE uid_rfid=?
        LIMIT 1
    ");
    $stmt->bind_param("s", $uid);
    $stmt->execute();

    echo json_encode(["status" => "ok"]);
    exit;
}

// ======================================================
// 6. CEK PERINTAH PENDING  ←← ENDPOINT BARU UNTUK ESP32
// ======================================================
// ESP32 kirim UID RFID yang sedang aktif.
// Server cari apakah ada OTP yang sudah diisi user di web
// untuk UID tersebut dan belum dieksekusi (status='menunggu').
//
// Response saat ada OTP pending:
//   {"status":"ok","otp":"123456","nomor_loker":1}
//
// Response saat belum ada OTP:
//   {"status":"tunggu"}
//
// Response OTP sudah kadaluarsa:
//   {"status":"error","msg":"otp_kadaluarsa"}
// ======================================================
if ($action == "cek_perintah") {

    $uid = $_GET['rfid'] ?? '';

    if (empty($uid)) {
        echo json_encode(["status" => "error", "msg" => "rfid_kosong"]);
        exit;
    }

    // Cari baris dengan UID ini yang punya OTP terisi
    // dan status masih 'menunggu' (belum dieksekusi ESP32)
    $stmt = $conn->prepare("
        SELECT id, otp, nomor_loker, otp_expired
        FROM perintah_loker
        WHERE uid_rfid = ?
        AND status = 'menunggu'
        AND otp IS NOT NULL
        AND otp != ''
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Belum ada OTP yang diisi user
        echo json_encode(["status" => "tunggu"]);
        exit;
    }

    $row = $result->fetch_assoc();

    // Cek expired
    if (time() > intval($row['otp_expired'])) {
        echo json_encode(["status" => "error", "msg" => "otp_kadaluarsa"]);
        exit;
    }

    // Ada OTP valid — kembalikan ke ESP32
    echo json_encode([
        "status"      => "ok",
        "otp"         => $row['otp'],
        "nomor_loker" => intval($row['nomor_loker'])
    ]);
    exit;
}

// ======================================================
// 7. TANDAI OTP SUDAH DIEKSEKUSI
// ======================================================
// Dipanggil ESP32 setelah relay berhasil dibuka,
// agar polling berikutnya tidak membuka loker lagi.
//
// Response: {"status":"ok"}
// ======================================================
if ($action == "tandai_selesai") {

    $uid = $_GET['rfid'] ?? '';
    $otp = $_GET['otp']  ?? '';

    if (empty($uid) || empty($otp)) {
        echo json_encode(["status" => "error", "msg" => "param_kosong"]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE perintah_loker
        SET status='selesai', status_loker='KOSONG', selesai_at=NOW()
        WHERE uid_rfid=?
        AND otp=?
        AND status='menunggu'
        LIMIT 1
    ");
    $stmt->bind_param("ss", $uid, $otp);
    $stmt->execute();

    echo json_encode(["status" => "ok", "affected" => $stmt->affected_rows]);
    exit;
}

// ======================================================
// Fallback — action tidak dikenal
// ======================================================
echo json_encode(["status" => "error", "msg" => "action_invalid"]);

$conn->close();
?>
