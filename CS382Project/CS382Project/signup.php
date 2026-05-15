<?php

include 'db_config.php';

// إذا استلم الملف بيانات (عن طريق AJAX)
if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT); // تشفير الباسورد للحماية

    $sql = "INSERT INTO users (username, email, password) VALUES ('$name', '$email', '$pass')";
    
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error";
    }
    exit(); // نوقف الكود هنا عشان ما يطبع الـ HTML مرتين
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - YIC To-Do System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <img src="YICLogo.jpg" alt="YIC Logo">
                <h2>Create Account</h2>
                <p>Join YIC To-Do System today</p>
            </div>

            <form id="signup-form">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Full Name" required id="id1">
                </div>

                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" placeholder="Email Address" required id="id2">
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" required id="id3">
                </div>

                <div class="input-group">
                    <i class="fas fa-check-circle"></i>
                    <input type="password" placeholder="Confirm Password" required id="id4">
                </div>

                <button type="submit" class="login-btn">Sign Up</button>
                
                <div class="login-footer">
                    <span>Already have an account? <a href="login.php">Login here</a></span>
                </div>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function(){
        $("#signup-form").submit(function(e){
            e.preventDefault();

            let fullname = $("#id1").val();
            let email = $("#id2").val();
            let password = $("#id3").val();
            let confirmPassword = $("#id4").val();

            if(password !== confirmPassword){
                alert("Passwords do not match!");
                return;
            }

            $.post("signup.php", // بينادي نفس الملف اللي احنا فيه
            {
                name: fullname,
                email: email,
                pass: password
            },
            function(data){
                if(data.trim() == "success"){
                    alert("Account created successfully!");
                    window.location.href = "login.php"; // يحوله لصفحة الدخول بعد النجاح
                } else {
                    alert("Sign up failed! Email might be already used.");
                }
            });
        });
    });
    </script>
</body>
</html>