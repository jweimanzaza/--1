<!-- filepath: c:\xampp\htdocs\register\edit_profile.php -->
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
$profile = $result->fetch_assoc(); // $profile อาจจะเป็น null ถ้าไม่พบข้อมูล

// เพิ่มการตรวจสอบและกำหนดค่าเริ่มต้นถ้า $profile เป็น null
if (is_null($profile)) {
    $profile = []; // กำหนดให้ $profile เป็นอาร์เรย์ว่าง
}

// หากไม่มีข้อมูลใน student_profiles ให้เตรียมเพิ่มข้อมูลใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $center = $_POST['center'];
    $branch = $_POST['branch'];
    $year_level = $_POST['year_level'];
    $birthdate = $_POST['birthdate'] ?? null; // ใช้ birthdate ให้ตรงกับฐานข้อมูล
    $nationality = $_POST['nationality'] ?? null;
    $religion = $_POST['religion'] ?? null;
    $siblings = $_POST['siblings'] ?? null;
    $family_members = $_POST['family_members'] ?? null;
    $gpa = $_POST['gpa'] ?? null;
    // แปลงค่า scholarship_history ให้ตรงกับ enum ในฐานข้อมูล
    $scholarship_history = ($_POST['scholarship_history'] ?? null) === 'yes' ? 'เคย' : 'ไม่เคย';
    $scholarship_reason = $_POST['scholarship_reason'] ?? null;

    if (!$profile) {
        $data = [
            $user_id,                // 1 (i)
            $first_name,             // 2 (s)
            $last_name,              // 3 (s)
            $_POST['student_id'],    // 4 (s)
            $year_level,             // 5 (i)
            $birthdate,              // 6 (s)
            $nationality,            // 7 (s)
            $religion,               // 8 (s)
            $_POST['phone'],         // 9 (s)
            $_POST['address'],       // 10 (s)
            $_POST['province'],      // 11 (s)
            $_POST['district'],      // 12 (s)
            $_POST['sub_district'],  // 13 (s)
            $_POST['road'],          // 14 (s)
            $_POST['village'],       // 15 (s)
            null,                    // 16 image (s)
            $_POST['father_name'],   // 17 (s)
            $_POST['father_surname'],// 18 (s)
            $_POST['father_age'],    // 19 (i)
            $_POST['father_job'],    // 20 (s)
            $_POST['father_phone'],  // 21 (s)
            $_POST['father_income'], // 22 (d)
            $_POST['mother_name'],   // 23 (s)
            $_POST['mother_surname'],// 24 (s)
            $_POST['mother_age'],    // 25 (i)
            $_POST['mother_phone'],  // 26 (s)
            $_POST['mother_job'],    // 27 (s)
            $_POST['mother_income'], // 28 (s)
            $siblings,               // 29 (i)
            $family_members,         // 30 (i)
            $gpa,                    // 31 (d)
            $scholarship_reason,     // 32 (s)
            $scholarship_history,    // 33 (s)
            $_POST['parent_status'], // 34 (s)
            $center,                 // 35 (s)
            $branch                  // 36 (s)
        ];

        $insert_query = "INSERT INTO student_profiles (
            user_id, first_name, last_name, student_id, year_level, birthdate, nationality, religion, phone, address, province, district, sub_district, road, village, image,
            father_name, father_surname, father_age, father_job, father_phone, father_income,
            mother_name, mother_surname, mother_age, mother_phone, mother_job, mother_income,
            siblings, family_members, gpa, scholarship_reason, scholarship_history, parent_status, center, branch
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // types: i=int, d=double, s=string (36 fields)
        $types = "isssissssssssssssisssisssissidssssss"; // 36 ตัว

        // Debug: ตรวจสอบจำนวนฟิลด์และ types
        if (count($data) !== strlen($types)) {
            echo "<pre style='color:red'>";
            echo "จำนวนฟิลด์ใน \$data: " . count($data) . "\n";
            echo "จำนวนตัวอักษรใน \$types: " . strlen($types) . "\n";
            echo "ข้อมูล \$data:\n";
            print_r($data);
            echo "</pre>";
            die("จำนวนฟิลด์กับ types ไม่ตรงกัน กรุณาตรวจสอบโค้ดและฐานข้อมูล");
        }

        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param($types, ...$data);

        if ($stmt->execute()) {
            $message = "<p style='color: green;'>เพิ่มข้อมูลสำเร็จ</p>";
        } else {
            $message = "<p style='color: red;'>เกิดข้อผิดพลาด: " . $stmt->error . "</p>";
        }
    } else {
        // UPDATE ข้อมูลเดิม
        $update_query = "UPDATE student_profiles SET
            first_name = ?, last_name = ?, student_id = ?, year_level = ?, birthdate = ?, nationality = ?, religion = ?, phone = ?, address = ?, province = ?, district = ?, sub_district = ?, road = ?, village = ?,
            father_name = ?, father_surname = ?, father_age = ?, father_job = ?, father_phone = ?, father_income = ?,
            mother_name = ?, mother_surname = ?, mother_age = ?, mother_phone = ?, mother_job = ?, mother_income = ?,
            siblings = ?, family_members = ?, gpa = ?, scholarship_reason = ?, scholarship_history = ?, parent_status = ?, center = ?, branch = ?
            WHERE user_id = ?";

        $data = [
            $first_name, $last_name, $_POST['student_id'], $year_level, $birthdate, $nationality, $religion, $_POST['phone'], $_POST['address'], $_POST['province'], $_POST['district'], $_POST['sub_district'], $_POST['road'], $_POST['village'],
            $_POST['father_name'], $_POST['father_surname'], $_POST['father_age'], $_POST['father_job'], $_POST['father_phone'], $_POST['father_income'],
            $_POST['mother_name'], $_POST['mother_surname'], $_POST['mother_age'], $_POST['mother_phone'], $_POST['mother_job'], $_POST['mother_income'],
            $siblings, $family_members, $gpa, $scholarship_reason, $scholarship_history, $_POST['parent_status'], $center, $branch,
            $user_id // WHERE user_id = ?
        ];

        // types: s=string, i=int, d=double (33 ฟิลด์ + user_id)
        $types = "sssissssssssssssisssisssissidsssssi";

        $stmt = $conn->prepare($update_query);
        $stmt->bind_param($types, ...$data);

        if ($stmt->execute()) {
            $message = "<p style='color: green;'>อัปเดตข้อมูลสำเร็จ</p>";
        } else {
            $message = "<p style='color: red;'>เกิดข้อผิดพลาด: " . $stmt->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        .btn-edit, .btn-save {
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
            cursor: pointer;
        }
        .btn-edit:hover, .btn-save:hover {
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
            cursor: pointer;
        }
        .btn-logout:hover {
            background: linear-gradient(90deg, #f87171 0%, #ef4444 100%);
            box-shadow: 0 4px 16px rgba(239,68,68,0.18);
        }
        .edit-profile-container {
            max-width: 700px;
            margin: 36px auto 36px auto;
            background: linear-gradient(135deg, #fff 60%, #e0e7ff 100%);
            padding: 32px 28px 28px 28px;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
        }
        .edit-profile-title {
            text-align: center;
            font-size: 2rem;
            color: #2563eb;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }
        form label {
            display: block;
            margin-bottom: 6px;
            color: #222;
            font-weight: 600;
        }
        form input[type="text"],
        form input[type="date"],
        form input[type="number"],
        form input[type="email"],
        form select,
        form textarea {
            width: 100%;
            padding: 9px 12px;
            margin-bottom: 16px;
            border: 1.5px solid #2563eb;
            border-radius: 8px;
            font-size: 1rem;
            background: #f1f5f9;
            transition: border 0.18s;
        }
        form input:focus,
        form select:focus,
        form textarea:focus {
            border: 2px solid #1d4ed8;
            outline: none;
        }
        .form-actions {
            text-align: center;
            margin-top: 18px;
        }
        .form-actions .btn-save {
            min-width: 160px;
        }
        .alert-success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: 600;
        }
        .alert-error {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: 600;
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
            .btn-edit, .btn-logout, .btn-save {
                margin: 10px 0 0 0;
                width: 100%;
            }
            .edit-profile-container {
                padding: 12px 2vw 12px 2vw;
            }
            .edit-profile-title {
                font-size: 1.3rem;
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
    <div class="edit-profile-container">
        <div class="edit-profile-title">แก้ไขโปรไฟล์</div>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="form-container">
            <h2>ข้อมูลส่วนตัว</h2>
            <?= isset($message) ? $message : ''; ?>
            <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="first_name">ชื่อ</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($profile['first_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">นามสกุล</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($profile['last_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="center">หลักสูตรการศึกษา</label>
                    <input type="text" id="center" name="center" value="<?= htmlspecialchars($profile['center'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="branch">สาขา</label>
                    <input type="text" id="branch" name="branch" value="<?= htmlspecialchars($profile['branch'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="student_id">รหัสนักศึกษา</label>
                    <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($profile['student_id'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="year_level">ชั้นปี</label>
                    <input type="number" id="year_level" name="year_level" value="<?= htmlspecialchars($profile['year_level'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">เบอร์โทรศัพท์</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address">ที่อยู่</label>
                    <textarea id="address" name="address" required><?= htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="province">จังหวัด</label>
                    <input type="text" id="province" name="province" value="<?= htmlspecialchars($profile['province'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="district">อำเภอ/เขต</label>
                    <input type="text" id="district" name="district" value="<?= htmlspecialchars($profile['district'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="sub_district">ตำบล/แขวง</label>
                    <input type="text" id="sub_district" name="sub_district" value="<?= htmlspecialchars($profile['sub_district'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="road">ถนน</label>
                    <input type="text" id="road" name="road" value="<?= htmlspecialchars($profile['road'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="village">หมู่บ้าน</label>
                    <input type="text" id="village" name="village" value="<?= htmlspecialchars($profile['village'] ?? ''); ?>">
                </div>

                <!-- ข้อมูลบิดา -->
                <h3>ข้อมูลบิดา</h3>
                <div class="form-group">
                    <label for="father_name">ชื่อ</label>
                    <input type="text" id="father_name" name="father_name" value="<?= htmlspecialchars($profile['father_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="father_surname">นามสกุล</label>
                    <input type="text" id="father_surname" name="father_surname" value="<?= htmlspecialchars($profile['father_surname'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="father_age">อายุ</label>
                    <input type="number" id="father_age" name="father_age" value="<?= htmlspecialchars($profile['father_age'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="father_job">อาชีพ</label>
                    <input type="text" id="father_job" name="father_job" value="<?= htmlspecialchars($profile['father_job'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="father_phone">เบอร์โทรศัพท์</label>
                    <input type="text" id="father_phone" name="father_phone" value="<?= htmlspecialchars($profile['father_phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="father_income">รายได้</label>
                    <input type="number" step="0.01" id="father_income" name="father_income" value="<?= htmlspecialchars($profile['father_income'] ?? ''); ?>">
                </div>

                <!-- ข้อมูลมารดา -->
                <h3>ข้อมูลมารดา</h3>
                <div class="form-group">
                    <label for="mother_name">ชื่อ</label>
                    <input type="text" id="mother_name" name="mother_name" value="<?= htmlspecialchars($profile['mother_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="mother_surname">นามสกุล</label>
                    <input type="text" id="mother_surname" name="mother_surname" value="<?= htmlspecialchars($profile['mother_surname'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="mother_age">อายุ</label>
                    <input type="number" id="mother_age" name="mother_age" value="<?= htmlspecialchars($profile['mother_age'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="mother_job">อาชีพ</label>
                    <input type="text" id="mother_job" name="mother_job" value="<?= htmlspecialchars($profile['mother_job'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="mother_phone">เบอร์โทรศัพท์</label>
                    <input type="text" id="mother_phone" name="mother_phone" value="<?= htmlspecialchars($profile['mother_phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="mother_income">รายได้</label>
                    <input type="text" id="mother_income" name="mother_income" value="<?= htmlspecialchars($profile['mother_income'] ?? ''); ?>">
                </div>

                <!-- สถานะของผู้ปกครอง -->
                <h3>สถานะของผู้ปกครอง</h3>
                <div class="form-group">
                    <label for="parent_status">สถานะ</label>
                    <select id="parent_status" name="parent_status">
                        <option value="อยู่ด้วยกัน" <?= ($profile['parent_status'] ?? '') === 'อยู่ด้วยกัน' ? 'selected' : ''; ?>>อยู่ด้วยกัน</option>
                        <option value="แยกกันอยู่ด้วยความจำเป็นด้านอาชีพ" <?= ($profile['parent_status'] ?? '') === 'แยกกันอยู่ด้วยความจำเป็นด้านอาชีพ' ? 'selected' : ''; ?>>แยกกันอยู่ด้วยความจำเป็นด้านอาชีพ</option>
                        <option value="หย่าขาดจากกัน" <?= ($profile['parent_status'] ?? '') === 'หย่าขาดจากกัน' ? 'selected' : ''; ?>>หย่าขาดจากกัน</option>
                        <option value="แยกกันอยู่ด้วยสาเหตุอื่น ๆ" <?= ($profile['parent_status'] ?? '') === 'แยกกันอยู่ด้วยสาเหตุอื่น ๆ' ? 'selected' : ''; ?>>แยกกันอยู่ด้วยสาเหตุอื่น ๆ</option>
                    </select>
                </div>

                <!-- ข้อมูลส่วนตัวเพิ่มเติม -->
                <h3>ข้อมูลส่วนตัวเพิ่มเติม</h3>
                <div class="form-group">
                    <label for="dob">วัน/เดือน/ปีเกิด</label>
                    <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($profile['dob'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="age">อายุ</label>
                    <input type="number" id="age" name="age" value="<?= htmlspecialchars($profile['age'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="nationality">สัญชาติ</label>
                    <input type="text" id="nationality" name="nationality" value="<?= htmlspecialchars($profile['nationality'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="religion">ศาสนา</label>
                    <input type="text" id="religion" name="religion" value="<?= htmlspecialchars($profile['religion'] ?? ''); ?>">
                </div>

                <!-- อื่นๆ -->
                <h3>อื่นๆ</h3>
                <div class="form-group">
                    <label for="attachment">แนบไฟล์/รูปถ่าย</label>
                    <input type="file" id="attachment" name="attachment">
                </div>

                <div class="input-group">
                    <button type="submit" name="save_profile" class="btn btn-primary">บันทึก</button>
                    <a href="student_dashboard.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>