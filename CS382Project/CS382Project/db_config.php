<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "yic_todo_db";
$p=3307;

$conn = mysqli_connect($host, $user, $pass, $dbname,$p);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>