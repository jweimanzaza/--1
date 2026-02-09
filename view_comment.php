<!-- filepath: c:\xampp\htdocs\register\view_comment.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่ามีการส่ง user_id และ scholarship_id มาหรือไม่
if (isset($_GET['user_id']) && isset($_GET['scholarship_id'])) {
    $user_id = intval($_GET['user_id']);
    $scholarship_id = intval($_GET['scholarship_id']);
    if ($user_id === 0 || $scholarship_id === 0) {
        die("ค่าที่ส่งมาไม่ถูกต้อง");
    }
} else {
    die("ไม่พบ user_id หรือ scholarship_id");
}

// ดึงความคิดเห็นของกรรมการ
$query_comment = "SELECT committee_comment 
                  FROM scholarship_applications 
                  WHERE user_id = ? AND scholarship_id = ?";
$stmt = $conn->prepare($query_comment);
$stmt->bind_param("ii", $user_id, $scholarship_id);
$stmt->execute();
$result_comment = $stmt->get_result();

if ($result_comment->num_rows > 0) {
    $row_comment = $result_comment->fetch_assoc();
    $committee_comment = $row_comment['committee_comment'];
} else {
    $committee_comment = "ไม่มีความคิดเห็น";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ความคิดเห็นของกรรมการ</title>
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
        .comment-container {
            max-width: 600px;
            margin: 36px auto 0 auto;
            background: #fff;
            padding: 36px 32px 32px 32px;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
        }
        .comment-title {
            color: #2563eb;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .comment-content {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 24px 18px 18px 18px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.06);
            color: #222;
            font-size: 1.08rem;
            text-align: center;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .no-comment {
            color: #ef4444;
            text-align: center;
            font-size: 1.08rem;
            margin-top: 24px;
        }
        .btn-back {
            display: inline-block;
            margin-top: 18px;
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
            .comment-container { padding: 14px 2vw 14px 2vw; }
            .comment-title { font-size: 1.1rem; }
            .btn-back { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-links">
            <a href="admin_dashboard.php"><i class="fa fa-home"></i> หน้าหลัก</a>
            <a href="manage_users.php"><i class="fa fa-users"></i> จัดการผู้ใช้</a>
            <a href="manage_scholarships.php"><i class="fa fa-graduation-cap"></i> จัดการทุนการศึกษา</a>
            <a href="manage_applications.php"><i class="fa fa-file-alt"></i> จัดการใบสมัคร</a>
        </div>
        <div class="navbar-actions">
            <a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="comment-container">
        <div class="comment-title"><i class="fa fa-comments"></i> ความคิดเห็นของกรรมการ</div>
        <div class="comment-content">
            <?= htmlspecialchars($committee_comment); ?>
        </div>
        <a href="manage_applications.php" class="btn-back"><i class="fa fa-arrow-left"></i> ย้อนกลับ</a>
    </div>
</body>
</html>