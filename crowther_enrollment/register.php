<?php
// register.php - Parent/Guardian Registration with Password Strength
session_start();

// DO NOT redirect if already logged in - just show registration form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Crowther Memorial College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 120px auto 60px;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        .register-container h1 {
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
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 60px;
        }
        .register-btn {
            width: 100%;
            background: #1a3c5e;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
        .register-btn:hover {
            background: #ffc107;
            color: #1a3c5e;
        }
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
        .login-link a {
            color: #1a3c5e;
            text-decoration: none;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            display: none;
        }
        .password-hint {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }
        .password-requirements {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 0.85rem;
        }
        .password-requirements ul {
            list-style: none;
            padding-left: 0;
            margin: 5px 0;
        }
        .password-requirements ul li {
            padding: 3px 0;
            color: #dc3545;
        }
        .password-requirements ul li.valid {
            color: #28a745;
        }
        .password-requirements ul li i {
            margin-right: 5px;
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
            <li><a href="index.php#admission-info"><i class="fas fa-info-circle"></i> Admission Info</a></li>
            <li><a href="index.php#contact"><i class="fas fa-envelope"></i> Contact</a></li>
            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
        </ul>
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>

<div class="register-container">
    <h1><i class="fas fa-user-plus"></i> Parent/Guardian Registration</h1>
    
    <div id="errorMsg" class="error-msg"></div>
    
    <form action="process_register.php" method="POST" autocomplete="off" id="registerForm">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Full Name</label>
            <input type="text" name="fullname" id="fullname" autocomplete="off" required placeholder="Enter your full name">
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" name="email" id="email" autocomplete="off" required placeholder="you@example.com">
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-phone"></i> Phone Number</label>
            <input type="tel" name="phone" id="phone" autocomplete="off" required placeholder="0803 123 4567">
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-home"></i> Home Address</label>
            <textarea name="address" id="address" rows="2" placeholder="Enter your home address"></textarea>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" id="password" autocomplete="new-password" required>
            
            <!-- Password Strength Requirements -->
            <div class="password-requirements" id="passwordRequirements">
                <p><strong>Password must contain:</strong></p>
                <ul>
                    <li id="reqLength"><i class="fas fa-circle"></i> At least 8 characters</li>
                    <li id="reqUppercase"><i class="fas fa-circle"></i> At least 1 uppercase letter (A-Z)</li>
                    <li id="reqLowercase"><i class="fas fa-circle"></i> At least 1 lowercase letter (a-z)</li>
                    <li id="reqNumber"><i class="fas fa-circle"></i> At least 1 number (0-9)</li>
                    <li id="reqSpecial"><i class="fas fa-circle"></i> At least 1 special character (!@#$%^&* etc.)</li>
                </ul>
            </div>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-lock"></i> Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" autocomplete="off" required>
            <div id="confirmMsg" style="font-size: 0.85rem; margin-top: 5px;"></div>
        </div>
        
        <button type="submit" class="register-btn"><i class="fas fa-user-check"></i> Register Account</button>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </form>
</div>

<footer>
    <div class="container">
        <p>&copy; 2026 Crowther Memorial College, Lokoja. All rights reserved.</p>
    </div>
</footer>

<script>
    // Password validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const confirmMsg = document.getElementById('confirmMsg');
    
    // Requirements elements
    const reqLength = document.getElementById('reqLength');
    const reqUppercase = document.getElementById('reqUppercase');
    const reqLowercase = document.getElementById('reqLowercase');
    const reqNumber = document.getElementById('reqNumber');
    const reqSpecial = document.getElementById('reqSpecial');
    
    // Check password strength in real-time
    password.addEventListener('input', function() {
        const value = this.value;
        
        // Check length (at least 8 characters)
        if (value.length >= 8) {
            reqLength.classList.add('valid');
            reqLength.innerHTML = '<i class="fas fa-check-circle"></i> At least 8 characters';
        } else {
            reqLength.classList.remove('valid');
            reqLength.innerHTML = '<i class="fas fa-circle"></i> At least 8 characters';
        }
        
        // Check uppercase
        if (/[A-Z]/.test(value)) {
            reqUppercase.classList.add('valid');
            reqUppercase.innerHTML = '<i class="fas fa-check-circle"></i> At least 1 uppercase letter (A-Z)';
        } else {
            reqUppercase.classList.remove('valid');
            reqUppercase.innerHTML = '<i class="fas fa-circle"></i> At least 1 uppercase letter (A-Z)';
        }
        
        // Check lowercase
        if (/[a-z]/.test(value)) {
            reqLowercase.classList.add('valid');
            reqLowercase.innerHTML = '<i class="fas fa-check-circle"></i> At least 1 lowercase letter (a-z)';
        } else {
            reqLowercase.classList.remove('valid');
            reqLowercase.innerHTML = '<i class="fas fa-circle"></i> At least 1 lowercase letter (a-z)';
        }
        
        // Check number
        if (/[0-9]/.test(value)) {
            reqNumber.classList.add('valid');
            reqNumber.innerHTML = '<i class="fas fa-check-circle"></i> At least 1 number (0-9)';
        } else {
            reqNumber.classList.remove('valid');
            reqNumber.innerHTML = '<i class="fas fa-circle"></i> At least 1 number (0-9)';
        }
        
        // Check special character
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
            reqSpecial.classList.add('valid');
            reqSpecial.innerHTML = '<i class="fas fa-check-circle"></i> At least 1 special character (!@#$%^&* etc.)';
        } else {
            reqSpecial.classList.remove('valid');
            reqSpecial.innerHTML = '<i class="fas fa-circle"></i> At least 1 special character (!@#$%^&* etc.)';
        }
        
        // Check confirm password match
        checkConfirmPassword();
    });
    
    // Check confirm password match
    function checkConfirmPassword() {
        if (confirmPassword.value.length > 0) {
            if (password.value === confirmPassword.value) {
                confirmMsg.innerHTML = '<span style="color: #28a745;"><i class="fas fa-check-circle"></i> Passwords match</span>';
            } else {
                confirmMsg.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Passwords do not match</span>';
            }
        } else {
            confirmMsg.innerHTML = '';
        }
    }
    
    confirmPassword.addEventListener('input', checkConfirmPassword);
    
    // Form validation before submit
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const value = password.value;
        let isValid = true;
        let errors = [];
        
        // Check all requirements
        if (value.length < 8) {
            isValid = false;
            errors.push('Password must be at least 8 characters');
        }
        if (!/[A-Z]/.test(value)) {
            isValid = false;
            errors.push('Password must contain at least 1 uppercase letter');
        }
        if (!/[a-z]/.test(value)) {
            isValid = false;
            errors.push('Password must contain at least 1 lowercase letter');
        }
        if (!/[0-9]/.test(value)) {
            isValid = false;
            errors.push('Password must contain at least 1 number');
        }
        if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
            isValid = false;
            errors.push('Password must contain at least 1 special character');
        }
        
        // Check if passwords match
        if (password.value !== confirmPassword.value) {
            isValid = false;
            errors.push('Passwords do not match');
        }
        
        if (!isValid) {
            e.preventDefault();
            const errorMsg = document.getElementById('errorMsg');
            errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Please fix the following:<br>' + errors.join('<br>');
            errorMsg.style.display = 'block';
            errorMsg.scrollIntoView({ behavior: 'smooth' });
        }
    });
    
    // Clear form fields on page load
    window.addEventListener('load', function() {
        document.getElementById('fullname').value = '';
        document.getElementById('email').value = '';
        document.getElementById('phone').value = '';
        document.getElementById('address').value = '';
        document.getElementById('password').value = '';
        document.getElementById('confirm_password').value = '';
        
        // Check for error from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        if (error === 'mismatch') {
            const errorMsg = document.getElementById('errorMsg');
            errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Passwords do not match. Please try again.';
            errorMsg.style.display = 'block';
        } else if (error === 'weak') {
            const errorMsg = document.getElementById('errorMsg');
            errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Password is too weak. Please follow the requirements.';
            errorMsg.style.display = 'block';
        } else if (error === 'exists') {
            const errorMsg = document.getElementById('errorMsg');
            errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Email already registered. Please login instead.';
            errorMsg.style.display = 'block';
        } else if (error === 'db') {
            const errorMsg = document.getElementById('errorMsg');
            errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Registration failed. Please try again.';
            errorMsg.style.display = 'block';
        }
    });
</script>

</body>
</html>