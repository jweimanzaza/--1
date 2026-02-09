<!-- filepath: c:\xampp\htdocs\register\scholarship-details.php -->
<?php
date_default_timezone_set('Asia/Bangkok'); // เพิ่มบรรทัดนี้
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('server.php');

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามี scholarship_id ใน URL หรือไม่
if (!isset($_GET['id'])) {
    die("ไม่พบ ID ของทุนการศึกษา");
}

$scholarship_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// ดึงข้อมูลทุนการศึกษาจากฐานข้อมูล
$query = "SELECT * FROM scholarships WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $scholarship_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $scholarship = $result->fetch_assoc();
} else {
    die("ไม่พบข้อมูลทุนการศึกษา");
}

// หากผู้ใช้เป็น admin ให้เปลี่ยนเส้นทางไปยัง manage_scholarships.php
// *** ลบหรือคอมเมนต์โค้ดนี้ออก ***
// if ($_SESSION['role'] === 'admin') {
//     header("Location: manage_scholarships.php?id=$scholarship_id");
//     exit();
// }

// ตรวจสอบสถานะทุนการศึกษา
$is_closed = ($scholarship['status'] === 'ปิดรับสมัคร');

// ตรวจสอบว่าผู้ใช้เคยสมัครทุนนี้ไปแล้วหรือไม่ (เฉพาะนักศึกษา)
function checkHasApplied($conn, $user_id, $scholarship_id) {
    $check_query = "SELECT * FROM scholarship_applications WHERE user_id = ? AND scholarship_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $scholarship_id);
    $stmt->execute();
    $application_result = $stmt->get_result();
    return $application_result->num_rows > 0;
}

$has_applied = false;
if ($_SESSION['role'] === 'student') {
    $has_applied = checkHasApplied($conn, $user_id, $scholarship_id);
}

// ตรวจสอบและอัปเดตสถานะทุนถ้าถึงเวลาปิด (ต้องมาก่อน POST)
if ($scholarship_id > 0) {
    $sql = "SELECT closing_date, status FROM scholarships WHERE id = $scholarship_id";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $now = date('Y-m-d H:i:s');
        if ($row['status'] == 'เปิดรับสมัคร' && $now > $row['closing_date']) {
            // อัปเดตสถานะเป็น 'ปิดรับสมัคร'
            $update = "UPDATE scholarships SET status = 'ปิดรับสมัคร' WHERE id = $scholarship_id";
            mysqli_query($conn, $update);
        }
        // ดึงข้อมูลทุนใหม่หลังอัปเดตสถานะ
        $query = "SELECT * FROM scholarships WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $scholarship_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $scholarship = $result->fetch_assoc();
        }
    }
}

// ตรวจสอบสถานะทุนการศึกษา (ต้องอยู่หลังอัปเดตและดึงข้อมูลใหม่)
$is_closed = ($scholarship['status'] === 'ปิดรับสมัคร');

// --- ส่วนสมัครทุนและแจ้งเตือน ---
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    // ตรวจสอบสถานะทุนก่อนสมัคร
    if ($is_closed) {
        $error_message = "ทุนนี้ได้ปิดรับสมัครแล้ว";
    } else {
        // ตรวจสอบซ้ำก่อน insert
        if (checkHasApplied($conn, $user_id, $scholarship_id)) {
            $error_message = "คุณได้เคยสมัครทุนการศึกษานี้ไปแล้ว";
            $has_applied = true;
        } else {
            // ตรวจสอบว่ามีไฟล์ PDF แนบมาหรือไม่
            if (
                isset($_FILES['application_pdf']) &&
                $_FILES['application_pdf']['error'] === UPLOAD_ERR_OK &&
                strtolower(pathinfo($_FILES['application_pdf']['name'], PATHINFO_EXTENSION)) === 'pdf'
            ) {
                $pdf_name = uniqid() . '_' . basename($_FILES['application_pdf']['name']);
                move_uploaded_file($_FILES['application_pdf']['tmp_name'], 'uploads/' . $pdf_name);

                $status = 'รอดำเนินการ';
                $insert_stmt = $conn->prepare("INSERT INTO scholarship_applications (user_id, scholarship_id, status, pdf_file) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("iiss", $user_id, $scholarship_id, $status, $pdf_name);
                if ($insert_stmt->execute()) {
                    $success_message = "คุณได้สมัครทุนการศึกษาสำเร็จ";
                    $has_applied = true;
                } else {
                    $error_message = "เกิดข้อผิดพลาด กรุณาลองใหม่";
                }
            } else {
                $error_message = "กรุณาอัปโหลดไฟล์ PDF ก่อนสมัครทุน";
            }
        }
    }
}

