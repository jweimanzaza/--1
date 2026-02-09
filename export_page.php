<?php
session_start();
include('server.php');

// ตรวจสอบว่าเป็น Admin หรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Export คะแนนเป็น Excel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap');
        body { background: #f8fafc; font-family: 'Prompt', Arial, sans-serif; }
        .export-container {
            max-width: 500px;
            margin: 60px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 24px rgba(37,99,235,0.08);
            padding: 36px 32px 32px 32px;
        }
        .export-title {
            text-align: center;
            color: #2563eb;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 24px;
        }
        label { font-weight: bold; margin-bottom: 10px; display: block; color: #222; }
        select, button {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 8px;
            border: 1.5px solid #e0e7ff;
            font-size: 1.08rem;
            background: #f8fafc;
            transition: border 0.18s;
        }
        select:focus, button:focus {
            border: 2px solid #2563eb;
            outline: none;
        }
        button {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            font-size: 1.1rem;
        }
        button:hover { background: linear-gradient(90deg, #60a5fa 0%, #2563eb 100%); }
        .fa-file-excel-o {
            color: #22c55e;
            margin-right: 10px;
            font-size: 1.5em;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="export-container">
        <div class="export-title"><i class="fa fa-file-excel-o"></i>Export คะแนนเป็น Excel</div>
        <form method="GET" action="export_applications.php">
            <label for="scholarship_id">เลือกชื่อทุนการศึกษา:</label>
            <select name="scholarship_id" id="scholarship_id" required>
                <option value="">-- เลือกทุน --</option>
                <?php
                $sql = "SELECT id, name FROM scholarships ORDER BY name ASC";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
                }
                ?>
            </select>
            <button type="submit"><i class="fa fa-download"></i> Export</button>
        </form>
    </div>
</body>
</html>