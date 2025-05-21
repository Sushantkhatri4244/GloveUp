<?php 
session_start(); 

// Database configuration 
include_once 'dbConnection.php';  

try {
    // Get POST data
    $emailOrPhone = trim($_POST['emailOrMobile']);
    $passwordInput = trim($_POST['password']);
    
    if (empty($emailOrPhone) || empty($passwordInput)) {
        throw new Exception("Please fill in all fields.");
    }
    
    // Determine if input is email or phone
    if (filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :input");
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE phone_number = :input");
    }
    
    $stmt->execute(['input' => $emailOrPhone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found.");
    }
    
    if (!password_verify($passwordInput, $user['password'])) {
        throw new Exception("Incorrect password.");
    }
    
    // Success – store session info
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['user_role'] = $user['role']; // Assuming role is stored in the users table
    
    // Redirect based on user role - Using case-insensitive comparison
    if (isset($user['role']) && strtolower($user['role']) === 'admin') {
        $_SESSION['success_message'] = "Welcome Admin!";
        $_SESSION['message_type'] = "success";
        header("Location: user.php");
        exit();
    } else {
        // $_SESSION['success_message'] = "Welcome back, " . $user['full_name'] . "!";
        $_SESSION['message_type'] = "success";
        header("Location: landingPage.php");
        exit();
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    $_SESSION['message_type'] = "danger";
    header("Location: login.php");
    exit();
}
?>