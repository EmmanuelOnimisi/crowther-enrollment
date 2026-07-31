<?php
// admin/verify_payment_ajax.php - AJAX handler for verify/reject payment
session_start();
include '../includes/config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$admin_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = mysqli_real_escape_string($conn, $_POST['application_id']);
    $action = mysqli_real_escape_string($conn, $_POST['action']);
    
    if ($action == 'verify') {
        // Update payment status to verified
        $update_app = "UPDATE applications SET 
            payment_status = 'verified',
            payment_verified_by = '$admin_id',
            payment_verified_at = NOW()
            WHERE application_id = '$application_id'";
        
        if (mysqli_query($conn, $update_app)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        
    } elseif ($action == 'reject') {
        // Update payment status to rejected
        $update_app = "UPDATE applications SET 
            payment_status = 'rejected'
            WHERE application_id = '$application_id'";
        
        if (mysqli_query($conn, $update_app)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>