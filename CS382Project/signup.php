<?php
// Include connection database configuration file
// تضمين ملف إعدادات قاعدة البيانات لإنشاء الاتصال
include 'db_config.php';

// Object-Oriented class to handle secure user registration
// كلاس قائم على البرمجة الشيئية للتحكم بعملية تسجيل المستخدمين الجدد بأمان
class SignupSystem {
    private $conn;

    // Receive the active database connection via the constructor
    // استقبال اتصال قاعدة البيانات النشط عبر الدالة البنائية
    public function __construct($connection) {
        $this->conn = $connection;
    }

    // Method to create a new user account with a securely hashed password
    // دالة لإنشاء حساب مستخدم جديد مع تشفير كلمة المرور بشكل آمن
    public function signup($name, $email, $password): bool {
        // Hash the password using the default secure standard algorithm
        // تشفير كلمة المرور باستخدام خوارزمية التشفير القياسية والآمنة الافتراضية للأنظمة
        $pass = password_hash($password, PASSWORD_DEFAULT);
        
        // SQL Query to insert the new user data into the users table
        // استعلام الداتا بيس لإدخل بيانات المستخدم الجديد في جدول المستخدمين
        $sql = "INSERT INTO users (username, email, password)
                VALUES ('$name', '$email', '$pass')";
                
        // Return true if insertion is successful, otherwise false
        // إرجاع قيمة (True) في حال نجاح عملية الإدخال، وغير ذلك يرجع (False)
        if(mysqli_query($this->conn, $sql)){
            return true;
        }
        return false;
    }
}

// Intercept AJAX POST request when registration variables are transmitted
// التقاط طلب الأجاكس المرسل عند تمرير متغيرات إنشاء الحساب الجديد
if (isset($_POST['name'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['pass'];

    // Instantiating the registration core object
    // إنشاء كائن جديد من كلاس نظام إنشاء الحساب
    $signup = new SignupSystem($conn);

    // Output status string back to the AJAX frontend caller
    // طباعة نصوص الحالات لإرجاعها إلى واجهة الأجاكس الأمامية
    if($signup->signup($name, $email, $password)){
        echo "success";
    }
    else{
        echo "error";
    }

    exit();
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
    // jQuery execution context ensuring DOM tree is fully compiled
    // بيئة تنفيذ الجي كويري لضمان اكتمال تحميل شجرة عناصر الواجهة
    $(document).ready(function(){
        // Handling form submission using interactive JavaScript events
        // معالجة إرسال النموذج باستخدام أحداث الجافا سكريبت التفاعلية
        $("#signup-form").submit(function(e){
            // Prevent traditional full-page form routing behavior
            // منع السلوك التقليدي للنموذج من إعادة تحميل الصفحة بالكامل
            e.preventDefault();

            // Extracting string inputs directly using jQuery ID selectors
            // استخراج نصوص المدخلات مباشرة عبر محددات الهوية التابعة للجي كويري
            let fullname = $("#id1").val();
            let email = $("#id2").val();
            let password = $("#id3").val();
            let confirmPassword = $("#id4").val();

            // Front-end validation check to verify both passwords match
            // تحقق بالواجهة الأمامية للتأكد من تطابق كلمتي المرور المدخلتين
            if(password !== confirmPassword){
                alert("Passwords do not match!");
                return;
            }

            // Asynchronous AJAX transmission requesting insertion process from back-end
            // إرسال طلب أجاكس غير متزامن لطلب عملية الإدخال والحفظ من الخلفية البرمجية
            $.post("signup.php", 
            {
                name: fullname,
                email: email,
                pass: password
            },
            function(data){
                // If creation succeeds, notify user and smoothly direct them to login page
                // في حال نجاح إنشاء الحساب، يتم تنبيه المستخدم وتوجيهه بسلاسة لصفحة تسجيل الدخول
                if(data.trim() == "success"){
                    alert("Account created successfully!");
                    window.location.href = "login.php"; 
                } else {
                    alert("Sign up failed! Email might be already used.");
                }
            });
        });
    });
    </script>
</body>
</html>