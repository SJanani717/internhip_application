<?php
// Start session to access user data and check login status
session_start();

// Redirect user to login page if they are not logged in (Security check)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header('Location: login.php');
    exit;
}

// Include the connection file, though it's not strictly needed until submission, 
// it's good practice to have it available.
include 'db_connect.php'; 

$username = $_SESSION['username']; // Get username for display/pre-filling

// Placeholder for any submission status messages
$message = ''; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Step 3: Application Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Step 3: Application Form</h2>
        <p>Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong></p>
        
        <?php echo $message; ?>

        <form action="submit_application.php" method="POST" enctype="multipart/form-data">

            <h3>Personal Information</h3>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($username); ?>"><br>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required><br>
            
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required><br>
            
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required><br>

            <h3>Educational Background</h3>
            <label for="college">College/University:</label>
            <input type="text" id="college" name="college" required><br>
            
            <label for="degree">Degree Pursuing:</label>
            <input type="text" id="degree" name="degree" required><br>
            
            <label for="major">Major/Field of Study:</label>
            <select id="major" name="major" required>
                <option value="">-- Select --</option>
                <option value="Data Science">Data Science</option>
                <option value="Web Technologies">Web Technologies</option>
                <option value="Networking">Networking</option>
                </select><br>

            <label for="year">Current Year:</label>
            <input type="number" id="year" name="year" min="1" max="5" required><br>

            <h3>Internship Information</h3>
            <label for="department">Department of Interest:</label>
            <select id="department" name="department" required>
                <option value="">-- Select --</option>
                <option value="IT">IT</option>
                <option value="HR">HR</option>
                <option value="Marketing">Marketing</option>
            </select><br>
            
            <label for="availability">Availability:</label>
            <select id="availability" name="availability" required>
                <option value="">-- Select --</option>
                <option value="Immediate">Immediate</option>
                <option value="Next Quarter">Next Quarter</option>
                <option value="Summer">Summer</option>
            </select><br>
            
            <label for="resume">Resume (PDF):</label>
            <input type="file" id="resume" name="resume" accept=".pdf" required><br>

            <label for="cover_letter">Cover Letter (PDF):</label>
            <input type="file" id="cover_letter" name="cover_letter" accept=".pdf" required><br>

            <input type="submit" name="submit_application" value="Submit Application">
        </form>
    </div>
</body>
</html>