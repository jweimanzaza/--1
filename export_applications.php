<?php
include('server.php');

$where = '';
if (isset($_GET['scholarship_id']) && $_GET['scholarship_id'] != '') {
    $scholarship_id = intval($_GET['scholarship_id']);
    $where = "WHERE r.scholarship_id = $scholarship_id";
}

// ดึงข้อมูลชื่อ user, ชื่อ-นามสกุล, รหัสนักศึกษา, ชื่อทุน, คะแนน, สถานะการสมัคร
$query = "SELECT 
            u.username AS user_name, 
            sp.first_name, 
            sp.last_name, 
            sp.student_id, 
            sp.center, 
            sp.branch, 
            s.name AS scholarship_name, 
            r.score,
            sa.status,
            sa.final_status
          FROM review_scores r
          JOIN users u ON r.user_id = u.id
          JOIN scholarships s ON r.scholarship_id = s.id
          JOIN student_profiles sp ON r.user_id = sp.user_id
          LEFT JOIN scholarship_applications sa 
            ON r.user_id = sa.user_id AND r.scholarship_id = sa.scholarship_id
          $where";
$result = $conn->query($query);

// เพิ่ม BOM เพื่อให้ Excel อ่านภาษาไทยถูกต้อง
echo "\xEF\xBB\xBF";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=review_scores_export.csv');

// สร้างไฟล์ CSV
$output = fopen('php://output', 'w');
fputcsv($output, ['ชื่อผู้ใช้', 'ชื่อ', 'นามสกุล', 'รหัสนักศึกษา', 'หลักสูตรการศึกษา', 'สาขา', 'ชื่อทุนการศึกษา', 'คะแนน', 'สถานะการสมัคร']);

while ($row = $result->fetch_assoc()) {
    // ใช้ empty() จะครอบคลุมทั้ง NULL, '', 0
    $application_status = !empty($row['final_status']) ? $row['final_status'] : $row['status'];
    fputcsv($output, [
        $row['user_name'],
        $row['first_name'],
        $row['last_name'],
        "'" . $row['student_id'], // เพิ่ม ' ข้างหน้า
        $row['center'],
        $row['branch'],
        $row['scholarship_name'],
        $row['score'],
        $application_status
    ]);
}
fclose($output);
exit;