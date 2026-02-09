<?php 
    session_start();
    include('server.php'); 

    // ตรวจสอบว่ามีการส่งข้อมูลการสมัครสมาชิกหรือไม่
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = trim($_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
        $role = 'user'; // กำหนด role เป็น 'user' สำหรับผู้ใช้ทั่วไป

        // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
        if ($password !== $confirm_password) {
            $_SESSION['errors'] = ["รหัสผ่านไม่ตรงกัน"];
            header("Location: register.php");
            exit();
        }

        // ตรวจสอบว่าค่า email ไม่ว่าง
        if (empty($email)) {
            $_SESSION['errors'] = ["กรุณากรอกอีเมล"];
            header("Location: register.php");
            exit();
        }

        // ตรวจสอบว่าค่า email ซ้ำหรือไม่
        $check_query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['errors'] = ["อีเมลนี้ถูกใช้งานแล้ว"];
            header("Location: register.php");
            exit();
        }

        // เข้ารหัสรหัสผ่าน
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // เพิ่มข้อมูลผู้ใช้ลงในฐานข้อมูล
        $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            $_SESSION['success'] = "สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ";
            header("Location: login.php"); // เปลี่ยนเส้นทางไปยังหน้า login.php
            exit();
        } else {
            $_SESSION['errors'] = ["เกิดข้อผิดพลาดในการสมัครสมาชิก: " . $stmt->error];
            header("Location: register.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .register-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .input-group input:focus {
            border-color: #007bff;
            outline: none;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>สมัครสมาชิก</h2>

        <?php
        if (isset($_SESSION['errors'])) {
            echo '<div class="error">';
            foreach ($_SESSION['errors'] as $error) {
                echo '<p>⚠️ ' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
            unset($_SESSION['errors']);
        }
        ?>

        <form action="register.php" method="post">
            <div class="input-group">
                <label for="username">ชื่อผู้ใช้</label>
                <input type="text" name="username" id="username" placeholder="กรอกชื่อผู้ใช้" required>
            </div>
            <div class="input-group">
                <label for="email">อีเมล</label>
                <input type="email" name="email" id="email" placeholder="กรอกอีเมล" required>
            </div>
            <div class="input-group">
                <label for="password">รหัสผ่าน</label>
                <input type="password" name="password" id="password" placeholder="กรอกรหัสผ่าน" required>
            </div>
            <div class="input-group">
                <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
            </div>
            <div class="input-group">
                <button type="submit" name="reg_user" class="btn">สมัครสมาชิก</button>
            </div>
            <div class="register-link">
                <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
            </div>
        </form>
    </div>
</body>
</html>
