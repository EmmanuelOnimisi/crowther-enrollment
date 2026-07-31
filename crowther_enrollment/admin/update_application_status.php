<?php
// admin/update_application_status.php - Update application status (no payment check)
session_start();
include '../includes/config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = mysqli_real_escape_string($conn, $_POST['application_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // No payment verification check - admin can approve/reject directly
    $query = "UPDATE applications SET status = '$status', reviewed_at = NOW(), reviewed_by = " . $_SESSION['user_id'] . " WHERE application_id = '$application_id'";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>