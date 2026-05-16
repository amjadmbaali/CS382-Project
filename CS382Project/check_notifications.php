<?php
// Start session to access user data
// بدء الجلسة للوصول إلى بيانات المستخدم الحالي
session_start();

// Include database link config
// تضمين ملف إعدادات الاتصال بقاعدة البيانات
include 'db_config.php';

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    
    // Query to count active pending tasks in real-time
    // استعلام لحساب عدد المهام المعلقة النشطة في الوقت الفعلي
    $res = mysqli_query($conn, "SELECT COUNT(*) AS pending_count FROM tasks WHERE user_id='$uid' AND status='pending'");
    $row = mysqli_fetch_assoc($res);
    
    // Output only the pure number for the Javascript notification system
    // طباعة الرقم المجرد فقط ليتغذى عليه نظام إشعارات الجافا سكريبت
    echo $row['pending_count'];
} else {
    echo "0";
}
exit();
?>