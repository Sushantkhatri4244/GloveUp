<?php
// Start the session (if not already started)
session_start();

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Finally, destroy the session
session_destroy();

// Set a logout message
session_start(); // Start a new session for the message
$_SESSION['logout_message'] = "You have been successfully logged out.";
$_SESSION['message_type'] = "success";

// Redirect to login page
header("Location: login.php");
exit();
?>