<!-- filepath: c:\xampp\htdocs\register\view_profile.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าเป็น Admin หรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการส่ง user_id มาหรือไม่
$user_id = null;
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
}

// ดึงข้อมูลโปรไฟล์ของผู้ใช้ที่เลือก (ถ้ามี user_id)
$user = null;
if ($user_id) {
    $query = "SELECT first_name, last_name, student_id, year_level AS year, phone, address, province, district, 
                 sub_district, road, village, 
                 birthdate, age, nationality, religion,
                 siblings, family_members, gpa, scholarship_history, scholarship_reason,
                 father_name, father_surname, father_age, father_job, 
                 father_phone, father_income, mother_name, mother_surname, mother_age, mother_job, mother_phone, mother_income, parent_status,
                 center, branch
          FROM student_profiles 
          WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ผู้ใช้</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap');
        body {
            font-family: 'Prompt', Arial, sans-serif;
            background: #f8fafc;
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
            margin-bottom: 24px;
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
            cursor: pointer;
        }
        .btn-logout:hover {
            background: linear-gradient(90deg, #f87171 0%, #ef4444 100%);
            box-shadow: 0 4px 16px rgba(239,68,68,0.18);
        }
        .profile-container {
            max-width: 600px;
            margin: 36px auto 0 auto;
            background: #fff;
            padding: 36px 32px 32px 32px;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
        }
        .profile-title {
            color: #2563eb;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 24px;
        }
        .profile-section-title {
            color: #1e293b;
            font-size: 1.1rem;
            font-weight: 700;
            margin: 24px 0 10px 0;
            border-left: 4px solid #2563eb;
            padding-left: 10px;
        }
        .profile-container p {
            font-size: 1.08rem;
            margin: 8px 0;
            line-height: 1.9;
        }
        .profile-container p strong {
            color: #2563eb;
            min-width: 120px;
            display: inline-block;
        }
        .btn-back {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 32px;
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.08rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            transition: background 0.18s, box-shadow 0.18s;
        }
        .btn-back:hover {
            background: linear-gradient(90deg, #60a5fa 0%, #2563eb 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.18);
        }
        @media (max-width: 700px) {
            .profile-container { padding: 14px 2vw 14px 2vw; }
            .profile-container p { font-size: 1rem; }
            .btn-back { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="150px-SDU2016.png" alt="SDU Logo" style="height:44px; margin-right:12px;">
        <div class="navbar-links">
            <a href="admin_dashboard.php"><i class="fa fa-home"></i> หน้าหลัก</a>
            <a href="manage_users.php"><i class="fa fa-users"></i> จัดการผู้ใช้</a>
            <a href="manage_scholarships.php"><i class="fa fa-graduation-cap"></i> จัดการทุนการศึกษา</a>
        </div>
        <div class="navbar-actions">
            <a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="profile-container">
        <div class="profile-title"><i class="fa fa-user"></i> โปรไฟล์ผู้ใช้</div>
        <?php if ($user): ?>
            <div class="profile-section-title"><i class="fa fa-id-card"></i> ข้อมูลส่วนตัว</div>
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($user['first_name']); ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($user['last_name']); ?></p>
            <p><strong>รหัสนักศึกษา:</strong> <?= htmlspecialchars($user['student_id']); ?></p>
            <p><strong>ชั้นปี:</strong> <?= htmlspecialchars($user['year']); ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($user['phone']); ?></p>
            <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($user['address']); ?></p>
            <p><strong>จังหวัด:</strong> <?= htmlspecialchars($user['province']); ?></p>
            <p><strong>อำเภอ/เขต:</strong> <?= htmlspecialchars($user['district']); ?></p>
            <p><strong>ตำบล/แขวง:</strong> <?= htmlspecialchars($user['sub_district'] ?? '-'); ?></p>
            <p><strong>ถนน:</strong> <?= htmlspecialchars($user['road']); ?></p>
            <p><strong>หมู่บ้าน:</strong> <?= htmlspecialchars($user['village']); ?></p>
            <p><strong>หลักสูตรการศึกษา:</strong> <?= htmlspecialchars($user['center'] ?? '-'); ?></p>
            <p><strong>สาขา:</strong> <?= htmlspecialchars($user['branch'] ?? '-'); ?></p>
            <p><strong>วัน/เดือน/ปีเกิด:</strong> <?= htmlspecialchars($user['birthdate'] ?? '-'); ?></p>
            <p><strong>อายุ:</strong> <?= htmlspecialchars($user['age'] ?? '-'); ?></p>
            <p><strong>สัญชาติ:</strong> <?= htmlspecialchars($user['nationality'] ?? '-'); ?></p>
            <p><strong>ศาสนา:</strong> <?= htmlspecialchars($user['religion'] ?? '-'); ?></p>
            <p><strong>จำนวนพี่น้อง:</strong> <?= htmlspecialchars($user['siblings'] ?? '-'); ?></p>
            <p><strong>จำนวนสมาชิกในครอบครัว:</strong> <?= htmlspecialchars($user['family_members'] ?? '-'); ?></p>
            <p><strong>เกรดเฉลี่ย (GPA):</strong> <?= htmlspecialchars($user['gpa'] ?? '-'); ?></p>
            <p><strong>เคยได้รับทุนหรือไม่:</strong> <?= htmlspecialchars($user['scholarship_history'] ?? '-'); ?></p>
            <p><strong>เหตุผลที่ขอทุน:</strong> <?= htmlspecialchars($user['scholarship_reason'] ?? '-'); ?></p>

            <div class="profile-section-title"><i class="fa fa-male"></i> ข้อมูลบิดา</div>
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($user['father_name']); ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($user['father_surname']); ?></p>
            <p><strong>อายุ:</strong> <?= htmlspecialchars($user['father_age']); ?></p>
            <p><strong>อาชีพ:</strong> <?= htmlspecialchars($user['father_job']); ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($user['father_phone']); ?></p>
            <p><strong>รายได้:</strong> <?= htmlspecialchars($user['father_income']); ?></p>

            <div class="profile-section-title"><i class="fa fa-female"></i> ข้อมูลมารดา</div>
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($user['mother_name']); ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($user['mother_surname']); ?></p>
            <p><strong>อายุ:</strong> <?= htmlspecialchars($user['mother_age']); ?></p>
            <p><strong>อาชีพ:</strong> <?= htmlspecialchars($user['mother_job']); ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($user['mother_phone']); ?></p>
            <p><strong>รายได้:</strong> <?= htmlspecialchars($user['mother_income']); ?></p>

            <div class="profile-section-title"><i class="fa fa-users"></i> สถานะของผู้ปกครอง</div>
            <p><strong>สถานะ:</strong> <?= htmlspecialchars($user['parent_status']); ?></p>

            <a href="manage_applications.php" class="btn-back"><i class="fa fa-arrow-left"></i> กลับไปหน้าหลัก</a>
        <?php else: ?>
            <p>ไม่พบข้อมูลผู้ใช้</p>
            <a href="manage_applications.php" class="btn-back"><i class="fa fa-arrow-left"></i> กลับไปหน้าหลัก</a>
        <?php endif; ?>
    </div>
</body>
</html>