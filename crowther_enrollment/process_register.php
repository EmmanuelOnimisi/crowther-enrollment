<?php
// process_register.php - Save new parent account with password strength validation
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // =============================================
    // SERVER-SIDE PASSWORD VALIDATION
    // =============================================
    $errors = [];
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        header('Location: register.php?error=mismatch');
        exit();
    }
    
    // Check minimum length
    if (strlen($password) < 8) {
        header('Location: register.php?error=weak');
        exit();
    }
    
    // Check for uppercase
    if (!preg_match('/[A-Z]/', $password)) {
        header('Location: register.php?error=weak');
        exit();
    }
    
    // Check for lowercase
    if (!preg_match('/[a-z]/', $password)) {
        header('Location: register.php?error=weak');
        exit();
    }
    
    // Check for number
    if (!preg_match('/[0-9]/', $password)) {
        header('Location: register.php?error=weak');
        exit();
    }
    
    // Check for special character
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};":\'\\|,.<>\/?]/', $password)) {
        header('Location: register.php?error=weak');
        exit();
    }
    
    // Check if email already exists
    $check_query = "SELECT id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        header('Location: register.php?error=exists');
        exit();
    }
    
    // Hash password using MD5 (for demo)
    $hashed_password = md5($password);
    
    // Insert into database
    $query = "INSERT INTO users (fullname, email, phone, address, password, role) 
              VALUES ('$fullname', '$email', '$phone', '$address', '$hashed_password', 'parent')";
    
    if (mysqli_query($conn, $query)) {
        header('Location: login.php?registered=1');
        exit();
    } else {
        header('Location: register.php?error=db');
        exit();
    }
    
    mysqli_close($conn);
} else {
    header('Location: register.php');
    exit();
}
?>