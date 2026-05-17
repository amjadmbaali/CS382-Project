<?php
// Start the session to track logged-in user data
// بدء الجلسة (Session) لتتبع بيانات المستخدم المسجل في النظام
session_start();

// Include the database configuration file
// تضمين ملف إعدادات قاعدة البيانات للاتصال بها
include 'db_config.php';

// Redirect to login page if the user is not authenticated
// تحويل المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجلاً في النظام
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

// ========================================================
// 1. SYSTEM CORE CLASS (OOP ARCHITECTURE)
// ========================================================
class TaskManager {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Method to fetch all tasks for a specific user
    // دالة جلب جميع المهام الخاصة بمستخدم معين
    public function getUserTasks($uid) {
        return mysqli_query($this->conn, "SELECT * FROM tasks WHERE user_id='$uid' ORDER BY id DESC");
    }

    // Method to add a new task into the database
    // دالة إضافة مهمة جديدة إلى قاعدة البيانات
    public function addTask($uid, $task_text) {
        $clean_text = mysqli_real_escape_string($this->conn, $task_text);
        mysqli_query($this->conn, "INSERT INTO tasks (user_id, task_text, status) VALUES ('$uid', '$clean_text', 'pending')");
    }

    // Method to toggle task status between pending and completed
    // دالة تبديل حالة المهمة بين معلقة ومكتملة
    public function toggleTaskStatus($uid, $id, $current_status) {
        $new_status = ($current_status == 'pending') ? 'completed' : 'pending';
        $completed_at = ($new_status == 'completed') ? "NOW()" : "NULL";
        mysqli_query($this->conn, "UPDATE tasks SET status='$new_status', completed_at=$completed_at WHERE id='$id' AND user_id='$uid'");
    }

    // Method to dynamically update the task description based on user inputs
    // الميثود المحدثة: تقوم بتعديل نص وصف المهمة ديناميكياً بناءً على مدخلات المستخدم الفعلية
    public function editTask($uid, $id, $new_text) {
        $clean_text = mysqli_real_escape_string($this->conn, $new_text);
        mysqli_query($this->conn, "UPDATE tasks SET task_text='$clean_text' WHERE id='$id' AND user_id='$uid'");
    }

    // Method to delete a task from the database
    // دالة حذف المهمة نهائياً من قاعدة البيانات
    public function deleteTask($uid, $id) {
        mysqli_query($this->conn, "DELETE FROM tasks WHERE id='$id' AND user_id='$uid'");
    }
}

// Instantiate the TaskManager object
// إنشاء كائن (Object) من كلاس إدارة المهام
$taskSystem = new TaskManager($conn);

// ========================================================
// 2. ASYNCHRONOUS AJAX REQUESTS HANDLING INTERCEPTOR
// ========================================================

// Handle adding a new task via AJAX
if (isset($_POST['add_task_text'])) {
    $taskSystem->addTask($uid, $_POST['add_task_text']);
    exit();
}

// Handle toggling task status via AJAX
if (isset($_POST['toggle_id']) && isset($_POST['current_status'])) {
    $taskSystem->toggleTaskStatus($uid, $_POST['toggle_id'], $_POST['current_status']);
    exit();
}

// Handle dynamic text update requests via AJAX
if (isset($_POST['edit_id']) && isset($_POST['new_task'])) {
    $taskSystem->editTask($uid, $_POST['edit_id'], $_POST['new_task']);
    exit();
}

// Handle deleting a task via AJAX
if (isset($_POST['delete_id'])) {
    $taskSystem->deleteTask($uid, $_POST['delete_id']);
    exit();
}

// ========================================================
// 3. STATISTICAL METRICS CALCULATION
// ========================================================
$allTasksQuery = $taskSystem->getUserTasks($uid);
$total_tasks = mysqli_num_rows($allTasksQuery);

$pendingRes = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id='$uid' AND status='pending'");
$pending_tasks = mysqli_num_rows($pendingRes);

