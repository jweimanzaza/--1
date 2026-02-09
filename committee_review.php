<!-- filepath: c:\xampp\htdocs\register\committee_review.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าเป็น Committee หรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'committee') {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการส่ง approve_id หรือ reject_id มาหรือไม่
if (isset($_GET['approve_id']) || isset($_GET['reject_id'])) {
    $application_id = isset($_GET['approve_id']) ? intval($_GET['approve_id']) : intval($_GET['reject_id']);
    $status = 'รอประกาศผล';
    $review_status = isset($_GET['review_status']) ? $_GET['review_status'] : null;

    // อัปเดต status และ review_status พร้อมกัน
    $stmt = $conn->prepare("UPDATE scholarship_applications SET status = ?, review_status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $review_status, $application_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "อัปเดตสถานะสำเร็จ";
        header("Location: committee_review.php");
        exit();
    } else {
        $_SESSION['errors'] = ["เกิดข้อผิดพลาดในการอัปเดตสถานะ"];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['review_status']) && isset($_POST['application_id'])) {
        $review_status = $_POST['review_status'];
        $application_id = $_POST['application_id'];
        // ...โค้ดอัปเดตฐานข้อมูล...
    }
}

// ส่วนนี้เพิ่มเข้าไป
if (isset($_POST['edit_save_score'])) {
    $user_id = intval($_POST['user_id']);
    $scholarship_id = intval($_POST['scholarship_id']);
    $committee_id = $_SESSION['user_id'];
    $new_score = floatval($_POST['new_score']);

    // ลบคะแนนเดิม
    $stmt = $conn->prepare("DELETE FROM review_scores WHERE user_id=? AND scholarship_id=? AND committee_id=?");
    $stmt->bind_param("iii", $user_id, $scholarship_id, $committee_id);
    $stmt->execute();

    // เพิ่มคะแนนใหม่
    $stmt = $conn->prepare("INSERT INTO review_scores (user_id, scholarship_id, committee_id, score, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiid", $user_id, $scholarship_id, $committee_id, $new_score);
    $stmt->execute();

    $_SESSION['success'] = "แก้ไขคะแนนสำเร็จ";
    header("Location: committee_review.php");
    exit();
}

// อัปเดตสถานะเป็น "ได้รับทุนการศึกษา" หรือ "ไม่ได้รับทุนการศึกษา" เมื่อถึงวันที่สิ้นสุด
$current_date = date('Y-m-d H:i:s');
$query_update_status = "
    UPDATE scholarship_applications a
    JOIN scholarships s ON a.scholarship_id = s.id
    SET a.status = CASE
        WHEN a.status = 'รอประกาศผล' AND a.id IN (
            SELECT id FROM scholarship_applications WHERE status = 'รอประกาศผล'
        ) THEN 'ได้รับทุนการศึกษา'
        ELSE 'ไม่ได้รับทุนการศึกษา'
    END
    WHERE s.closing_date <= ? AND a.status = 'รอประกาศผล';
";
$stmt = $conn->prepare($query_update_status);
$stmt->bind_param("s", $current_date);
$stmt->execute();

