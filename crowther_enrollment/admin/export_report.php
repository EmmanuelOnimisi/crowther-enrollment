<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$type = $_GET['type'] ?? 'applications';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

if ($type == 'applications') {
    fputcsv($output, ['Application ID', 'Student Name', 'Class', 'Parent Name', 'Parent Email', 'Parent Phone', 'Status', 'Submitted Date']);
    
    $query = "SELECT a.*, u.fullname as parent_name, u.email as parent_email 
              FROM applications a 
              JOIN users u ON a.user_id = u.id 
              ORDER BY a.submitted_at DESC";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['application_id'],
            $row['surname'] . ' ' . $row['firstname'],
            $row['class_applying'],
            $row['parent_name'],
            $row['parent_email'],
            $row['parent_phone'],
            $row['status'],
            $row['submitted_at']
        ]);
    }
} 
elseif ($type == 'students') {
    fputcsv($output, ['Admission No.', 'Student Name', 'Class', 'Parent Name', 'Parent Phone', 'Status']);
    
    $query = "SELECT a.*, u.fullname as parent_name, u.phone as parent_phone 
              FROM applications a 
              JOIN users u ON a.user_id = u.id 
              WHERE a.status = 'approved'";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['application_id'],
            $row['surname'] . ' ' . $row['firstname'],
            $row['class_applying'],
            $row['parent_name'],
            $row['parent_phone'],
            'Active'
        ]);
    }
}
elseif ($type == 'payments') {
    fputcsv($output, ['Receipt No.', 'Student Name', 'Fee Type', 'Amount', 'Payment Method', 'Status', 'Payment Date']);
    
    $query = "SELECT * FROM payments ORDER BY payment_date DESC";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['receipt_no'],
            $row['student_name'],
            $row['fee_type'],
            $row['amount'],
            $row['payment_method'],
            $row['status'],
            $row['payment_date']
        ]);
    }
}

fclose($output);
mysqli_close($conn);
?>