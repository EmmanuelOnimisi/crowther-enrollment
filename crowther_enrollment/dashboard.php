<?php
session_start();
include 'includes/config.php';

// =============================================
// FIXED: Proper user authentication - NO auto-login as admin
// =============================================
$is_logged_in = false;
$user_id = 0;
$user_name = '';

// Check if user is logged in via session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? '';
    $is_logged_in = true;
}

// If not logged in, redirect to login
if (!$is_logged_in) {
    header('Location: login.php');
    exit();
}

// If user is admin, redirect to admin dashboard
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    header('Location: admin/dashboard.php');
    exit();
}

// Get user profile from database
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// If user not found in database, logout
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get user's applications with payment status
$apps_query = "SELECT * FROM applications WHERE user_id = '$user_id' ORDER BY submitted_at DESC";
$apps_result = mysqli_query($conn, $apps_query);
$applications = [];
while ($row = mysqli_fetch_assoc($apps_result)) {
    $applications[] = $row;
}

// Get uploaded documents
$docs_query = "SELECT * FROM documents WHERE application_id IN (SELECT application_id FROM applications WHERE user_id = '$user_id') ORDER BY uploaded_at DESC";
$docs_result = mysqli_query($conn, $docs_query);
$documents = [];
while ($row = mysqli_fetch_assoc($docs_result)) {
    $documents[] = $row;
}

// Count statistics
$total_applications = count($applications);
$pending_count = 0;
$approved_count = 0;
$rejected_count = 0;
$pending_payment_count = 0;

foreach ($applications as $app) {
    if ($app['status'] == 'pending_payment') $pending_payment_count++;
    elseif ($app['status'] == 'pending') $pending_count++;
    elseif ($app['status'] == 'approved') $approved_count++;
    elseif ($app['status'] == 'rejected') $rejected_count++;
}

// Get user's payments
$payments_query = "SELECT p.* FROM payments p 
                   WHERE p.application_id IN (SELECT application_id FROM applications WHERE user_id = '$user_id') 
                   ORDER BY p.payment_date DESC";
$payments_result = mysqli_query($conn, $payments_query);
$payments = [];
while ($row = mysqli_fetch_assoc($payments_result)) {
    $payments[] = $row;
}

// Calculate total fees paid
$total_paid = 0;
foreach ($payments as $payment) {
    if ($payment['status'] == 'completed') {
        $total_paid += $payment['amount'];
    }
}

// Function to get status badge
function getStatusBadge($status, $payment_status = null) {
    if ($status == 'approved') {
        return '<span class="status-badge status-approved">✅ Approved</span>';
    } elseif ($status == 'rejected') {
        return '<span class="status-badge status-rejected">❌ Rejected</span>';
    } elseif ($status == 'pending_payment') {
        return '<span class="status-badge status-pending">⏳ Awaiting Payment</span>';
    } elseif ($status == 'pending') {
        return '<span class="status-badge status-pending">⏳ Pending Review</span>';
    }
    return '<span class="status-badge status-pending">' . ucfirst($status) . '</span>';
}

// Function to get payment status badge
function getPaymentStatusBadge($payment_status) {
    if ($payment_status == 'verified') {
        return '<span class="status-badge status-approved">✅ Payment Verified</span>';
    } elseif ($payment_status == 'paid') {
        return '<span class="status-badge status-approved">💰 Paid - Awaiting Verification</span>';
    } elseif ($payment_status == 'pending') {
        return '<span class="status-badge status-pending">⏳ Payment Pending</span>';
    } elseif ($payment_status == 'rejected') {
        return '<span class="status-badge status-rejected">❌ Payment Rejected</span>';
    }
    return '<span class="status-badge status-pending">⏳ Pending</span>';
}

