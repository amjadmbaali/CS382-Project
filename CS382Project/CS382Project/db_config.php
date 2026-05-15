<?php
// Database credentials
$host = "localhost";
$user = "root";
$pass = ""; // Default for Laragon is empty
$dbname = "yic_todo_db";

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check if it works
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>