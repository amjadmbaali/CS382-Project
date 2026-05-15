<?php
session_start();
include 'db_config.php';

// Check if data is coming from the form
if (isset($_POST['user'])) {
    $u = $_POST['user'];
    $p = $_POST['pass'];

    // Search for the user by email or username
    $sql = "SELECT * FROM users WHERE email='$u' OR username='$u'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    // Verify password
    if ($user && password_verify($p, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo "success"; // This tells your JavaScript that login is okay
    } else {
        echo "fail";
    }
    exit(); // Stop running the rest of the page
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YIC To-Do System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

</head>
<body class="login-body">

    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <img src="YICLogo.jpg" alt="YIC Logo">
                <h2>YIC To-Do System</h2>
                <p>Please login to manage your tasks</p>
            </div>

            <form id="login-form">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Username or Email" required id="id1">
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" required id="id2">
                </div>

                <button type="submit" class="login-btn">Login</button>
                
                <div class="login-footer">
                    <a href="#">Forgot Password?</a>
                    <span>Don't have an account? <a href="signup.html">Sign Up</a></span>
                </div>
            </form>
        </div>
    </div>
<script>
    $(document).ready(function(){
    $("#login-form").submit(function(e){
                e.preventDefault();


        let username =document.getElementById("id1").value;
        let password =document.getElementById("id2").value;

        if(username == "" || password == ""){
            alert("Please fill all fields");
        }
      $.post("login.php", 
{
    user: username,
    pass: password
},
function(data){
    if(data.trim() == "success"){
        // هذا السطر هو المسؤول عن فتح الموقع بعد نجاح الدخول
        window.location.href = "index.php"; 
    } else {
        alert("Invalid Username or Password");
    }
});
    });
});

</script>

</body>
</html>