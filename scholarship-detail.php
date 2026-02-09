<!-- filepath: c:\xampp\htdocs\register\scholarship-detail.php -->
<?php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['username'])) {
    header('location: login.php');
    exit();
}

// รับ ID ของทุนจาก URL
$scholarship_id = isset($_GET['id']) ? $_GET['id'] : null;

// ตัวอย่างข้อมูลทุน (คุณสามารถดึงข้อมูลจากฐานข้อมูลแทนได้)
$scholarships = [
    1 => ['name' => 'ทุนใน 1', 'description' => 'รายละเอียดทุนใน 1'],
    2 => ['name' => 'ทุนใน 2', 'description' => 'รายละเอียดทุนใน 2'],
    3 => ['name' => 'ทุนนอก 1', 'description' => 'รายละเอียดทุนนอก 1'],
    4 => ['name' => 'ทุนนอก 2', 'description' => 'รายละเอียดทุนนอก 2'],
];

// ตรวจสอบว่าทุนมีอยู่หรือไม่
if (!isset($scholarships[$scholarship_id])) {
    echo "ไม่พบข้อมูลทุน";
    exit();
}

$scholarship = $scholarships[$scholarship_id];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $scholarship['name']; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <a href="index.php" class="navbar-item">หน้าหลัก</a>
            <a href="#" class="navbar-item">ทุนการศึกษา</a>
            <a href="#" class="navbar-item">ติดตามสถานะ</a>
            <a href="#" class="navbar-item">ประวัติรับทุนการศึกษา</a>
        </div>
    </div>

    <div class="scholarship-detail">
        <div class="scholarship-header" style="background-color: #FFCCCC;">
            <h1><?php echo $scholarship['name']; ?></h1>
        </div>
        <div class="scholarship-content">
            <p><?php echo $scholarship['description']; ?></p>
        </div>
        <div class="scholarship-actions">
            <a href="#" class="btn">สมัครทุน</a>
        </div>
    </div>
</body>
</html>