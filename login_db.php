<?php 
session_start();
include('server.php');

$errors = array();


if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = mysqli_real_escape_string($conn, $_POST['password']);
    
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password_input)) {
        array_push($errors, "Password is required");
    }

    if (count($errors) == 0) {
        $password = md5($password_input); // ใช้ md5 ตามระบบเดิม
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $results = mysqli_query($conn, $query);

        if (mysqli_num_rows($results) == 1) {
            $user = mysqli_fetch_assoc($results);
            
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['success'] = "You are now logged in";

            if ($user['role'] === 'admin') {
                header('location: admin_dashboard.php');
            } else {
                header('location: user_dashboard.php');
            }
        } else {
            array_push($errors, "Wrong username/password combination");
            $_SESSION['errors'] = $errors;
            header('location: login.php');
        }
    } else {
        $_SESSION['errors'] = $errors;
        header('location: login.php');
    }
}
?>
