<?php
session_start();
include 'db_config.php';

$uid = $_SESSION['user_id'];

class TaskSystem {

    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function addTask($uid, $task) {
        mysqli_query($this->conn, "INSERT INTO tasks (user_id, task_text, status)
                                   VALUES ('$uid', '$task', 'pending')");
    }

    public function deleteTask($uid, $id) {
        mysqli_query($this->conn, "DELETE FROM tasks 
                                   WHERE id='$id' AND user_id='$uid'");
    }

    public function editTask($uid, $id) {
        mysqli_query($this->conn, "UPDATE tasks 
                                   SET task_text='Edited Task'
                                   WHERE id='$id' AND user_id='$uid'");
    }

    public function completeTask($uid, $id) {
        mysqli_query($this->conn, "UPDATE tasks 
                                   SET status='completed'
                                   WHERE id='$id' AND user_id='$uid'");
    }
}

$taskSystem = new TaskSystem($conn);

if (isset($_POST['task'])) {
    $taskSystem->addTask($uid, $_POST['task']);
    exit();
}

if (isset($_POST['delete_id'])) {
    $taskSystem->deleteTask($uid, $_POST['delete_id']);
    exit();
}

if (isset($_POST['edit_id'])) {
    $taskSystem->editTask($uid, $_POST['edit_id']);
    exit();
}

if (isset($_POST['complete_id'])) {
    $taskSystem->completeTask($uid, $_POST['complete_id']);
    exit();
}

$res1 = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id='$uid'");
$total = mysqli_num_rows($res1);

$res2 = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id='$uid' AND status='completed'");
$completed = mysqli_num_rows($res2);

$pending = $total - $completed;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YIC To-Do System | Phase 2</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
                <li class="nav-item active" id="nav-dashboard">
                    <a href="#"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li class="nav-item" id="nav-tasks">
                    <a href="tasks.php"><i class="fas fa-tasks"></i> My Tasks</a>
                </li>
                <li class="nav-item" id="nav-weekly">
                    <a href="weekly.php"><i class="fas fa-chart-line"></i> Weekly Progress</a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header>
            <div class="header-title">
                <h1>Dashboard</h1>
                <p>Track your academic tasks efficiently</p>
            </div>
                   <div class="notifications" id="notif-bell" onclick="window.location.href='notification.php'">
                    <i class="fas fa-bell"></i>
                 <span class="badge"><?php echo $pending; ?></span>
            </div>
        </header>

        <section id="dashboard-section" class="content-section">
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Tasks</h3>
                    <p id="total-tasks-count"><?php echo $total; ?></p>
                </div>
                <div class="stat-card completed">
                    <h3>Completed</h3>
                    <p id="completed-tasks-count"><?php echo $completed; ?></p>
                </div>
                <div class="stat-card pending">
                    <h3>Incomplete</h3>
                    <p id="incomplete-tasks-count"><?php echo $pending; ?></p>
                </div>
            </div>
        </section>

        <section id="tasks-section" class="content-section">
            <div class="task-container">
                <div class="add-task-form">
                    <input type="text" placeholder="What needs to be done?" id="task-input">
                    <button id="add-btn">Add Task</button>
                </div>

                <div class="task-list">
                    <h2>Current Task List</h2>
                    <ul id="items-list">

                    <?php
                    $query = "SELECT * FROM tasks WHERE user_id='$uid' ORDER BY id DESC";
                    $result = mysqli_query($conn, $query);

                    while($row = mysqli_fetch_assoc($result)) {
                        $status = ($row['status'] == 'completed') ? 'checked' : '';

                        echo '
                        <li class="task-item">
                            <div class="task-info">
                                <input type="checkbox" class="check-task" data-id="'.$row['id'].'" '.$status.'>
                                <span class="task-text">'.$row['task_text'].'</span>
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
            </div>
        </section>

        <section id="weekly-section" class="content-section" style="display: none;">
            <div class="weekly-stats-container">
                <h2><i class="fas fa-chart-bar"></i> Weekly Performance Analysis</h2>
                <div class="chart-box">
                    <p>Weekly Achievement Level</p>
                    <div class="progress-bar-container">
                        <div class="progress-fill" style="width: 66%;">66% Completed</div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<div id="notification-toast" class="toast">
    <div class="toast-header">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>System Reminder</strong>
    </div>
    <div class="toast-body">
            Attention: You still have <span id="toast-count"><?php echo $pending; ?></span> incomplete tasks for this week!
    </div>
</div>

<script>
$(document).ready(function(){

    $("#add-btn").click(function(){
        let taskValue = $("#task-input").val();

        if(taskValue == ""){
            alert("Please enter a task");
        }
        else{
            $.post("index.php",
            {
                task: taskValue
            },
            function(data){
                location.reload();
            });
        }
    });

    $(".delete-icon").click(function(){
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
    $(this).parents(".task-item").text("Edited Task");
    $.post("index.php",
    {
        edit_id: id,
        new_task: "Edited Task"
    },
    function(data){
        location.reload();
    });

});

    $(".check-task").click(function(){
        let id = $(this).data("id");
        $.post("index.php",
        {
            complete_id: id
        },
        function(data){
            location.reload();
        });
    });

 

});
</script>

</body>
</html>