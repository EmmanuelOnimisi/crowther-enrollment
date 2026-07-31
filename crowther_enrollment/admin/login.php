<?php
// admin/login.php - Secret Admin Login (not linked publicly)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Crowther Memorial College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a3c5e 0%, #2c5a8c 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .admin-login {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .admin-login h2 {
            color: #1a3c5e;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .admin-login input {
            width: 100%;
            padding: 12px;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .admin-login button {
            width: 100%;
            padding: 12px;
            background: #1a3c5e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .admin-login button:hover {
            background: #ffc107;
            color: #1a3c5e;
        }
    </style>
</head>
<body>
    <div class="admin-login">
        <h2><i class="fas fa-user-shield"></i> Admin Access</h2>
        <form action="dashboard.php" method="POST">
            <input type="text" name="username" placeholder="Admin Username" required>
            <input type="password" name="password" placeholder="Admin Password" required>
            <button type="submit"><i class="fas fa-lock-open"></i> Login to Admin Panel</button>
        </form>
    </div>
</body>
</html>