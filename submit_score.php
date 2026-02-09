<?php
session_start();
include('server.php');

// ตรวจสอบว่าเป็น Committee หรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'committee') {
    header("Location: login.php");
    exit();
}

// รับข้อมูลจากฟอร์ม
$user_id = intval($_POST['user_id']);
$scholarship_id = intval($_POST['scholarship_id']);
$committee_id = $_SESSION['user_id']; // ID ของกรรมการที่ล็อกอิน
$score = floatval($_POST['score']);

// ตรวจสอบว่ากรรมการคนนี้ได้ให้คะแนนนักศึกษาคนนี้ในทุนนี้แล้วหรือยัง
$query = "SELECT COUNT(*) AS count FROM review_scores 
          WHERE user_id = ? AND scholarship_id = ? AND committee_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $scholarship_id, $committee_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    // หากมีการให้คะแนนแล้ว
    header("Location: committee_review.php?error=already_scored");
    exit();
}

// บันทึกคะแนนลงฐานข้อมูล
$query = "INSERT INTO review_scores (user_id, committee_id, scholarship_id, score) 
          VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiid", $user_id, $committee_id, $scholarship_id, $score);

if ($stmt->execute()) {
    header("Location: committee_review.php?success=score");
    exit();
} else {
    echo "เกิดข้อผิดพลาดในการบันทึกคะแนน";
}
?>