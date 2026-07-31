<?php
// apply.php - Student Enrolment Application Form with Database Fee Calculation
session_start();
include 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details to pre-fill parent information
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// ============= FETCH FEE SETTINGS FROM DATABASE =============
$fee_settings_query = "SELECT * FROM system_settings WHERE setting_key IN ('primary_tuition', 'primary_levy', 'jss_tuition', 'jss_levy', 'sss_tuition', 'sss_levy', 'application_fee')";
$fee_settings_result = mysqli_query($conn, $fee_settings_query);
$fee_settings = [];
while ($row = mysqli_fetch_assoc($fee_settings_result)) {
    $fee_settings[$row['setting_key']] = $row['setting_value'];
}

// Set default values if not found in database
$primary_tuition = $fee_settings['primary_tuition'] ?? 35000;
$primary_levy = $fee_settings['primary_levy'] ?? 8000;
$jss_tuition = $fee_settings['jss_tuition'] ?? 45000;
$jss_levy = $fee_settings['jss_levy'] ?? 10000;
$sss_tuition = $fee_settings['sss_tuition'] ?? 55000;
$sss_levy = $fee_settings['sss_levy'] ?? 12000;
$application_fee = $fee_settings['application_fee'] ?? 5000;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Now | Crowther Memorial College</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 120px auto 60px;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        .form-container h1 {
            color: #1a3c5e;
            margin-bottom: 0.5rem;
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        .form-section h2 {
            color: #1a3c5e;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }
        .form-section h2 i {
            color: #ffc107;
            margin-right: 0.5rem;
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
        .form-group label i {
            color: #ffc107;
            width: 25px;
        }
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group input[type="file"] {
            padding: 5px;
        }
        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #ffc107;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .required::after {
            content: " *";
            color: red;
        }
        .submit-btn {
            background: #1a3c5e;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background: #ffc107;
            color: #1a3c5e;
        }
        .file-hint {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }
        .fee-display {
            background: #f4f6f9;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid #ffc107;
        }
        .fee-display h4 {
            color: #1a3c5e;
            margin-bottom: 0.5rem;
        }
        .fee-display p {
            margin: 0.3rem 0;
        }
        @media (max-width: 768px) {
            .form-container {
                margin: 100px 20px 40px;
                padding: 1.5rem;
            }
            .form-row {
                grid-template-columns: 1fr;
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
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>

<div class="form-container">
    <h1><i class="fas fa-edit"></i> Student Enrolment Application</h1>
    <p>Please fill all required fields carefully.</p>
    
    <form action="process_application.php" method="POST" enctype="multipart/form-data">
        
        <!-- Student Information -->
        <div class="form-section">
            <h2><i class="fas fa-user-graduate"></i> Student Information</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="required"><i class="fas fa-user"></i> Surname</label>
                    <input type="text" name="surname" required>
                </div>
                
                <div class="form-group">
                    <label class="required"><i class="fas fa-user"></i> First Name</label>
                    <input type="text" name="firstname" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Middle Name</label>
                    <input type="text" name="middlename">
                </div>
                
                <div class="form-group">
                    <label class="required"><i class="fas fa-calendar"></i> Date of Birth</label>
                    <input type="date" name="dob" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="required"><i class="fas fa-venus-mars"></i> Gender</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="required"><i class="fas fa-chalkboard"></i> Applying for Class</label>
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
                <label><i class="fas fa-map-marker-alt"></i> Home Address</label>
                <textarea name="address" rows="2" placeholder="Enter student's home address"></textarea>
            </div>
        </div>
        
        <!-- Parent/Guardian Information -->
        <div class="form-section">
            <h2><i class="fas fa-users"></i> Parent/Guardian Information</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="required"><i class="fas fa-user-friends"></i> Father's Name</label>
                    <input type="text" name="father_name" required>
                </div>
                
                <div class="form-group">
                    <label class="required"><i class="fas fa-user-friends"></i> Mother's Name</label>
                    <input type="text" name="mother_name" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="required"><i class="fas fa-phone"></i> Parent Phone</label>
                    <input type="tel" name="parent_phone" required>
                </div>
                
                <div class="form-group">
                    <label class="required"><i class="fas fa-envelope"></i> Parent Email</label>
                    <input type="email" name="parent_email" required>
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-briefcase"></i> Occupation</label>
                <input type="text" name="parent_occupation" placeholder="e.g., Civil Servant, Businessman">
            </div>
        </div>
        
        <!-- Previous School Information -->
        <div class="form-section">
            <h2><i class="fas fa-school"></i> Previous School Information</h2>
            
            <div class="form-group">
                <label>Previous School Name</label>
                <input type="text" name="previous_school" placeholder="If applicable">
            </div>
            
            <div class="form-group">
                <label>Last Class Completed</label>
                <input type="text" name="last_class" placeholder="e.g., Primary 5">
            </div>
            
            <div class="form-group">
                <label>Reason for Transfer (if any)</label>
                <textarea name="transfer_reason" rows="2" placeholder="e.g., Relocation, Better facilities"></textarea>
            </div>
        </div>
        
        <!-- Fee Display -->
        <div class="form-section">
            <h2><i class="fas fa-money-bill-wave"></i> Fee Structure</h2>
            <div id="feeDisplay" class="fee-display" style="display: none;"></div>
        </div>
        
        <!-- Document Uploads -->
        <div class="form-section">
            <h2><i class="fas fa-upload"></i> Required Documents</h2>
            
            <div class="form-group">
                <label class="required"><i class="fas fa-camera"></i> Passport Photo</label>
                <input type="file" name="passport_photo" accept="image/jpeg,image/png,image/jpg" required>
                <div class="file-hint"><i class="fas fa-info-circle"></i> JPEG or PNG only, max 2MB. Recent passport photograph.</div>
            </div>
            
            <div class="form-group">
                <label class="required"><i class="fas fa-birthday-cake"></i> Birth Certificate</label>
                <input type="file" name="birth_certificate" accept=".pdf,.jpg,.jpeg,.png" required>
                <div class="file-hint"><i class="fas fa-info-circle"></i> PDF or Image (JPEG/PNG), max 2MB</div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-file-alt"></i> Last Report Card</label>
                <input type="file" name="report_card" accept=".pdf,.jpg,.jpeg,.png">
                <div class="file-hint"><i class="fas fa-info-circle"></i> Optional but recommended. PDF or Image, max 2MB</div>
            </div>
        </div>
        
        <!-- Declaration -->
        <div class="form-section">
            <h2><i class="fas fa-check-circle"></i> Declaration</h2>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="declaration" required>
                    I hereby declare that all information provided and documents uploaded are true and correct to the best of my knowledge.
                </label>
            </div>
        </div>
        
        <button type="submit" class="submit-btn">
            <i class="fas fa-paper-plane"></i> Submit Application
        </button>
    </form>
</div>

<!-- Footer -->
<footer>
    <div class="container">
        <p>&copy; 2026 Crowther Memorial College, Lokoja. All rights reserved.</p>
    </div>
</footer>

<script>
    // Fee structure by class (loaded from database via PHP)
    const feeStructure = {
        'Primary': { 
            tuition: <?php echo $primary_tuition; ?>, 
            levy: <?php echo $primary_levy; ?>, 
            total: <?php echo $primary_tuition + $primary_levy + $application_fee; ?> 
        },
        'JSS': { 
            tuition: <?php echo $jss_tuition; ?>, 
            levy: <?php echo $jss_levy; ?>, 
            total: <?php echo $jss_tuition + $jss_levy + $application_fee; ?> 
        },
        'SSS': { 
            tuition: <?php echo $sss_tuition; ?>, 
            levy: <?php echo $sss_levy; ?>, 
            total: <?php echo $sss_tuition + $sss_levy + $application_fee; ?> 
        }
    };
    
    const applicationFee = <?php echo $application_fee; ?>;
    
    const classSelect = document.getElementById('class_applying');
    const feeDisplay = document.getElementById('feeDisplay');
    
    classSelect.addEventListener('change', function() {
        const selectedClass = this.value;
        
        let category = '';
        if (selectedClass.includes('Primary')) category = 'Primary';
        else if (selectedClass.includes('JSS')) category = 'JSS';
        else if (selectedClass.includes('SSS')) category = 'SSS';
        
        if (category && selectedClass !== '') {
            const fees = feeStructure[category];
            feeDisplay.innerHTML = `
                <h4><i class="fas fa-calculator"></i> Fee Breakdown for ${selectedClass}:</h4>
                <p><strong>Application Fee:</strong> ₦${applicationFee.toLocaleString()} <span style="color: #28a745;">(one-time payment)</span></p>
                <p><strong>Tuition Fee (per term):</strong> ₦${fees.tuition.toLocaleString()}</p>
                <p><strong>Development Levy (per term):</strong> ₦${fees.levy.toLocaleString()}</p>
                <hr>
                <p><strong>Total Payable (first term):</strong> ₦${(applicationFee + fees.tuition + fees.levy).toLocaleString()}</p>
                <p><strong>Subsequent terms:</strong> ₦${(fees.tuition + fees.levy).toLocaleString()} per term</p>
                <small><i class="fas fa-info-circle"></i> School fees are payable per term. Three terms per session.</small>
            `;
            feeDisplay.style.display = 'block';
        } else {
            feeDisplay.style.display = 'none';
        }
    });
</script>
<script src="assets/js/main.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>