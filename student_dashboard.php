<?php
session_start();
include('server.php');

// ตรวจสอบว่านักศึกษาเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// ดึงปีที่มีทุน (ย้อนหลัง 5 ปี)
$currentYear = date('Y');
$years = [];
for ($i = 0; $i < 5; $i++) {
    $years[] = $currentYear - $i;
}

// รับค่าปีที่เลือก
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// ดึงทุนเฉพาะปีที่เลือก (ใช้ opening_date)
$query = "SELECT * FROM scholarships WHERE YEAR(opening_date) = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $selected_year);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลักนักศึกษา</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        }
        .navbar-links a {
            color: #222;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.08rem;
            padding: 8px 18px;
            border-radius: 8px;
            transition: background 0.18s, color 0.18s;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .navbar-links a.active, .navbar-links a:hover {
            background: #e0e7ff;
            color: #2563eb;
            font-weight: 600;
        }
        .navbar-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .navbar-actions .btn-edit {
            background: linear-gradient(90deg, #fbbf24 0%, #f59e42 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 1rem;
            transition: box-shadow 0.18s, background 0.18s;
            box-shadow: 0 2px 8px rgba(251,191,36,0.08);
        }
        .navbar-actions .btn-edit:hover {
            background: linear-gradient(90deg, #f59e42 0%, #fbbf24 100%);
            box-shadow: 0 4px 16px rgba(251,191,36,0.18);
        }
        .navbar-actions .btn-logout {
            background: linear-gradient(90deg, #ef4444 0%, #f87171 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 1rem;
            transition: box-shadow 0.18s, background 0.18s;
            box-shadow: 0 2px 8px rgba(239,68,68,0.08);
        }
        .navbar-actions .btn-logout:hover {
            background: linear-gradient(90deg, #f87171 0%, #ef4444 100%);
            box-shadow: 0 4px 16px rgba(239,68,68,0.18);
        }
        .welcome-section, .recommend-section {
            text-align: center;
            padding: 32px 0 18px 0;
            background: none;
            margin-bottom: 0;
        }
        .welcome-section h1 {
            margin: 0;
            font-size: 2.1rem;
            color: #2563eb;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .welcome-section p {
            margin: 8px 0 0;
            font-size: 1.1rem;
            color: #555;
        }
        .recommend-section h2 {
            margin: 0;
            font-size: 1.4rem;
            color: #1e293b;
            font-weight: 600;
        }
        .recommend-section p {
            margin: 5px 0 0;
            font-size: 1rem;
            color: #555;
        }
        .year-select-box {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 24px;
            margin-left: 60px;
        }
        .year-select-inner {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(37,99,235,0.07);
            padding: 20px 36px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .year-select-inner label {
            font-size: 1.1rem;
            color: #222;
            margin-bottom: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .year-select-inner select {
            font-size: 1rem;
            padding: 7px 18px;
            border-radius: 8px;
            border: 1.5px solid #2563eb;
            outline: none;
            transition: border 0.2s;
            background: #f1f5f9;
        }
        .year-select-inner select:focus {
            border: 2px solid #1d4ed8;
        }
        .scholarship-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 32px;
            padding: 24px 0 32px 0;
        }
        .scholarship-card {
            background: linear-gradient(135deg, #fff 60%, #e0e7ff 100%);
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
            padding: 28px 22px 22px 22px;
            width: 320px;
            text-align: center;
            transition: transform 0.18s, box-shadow 0.18s;
            border: 1.5px solid #e0e7ff;
        }
        .scholarship-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 12px 32px rgba(37,99,235,0.16);
            border: 1.5px solid #2563eb;
        }
        .scholarship-card h3 {
            margin: 0 0 12px;
            font-size: 1.18rem;
            color: #1e293b;
            font-weight: 700;
        }
        .scholarship-card p {
            font-size: 1rem;
            color: #475569;
            margin: 0 0 15px;
        }
        .scholarship-card a {
            display: inline-block;
            padding: 10px 26px;
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
        }
        .scholarship-card a:hover {
            background: linear-gradient(90deg, #60a5fa 0%, #2563eb 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.18);
        }
        footer {
            text-align: center;
            padding: 18px;
            background: #fff;
            margin-top: 32px;
            font-size: 1rem;
            color: #64748b;
            border-radius: 18px 18px 0 0;
            box-shadow: 0 -2px 12px rgba(37,99,235,0.04);
        }
        @media (max-width: 900px) {
            .scholarship-container {
                flex-direction: column;
                align-items: center;
            }
            .year-select-box {
                margin-left: 0;
                justify-content: center;
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

    <!-- Welcome Section -->
    <div class="welcome-section">
        <h1>ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']); ?></h1>
        <p>นี่คือหน้าหลักสำหรับนักศึกษา</p>
    </div>

    <!-- Recommend Section -->
    <div class="recommend-section">
        <h2>ทุนการศึกษาที่แนะนำและยังเปิดให้สมัครอยู่</h2>
    </div>

    <!-- Year Selection Form -->
    <div class="year-select-box">
        <form method="get" class="year-select-inner">
            <label for="year">เลือกปีของทุนการศึกษา</label>
            <select name="year" id="year" onchange="this.form.submit()">
                <?php foreach ($years as $year): ?>
                    <option value="<?= $year ?>" <?= $year == $selected_year ? 'selected' : '' ?>><?= $year ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Scholarship Section -->
    <div class="scholarship-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="scholarship-card">
                <h3><?= htmlspecialchars($row['name']); ?></h3>
                <p><?= htmlspecialchars($row['description']); ?></p>
                <p>
                    <strong>เปิดรับสมัคร:</strong> <?= htmlspecialchars($row['opening_date']); ?><br>
                    <strong>ปิดรับสมัคร:</strong> <?= htmlspecialchars($row['closing_date']); ?>
                </p>
                <a href="scholarship-details.php?id=<?= $row['id']; ?>">ดูรายละเอียด</a>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Footer -->
    <footer>
        Copyright © 2025 ระบบจัดการทุนการศึกษา
    </footer>
</body>
</html>