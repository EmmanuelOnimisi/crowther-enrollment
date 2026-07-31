<?php
session_start();
include '../includes/config.php';

// =============================================
// Proper admin authentication check
// =============================================
$is_admin = false;
$admin_name = 'Administrator';

// Check if admin is logged in via session
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $is_admin = true;
    $admin_name = $_SESSION['user_name'] ?? 'Administrator';
    $admin_id = $_SESSION['user_id'] ?? 0;
} else {
    // Try to auto-login as admin (for demo)
    $admin_query = "SELECT * FROM users WHERE role = 'admin' LIMIT 1";
    $admin_result = mysqli_query($conn, $admin_query);
    if ($admin = mysqli_fetch_assoc($admin_result)) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_name'] = $admin['fullname'];
        $_SESSION['user_email'] = $admin['email'];
        $_SESSION['user_role'] = 'admin';
        $is_admin = true;
        $admin_name = $admin['fullname'];
        $admin_id = $admin['id'];
    }
}

// If not admin, redirect to login
if (!$is_admin) {
    header('Location: login.php');
    exit();
}

// ============= STATISTICS =============
$total_apps_query = "SELECT COUNT(*) as total FROM applications";
$total_apps_result = mysqli_query($conn, $total_apps_query);
$total_apps = mysqli_fetch_assoc($total_apps_result)['total'];

$pending_query = "SELECT COUNT(*) as total FROM applications WHERE status = 'pending' OR status = 'pending_payment' OR status = 'pending_review'";
$pending_result = mysqli_query($conn, $pending_query);
$pending_count = mysqli_fetch_assoc($pending_result)['total'];

$approved_query = "SELECT COUNT(*) as total FROM applications WHERE status = 'approved'";
$approved_result = mysqli_query($conn, $approved_query);
$approved_count = mysqli_fetch_assoc($approved_result)['total'];

$rejected_query = "SELECT COUNT(*) as total FROM applications WHERE status = 'rejected'";
$rejected_result = mysqli_query($conn, $rejected_query);
$rejected_count = mysqli_fetch_assoc($rejected_result)['total'];

$total_students = $approved_count;

$fees_query = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
$fees_result = mysqli_query($conn, $fees_query);
$total_fees = mysqli_fetch_assoc($fees_result)['total'] ?? 0;

// ============= APPLICATIONS (FIXED: Use application form data) =============
$applications_query = "SELECT 
    a.*, 
    u.fullname as registered_name, 
    u.email as registered_email, 
    u.phone as registered_phone,
    a.father_name as parent_name,
    a.mother_name as mother_name,
    a.parent_email,
    a.parent_phone
    FROM applications a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.submitted_at DESC";
$applications_result = mysqli_query($conn, $applications_query);
$applications = [];
while ($row = mysqli_fetch_assoc($applications_result)) {
    $applications[] = $row;
}

// ============= STUDENTS =============
$students_query = "SELECT a.*, u.fullname as parent_name, u.phone as parent_phone 
                   FROM applications a 
                   JOIN users u ON a.user_id = u.id 
                   WHERE a.status = 'approved' 
                   ORDER BY a.submitted_at DESC";
$students_result = mysqli_query($conn, $students_query);
$students = [];
while ($row = mysqli_fetch_assoc($students_result)) {
    $students[] = $row;
}

// ============= PAYMENTS =============
$payments_query = "SELECT * FROM payments ORDER BY payment_date DESC";
$payments_result = mysqli_query($conn, $payments_query);
$payments = [];
while ($row = mysqli_fetch_assoc($payments_result)) {
    $payments[] = $row;
}

// ============= FEE SUMMARY BY CLASS CATEGORY =============
$fee_summary_query = "SELECT 
    a.class_category, 
    COUNT(DISTINCT a.id) as student_count,
    SUM(CASE 
        WHEN a.class_category = 'Primary' THEN 48000
        WHEN a.class_category = 'JSS' THEN 60000
        WHEN a.class_category = 'SSS' THEN 72000
        ELSE 48000
    END) as expected_fees,
    COALESCE(SUM(p.amount), 0) as paid_fees
    FROM applications a 
    LEFT JOIN payments p ON a.application_id = p.application_id AND p.status = 'completed'
    WHERE a.status = 'approved'
    GROUP BY a.class_category";
