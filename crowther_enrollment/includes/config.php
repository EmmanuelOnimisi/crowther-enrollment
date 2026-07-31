<?php
// includes/config.php - Database configuration

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'crowther_enrollment_db';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// For using prepared statements with MySQLi
// You can also use PDO if preferred, but stick with MySQLi for now
?>