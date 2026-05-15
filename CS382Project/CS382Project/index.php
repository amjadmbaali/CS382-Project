<?php
session_start();
include 'db_config.php';
$uid = $_SESSION['user_id']; // The logged-in user's ID

// 1. Count all tasks
$res1 = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id='$uid'");
$total = mysqli_num_rows($res1);

// 2. Count completed tasks
$res2 = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id='$uid' AND status='completed'");
$completed = mysqli_num_rows($res2);

// 3. Simple math for incomplete
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
                <div class="notifications" id="notif-bell">
                    <i class="fas fa-bell"></i>
                    <span class="badge">4</span> </div>
            </header>

            <section id="dashboard-section" class="content-section">
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Total Tasks</h3>
                        <p id="total-tasks-count">12</p>
                    </div>
                    <div class="stat-card completed">
                        <h3>Completed</h3>
                        <p id="completed-tasks-count">8</p>
                    </div>
                    <div class="stat-card pending">
                        <h3>Incomplete</h3>
                        <p id="incomplete-tasks-count">4</p>
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
    // Check if task is finished to mark the checkbox
    $status = ($row['status'] == 'completed') ? 'checked' : '';
    
    echo '
    <li class="task-item">
        <div class="task-info">
            <input type="checkbox" '.$status.'>
            <span class="task-text">'.$row['task_text'].'</span>
        </div>
        <div class="actions">
            <i class="fas fa-edit edit-icon"></i>
            <i class="fas fa-trash delete-icon"></i>
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
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Tasks Created</th>
                                <th>Tasks Finished</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sunday</td>
                                <td>4</td>
                                <td>4</td>
                                <td><span class="status-badge perfect">Excellent</span></td>
                            </tr>
                            <tr>
                                <td>Monday</td>
                                <td>6</td>
                                <td>2</td>
                                <td><span class="status-badge warning">Needs Work</span></td>
                            </tr>
                        </tbody>
                    </table>
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
            Attention: You still have <span id="toast-count">4</span> incomplete tasks for this week!
        </div>
    </div>
    <script>
$(document).ready(function(){
    $(".delete-icon").click(function(){
        $(this).parents(".task-item").remove();
    });

   $(".edit-icon").click(function(){
        let oldText =$(this).parents(".task-item").find(".task-text").text();
        let newText = prompt("Edit task:", oldText);
        if(newText != "" && newText != null){
            $(this).parents(".task-item").find(".task-text").text(newText);
        }
    });

    $("input[type='checkbox']").click(function(){
         let element = $(this).next();
         let text = element.text();
         element.css("color","gray");
    });

    $("#add-btn").click(function(){
        let taskValue = $("#task-input").val();
        if(taskValue == ""){
            alert("Please enter a task");
        }
        else{
            $.post("add_task.php",
            {
                task: taskValue
            },
            function(data){
                $("#items-list").html(data);
                $("#task-input").val("");
            });
        }

    });

    $("#notif-bell").click(function(){
        $.get("get_notification.php", function(data){
            $("#toast-count").text(data);
        });
    });

});
</script>

</body>
</html>
