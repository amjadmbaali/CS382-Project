<?php
session_start();
include 'db_config.php'; // هذا الجسر اللي يربطك بالقاعدة [cite: 82, 125]

// نأخذ رقم المستخدم اللي سجل دخول عشان نعرض مهامه هو بس
$uid = $_SESSION['user_id']; 
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
            </section>
        </main>
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
        let task = $("#task-input").val();
        if(task == ""){
            alert("Please enter a task");
        }
        else{
            $.post("add_task.php",
            {
                task: task
            },
            function(data){
               $("#items-list").hide();      
              $("#items-list").html(data);  
              $("#items-list").fadeIn();
            });
        }

    });

});
</script>
</body>
</html>
