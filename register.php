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
        :root{
            --bg1:#0f172a;
            --bg2:#1e293b;
            --card:#ffffff;
            --accent:#4f46e5;
            --muted:#64748b;
        }
        *{box-sizing:border-box}
        body{
            margin:0;min-height:100vh;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;
            background:linear-gradient(135deg,var(--bg1) 0%, var(--bg2) 100%);
            display:flex;align-items:center;justify-content:center;padding:24px;color:#0f172a;
        }
        .card{
            width:100%;max-width:420px;background:linear-gradient(180deg,rgba(255,255,255,0.96),rgba(255,255,255,0.94));
            border-radius:14px;padding:28px;box-shadow:0 10px 30px rgba(2,6,23,0.6);overflow:hidden;
        }
        .brand{
            display:flex;align-items:center;gap:12px;margin-bottom:18px
        }
        .logo{
            width:56px;height:56px;border-radius:12px;overflow:hidden;background:transparent;
            display:flex;align-items:center;justify-content:center;
        }
        .logo-img{width:100%;height:100%;object-fit:cover;display:block}
        h1{font-size:20px;margin:0;color:#0f172a}
        p.lead{margin:6px 0 18px;color:var(--muted);font-size:14px}
        .errors{background:#fee2e2;border:1px solid #fecaca;color:#7f1d1d;padding:10px;border-radius:8px;margin-bottom:14px}
        .success{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:10px;border-radius:8px;margin-bottom:14px}

        form .field{position:relative;margin-bottom:14px}
        .field input{
            width:100%;padding:14px 14px 14px 44px;border-radius:10px;border:1px solid #e6edf3;background:transparent;font-size:14px;color:#0f172a
        }
        .field svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted)}
        .btn{
            width:100%;padding:0;height:48px;border-radius:10px;border:none;cursor:pointer;font-weight:600;color:white;background:var(--accent);display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all 0.3s ease;margin-top:8px
        }
        .btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(79,70,229,0.3)}
        .links{display:flex;justify-content:center;margin-top:12px;font-size:13px;color:var(--muted)}
        .links a{color:var(--accent);text-decoration:none;margin-left:4px}
        @media (max-width:420px){.card{padding:20px}.logo{width:48px;height:48px}}
    </style>
</head>
<body>
    <div class="card" role="main">
        <div class="brand">
            <div class="logo">
                <img class="logo-img" src="https://ird.rmutp.ac.th/wp-content/uploads/%E0%B8%AA%E0%B8%A7%E0%B8%99%E0%B8%94%E0%B8%B8%E0%B8%AA%E0%B8%B4%E0%B8%95.png" alt="โลโก้">
            </div>
            <div>
                <h1>ระบบลงทะเบียนทุน</h1>
                <p class="lead">สร้างบัญชีใหม่เพื่อเข้าใช้ระบบ</p>
            </div>
        </div>

        <?php
        if (isset($_SESSION['errors'])) {
            echo '<div class="errors">';
            foreach ($_SESSION['errors'] as $error) {
                echo '<div>⚠️ ' . htmlspecialchars($error) . '</div>';
            }
            echo '</div>';
            unset($_SESSION['errors']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="success">✓ ' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <form action="register.php" method="post" autocomplete="on">
            <div class="field">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.2"/>
                    <path d="M4 20c0-3.314 3.582-6 8-6s8 2.686 8 6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input type="text" name="username" id="username" placeholder="ชื่อผู้ใช้" required>
            </div>

            <div class="field">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="1.2"/>
                    <path d="M2 6l10 7 10-7" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input type="email" name="email" id="email" placeholder="อีเมล" required>
            </div>

            <div class="field">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 8V7a5 5 0 10-10 0v1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="3" y="8" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.2"/>
                </svg>
                <input type="password" name="password" id="password" placeholder="รหัสผ่าน" required>
            </div>

            <div class="field">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 8V7a5 5 0 10-10 0v1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="3" y="8" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.2"/>
                </svg>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
            </div>

            <button type="submit" name="reg_user" class="btn">สมัครสมาชิก</button>

            <div class="links">
                มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบที่นี่</a>
            </div>
        </form>
    </div>
</body>
</html>
