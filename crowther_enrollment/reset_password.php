<?php
// reset_password.php - Simple password reset
session_start();
include 'includes/config.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $check_query = "SELECT id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $error = "Email address not found in our system.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed_password = md5($new_password);
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
        
        if (mysqli_query($conn, $update_query)) {
            $success = "Password reset successful! You can now login with your new password.";
        } else {
            $error = "Failed to reset password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | Crowther Memorial College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; }
        .reset-container { max-width: 450px; margin: 120px auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); }
        .reset-container h1 { color: #1a3c5e; text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .reset-btn { width: 100%; background: #1a3c5e; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; }
        .reset-btn:hover { background: #ffc107; color: #1a3c5e; }
        .back-link { text-align: center; margin-top: 1rem; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 1rem; }
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="reset-container">
        <h1><i class="fas fa-key"></i> Reset Password</h1>
        
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
            <div class="back-link"><a href="login.php">← Back to Login</a></div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="reset-btn">Reset Password</button>
                <div class="back-link"><a href="login.php">← Back to Login</a></div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>