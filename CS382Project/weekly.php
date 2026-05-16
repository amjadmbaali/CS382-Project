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
// 1. STATISTICAL QUERIES (DYNAMIC BACKEND LOGIC)
// ========================================================

// Query 1: Calculate the overall completion percentage for the progress bar
// الاستعلام الأول: حساب النسبة المئوية الإجمالية للمهام المكتملة لشريط التقدم
$progressQuery = mysqli_query($conn, "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed FROM tasks WHERE user_id = '$uid'");
$progressRow = mysqli_fetch_assoc($progressQuery);
$totalTasks = $progressRow['total'];
$completedTasks = $progressRow['completed'];

// Calculate percentage smoothly / حساب النسبة المئوية بسلاسة لضمان عدم القسمة على صفر
$overallRate = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100) : 0;

// Query 2: Fetch group summary of created, completed, and incomplete tasks per day from DB
// الاستعلام الثاني: جلب ملخص ومجموع المهام المنشأة، المكتملة، وغير المكتملة حياً لكل يوم من الداتا بيس
$statsResult = mysqli_query($conn, "SELECT 
            DAYNAME(created_at) AS day_name,
            COUNT(*) AS total_created,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS total_completed,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS total_incomplete
          FROM tasks
          WHERE user_id = '$uid'
          GROUP BY DAYNAME(created_at)
          ORDER BY FIELD(day_name, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Progress - YIC To-Do List</title>
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
                    <li class="nav-item"><a href="tasks.php"><i class="fas fa-tasks"></i> My Tasks</a></li>
                    <li class="nav-item active"><a href="weekly.php"><i class="fas fa-chart-line"></i> Weekly Progress</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header>
                <div class="header-title">
                    <h1>Weekly Performance</h1>
                    <p>Detailed analysis of your task completion rates</p>
                </div>
            </header>

            <section class="weekly-analysis">
                <div class="chart-box">
                    <h3>Overall Completion Rate</h3>
                    <div class="progress-bar-container">
                        <div class="progress-fill" style="width: <?php echo $overallRate; ?>%;"><?php echo $overallRate; ?>% Achievement</div>
                    </div>
                </div>

                <div class="table-container">
                    <h2>Weekly Activity Log</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Tasks Created</th>
                                <th>Completed</th>
                                <th>Incomplete</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="weekly-table">
                            <?php if (mysqli_num_rows($statsResult) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($statsResult)): 
                                    // Dynamic badge assignment evaluation logic
                                    // منطق برمجي ذكي لتحديد لون ونوع شارة الحالة ديناميكياً بناءً على إنجاز اليوم
                                    if ($row['total_incomplete'] == 0) {
                                        $badgeClass = "perfect";
                                        $badgeText = "Excellent";
                                    } elseif ($row['total_completed'] >= $row['total_incomplete']) {
                                        $badgeClass = "warning";
                                        $badgeText = "Good Progress";
                                    } else {
                                        $badgeClass = "danger";
                                        $badgeText = "Action Required";
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $row['day_name']; ?></td>
                                        <td><?php echo $row['total_created']; ?></td>
                                        <td class="text-complete"><?php echo $row['total_completed']; ?></td>
                                        <td class="text-incomplete"><?php echo $row['total_incomplete']; ?></td>
                                        <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="color: #9e9e9e;">No database activity recorded for this week yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>