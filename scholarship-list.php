<!-- filepath: c:\xampp\htdocs\register\scholarship-list.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่านักศึกษาเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// ตั้งค่าการเข้ารหัสของการเชื่อมต่อฐานข้อมูล
mysqli_set_charset($conn, "utf8");

// ดึงข้อมูลทุนการศึกษาจากฐานข้อมูล
$query = "SELECT id, name, description, opening_date, closing_date, status FROM scholarships";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทุนการศึกษา</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap');
        body {
            font-family: 'Prompt', Arial, sans-serif;
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
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
        .scholarship-list-container {
            max-width: 1200px;
            margin: 36px auto 36px auto;
            background: linear-gradient(135deg, #fff 60%, #e0e7ff 100%);
            padding: 32px 28px 28px 28px;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
        }
        .scholarship-list-title {
            text-align: center;
            font-size: 2rem;
            color: #2563eb;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }
        .scholarship-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }
        .scholarship-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(37,99,235,0.08);
            padding: 28px 22px 22px 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.18s, box-shadow 0.18s;
            border: 1.5px solid #e0e7ff;
        }
        .scholarship-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 12px 32px rgba(37,99,235,0.16);
            border: 1.5px solid #2563eb;
        }
        .scholarship-card h4 {
            margin: 0 0 10px 0;
            color: #2563eb;
            font-size: 1.18rem;
            font-weight: 700;
        }
        .scholarship-card .desc {
            font-size: 1rem;
            color: #475569;
            margin-bottom: 12px;
            min-height: 48px;
        }
        .scholarship-card .dates {
            font-size: 0.98rem;
            color: #64748b;
            margin-bottom: 10px;
        }
        .scholarship-card .status {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .status-open {
            color: #22c55e;
        }
        .status-closed {
            color: #ef4444;
        }
        .btn-details {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: white;
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            display: inline-block;
        }
        .btn-details:hover {
            background: linear-gradient(90deg, #60a5fa 0%, #2563eb 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.18);
        }
        @media (max-width: 900px) {
            .scholarship-list-container {
                padding: 16px 4vw 16px 4vw;
            }
            .scholarship-grid {
                grid-template-columns: 1fr;
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
            .btn-edit, .btn-logout {
                margin: 10px 0 0 0;
                width: 100%;
            }
            .scholarship-list-container {
                padding: 12px 2vw 12px 2vw;
            }
            .scholarship-list-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <img src="150px-SDU2016.png" alt="SDU Logo" style="height:48px; margin-right:12px;">
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
    <div class="scholarship-list-container">
        <div class="scholarship-list-title">รายการทุนการศึกษา</div>
        <div class="scholarship-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="scholarship-card">
                        <h4><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <div class="desc"><?= htmlspecialchars(mb_substr($row['description'], 0, 100, 'UTF-8')) . (mb_strlen($row['description'], 'UTF-8') > 100 ? '...' : ''); ?></div>
                        <div class="dates">
                            <strong>เปิด:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($row['opening_date']))); ?>
                            <br>
                            <strong>ปิด:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($row['closing_date']))); ?>
                        </div>
                        <div class="status <?= $row['status'] == 'เปิดรับสมัคร' ? 'status-open' : 'status-closed' ?>">
                            <?= htmlspecialchars($row['status']); ?>
                        </div>
                        <a href="scholarship-details.php?id=<?= $row['id']; ?>" class="btn-details">ดูรายละเอียด</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center;">ไม่มีทุนการศึกษาในขณะนี้</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>