$completed_tasks = $total_tasks - $pending_tasks;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - YIC To-Do List</title>
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
                <li class="nav-item active"><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="nav-item"><a href="tasks.php"><i class="fas fa-tasks"></i> My Tasks</a></li>
                <li class="nav-item"><a href="weekly.php"><i class="fas fa-chart-line"></i> Weekly Progress</a></li>
                <li class="nav-item">
                    <a href="notification.php">
                        <i class="fas fa-bell"></i> Notifications
                        <span class="badge" style="display: none;"><?php echo $pending_tasks; ?></span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header>
            <div class="header-title">
                <h1>Welcome Back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</h1>
                <p>Track your academic tasks efficiently</p>
            </div>
            <div class="notifications" id="notif-bell" onclick="window.location.href='notification.php'">
                <i class="fas fa-bell"></i>
                <span class="badge" style="display: none;"><?php echo $pending_tasks; ?></span>
            </div>
        </header>

        <section class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Tasks</h3>
                <p><?php echo $total_tasks; ?></p>
            </div>
            <div class="stat-card completed">
                <h3>Completed</h3>
                <p><?php echo $completed_tasks; ?></p>
            </div>
            <div class="stat-card pending">
                <h3>Incomplete</h3>
                <p><?php echo $pending_tasks; ?></p>
            </div>
        </section>

        <section class="task-container">
            <h2>Workspace Tasks</h2>
            
            <div class="add-task-form">
                <input type="text" id="task-input" placeholder="What needs to be done?" autocomplete="off">
                <button type="button" id="add-btn" class="login-btn" style="width: auto; padding: 12px 30px;">Add Task</button>
            </div>

            <div class="task-list">
                <?php if (mysqli_num_rows($allTasksQuery) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($allTasksQuery)): ?>
                        <div class="task-item">
                            <div class="task-info">
                                <input type="checkbox" class="status-checkbox" data-id="<?php echo $row['id']; ?>" data-status="<?php echo $row['status']; ?>" <?php echo ($row['status'] == 'completed') ? 'checked' : ''; ?>>
                                <span class="task-text" style="<?php echo ($row['status'] == 'completed') ? 'text-decoration: line-through; color: #bdbdbd;' : ''; ?>">
                                    <?php echo htmlspecialchars($row['task_text']); ?>
                                </span>
                            </div>
                            <div class="actions">
                                <i class="fas fa-edit edit-icon" data-id="<?php echo $row['id']; ?>" style="color: #3949ab;"></i>
                                <i class="fas fa-trash-alt delete-icon" data-id="<?php echo $row['id']; ?>" style="color: #e74c3c;"></i>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #9e9e9e; margin-top: 20px;">No tasks available. Add some tasks to get started!</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<div id="notification-toast" class="toast" style="display: none;">
    <div class="toast-header">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>System Reminder</strong>
    </div>
    <div class="toast-body">
         Attention: You still have <span>0</span> incomplete tasks for this week!
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // ========================================================
    // LIVE PUSH NOTIFICATION SYSTEM (SHORT POLLING)
    // نظام الإشعارات الفورية الحية عبر الفحص الدوري المتزامن كل 5 ثوانٍ
    // ========================================================
    function pushNotificationCheck() {
        $.get("check_notifications.php", function(pendingCount) {
            let count = parseInt(pendingCount.trim());
            
            if (count > 0) {
                // Smoothly update counters and slide in the notification popup
                // تحديث العدادات الرقمية وإظهار كرت التنبيه والاشعارات حركياً بنعومة
                $(".sidebar .badge, .notifications .badge").text(count).show();
                $("#notification-toast span").text(count);
                $("#notification-toast").fadeIn(500); 
            } else {
                // Automatically hide notification interfaces if zero pending tasks found
                // إخفاء واجهات الإشعارات تلقائياً في حال إنجاز المهام بالكامل
                $(".sidebar .badge, .notifications .badge").hide();
                $("#notification-toast").fadeOut(500);
            }
        });
    }

    // Run the push notification script immediately upon compile
    // تشغيل نظام فحص الإشعارات فوراً عند تحميل الصفحة
    pushNotificationCheck();

    // Re-trigger the background API automated check interval every 5 seconds
    // إعادة إطلاق الفحص الخلفي التلقائي بشكل دوري مستمر حياً كل 5 ثوانٍ
    setInterval(pushNotificationCheck, 5000); 


    // Add task asynchronously via AJAX
   $("#add-btn").click(function(){

    let taskValue = $("#task-input").val();

    if(taskValue == ""){
        alert("Please enter a task");
    }
    else{
        $.post("index.php",
        {
            add_task_text: taskValue
        },
        function(data){
            location.reload();
        });
    }

});

    // Toggle task status checkbox asynchronously via AJAX
    $(".status-checkbox").change(function() {
        let id = $(this).data("id");
        let status = $(this).data("status");
        $.post("index.php", { toggle_id: id, current_status: status }, function() {
            location.reload();
        });
    });

    // Launches a prompt dialogue allowing dynamic text updates
    $(document).on("click", ".actions .edit-icon", function() {
        let id = $(this).data("id");
        let currentText = $(this).closest(".task-item").find(".task-text").text().trim();
        let customTask = prompt("Edit your task description:", currentText);
        
        if (customTask !== null && customTask.trim() !== "") {
            $.post("index.php", { 
                edit_id: id, 
                new_task: customTask.trim() 
            }, function(data) {
                location.reload();
            });
        }
    });

    // Delete task entry asynchronously via AJAX
   $(".delete-icon").click(function(){
    alert("Are you sure you want to delete this task?.");

            let id = $(this).data("id");

            $.post("index.php",
            {
                delete_id: id
            },
            function(data){
                location.reload();
            });
        });

});
</script>

</body>
</html>
