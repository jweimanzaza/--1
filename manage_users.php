<!-- filepath: c:\xampp\htdocs\register\manage_users.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าผู้ดูแลระบบเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ลบผู้ใช้
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_users.php");
    exit();
}

// เปลี่ยนสิทธิ์ผู้ใช้
if (isset($_GET['change_role_id'])) {
    $id = $_GET['change_role_id'];
    $new_role = $_GET['new_role'];
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $id);
    $stmt->execute();
    header("Location: manage_users.php");
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$query = "SELECT id, username, email, role FROM users";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้</title>
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
        .users-table-container {
            max-width: 1200px;
            margin: 36px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(37,99,235,0.08);
            padding: 24px 18px 18px 18px;
        }
        .users-table-title {
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 18px;
            color: #222;
        }
        table {
            width: 100%;
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
        .btn-details {
            background: #ffb300;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 7px 14px;
            font-size: 15px;
            font-weight: 600;
            margin-right: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-details:hover {
            background: #ff9800;
        }
        .btn-delete {
            background: #ef4444;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 7px 14px;
            font-size: 15px;
            font-weight: 600;
            margin-right: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-delete:hover {
            background: #d32f2f;
        }
        .btn-role-admin, .btn-role-user {
            background: #22c55e;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 7px 14px;
            font-size: 15px;
            font-weight: 600;
            margin-right: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-role-admin:hover, .btn-role-user:hover {
            background: #16a34a;
        }
        @media (max-width: 700px) {
            .users-table-container {
                padding: 8px 2vw 8px 2vw;
            }
            th, td {
                padding: 8px 4px;
                font-size: 0.98rem;
            }
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
    <div class="users-table-container">
        <div class="users-table-title">รายการผู้ใช้</div>
        <table>
            <thead>
                <tr>
                    <th>ชื่อผู้ใช้</th>
                    <th>อีเมล</th>
                    <th>สิทธิ์</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['role']); ?></td>
                        <td>
                            <a href="user-details.php?id=<?= $row['id']; ?>" class="btn-details">ดูรายละเอียด</a>
                            <?php if ($row['role'] !== 'admin'): ?>
                                <a href="manage_users.php?delete_id=<?= $row['id']; ?>" class="btn-delete" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?');">ลบ</a>
                                <a href="manage_users.php?change_role_id=<?= $row['id']; ?>&new_role=admin" class="btn-role-admin">ตั้งเป็น Admin</a>
                            <?php else: ?>
                                <a href="manage_users.php?change_role_id=<?= $row['id']; ?>&new_role=user" class="btn-role-user">ตั้งเป็น User</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>