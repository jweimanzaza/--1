<?php
session_start();
include('server.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['username'])) {
    header('location: login.php');
    exit();
}

// หากผู้ใช้คลิก Logout
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    header('location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <a href="index.php" class="navbar-item">หน้าหลัก</a>
            <a href="scholarships.php" class="navbar-item">ทุนการศึกษา</a>
            <a href="#" class="navbar-item">ติดตามสถานะ</a>
            <a href="#" class="navbar-item">ประวัติรับทุนการศึกษา</a>
        </div>
        <div class="navbar-right">
            <p>Welcome, <strong><?php echo $_SESSION['username']; ?></strong></p>
            <div class="user-actions">
                <a href="edit-profile.php" class="navbar-item">แก้ไขข้อมูลส่วนตัว</a>
                <a href="scholarship-info.php">ข้อมูลการสมัครทุน</a>
            </div>
            <a href="edit-profile.php" class="navbar-item" style="color: blue;">แก้ไขโปรไฟล์</a>
            <a href="index.php?logout='1'" style="color: red;">Logout</a>
        </div>
    </div>

    <div class="header">
        <h2>หน้าหลัก</h2>
    </div>

    <div class="content">
        <p>ยินดีต้อนรับสู่หน้า Homepage</p>
        <a href="studentform.php" class="button">กรอกข้อมูลส่วนตัว</a>
    </div>

    <div class="header">
        <h2>ทุนแนะนำ</h2>
    </div>

    <div class="recommended-scholarships">
        <div class="image-grid">
            <div class="image-box">
                <a href="scholarship-details.php?id=1">
                    <img src="path/to/image1.jpg" alt="ทุนการศึกษา 1">
                    <p>ทุนการศึกษา 1</p>
                </a>
            </div>
            <div class="image-box">
                <a href="scholarship-details.php?id=2">
                    <img src="path/to/image2.jpg" alt="ทุนการศึกษา 2">
                    <p>ทุนการศึกษา 2</p>
                </a>
            </div>
            <div class="image-box">
                <a href="scholarship-details.php?id=3">
                    <img src="path/to/image3.jpg" alt="ทุนการศึกษา 3">
                    <p>ทุนการศึกษา 3</p>
                </a>
            </div>
            <div class="image-box">
                <a href="scholarship-details.php?id=4">
                    <img src="path/to/image4.jpg" alt="ทุนการศึกษา 4">
                    <p>ทุนการศึกษา 4</p>
                </a>
            </div>
            <div class="image-box">
                <a href="scholarship-details.php?id=5">
                    <img src="path/to/image5.jpg" alt="ทุนการศึกษา 5">
                    <p>ทุนการศึกษา 5</p>
                </a>
            </div>
            <div class="image-box">
                <a href="scholarship-details.php?id=6">
                    <img src="path/to/image6.jpg" alt="ทุนการศึกษา 6">
                    <p>ทุนการศึกษา 6</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>