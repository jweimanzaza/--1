<?php
session_start();
include('server.php');

// ตรวจสอบว่าผู้ดูแลระบบเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลนักศึกษาทั้งหมดจากฐานข้อมูล
$query = "SELECT u.username, s.*, a.id AS application_id, a.status AS application_status 
          FROM student_profiles s 
          JOIN users u ON s.user_id = u.id
          LEFT JOIN scholarship_applications a ON s.user_id = a.user_id";
$results = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลักผู้ดูแลระบบ</title>
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
        .header {
            background: none;
            color: #222;
            padding: 32px 0 0 0;
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .dashboard-title {
            text-align: center;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 28px;
            color: #222;
        }
        .dashboard-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 32px;
            margin-top: 18px;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(37,99,235,0.08);
            padding: 32px 36px 28px 36px;
            min-width: 240px;
            max-width: 270px;
            text-align: center;
            transition: transform 0.18s, box-shadow 0.18s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .dashboard-card:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 8px 24px rgba(37,99,235,0.13);
        }
        .dashboard-card h4 {
            margin: 0 0 8px 0;
            color: #222;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .dashboard-card .main-value {
            font-size: 1.5rem;
            color: #1565c0;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .dashboard-card .main-label {
            color: #222;
            font-size: 1rem;
            margin-bottom: 18px;
        }
        .dashboard-card .icon {
            font-size: 2.2rem;
            margin-bottom: 8px;
            color: #007bff;
        }
        .dashboard-card .btn-action {
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 10px 28px;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            display: inline-block;
        }
        .dashboard-card .btn-action:hover {
            background: #0056b3;
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
        @media (max-width: 900px) {
            .dashboard-grid {
                flex-direction: column;
                align-items: center;
                gap: 18px;
            }
            .dashboard-card {
                min-width: 180px;
                max-width: 100%;
                width: 100%;
            }
        }
        @media (max-width: 700px) {
            .navbar {
                flex-direction: column;
                height: auto;
                align-items: flex-start;
                padding: 0 10px;
            }
            .navbar-links, .navbar-actions {
                flex-direction: column;
                width: 100%;
                gap: 0;
            }
            .navbar-actions {
                margin-top: 10px;
            }
            .btn-logout {
                margin: 10px 0 0 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="150px-SDU2016.png" alt="SDU Logo" style="height:48px; margin-right:12px;">
        <div class="navbar-links">
            <a href="admin_dashboard.php"><i class="fa fa-home"></i> หน้าหลัก</a>
            <a href="manage_users.php"><i class="fa fa-users"></i> จัดการผู้ใช้</a>
            <a href="manage_scholarships.php"><i class="fa fa-graduation-cap"></i> จัดการทุนการศึกษา</a>
            <a href="manage_applications.php"><i class="fa fa-file-alt"></i> จัดการใบสมัคร</a>
        </div>
        <div class="navbar-actions">
            <a href="login.php" class="btn-logout"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="header">ข้อมูลสำคัญ</div>
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h4>จำนวนผู้ใช้ทั้งหมด</h4>
            <div class="main-value"><?= $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0]; ?> คน</div>
            <a href="manage_users.php" class="btn-action">ดูรายละเอียด</a>
        </div>
        <div class="dashboard-card">
            <h4>จำนวนทุนการศึกษา</h4>
            <div class="main-value"><?= $conn->query("SELECT COUNT(*) FROM scholarships")->fetch_row()[0]; ?> ทุน</div>
            <a href="manage_scholarships.php" class="btn-action">ดูรายละเอียด</a>
        </div>
        <div class="dashboard-card">
            <h4>คำขอสมัครทุน</h4>
            <div class="main-value"><?= $conn->query("SELECT COUNT(*) FROM scholarship_applications")->fetch_row()[0]; ?> รายการ</div>
            <a href="manage_applications.php" class="btn-action">ดูรายละเอียด</a>
        </div>
        <div class="dashboard-card">
            <h4>Export คะแนนเป็น Excel</h4>
            <span class="icon"><i class="fa fa-bar-chart"></i></span>
            <a href="export_page.php" class="btn-action">ไปที่หน้า Export</a>
        </div>
        <div class="dashboard-card">
            <h4>รายงานผู้ได้รับทุน</h4>
            <span class="icon"><i class="fa fa-file-text-o"></i></span>
            <a href="admin_report.php" class="btn-action">ไปที่หน้า Report</a>
        </div>
    </div>
</body>
</html>