// Function to check if "Pay Now" should be shown
function showPayNow($status, $payment_status) {
    if ($status == 'approved' || $status == 'rejected') {
        return false;
    }
    if ($payment_status == 'verified' || $payment_status == 'paid') {
        return false;
    }
    if ($status == 'pending_payment') {
        return true;
    }
    if ($status == 'pending' && ($payment_status == 'pending' || $payment_status == 'rejected' || empty($payment_status))) {
        return true;
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | Crowther Memorial College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
        }
        
        .navbar {
            background: #1a3c5e;
            color: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }
        
        .logo {
            font-size: 1.3rem;
            font-weight: bold;
        }
        
        .logo i {
            color: #ffc107;
            margin-right: 0.5rem;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 1.5rem;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
        }
        
        .nav-menu a:hover {
            color: #ffc107;
        }
        
        .dashboard-container {
            max-width: 1300px;
            margin: 100px auto 40px;
            padding: 0 2rem;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #1a3c5e 0%, #2c5a8c 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card i {
            font-size: 2rem;
            color: #ffc107;
            margin-bottom: 0.5rem;
        }
        
        .stat-card h3 {
            font-size: 1.5rem;
            color: #1a3c5e;
        }
        
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: white;
            padding: 0.5rem;
            border-radius: 10px;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            padding: 12px 25px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: bold;
        }
        
        .tab-btn i {
            margin-right: 8px;
        }
        
        .tab-btn:hover {
            background: #f0f0f0;
        }
        
        .tab-btn.active {
            background: #ffc107;
            color: #1a3c5e;
        }
        
        .tab-content {
            display: none;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tab-content.active {
            display: block;
        }
        
        .info-card {
            background: #f4f6f9;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .info-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }
        
        .info-item label {
            font-weight: bold;
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
            color: #1a3c5e;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn-primary, .btn-success, .btn-danger, .btn-warning {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #1a3c5e;
            color: white;
        }
        
        .btn-primary:hover {
            background: #ffc107;
            color: #1a3c5e;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #1a3c5e;
        }
        
        .btn-warning:hover {
            background: #e6a800;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            font-size: 0.85rem;
        }
        
        .status-pending { background: #ffc107; color: #1a3c5e; }
        .status-approved { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f4f6f9;
            color: #1a3c5e;
        }
        
        .file-link {
            color: #1a3c5e;
            text-decoration: none;
        }
        
        .file-link:hover {
            color: #ffc107;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 1rem;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .tabs {
                justify-content: center;
            }
            .tab-btn {
                font-size: 0.85rem;
                padding: 8px 12px;
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
            <li><a href="apply.php"><i class="fas fa-file-alt"></i> Apply Now</a></li>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div>
            <h2><i class="fas fa-smile-wink"></i> Welcome, <?php echo htmlspecialchars($user['fullname']); ?>!</h2>
            <p>Manage your children's enrolment, upload documents, and make payments.</p>
        </div>
        <div>
            <i class="fas fa-child" style="font-size: 3rem; opacity: 0.5;"></i>
        </div>
    </div>
    
    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <i class="fas fa-file-alt"></i>
            <h3><?php echo $total_applications; ?></h3>
            <p>Total Applications</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-credit-card"></i>
            <h3><?php echo $pending_payment_count; ?></h3>
            <p>Awaiting Payment</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle"></i>
            <h3><?php echo $approved_count; ?></h3>
            <p>Approved</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-money-bill-wave"></i>
            <h3>₦<?php echo number_format($total_paid); ?></h3>
            <p>Total Fees Paid</p>
        </div>
    </div>
    
    <!-- Tabs Navigation -->
    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('profile')">
            <i class="fas fa-user-circle"></i> My Profile
        </button>
        <button class="tab-btn" onclick="showTab('application')">
            <i class="fas fa-file-alt"></i> New Application
        </button>
        <button class="tab-btn" onclick="showTab('payments')">
            <i class="fas fa-credit-card"></i> Payments
        </button>
        <button class="tab-btn" onclick="showTab('status')">
            <i class="fas fa-chart-line"></i> Application Status
        </button>
    </div>
    
    <!-- Tab 1: My Profile -->
    <div id="profile" class="tab-content active">
        <h2><i class="fas fa-user-circle"></i> Parent/Guardian Information</h2>
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name</label>
                    <p><?php echo htmlspecialchars($user['fullname']); ?></p>
                </div>
                <div class="info-item">
                    <label>Email Address</label>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="info-item">
                    <label>Phone Number</label>
                    <p><?php echo htmlspecialchars($user['phone']); ?></p>
                </div>
                <div class="info-item">
                   <label>Address</label>
                   <p><?php echo htmlspecialchars($user['address']) ?: 'Not provided'; ?></p>
                </div>
                <div class="info-item">
                    <label>Member Since</label>
                    <p><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
                <div class="info-item">
                    <label>Children Enrolled</label>
                    <p><?php echo $total_applications; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab 2: New Application -->
    <div id="application" class="tab-content">
        <h2><i class="fas fa-file-alt"></i> New Student Enrolment Application</h2>
        <form action="process_application.php" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Surname *</label>
                    <input type="text" name="surname" required>
                </div>
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="firstname" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middlename">
                </div>
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required>
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Applying for Class *</label>
                    <select name="class_applying" id="class_applying" required>
                        <option value="">Select Class</option>
                        <option value="Primary 1">Primary 1</option>
                        <option value="Primary 2">Primary 2</option>
                        <option value="Primary 3">Primary 3</option>
                        <option value="Primary 4">Primary 4</option>
                        <option value="Primary 5">Primary 5</option>
                        <option value="Primary 6">Primary 6</option>
                        <option value="JSS 1">JSS 1</option>
                        <option value="JSS 2">JSS 2</option>
                        <option value="JSS 3">JSS 3</option>
                        <option value="SSS 1">SSS 1</option>
                        <option value="SSS 2">SSS 2</option>
                        <option value="SSS 3">SSS 3</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Home Address</label>
                <textarea name="address" rows="2"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Father's Name *</label>
                    <input type="text" name="father_name" required>
                </div>
                <div class="form-group">
                    <label>Mother's Name *</label>
                    <input type="text" name="mother_name" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Parent Phone *</label>
                    <input type="tel" name="parent_phone" required value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                <div class="form-group">
                    <label>Parent Email *</label>
                    <input type="email" name="parent_email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Previous School (if applicable)</label>
                <input type="text" name="previous_school">
            </div>
            
            <!-- File Uploads -->
            <div class="form-group">
                <label><i class="fas fa-camera"></i> Passport Photo *</label>
                <input type="file" name="passport_photo" accept="image/*" required>
                <small>JPEG or PNG only, max 2MB</small>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-birthday-cake"></i> Birth Certificate *</label>
                <input type="file" name="birth_certificate" accept=".pdf,.jpg,.png" required>
                <small>PDF or Image, max 2MB</small>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-file-alt"></i> Last Report Card</label>
                <input type="file" name="report_card" accept=".pdf,.jpg,.png">
                <small>Optional, max 2MB</small>
            </div>
            
            <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Submit Application</button>
        </form>
    </div>
    
    <!-- Tab 3: Payments -->
    <div id="payments" class="tab-content">
        <h2><i class="fas fa-credit-card"></i> Fee Payments</h2>
        
        <?php if (count($payments) > 0): ?>
            <div class="info-card">
                <h3>Payment History</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Student Name</th>
                            <th>Fee Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['receipt_no']); ?></td>
                            <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['fee_type']); ?></td>
                            <td>₦<?php echo number_format($payment['amount']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $payment['status'] == 'completed' ? 'status-approved' : 'status-pending'; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="info-card">
                <p>No payment records found.</p>
                <p style="margin-top: 0.5rem;"><small>Once you make a payment for an application, it will appear here.</small></p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Tab 4: Application Status -->
    <div id="status" class="tab-content">
        <h2><i class="fas fa-chart-line"></i> Application Status</h2>
        
        <?php if (count($applications) > 0): ?>
            <div class="info-card">
                <table>
                    <thead>
                        <tr>
                            <th>Application ID</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['application_id']); ?></td>
                            <td><?php echo htmlspecialchars($app['surname'] . ' ' . $app['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($app['class_applying']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($app['submitted_at'])); ?></td>
                            <td><?php echo getStatusBadge($app['status'], $app['payment_status'] ?? null); ?></td>
                            <td><?php echo getPaymentStatusBadge($app['payment_status'] ?? 'pending'); ?></td>
                            <td>
                                <?php if (showPayNow($app['status'], $app['payment_status'] ?? 'pending')): ?>
                                    <a href="payment.php?application_id=<?php echo $app['application_id']; ?>" class="btn-warning" style="padding: 5px 12px; border-radius: 5px; text-decoration: none; font-size: 0.8rem; display: inline-block;">
                                        <i class="fas fa-credit-card"></i> Pay Now
                                    </a>
                                <?php elseif ($app['status'] == 'approved'): ?>
                                    <span style="color: #28a745;"><i class="fas fa-check-circle"></i> Enrolled ✅</span>
                                <?php elseif ($app['status'] == 'rejected'): ?>
                                    <span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Not Selected</span>
                                <?php elseif ($app['status'] == 'pending' && ($app['payment_status'] == 'paid' || $app['payment_status'] == 'verified')): ?>
                                    <span style="color: #17a2b8;"><i class="fas fa-clock"></i> Awaiting Admin Review</span>
                                <?php else: ?>
                                    <span style="color: #ffc107;"><i class="fas fa-clock"></i> Processing</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="info-card">
                <p>You haven't submitted any applications yet.</p>
                <button class="btn-primary" onclick="showTab('application')">Start New Application</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer style="background: #0f2a43; color: white; text-align: center; padding: 2rem; margin-top: 2rem;">
    <p>&copy; 2026 Crowther Memorial College, Lokoja. All rights reserved.</p>
</footer>

<script>
    // Tab switching function
    function showTab(tabId) {
        var contents = document.getElementsByClassName('tab-content');
        for (var i = 0; i < contents.length; i++) {
            contents[i].classList.remove('active');
        }
        
        var buttons = document.getElementsByClassName('tab-btn');
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].classList.remove('active');
        }
        
        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
    }
</script>

</body>
</html>
<?php mysqli_close($conn); ?>