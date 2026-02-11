<!-- filepath: c:\xampp\htdocs\register\profile.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่า user_id ถูกตั้งค่าในเซสชันหรือไม่
if (!isset($_SESSION['user_id'])) {
    die("คุณยังไม่ได้เข้าสู่ระบบ");
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้จาก student_profiles
$query = "SELECT * FROM student_profiles WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

$profileNotFound = !$profile;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของคุณ</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap');
        body {
            font-family: 'Prompt', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
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
        .navbar-brand {
            font-size: 2rem;
            font-weight: bold;
            color: #d1f30f !important;
            margin-right: 18px;
            line-height: 1;
            letter-spacing: 1px;
            flex-shrink: 0;
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
        .navbar-title {
            background: #2563eb;
            color: #fff !important;
            text-align: center;
            padding: 22px 0 12px 0;
            margin: 0;
            border-radius: 0 0 18px 18px;
            width: 100vw;
            max-width: 100vw;
            box-shadow: 0 2px 12px rgba(37,99,235,0.07);
        }
        .navbar-title h2 {
            margin: 0;
            font-size: 2.1rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #fff !important;
        }
        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 32px 0 32px 0;
        }
        .profile-container {
            background: linear-gradient(135deg, #fff 60%, #e0e7ff 100%);
            padding: 36px 32px 32px 32px;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
            width: 100%;
            max-width: 600px;
            margin-top: 32px;
        }
        .profile-container h2 {
            margin-bottom: 18px;
            font-size: 1.6rem;
            color: #2563eb;
            text-align: center;
            font-weight: 700;
        }
        .profile-container h3 {
            margin-top: 28px;
            margin-bottom: 10px;
            font-size: 1.18rem;
            color: #1e293b;
            font-weight: 700;
        }
        .profile-container p {
            margin: 8px 0;
            font-size: 1rem;
            color: #475569;
        }
        .profile-container .btn-edit {
            margin-top: 24px;
            width: 100%;
            justify-content: center;
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
            .profile-container {
                padding: 18px 8px 18px 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <img src="150px-SDU2016.png" alt="SDU Logo" style="height:48px; margin-right:12px;">
        <span class="navbar-brand" style="font-size:2rem; font-weight:bold; color:#d1f30f; margin-right:18px; line-height:1; letter-spacing:1px; flex-shrink:0;">
            
        </span>
        <div class="navbar-links" style="display:flex; align-items:center; gap:18px; flex:1;">
            <a href="student_dashboard.php"><i class="fa fa-home"></i> หน้าหลัก</a>
            <a href="profile.php"><i class="fa fa-user"></i> โปรไฟล์</a>
            <a href="application_status.php"><i class="fa fa-search"></i> การติดตามสถานะการสมัคร</a>
            <a href="scholarship-list.php"><i class="fa fa-graduation-cap"></i> ทุนการศึกษา</a>
        </div>
        <div class="navbar-actions" style="display:flex; align-items:center; gap:10px;">
            <a href="edit_profile.php" class="btn-edit" style="background-color:#ffb300; color:#fff !important; padding:7px 18px; border-radius:7px; font-size:16px; display:flex; align-items:center; font-weight:bold; border:none;">
                <i class="fa fa-edit"></i> แก้ไขโปรไฟล์
            </a>
            <a href="login.php" class="btn-logout" style="background-color:#f44336; color:#fff !important; padding:7px 18px; border-radius:7px; font-size:16px; display:flex; align-items:center; font-weight:bold; border:none;">
                <i class="fa fa-sign-out"></i> ออกจากระบบ
            </a>
        </div>
    </div>
    <div class="navbar-title" style="background-color:#007bff; color:white; text-align:center; padding:18px 0 10px 0; margin:0; border-radius:0 0 10px 10px; width:100vw; max-width:100vw;">
        <h2 style="margin:0; font-size:2rem; font-weight:600; letter-spacing:1px;">โปรไฟล์</h2>
    </div>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="profile-container">
            <?php if ($profileNotFound): ?>
                <!-- Error State UI -->
                <div style="text-align: center; padding: 48px 24px;">
                    <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;">📋</div>
                    <h2 style="color: #64748b; font-size: 1.8rem; margin: 0 0 12px 0; font-weight: 600;">
                        ไม่พบข้อมูลโปรไฟล์
                    </h2>
                    <p style="color: #94a3b8; font-size: 1.08rem; margin: 0 0 28px 0; line-height: 1.6;">
                        ดูเหมือนว่าข้อมูลโปรไฟล์ของคุณยังไม่ได้ถูกสร้าง<br>
                        กรุณาคลิกปุ่มด้านล่างเพื่อเริ่มสร้างข้อมูลโปรไฟล์
                    </p>
                    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                        <a href="edit_profile.php" style="background: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%); color: #fff !important; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 1.02rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(37,99,235,0.15);">
                            <i class="fa fa-plus"></i> สร้างข้อมูลโปรไฟล์
                        </a>
                        <a href="student_dashboard.php" style="background: #f1f5f9; color: #475569 !important; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 1.02rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; border: 2px solid #cbd5e1;">
                            <i class="fa fa-home"></i> กลับหน้าหลัก
                        </a>
                    </div>
                </div>
            <?php else: ?>
            <h2>ข้อมูลส่วนตัว</h2>
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($profile['first_name']); ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($profile['last_name']); ?></p>
            <p><strong>รหัสนักศึกษา:</strong> <?= htmlspecialchars($profile['student_id']); ?></p>
            <p><strong>ชั้นปี:</strong> <?= htmlspecialchars($profile['year_level']); ?></p>
            <p><strong>วัน/เดือน/ปีเกิด:</strong> <?= htmlspecialchars($profile['birthdate'] ?? '-'); ?></p>
            <p><strong>อายุ:</strong> <?= htmlspecialchars($profile['age'] ?? '-'); ?></p>
            <p><strong>สัญชาติ:</strong> <?= htmlspecialchars($profile['nationality'] ?? '-'); ?></p>
            <p><strong>ศาสนา:</strong> <?= htmlspecialchars($profile['religion'] ?? '-'); ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($profile['phone']); ?></p>
            <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($profile['address']); ?></p>
            <p><strong>จังหวัด:</strong> <?= htmlspecialchars($profile['province']); ?></p>
            <p><strong>อำเภอ/เขต:</strong> <?= htmlspecialchars($profile['district']); ?></p>
            <p><strong>ตำบล/แขวง:</strong> <?= htmlspecialchars($profile['sub_district']); ?></p>
            <p><strong>ถนน:</strong> <?= htmlspecialchars($profile['road']); ?></p>
            <p><strong>หมู่บ้าน:</strong> <?= htmlspecialchars($profile['village']); ?></p>
            <p><strong>หลักสูตรการศึกษา:</strong> <?= htmlspecialchars($profile['center'] ?? '-'); ?></p>
            <p><strong>สาขา:</strong> <?= htmlspecialchars($profile['branch'] ?? '-'); ?></p>

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
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($profile['mother_name'] ?? '') ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($profile['mother_surname'] ?? '') ?></p>
            <p><strong>อายุ:</strong> <?= htmlspecialchars($profile['mother_age'] ?? '') ?></p>
            <p><strong>อาชีพ:</strong> <?= htmlspecialchars($profile['mother_job'] ?? '') ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($profile['mother_phone'] ?? '') ?></p>
            <p><strong>รายได้:</strong> <?= htmlspecialchars($profile['mother_income'] ?? '') ?></p>

            <h3>สถานะของผู้ปกครอง</h3>
            <p><strong>สถานะ:</strong> <?= htmlspecialchars($profile['parent_status']); ?></p>

            <a href="edit_profile.php" class="btn btn-edit">แก้ไขข้อมูล</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>