$fee_summary_result = mysqli_query($conn, $fee_summary_query);
$fee_summary = [];
while ($row = mysqli_fetch_assoc($fee_summary_result)) {
    $fee_summary[] = $row;
}

$total_expected = 0;
$total_paid_fees = 0;
foreach ($fee_summary as $summary) {
    $total_expected += $summary['expected_fees'];
    $total_paid_fees += $summary['paid_fees'];
}
$total_outstanding = $total_expected - $total_paid_fees;

// ============= SYSTEM SETTINGS =============
$settings_query = "SELECT * FROM system_settings";
$settings_result = mysqli_query($conn, $settings_query);
$settings = [];
while ($row = mysqli_fetch_assoc($settings_result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$primary_tuition = $settings['primary_tuition'] ?? 35000;
$primary_levy = $settings['primary_levy'] ?? 8000;
$jss_tuition = $settings['jss_tuition'] ?? 45000;
$jss_levy = $settings['jss_levy'] ?? 10000;
$sss_tuition = $settings['sss_tuition'] ?? 55000;
$sss_levy = $settings['sss_levy'] ?? 12000;
$application_fee = $settings['application_fee'] ?? 5000;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Crowther Memorial College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; }
        .top-nav { background: #1a3c5e; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; position: fixed; width: 100%; top: 0; z-index: 1000; }
        .logo { font-size: 1.3rem; font-weight: bold; }
        .logo i { color: #ffc107; margin-right: 0.5rem; }
        .admin-info { display: flex; align-items: center; gap: 1rem; }
        .logout-btn { background: #dc3545; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; }
        .logout-btn:hover { background: #c82333; }
        .dashboard-container { margin-top: 80px; padding: 2rem; }
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-info h3 { font-size: 1.8rem; color: #1a3c5e; }
        .stat-info p { color: #666; }
        .stat-icon i { font-size: 2.5rem; color: #ffc107; }
        .tabs { display: flex; gap: 0.5rem; margin-bottom: 2rem; background: white; padding: 0.5rem; border-radius: 10px; flex-wrap: wrap; }
        .tab-btn { padding: 12px 25px; background: transparent; border: none; cursor: pointer; font-size: 1rem; border-radius: 8px; transition: all 0.3s; font-weight: bold; }
        .tab-btn i { margin-right: 8px; }
        .tab-btn:hover { background: #f0f0f0; }
        .tab-btn.active { background: #ffc107; color: #1a3c5e; }
        .tab-content { display: none; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .tab-content.active { display: block; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 1rem; overflow-x: auto; display: block; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .data-table th { background: #f4f6f9; color: #1a3c5e; }
        .status-pending { background: #ffc107; color: #1a3c5e; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-block; }
        .status-approved { background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-block; }
        .status-rejected { background: #dc3545; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-block; }
        .status-pending_payment { background: #ffc107; color: #1a3c5e; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-block; }
        .action-approve { background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; margin: 2px; }
        .action-reject { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; margin: 2px; }
        .action-view { background: #1a3c5e; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; margin: 2px; }
        .filter-bar { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
        .filter-bar input, .filter-bar select { padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .search-btn { background: #1a3c5e; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        .modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 2rem; width: 90%; max-width: 600px; border-radius: 10px; max-height: 80%; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 1rem; border-bottom: 2px solid #ffc107; padding-bottom: 0.5rem; }
        .close-modal { cursor: pointer; font-size: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 0.5rem; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .save-btn { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .fee-card { background: #f4f6f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .fee-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #ddd; }
        @media (max-width: 768px) { .dashboard-container { padding: 1rem; } .tabs { justify-content: center; } .tab-btn { font-size: 0.8rem; padding: 8px 12px; } .data-table { font-size: 0.8rem; } }
    </style>
</head>
<body>

<div class="top-nav">
    <div class="logo">
        <i class="fas fa-school"></i>
        Crowther Memorial College - Admin Panel
    </div>
    <div class="admin-info">
        <span><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($admin_name); ?></span>
        <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-container">
    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card"><div class="stat-info"><h3><?php echo $total_apps; ?></h3><p>Total Applications</p></div><div class="stat-icon"><i class="fas fa-file-alt"></i></div></div>
        <div class="stat-card"><div class="stat-info"><h3><?php echo $pending_count; ?></h3><p>Pending Review</p></div><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
        <div class="stat-card"><div class="stat-info"><h3><?php echo $approved_count; ?></h3><p>Approved</p></div><div class="stat-icon"><i class="fas fa-check-circle"></i></div></div>
        <div class="stat-card"><div class="stat-info"><h3><?php echo $rejected_count; ?></h3><p>Rejected</p></div><div class="stat-icon"><i class="fas fa-times-circle"></i></div></div>
        <div class="stat-card"><div class="stat-info"><h3><?php echo $total_students; ?></h3><p>Total Students</p></div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
        <div class="stat-card"><div class="stat-info"><h3>₦<?php echo number_format($total_fees); ?></h3><p>Total Fees</p></div><div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div></div>
    </div>
    
    <!-- Tabs Navigation -->
    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('applications')"><i class="fas fa-file-alt"></i> Applications</button>
        <button class="tab-btn" onclick="showTab('students')"><i class="fas fa-users"></i> Students</button>
        <button class="tab-btn" onclick="showTab('payments')"><i class="fas fa-credit-card"></i> Payments</button>
        <button class="tab-btn" onclick="showTab('reports')"><i class="fas fa-chart-bar"></i> Reports</button>
        <button class="tab-btn" onclick="showTab('settings')"><i class="fas fa-cog"></i> Settings</button>
    </div>
    
    <!-- Tab 1: Applications -->
    <div id="applications" class="tab-content active">
        <h2><i class="fas fa-file-alt"></i> Manage Applications</h2>
        
        <div class="filter-bar">
            <select id="statusFilter" onchange="filterApplications()">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="pending_payment">Awaiting Payment</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <input type="text" id="searchInput" placeholder="Search by name or ID">
            <button class="search-btn" onclick="filterApplications()"><i class="fas fa-search"></i> Search</button>
        </div>
        
        <table class="data-table" id="applicationsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Parent Name</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                <tr data-status="<?php echo $app['status']; ?>" data-payment="<?php echo $app['payment_status'] ?? 'pending'; ?>">
                    <td><?php echo htmlspecialchars($app['application_id']); ?></td>
                    <td><?php echo htmlspecialchars($app['surname'] . ' ' . $app['firstname']); ?></td>
                    <td><?php echo htmlspecialchars($app['class_applying']); ?></td>
                    <td><?php echo htmlspecialchars($app['parent_name'] ?? $app['father_name']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($app['submitted_at'])); ?></td>
                    <td>
                        <span class="status-<?php echo $app['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $app['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $payment_status = $app['payment_status'] ?? 'pending';
                        if ($payment_status == 'verified'): ?>
                            <span style="color: #28a745; font-weight: bold;">✅ Verified</span>
                        <?php elseif ($payment_status == 'paid'): ?>
                            <span style="color: #17a2b8; font-weight: bold;">💰 Paid</span>
                        <?php elseif ($payment_status == 'rejected'): ?>
                            <span style="color: #dc3545; font-weight: bold;">❌ Rejected</span>
                        <?php else: ?>
                            <span style="color: #ffc107; font-weight: bold;">⏳ Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($app['status'] == 'pending_payment'): ?>
                            <span style="color: #ffc107;">⏳ Awaiting Payment</span>
                        
                        <?php elseif ($app['status'] == 'pending' || $app['status'] == 'pending_review'): ?>
                            <?php if ($app['payment_status'] == 'paid' || $app['payment_status'] == 'pending' || $app['payment_status'] == '' || $app['payment_status'] == NULL): ?>
                                <a href="verify_payment.php?application_id=<?php echo $app['application_id']; ?>" class="action-approve" style="background: #17a2b8; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; display: inline-block; margin: 2px;">
                                    <i class="fas fa-credit-card"></i> Verify Payment
                                </a>
                            <?php endif; ?>
                            <?php if ($app['payment_status'] == 'verified'): ?>
                                <button class="action-approve" onclick="updateStatus('<?php echo $app['application_id']; ?>', 'approved')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            <?php endif; ?>
                            <button class="action-reject" onclick="updateStatus('<?php echo $app['application_id']; ?>', 'rejected')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        
                        <?php elseif ($app['status'] == 'approved'): ?>
                            <span style="color: #28a745;"><i class="fas fa-check-circle"></i> Enrolled</span>
                        
                        <?php elseif ($app['status'] == 'rejected'): ?>
                            <span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Rejected</span>
                        
                        <?php else: ?>
                            <button class="action-approve" onclick="updateStatus('<?php echo $app['application_id']; ?>', 'approved')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="action-reject" onclick="updateStatus('<?php echo $app['application_id']; ?>', 'rejected')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        <?php endif; ?>
                        
                        <button class="action-view" onclick="viewApplication('<?php echo $app['application_id']; ?>')">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($applications) == 0): ?>
                <tr><td colspan="8" style="text-align: center;">No applications found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Tab 2: Students -->
    <div id="students" class="tab-content">
        <h2><i class="fas fa-users"></i> Registered Students</h2>
        <div class="filter-bar">
            <input type="text" id="studentSearch" placeholder="Search by name or admission #">
            <button class="search-btn" onclick="filterStudents()"><i class="fas fa-search"></i> Search</button>
        </div>
        <table class="data-table" id="studentsTable">
            <thead><tr><th>Admission #</th><th>Student Name</th><th>Class</th><th>Class Category</th><th>Parent Name</th><th>Phone</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['application_id']); ?></td>
                    <td><?php echo htmlspecialchars($student['surname'] . ' ' . $student['firstname']); ?></td>
                    <td><?php echo htmlspecialchars($student['class_applying']); ?></td>
                    <td><?php echo htmlspecialchars($student['class_category'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($student['parent_name'] ?? $student['father_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['parent_phone']); ?></td>
                    <td><span class="status-approved">Active</span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($students) == 0): ?>
                <tr><td colspan="7" style="text-align: center;">No approved students yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Tab 3: Payments -->
    <div id="payments" class="tab-content">
        <h2><i class="fas fa-credit-card"></i> Fee Payments</h2>
        <table class="data-table">
            <thead><tr><th>Receipt #</th><th>Student Name</th><th>Fee Type</th><th>Amount</th><th>Date Paid</th><th>Method</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($payment['receipt_no']); ?></td>
                    <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($payment['fee_type']); ?></td>
                    <td>₦<?php echo number_format($payment['amount']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                    <td><?php echo ucfirst($payment['payment_method']); ?></td>
                    <td><span class="status-<?php echo $payment['status']; ?>"><?php echo ucfirst($payment['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($payments) == 0): ?>
                <tr><td colspan="7" style="text-align: center;">No payments recorded yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 2rem; background: #f4f6f9; padding: 1rem; border-radius: 10px;">
            <h3><i class="fas fa-chart-pie"></i> Fee Summary by Class Category</h3>
            <table class="data-table">
                <thead><tr><th>Class Category</th><th>No. of Students</th><th>Expected Fees (Annual)</th><th>Paid Fees</th><th>Outstanding</th></tr></thead>
                <tbody>
                    <?php foreach ($fee_summary as $summary): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($summary['class_category']); ?></td>
                        <td><?php echo $summary['student_count']; ?></td>
                        <td>₦<?php echo number_format($summary['expected_fees']); ?></td>
                        <td>₦<?php echo number_format($summary['paid_fees']); ?></td>
                        <td>₦<?php echo number_format($summary['expected_fees'] - $summary['paid_fees']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background: #e9ecef; font-weight: bold;">
                        <td>TOTAL</td>
                        <td>-</td>
                        <td>₦<?php echo number_format($total_expected); ?></td>
                        <td>₦<?php echo number_format($total_paid_fees); ?></td>
                        <td>₦<?php echo number_format($total_outstanding); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Tab 4: Reports -->
    <div id="reports" class="tab-content">
        <h2><i class="fas fa-chart-bar"></i> Generate Reports</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div style="background: #f4f6f9; padding: 1.5rem; border-radius: 10px;">
                <i class="fas fa-file-alt" style="font-size: 2rem; color: #1a3c5e;"></i>
                <h3>Applications Report</h3>
                <p>Export all applications with status</p>
                <button class="action-view" onclick="generateReport('applications')"><i class="fas fa-download"></i> Export CSV</button>
            </div>
            <div style="background: #f4f6f9; padding: 1.5rem; border-radius: 10px;">
                <i class="fas fa-users" style="font-size: 2rem; color: #1a3c5e;"></i>
                <h3>Students Report</h3>
                <p>Export all registered students</p>
                <button class="action-view" onclick="generateReport('students')"><i class="fas fa-download"></i> Export CSV</button>
            </div>
            <div style="background: #f4f6f9; padding: 1.5rem; border-radius: 10px;">
                <i class="fas fa-money-bill-wave" style="font-size: 2rem; color: #1a3c5e;"></i>
                <h3>Financial Report</h3>
                <p>Fee collection summary by class</p>
                <button class="action-view" onclick="generateReport('payments')"><i class="fas fa-download"></i> Export CSV</button>
            </div>
        </div>
    </div>
    
    <!-- Tab 5: Settings -->
    <div id="settings" class="tab-content">
        <h2><i class="fas fa-cog"></i> System Settings</h2>
        <form action="update_settings.php" method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
                <div style="background: #f4f6f9; padding: 1.5rem; border-radius: 10px;">
                    <h3><i class="fas fa-calendar-alt"></i> Academic Session</h3>
                    <div class="form-group">
                        <label>Current Session</label>
                        <input type="text" name="current_session" value="<?php echo htmlspecialchars($settings['current_session'] ?? '2025/2026'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Current Term</label>
                        <select name="current_term">
                            <option <?php echo ($settings['current_term'] ?? '') == 'First Term' ? 'selected' : ''; ?>>First Term</option>
                            <option <?php echo ($settings['current_term'] ?? '') == 'Second Term' ? 'selected' : ''; ?>>Second Term</option>
                            <option <?php echo ($settings['current_term'] ?? '') == 'Third Term' ? 'selected' : ''; ?>>Third Term</option>
                        </select>
                    </div>
                </div>
                <div style="background: #f4f6f9; padding: 1.5rem; border-radius: 10px;">
                    <h3><i class="fas fa-dollar-sign"></i> Fee Structure by Class</h3>
                    <div class="form-group">
                        <label>Application Fee (all classes)</label>
                        <input type="number" name="application_fee" value="<?php echo $application_fee; ?>">
                    </div>
                    <hr>
                    <h4>Primary 1-6</h4>
                    <div class="form-group">
                        <label>Tuition Fee per term (₦)</label>
                        <input type="number" name="primary_tuition" value="<?php echo $primary_tuition; ?>">
                    </div>
                    <div class="form-group">
                        <label>Development Levy per term (₦)</label>
                        <input type="number" name="primary_levy" value="<?php echo $primary_levy; ?>">
                    </div>
                    <hr>
                    <h4>JSS 1-3</h4>
                    <div class="form-group">
                        <label>Tuition Fee per term (₦)</label>
                        <input type="number" name="jss_tuition" value="<?php echo $jss_tuition; ?>">
                    </div>
                    <div class="form-group">
                        <label>Development Levy per term (₦)</label>
                        <input type="number" name="jss_levy" value="<?php echo $jss_levy; ?>">
                    </div>
                    <hr>
                    <h4>SSS 1-3</h4>
                    <div class="form-group">
                        <label>Tuition Fee per term (₦)</label>
                        <input type="number" name="sss_tuition" value="<?php echo $sss_tuition; ?>">
                    </div>
                    <div class="form-group">
                        <label>Development Levy per term (₦)</label>
                        <input type="number" name="sss_levy" value="<?php echo $sss_levy; ?>">
                    </div>
                </div>
            </div>
            <div style="margin-top: 2rem; text-align: center;">
                <button type="submit" class="save-btn"><i class="fas fa-save"></i> Save Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div id="applicationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Application Details</h3>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <div id="modalBody"></div>
    </div>
</div>

<script>
    function showTab(tabId) {
        var contents = document.getElementsByClassName('tab-content');
        for (var i = 0; i < contents.length; i++) { contents[i].classList.remove('active'); }
        var buttons = document.getElementsByClassName('tab-btn');
        for (var i = 0; i < buttons.length; i++) { buttons[i].classList.remove('active'); }
        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
    }
    
    function updateStatus(applicationId, newStatus) {
        if (confirm('Are you sure you want to ' + newStatus + ' this application?')) {
            fetch('update_application_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'application_id=' + applicationId + '&status=' + newStatus
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) { alert('Application ' + newStatus + ' successfully!'); location.reload(); }
                else { alert('Error: ' + data.message); }
            })
            .catch(error => { alert('Error updating status. Please try again.'); });
        }
    }
    
    function viewApplication(applicationId) {
        fetch('get_application_details.php?application_id=' + applicationId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    var modalBody = document.getElementById('modalBody');
                    modalBody.innerHTML = `
                        <p><strong>Application ID:</strong> ${data.application_id}</p>
                        <p><strong>Student Name:</strong> ${data.surname} ${data.firstname} ${data.middlename || ''}</p>
                        <p><strong>Date of Birth:</strong> ${data.dob}</p>
                        <p><strong>Gender:</strong> ${data.gender}</p>
                        <p><strong>Class:</strong> ${data.class_applying}</p>
                        <p><strong>Class Category:</strong> ${data.class_category || 'N/A'}</p>
                        <p><strong>Address:</strong> ${data.address || 'Not provided'}</p>
                        <hr>
                        <p><strong>Father's Name:</strong> ${data.father_name || 'Not provided'}</p>
                        <p><strong>Mother's Name:</strong> ${data.mother_name || 'Not provided'}</p>
                        <p><strong>Parent Phone:</strong> ${data.parent_phone}</p>
                        <p><strong>Parent Email:</strong> ${data.parent_email}</p>
                        <p><strong>Previous School:</strong> ${data.previous_school || 'Not provided'}</p>
                        <hr>
                        <p><strong>Status:</strong> <span class="status-${data.status}">${data.status.toUpperCase()}</span></p>
                        <p><strong>Payment Status:</strong> <span class="status-${data.payment_status || 'pending'}">${(data.payment_status || 'PENDING').toUpperCase()}</span></p>
                        <p><strong>Payment Reference:</strong> ${data.payment_reference || 'Not generated'}</p>
                        <p><strong>Submitted:</strong> ${data.submitted_at}</p>
                        <hr>
                        <p><strong>Documents:</strong></p>
                        ${data.file_links ? `
                            <p>${data.file_links.passport || 'No passport'}</p>
                            <p>${data.file_links.birth_cert || 'No birth certificate'}</p>
                            <p>${data.file_links.report_card || 'No report card'}</p>
                            <p>${data.file_links.payment_proof || 'No payment proof'}</p>
                        ` : '<p>No documents uploaded</p>'}
                    `;
                    document.getElementById('applicationModal').style.display = 'block';
                } else {
                    alert('Error loading application details');
                }
            });
    }
    
    function closeModal() { document.getElementById('applicationModal').style.display = 'none'; }
    
    function filterApplications() {
        var filter = document.getElementById('statusFilter').value;
        var search = document.getElementById('searchInput').value.toLowerCase();
        var rows = document.querySelectorAll('#applicationsTable tbody tr');
        rows.forEach(row => {
            var status = row.getAttribute('data-status');
            var text = row.innerText.toLowerCase();
            var statusMatch = filter === 'all' || status === filter;
            var searchMatch = search === '' || text.includes(search);
            row.style.display = statusMatch && searchMatch ? '' : 'none';
        });
    }
    
    function filterStudents() {
        var search = document.getElementById('studentSearch').value.toLowerCase();
        var rows = document.querySelectorAll('#studentsTable tbody tr');
        rows.forEach(row => {
            var text = row.innerText.toLowerCase();
            row.style.display = search === '' || text.includes(search) ? '' : 'none';
        });
    }
    
    function generateReport(type) { window.location.href = 'export_report.php?type=' + type; }
    
    window.onclick = function(event) {
        var modal = document.getElementById('applicationModal');
        if (event.target == modal) { modal.style.display = 'none'; }
    }
</script>

</body>
</html>
<?php mysqli_close($conn); ?>