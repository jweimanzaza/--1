<!-- filepath: c:\xampp\htdocs\register\committee_dashboard.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าเป็นกรรมการระดับคณะหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'committee') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดกรรมการ</title>
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
        .dashboard-title {
            text-align: center;
            color: #2563eb;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 36px 0 24px 0;
            letter-spacing: 1px;
        }
        .welcome-card, .pending-card {
            max-width: 600px;
            margin: 0 auto 24px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
            padding: 32px 28px 28px 28px;
            text-align: center;
        }
        .welcome-card h3, .pending-card h3 {
            color: #2563eb;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .btn-review {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 32px;
            font-size: 1.08rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            margin-top: 12px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            transition: background 0.18s, box-shadow 0.18s;
            display: inline-block;
        }
        .btn-review:hover {
            background: linear-gradient(90deg, #60a5fa 0%, #2563eb 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.18);
        }
        @media (max-width: 700px) {
            .navbar { padding: 0 10px; }
            .welcome-card, .pending-card { padding: 14px 2vw 14px 2vw; }
            .dashboard-title { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="150px-SDU2016.png" alt="SDU Logo" style="height:44px; margin-right:12px;">
        <div class="navbar-links">
            <a href="committee_dashboard.php" class="active"><i class="fa fa-dashboard"></i> หน้าหลัก</a>
            <a href="committee_review.php"><i class="fa fa-file-text"></i> พิจารณาทุนการศึกษา</a>
        </div>
        <div class="navbar-actions">
            <a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="dashboard-title"><i class="fa fa-dashboard"></i> หน้าหลักกรรมการระดับคณะ</div>
    <div class="welcome-card">
        <h3><i class="fa fa-user"></i> ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']); ?></h3>
        <p>คุณสามารถพิจารณาคำขอทุนการศึกษาได้โดยคลิกที่เมนู <b>พิจารณาทุนการศึกษา</b></p>
    </div>
    <div class="pending-card">
        <h3><i class="fa fa-clock-o"></i> คำขอทุนที่รอการพิจารณา</h3>
        <p>ไปที่ <a href="committee_review.php" class="btn-review"><i class="fa fa-search"></i> พิจารณาทุนการศึกษา</a> เพื่อดูคำขอทุนทั้งหมด</p>
    </div>
</body>
</html>