<?php
// status.php - Check application status (front-end mockup)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status | Crowther Memorial College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .status-container {
            max-width: 800px;
            margin: 120px auto 60px;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        .track-form {
            background: #f4f6f9;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .track-form input {
            width: 70%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .track-form button {
            padding: 10px 20px;
            background: #1a3c5e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
        </ul>
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>

<div class="status-container">
    <h1><i class="fas fa-search"></i> Track Application Status</h1>
    <p>Enter your Application ID to check the status of your enrolment.</p>
    
    <div class="track-form">
        <input type="text" placeholder="Enter Application ID (e.g., CMC20260429001)">
        <button><i class="fas fa-search"></i> Track Status</button>
    </div>
    
    <div class="result" style="display: none;">
        <!-- This will show after tracking -->
    </div>
    
    <div class="info">
        <p><i class="fas fa-info-circle"></i> Don't have an Application ID? <a href="apply.php">Apply now</a></p>
        <p><i class="fas fa-envelope"></i> For enquiries, contact admissions@crowthermemorial.edu.ng</p>
    </div>
</div>

<footer>
    <div class="container">
        <p>&copy; 2026 Crowther Memorial College, Lokoja. All rights reserved.</p>
    </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>