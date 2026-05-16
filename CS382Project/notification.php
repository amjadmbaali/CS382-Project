<?php
// Start the session to track logged-in user data
// بدء الجلسة (Session) لتتبع بيانات المستخدم المسجل في النظام
session_start();

// Include the database configuration file
// تضمين ملف إعدادات قاعدة البيانات للاتصال بها
include 'db_config.php';

// Store the logged-in user ID from the session into a variable
// تخزين معرف المستخدم المسجل الحالي من الجلسة داخل متغير
$uid = $_SESSION['user_id'];

// Query to fetch all pending/incomplete tasks for the current user
// استعلام لجلب جميع المهام المعلقة وغير المكتملة للمستخدم الحالي
$res = mysqli_query($conn, "SELECT * FROM tasks 
                            WHERE user_id='$uid' 
                            AND status='pending'");

// Calculate the number of pending tasks returned from the database
// حساب عدد المهام المعلقة المسترجعة من قاعدة البيانات
$pending = mysqli_num_rows($res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - YIC To-Do</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
<div class="container">

    <aside class="sidebar">
        <div class="logo">
            <img src="YICLogo.jpg" alt="YIC Logo" class="logo-img">
            <h3>YIC To-Do List</h3>
        </div>

        <nav>
            <ul>
                <li class="nav-item">
                    <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="tasks.php"><i class="fas fa-tasks"></i> My Tasks</a>
                </li>
                <li class="nav-item">
                    <a href="weekly.php"><i class="fas fa-chart-line"></i> Weekly Progress</a>
                </li>
                <li class="nav-item active">
                    <a href="notification.php"><i class="fas fa-bell"></i> Notifications</a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header>
            <div class="header-title">
                <h1>Notifications</h1>
                <p>Your task reminders</p>
            </div>
        </header>

        <section class="content-section">
            <div class="task-list">
                <h2>System Reminder</h2>

                <?php
                // PHP conditional block to check if there are pending actions
                // جملة شرطية بالـ PHP للفحص والتحقق مما إذا كان هناك مهام معلقة
                if($pending > 0){
                    // Display warning alert card printing the total count of remaining tasks
                    // عرض كرت التنبيه والتحذير البرتقالي ويطبع العدد الإجمالي للمهام المتبقية
                    echo '
                    <div class="toast" style="display:block; position:static;">
                        <div class="toast-header">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Reminder</strong>
                        </div>
                        <div class="toast-body">
                            You still have '.$pending.' incomplete tasks.
                        </div>
                    </div>';
                } else {
                    // Display congratulations success card using the perfect/green status badge properties
                    // عرض كرت النجاح والتهنئة الأخضر باستخدام خصائص شارات الأداء الممتاز
                    echo '
                    <div class="toast" style="display:block; position:static; border-left-color: #4caf50;">
                        <div class="toast-header" style="color: #2e7d32;">
                            <i class="fas fa-check-circle"></i>
                            <strong>Good Job</strong>
                        </div>
                        <div class="toast-body">
                            You completed all your tasks.
                        </div>
                    </div>';
                }
                ?>

            </div>
        </section>
    </main>

</div>
</body>
</html>