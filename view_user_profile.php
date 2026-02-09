<!-- filepath: c:\xampp\htdocs\register\view_user_profile.php -->
<?php
session_start();
include('server.php');

// ดึง user_id จาก URL
if (!isset($_GET['user_id'])) {
    die("ไม่พบ user_id");
}

$user_id = intval($_GET['user_id']); // แปลงเป็นตัวเลขเพื่อความปลอดภัย

// ดึงข้อมูลผู้ใช้จาก student_profiles
$query = "SELECT * FROM student_profiles WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

if (!$profile) {
    die("ไม่พบข้อมูลโปรไฟล์");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลส่วนตัวผู้ใช้</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .profile-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }
        .profile-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            text-align: center;
        }
        .profile-container p {
            margin: 10px 0;
            font-size: 16px;
            color: #555;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .btn-back {
            background-color: #007bff;
            color: white;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>ข้อมูลส่วนตัวผู้ใช้</h2>
    </div>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="profile-container">
            <h2>ข้อมูลส่วนตัว</h2>
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($profile['first_name']); ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($profile['last_name']); ?></p>
            <p><strong>รหัสนักศึกษา:</strong> <?= htmlspecialchars($profile['student_id']); ?></p>
            <p><strong>ชั้นปี:</strong> <?= htmlspecialchars($profile['year_level']); ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($profile['phone']); ?></p>
            <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($profile['address']); ?></p>
            <p><strong>จังหวัด:</strong> <?= htmlspecialchars($profile['province']); ?></p>
            <p><strong>อำเภอ/เขต:</strong> <?= htmlspecialchars($profile['district']); ?></p>
            <p><strong>ตำบล/แขวง:</strong> <?= htmlspecialchars($profile['sub_district']); ?></p>
            <p><strong>ถนน:</strong> <?= htmlspecialchars($profile['road']); ?></p>
            <p><strong>หมู่บ้าน:</strong> <?= htmlspecialchars($profile['village']); ?></p>
            <p><strong>หลักสูตรการศึกษา:</strong> <?= htmlspecialchars($profile['center'] ?? '-'); ?></p>
            <p><strong>สาขา:</strong> <?= htmlspecialchars($profile['branch'] ?? '-'); ?></p>

            <p><strong>วัน/เดือน/ปีเกิด:</strong> <?= htmlspecialchars($profile['birthdate'] ?? '-'); ?></p>
            <p><strong>อายุ:</strong> <?= htmlspecialchars($profile['age'] ?? '-'); ?></p>
            <p><strong>สัญชาติ:</strong> <?= htmlspecialchars($profile['nationality'] ?? '-'); ?></p>
            <p><strong>ศาสนา:</strong> <?= htmlspecialchars($profile['religion'] ?? '-'); ?></p>
            <p><strong>จำนวนพี่น้อง:</strong> <?= htmlspecialchars($profile['siblings'] ?? '-'); ?></p>
            <p><strong>จำนวนสมาชิกในครอบครัว:</strong> <?= htmlspecialchars($profile['family_members'] ?? '-'); ?></p>
            <p><strong>เกรดเฉลี่ย (GPA):</strong> <?= htmlspecialchars($profile['gpa'] ?? '-'); ?></p>
            <p><strong>เคยได้รับทุนหรือไม่:</strong> <?= htmlspecialchars($profile['scholarship_history'] ?? '-'); ?></p>
            <p><strong>เหตุผลที่ขอทุน:</strong> <?= htmlspecialchars($profile['scholarship_reason'] ?? '-'); ?></p>

            <h3>ข้อมูลบิดา</h3>
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($profile['father_name']); ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($profile['father_surname']); ?></p>
            <p><strong>อายุ:</strong> <?= htmlspecialchars($profile['father_age']); ?></p>
            <p><strong>อาชีพ:</strong> <?= htmlspecialchars($profile['father_job']); ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($profile['father_phone']); ?></p>
            <p><strong>รายได้:</strong> <?= htmlspecialchars($profile['father_income']); ?></p>

            <h3>ข้อมูลมารดา</h3>
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($profile['mother_name']); ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($profile['mother_surname']); ?></p>
            <p><strong>อายุ:</strong> <?= htmlspecialchars($profile['mother_age']); ?></p>
            <p><strong>อาชีพ:</strong> <?= htmlspecialchars($profile['mother_job']); ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($profile['mother_phone'] ?? '') ?></p>
            <p><strong>รายได้:</strong> <?= htmlspecialchars($profile['mother_income'] ?? '') ?></p>

            <h3>สถานะของผู้ปกครอง</h3>
            <p><strong>สถานะ:</strong> <?= htmlspecialchars($profile['parent_status']); ?></p>

            <a href="committee_review.php" class="btn btn-back">กลับไปหน้าหลัก</a>
        </div>
    </div>
</body>
</html>