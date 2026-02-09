<!-- filepath: c:\xampp\htdocs\register\user_dashboard.php -->
<?php
// เริ่มต้น session
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
    exit();
}

// หากผู้ใช้เข้าสู่ระบบแล้ว ให้แสดงหน้า Dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        <div class="navbar-right">
            <p>Welcome, <strong><?php echo $_SESSION['username']; ?></strong></p>
            <a href="index.php?logout='1'" style="color: red;">Logout</a>
        </div>
    </div>

    <div class="header">
        <h2>Dashboard</h2>
    </div>

    <div class="content">
        <p>ยินดีต้อนรับสู่หน้า Dashboard</p>
        <p><a href="index.php">ไปที่หน้าหลัก</a></p>
    </div>
</body>
</html>