<?php
// login.php - Parent/Student Login with Forgot Password link
session_start();

// Force destroy any existing session on login page
if (isset($_SESSION['user_id'])) {
    session_destroy();
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Crowther Memorial College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            max-width: 450px;
            margin: 120px auto 60px;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        .login-container h1 {
            color: #1a3c5e;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .login-btn {
            width: 100%;
            background: #1a3c5e;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
        .login-btn:hover {
            background: #ffc107;
            color: #1a3c5e;
        }
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
        .register-link a {
            color: #1a3c5e;
            text-decoration: none;
        }
        .forgot-link {
            text-align: right;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
        }
        .forgot-link a {
            color: #ffc107;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .forgot-link a:hover {
            text-decoration: underline;
        }
        .admin-note {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #ddd;
            font-size: 0.85rem;
            color: #666;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            display: none;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            display: none;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <i class="fas fa-school"></i>
            <span>Crowther Memorial College</span>
        </div>
        <ul class="nav-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="apply.php"><i class="fas fa-file-alt"></i> Apply Now</a></li>
            <li><a href="index.php#admission-info"><i class="fas fa-info-circle"></i> Admission Info</a></li>
            <li><a href="index.php#contact"><i class="fas fa-envelope"></i> Contact</a></li>
            <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
        </ul>
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>

<div class="login-container">
    <h1><i class="fas fa-sign-in-alt"></i> Parent/Student Login</h1>
    
    <div id="errorMsg" class="error-msg"></div>
    <div id="successMsg" class="success-msg"></div>
    
    <form action="process_login.php" method="POST" autocomplete="off">
        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" name="email" id="email" autocomplete="off" required placeholder="your@email.com">
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" id="password" autocomplete="new-password" required>
        </div>
        
        <!-- ✅ FORGOT PASSWORD LINK ADDED HERE -->
        <div class="forgot-link">
            <a href="reset_password.php"><i class="fas fa-key"></i> Forgot Password?</a>
        </div>
        
        <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login to Dashboard</button>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        
       
    </form>
</div>

<footer>
    <div class="container">
        <p>&copy; 2026 Crowther Memorial College, Lokoja. All rights reserved.</p>
    </div>
</footer>

<script>
    window.addEventListener('load', function() {
        document.getElementById('email').value = '';
        document.getElementById('password').value = '';
        
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        if (error === 'invalid') {
            const errorMsg = document.getElementById('errorMsg');
            errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Invalid email or password. Please try again.';
            errorMsg.style.display = 'block';
        }
        
        const registered = urlParams.get('registered');
        if (registered === '1') {
            const successMsg = document.getElementById('successMsg');
            successMsg.innerHTML = '<i class="fas fa-check-circle"></i> Registration successful! Please login with your credentials.';
            successMsg.style.display = 'block';
        }
    });
</script>

</body>
</html>