<?php
// process_application.php - Save enrolment application with payment tracking
session_start();
include 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $user_id = $_SESSION['user_id'];
    
    // Generate unique Application ID
    $application_id = "CMC" . date('Ymd') . rand(100, 999);
    
    // Generate unique Payment Reference
    $payment_reference = "PAY" . date('Ymd') . rand(100000, 999999);
    
    // Student Information
    $surname = mysqli_real_escape_string($conn, $_POST['surname']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = isset($_POST['middlename']) ? mysqli_real_escape_string($conn, $_POST['middlename']) : '';
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $class_applying = mysqli_real_escape_string($conn, $_POST['class_applying']);
    $address = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
    
    // Determine class category
    $class_category = '';
    if (strpos($class_applying, 'Primary') !== false) {
        $class_category = 'Primary';
    } elseif (strpos($class_applying, 'JSS') !== false) {
        $class_category = 'JSS';
    } elseif (strpos($class_applying, 'SSS') !== false) {
        $class_category = 'SSS';
    }
    
    // Parent Information
    $father_name = isset($_POST['father_name']) ? mysqli_real_escape_string($conn, $_POST['father_name']) : '';
    $mother_name = isset($_POST['mother_name']) ? mysqli_real_escape_string($conn, $_POST['mother_name']) : '';
    $parent_phone = mysqli_real_escape_string($conn, $_POST['parent_phone']);
    $parent_email = mysqli_real_escape_string($conn, $_POST['parent_email']);
    $parent_occupation = isset($_POST['parent_occupation']) ? mysqli_real_escape_string($conn, $_POST['parent_occupation']) : '';
    
    // Previous School Information (FIXED: using isset to avoid undefined warnings)
    $previous_school = isset($_POST['previous_school']) ? mysqli_real_escape_string($conn, $_POST['previous_school']) : '';
    $last_class = isset($_POST['last_class']) ? mysqli_real_escape_string($conn, $_POST['last_class']) : '';
    $transfer_reason = isset($_POST['transfer_reason']) ? mysqli_real_escape_string($conn, $_POST['transfer_reason']) : '';
    
    // File Upload Handling
    $passport_path = null;
    $birth_cert_path = null;
    $report_card_path = null;
    
    // Upload Passport Photo
    if (isset($_FILES['passport_photo']) && $_FILES['passport_photo']['error'] == 0) {
        $target_dir = "uploads/passports/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = strtolower(pathinfo($_FILES['passport_photo']['name'], PATHINFO_EXTENSION));
        $new_filename = $application_id . "_passport." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['passport_photo']['tmp_name'], $target_file)) {
                $passport_path = $target_file;
            }
        }
    }
    
    // Upload Birth Certificate
    if (isset($_FILES['birth_certificate']) && $_FILES['birth_certificate']['error'] == 0) {
        $target_dir = "uploads/birth_certificates/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = strtolower(pathinfo($_FILES['birth_certificate']['name'], PATHINFO_EXTENSION));
        $new_filename = $application_id . "_birth_cert." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['birth_certificate']['tmp_name'], $target_file)) {
                $birth_cert_path = $target_file;
            }
        }
    }
    
    // Upload Report Card
    if (isset($_FILES['report_card']) && $_FILES['report_card']['error'] == 0) {
        $target_dir = "uploads/report_cards/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = strtolower(pathinfo($_FILES['report_card']['name'], PATHINFO_EXTENSION));
        $new_filename = $application_id . "_report_card." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['report_card']['tmp_name'], $target_file)) {
                $report_card_path = $target_file;
            }
        }
    }
    
    // Calculate total fees based on class category
    switch($class_category) {
        case 'Primary':
            $total_fees = 48000;
            break;
        case 'JSS':
            $total_fees = 60000;
            break;
        case 'SSS':
            $total_fees = 72000;
            break;
        default:
            $total_fees = 48000;
    }
    
    // Insert into database
    $query = "INSERT INTO applications (
        application_id, user_id, surname, firstname, middlename, 
        dob, gender, class_applying, class_category, address, father_name, 
        mother_name, parent_phone, parent_email, parent_occupation,
        previous_school, last_class, transfer_reason,
        passport_photo, birth_certificate, report_card, 
        status, payment_status, payment_reference
    ) VALUES (
        '$application_id', '$user_id', '$surname', '$firstname', '$middlename',
        '$dob', '$gender', '$class_applying', '$class_category', '$address', '$father_name',
        '$mother_name', '$parent_phone', '$parent_email', '$parent_occupation',
        '$previous_school', '$last_class', '$transfer_reason',
        '$passport_path', '$birth_cert_path', '$report_card_path',
        'pending_payment', 'pending', '$payment_reference'
    )";
    
    if (mysqli_query($conn, $query)) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Application Submitted - Awaiting Payment</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <style>
                body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
                .success-box { background: white; padding: 2rem; border-radius: 10px; text-align: center; max-width: 500px; margin: 2rem; }
                .success { color: #28a745; }
                .info { background: #f4f6f9; padding: 1rem; border-radius: 8px; margin: 1rem 0; text-align: left; }
                .btn { display: inline-block; margin-top: 1rem; padding: 10px 20px; background: #1a3c5e; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
                .btn-pay { background: #ffc107; color: #1a3c5e; }
                .btn-pay:hover { background: #e6a800; }
            </style>
        </head>
        <body>
            <div class="success-box">
                <i class="fas fa-check-circle success" style="font-size: 3rem;"></i>
                <h2 class="success">Application Submitted!</h2>
                <div class="info">
                    <p><strong>Application ID:</strong> <?php echo $application_id; ?></p>
                    <p><strong>Student Name:</strong> <?php echo $surname . ' ' . $firstname; ?></p>
                    <p><strong>Class:</strong> <?php echo $class_applying; ?></p>
                    <p><strong>Payment Reference:</strong> <?php echo $payment_reference; ?></p>
                    <p><strong>Total Fees Due:</strong> ₦<?php echo number_format($total_fees); ?></p>
                    <p><strong>Status:</strong> <span style="color: #ffc107; font-weight: bold;">Awaiting Payment</span></p>
                </div>
                <p>Please proceed to make payment to complete your application.</p>
                <a href="dashboard.php" class="btn">Go to Dashboard</a>
                <a href="payment.php?application_id=<?php echo $application_id; ?>" class="btn btn-pay"><i class="fas fa-credit-card"></i> Pay Now</a>
            </div>
        </body>
        </html>
        <?php
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Application Error</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <style>
                body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
                .error-box { background: white; padding: 2rem; border-radius: 10px; text-align: center; max-width: 500px; }
                .error { color: #dc3545; }
                .btn { display: inline-block; margin-top: 1rem; padding: 10px 20px; background: #1a3c5e; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <i class="fas fa-times-circle error" style="font-size: 3rem;"></i>
                <h2 class="error">Application Failed</h2>
                <p>There was an error submitting your application. Please try again.</p>
                <p><strong>Error:</strong> <?php echo mysqli_error($conn); ?></p>
                <a href="apply.php" class="btn">Try Again</a>
            </div>
        </body>
        </html>
        <?php
    }
    
    mysqli_close($conn);
} else {
    header('Location: apply.php');
    exit();
}
?>