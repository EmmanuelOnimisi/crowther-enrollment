<?php
// submit_application.php - handles form submission

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $surname = $_POST['surname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $class_applying = $_POST['class_applying'];
    $address = $_POST['address'];
    $father_name = $_POST['father_name'];
    $mother_name = $_POST['mother_name'];
    $parent_phone = $_POST['parent_phone'];
    $parent_email = $_POST['parent_email'];
    $parent_occupation = $_POST['parent_occupation'];
    $previous_school = $_POST['previous_school'];
    $last_class = $_POST['last_class'];
    $transfer_reason = $_POST['transfer_reason'];
    
    // For now, just display the submitted data (we'll connect to database later)
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Application Submitted | Crowther Memorial College</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <style>
            .success-container {
                max-width: 600px;
                margin: 120px auto;
                text-align: center;
                background: white;
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            }
            .success-icon {
                font-size: 5rem;
                color: #28a745;
                margin-bottom: 1rem;
            }
            .btn-back {
                display: inline-block;
                margin-top: 1rem;
                padding: 10px 20px;
                background: #1a3c5e;
                color: white;
                text-decoration: none;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="success-container">
            <i class="fas fa-check-circle success-icon"></i>
            <h1>Application Received!</h1>
            <p>Thank you for applying to Crowther Memorial College, Lokoja.</p>
            <p><strong>Application ID:</strong> CMC<?php echo date('YmdHis'); ?></p>
            <p>We have received your application for <strong><?php echo $class_applying; ?></strong>.</p>
            <p>A confirmation email has been sent to <strong><?php echo $parent_email; ?></strong>.</p>
            <p>Please keep your Application ID for future reference.</p>
            <a href="index.php" class="btn-back"><i class="fas fa-home"></i> Return to Home</a>
        </div>
    </body>
    </html>
    <?php
} else {
    // If someone tries to access this file directly without submitting form
    header('Location: apply.php');
    exit();
}
?>