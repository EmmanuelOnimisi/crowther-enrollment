<?php
// admin/verify_payment.php - Admin verifies payment
session_start();
include '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['user_id'] ?? 0;
$admin_name = $_SESSION['user_name'] ?? 'Administrator';

// Get application ID from URL
$application_id = isset($_GET['application_id']) ? mysqli_real_escape_string($conn, $_GET['application_id']) : '';

if (empty($application_id)) {
    header('Location: dashboard.php');
    exit();
}

// Get application details with payment info
$app_query = "SELECT a.*, u.fullname as parent_name, u.email as parent_email, u.phone as parent_phone 
              FROM applications a 
              JOIN users u ON a.user_id = u.id 
              WHERE a.application_id = '$application_id'";
$app_result = mysqli_query($conn, $app_query);

if (mysqli_num_rows($app_result) == 0) {
    header('Location: dashboard.php');
    exit();
}

$application = mysqli_fetch_assoc($app_result);

// Get payment record
$pay_query = "SELECT * FROM payments WHERE application_id = '$application_id' ORDER BY payment_date DESC LIMIT 1";
$pay_result = mysqli_query($conn, $pay_query);
$payment = mysqli_fetch_assoc($pay_result);

// Check if payment already verified
if ($application['payment_status'] == 'verified') {
    $already_verified = true;
} else {
    $already_verified = false;
}

