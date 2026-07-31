<?php
// process_upload.php - Upload documents from dashboard
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $document_type = $_POST['document_type'];
    $application_id = $_POST['application_id'];
    
    $target_dir = "";
    switch ($document_type) {
        case 'passport':
            $target_dir = "uploads/passports/";
            break;
        case 'birth_cert':
            $target_dir = "uploads/birth_certificates/";
            break;
        case 'report_card':
            $target_dir = "uploads/report_cards/";
            break;
        case 'medical':
            $target_dir = "uploads/medical/";
            break;
    }
    
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        $new_filename = $application_id . "_" . $document_type . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
                // Save to documents table
                $query = "INSERT INTO documents (application_id, document_type, file_name, file_path) 
                          VALUES ('$application_id', '$document_type', '$new_filename', '$target_file')";
                mysqli_query($conn, $query);
                
                // Also update applications table if needed
                $update_field = "";
                if ($document_type == 'passport') $update_field = "passport_photo = '$target_file'";
                elseif ($document_type == 'birth_cert') $update_field = "birth_certificate = '$target_file'";
                elseif ($document_type == 'report_card') $update_field = "report_card = '$target_file'";
                
                if ($update_field) {
                    mysqli_query($conn, "UPDATE applications SET $update_field WHERE application_id = '$application_id' AND user_id = '$user_id'");
                }
                
                header('Location: dashboard.php?upload=success');
                exit();
            }
        }
    }
}

header('Location: dashboard.php?upload=failed');
exit();
?>