<?php
// process_login.php - Authenticate user from database
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']); // MD5 hash for demo
    
    // Check if user exists
    $query = "SELECT id, fullname, email, role FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Redirect based on role
        if ($user['role'] == 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Login Failed</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <style>
                body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
                .error-box { background: white; padding: 2rem; border-radius: 10px; text-align: center; max-width: 400px; }
                .error { color: #dc3545; }
                .btn { display: inline-block; margin-top: 1rem; padding: 10px 20px; background: #1a3c5e; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <i class="fas fa-times-circle error" style="font-size: 3rem;"></i>
                <h2 class="error">Invalid Credentials</h2>
                <p>The email or password you entered is incorrect.</p>
                <p><strong>Demo parent:</strong> parent@example.com / password123</p>
                <p><strong>Demo admin:</strong> admin@crowther.edu.ng / admin123</p>
                <a href="login.php" class="btn">Try Again</a>
            </div>
        </body>
        </html>
        <?php
    }
    
    mysqli_close($conn);
} else {
    header('Location: login.php');
    exit();
}
?>