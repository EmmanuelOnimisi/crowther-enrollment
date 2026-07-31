<?php
// payment.php - Fee Payment Page
session_start();
include 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get application ID from URL
$application_id = isset($_GET['application_id']) ? mysqli_real_escape_string($conn, $_GET['application_id']) : '';

if (empty($application_id)) {
    header('Location: dashboard.php');
    exit();
}

// Get application details
$app_query = "SELECT a.*, u.fullname as parent_name, u.email as parent_email 
              FROM applications a 
              JOIN users u ON a.user_id = u.id 
              WHERE a.application_id = '$application_id' AND a.user_id = '$user_id'";
$app_result = mysqli_query($conn, $app_query);

if (mysqli_num_rows($app_result) == 0) {
    header('Location: dashboard.php');
    exit();
}

$application = mysqli_fetch_assoc($app_result);

// Calculate fees based on class category
switch($application['class_category']) {
    case 'Primary':
        $tuition = 35000;
        $levy = 8000;
        $total_fees = 48000;
        break;
    case 'JSS':
        $tuition = 45000;
        $levy = 10000;
        $total_fees = 60000;
        break;
    case 'SSS':
        $tuition = 55000;
        $levy = 12000;
        $total_fees = 72000;
        break;
    default:
        $tuition = 35000;
        $levy = 8000;
        $total_fees = 48000;
}

$application_fee = 5000;
$total_payable = $application_fee + $tuition + $levy;

