<!-- filepath: c:\xampp\htdocs\register\user-details.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าผู้ดูแลระบบเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการส่ง ID ผู้ใช้มาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id = $_GET['id'];

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p>ไม่พบข้อมูลผู้ใช้</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดผู้ใช้</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap');
        body {
            font-family: 'Prompt', Arial, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background: #fff;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            padding: 0 36px;
            min-height: 64px;
            border-radius: 0 0 18px 18px;
            margin-bottom: 24px;
        }
        .navbar img {
            height: 44px;
            margin-right: 18px;
        }
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 28px;
            flex: 1;
        }
        .navbar-links a {
            color: #222 !important;
            text-decoration: none;
            font-size: 1.08rem;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: background 0.18s, color 0.18s;
        }
        .navbar-links a.active, .navbar-links a:hover {
            background: #e0e7ff;
            color: #2563eb !important;
            font-weight: 600;
        }
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-logout {
            background: linear-gradient(90deg, #ef4444 0%, #f87171 100%);
            color: #fff !important;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 1rem;
            transition: box-shadow 0.18s, background 0.18s;
            box-shadow: 0 2px 8px rgba(239,68,68,0.08);
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .btn-logout:hover {
            background: linear-gradient(90deg, #f87171 0%, #ef4444 100%);
            box-shadow: 0 4px 16px rgba(239,68,68,0.18);
        }
        .content-wrapper {
            padding: 0 0 36px 0;
        }
        .content {
            max-width: 520px;
            margin: 36px auto 0 auto;
            background: #fff;
            padding: 36px 32px 32px 32px;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
        }
        .content h3 {
            margin-bottom: 24px;
            color: #2563eb;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
        }
        .user-details {
            font-size: 1.08rem;
            line-height: 2.1;
            color: #222;
            margin-bottom: 18px;
        }
        .user-details strong {
            color: #2563eb;
            min-width: 110px;
            display: inline-block;
        }
        .btn-back {
            display: inline-block;
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            padding: 12px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.08rem;
            margin-top: 18px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            transition: background 0.18s, box-shadow 0.18s;
        }
        .btn-back:hover {
            background: linear-gradient(90deg, #60a5fa 0%, #2563eb 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.18);
        }
        @media (max-width: 700px) {
            .content { padding: 14px 2vw 14px 2vw; }
            .user-details { font-size: 1rem; }
            .btn-back { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="150px-SDU2016.png" alt="SDU Logo" style="height:44px; margin-right:12px;">
        <div class="navbar-links">
            <a href="admin_dashboard.php"><i class="fa fa-home"></i> หน้าหลัก</a>
            <a href="manage_users.php"><i class="fa fa-users"></i> จัดการผู้ใช้</a>
            <a href="manage_scholarships.php"><i class="fa fa-graduation-cap"></i> จัดการทุนการศึกษา</a>
        </div>
        <div class="navbar-actions">
            <a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="content-wrapper">
        <div class="content">
            <h3><i class="fa fa-user"></i> ข้อมูลผู้ใช้</h3>
            <div class="user-details">
                <p><strong>ชื่อผู้ใช้:</strong> <?= htmlspecialchars($user['username']); ?></p>
                <p><strong>อีเมล:</strong> <?= htmlspecialchars($user['email']); ?></p>
                <p><strong>สิทธิ์:</strong> <?= htmlspecialchars($user['role']); ?></p>
            </div>
            <a href="manage_users.php" class="btn-back"><i class="fa fa-arrow-left"></i> กลับไปหน้าจัดการผู้ใช้</a>
        </div>
    </div>
</body>
</html>