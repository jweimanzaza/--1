<!-- filepath: c:\xampp\htdocs\register\manage_scholarships.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าผู้ดูแลระบบเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// เพิ่มทุนการศึกษา
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_scholarship'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $opening_date = $_POST['opening_date'];
    $closing_date = $_POST['closing_date'];
    $status = 'เปิดรับสมัคร';

    $stmt = $conn->prepare("INSERT INTO scholarships (name, description, opening_date, closing_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $description, $opening_date, $closing_date, $status);
    $stmt->execute();
    header("Location: manage_scholarships.php");
    exit();
}

// ปิดทุนการศึกษา
if (isset($_GET['close_id'])) {
    $id = intval($_GET['close_id']);
    $stmt = $conn->prepare("UPDATE scholarships SET status = 'ปิดรับสมัคร' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_scholarships.php");
    exit();
}

// อนุมัติทุนการศึกษา
if (isset($_GET['approve_id'])) {
    $application_id = $_GET['approve_id'];

    // อัปเดตสถานะเป็น "รอพิจารณาจากคณะ"
    $query = "UPDATE scholarship_applications SET status = 'รอพิจารณาจากคณะ' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $application_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "อัปเดตสถานะสำเร็จ";
        header("Location: manage_scholarships.php");
        exit();
    } else {
        $_SESSION['errors'] = ["เกิดข้อผิดพลาดในการอัปเดตสถานะ"];
    }
}

// ลบทุนการศึกษา
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM scholarships WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_scholarships.php");
        exit();
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
}

// อัปเดตทุนการศึกษา
if (isset($_POST['update_btn'])) {
    $id = intval($_POST['update_id']);
    $opening = $_POST['opening_date'];
    $closing = $_POST['closing_date'];

    // เช็คสถานะใหม่
    $now = date('Y-m-d H:i:s');
    if ($now >= $opening && $now <= $closing) {
        $status = 'เปิดรับสมัคร';
    } else {
        $status = 'ปิดรับสมัคร';
    }

    $stmt = $conn->prepare("UPDATE scholarships SET opening_date=?, closing_date=?, status=? WHERE id=?");
    $stmt->bind_param("sssi", $opening, $closing, $status, $id);
    $stmt->execute();
    header("Location: manage_scholarships.php");
    exit();
}

// ดึงข้อมูลทุนการศึกษาจากฐานข้อมูล
$query_scholarships = "SELECT * FROM scholarships";
$result_scholarships = $conn->query($query_scholarships);

// ดึงข้อมูลการสมัครทุนการศึกษาจากฐานข้อมูล
$query_applications = "SELECT a.id AS application_id, a.status, s.name AS scholarship_name, 
                              u.id AS user_id, u.username AS applicant_name, u.email AS applicant_email
                       FROM scholarship_applications a
                       JOIN scholarships s ON a.scholarship_id = s.id
                       JOIN users u ON a.user_id = u.id";
