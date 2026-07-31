<?php
/**
 * config-sample.php - Database Configuration Template
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to config.php
 * 2. Update the database credentials below with your own
 * 3. DO NOT upload config.php to GitHub (it's already in .gitignore)
 */

// =====================================================
// DATABASE CONFIGURATION
// =====================================================

// Database host (usually localhost or 127.0.0.1)
$db_host = 'localhost';

// Database username (default for XAMPP/Laragon is 'root')
$db_user = 'root';

// Database password (default for XAMPP/Laragon is blank '')
$db_pass = '';

// Database name (create this in phpMyAdmin)
$db_name = 'crowther_enrollment_db';

// =====================================================
// CREATE CONNECTION
// =====================================================

// Create connection using MySQLi
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8 for proper character encoding
mysqli_set_charset($conn, "utf8mb4");

// Optional: Set timezone (for Nigeria)
// date_default_timezone_set('Africa/Lagos');

// =====================================================
// EXAMPLE USAGE
// =====================================================
// Include this file in your PHP pages:
// include 'includes/config.php';
//
// Then use $conn for database queries:
// $result = mysqli_query($conn, "SELECT * FROM users");
//
// Close connection at the end:
// mysqli_close($conn);
?>