// ดึงข้อมูลคำขอทุนพร้อมคะแนนเฉลี่ย
$query = "
    SELECT a.id AS application_id, a.status, s.name AS scholarship_name, 
           u.id AS user_id, u.username AS applicant_name, u.email AS applicant_email, 
           a.scholarship_id,
           a.pdf_file,
           COALESCE(AVG(r.score), 0) AS average_score,
           sp.first_name, sp.last_name
    FROM scholarship_applications a
    JOIN scholarships s ON a.scholarship_id = s.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN student_profiles sp ON u.id = sp.user_id
    LEFT JOIN review_scores r ON r.scholarship_id = a.scholarship_id AND r.user_id = u.id
    WHERE a.status = 'รอพิจารณาจากคณะ'
    GROUP BY a.id, u.id, s.id
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิจารณาทุนการศึกษา</title>
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
        .review-title {
            text-align: center;
            color: #2563eb;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 36px 0 24px 0;
            letter-spacing: 1px;
        }
        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto 36px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
            padding: 32px 28px 28px 28px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: none;
            min-width: 1200px;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
        }
        th {
            background: #2196f3;
            color: #fff;
            font-weight: 700;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        tr:hover {
            background: #e0e7ff;
        }
        .btn, .btn-approve, .btn-reject, .btn-download, .btn-primary, .btn-secondary {
            border: none;
            border-radius: 7px;
            padding: 8px 18px;
            font-size: 1rem;
            font-weight: 600;
            margin-right: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.18s, box-shadow 0.18s;
        }
        .btn-approve {
            background: #22c55e;
            color: #fff;
        }
        .btn-approve:hover {
            background: #16a34a;
        }
        .btn-reject {
            background: #ef4444;
            color: #fff;
        }
        .btn-reject:hover {
            background: #c82333;
        }
        .btn-primary {
            background: #2196f3;
            color: #fff;
        }
        .btn-primary:hover {
            background: #1565c0;
        }
        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }
        .btn-secondary:hover {
            background: #495057;
        }
        .btn-download {
            background: #2196f3;
            color: #fff;
        }
        .btn-download:hover {
            background: #1565c0;
        }
        .edit-score-form {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 8px;
        }
        .edit-score-form input[type="number"] {
            width: 80px;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .btn-edit-score {
            background: none;
            color: #2196f3;
            border: none;
            padding: 8px 18px;
            font-size: 1em;
            border-radius: 5px;
            margin: 2px 4px 2px 0;
            cursor: pointer;
            transition: color 0.2s;
        }
        .btn-edit-score:hover {
            color: #1565c0;
            text-decoration: underline;
            background: none;
        }
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 600;
        }
        .message.error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #ef4444;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 600;
        }
        @media (max-width: 900px) {
            .content-wrapper { padding: 12px 2vw 12px 2vw; }
            table { min-width: 900px; }
        }
        @media (max-width: 600px) {
            .navbar { flex-direction: column; padding: 0 10px; }
            .review-title { font-size: 1.1rem; }
            .content-wrapper { padding: 8px 1vw 8px 1vw; }
            table { min-width: 600px; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="150px-SDU2016.png" alt="SDU Logo" style="height:44px; margin-right:12px;">
        <div class="navbar-links">
            <a href="committee_dashboard.php"><i class="fa fa-dashboard"></i> หน้าหลัก</a>
            <a href="committee_review.php" class="active"><i class="fa fa-file-text"></i> พิจารณาทุนการศึกษา</a>
        </div>
        <div class="navbar-actions">
            <a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="review-title"><i class="fa fa-file-text"></i> พิจารณาทุนการศึกษา</div>
    <div class="content-wrapper">
        <h3 style="color:#2563eb;">รายการคำขอทุนที่รอการพิจารณา</h3>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['errors'])): ?>
            <div class="message error">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p><?= $error; ?></p>
                <?php endforeach; unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ชื่อผู้สมัคร</th>
                    <th>อีเมล</th>
                    <th>ชื่อทุนการศึกษา</th>
                    <th>คะแนนเฉลี่ย</th>
                    <th>ให้คะแนน</th>
                    <th>การจัดการ</th>
                    <th>ไฟล์แนบ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($row['applicant_email']); ?></td>
                        <td><?= htmlspecialchars($row['scholarship_name']); ?></td>
                        <td><?= number_format($row['average_score'], 2); ?></td>
                        <td style="display:flex; flex-direction:column; align-items:flex-start;">
                            <?php
                            if (isset($_SESSION['user_id'])) {
                                $committee_id = $_SESSION['user_id'];
                                $user_id = $row['user_id'];
                                $scholarship_id = $row['scholarship_id'];
                                $score_stmt = $conn->prepare("SELECT id, score FROM review_scores WHERE user_id=? AND scholarship_id=? AND committee_id=? LIMIT 1");
                                $score_stmt->bind_param("iii", $user_id, $scholarship_id, $committee_id);
                                $score_stmt->execute();
                                $score_result = $score_stmt->get_result();
                                $score_row = $score_result->fetch_assoc();

                                if ($score_row): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="edit_score_id" value="<?= $score_row['id'] ?>">
                                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                        <input type="hidden" name="scholarship_id" value="<?= $scholarship_id ?>">
                                        <button type="submit" name="edit_score_btn" class="btn-edit-score">แก้ไขคะแนน</button>
                                    </form>
                                    <?php if (
                                        isset($_POST['edit_score_btn']) &&
                                        $_POST['edit_score_id'] == $score_row['id'] &&
                                        $_POST['user_id'] == $user_id &&
                                        $_POST['scholarship_id'] == $scholarship_id
                                    ): ?>
                                        <form method="post" class="edit-score-form">
                                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                            <input type="hidden" name="scholarship_id" value="<?= $scholarship_id ?>">
                                            <input type="number" name="new_score" step="0.01" min="0" max="100" value="<?= $score_row['score'] ?>" required>
                                            <button type="submit" name="edit_save_score" class="btn btn-primary">บันทึก</button>
                                        </form>
                                    <?php endif;
                                else: ?>
                                    <form action="submit_score.php" method="POST" class="edit-score-form">
                                        <input type="hidden" name="user_id" value="<?= $user_id; ?>">
                                        <input type="hidden" name="scholarship_id" value="<?= $scholarship_id; ?>">
                                        <label for="score" style="margin-right:4px;">คะแนน:</label>
                                        <input type="number" name="score" step="0.01" min="0" max="100" required>
                                        <button type="submit" class="btn btn-primary">บันทึก</button>
                                    </form>
                                <?php endif;
                            } else {
                                echo '<span class="text-danger">ไม่พบข้อมูลกรรมการ</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                <a href="committee_review.php?approve_id=<?= $row['application_id']; ?>&review_status=อนุมัติ" class="btn-approve">อนุมัติ</a>
                                <a href="committee_review.php?reject_id=<?= $row['application_id']; ?>&review_status=ไม่อนุมัติ" class="btn-reject">ปฏิเสธ</a>
                                <?php if (!empty($row['user_id']) && !empty($row['scholarship_id'])): ?>
                                    <a href="committee_comment.php?user_id=<?= htmlspecialchars($row['user_id']); ?>&scholarship_id=<?= htmlspecialchars($row['scholarship_id']); ?>" class="btn-primary">ไปยังหน้าแสดงความคิดเห็น</a>
                                <?php else: ?>
                                    <span class="text-danger">ข้อมูลไม่ครบถ้วน</span>
                                <?php endif; ?>
                                <a href="view_user_profile.php?user_id=<?= $row['user_id']; ?>" class="btn-secondary">แสดงข้อมูลส่วนตัว</a>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($row['pdf_file'])): ?>
                                <a href="uploads/<?= htmlspecialchars($row['pdf_file']); ?>" target="_blank" class="btn-download">ดาวน์โหลดไฟล์ PDF</a>
                            <?php else: ?>
                                <span style="color: #888;">ไม่มีไฟล์แนบ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>