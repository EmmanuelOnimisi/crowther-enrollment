<?php
// admin/get_application_details.php - Get full application details with Approve/Reject buttons
session_start();
include '../includes/config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_GET['application_id'])) {
    $application_id = mysqli_real_escape_string($conn, $_GET['application_id']);
    
    $query = "SELECT * FROM applications WHERE application_id = '$application_id'";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        
        // Format file links
        $file_links = [];
        
        // Passport Photo
        if ($row['passport_photo'] && file_exists('../' . $row['passport_photo'])) {
            $file_links['passport'] = '<a href="../' . $row['passport_photo'] . '" target="_blank" class="action-view" style="display: inline-block; margin: 5px;"><i class="fas fa-camera"></i> View Passport</a>';
        } else {
            $file_links['passport'] = '<span style="color: #999;">Not uploaded</span>';
        }
        
        // Birth Certificate
        if ($row['birth_certificate'] && file_exists('../' . $row['birth_certificate'])) {
            $file_links['birth_cert'] = '<a href="../' . $row['birth_certificate'] . '" target="_blank" class="action-view" style="display: inline-block; margin: 5px;"><i class="fas fa-file-pdf"></i> View Birth Certificate</a>';
        } else {
            $file_links['birth_cert'] = '<span style="color: #999;">Not uploaded</span>';
        }
        
        // Report Card
        if ($row['report_card'] && file_exists('../' . $row['report_card'])) {
            $file_links['report_card'] = '<a href="../' . $row['report_card'] . '" target="_blank" class="action-view" style="display: inline-block; margin: 5px;"><i class="fas fa-file-alt"></i> View Report Card</a>';
        } else {
            $file_links['report_card'] = '<span style="color: #999;">Not uploaded</span>';
        }
        
        // Payment Proof
        if ($row['payment_proof'] && file_exists('../' . $row['payment_proof'])) {
            $file_links['payment_proof'] = '<a href="../' . $row['payment_proof'] . '" target="_blank" class="action-view" style="display: inline-block; margin: 5px;"><i class="fas fa-receipt"></i> View Payment Proof</a>';
        } else {
            $file_links['payment_proof'] = '<span style="color: #999;">No payment proof uploaded</span>';
        }
        
        // Determine if Approve/Reject buttons should be shown
        $show_buttons = false;
        $status = $row['status'];
        
        // Show buttons for pending applications (any payment status)
        if ($status == 'pending' || $status == 'pending_review' || $status == 'pending_payment') {
            $show_buttons = true;
        }
        
        $response = [
            'success' => true,
            'application_id' => $row['application_id'],
            'surname' => $row['surname'],
            'firstname' => $row['firstname'],
            'middlename' => $row['middlename'],
            'dob' => date('d F, Y', strtotime($row['dob'])),
            'gender' => $row['gender'],
            'class_applying' => $row['class_applying'],
            'class_category' => $row['class_category'] ?? 'N/A',
            'address' => $row['address'] ?? 'Not provided',
            'father_name' => $row['father_name'] ?? 'Not provided',
            'mother_name' => $row['mother_name'] ?? 'Not provided',
            'parent_phone' => $row['parent_phone'],
            'parent_email' => $row['parent_email'],
            'parent_occupation' => $row['parent_occupation'] ?? 'Not provided',
            'previous_school' => $row['previous_school'] ?? 'Not provided',
            'last_class' => $row['last_class'] ?? 'Not provided',
            'transfer_reason' => $row['transfer_reason'] ?? 'Not provided',
            'status' => $row['status'],
            'payment_status' => $row['payment_status'] ?? 'pending',
            'payment_reference' => $row['payment_reference'] ?? 'Not generated',
            'submitted_at' => date('d/m/Y h:i A', strtotime($row['submitted_at'])),
            'reviewed_at' => $row['reviewed_at'] ? date('d/m/Y h:i A', strtotime($row['reviewed_at'])) : 'Not reviewed yet',
            'file_links' => $file_links,
            'show_buttons' => $show_buttons
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'No application ID provided']);
}
?>