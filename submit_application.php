<?php
// Start the session
session_start();

// Security Check: Redirect if user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header('Location: login.php');
    exit;
}

// Ensure db_connect.php is correctly included and provides $conn
include 'db_connect.php'; 

// Define the directory for file uploads (Step 1e)
$target_dir = "uploads/";
// Ensure the uploads directory exists
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$errors = [];
$submitted_data = [];
$submission_success = false;

if (isset($_POST['submit_application'])) {
    // 1. Collect and Sanitize Input
    $user_id = $_SESSION['user_id'];
    $data = [
        'name' => htmlspecialchars($_POST['name']),
        'email' => htmlspecialchars($_POST['email']),
        'phone' => htmlspecialchars($_POST['phone']),
        'dob' => htmlspecialchars($_POST['dob']),
        'address' => htmlspecialchars($_POST['address']),
        'college' => htmlspecialchars($_POST['college']),
        'degree' => htmlspecialchars($_POST['degree']),
        'major' => htmlspecialchars($_POST['major']),
        'year' => htmlspecialchars($_POST['year']),
        'department' => htmlspecialchars($_POST['department']), // Data collected from form
        'availability' => htmlspecialchars($_POST['availability']),
        'resume_path' => '', // Will be filled during file upload
        'cover_path' => '',  // Will be filled during file upload
    ];

    // 2. Server-Side Validation (Step 1d)
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!is_numeric($data['year']) || $data['year'] < 1 || $data['year'] > 5) {
        $errors[] = "Current Year must be a number between 1 and 5.";
    }
    foreach ($data as $key => $value) {
        if ($key != 'resume_path' && $key != 'cover_path' && empty($value)) {
             $errors[] = "The " . ucfirst($key) . " field is required.";
        }
    }

    // 3. File Upload Handling (Step 1e)
    $files = ['resume' => 'resume_path', 'cover_letter' => 'cover_path'];
    foreach ($files as $input_name => $path_key) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$input_name];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // Check file type (Only allow PDF file types)
            if ($file_ext !== 'pdf') {
                $errors[] = "The {$input_name} must be a PDF file.";
                continue;
            }

            // Create a unique file name
            $new_file_name = $target_dir . $user_id . "_" . $input_name . "_" . time() . "." . $file_ext;

            // Move the uploaded file
            if (move_uploaded_file($file['tmp_name'], $new_file_name)) {
                $data[$path_key] = $new_file_name;
            } else {
                $errors[] = "Failed to upload {$input_name}. Check directory permissions.";
            }
        } else {
            $errors[] = ucfirst($input_name) . " upload failed or file was missing.";
        }
    }

    // 4. Database Insertion (Step 1f)
    // *** CRITICAL FIX: Ensure all database operations are inside this block ***
    if (empty($errors)) {
        
        // FIX 1: Column 'department' changed to 'dept_interest'
        // Cor
    // This is the clean SQL query, ready for preparation.
    $sql = "INSERT INTO candidates (
        user_id, full_name, email, phone, dob, address, college, degree, major, 
        current_year, dept_interest, availability, resume_path, cover_letter_path
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Line 96, where the error occurs, will now prepare the clean SQL string.
    $stmt = $conn->prepare($sql); 
    
    // ... rest of the database logic ...
        
        // Check if the prepare succeeded
        if ($stmt === FALSE) {
            $errors[] = "SQL Prepare failed: " . $conn->error;
        } else {
            // FIX 2: Corrected type string to 'issssssssiisss' (10th param 'year' is integer)
            $stmt->bind_param(
                "issssssssiisss", 
                $user_id, $data['name'], $data['email'], $data['phone'], $data['dob'], 
                $data['address'], $data['college'], $data['degree'], $data['major'], 
                $data['year'], $data['department'], $data['availability'], 
                $data['resume_path'], $data['cover_path']
            );

            if ($stmt->execute()) {
                $submission_success = true;
                $submitted_data = $data; // Store data for display (Step 1g)
            } else {
                $errors[] = "Database insertion failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
// This closing brace is for 'if (isset($_POST['submit_application']))'
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Submission Status</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        
        <?php if ($submission_success): ?>
            <h2>Application submitted successfully!</h2>
            <p class="success-message">Application submitted successfully! Also, display the submitted form data below the message.</p>
            
            <h3>My Application (Step 4)</h3>
            
            <table class="data-summary">
                <tr><th colspan="2">Personal Information</th></tr>
                <tr><td>Name:</td><td><?php echo $submitted_data['name']; ?></td></tr>
                <tr><td>Email:</td><td><?php echo $submitted_data['email']; ?></td></tr>
                <tr><td>Phone:</td><td><?php echo $submitted_data['phone']; ?></td></tr>
                <tr><td>Date of Birth:</td><td><?php echo $submitted_data['dob']; ?></td></tr>
                <tr><td>Address:</td><td><?php echo $submitted_data['address']; ?></td></td></tr>
                
                <tr><th colspan="2">Educational Background</th></tr>
                <tr><td>College/University:</td><td><?php echo $submitted_data['college']; ?></td></tr>
                <tr><td>Degree Pursuing:</td><td><?php echo $submitted_data['degree']; ?></td></tr>
                <tr><td>Major/Field of Study:</td><td><?php echo $submitted_data['major']; ?></td></tr>
                <tr><td>Current Year:</td><td><?php echo $submitted_data['year']; ?></td></tr>
                
                <tr><th colspan="2">Internship Information</th></tr>
                <tr><td>Department of Interest:</td><td><?php echo $submitted_data['department']; ?></td></tr>
                <tr><td>Availability:</td><td><?php echo $submitted_data['availability']; ?></td></tr>
                
                <tr><th colspan="2">Submitted Documents</th></tr>
                <tr><td>Resume:</td><td><a href="<?php echo $submitted_data['resume_path']; ?>" target="_blank">View Resume (PDF)</a></td></tr>
                <tr><td>Cover Letter:</td><td><a href="<?php echo $submitted_data['cover_path']; ?>" target="_blank">View Cover Letter (PDF)</a></td></tr>
            </table>

        <?php else: ?>
            <h2>Application Submission Failed</h2>
            <div style="color:red;">
                <p>Please fix the following errors:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <p><a href="application_form.php">Go back to the form</a></p>
        <?php endif; ?>
        
        <hr>
        <p><a href="index.php">Return to Home</a></p>

    </div>
</body>
</html>