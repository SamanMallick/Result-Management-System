<?php
session_start();

include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];


    $sql = "SELECT * FROM students WHERE email = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("SQL Error: " . mysqli_error($conn));
    }


    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);


    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) == 1) {


        $row = mysqli_fetch_assoc($result);


        if (password_verify($password, $row['password'])) {


            session_regenerate_id(true);


            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['student_name'] = $row['candidate_name'];
            $_SESSION['student_email'] = $row['email'];


            header("Location: student_dashboard.php");
            exit();


        } else {

            echo "<script>
                    alert('Incorrect Password');
                    window.location.href='student_login.php';
                  </script>";
        }


    } else {


        echo "<script>
                alert('Email not found');
                window.location.href='student_login.php';
              </script>";

    }

    mysqli_stmt_close($stmt);

}

mysqli_close($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.0/css/all.min.css">

    <link rel="stylesheet" href="login.css">
</head>

<body>

<div class="lowersection">
    <a href="index.php" class="lower-sec">HOME</a>
    <a href="student_login.php" class="lower-sec">STUDENT LOGIN</a>
    <a href="teacher_login.php" class="lower-sec">TEACHER LOGIN</a>
    <a href="admin_login.php" class="lower-sec">ADMIN LOGIN</a>
    <a href="#" class="lower-sec">FEEDBACK</a>
    <a href="#" class="lower-sec">FAQ</a>
    <a href="#" class="lower-sec">HELP DESK</a>
    <a href="#" class="lower-sec">CONTACT US</a>
</div>

<div class="page">

    <div class="login-card">

        <div class="img12">
             <i class="fa-solid fa-user"></i>
        </div>

        <h2>Student Login</h2>

        <form action="" method="POST">

            <label>Email</label>
            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >

            <label>Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <div class="show-password">
                <input type="checkbox" id="showPassword">
                <label for="showPassword">Show Password</label>
            </div>

            <button type="submit">Login</button>

        </form>

    </div>

</div>

<script>
const showPassword = document.getElementById("showPassword");
const password = document.getElementById("password");

showPassword.addEventListener("change", function () {
    password.type = this.checked ? "text" : "password";
});
</script>

</body>
</html>