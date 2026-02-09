<!-- filepath: c:\xampp\htdocs\register\scholarships.php -->
<?php
session_start();

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
    <title>ทุนการศึกษา</title>
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
            <a href="index.php?logout='1'" style="color: red;">Logout</a>
        </div>
    </div>

    <div class="header">
        <h2>ทุนการศึกษา</h2>
    </div>

    <div class="filter">
        <p class="dropdown">
            ประเภทของทุน <span>▼</span>
            <ul class="dropdown-menu">
                <li><a href="scholarships.php?type=internal">ทุนใน</a></li>
                <li><a href="scholarships.php?type=external">ทุนนอก</a></li>
            </ul>
        </p>
    </div>

    <div class="scholarship-grid">
        <div class="scholarship-box" style="background-color: #FFCCCC;">
            <a href="scholarship-detail.php?id=1" class="scholarship-link">
                <p>ทุนใน 1</p>
            </a>
        </div>
        <div class="scholarship-box" style="background-color: #CCFFCC;">
            <a href="scholarship-detail.php?id=2" class="scholarship-link">
                <p>ทุนใน 2</p>
            </a>
        </div>
        <div class="scholarship-box" style="background-color: #CCCCFF;">
            <a href="scholarship-detail.php?id=3" class="scholarship-link">
                <p>ทุนนอก 1</p>
            </a>
        </div>
        <div class="scholarship-box" style="background-color: #FFCCFF;">
            <a href="scholarship-detail.php?id=4" class="scholarship-link">
                <p>ทุนนอก 2</p>
            </a>
        </div>
    </div>
</body>
</html>