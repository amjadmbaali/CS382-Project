<?php
// Start session to save user state after verifying identity
// بدء الجلسة (Session) لحفظ حالة المستخدم بعد التحقق من هويته
session_start();

// Include connection database configuration file
// تضمين ملف إعدادات قاعدة البيانات لإنشاء الاتصال
include 'db_config.php';

// Object-Oriented class to handle secure user authentication
// كلاس قائم على البرمجة الشيئية للتحكم بالتحقق الآمن من هوية المستخدمين
class LoginSystem {

    private $conn;

    // Receive the active database connection via the constructor
    // استقبال اتصال قاعدة البيانات النشط عبر الدالة البنائية
    public function __construct($connection) {
        $this->conn = $connection;
    }

    // Method to check credentials against records and verify hashed password
    // دالة لمطابقة البيانات مع السجلات والتحقق من كلمة المرور المشفرة
    public function login($username, $password): bool {

        // Check if input matches either registered email or username
        // فحص ما إذا كانت المدخلات تطابق الإيميل أو اسم المستخدم المسجل
        $sql = "SELECT * FROM users 
                WHERE email='$username' 
                OR username='$username'";

        $result = mysqli_query($this->conn, $sql);
        $user = mysqli_fetch_assoc($result);

        // Verify plain password against securely hashed password in database
        // التحقق من كلمة المرور المدخلة مقارنة بالتشفير المخزن في قاعدة البيانات
        if ($user && password_verify($password, $user['password'])) {

            // Save user data inside session variables for global accessibility
            // حفظ بيانات المستخدم داخل متغيرات الجلسة للوصول لها من بقية الصفحات
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            return true;
        }

        return false;
    }
}

// Intercept AJAX POST request when login variables are transmitted
// التقاط طلب الأجاكس المرسل عند تمرير متغيرات تسجيل الدخول
if (isset($_POST['user'])) {

    $u = $_POST['user'];
    $p = $_POST['pass'];

    // Instantiating the login core object
    // إنشاء كائن جديد من كلاس نظام تسجيل الدخول
    $login = new LoginSystem($conn);

    // Output status string back to the AJAX frontend caller
    // طباعة نصوص الحالات لإرجاعها إلى واجهة الأجاكس الأمامية
    if ($login->login($u, $p)) {
        echo "success";
    } else {
        echo "fail";
    }

    exit();
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
                    <span>Don't have an account? <a href="signup.php">Sign Up</a></span>
                </div>
            </form>
        </div>
    </div>

<script>
// jQuery execution context ensuring DOM tree is fully compiled
// بيئة تنفيذ الجي كويري لضمان اكتمال تحميل شجرة عناصر الواجهة
$(document).ready(function(){

    // Handling form submission using interactive JavaScript events
    // معالجة إرسال النموذج باستخدام أحداث الجافا سكريبت التفاعلية
    $("#login-form").submit(function(e){
        // Prevent traditional full-page form routing behavior
        // منع السلوك التقليدي للنموذج من إعادة تحميل الصفحة بالكامل
        e.preventDefault();

        // Extracting string inputs directly via unique elements IDs
        // استخراج نصوص المدخلات مباشرة عبر المعرفات الفريدة للعناصر
        let username = document.getElementById("id1").value;
        let password = document.getElementById("id2").value;

        // Basic front-end validation check for empty strings
        // تحقق مبدئي بالواجهة الأمامية للتأكد من عدم وجود حقول فارغة
        if(username == "" || password == ""){
            alert("Please fill all fields");
            return;
        }

        // Asynchronous AJAX transmission requesting verify process from back-end
        // إرسال طلب أجاكس غير متزامن لطلب عملية التحقق من الخلفية البرمجية
        $.post("login.php",
        {
            user: username,
            pass: password
        },
        function(data){
            // If match validation passes, route user smoothly to main dashboard
            // في حال نجاح التحقق، يتم توجيه المستخدم بسلاسة إلى لوحة التحكم الرئيسية
            if(data.trim() == "success"){
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