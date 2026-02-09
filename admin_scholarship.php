<?php
session_start();
include('server.php');

// ตรวจสอบว่าเป็น admin หรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลการสมัครทุนทั้งหมด
$query = "SELECT sa.id AS application_id, s.first_name, s.last_name, s.student_id, sc.name AS scholarship_name, sa.status
          FROM scholarship_applications sa
          JOIN student_profiles s ON sa.user_id = s.user_id
          JOIN scholarships sc ON sa.scholarship_id = sc.id";
$applications = mysqli_query($conn, $query);

// เมื่อ Admin กดอนุมัติหรือปฏิเสธ
if (isset($_POST['update_status'])) {
    $application_id = $_POST['application_id'];
    $new_status = $_POST['status'];

    $update_query = "UPDATE scholarship_applications SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $application_id);
    $stmt->execute();
    header("Location: admin_scholarship.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการสมัครทุน</title>
</head>
<body>
    <h2>จัดการการสมัครทุน</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ชื่อ</th>
            <th>นามสกุล</th>
            <th>รหัสนักศึกษา</th>
            <th>ทุนการศึกษา</th>
            <th>สถานะ</th>
            <th>การจัดการ</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($applications)): ?>
            <tr>
                <td><?= htmlspecialchars($row['first_name']) ?></td>
                <td><?= htmlspecialchars($row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['student_id']) ?></td>
                <td><?= htmlspecialchars($row['scholarship_name']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">
                        <button type="submit" name="update_status" value="approved">อนุมัติ</button>
                        <button type="submit" name="update_status" value="rejected">ปฏิเสธ</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>