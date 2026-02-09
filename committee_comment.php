<!-- filepath: c:\xampp\htdocs\register\committee_comment.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบในบทบาทกรรมการหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'committee') {
    die("คุณไม่มีสิทธิ์เข้าถึงหน้านี้");
}

// ตรวจสอบว่ามีการส่ง user_id และ scholarship_id มาหรือไม่
if (isset($_GET['user_id']) && isset($_GET['scholarship_id'])) {
    $user_id = intval($_GET['user_id']);
    $scholarship_id = intval($_GET['scholarship_id']);

    // ตรวจสอบค่าที่ได้รับ
    if ($user_id === 0 || $scholarship_id === 0) {
        die("ค่าที่ส่งมาผิดพลาด");
    }
} else {
    die("ไม่พบ user_id หรือ scholarship_id");
}

// ตรวจสอบว่ามีข้อมูลในฐานข้อมูลหรือไม่
$query_check = "SELECT * FROM scholarship_applications WHERE user_id = ? AND scholarship_id = ?";
$stmt = $conn->prepare($query_check);
$stmt->bind_param("ii", $user_id, $scholarship_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูลที่เกี่ยวข้องในฐานข้อมูล");
}

// ดึงข้อมูลความคิดเห็นปัจจุบัน (ถ้ามี)
$application = $result->fetch_assoc();
$current_comment = $application['committee_comment'];

// ตรวจสอบการส่งข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['committee_comment'])) {
    $committee_comment = $_POST['committee_comment'];

    // อัปเดตความคิดเห็นของกรรมการในตาราง scholarship_applications
    $query_update = "UPDATE scholarship_applications
                     SET committee_comment = ?
                     WHERE user_id = ? AND scholarship_id = ?";
    $stmt = $conn->prepare($query_update);
    $stmt->bind_param("sii", $committee_comment, $user_id, $scholarship_id);

    if ($stmt->execute()) {
        $success_message = "บันทึกความคิดเห็นสำเร็จ";
    } else {
        $error_message = "เกิดข้อผิดพลาดในการบันทึกความคิดเห็น";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แสดงความคิดเห็น</title>
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
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .content-wrapper h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }
        .btn-submit {
            display: block;
            width: 100%;
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            border: none;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .message.success {
            color: #28a745;
        }
        .message.error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>แสดงความคิดเห็น</h2>
    </div>

    <!-- Content Section -->
    <div class="content-wrapper">
        <h3>แสดงความคิดเห็น</h3>
        <?php if (isset($success_message)): ?>
            <div class="message success"><?= $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="message error"><?= $error_message; ?></div>
        <?php endif; ?>
        <form action="committee_comment.php?user_id=<?= $user_id; ?>&scholarship_id=<?= $scholarship_id; ?>" method="post">
            <div class="form-group">
                <label for="committee_comment">ความคิดเห็น:</label>
                <textarea name="committee_comment" id="committee_comment" rows="5" required><?= htmlspecialchars($current_comment ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn-submit">บันทึกความคิดเห็น</button>
        </form>
    </div>
</body>
</html>