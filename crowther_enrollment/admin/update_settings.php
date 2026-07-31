<?php
// admin/update_settings.php - Update system settings including fee structure
session_start();
include '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $settings = [
        'current_session' => $_POST['current_session'],
        'current_term' => $_POST['current_term'],
        'application_fee' => $_POST['application_fee'],
        'primary_tuition' => $_POST['primary_tuition'],
        'primary_levy' => $_POST['primary_levy'],
        'jss_tuition' => $_POST['jss_tuition'],
        'jss_levy' => $_POST['jss_levy'],
        'sss_tuition' => $_POST['sss_tuition'],
        'sss_levy' => $_POST['sss_levy']
    ];
    
    $success = true;
    
    foreach ($settings as $key => $value) {
        $value = mysqli_real_escape_string($conn, $value);
        
        // Check if setting exists
        $check_query = "SELECT id FROM system_settings WHERE setting_key = '$key'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing
            $query = "UPDATE system_settings SET setting_value = '$value' WHERE setting_key = '$key'";
        } else {
            // Insert new
            $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES ('$key', '$value')";
        }
        
        if (!mysqli_query($conn, $query)) {
            $success = false;
        }
    }
    
    // Redirect back to settings tab with success/failure message
    if ($success) {
        header('Location: dashboard.php?tab=settings&success=1');
    } else {
        header('Location: dashboard.php?tab=settings&error=1');
    }
    exit();
    
} else {
    header('Location: dashboard.php');
    exit();
}

mysqli_close($conn);
?>