// Handle verification action
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    if ($action == 'verify') {
        // Update payment status to completed
        $update_pay = "UPDATE payments SET status = 'completed' WHERE id = " . $payment['id'];
        mysqli_query($conn, $update_pay);
        
        // Update application payment status
        $update_app = "UPDATE applications SET 
            payment_status = 'verified',
            payment_verified_by = '$admin_id',
            payment_verified_at = NOW(),
            payment_notes = '$notes'
            WHERE application_id = '$application_id'";
        
        if (mysqli_query($conn, $update_app)) {
            $success = "✅ Payment verified successfully! You can now approve the application.";
        } else {
            $error = "❌ Failed to verify payment: " . mysqli_error($conn);
        }
        
    } elseif ($action == 'reject') {
        // Update payment status to rejected
        $update_pay = "UPDATE payments SET status = 'failed' WHERE id = " . $payment['id'];
        mysqli_query($conn, $update_pay);
        
        // Update application payment status
        $update_app = "UPDATE applications SET 
            payment_status = 'rejected',
            payment_notes = '$notes'
            WHERE application_id = '$application_id'";
        
        if (mysqli_query($conn, $update_app)) {
            $success = "❌ Payment rejected. Parent will be notified to make a new payment.";
        } else {
            $error = "❌ Failed to reject payment: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Payment | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; }
        .container { max-width: 800px; margin: 50px auto; padding: 2rem; background: white; border-radius: 10px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ffc107; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .header h1 { color: #1a3c5e; }
        .back-btn { background: #1a3c5e; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; }
        .back-btn:hover { background: #ffc107; color: #1a3c5e; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .info-item { background: #f4f6f9; padding: 1rem; border-radius: 8px; }
        .info-item label { font-weight: bold; color: #1a3c5e; display: block; margin-bottom: 0.3rem; }
        .info-item p { margin: 0; }
        .proof-box { background: #f4f6f9; padding: 1.5rem; border-radius: 8px; text-align: center; margin: 1rem 0; }
        .proof-box img { max-width: 100%; max-height: 400px; border-radius: 5px; }
        .proof-box a { color: #1a3c5e; }
        .status-badge { padding: 5px 15px; border-radius: 20px; font-weight: bold; display: inline-block; }
        .status-pending { background: #ffc107; color: #1a3c5e; }
        .status-paid { background: #17a2b8; color: white; }
        .status-verified { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .actions { display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap; }
        .btn-verify { background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-verify:hover { background: #218838; }
        .btn-reject { background: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-reject:hover { background: #c82333; }
        .btn-approve { background: #ffc107; color: #1a3c5e; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-approve:hover { background: #e6a800; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 0.5rem; }
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .alert { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        @media (max-width: 768px) {
            .info-grid { grid-template-columns: 1fr; }
            .container { margin: 20px; padding: 1rem; }
            .actions { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-credit-card"></i> Verify Payment</h1>
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
        <div style="text-align: center; margin-top: 1rem;">
            <?php if ($action == 'verify'): ?>
                <a href="dashboard.php" class="btn-approve"><i class="fas fa-arrow-left"></i> Go to Dashboard</a>
                <a href="verify_payment.php?application_id=<?php echo $application_id; ?>" class="btn-approve"><i class="fas fa-sync"></i> Refresh</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn-approve"><i class="fas fa-arrow-left"></i> Go to Dashboard</a>
            <?php endif; ?>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="verify_payment.php?application_id=<?php echo $application_id; ?>" class="btn-approve"><i class="fas fa-sync"></i> Try Again</a>
        </div>
    <?php elseif ($already_verified): ?>
        <div class="alert alert-info">
            <i class="fas fa-check-circle"></i> Payment already verified by <?php echo htmlspecialchars($admin_name); ?> on <?php echo date('d/m/Y h:i A', strtotime($application['payment_verified_at'])); ?>
        </div>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="dashboard.php" class="btn-approve"><i class="fas fa-arrow-left"></i> Go to Dashboard</a>
        </div>
    <?php else: ?>
    
    <!-- Application Info -->
    <div class="info-grid">
        <div class="info-item">
            <label>Application ID</label>
            <p><strong><?php echo htmlspecialchars($application['application_id']); ?></strong></p>
        </div>
        <div class="info-item">
            <label>Status</label>
            <p>
                <span class="status-badge status-<?php echo $application['payment_status']; ?>">
                    <?php echo ucfirst($application['payment_status']); ?>
                </span>
            </p>
        </div>
        <div class="info-item">
            <label>Student Name</label>
            <p><?php echo htmlspecialchars($application['surname'] . ' ' . $application['firstname']); ?></p>
        </div>
        <div class="info-item">
            <label>Class</label>
            <p><?php echo htmlspecialchars($application['class_applying']); ?></p>
        </div>
        <div class="info-item">
            <label>Parent Name</label>
            <p><?php echo htmlspecialchars($application['parent_name']); ?></p>
        </div>
        <div class="info-item">
            <label>Parent Email</label>
            <p><?php echo htmlspecialchars($application['parent_email']); ?></p>
        </div>
        <div class="info-item">
            <label>Parent Phone</label>
            <p><?php echo htmlspecialchars($application['parent_phone']); ?></p>
        </div>
        <div class="info-item">
            <label>Payment Reference</label>
            <p><strong><?php echo htmlspecialchars($application['payment_reference']); ?></strong></p>
        </div>
        <?php if ($payment): ?>
        <div class="info-item">
            <label>Amount Paid</label>
            <p><strong>₦<?php echo number_format($payment['amount']); ?></strong></p>
        </div>
        <div class="info-item">
            <label>Payment Method</label>
            <p><?php echo ucfirst($payment['payment_method']); ?></p>
        </div>
        <div class="info-item">
            <label>Payment Date</label>
            <p><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Payment Proof -->
    <h3><i class="fas fa-receipt"></i> Payment Proof</h3>
    <div class="proof-box">
        <?php if ($application['payment_proof'] && file_exists('../' . $application['payment_proof'])): ?>
            <?php 
            $ext = strtolower(pathinfo($application['payment_proof'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <img src="../<?php echo $application['payment_proof']; ?>" alt="Payment Proof">
                <br><br>
                <a href="../<?php echo $application['payment_proof']; ?>" target="_blank" class="back-btn"><i class="fas fa-external-link-alt"></i> Open Full Image</a>
            <?php elseif ($ext == 'pdf'): ?>
                <i class="fas fa-file-pdf" style="font-size: 4rem; color: #dc3545;"></i>
                <p><a href="../<?php echo $application['payment_proof']; ?>" target="_blank">📄 View Payment Receipt (PDF)</a></p>
            <?php else: ?>
                <p><a href="../<?php echo $application['payment_proof']; ?>" target="_blank"><i class="fas fa-file"></i> View Payment Proof</a></p>
            <?php endif; ?>
        <?php else: ?>
            <p><i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i> No payment proof uploaded by parent.</p>
            <p><small>File path: <?php echo htmlspecialchars($application['payment_proof']); ?></small></p>
        <?php endif; ?>
    </div>
    
    <?php if ($application['payment_notes']): ?>
        <div class="alert alert-info">
            <i class="fas fa-sticky-note"></i> <strong>Parent Notes:</strong> <?php echo htmlspecialchars($application['payment_notes']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Verification Actions -->
    <?php if ($application['payment_status'] == 'paid'): ?>
        <form method="POST">
            <div class="form-group">
                <label>Admin Notes (optional)</label>
                <textarea name="notes" rows="2" placeholder="Add notes about this payment verification"></textarea>
            </div>
            
            <div class="actions">
                <button type="submit" name="action" value="verify" class="btn-verify">
                    <i class="fas fa-check"></i> Verify Payment
                </button>
                <button type="submit" name="action" value="reject" class="btn-reject">
                    <i class="fas fa-times"></i> Reject Payment
                </button>
            </div>
        </form>
    <?php elseif ($application['payment_status'] == 'verified'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Payment verified by <?php echo htmlspecialchars($admin_name); ?> on <?php echo date('d/m/Y h:i A', strtotime($application['payment_verified_at'])); ?>
        </div>
        <div class="actions">
            <a href="dashboard.php" class="btn-approve"><i class="fas fa-arrow-left"></i> Go to Dashboard</a>
        </div>
    <?php elseif ($application['payment_status'] == 'rejected'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i> Payment rejected.
        </div>
        <div class="actions">
            <a href="dashboard.php" class="btn-approve"><i class="fas fa-arrow-left"></i> Go to Dashboard</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-clock"></i> Payment not yet submitted by parent.
        </div>
        <div class="actions">
            <a href="dashboard.php" class="btn-approve"><i class="fas fa-arrow-left"></i> Go to Dashboard</a>
        </div>
    <?php endif; ?>
    
    <?php endif; ?>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>