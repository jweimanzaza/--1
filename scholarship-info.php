<!-- filepath: c:\xampp\htdocs\register\scholarship-info.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ดึง user_id จาก username
$username = $_SESSION['username'];
$query = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$user_id = $row['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $year_level = $_POST['year_level'];
    $student_id = $_POST['student_id'];
    $program = $_POST['program'];
    $major = $_POST['major'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $village = $_POST['village'];
    $road = $_POST['road'];
    $sub_district = $_POST['sub_district'];
    $district = $_POST['district'];
    $province = $_POST['province'];

    // รูปภาพ
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        echo "<p style='color: green;'>อัปโหลดรูปภาพสำเร็จ!</p>";
    } else {
        echo "<p style='color: red;'>การอัปโหลดรูปภาพล้มเหลว!</p>";
    }

    // ตรวจสอบว่ามีข้อมูลอยู่แล้วหรือไม่
    $check_query = "SELECT * FROM student_profiles WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // อัปเดตข้อมูล
        $update_query = "UPDATE student_profiles SET 
            first_name = ?, 
            last_name = ?, 
            year_level = ?, 
            student_id = ?, 
            program = ?, 
            major = ?, 
            phone = ?, 
            address = ?, 
            village = ?, 
            road = ?, 
            sub_district = ?, 
            district = ?, 
            province = ?, 
            image = ? 
            WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param(
            "ssssssssssssssi",
            $first_name,
            $last_name,
            $year_level,
            $student_id,
            $program,
            $major,
            $phone,
            $address,
            $village,
            $road,
            $sub_district,
            $district,
            $province,
            $image,
            $user_id
        );
        if ($update_stmt->execute()) {
            echo "<p style='color: green;'>อัปเดตข้อมูลเรียบร้อยแล้ว!</p>";
        } else {
            echo "<p style='color: red;'>เกิดข้อผิดพลาด: " . $update_stmt->error . "</p>";
        }
    } else {
        // เพิ่มข้อมูลใหม่
        $insert_query = "INSERT INTO student_profiles 
            (user_id, first_name, last_name, year_level, student_id, program, major, phone, address, village, road, sub_district, district, province, image) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param(
            "issssssssssssss",
            $user_id,
            $first_name,
            $last_name,
            $year_level,
            $student_id,
            $program,
            $major,
            $phone,
            $address,
            $village,
            $road,
            $sub_district,
            $district,
            $province,
            $image
        );
        if ($insert_stmt->execute()) {
            echo "<p style='color: green;'>บันทึกข้อมูลเรียบร้อยแล้ว!</p>";
        } else {
            echo "<p style='color: red;'>เกิดข้อผิดพลาด: " . $insert_stmt->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลการสมัครทุน</title>
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

    <div class="header">
        <h2>ข้อมูลการสมัครทุน</h2>
    </div>

    <form method="POST" enctype="multipart/form-data" class="scholarship-form">
        <h3>ข้อมูลการสมัครทุน</h3>

        <div class="form-row">
            <label>ชื่อ</label>
            <input type="text" name="first_name" required>
            <label>นามสกุล</label>
            <input type="text" name="last_name" required>
            <label>ชั้นปี</label>
            <input type="text" name="year_level" required>
            <label>รหัสนักศึกษา</label>
            <input type="text" name="student_id" required>
        </div>

        <div class="form-row">
            <label>หลักสูตร</label>
            <input type="text" name="program" required>
            <label>สาขาวิชา</label>
            <input type="text" name="major" required>
            <label>เบอร์โทรศัพท์มือถือ</label>
            <input type="text" name="phone" required>
        </div>

        <div class="form-row">
            <label>ที่อยู่ปัจจุบัน</label>
            <input type="text" name="address" required>
            <label>หมู่ที่</label>
            <input type="text" name="village" required>
            <label>ถนน</label>
            <input type="text" name="road" required>
            <label>ตำบล/แขวง</label>
            <input type="text" name="sub_district" required>
            <label>อำเภอ/เขต</label>
            <input type="text" name="district" required>
            <label>จังหวัด</label>
            <input type="text" name="province" required>
        </div>

        <div class="form-row">
            <label>อัปโหลดรูปภาพ (เฉพาะ .png, .jpg)</label>
            <input type="file" name="image" accept=".png, .jpg" required>
        </div>

        <div class="form-row">
            <button type="submit" name="submit_scholarship" class="btn">ยืนยัน</button>
        </div>
    </form>
</body>
</html>