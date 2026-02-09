<?php
session_start();
include('server.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password_1 = $_POST['password_1'];
    $password_2 = $_POST['password_2'];
    $role = 'user'; // กำหนด Role เป็น 'user' โดยอัตโนมัติ

    // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
    if ($password_1 !== $password_2) {
        $_SESSION['errors'] = ["รหัสผ่านไม่ตรงกัน"];
        header("Location: register.php");
        exit();
    }

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password_1, PASSWORD_DEFAULT);

    // เพิ่มข้อมูลผู้ใช้ในฐานข้อมูล
    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        // เปลี่ยนเส้นทางไปยัง student_dashboard.php
        header("Location: student_dashboard.php");
        exit();
    } else {
        $_SESSION['errors'] = ["เกิดข้อผิดพลาดในการสมัครสมาชิก"];
        header("Location: register.php");
        exit();
    }
}
?>