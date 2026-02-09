<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('server.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลทุนการศึกษาทั้งหมด
$scholarship_query = "SELECT * FROM scholarships";
$scholarships = mysqli_query($conn, $scholarship_query);

$message = ""; // ตัวแปรสำหรับเก็บข้อความแจ้งเตือน

// เมื่อกดปุ่มสมัครทุน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scholarship_id'])) {
    $scholarship_id = intval($_POST['scholarship_id']); // รับค่า scholarship_id จากฟอร์ม
    $user_id = $_SESSION['user_id']; // ใช้ user_id จาก session

    // ตรวจสอบว่านักศึกษาได้สมัครทุนนี้ไปแล้วหรือยัง
    $check_query = "SELECT * FROM scholarship_applications WHERE user_id = ? AND scholarship_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $scholarship_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['errors'] = ["คุณได้สมัครทุนนี้ไปแล้ว"];
        header("Location: scholarship-details.php?id=$scholarship_id");
        exit();
    } else {
        // สมัครทุน
        $apply_query = "INSERT INTO scholarship_applications (user_id, scholarship_id, status, applied_at) VALUES (?, ?, 'รอดำเนินการ', NOW())";
        $stmt = $conn->prepare($apply_query);
        $stmt->bind_param("ii", $user_id, $scholarship_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "สมัครทุนสำเร็จ";
            header("Location: scholarship-details.php?id=$scholarship_id");
            exit();
        } else {
            $_SESSION['errors'] = ["เกิดข้อผิดพลาดในการสมัครทุน: " . $stmt->error];
            header("Location: scholarship-details.php?id=$scholarship_id");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครทุนการศึกษา</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .message {
            font-size: 18px;
            color: #007bff;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group select {
            padding: 10px;
            width: 100%;
            font-size: 16px;
        }
        .btn-submit, .btn-back {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .btn-submit:hover, .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>สมัครทุนการศึกษา</h2>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message); ?></p>
        <?php else: ?>
            <form method="POST" action="student_scholarship.php">
                <div class="form-group">
                    <label for="scholarship_id">เลือกทุนการศึกษา:</label>
                    <select name="scholarship_id" id="scholarship_id" required>
                        <?php while ($row = mysqli_fetch_assoc($scholarships)): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit">สมัครทุน</button>
            </form>
        <?php endif; ?>
        <!-- ปุ่มย้อนกลับไปหน้าหลัก -->
        <a href="student_dashboard.php" class="btn-back">ย้อนกลับไปหน้าหลัก</a>
    </div>
</body>
</html>