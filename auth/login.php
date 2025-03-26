<?php
session_start();
require '../config/db.php';

$error = ''; // Initialize the error variable
$success = isset($_SESSION["success"]) ? $_SESSION["success"] : ''; // Retrieve success message

// Unset the success message after displaying it
unset($_SESSION["success"]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Login logic
    if (isset($_POST["login"])) {
        // Ensure the keys exist before accessing them
        if (isset($_POST["username"]) && isset($_POST["password"])) {
            $username = trim($_POST["username"]);
            $password = trim($_POST["password"]);

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["username"] = $user["username"];  

                // Redirect based on role
                if ($user["role"] === "admin") {
                    header("Location: ../admin/admin_upload.php");
                } else {
                    header("Location: ../user/user_dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid username or password."; 
            }
        } else {
            $error = "Please enter both username and password.";
        }
    }

    // Registration logic
    if (isset($_POST["register"])) {
        if (!empty($_POST["username"]) && !empty($_POST["email"]) && !empty($_POST["password"]) && !empty($_POST["role"])) {
            $username = trim($_POST["username"]);
            $email = trim($_POST["email"]);
            $password = trim($_POST["password"]);
            $role = trim($_POST["role"]); // Capture role from form input

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } elseif (strlen($password) < 6) {
                $error = "Password must be at least 6 characters long.";
            } else {
                // Check if the username or email is already taken
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                $existingUser  = $stmt->fetch();

                if ($existingUser ) {
                    $error = "Username or Email already in use.";
                } else {
                    // Hash the password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new user into the database
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashedPassword, $role]); // Use captured role

                    // Auto-login the user
                    $_SESSION["user_id"] = $pdo->lastInsertId();
                    $_SESSION["role"] = $role; 
                    $_SESSION["username"] = $username;

                    // Redirect to login page with success message
                    $_SESSION["success"] = "Account created successfully! Please log in.";
                    header("Location: ../auth/login.php");
                    exit();
                }
            }
        } else {
            $error = "Please fill in all the registration fields.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log-in</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<video autoplay muted loop id="bg-video">
        <source src="../assets/warehouse.mp4" type="video/mp4">
        Your browser does not support the video tag.
</video>

<div class="container" id="container">
    <div class="form-container sign-up-container">
        <form method="post">
            <h1>Create Account</h1>
            <span>or use your email for registration</span>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="hidden" name="role" value="user">
            <button type="submit" name="register">Register</button>
        </form>
    </div>
    <div class="form-container sign-in-container">
        <form method="post">
            <h1>Login</h1>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <?php if ($error): ?>
                <p class="error-message"><?php echo $error; ?></p> <!-- Display error if any -->
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success-message"><?php echo $success; ?></p> <!-- Display success message if any -->
            <?php endif; ?>
            <a href="#">Forgot your password?</a>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Welcome Back!</h1>
                <p>To keep connected with us please login with your personal info</p>
                <button class="ghost" id="signIn">Login</button>
            </div>
            <div class="overlay-panel overlay-right">
                <img src="../assets/logo_white.png" alt="">
                <p>Don't have an account? Click the Register button.</p>
                <button class="ghost" id="signUp">Register</button>
            </div>
        </div>
    </div>
</div>
<!-- JS SCRIPT -->
<script src="../js/loginRegisterToggle.js"></script>
</body>
</html>