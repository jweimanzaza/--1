<?php
session_start();
include('server.php');
$path = __DIR__ . '/libs/tcpdf/tcpdf.php';
if (!file_exists($path)) {
    die("ไม่พบไฟล์: $path");
}
require_once($path);

// ตรวจสอบว่าเป็น Admin หรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการส่ง user_id มาหรือไม่
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $query = "SELECT first_name, last_name, student_id, year_level AS year, phone, address, province, district, sub_district AS subdistrict, road, village 
              FROM student_profiles 
              WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "ไม่พบข้อมูลผู้ใช้";
        exit();
    }
} else {
    echo "ไม่มีการระบุ User ID";
    exit();
}

// รับความคิดเห็นของกรรมการจากแบบฟอร์ม
$committee_comment = isset($_POST['committee_comment']) ? $_POST['committee_comment'] : '';

// สร้าง PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('ระบบจัดการทุนการศึกษา');
$pdf->SetTitle('ใบสมัครขอรับทุนการศึกษา');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// เพิ่มข้อมูลส่วนตัวของผู้ใช้
$html = '
<h3 style="text-align: center;">แบบฟอร์มใบสมัครขอรับทุนการศึกษา</h3>
<p><strong>ชื่อ:</strong> ' . htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) . '</p>
<p><strong>รหัสนักศึกษา:</strong> ' . htmlspecialchars($user['student_id']) . '</p>
<p><strong>ชั้นปี:</strong> ' . htmlspecialchars($user['year']) . '</p>
<p><strong>เบอร์โทรศัพท์:</strong> ' . htmlspecialchars($user['phone']) . '</p>
<p><strong>ที่อยู่:</strong> ' . htmlspecialchars($user['address']) . ', ' . htmlspecialchars($user['subdistrict']) . ', ' . htmlspecialchars($user['district']) . ', ' . htmlspecialchars($user['province']) . '</p>
<p><strong>ถนน:</strong> ' . htmlspecialchars($user['road']) . '</p>
<p><strong>หมู่บ้าน:</strong> ' . htmlspecialchars($user['village']) . '</p>
';

// เพิ่มความคิดเห็นของกรรมการ
$html .= '
<h4>ความเห็นประธานกรรมการบริหารหลักสูตร</h4>
<p>' . nl2br(htmlspecialchars($committee_comment)) . '</p>
';

// เขียน HTML ลงใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// บันทึกหรือแสดง PDF
$pdf->Output('application_form.pdf', 'I');
?>