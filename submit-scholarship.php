<!-- filepath: c:\xampp\htdocs\register\submit-scholarship.php -->
<?php
session_start();
include('server.php');

if (isset($_POST['submit_scholarship'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $major = mysqli_real_escape_string($conn, $_POST['major']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $current_address = mysqli_real_escape_string($conn, $_POST['current_address']);
    $sub_district = mysqli_real_escape_string($conn, $_POST['sub_district']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);

    // Handle file upload
    $image = $_FILES['scholarship_image']['name'];
    $target = "uploads/" . basename($image);

    if (move_uploaded_file($_FILES['scholarship_image']['tmp_name'], $target)) {
        $query = "INSERT INTO scholarships (first_name, last_name, student_id, course, major, phone, current_address, sub_district, district, province, image) 
                  VALUES ('$first_name', '$last_name', '$student_id', '$course', '$major', '$phone', '$current_address', '$sub_district', '$district', '$province', '$image')";
        mysqli_query($conn, $query);
        $_SESSION['success'] = "Scholarship application submitted successfully!";
        header('location: index.php');
    } else {
        $_SESSION['errors'] = ["Failed to upload image."];
        header('location: scholarship-info.php');
    }
}
?>