// Generate payment reference if not exists
if (empty($application['payment_reference'])) {
    $payment_reference = "PAY" . date('Ymd') . rand(100000, 999999);
    $update_ref = "UPDATE applications SET payment_reference = '$payment_reference' WHERE application_id = '$application_id'";
    mysqli_query($conn, $update_ref);
} else {
    $payment_reference = $application['payment_reference'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment | Crowther Memorial College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .payment-container {
            max-width: 700px;
            margin: 120px auto 60px;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        .payment-container h1 {
            color: #1a3c5e;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .payment-container .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
        }
        .fee-breakdown {
            background: #f4f6f9;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .fee-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #ddd;
        }
        .fee-row:last-child {
            border-bottom: none;
        }
        .fee-row.total {
            font-weight: bold;
            font-size: 1.2rem;
            color: #1a3c5e;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .payment-method {
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #ffc107;
        }
        .payment-method.selected {
            border-color: #ffc107;
            background: #fff8e1;
        }
        .payment-method i {
            font-size: 2rem;
            color: #1a3c5e;
            display: block;
            margin-bottom: 0.5rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group input[type="file"] {
            padding: 5px;
        }
        .pay-btn {
            width: 100%;
            background: #28a745;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
        }
        .pay-btn:hover {
            background: #218838;
        }
        .pay-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .bank-details {
            background: #fff8e1;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin: 1rem 0;
        }
        .bank-details p {
            margin: 0.3rem 0;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            font-size: 0.85rem;
        }
        .status-pending { background: #ffc107; color: #1a3c5e; }
        .status-paid { background: #28a745; color: white; }
        .status-verified { background: #17a2b8; color: white; }
        .reference-box {
            background: #1a3c5e;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-family: monospace;
            font-size: 1.1rem;
            text-align: center;
            margin: 1rem 0;
        }
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 768px) {
            .payment-container {
                margin: 100px 20px 40px;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <i class="fas fa-school"></i>
            <span>Crowther Memorial College</span>
        </div>
        <ul class="nav-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>

<div class="payment-container">
    <h1><i class="fas fa-credit-card"></i> Make Payment</h1>
    <p class="subtitle">Complete your payment to finalize the enrolment application</p>
    
    <?php if ($application['payment_status'] == 'verified'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Payment already verified for this application.
        </div>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="dashboard.php" class="btn-primary" style="padding: 10px 30px; border-radius: 5px; text-decoration: none;">Go to Dashboard</a>
        </div>
    <?php elseif ($application['payment_status'] == 'paid'): ?>
        <div class="alert alert-warning">
            <i class="fas fa-clock"></i> Payment submitted! Awaiting admin verification.
        </div>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="dashboard.php" class="btn-primary" style="padding: 10px 30px; border-radius: 5px; text-decoration: none;">Go to Dashboard</a>
        </div>
    <?php else: ?>
    
    <div class="fee-breakdown">
        <h3>Fee Breakdown for <?php echo htmlspecialchars($application['class_applying']); ?></h3>
        <div class="fee-row">
            <span>Application Fee</span>
            <span>₦<?php echo number_format($application_fee); ?></span>
        </div>
        <div class="fee-row">
            <span>Tuition Fee (per term)</span>
            <span>₦<?php echo number_format($tuition); ?></span>
        </div>
        <div class="fee-row">
            <span>Development Levy (per term)</span>
            <span>₦<?php echo number_format($levy); ?></span>
        </div>
        <div class="fee-row total">
            <span>Total Payable</span>
            <span>₦<?php echo number_format($total_payable); ?></span>
        </div>
    </div>
    
    <div class="reference-box">
        <strong>Payment Reference:</strong> <?php echo $payment_reference; ?>
    </div>
    
    <form action="process_payment.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
        <input type="hidden" name="payment_reference" value="<?php echo $payment_reference; ?>">
        <input type="hidden" name="total_amount" value="<?php echo $total_payable; ?>">
        
        <h3>Payment Method</h3>
        <div class="payment-methods">
            <div class="payment-method selected" onclick="selectMethod(this)">
                <i class="fas fa-university"></i>
                <span>Bank Transfer</span>
                <input type="radio" name="payment_method" value="bank" checked style="display: none;">
            </div>
            <div class="payment-method" onclick="selectMethod(this)">
                <i class="fas fa-mobile-alt"></i>
                <span>USSD</span>
                <input type="radio" name="payment_method" value="ussd" style="display: none;">
            </div>
            <div class="payment-method" onclick="selectMethod(this)">
                <i class="fas fa-hand-holding-usd"></i>
                <span>Cash at School</span>
                <input type="radio" name="payment_method" value="cash" style="display: none;">
            </div>
        </div>
        
        <div class="bank-details">
            <h4><i class="fas fa-info-circle"></i> Bank Transfer Details</h4>
            <p><strong>Bank:</strong> First Bank of Nigeria</p>
            <p><strong>Account Name:</strong> Crowther Memorial College</p>
            <p><strong>Account Number:</strong> 1234567890</p>
            <p><strong>Amount:</strong> ₦<?php echo number_format($total_payable); ?></p>
            <p><strong>Reference:</strong> <?php echo $payment_reference; ?></p>
            <small><i class="fas fa-exclamation-triangle"></i> Use the Payment Reference as narration when making transfer.</small>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-upload"></i> Upload Payment Receipt/Proof</label>
            <input type="file" name="payment_proof" accept="image/*,.pdf" required>
            <small>Upload a screenshot of bank transfer, USSD confirmation, or cash payment receipt</small>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-calendar-alt"></i> Payment Date</label>
            <input type="date" name="payment_date" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-sticky-note"></i> Additional Notes (optional)</label>
            <textarea name="notes" rows="2" placeholder="Any additional information about the payment"></textarea>
        </div>
        
        <button type="submit" class="pay-btn">
            <i class="fas fa-check-circle"></i> Submit Payment
        </button>
    </form>
    
    <?php endif; ?>
</div>

<script>
    function selectMethod(element) {
        // Remove selected class from all
        var methods = document.querySelectorAll('.payment-method');
        methods.forEach(function(method) {
            method.classList.remove('selected');
        });
        
        // Add selected class to clicked
        element.classList.add('selected');
        
        // Check the radio button
        var radio = element.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
        }
    }
</script>

<footer style="background: #0f2a43; color: white; text-align: center; padding: 2rem; margin-top: 2rem;">
    <p>&copy; 2026 Crowther Memorial College, Lokoja. All rights reserved.</p>
</footer>

</body>
</html>
<?php mysqli_close($conn); ?>