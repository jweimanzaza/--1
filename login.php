<?php
session_start();
include('server.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลผู้ใช้
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];

            // เปลี่ยนเส้นทางตาม Role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] === 'committee') {
                header("Location: committee_dashboard.php");
            } elseif ($user['role'] === 'user') {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $_SESSION['errors'] = ["รหัสผ่านไม่ถูกต้อง"];
        }
    } else {
        $_SESSION['errors'] = ["ไม่พบชื่อผู้ใช้นี้"];
    }
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
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

        form .field{position:relative;margin-bottom:14px}
        .field input{
            width:100%;padding:14px 14px 14px 44px;border-radius:10px;border:1px solid #e6edf3;background:transparent;font-size:14px;color:#0f172a
        }
        .field svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted)}
        .actions{display:flex;gap:12px;align-items:stretch;margin-top:12px}
        .btn{
            flex:1;padding:0 12px;border-radius:10px;border:none;cursor:pointer;font-weight:600;color:white;background:var(--accent);height:48px;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all 0.3s ease
        }
        .btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(79,70,229,0.3)}
        .btn.secondary{background:transparent;color:var(--accent);border:1.5px solid var(--accent)}
        .links{display:flex;justify-content:space-between;margin-top:12px;font-size:13px;color:var(--muted)}
        .links a{color:var(--accent);text-decoration:none}
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
                <p class="lead">เข้าสู่ระบบเพื่อจัดการและสมัครทุนการศึกษา</p>
            </div>
        </div>

        <?php if (isset($_SESSION['errors'])): ?>
            <div class="errors">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <div>⚠️ <?= htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" autocomplete="on">
            <div class="field">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="8" r="3.5" stroke="#64748b" stroke-width="1.2"/>
                    <path d="M4 20c0-3.314 3.582-6 8-6s8 2.686 8 6" stroke="#64748b" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input type="text" name="username" id="username" placeholder="ชื่อผู้ใช้" required>
            </div>

            <div class="field">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 8V7a5 5 0 10-10 0v1" stroke="#64748b" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="3" y="8" width="18" height="13" rx="2" stroke="#64748b" stroke-width="1.2"/>
                </svg>
                <input type="password" name="password" id="password" placeholder="รหัสผ่าน" required>
            </div>

            <div class="actions">
                <button type="submit" class="btn">เข้าสู่ระบบ</button>
                <a href="register.php" class="btn secondary">สมัครสมาชิก</a>
            </div>

            <div class="links">
                <span>ยังไม่มีบัญชี? <a href="register.php">สมัครที่นี่</a></span>
            </div>
        </form>
    </div>
</body>
</html>
