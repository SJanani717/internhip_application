<?php
// Start the session at the very beginning
session_start();

// Include the database connection file
include 'db_connect.php'; 

$message = ''; // Variable to hold error messages

if (isset($_POST['login'])) {
    // 1. Get and sanitize input
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // 2. Prepare the SQL statement to select the user and their hashed password
    // Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT id, username, password FROM useraccount WHERE email = ?");
    
    if ($stmt === false) {
        $message = '<p style="color:red;">Database error: Statement preparation failed.</p>';
    } else {
        // 3. Bind the email parameter ("s" indicates string)
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // User found, fetch the data (including the stored hash)
            $user = $result->fetch_assoc();
            $hashed_password = $user['password'];

            // 4. Verify the password
            // Use password_verify() to check the plain text password against the stored hash
            if (password_verify($password, $hashed_password)) {
                
                // Login successful! (Step 1b)
                
                // 5. Set session variables
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // 6. Redirect to the application form (Step 1b)
                header('Location: application_form.php');
                exit; // Important to stop script execution after redirect
            } else {
                // Password does not match
                $message = "<p style='color:red;'>Incorrect email or password.</p>";
            }
        } else {
            // User not found
            $message = "<p style='color:red;'>Incorrect email or password.</p>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Step 2: Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Step 2: Login</h2>
        
        <?php echo $message; ?>

        <form method="post" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            
            <input type="submit" name="login" value="Login">
        </form>
    </div>
</body>
</html>