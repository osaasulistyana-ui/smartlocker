<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($username === 'admin' && $password === 'password') {
        $_SESSION['admin'] = true;
        header('Location: admin-dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Smart Locker</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 380px;
        }
        h2 { color: #1a1a2e; margin-bottom: 6px; }
        .sub { color: #888; font-size: 13px; margin-bottom: 24px; }
        label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 6px; }
        input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 16px;
        }
        input:focus { outline: none; border-color: #1a1a2e; }
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #1a1a2e;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .watermark { color: #aaa; font-size: 12px; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
<div class="card">
    <h2>⚙ Admin Login</h2>
    <p class="sub">Masuk ke panel admin Smart Locker</p>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" placeholder="admin">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••">
        <button class="btn" type="submit">Masuk</button>
    </form>
    <div class="watermark">@Smart Locker_Tugas Akhir Osa</div>
</div>
</body>
</html>