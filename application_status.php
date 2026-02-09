<!-- filepath: c:\xampp\htdocs\register\application_status.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่านักศึกษาเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // รับ user_id จาก session

date_default_timezone_set('Asia/Bangkok');
$now = date('Y-m-d H:i:s');

// อัปเดตสถานะสุดท้ายของการสมัครทุนการศึกษา
$sql = "SELECT a.id, a.review_status, a.final_status, s.closing_date
        FROM scholarship_applications a
        JOIN scholarships s ON a.scholarship_id = s.id
        WHERE a.final_status IS NULL AND a.review_status IS NOT NULL AND NOW() >= s.closing_date";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $final = ($row['review_status'] == 'อนุมัติ') ? 'ได้รับทุนการศึกษา' : 'ไม่ได้รับทุนการศึกษา';
    $update = $conn->prepare("UPDATE scholarship_applications SET final_status = ? WHERE id = ?");
    $update->bind_param("si", $final, $row['id']);
    $update->execute();
}

// ดึงข้อมูล application และทุน
$sql = "SELECT a.*, s.closing_date, s.name AS scholarship_name
        FROM scholarship_applications a
        JOIN scholarships s ON a.scholarship_id = s.id
        WHERE a.user_id = ?"; // ใส่ user_id ตาม session
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะการสมัครทุน</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap');
        body {
            font-family: 'Prompt', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
        }
        .navbar {
            background: #fff;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            padding: 0 36px;
            min-height: 64px;
            border-radius: 0 0 18px 18px;
        }
        .navbar img {
            height: 44px;
            margin-right: 18px;
        }
        .navbar-brand {
            font-size: 2rem;
            font-weight: bold;
            color: #d1f30f !important;
            margin-right: 18px;
            line-height: 1;
            letter-spacing: 1px;
            flex-shrink: 0;
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
        .btn-edit {
            background: linear-gradient(90deg, #fbbf24 0%, #f59e42 100%);
            color: #fff !important;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 1rem;
            transition: box-shadow 0.18s, background 0.18s;
            box-shadow: 0 2px 8px rgba(251,191,36,0.08);
            display: flex;
            align-items: center;
        }
        .btn-edit:hover {
            background: linear-gradient(90deg, #f59e42 0%, #fbbf24 100%);
            box-shadow: 0 4px 16px rgba(251,191,36,0.18);
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
        }
        .btn-logout:hover {
            background: linear-gradient(90deg, #f87171 0%, #ef4444 100%);
            box-shadow: 0 4px 16px rgba(239,68,68,0.18);
        }
        .navbar-title {
            background: #2563eb;
            color: #fff !important;
            text-align: center;
            padding: 22px 0 12px 0;
            margin: 0;
            border-radius: 0 0 18px 18px;
            width: 100vw;
            max-width: 100vw;
            box-shadow: 0 2px 12px rgba(37,99,235,0.07);
        }
        .navbar-title h2 {
            margin: 0;
            font-size: 2.1rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #fff !important;
        }
        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 32px 0 32px 0;
        }
        .status-container {
            background: linear-gradient(135deg, #fff 60%, #e0e7ff 100%);
            padding: 36px 32px 32px 32px;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
            width: 100%;
            max-width: 700px;
            margin-top: 32px;
        }
        .status-container h2 {
            margin-bottom: 18px;
            font-size: 1.6rem;
            color: #2563eb;
            text-align: center;
            font-weight: 700;
        }
        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(37,99,235,0.05);
        }
        .status-table th, .status-table td {
            padding: 14px 10px;
            text-align: center;
        }
        .status-table th {
            background: #e0e7ff;
            color: #2563eb;
            font-weight: 700;
            font-size: 1.05rem;
        }
        .status-table tr {
            border-bottom: 1px solid #e5e7eb;
        }
        .status-table tr:last-child {
            border-bottom: none;
        }
        .status-table td {
            font-size: 1rem;
            color: #475569;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 16px;
            font-size: 0.98rem;
            font-weight: 600;
            color: #fff;
        }
        .status-pending { background: #fbbf24; }
        .status-approved { background: #22c55e; }
        .status-rejected { background: #ef4444; }
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
            .btn-edit, .btn-logout {
                margin: 10px 0 0 0;
                width: 100%;
            }
            .status-container {
                padding: 18px 8px 18px 8px;
            }
            .status-table th, .status-table td {
                padding: 10px 4px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <img src="150px-SDU2016.png" alt="SDU Logo" style="height:48px; margin-right:12px;">
        <span class="navbar-brand"></span>
        <div class="navbar-links">
            <a href="student_dashboard.php"><i class="fa fa-home"></i> หน้าหลัก</a>
            <a href="profile.php"><i class="fa fa-user"></i> โปรไฟล์</a>
            <a href="application_status.php"><i class="fa fa-search"></i> การติดตามสถานะการสมัคร</a>
            <a href="scholarship-list.php"><i class="fa fa-graduation-cap"></i> ทุนการศึกษา</a>
        </div>
        <div class="navbar-actions">
            <a href="edit_profile.php" class="btn-edit"><i class="fa fa-edit"></i> แก้ไขโปรไฟล์</a>
            <a href="login.php" class="btn-logout"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="navbar-title">
        <h2>สถานะการสมัครทุน</h2>
    </div>
    <div class="content-wrapper">
        <div class="status-container">
            <h2>รายการสถานะการสมัครทุนของคุณ</h2>
            <!-- ตัวอย่างตารางสถานะ (ให้แทนที่ด้วย PHP loop จริง) -->
            <table class="status-table">
                <thead>
                    <tr>
                        <th>ชื่อทุน</th>
                        <th>วันที่สมัคร</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- ตัวอย่างข้อมูล -->
                    <tr>
                        <td>ทุนเรียนดี</td>
                        <td>2024-04-01</td>
                        <td><span class="status-badge status-pending">รอดำเนินการ</span></td>
                    </tr>
                    <tr>
                        <td>ทุนขาดแคลนทุนทรัพย์</td>
                        <td>2024-03-15</td>
                        <td><span class="status-badge status-approved">อนุมัติ</span></td>
                    </tr>
                    <tr>
                        <td>ทุนกิจกรรม</td>
                        <td>2024-02-20</td>
                        <td><span class="status-badge status-rejected">ไม่ผ่าน</span></td>
                    </tr>
                    <!-- /ตัวอย่างข้อมูล -->
                    <!-- 
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['scholarship_name']); ?></td>
                            <td><?= htmlspecialchars($row['applied_date']); ?></td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <span class="status-badge status-pending">รอดำเนินการ</span>
                                <?php elseif ($row['status'] == 'approved'): ?>
                                    <span class="status-badge status-approved">อนุมัติ</span>
                                <?php else: ?>
                                    <span class="status-badge status-rejected">ไม่ผ่าน</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    -->
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>