// รับ id ทุนจาก GET
$scholarship_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ตรวจสอบและอัปเดตสถานะทุนถ้าถึงเวลาปิด
if ($scholarship_id > 0) {
    $sql = "SELECT closing_date, status FROM scholarships WHERE id = $scholarship_id";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $now = date('Y-m-d H:i:s');
        if ($row['status'] == 'เปิดรับสมัคร' && $now > $row['closing_date']) {
            // อัปเดตสถานะเป็น 'ปิดรับสมัคร'
            $update = "UPDATE scholarships SET status = 'ปิดรับสมัคร' WHERE id = $scholarship_id";
            mysqli_query($conn, $update);
        }
        // *** ดึงข้อมูลทุนใหม่หลังอัปเดตสถานะ ***
        $query = "SELECT * FROM scholarships WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $scholarship_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $scholarship = $result->fetch_assoc();
        }
    }
}

// ตรวจสอบสถานะทุนการศึกษา (ย้ายมาตรงนี้)
$is_closed = ($scholarship['status'] === 'ปิดรับสมัคร');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($scholarship['name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        .scholarship-detail {
            max-width: 800px;
            margin: 36px auto 36px auto;
            background: linear-gradient(135deg, #fff 60%, #e0e7ff 100%);
            padding: 32px 28px 28px 28px;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
        }
        .scholarship-header {
            background: #2563eb;
            padding: 24px 0 18px 0;
            text-align: center;
            border-radius: 14px 14px 0 0;
        }
        .scholarship-header h1 {
            margin: 0;
            font-size: 2rem;
            color: #fff;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .scholarship-content {
            padding: 22px 0 0 0;
            font-size: 1.08rem;
            line-height: 1.7;
            color: #475569;
        }
        .scholarship-content p {
            margin: 0 0 10px 0;
        }
        .scholarship-actions {
            text-align: center;
            margin-top: 24px;
        }
        .scholarship-actions button,
        .scholarship-actions input[type="submit"] {
            padding: 10px 28px;
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
        }
        .scholarship-actions button:hover,
        .scholarship-actions input[type="submit"]:hover {
            background: linear-gradient(90deg, #60a5fa 0%, #2563eb 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.18);
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
        .btn-download {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 10px;
            font-weight: 600;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(34,197,94,0.10);
        }
        .btn-download:hover {
            background: linear-gradient(90deg, #4ade80 0%, #22c55e 100%);
            box-shadow: 0 4px 16px rgba(34,197,94,0.18);
        }
        @media (max-width: 900px) {
            .scholarship-detail {
                padding: 16px 4vw 16px 4vw;
            }
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
            .scholarship-detail {
                padding: 12px 2vw 12px 2vw;
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
            <a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
        </div>
    </div>
    <!-- Scholarship Details -->
    <div class="scholarship-detail">
        <div class="scholarship-header">
            <h1><?= htmlspecialchars($scholarship['name']); ?></h1>
        </div>
        <div class="scholarship-content">
            <p><?= nl2br(htmlspecialchars($scholarship['description'])); ?></p>
            <p><strong>วันที่เปิด:</strong> <?= date('d/m/Y H:i', strtotime($scholarship['opening_date'])); ?></p>
            <p><strong>วันที่ปิด:</strong> <?= date('d/m/Y H:i', strtotime($scholarship['closing_date'])); ?></p>
            <?php if ($scholarship['name'] === 'ทุนการศึกษาสำหรับนักศึกษาที่มีผลการเรียนดี'): ?>
                <a href="https://drive.google.com/file/d/1l0I-ZIfPdwmHZAyLFZbQ0jjEVh4B3wBO/view?usp=sharing" target="_blank" class="btn btn-download">ดาวน์โหลดแบบฟอร์ม PDF</a>
            <?php elseif ($scholarship['name'] === 'ทุนการศึกษาสำหรับนักศึกษาที่ขาดแคลนทุนทรัพย์'): ?>
                <a href="https://drive.google.com/file/d/120Nphd3rxFdnfRoh-uPJ2Xk-2g_Rc60a/view?usp=drive_link" target="_blank" class="btn btn-download">ดาวน์โหลดแบบฟอร์ม PDF</a>
            <?php endif; ?>
        </div>
        <div class="scholarship-actions">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="alert-error">ผู้ดูแลระบบไม่สามารถสมัครทุนการศึกษาได้</div>
            <?php elseif ($is_closed): ?>
                <p style="color: red; font-weight: bold;">ทุนนี้ได้ปิดไปแล้ว</p>
            <?php elseif ($success_message): ?>
                <div class="alert-success"><?= $success_message; ?></div>
            <?php elseif ($has_applied): ?>
                <div class="alert-error">คุณได้เคยสมัครทุนการศึกษานี้ไปแล้ว</div>
            <?php elseif ($error_message): ?>
                <div class="alert-error"><?= $error_message; ?></div>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="application_pdf" accept="application/pdf" required style="margin-bottom:12px;">
                    <input type="submit" name="apply" value="สมัครทุน">
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>