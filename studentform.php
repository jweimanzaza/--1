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
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $sql = "INSERT INTO student_profiles 
            (user_id, first_name, last_name, year_level, student_id, program, major, phone, address, village, road, sub_district, district, province, image)
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
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
    $stmt->execute();
    echo "<p style='color: green;'>บันทึกข้อมูลเรียบร้อยแล้ว!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กรอกข้อมูลส่วนตัว</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>กรอกข้อมูลส่วนตัว</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="first_name" placeholder="ชื่อ" required><br>
        <input type="text" name="last_name" placeholder="นามสกุล" required><br>
        <input type="text" name="year_level" placeholder="ชั้นปี" required><br>
        <input type="text" name="student_id" placeholder="รหัสนักศึกษา" required><br>
        <input type="text" name="program" placeholder="หลักสูตร"><br>
        <input type="text" name="major" placeholder="สาขาวิชา"><br>
        <input type="text" name="phone" placeholder="เบอร์โทรศัพท์"><br>
        <textarea name="address" placeholder="ที่อยู่ปัจจุบัน"></textarea><br>
        <input type="text" name="village" placeholder="หมู่ที่"><br>
        <input type="text" name="road" placeholder="ถนน"><br>
        <input type="text" name="sub_district" placeholder="ตำบล/แขวง"><br>
        <input type="text" name="district" placeholder="อำเภอ/เขต"><br>
        <input type="text" name="province" placeholder="จังหวัด"><br>
        <input type="file" name="image" accept=".jpg,.jpeg,.png"><br>
        <button type="submit">บันทึกข้อมูล</button>
    </form>
</body>
</html>