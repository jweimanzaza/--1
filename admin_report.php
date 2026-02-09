<?php
session_start();
include('server.php');

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลผู้ใช้และทุน
$sql = "SELECT u.id, u.username, u.role, sp.first_name, sp.last_name, sa.scholarship_id, s.name AS scholarship_name, sa.status, sa.final_status
        FROM users u
        LEFT JOIN student_profiles sp ON u.id = sp.user_id
        LEFT JOIN scholarship_applications sa ON u.id = sa.user_id
        LEFT JOIN scholarships s ON sa.scholarship_id = s.id
        WHERE u.role = 'user'
        ORDER BY u.username, sa.id DESC";
$result = mysqli_query($conn, $sql);

// สร้าง array $users จากผลลัพธ์
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    if (!isset($users[$row['id']])) {
        $users[$row['id']] = [
            'username' => $row['username'],
            'name' => $row['name'],
            'scholarships' => [],
            'statuses' => [],
            'final_statuses' => [],
        ];
    }
    if ($row['scholarship_name']) {
        $users[$row['id']]['scholarships'][] = $row['scholarship_name'];
        $users[$row['id']]['statuses'][] = $row['status'];
        $users[$row['id']]['final_statuses'][] = $row['final_status'];
    }
}
$users = array_values($users);

// จัดกลุ่มข้อมูล
$never = [];
$approved = [];
$pending = [];
$waiting = [];

foreach ($users as $user) {
    if (empty($user['scholarships'])) {
        $never[] = $user;
        continue;
    }

    // เลือกสถานะล่าสุด (application ล่าสุด)
    $last_final_status = null;
    $last_status = null;
    if (!empty($user['final_statuses'])) {
        foreach ($user['final_statuses'] as $fs) {
            if ($fs !== null && $fs !== '') {
                $last_final_status = $fs;
                break;
            }
        }
    }
    if (!empty($user['statuses'])) {
        foreach ($user['statuses'] as $st) {
            if ($st !== null && $st !== '') {
                $last_status = $st;
                break;
            }
        }
    }
    $status_to_use = ($last_final_status !== null && $last_final_status !== '') ? $last_final_status : $last_status;

    // ได้รับทุนแล้ว
    if ($status_to_use === 'approved' || $status_to_use === 'อนุมัติ') {
        $approved[] = $user;
    }
    // รอดำเนินการ
    elseif ($status_to_use === 'pending' || $status_to_use === 'รอดำเนินการ') {
        $pending[] = $user;
    }
    // รอประกาศผล
    elseif ($status_to_use === 'waiting' || $status_to_use === 'รอประกาศผล') {
        $waiting[] = $user;
    }
    // ถ้าไม่เข้าเงื่อนไขใดเลย ให้ใส่ใน never
    else {
        $never[] = $user;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานผู้ใช้และทุนการศึกษา</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', Arial, sans-serif;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .navbar {
            display: flex;
            gap: 10px;
            background: #fff;
            padding: 14px 32px;
            box-shadow: 0 2px 12px rgba(37,99,235,0.08);
            border-radius: 0 0 18px 18px;
            align-items: center;
            margin-bottom: 24px;
        }
        .navbar a {
            color: #2563eb;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-size: 17px;
            font-weight: 600;
            transition: background 0.2s, color 0.2s;
        }
        .navbar a.active, .navbar a:hover {
            background: #e0e7ff;
            color: #1e40af;
        }
        .navbar a:last-child {
            background: linear-gradient(90deg, #ef4444 0%, #f87171 100%);
            color: #fff;
            margin-left: auto;
        }
        .content-wrapper {
            max-width: 1200px;
            margin: 48px auto 0 auto;
            padding: 0 20px 40px 20px;
        }
        .report-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 32px rgba(37,99,235,0.10);
            padding: 36px 28px 28px 28px;
            margin-top: 32px;
            transition: box-shadow 0.2s;
        }
        .report-card:hover {
            box-shadow: 0 10px 40px rgba(37,99,235,0.18);
        }
        .report-title {
            text-align: center;
            color: #2563eb;
            margin-bottom: 30px;
            font-size: 1.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .report-title .fa {
            font-size: 1.3em;
        }
        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(37,99,235,0.04);
        }
        .report-table th, .report-table td {
            border: none;
            padding: 15px 12px;
            text-align: left;
            font-size: 16px;
        }
        .report-table th {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .report-table tr {
            border-radius: 12px;
        }
        .report-table tr:nth-child(even) {
            background: #f1f5f9;
        }
        .report-table tr:nth-child(odd) {
            background: #fff;
        }
        .report-table tbody tr:hover {
            background: #e0e7ff;
            transition: background 0.15s;
        }
        .no-scholarship {
            color: #ef4444;
            font-weight: bold;
        }
        @media (max-width: 900px) {
            .content-wrapper {
                max-width: 98vw;
                padding: 0 2vw 40px 2vw;
            }
            .report-card {
                padding: 18px 4px 18px 4px;
            }
            .report-title {
                font-size: 1.2rem;
            }
            .report-table th, .report-table td {
                font-size: 14px;
                padding: 8px 4px;
            }
        }
        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
                gap: 6px;
                padding: 10px 6px;
                border-radius: 0 0 10px 10px;
            }
            .report-title {
                font-size: 1rem;
            }
            .report-card {
                margin-top: 18px;
                padding: 10px 2px 10px 2px;
            }
            .report-table th, .report-table td {
                font-size: 12px;
                padding: 6px 2px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin_dashboard.php"><i class="fa fa-home"></i> หน้าหลัก</a>
        <a href="admin_report.php" class="active"><i class="fa fa-bar-chart"></i> Report</a>
        <a href="logout.php" style="background:linear-gradient(90deg,#ef4444 0%,#f87171 100%);color:#fff;"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
    </div>
    <div class="content-wrapper">
        <!-- ตารางผู้ใช้ที่ยังไม่เคยได้รับทุน -->
        <div class="report-card">
            <div class="report-title"><i class="fa fa-user-times"></i> ยังไม่เคยได้รับทุน</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>ชื่อผู้ใช้</th>
                        <th>ชื่อ-นามสกุล</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($never as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ตารางผู้ใช้ที่ได้รับทุนแล้ว -->
        <div class="report-card">
            <div class="report-title"><i class="fa fa-check-circle"></i> ได้รับทุนแล้ว</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>ชื่อผู้ใช้</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ทุนที่ได้รับ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approved as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= implode(', ', array_map('htmlspecialchars', $user['scholarships'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ตารางรอดำเนินการ -->
        <div class="report-card">
            <div class="report-title"><i class="fa fa-hourglass-half"></i> รอดำเนินการ</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>ชื่อผู้ใช้</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ทุนที่สมัคร</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= implode(', ', array_map('htmlspecialchars', $user['scholarships'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ตารางรอประกาศผล -->
        <div class="report-card">
            <div class="report-title"><i class="fa fa-clock-o"></i> รอประกาศผล</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>ชื่อผู้ใช้</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ทุนที่สมัคร</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($waiting as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= implode(', ', array_map('htmlspecialchars', $user['scholarships'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>