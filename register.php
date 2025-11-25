<?php
// Start session for potential use after login/redirect
session_start();
// Include the database connection file
include 'db_connect.php'; 

$message = ''; // Variable to hold success or error messages

if (isset($_POST['register'])) {
    // 1. Get and sanitize input
    $user = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // 2. (d) Server-Side Validation: Check email format and basic password length
    if (empty($user) || empty($email) || empty($password)) {
        $message = "<p style='color:red;'>All fields are required.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p style='color:red;'>Invalid email format.</p>";
    } elseif (strlen($password) < 6) {
        $message = "<p style='color:red;'>Password must be at least 6 characters long.</p>";
    } else {
        // Hashing password for security (Best Practice)
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

        // 3. Prepare the SQL statement using placeholders (?)
        $stmt = $conn->prepare("INSERT INTO useraccount (username, email, password) VALUES (?, ?, ?)");
        
        // Check if statement preparation failed (good practice)
        if ($stmt === false) {
            $message = '<p style="color:red;">Database error: Statement preparation failed.</p>';
        } else {
            // 4. Bind parameters ("sss" indicates three strings)
            // This is the step that PREVENTS SQL INJECTION
            $stmt->bind_param("sss", $user, $email, $hashed_pass);
            
            if ($stmt->execute()) {
                $message = "<p class='success-message'>Registration successful! Please <a href='login.php'>Login here</a>.</p>";
            } else {
                // Check for duplicate email error (error code 1062 for MySQL)
                if ($conn->errno == 1062) {
                    $message = "<p style='color:red;'>Error: Email address already registered.</p>";
                } else {
                    $message = "<p style='color:red;'>Error during registration: " . $stmt->error . "</p>";
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Step 1: Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Step 1: Register</h2>
        
        <?php echo $message; ?>

        <form method="post" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            
            <input type="submit" name="register" value="Register">
        </form>
    </div>
</body>
</html>