<?php
// process_payment.php - Handle payment submission (FIXED - keeps user role)
session_start();
include 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'parent'; // Preserve user role

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $application_id = mysqli_real_escape_string($conn, $_POST['application_id']);
    $payment_reference = mysqli_real_escape_string($conn, $_POST['payment_reference']);
    $total_amount = mysqli_real_escape_string($conn, $_POST['total_amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Verify application belongs to this user
    $verify_query = "SELECT * FROM applications WHERE application_id = '$application_id' AND user_id = '$user_id'";
    $verify_result = mysqli_query($conn, $verify_query);
    
    if (mysqli_num_rows($verify_result) == 0) {
        header('Location: dashboard.php');
        exit();
    }
    
    $application = mysqli_fetch_assoc($verify_result);
    
    // Check if already paid
    if ($application['payment_status'] == 'verified' || $application['payment_status'] == 'paid') {
        header('Location: dashboard.php?payment=already');
        exit();
    }
    
    // Handle file upload
    $payment_proof_path = null;
    
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $target_dir = "uploads/payments/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
        $new_filename = $application_id . "_payment_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file)) {
                $payment_proof_path = $target_file;
            }
        }
    }
    
    // Generate receipt number
    $receipt_no = "RCP-" . date('Y') . "-" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    // Insert into payments table
    $insert_payment = "INSERT INTO payments (
        receipt_no, 
        application_id, 
        student_name, 
        fee_type, 
        amount, 
        payment_method, 
        status, 
        payment_date
    ) VALUES (
        '$receipt_no',
        '$application_id',
        '" . mysqli_real_escape_string($conn, $application['surname'] . ' ' . $application['firstname']) . "',
        'Full Payment',
        '$total_amount',
        '$payment_method',
        'pending',
        '$payment_date'
    )";
    
    if (mysqli_query($conn, $insert_payment)) {
        
        // Update application payment status to 'paid'
        $update_app = "UPDATE applications SET 
            payment_status = 'paid',
            payment_reference = '$payment_reference',
            payment_proof = '$payment_proof_path',
            payment_notes = '$notes',
            status = 'pending'
            WHERE application_id = '$application_id'";
        mysqli_query($conn, $update_app);
        
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Payment Submitted</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
                .success-box { background: white; padding: 2rem; border-radius: 10px; text-align: center; max-width: 500px; margin: 2rem; box-shadow: 0 5px 25px rgba(0,0,0,0.1); }
                .success { color: #28a745; }
                .info { background: #f4f6f9; padding: 1rem; border-radius: 8px; margin: 1rem 0; text-align: left; }
                .btn { display: inline-block; margin-top: 1rem; padding: 10px 20px; background: #1a3c5e; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
                .btn-warning { background: #ffc107; color: #1a3c5e; }
                .btn-warning:hover { background: #e6a800; }
                .icon-large { font-size: 4rem; }
            </style>
        </head>
        <body>
            <div class="success-box">
                <i class="fas fa-check-circle success icon-large"></i>
                <h2 class="success">Payment Submitted!</h2>
                <p>Your payment has been received and is awaiting admin verification.</p>
                <div class="info">
                    <p><strong>Application ID:</strong> <?php echo $application_id; ?></p>
                    <p><strong>Payment Reference:</strong> <?php echo $payment_reference; ?></p>
                    <p><strong>Receipt Number:</strong> <?php echo $receipt_no; ?></p>
                    <p><strong>Amount Paid:</strong> ₦<?php echo number_format($total_amount); ?></p>
                    <p><strong>Payment Status:</strong> <span style="color: #17a2b8; font-weight: bold;">Awaiting Verification</span></p>
                </div>
                <p>You will be notified once your payment is verified by the school.</p>
                <a href="dashboard.php" class="btn"><i class="fas fa-home"></i> Go to Dashboard</a>
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
            <title>Payment Failed</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                .error-box { background: white; padding: 2rem; border-radius: 10px; text-align: center; max-width: 400px; }
                .error { color: #dc3545; }
                .btn { display: inline-block; margin-top: 1rem; padding: 10px 20px; background: #1a3c5e; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <i class="fas fa-times-circle error" style="font-size: 3rem;"></i>
                <h2 class="error">Payment Failed</h2>
                <p>There was an error processing your payment. Please try again.</p>
                <p><strong>Error:</strong> <?php echo mysqli_error($conn); ?></p>
                <a href="payment.php?application_id=<?php echo $application_id; ?>" class="btn">Try Again</a>
            </div>
        </body>
        </html>
        <?php
    }
    
} else {
    header('Location: dashboard.php');
    exit();
}

mysqli_close($conn);
?>