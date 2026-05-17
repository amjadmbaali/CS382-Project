<?php
// Start session to access stored login credentials
// بدء الجلسة (Session) للوصول إلى بيانات تسجيل الدخول المخزنة
session_start();

// Include database configuration connection
// تضمين ملف إعدادات قاعدة البيانات لإنشاء الاتصال
include 'db_config.php'; 

// Fetch the authenticated user ID from current session
// جلب معرف المستخدم الحالي المسجل من الجلسة النشطة
$uid = $_SESSION['user_id']; 

// OOP Class created to separate the task viewing layer
// كلاس قائم على البرمجة الشيئية تم إنشاؤه لفصل طبقة استعراض وجلب المهام
class TaskViewer {

    private $conn;

    // Constructor to receive the active database bridge
    // الدالة البنائية لاستقبال جسر اتصال قاعدة البيانات النشط
    public function __construct($connection) {
        $this->conn = $connection;
    }

    // Method to query and fetch all tasks for specific user ordered by newest
    // دالة للاستعلام وجلب جميع مهام مستخدم معين مرتبة من الأحدث للأقدم
    public function getTasks($uid) {

        $query = "SELECT * FROM tasks
                  WHERE user_id='$uid'
                  ORDER BY id DESC";

        return mysqli_query($this->conn, $query);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - YIC To-Do List</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img src="YICLogo.jpg" alt="YIC Logo" class="logo-img">
                <h3>YIC To-Do</h3>
            </div>
            <nav>
                <ul>
                    <li class="nav-item"><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="nav-item active"><a href="tasks.php"><i class="fas fa-tasks"></i> My Tasks</a></li>
                    <li class="nav-item"><a href="weekly.php"><i class="fas fa-chart-line"></i> Weekly Progress</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header>
                <h1>My Tasks</h1>
                <p>Manage your daily activities</p>
            </header>

            <section class="task-container">
                <div class="add-task-form">
                    <input type="text" placeholder="Add a new task..." id="task-input">
                    <button id="add-btn">Add Task</button>
                </div>

                <div class="task-list">
                    <ul id="items-list">
                        <?php
                        // Instantiate TaskViewer and query records dynamically via loop
                        // إنشاء كائن استعراض المهام وطباعة السجلات ديناميكياً عبر حلقة الدوران
                        $taskViewer = new TaskViewer($conn);
                        $result = $taskViewer->getTasks($uid);

                        while($row = mysqli_fetch_assoc($result)) {
                            $status = ($row['status'] == 'completed') ? 'checked' : '';
                            
                            echo '
                            <li class="task-item">
                                <div class="task-info">
                <input type="checkbox" class="check-task" data-id="'.$row['id'].'" data-status="'.$row['status'].'" '.$status.'>                                    <span class="task-text">'.$row['task_text'].'</span>
                                </div>
                                <div class="actions">
                                    <i class="fas fa-edit edit-icon" data-id="'.$row['id'].'"></i>
                                   <i class="fas fa-trash delete-icon" data-id="'.$row['id'].'"></i>
                                </div>
                            </li>';
                        }
                        ?>
                    </ul>
                </div>
            </section>
        </main>
    </div>

    <script>
    // jQuery code block handling active UI events and routing AJAX background posts
    // كود الجي كويري للتحكم بأحداث الواجهة وتوجيه طلبات الأجاكس البرمجية في الخلفية
$(document).ready(function(){

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

    $(".delete-icon").click(function(){
        alert("Are you sure you want to delete this task?");

        let id = $(this).data("id");

        $.post("index.php",
        {
            delete_id: id
        },
        function(data){
            location.reload();
        });
    });

    $(".edit-icon").click(function(){

        let id = $(this).data("id");
        let newTask = prompt("Edit Task");

        if(newTask != "" && newTask != null){
            $.post("index.php",
            {
                edit_id: id,
                new_task: newTask
            },
            function(data){
                location.reload();
            });
        }

    });

    $(".check-task").click(function(){

        let id = $(this).data("id");
    let status = $(this).data("status");

        $(this).next().css("color","gray");

        $.post("index.php",
        {
            toggle_id: id,
            current_status: status
        },
        function(data){
            location.reload();
        });

    });

});
    </script>
</body>
</html>
