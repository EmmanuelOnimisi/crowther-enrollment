<?php
// logout.php - Destroy session and redirect to home
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear any local storage that might remember login
echo '<!DOCTYPE html>
<html>
<head>
    <script>
        // Clear any stored form data
        if (window.localStorage) {
            localStorage.clear();
        }
        if (window.sessionStorage) {
            sessionStorage.clear();
        }
        // Redirect to home
        window.location.href = "index.php";
    </script>
</head>
<body>
    <p>Logging out...</p>
</body>
</html>';
exit();
?>