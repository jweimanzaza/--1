<!-- filepath: c:\xampp\htdocs\register\manage_applications.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าเป็น Admin หรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// อัปเดตสถานะคำขอทุน
if (isset($_GET['approve_id'])) {
    $application_id = $_GET['approve_id'];

    // อัปเดตสถานะเป็น "รอพิจารณาจากคณะ"
    $query = "UPDATE scholarship_applications SET status = 'รอพิจารณาจากคณะ' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $application_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "อัปเดตสถานะสำเร็จ";
        header("Location: manage_applications.php");
        exit();
    } else {
        $_SESSION['errors'] = ["เกิดข้อผิดพลาดในการอัปเดตสถานะ: " . $stmt->error];
    }
}

if (isset($_GET['reject_id'])) {
    $application_id = $_GET['reject_id'];

    // อัปเดตสถานะเป็น "ปฏิเสธ"
    $query = "UPDATE scholarship_applications SET status = 'ปฏิเสธ' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $application_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "อัปเดตสถานะสำเร็จ";
        header("Location: manage_applications.php");
        exit();
    } else {
        $_SESSION['errors'] = ["เกิดข้อผิดพลาดในการอัปเดตสถานะ: " . $stmt->error];
    }
}

// ดึงข้อมูลคำขอทุนทั้งหมด
$where = '';
if (isset($_GET['year']) && $_GET['year'] != '') {
    $year = intval($_GET['year']);
    $where = "WHERE YEAR(s.opening_date) = $year";
}

$query = "SELECT a.id AS application_id, a.status, a.final_status, s.name AS scholarship_name, 
                 u.id AS user_id, u.username AS applicant_name, u.email AS applicant_email,
                 sp.first_name, sp.last_name,
                 a.scholarship_id, a.committee_comment, a.pdf_file
          FROM scholarship_applications a
          JOIN scholarships s ON a.scholarship_id = s.id
          JOIN users u ON a.user_id = u.id
          LEFT JOIN student_profiles sp ON u.id = sp.user_id
          $where";
$result = $conn->query($query);

// แยกข้อมูลตามสถานะ
$applications = [
    'รอดำเนินการ' => [],
    'รอพิจารณาจากคณะ' => [],
    'ปฏิเสธ' => [],
    'ได้รับทุนการศึกษา' => [],
    'ไม่ได้รับทุนการศึกษา' => [],
];

