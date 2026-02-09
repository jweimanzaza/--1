<!-- filepath: c:\xampp\htdocs\register\manage_application.php -->
<?php
session_start();
include('server.php');

// ตรวจสอบว่าเป็นผู้ดูแลระบบหรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$application_id || !$action) {
        die("ไม่มีข้อมูลที่ส่งมาจากฟอร์ม");
    }

    // ตรวจสอบว่าการกระทำคือ "ยอมรับ" หรือ "ไม่ยอมรับ"
    if ($action === 'accept') {
        $query = "UPDATE scholarship_applications SET status = 'ผ่าน' WHERE id = ?";
    } elseif ($action === 'reject') {
        $query = "UPDATE scholarship_applications SET status = 'ไม่ผ่าน' WHERE id = ?";
    } else {
        die("การกระทำไม่ถูกต้อง");
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $application_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
}
?>