$result_applications = $conn->query($query_applications);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการทุนการศึกษา</title>
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
        .scholarships-main-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 32px 0 18px 0;
            color: #222;
        }
        .scholarship-form-box {
            max-width: 520px;
            margin: 0 auto 32px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(37,99,235,0.07);
            padding: 32px 28px 24px 28px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .scholarship-form-box input[type="text"],
        .scholarship-form-box textarea,
        .scholarship-form-box input[type="datetime-local"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1.5px solid #e0e7ff;
            font-size: 1rem;
            background: #f8fafc;
            margin-bottom: 0;
            transition: border 0.18s;
        }
        .scholarship-form-box input:focus,
        .scholarship-form-box textarea:focus {
            border: 2px solid #2563eb;
            outline: none;
        }
        .scholarship-form-box textarea {
            min-height: 60px;
            resize: vertical;
        }
        .scholarship-form-box .btn-add {
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 12px 0;
            font-size: 1.08rem;
            font-weight: 600;
            margin-top: 10px;
            cursor: pointer;
            width: 100%;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
        }
        .scholarship-form-box .btn-add:hover {
            background: #0056b3;
        }
        .scholarships-table-container {
            max-width: 1400px;
            margin: 0 auto 36px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(37,99,235,0.08);
            padding: 24px 18px 18px 18px;
        }
        table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
            background: none;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
        }
        th {
            background: #f2f2f2;
            font-weight: 700;
            color: #222;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        tr:hover {
            background: #e0e7ff;
        }
        .btn-edit, .btn-view, .btn-close, .btn-delete, .btn-edit-time, .btn-save {
            border: none;
            border-radius: 5px;
            padding: 7px 14px;
            font-size: 15px;
            font-weight: 600;
            margin-right: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.18s;
        }
        .btn-edit { background: #ffb300; color: #fff; }
        .btn-edit:hover { background: #ff9800; }
        .btn-view { background: #2563eb; color: #fff; }
        .btn-view:hover { background: #1d4ed8; }
        .btn-close { background: #ef4444; color: #fff; }
        .btn-close:hover { background: #d32f2f; }
        .btn-delete { background: #ff4d4f; color: #fff; }
        .btn-delete:hover { background: #d9363e; }
        .btn-edit-time { background: #17a2b8; color: #fff; }
        .btn-edit-time:hover { background: #138496; }
        .btn-save { background: #007bff; color: #fff; }
        .btn-save:hover { background: #0056b3; }
        .action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }
        .text-muted {
            color: #888;
            font-size: 13px;
            padding: 0 6px;
        }
        @media (max-width: 700px) {
            .scholarship-form-box { padding: 12px 2vw 12px 2vw; }
            .scholarships-table-container { padding: 8px 2vw 8px 2vw; }
            th, td { padding: 8px 4px; font-size: 0.98rem; }
            .action-group { flex-direction: column; align-items: stretch; }
            .btn-edit, .btn-view, .btn-close, .btn-delete, .btn-edit-time, .btn-save { width: 100%; margin-bottom: 4px; }
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
    <div class="scholarships-main-title">รายการทุนการศึกษา</div>
    <form method="POST" class="scholarship-form-box">
        <input type="text" name="name" placeholder="ชื่อทุนการศึกษา" required>
        <textarea name="description" placeholder="คำอธิบาย" required></textarea>
        <input type="datetime-local" name="opening_date" required>
        <input type="datetime-local" name="closing_date" required>
        <button type="submit" name="add_scholarship" class="btn-add">เพิ่มทุนการศึกษา</button>
    </form>
    <div class="scholarships-table-container">
        <div class="scholarships-table-title">รายการทุนทั้งหมด</div>
        <table>
            <thead>
                <tr>
                    <th>ชื่อทุนการศึกษา</th>
                    <th>คำอธิบาย</th>
                    <th>สถานะ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_scholarships->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['description']); ?></td>
                        <td><?= htmlspecialchars(isset($row['status']) ? $row['status'] : 'ไม่ระบุ'); ?></td>
                        <td>
                            <div class="action-group" style="gap:12px;">
                                <a href="scholarship-details.php?id=<?= htmlspecialchars($row['id']); ?>" 
                                   class="btn-view"
                                   style="background:linear-gradient(90deg,#2563eb 0%,#60a5fa 100%);color:#fff;font-weight:700;padding:10px 28px;border-radius:8px;font-size:1rem;box-shadow:0 2px 8px rgba(37,99,235,0.10);border:none;transition:background 0.18s;">
                                    ดูรายละเอียด
                                </a>
                                <?php if ($row['status'] === 'เปิดรับสมัคร'): ?>
                                    <span class="text-muted" style="color:#64748b;font-weight:600;font-size:1rem;align-self:center;">ปิดแล้ว</span>
                                <?php else: ?>
                                    <span class="text-muted" style="color:#64748b;font-weight:600;font-size:1rem;align-self:center;">ปิดแล้ว</span>
                                <?php endif; ?>
                                <a href="manage_scholarships.php?delete_id=<?= htmlspecialchars($row['id']); ?>"
                                   class="btn-delete"
                                   style="background:#ef4444;color:#fff;font-weight:700;padding:10px 28px;border-radius:8px;font-size:1rem;box-shadow:0 2px 8px rgba(239,68,68,0.10);border:none;transition:background 0.18s;display:inline-block;">
                                    ลบทุน
                                </a>
                                <button type="submit" form="edit-time-<?= $row['id'] ?>" name="edit_btn"
                                    class="btn-edit-time"
                                    style="background:linear-gradient(90deg,#17a2b8 0%,#38bdf8 100%);color:#fff;font-weight:700;padding:10px 28px;border-radius:8px;font-size:1rem;box-shadow:0 2px 8px rgba(23,162,184,0.10);border:none;transition:background 0.18s;display:inline-block;">
                                    แก้ไขวันเวลา
                                </button>
                                <form id="edit-time-<?= $row['id'] ?>" method="post" style="display:none;">
                                    <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php if (isset($_POST['edit_btn']) && $_POST['edit_id'] == $row['id']): ?>
                    <tr style="border:none;background:none;">
    <td colspan="4" style="background:none;padding:0;border:none;">
        <form method="post" style="display:flex;gap:12px;align-items:center;justify-content:center;width:100%;background:none;border:none;box-shadow:none;outline:none;margin:0;">
            <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
            <label style="font-weight:bold;">วันเปิด:
                <input type="datetime-local" name="opening_date" value="<?= date('Y-m-d\TH:i', strtotime($row['opening_date'])) ?>" style="margin-left:8px;">
            </label>
            <label style="font-weight:bold;">วันปิด:
                <input type="datetime-local" name="closing_date" value="<?= date('Y-m-d\TH:i', strtotime($row['closing_date'])) ?>" style="margin-left:8px;">
            </label>
            <button type="submit" name="update_btn" class="btn-save" style="margin-left:12px;">บันทึก</button>
        </form>
    </td>
</tr>
                    <?php endif; ?>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>