while ($row = $result->fetch_assoc()) {
    if ($row['final_status'] === 'ได้รับทุนการศึกษา') {
        $applications['ได้รับทุนการศึกษา'][] = $row;
    } elseif ($row['final_status'] === 'ไม่ได้รับทุนการศึกษา') {
        $applications['ไม่ได้รับทุนการศึกษา'][] = $row;
    } elseif (isset($applications[$row['status']])) {
        $applications[$row['status']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำขอทุนการศึกษา</title>
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
        .header {
            background: none;
            color: #2563eb;
            padding: 32px 0 0 0;
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .content-wrapper {
            padding: 0 0 36px 0;
        }
        .content {
            max-width: 1300px;
            margin: 0 auto;
            background: #fff;
            padding: 32px 28px 28px 28px;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
        }
        .content h3 {
            margin-bottom: 20px;
            color: #2563eb;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 32px 0 12px 0;
            color: #2563eb;
        }
        table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
            margin-bottom: 28px;
            background: none;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
        }
        th {
            background: #e0e7ff;
            font-weight: 700;
            color: #2563eb;
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
            background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
            color: #fff;
        }
        .btn-approve:hover {
            background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
        }
        .btn-reject {
            background: linear-gradient(90deg, #ef4444 0%, #f87171 100%);
            color: #fff;
        }
        .btn-reject:hover {
            background: linear-gradient(90deg, #f87171 0%, #ef4444 100%);
        }
        .btn-download {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
        }
        .btn-download:hover {
            background: linear-gradient(90deg, #60a5fa 0%, #2563eb 100%);
        }
        .btn-primary {
            background: #ffb300;
            color: #fff;
        }
        .btn-primary:hover {
            background: #ff9800;
        }
        .btn-secondary {
            background: #38bdf8;
            color: #fff;
        }
        .btn-secondary:hover {
            background: #0ea5e9;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 600;
        }
        .error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #ef4444;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 600;
        }
        .text-muted {
            color: #888;
            font-size: 0.98rem;
        }
        .search-box {
            background: #fafbfc;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 24px 32px 20px 32px;
            margin: 0 auto 30px auto;
            max-width: 400px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .search-box label {
            font-weight: bold;
            margin-bottom: 10px;
            color: #222;
        }
        .search-box select {
            padding: 6px 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            margin-bottom: 16px;
            width: 100%;
            box-sizing: border-box;
        }
        .search-box button {
            width: 100%;
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 0;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .search-box button:hover {
            background: #0056b3;
        }
        @media (max-width: 1200px) {
            .content { padding: 12px 2vw 12px 2vw; }
            table { min-width: 900px; }
        }
        @media (max-width: 900px) {
            .content { padding: 8px 1vw 8px 1vw; }
            table { min-width: 600px; }
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
            .btn-logout {
                margin: 10px 0 0 0;
                width: 100%;
            }
            .content { padding: 4px 0 4px 0; }
            table, th, td { font-size: 0.98rem; }
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
            <a href="manage_applications.php"><i class="fa fa-file-alt"></i> จัดการใบสมัคร</a>
        </div>
        <div class="navbar-actions">
            <a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="header">จัดการคำขอสมัครทุนการศึกษา</div>
    <div class="content-wrapper">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors'])): ?>
            <div class="error">
                <?= implode('<br>', $_SESSION['errors']); unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

        <div class="content">
            <h3>รายการคำขอสมัครทุนการศึกษา</h3>
            <a href="export_applications.php" class="btn btn-download" style="margin: 20px 0; display: inline-block;">Export คะแนนเป็น Excel</a>
            <form method="GET" action="" class="search-box">
                <label for="year">ค้นหาทุนตามปีที่เปิด:</label>
                <select name="year" id="year">
                    <option value="">-- เลือกปี --</option>
                    <?php
                    // ดึงปีที่มีใน opening_date จาก scholarships
                    $years = [];
                    $year_query = "SELECT DISTINCT YEAR(opening_date) AS year FROM scholarships ORDER BY year DESC";
                    $year_result = $conn->query($year_query);
                    while ($row = $year_result->fetch_assoc()) {
                        $years[] = $row['year'];
                    }
                    foreach ($years as $y) {
                        $selected = (isset($_GET['year']) && $_GET['year'] == $y) ? 'selected' : '';
                        echo "<option value=\"$y\" $selected>$y</option>";
                    }
                    ?>
                </select>
                <button type="submit">ค้นหา</button>
            </form>

            <!-- ตาราง: รอดำเนินการ -->
            <div class="section-title" style="color:#2563eb;">รอดำเนินการ</div>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อผู้สมัคร</th>
                        <th>อีเมล</th>
                        <th>ชื่อทุนการศึกษา</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications['รอดำเนินการ'] as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></td>
                            <td><?= htmlspecialchars($row['applicant_email']); ?></td>
                            <td><?= htmlspecialchars($row['scholarship_name']); ?></td>
                            <td>
                                <a href="manage_applications.php?approve_id=<?= $row['application_id']; ?>" class="btn-approve">อนุมัติ</a>
                                <a href="manage_applications.php?reject_id=<?= $row['application_id']; ?>" class="btn-reject">ปฏิเสธ</a>
                                <?php if (!empty($row['user_id']) && !empty($row['scholarship_id'])): ?>
                                    <a href="view_comment.php?user_id=<?= htmlspecialchars($row['user_id']); ?>&scholarship_id=<?= htmlspecialchars($row['scholarship_id']); ?>" class="btn-primary">ดูความคิดเห็น</a>
                                <?php else: ?>
                                    <span class="text-muted">ยังไม่มีความคิดเห็น</span>
                                <?php endif; ?>
                                <a href="view_profile.php?user_id=<?= htmlspecialchars($row['user_id']); ?>" class="btn-secondary">ดูโปรไฟล์</a>
                                <?php if (!empty($row['pdf_file'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['pdf_file']); ?>" target="_blank" class="btn-download">ดาวน์โหลดไฟล์ PDF</a>
                                <?php else: ?>
                                    <span class="text-muted">ไม่มีไฟล์แนบ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- ตาราง: รอพิจารณาจากคณะ -->
            <div class="section-title" style="color:orange;">รอพิจารณาจากคณะ</div>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อผู้สมัคร</th>
                        <th>อีเมล</th>
                        <th>ชื่อทุนการศึกษา</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications['รอพิจารณาจากคณะ'] as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></td>
                            <td><?= htmlspecialchars($row['applicant_email']); ?></td>
                            <td><?= htmlspecialchars($row['scholarship_name']); ?></td>
                            <td>
                                <?php if (!empty($row['user_id']) && !empty($row['scholarship_id'])): ?>
                                    <a href="view_comment.php?user_id=<?= htmlspecialchars($row['user_id']); ?>&scholarship_id=<?= htmlspecialchars($row['scholarship_id']); ?>" class="btn-primary">ดูความคิดเห็น</a>
                                <?php else: ?>
                                    <span class="text-muted">ยังไม่มีความคิดเห็น</span>
                                <?php endif; ?>
                                <a href="view_profile.php?user_id=<?= htmlspecialchars($row['user_id']); ?>" class="btn-secondary">ดูโปรไฟล์</a>
                                <?php if (!empty($row['pdf_file'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['pdf_file']); ?>" target="_blank" class="btn-download">ดาวน์โหลดไฟล์ PDF</a>
                                <?php else: ?>
                                    <span class="text-muted">ไม่มีไฟล์แนบ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- ตาราง: ปฏิเสธ -->
            <div class="section-title" style="color:#ef4444;">ปฏิเสธ</div>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อผู้สมัคร</th>
                        <th>อีเมล</th>
                        <th>ชื่อทุนการศึกษา</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications['ปฏิเสธ'] as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></td>
                            <td><?= htmlspecialchars($row['applicant_email']); ?></td>
                            <td><?= htmlspecialchars($row['scholarship_name']); ?></td>
                            <td>
                                <?php if (!empty($row['user_id']) && !empty($row['scholarship_id'])): ?>
                                    <a href="view_comment.php?user_id=<?= htmlspecialchars($row['user_id']); ?>&scholarship_id=<?= htmlspecialchars($row['scholarship_id']); ?>" class="btn-primary">ดูความคิดเห็น</a>
                                <?php else: ?>
                                    <span class="text-muted">ยังไม่มีความคิดเห็น</span>
                                <?php endif; ?>
                                <a href="view_profile.php?user_id=<?= htmlspecialchars($row['user_id']); ?>" class="btn-secondary">ดูโปรไฟล์</a>
                                <?php if (!empty($row['pdf_file'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['pdf_file']); ?>" target="_blank" class="btn-download">ดาวน์โหลดไฟล์ PDF</a>
                                <?php else: ?>
                                    <span class="text-muted">ไม่มีไฟล์แนบ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- ตาราง: ได้รับทุนการศึกษา -->
            <div class="section-title" style="color:#22c55e;">ได้รับทุนการศึกษา</div>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อผู้สมัคร</th>
                        <th>อีเมล</th>
                        <th>ชื่อทุนการศึกษา</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications['ได้รับทุนการศึกษา'] as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></td>
                            <td><?= htmlspecialchars($row['applicant_email']); ?></td>
                            <td><?= htmlspecialchars($row['scholarship_name']); ?></td>
                            <td>
                                <?php if (!empty($row['user_id']) && !empty($row['scholarship_id'])): ?>
                                    <a href="view_comment.php?user_id=<?= htmlspecialchars($row['user_id']); ?>&scholarship_id=<?= htmlspecialchars($row['scholarship_id']); ?>" class="btn-primary">ดูความคิดเห็น</a>
                                <?php else: ?>
                                    <span class="text-muted">ยังไม่มีความคิดเห็น</span>
                                <?php endif; ?>
                                <a href="view_profile.php?user_id=<?= htmlspecialchars($row['user_id']); ?>" class="btn-secondary">ดูโปรไฟล์</a>
                                <?php if (!empty($row['pdf_file'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['pdf_file']); ?>" target="_blank" class="btn-download">ดาวน์โหลดไฟล์ PDF</a>
                                <?php else: ?>
                                    <span class="text-muted">ไม่มีไฟล์แนบ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- ตาราง: ไม่ได้รับทุนการศึกษา -->
            <div class="section-title" style="color:#b22222;">ไม่ได้รับทุนการศึกษา</div>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อผู้สมัคร</th>
                        <th>อีเมล</th>
                        <th>ชื่อทุนการศึกษา</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications['ไม่ได้รับทุนการศึกษา'] as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></td>
                            <td><?= htmlspecialchars($row['applicant_email']); ?></td>
                            <td><?= htmlspecialchars($row['scholarship_name']); ?></td>
                            <td>
                                <?php if (!empty($row['user_id']) && !empty($row['scholarship_id'])): ?>
                                    <a href="view_comment.php?user_id=<?= htmlspecialchars($row['user_id']); ?>&scholarship_id=<?= htmlspecialchars($row['scholarship_id']); ?>" class="btn-primary">ดูความคิดเห็น</a>
                                <?php else: ?>
                                    <span class="text-muted">ยังไม่มีความคิดเห็น</span>
                                <?php endif; ?>
                                <a href="view_profile.php?user_id=<?= htmlspecialchars($row['user_id']); ?>" class="btn-secondary">ดูโปรไฟล์</a>
                                <?php if (!empty($row['pdf_file'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['pdf_file']); ?>" target="_blank" class="btn-download">ดาวน์โหลดไฟล์ PDF</a>
                                <?php else: ?>
                                    <span class="text-muted">ไม่มีไฟล์แนบ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>