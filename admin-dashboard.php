<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: admin-login.php');
    exit;
}
require_once 'config.php';

// Tambah loker
if (isset($_POST['tambah_loker'])) {
    $max = $conn->query("SELECT MAX(nomor_loker) as max FROM loker")->fetch_assoc()['max'];
    $baru = ($max ?? 0) + 1;
    $conn->query("INSERT INTO loker (nomor_loker, status) VALUES ($baru, 'kosong')");
}

// Reset loker (manual oleh admin)
if (isset($_POST['reset_loker'])) {
    $id = (int)$_POST['reset_loker'];
    $nomor = $conn->query("SELECT nomor_loker FROM loker WHERE id=$id")->fetch_assoc()['nomor_loker'];
    $conn->query("UPDATE loker SET status='kosong' WHERE id=$id");
    $conn->query("UPDATE data_titip SET status='Diambil' WHERE nomor_loker=$nomor AND status='Aktif'");
}

// Hapus pengguna
if (isset($_POST['hapus_pengguna'])) {
    $id = (int)$_POST['hapus_pengguna'];
    $row = $conn->query("SELECT nomor_loker, status FROM data_titip WHERE id=$id")->fetch_assoc();
    if ($row) {
        $conn->query("DELETE FROM data_titip WHERE id=$id");
        if ($row['status'] === 'Aktif') {
            $nomor = (int)$row['nomor_loker'];
            $conn->query("UPDATE loker SET status='kosong' WHERE nomor_loker=$nomor");
        }
    }
}

// PERBAIKAN UTAMA: Sinkronkan status loker dari data_titip setiap halaman dimuat
$conn->query("
    UPDATE loker l
    SET l.status = CASE
        WHEN EXISTS (
            SELECT 1 FROM data_titip d
            WHERE d.nomor_loker = l.nomor_loker
              AND d.status = 'Aktif'
        ) THEN 'terisi'
        ELSE 'kosong'
    END
");

// Ambil data loker
$lokers = $conn->query("SELECT * FROM loker ORDER BY nomor_loker")->fetch_all(MYSQLI_ASSOC);

// Ambil data pengguna
$pengguna = $conn->query("SELECT * FROM data_titip ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Locker</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }

        .topbar {
            background: #1a1a2e;
            color: white;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar h1 { font-size: 20px; }
        .topbar a {
            color: #e74c3c;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid #e74c3c;
            padding: 6px 14px;
            border-radius: 6px;
        }

        .content { padding: 28px; max-width: 1000px; margin: 0 auto; }

        h2 { color: #1a1a2e; margin-bottom: 16px; font-size: 18px; }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 28px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        .loker-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .loker-item {
            border-radius: 10px;
            padding: 16px 20px;
            text-align: center;
            color: white;
            min-width: 110px;
        }
        .loker-item.kosong { background: #27ae60; }
        .loker-item.terisi { background: #e74c3c; }
        .loker-item p { font-size: 12px; margin-top: 4px; }
        .loker-item form { margin-top: 8px; }

        .btn-reset {
            background: white;
            color: #e74c3c;
            border: none;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-tambah {
            background: #1a1a2e;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
        }

        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th {
            background: #1a1a2e;
            color: white;
            padding: 10px 14px;
            text-align: left;
        }
        td { padding: 10px 14px; border-bottom: 1px solid #eee; }
        tr:hover td { background: #f9f9f9; }

        .badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-Aktif   { background: #fff3cd; color: #856404; }
        .badge-Diambil { background: #d4edda; color: #155724; }

        .btn-hapus {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 12px;
            cursor: pointer;
        }

        .watermark { color: #aaa; font-size: 12px; text-align: center; padding: 20px; }
    </style>
</head>
<body>

<div class="topbar">
    <h1>⚙ Admin Dashboard — Smart Locker</h1>
    <a href="admin-logout.php">Keluar</a>
</div>

<div class="content">

    <!-- SECTION LOKER -->
    <div class="card">
        <h2>📦 Status Loker</h2>
        <div class="loker-grid">
            <?php foreach ($lokers as $l): ?>
            <div class="loker-item <?= $l['status'] ?>">
                <strong>Loker <?= $l['nomor_loker'] ?></strong>
                <p><?= ucfirst($l['status']) ?></p>
                <?php if ($l['status'] === 'terisi'): ?>
                <form method="POST" onsubmit="return confirm('Reset loker ini? Pengguna aktif akan ditandai Diambil.')">
                    <input type="hidden" name="reset_loker" value="<?= $l['id'] ?>">
                    <button class="btn-reset" type="submit">🔓 Reset</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <form method="POST" onsubmit="return confirm('Tambah 1 loker baru?')">
            <button class="btn-tambah" name="tambah_loker" type="submit">+ Tambah Loker</button>
        </form>
    </div>

    <!-- SECTION PENGGUNA -->
    <div class="card">
        <h2>👥 Data Pengguna</h2>
        <?php if (empty($pengguna)): ?>
            <p style="color:#888">Belum ada data pengguna.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>No HP</th>
                    <th>Alamat</th>
                    <th>Loker</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pengguna as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($p['nama']) ?></td>
                    <td><?= htmlspecialchars($p['no_hp']) ?></td>
                    <td><?= htmlspecialchars($p['alamat']) ?></td>
                    <td>Loker <?= $p['nomor_loker'] ?></td>
                    <td><?= htmlspecialchars($p['tanggal_pakai'] ?? $p['created_at']) ?></td>
                    <td>
                        <span class="badge badge-<?= htmlspecialchars($p['status']) ?>">
                            <?= htmlspecialchars($p['status']) ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Hapus data ini?')">
                            <input type="hidden" name="hapus_pengguna" value="<?= $p['id'] ?>">
                            <button class="btn-hapus" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<div class="watermark">@Smart Locker_Tugas Akhir Osa</div>
</body>
</html>