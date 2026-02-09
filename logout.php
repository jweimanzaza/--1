<!-- filepath: c:\xampp\htdocs\register\logout.php -->
<?php
session_start();

// ลบข้อมูลเซสชันทั้งหมด
session_unset();
session_destroy();

// เปลี่ยนเส้นทางไปยังหน้า login.php
header("Location: login.php");
exit();
?>