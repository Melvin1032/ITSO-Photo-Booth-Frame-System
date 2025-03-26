<?php
session_start();
session_unset(); 
session_destroy(); 

// Remove session cookie
if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page
header("Location: ../auth/login.php");
exit();
?>